<?php

use Phalcon\Mvc\Model;

class NotificationUser extends Model
{
	public $id;

	public $notification_id;

	public $user_id;

	public function initialize()
	{
		$this->setSource("NotificationUsers");
	}

	public static function create_notification_user($notification_id, $user_id)
	{
		$notification_user = new NotificationUser();
		$notification_user->notification_id = $notification_id;
		$notification_user->user_id = $user_id;
		return $notification_user;
	}
}
