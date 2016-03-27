<?php

use Phalcon\DI;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class Bid extends Model
{
	public $id;

	public $user_id;

	public $item_id;

	public $timestamp;

	public $amount;

	public function initialize()
	{
		$this->setSource("Bids");
	}

	public static function get_all($item_id, $after)
	{
		$builder = DI::getDefault()->get('modelsManager')->createBuilder()->
			from('Bid')->columns(array('UNIX_TIMESTAMP(Bid.timestamp) as timestamp',
			'Bid.amount as amount', 'User.first_name as first_name',
			'User.last_name as last_name', 'User.username as username'))->
			where('Bid.item_id = :item_id: AND Bid.timestamp > FROM_UNIXTIME(:time:)',
			array('item_id' => $item_id, 'time' => $after))->
			leftjoin('User', 'Bid.user_id = User.fb_id')->
			orderBy('Bid.timestamp DESC');
		return $builder->getQuery()->execute();
	}

	public static function get_other_bid_items($user_id)
	{
		$query = 'SELECT Items.id as item_id, Items.name as item_name,
			Items.starting_price as item_starting_price, Items.filename as item_image,
			MAX(Bids.amount) as user_bid,
			Users.first_name as item_creator_first_name,
			Users.last_name as item_creator_last_name, max_bid, num_bids,
			Users.username as item_creator_username
			FROM Bids
			LEFT JOIN Items ON Bids.item_id = Items.id
			LEFT JOIN Users ON Users.fb_id = Items.user_id
			LEFT JOIN (SELECT Bids.item_id, MAX(Bids.amount) as max_bid,
			COUNT(*) as num_bids FROM Bids GROUP BY Bids.item_id) tmp
			ON tmp.item_id = Items.id WHERE Bids.user_id = :user_id
			GROUP BY Bids.item_id HAVING user_bid != max_bid';

			$model = new Bid();

			return new Resultset(null, $model, $model->getReadConnection()->
				query($query, array(':user_id' => $user_id)));
	}

	public static function get_winning_bid_items($user_id)
	{
		$query = 'SELECT Items.id as item_id, Items.name as item_name,
			Items.starting_price as item_starting_price, Items.filename as item_image,
			MAX(Bids.amount) as user_bid,
			Users.first_name as item_creator_first_name,
			Users.last_name as item_creator_last_name, max_bid, num_bids,
			Users.username as item_creator_username
			FROM Bids
			LEFT JOIN Items ON Bids.item_id = Items.id
			LEFT JOIN Users ON Users.fb_id = Items.user_id
			LEFT JOIN (SELECT Bids.item_id, MAX(Bids.amount) as max_bid,
			COUNT(*) as num_bids FROM Bids GROUP BY Bids.item_id) tmp
			ON tmp.item_id = Items.id WHERE Bids.user_id = :user_id
			GROUP BY Bids.item_id HAVING user_bid = max_bid';

			$model = new Bid();

			return new Resultset(null, $model, $model->getReadConnection()->
				query($query, array(':user_id' => $user_id)));
	}

	public static function get_bid_stats($user_id)
	{
		$builder = DI::getDefault()->get('modelsManager')->createBuilder()->
			from('Bid')->columns(array('MAX(amount) as max_bid',
			'COUNT(*) as num_bids'))->where('user_id = :user_id:',
			array('user_id' => $user_id));
		return $builder->getQuery()->execute();
	}
}

?>
