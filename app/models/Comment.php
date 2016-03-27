<?php

use Phalcon\DI;
use Phalcon\Mvc\Model;

class Comment extends Model
{
	public $id;

	public $text;

	public $timestamp;

	public $item_id;

	public $user_id;

	public function initialize()
	{
		$this->setSource("Comments");
	}

	public static function get_from_item($item_id, $after)
	{
		$builder = DI::getDefault()->get('modelsManager')->createBuilder()->
			from('Comment')->columns(array('Comment.text',
			'UNIX_TIMESTAMP(Comment.timestamp) as timestamp', 'User.first_name',
			'User.last_name', 'User.username', 'User.profile_small as user_image'))->
			where('Comment.item_id = :item_id: AND
			Comment.timestamp > FROM_UNIXTIME(:time:)', array('item_id' => $item_id,
			'time' => $after))->leftjoin('User', 'Comment.user_id = User.fb_id')->
			orderBy('Comment.timestamp ASC');
		return $builder->getQuery()->execute();
	}
}
