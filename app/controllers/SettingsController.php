<?php

class SettingsController extends ControllerBase
{
	public function initialize()
	{
		$this->tag->setTitle('Settings');
		parent::initialize();
	}

	public function indexAction()
	{			
		$user = $this->session->get('user');

		$this->view->first_name = $user->first_name;
		$this->view->last_name = $user->last_name;
		$this->view->username = $user->username;
		$this->view->email = $user->email;
	}
}

?>
