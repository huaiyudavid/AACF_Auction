<?php

use Facebook\PersistentData\PersistentDataInterface;

class FacebookDataHandler implements PersistentDataInterface
{
	const FB_PREFIX = 'FB_STATE';

	private $session;

	public function __construct($session)
	{
		$this->session = $session;
	}

	public function get($key)
	{
		if ($this->session->has(FacebookDataHandler::FB_PREFIX . $key))
		{
			return $this->session->get(FacebookDataHandler::FB_PREFIX . $key);
		}
		return null;
	}

	public function set($key, $value)
	{
		$this->session->set(FacebookDataHandler::FB_PREFIX . $key, $value);
	}
}

?>
