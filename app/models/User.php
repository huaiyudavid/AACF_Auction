<?php

use Phalcon\DI;
use Phalcon\Mvc\Model;

class User extends Model
{
	public $fb_id;

	public $first_name;

	public $last_name;

	public $username;

	public $email;

	public $profile_small;

	public $profile_large;

	public $last_notification;

	public function initialize()
	{
		$this->setSource("Users");
	}

	public static function find_by_username($username)
	{
		$user = parent::findFirst("username='" . $username . "'");
		return $user;
	}

	public static function find_by_id($user_id)
	{
		$user = parent::findFirst("fb_id='" . $user_id . "'");
		return $user;
	}

	public function search($term)
	{
		$term_entry = $term . '%';
		$term_both = '%' . $term_entry;
		$builder = DI::getDefault()->get('modelsManager')->createBuilder()->
			from('User')->columns(array('User.first_name as first_name',
			'User.last_name as last_name', 'User.username as username',
			'User.profile_large as user_image'))->
			where('User.first_name LIKE :term_one: OR User.last_name LIKE :term_two:
			OR CONCAT_WS(\' \', User.first_name, User.last_name) LIKE :term_three:',
			array('term_one' => $term_entry, 'term_two' => $term_entry,
			'term_three' => $term_both));
		return $builder->getQuery()->execute();
	}
}

?>
