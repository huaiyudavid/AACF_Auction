<?php

class SignupController extends ControllerBase
{
	public function initialize()
	{
		$this->tag->setTitle('Sign Up');
		parent::initialize();
	}

	public function indexAction()
	{
		// Check if the user is in the right state
		$fb_session = $this->session->get('facebook');
		$fb_user = $this->session->get('fb_user');
		if (!$fb_session || !$fb_user)
			return $this->http->redirect();

		$this->view->script = $this->config->application->website . 'js/signup.js';
		$this->view->first_name = $fb_user->first_name;
		$this->view->last_name = $fb_user->last_name;
		$this->view->email = $fb_user->email;
	}
}

?>
