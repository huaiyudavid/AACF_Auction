<?php

use Phalcon\Mvc\Controller;

class ControllerBase extends Controller
{
	protected function initialize()
	{
		if ($this->session->get('user'))
			$this->view->setTemplateAfter('user_main');
		else
			$this->view->setTemplateAfter('guest_main');

		if (!$this->session->has('token'))
		{
			$token = substr(hash_hmac('sha256', openssl_random_pseudo_bytes(32),
				openssl_random_pseudo_bytes(16)), 0, 20);
			$this->session->set('token', $token);
			$this->cookies->set('a', $token, 0, '/auction/', false, null, false);
		}
	}
}

?>
