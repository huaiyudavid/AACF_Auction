<?php

class LogoutController extends ControllerBase
{
	public function initialize()
	{
		$this->tag->setTitle('Log Out');
		parent::initialize();
	}

	public function indexAction()
	{
		if (!$this->request->isPost())
		{
			$this->dispatcher->forward(array(
				'controller' => 'error',
				'action' => 'not_found'));
			return;
		}

		$token = $this->request->getPost('dt');
		if ($token !== $this->session->get('token'))
			$this->http->redirect();

		$this->session->remove('facebook');
		$this->session->remove('user');
		$this->session->remove('profile_picture');
		$this->session->remove('token');
		session_regenerate_id(true);

		return $this->http->redirect();
	}
}

?>
