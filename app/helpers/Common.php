<?php

use Facebook\Facebook;

use Phalcon\DI;

class Common
{
	const IMAGE_FOLDER      = '/var/www/html/auction/public/images/items/';
	const ITEMS_PER_PAGE    = 10;
	const MAX_NOTIFICATIONS = 30;
	const IMAGE_SIZE        = 800;

	public static function get_facebook()
	{
		return new Facebook([
			'app_id' => DI::getDefault()->get('config')->application->fbAppID,
			'app_secret' => DI::getDefault()->get('config')->application->fbAppSecret,
			'default_graph_version' => 'v2.2',
			'persistent_data_handler' =>
				new FacebookDataHandler(DI::getDefault()->get('session'))
		]);
	}

	public static function parse_search($search)
	{
		static $exclude = array('-', '+', '<', '>', '*', '"', '@', '(', ')', '~');
		$search_term = str_replace($exclude, ' ', $search);
		$search_array = explode(' ', $search_term);
		$final_search = '';
		foreach ($search_array as $value)
		{
			if (empty($value))
				continue;

			$final_search .= ' ' . $value . '* >' . $value;
		}
		return $final_search;
	}

	public static function fill_newsfeed($user_id, $tot_stories)
	{
		$logged_in_user = User::findFirst(array('fb_id = :user_id:',
			'bind' => array('user_id' => DI::getDefault()->get('session')->
			get('user')->fb_id)));
		$timestamp = $logged_in_user->last_newsfeed;
		$new_stories = Newsfeed::get_new_stories($user_id, $timestamp, new Newsfeed());
		$old_stories = Newsfeed::get_old_stories($user_id, $timestamp, ($tot_stories - count($new_stories)), new Newsfeed());

		$current_timestamp = time();
		$update_user = 'UPDATE Users SET last_newsfeed = FROM_UNIXTIME(:time)
			WHERE fb_id = :user_id';
		$write_model = new User();
		$write_model->getWriteConnection()->execute($update_user,
			array(':time' => $current_timestamp,
			':user_id' => DI::getDefault()->get('session')->get('user')->fb_id));

		return array_merge($new_stories,$old_stories);
	}
	public static function make_recommendation($user_id)
	{
		$vals = Item::recommend($user_id,3, new Item());
		return $vals;
	}
}

?>
