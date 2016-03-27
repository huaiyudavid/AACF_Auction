<?php
use Phalcon\DI;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Query;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class Newsfeed extends Model
{
	public $id;

	public $item_id;

	public $user_id;

	public $action_id;

	public $timestamp;

	public $action_type;

	public function initialize()
	{
		$this->setSource("Newsfeed");
	}

	private static function delete_old_stories($user_id, $action_id, $item_id)
	{
		foreach(Newsfeed::find(array(
			'user_id=:user_id: AND action_id=:action_id: AND item_id=:item_id:',
			"bind"=>array('user_id'=>$user_id, 'action_id'=>$action_id,
			'item_id'=>$item_id))) as $old_row)
		{
			$old_story_id = $old_row->id;
			foreach(NewsfeedUser::find(array("story_id=:old_story_id:",
				"bind"=>array("old_story_id"=>$old_story_id))) as $old_story_user)
			{
				if($old_story_user->delete() == false)
				{
					#well shit...this is the end
				}
			}
			if($old_row->delete()==false)
			{
				#well shit...probably not that big a deal if the other one worked
			}
		}
	}
	public static function get_comments($user_ids, $item_id, $timestamp)
	{
		$model = new Comment();
		//print_r($user_ids);
		$query = 'SELECT Users.profile_small AS user_image, Users.first_name, 
			Users.last_name, Users.username, 
			Comments.text AS text, UNIX_TIMESTAMP(Comments.timestamp) AS timestamp FROM Comments LEFT JOIN Users
			ON Comments.user_id = Users.fb_id WHERE Comments.item_id = :item_id
			AND Comments.user_id IN (' . $user_ids . ') AND Comments.timestamp > :timestamp ORDER BY timestamp ASC';

		$comments = new stdClass();
//		$comments->comments = new Resultset(null,$model, 
//			       	$model->getReadConnection()->query($query, array('item_id'=>$item_id, 'user_ids'=>implode(', ',$user_ids), 'timestamp'=>$timestamp)));
		$comments->comments = new Resultset(null,$model, 
			       	$model->getReadConnection()->query($query, array('item_id'=>$item_id, 'timestamp'=>$timestamp)));
//		print_r($comments->comments);
		//echo "number of comments: " . count($comments->comments);
		$comments_array = array();
		//print_r($comments->comments) . "\n";
		foreach($comments->comments as $comment_obj)
		{
			$tmp = new stdClass();
			$tmp -> username = $comment_obj -> username;
			$tmp -> first_name = $comment_obj -> first_name;
			$tmp -> last_name = $comment_obj -> last_name;
			$tmp -> timestamp = $comment_obj -> timestamp;
			$tmp -> text = $comment_obj -> text;
			$tmp -> user_image = $comment_obj -> user_image;
			$comments_array[] = $tmp;
		}
		return $comments_array;
	}

	public static function get_bids($user_ids, $item_id, $timestamp)
	{
		$model = new Bid();
		$query = 'SELECT Users.profile_small AS user_image, Users.first_name, Users.last_name, Users.username, 
			Bids.amount, UNIX_TIMESTAMP(Bids.timestamp) AS timestamp FROM Bids LEFT JOIN Users
			ON Bids.user_id = Users.fb_id WHERE Bids.item_id = :item_id
			AND Bids.user_id IN ('.$user_ids.') AND Bids.timestamp > :timestamp ORDER BY timestamp DESC';

		$bids = new stdClass();
		$bids->bids = new Resultset(null,$model, 
			       	$model->getReadConnection()->query($query, array('item_id'=>$item_id, 'timestamp'=>$timestamp)));
		return $bids->bids;
	}
	public static function get_new_stories($user_id, $timestamp, $model )
	{
		$query = 'SELECT SQL_CALC_FOUND_ROWS Actions.user_id as action_user_id,
		       	Items.user_id AS item_user_id, Items.starting_price AS
			item_starting_price, UNIX_TIMESTAMP(Actions.timestamp) as timestamp,
			Actions.timestamp as date_time,
		       	Items.name AS item_name, Actions.action_id as action_id, 
			ActionTypes.action_type as action_type, Items.category_id
			AS category_id, Items.id AS item_id, Items.name AS
		       	item_name, Items.filename AS item_image, ItemUser.first_name AS item_user_first,
		       	ItemUser.last_name AS item_user_last, ItemUser.username
			AS item_creator_username, ActionUser.first_name
			AS action_user_first, ActionUser.last_name AS
		       	action_user_last, ActionUser.username AS action_username,
		       	MaxBids.max_amount AS max_amount, 
			MaxBids.user_id AS max_bidder, MaxBids.num_bids AS 
			num_bids, CategoryAffinities.affinity_score as cat_affinity,
		       	UserAffinities.affinity_score as user_affinity FROM
			Actions LEFT JOIN ActionTypes ON Actions.action_id = ActionTypes.id
		       	LEFT JOIN Items ON ActionTypes.item_id = Items.id LEFT JOIN 
			CategoryAffinities ON Items.category_id = CategoryAffinities.category_id
		       	AND CategoryAffinities.user_id = :user_id LEFT JOIN 
			UserAffinities ON UserAffinities.user_id = :user_id
		       	AND UserAffinities.friend_id = Actions.user_id LEFT JOIN
		       	(SELECT Bids.item_id, Bids.user_id, MAX(Bids.amount) AS
			 max_amount, COUNT(*) AS num_bids FROM Bids GROUP BY 
			Bids.item_id) MaxBids ON MaxBids.item_id = Items.id
		       	LEFT JOIN Users AS ItemUser ON ItemUser.fb_id = Items.user_id 
			LEFT JOIN Users AS ActionUser ON ActionUser.fb_id = Actions.user_id
			WHERE Actions.timestamp > :timestamp AND Actions.user_id != :user_id ORDER BY item_id, action_type, user_affinity DESC';
		$new_stories = new stdClass();
		$new_stories->stories = new Resultset(null,$model, 
			       	$model->getReadConnection()->query($query, array('user_id'=>$user_id, 'user_id'=>$user_id, 'timestamp'=>$timestamp, 'user_id'=>$user_id)));
		$actiontype = $item_name = $item_creator_first_name = $item_creator_last_name = $item_creator_username = "";
		$item_image;
		$new_timestamp;
		$item_id = $max_bid = $max_bidder = $num_bids =  $action_id = $rank=$item_user_id = $item_starting_price =  0;
		$users = array();
		$user_ids = array();
		$stories = array();
		foreach($new_stories->stories as $new_story)
		{
			//if the next item we're seeing is new
			//this if statement is probably true the first time around but the second will fail so nbd
			if($new_story->action_type != $actiontype || $item_id != $new_story->item_id)
			{
				if(count($users) > 0)
				{
					Newsfeed::delete_old_stories($user_id, $action_id, $item_id);
					$story = new stdClass();
					if($actiontype == 'comment')
					{
						$string_userids = implode(',', array_values($user_ids));
						$story->comments= Newsfeed::get_comments($string_userids, $item_id, $timestamp);
					}
					else if($actiontype == 'bid')
					{
						$string_userids = implode(',',array_values($user_ids));
						$story->bids = Newsfeed::get_bids($string_userids, $item_id, $timestamp);
					}
					
					$story->item_id = $item_id;
					$story->item_user_id = $item_user_id;
					$story->user_id = $user_id;
					$story->timestamp = $new_timestamp;
					$story->action_type=  $actiontype;
					$story->action_id = $action_id;
					$story->friend_names = $users;
					$story->rank = $rank;
				
					$story->max_bid = $max_bid;
					$story->max_bidder = $max_bidder;
					$story->num_bids = $num_bids;
					$story->item_name = $item_name;
					$story->item_creator_username = $item_creator_username;

					$story->item_creator_first_name = $item_creator_first_name;
					$story->item_creator_last_name = $item_creator_last_name;
					$story->item_starting_price = $item_starting_price;
					$story->item_image = $item_image;
					
					$story->action_user_ids = $user_ids;
					$stories[] = $story;
					unset($users);
					$users = array();
					$rank = 0;
				}
				$actiontype = $new_story->action_type;
				$item_id = $new_story->item_id;
				$item_user_id = $new_story->item_user_id;
				$max_bid = $new_story->max_amount;
				$max_bidder = $new_story->max_bidder;
				$num_bids = $new_story->num_bids;
				$item_name = $new_story->item_name;
				$item_creator_username = $new_story->item_creator_username;
				$item_creator_first_name = $new_story->item_user_first;
				$item_creator_last_name = $new_story->item_user_last;
				$item_starting_price = $new_story->item_starting_price;
			}
			$new_timestamp = $timestamp;
			$action_id = $new_story->action_id;
			$item_image = $new_story->item_image;	
			$old_rank = $rank;
			$rank = $rank+5*(is_null($new_story->user_affinity) ? 0 : $new_story->user_affinity) + 3*(is_null($new_story->cat_affinity) ? 0 : $new_story->cat_affinity);
			if($rank-$old_rank > 0)
			{
				if(!array_key_exists($new_story->action_username, $users) && !is_null($new_story->user_affinity) && $new_story->user_affinity > 5)
				{
					$users[$new_story->action_username] = $new_story->action_user_first . " " . $new_story->action_user_last;
					$user_ids[$new_story->action_username] = $new_story->action_user_id;
				}
			}
		}
		//checks the last story
		if(count($users) > 0)
		{
			Newsfeed::delete_old_stories($user_id, $action_id, $item_id);
				
			$story = new stdClass();
			if($actiontype == 'comment')
			{
				$string_userids = implode(',', array_values($user_ids));
				$comments_array = Newsfeed::get_comments($string_userids, $item_id, $timestamp);
				$story->comments = $comments_array;
			}
			else if($actiontype == 'bid')
			{
				$string_userids = implode(',',array_values($user_ids));
				$story->bids = Newsfeed::get_bids($string_userids, $item_id, $timestamp);
			}
			$story->item_id = $item_id;
			$story->user_id = $user_id;
			$story->timestamp = $new_timestamp;
			$story->action_type=  $actiontype;
			$story->action_id = $action_id;
			$story->friend_names = $users;
			$story->rank = $rank;
			$story->max_bid = $max_bid;
			$story->max_bidder = $max_bidder;
			$story->num_bids = $num_bids;
			$story->item_name = $item_name;
			$story->item_user_id = $item_user_id;
			$story->item_creator_first_name = $item_creator_first_name;
			$story->item_creator_last_name = $item_creator_last_name;
			$story->item_starting_price = $item_starting_price;
			$story->item_creator_username = $item_creator_username;	
			$story->item_image = $item_image;
			$story->action_user_ids = $user_ids;
			$stories[] = $story;
		}
		//sorts low to high
		usort($stories, function($a, $b)
			{	
				if($a->rank == $b->rank) return 0;
				return ($a->rank <  $b->rank) ? 1:-1;
			});
		
		foreach($stories as $key=>$new_story)
		{
			$newsfeed = new Newsfeed();
			$newsfeed->item_id = $new_story->item_id;
			$newsfeed->user_id = $user_id;
			$newsfeed->action_id = $new_story->action_id;
			$newsfeed->timestamp = $new_story->timestamp;
			$newsfeed->action_type = $new_story->action_type;
			$newsfeed->save();
			$id = $newsfeed->id;
			$stories[$key]->id = $id;
			foreach($new_story->friend_names as $key=>$user)
			{
				$newsfeed_user = new NewsfeedUser();
				$newsfeed_user->story_id = $id;
				$newsfeed_user->user_id = $new_story->action_user_ids[$key];
				$newsfeed_user->save();
			}
		}
		return $stories;
	}
	public static function get_old_stories($user_id, $timestamp, $num_stories, $model)
	{
		$query = 'SELECT Newsfeed.id as id, Newsfeed.item_id as item_id,
		       	 Newsfeed.timestamp as timestamp, 
			MaxBids.max_amount AS max_bid, MaxBids.num_bids AS num_bids,
			Items.name AS item_name, Items.starting_price AS item_starting_price, Items.filename AS item_image,
			Users.first_name AS item_creator_first_name, Users.last_name 
			AS item_creator_last_name, Users.username AS item_creator_username,
		      	user_info.action_users AS action_users, user_info.user_names
			AS action_user_names, user_info.user_ids AS action_user_ids, 
			ActionTypes.action_type AS action_type
			FROM Newsfeed LEFT JOIN (SELECT DISTINCT Newsfeed.id 
			FROM Newsfeed LIMIT :num_stories) a ON Newsfeed.id = a.id 
		       	LEFT JOIN (SELECT Bids.item_id, Bids.user_id, MAX(Bids.amount) AS
			 max_amount, COUNT(*) AS num_bids FROM Bids GROUP BY 
			Bids.item_id) MaxBids ON MaxBids.item_id = Newsfeed.item_id
			LEFT JOIN Items ON Items.id = Newsfeed.item_id LEFT JOIN
		       	Users ON Items.user_id = Users.fb_id LEFT JOIN (SELECT
			Newsfeed.id AS story_id, GROUP_CONCAT(CONCAT_WS(" ", Users.first_name,
			Users.last_name) ORDER BY NewsfeedUsers.id) AS action_users, 
			GROUP_CONCAT(Users.username ORDER BY Newsfeed.id) AS user_names, GROUP_CONCAT(Users.fb_id ORDER BY NewsfeedUsers.id) AS user_ids
		       	FROM Newsfeed LEFT JOIN NewsfeedUsers ON Newsfeed.id = NewsfeedUsers.story_id
			LEFT JOIN Users On NewsfeedUsers.user_id = Users.fb_id GROUP BY Newsfeed.id) user_info
			ON user_info.story_id = Newsfeed.id LEFT JOIN ActionTypes ON Newsfeed.action_id = ActionTypes.id WHERE Newsfeed.user_id = :user_id
		       	AND Newsfeed.timestamp < :timestamp ORDER BY id DESC';
		$old_newsfeed_stories = new stdClass();
		$old_newsfeed_stories->stories = new Resultset(null,$model, 
			       	$model->getReadConnection()->query($query, array('num_stories'=>$num_stories,'timestamp'=>$timestamp, 'user_id'=>$user_id), array('num_stories' => \Phalcon\Db\Column::BIND_PARAM_INT, 'user_id'=>\Phalcon\Db\Column::BIND_PARAM_INT)));
		$old_stories = array();
		//frontend expects key val pair where key is the username
		//val is the user's name so this is needed
		$query_results = $old_newsfeed_stories->stories;
		foreach($query_results as $old_story)
		{
			if($old_story->action_type == 'comment')
			{
				//echo $old_story->timestamp . " ";
				$comments = Newsfeed::get_comments($old_story->action_user_ids, $old_story->item_id, $old_story->timestamp);
				$old_story->comments = 0;
				$old_story->comments = $comments;
				
			}
			if($old_story->action_type == 'bid')
			{
				$old_story->bids = 0;
				$old_story->bids = Newsfeed::get_bids($old_story->action_user_ids, $old_story->item_id, $old_story->timestamp);
			}
			$friend_names_dict = array();
			$friend_names = 0;
			$user_names = 0;
			$friend_names=  explode(",", $old_story->action_users);
			$user_names = explode(",", $old_story->action_user_names);
			$user_names_reverse = array_reverse($user_names);
			$i = 0;
			foreach($user_names_reverse as $user_names)
			{
				$friend_names_dict[$user_names] = $friend_names[$i++];
			}
			//DON'T DELETE THE NEXT LINE
			$old_story->friend_names = 0;
			$old_story->friend_names = $friend_names_dict;
			$old_stories[] = $old_story;
		}
		return $old_stories;
	}
}
?>
