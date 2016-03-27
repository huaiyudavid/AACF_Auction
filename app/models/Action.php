<?php
use Phalcon\DI;
use Phalcon\Mvc\Model;

class Action extends Model
{
	public $id;
	
	public $user_id;
	
	public $action_id;

	public $timestamp;

	public function initialize()
	{
		$this->setSource("Actions");
	}	
}
?>
