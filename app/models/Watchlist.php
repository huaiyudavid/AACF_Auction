<?php

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class Watchlist extends Model
{
	public $id;
	
	public $item_id;

	public $user_id;

	public $is_user_created;

	public function initialize()
	{
		$this->setSource("Watchlist");
	}

	public function get_user_watchlist($user_id)
	{
		$query = 'SELECT Items.id as item_id, Items.name as item_name,
			Items.starting_price as item_starting_price, Items.filename as item_image,
			Users.first_name as item_creator_first_name,
			Users.last_name as item_creator_last_name, max_bid, num_bids,
			Users.username as item_creator_username
			FROM Watchlist
			LEFT JOIN Items ON Watchlist.item_id = Items.id
			LEFT JOIN Users ON Users.fb_id = Items.user_id
			LEFT JOIN (SELECT Bids.item_id, MAX(Bids.amount) as max_bid,
			COUNT(*) as num_bids FROM Bids GROUP BY Bids.item_id) tmp
			ON tmp.item_id = Items.id WHERE Watchlist.user_id = :user_id AND
			Watchlist.is_user_created = 1';

			$model = new Watchlist();

			return new Resultset(null, $model, $model->getReadConnection()->
				query($query, array(':user_id' => $user_id)));
	}
}

?>
