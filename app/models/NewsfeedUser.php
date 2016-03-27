<?php
use Phalcon\DI;
use Phalcon\Mvc\Model;

class NewsfeedUser extends Model
{
	public $id;
	
	public $story_id;
	
	public $user_id;

	public function initialize()
	{
		$this->setSource("NewsfeedUsers");
	}
}
?>
