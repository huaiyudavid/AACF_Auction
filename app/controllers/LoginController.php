<?php

use Facebook\GraphNodes\GraphUser;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;

class LoginController extends ControllerBase
{
	public function initialize()
	{
		$this->tag->setTitle('Log In');
		parent::initialize();
	}

	public function indexAction()
	{
		// Check if the user is already logged in
		if ($this->session->get('user'))
			return $this->http->redirect();

		$fb = Common::get_facebook();
		$permissions = ['email'];

		$redirect_url = $this->config->application->website . 'login/login';
		$redirect_controller =
			$this->dispatcher->getParam('redirect_controller', 'trim', null);
		if ($redirect_controller)
		{
			$redirect_action =
				$this->dispatcher->getParam('redirect_action', 'trim', null);
			$redirect_params =
				$this->dispatcher->getParam('redirect_params', 'trim', null);
			$redirect_url .= '?redirect_controller=' . urlencode($redirect_controller)
				. '&redirect_action=' . $redirect_action;
			$count = 0;
			foreach ($redirect_params as $param)
			{
				$redirect_url .= '&param' . $count . '=' . $param;
				$count++;
			}
		}

		$this->view->redirect = $fb->getRedirectLoginHelper()->
			getLoginUrl($redirect_url, $permissions);
	}

	public function loginAction()
	{
		$logger = new FileAdapter("test.log");

		// Check if the user is already logged in
		if ($this->session->get('user'))
			return $this->http->redirect();

		$fb = Common::get_facebook();
		try
		{
			$accessToken = $fb->getRedirectLoginHelper()->getAccessToken();
		}
		catch (Exception $e)
		{
			// TODO: Error message about not begin able to login
			return $this->http->redirect('notabletologin');
		}

		if (!$accessToken)
		{
			// TODO: Error message about bad login
			return $this->http->redirect('badlogin');
		}

		if (!$accessToken->isLongLived())
		{
			try
			{
				$oauth = $fb->getOAuth2Client();
				$accessToken = $oauth->getLongLivedAccessToken($accessToken);
			}
			catch (Exception $e)
			{
				// TODO: Error message about bad login
				return $this->http->redirect('badaccesstoken');
			}
		}

		try
		{
			$user_request = $fb->request('GET',
				'/me?fields=first_name,last_name,timezone,email');
			$small_request = $fb->request('GET',
				'/me/picture?type=square&redirect=false');
			$large_request = $fb->request('GET',
				'/me/picture?type=large&redirect=false');
			$responses = $fb->sendBatchRequest([$user_request, $small_request,
				$large_request], $accessToken);

			if ($responses[0]->isError() || $responses[1]->isError() ||
				$responses[2]->isError())
			{
				return $this->http->redirect('errorinresponses');
			}

			$fb_user = json_decode($responses[0]->getBody());
			$fb_picture = json_decode($responses[1]->getBody());
			$fb_picture_large = json_decode($responses[2]->getBody());

			$this->session->set('timezone', $fb_user->timezone);

			// Redirect to signup if user is not stored
			$existed_user = User::find_by_id($fb_user->id);
			if (!$existed_user)
			{
				$this->session->set('facebook', $fb);
				$this->session->set('profile_picture', $fb_picture->data->url);
				$this->session->set('profile_picture_large',
					$fb_picture_large->data->url);
				$this->session->set('fb_user', $fb_user);
				return $this->http->redirect('signup');
			}
			else
			{
				$existed_user->profile_small = $fb_picture->data->url;
				$existed_user->profile_large = $fb_picture_large->data->url;
				$existed_user->save();
			}

			$this->session->set('user', $existed_user);
			$this->session->remove('token');
			session_regenerate_id(true);
		}
		catch (Exception $e)
		{
			// TODO: Error message about not begin able to login
			//$logger->log("This is a message");
			//$logger->log($e->getTraceAsString());
			//$logger->log("line: " + $e->getLine());
			echo "this is a message";
			return $this->http->redirect('failingeneral');
		}

		$redirect_controller = $_GET['redirect_controller'];
		if ($redirect_controller)
		{
			$redirect_action = $_GET['redirect_action'];
			$redirect = $redirect_controller .
				($redirect_action == 'index' ? '' : '/' . $redirect_action);
			for ($i = 2; $i < count($_GET) - 3; ++$i)
				$redirect .= '/' . $_GET['param' . ($i - 2)];
			return $this->http->redirect($redirect);
		}

		return $this->http->redirect();
	}
}

?>
