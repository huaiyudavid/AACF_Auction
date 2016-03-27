<?php
use Phalcon\DI;
use Phalcon\Mvc\Model;

class UserAffinity extends Model
{
	public $id;
	public $user_id;
	public $friend_id;
	public $affinity_score;

	public function initialize()
	{
		$this->setSource("UserAffinities");
	}
}
