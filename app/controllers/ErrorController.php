<?php

class ErrorController extends ControllerBase
{
	public function initialize()
	{
		parent::initialize();
	}

	public function not_foundAction()
	{
		$this->tag->setTitle('Not Found');
	}
}

?>
