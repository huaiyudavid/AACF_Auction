<?php

use Phalcon\DI;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class Item extends Model
{
	public $id;

	public $name;

	public $description;

	public $user_id;
	
	public $created;

	public $starting_price;

	public $increment;

	public $category_id;

	public $filename;

	public function initialize()
	{
		$this->setSource("Items");
	}

	public static function find_by_id($item_id)
	{
		return parent::findFirst(array(
			'id = :id:',
			'bind' => array('id' => $item_id)));
	}

	public static function get_count()
	{
		return parent::count();
	}

	public static function get_all($sort, $filter, $page)
	{
		$category = null;
		if ($filter != 'none')
		{
			$category = Category::findFirst(array('category = :category_id:',
				'bind' => array('category_id' => $filter)));
			if (!$category)
				return null;
		}

		$query = 'SELECT SQL_CALC_FOUND_ROWS Items.id as item_id,
			Items.name as item_name, Items.starting_price as item_starting_price,
			Items.filename as item_image, Users.first_name as item_creator_first_name,
			Users.last_name as item_creator_last_name, max_bid, num_bids,
			Users.username as item_creator_username FROM Items
			LEFT JOIN Users ON Users.fb_id = Items.user_id
			LEFT JOIN (SELECT Bids.item_id, MAX(Bids.amount) as max_bid,
			COUNT(*) as num_bids FROM Bids GROUP BY Bids.item_id) tmp
			ON tmp.item_id = Items.id' . ($category ?
			' WHERE Items.category_id = :category_id' : '');

		switch ($sort)
		{
		case 'newest':
			$query .= ' ORDER BY item_id DESC';
			break;
		case 'oldest':
			$query .= ' ORDER BY item_id ASC';
			break;
		case 'highest-price':
			$query .= ' ORDER BY max_bid DESC';
			break;
		case 'lowest-price':
			$query .= ' ORDER BY max_bid ASC';
			break;
		default:
			return null;
		}

		$params = array();
		if ($category)
			$params[':category_id'] = $category->id;

		$paginator = new Paginator(array(
			'query' => $query,
			'params' => $params,
			'limit' => Common::ITEMS_PER_PAGE,
			'page' => $page));
		return $paginator->getPaginate(new Item());
	}

	public static function get_details($id)
	{
		$builder = DI::getDefault()->get('modelsManager')->createBuilder()->
			from('Item')->columns(array('Item.id as id', 'Item.name as name',
			'Item.description as description', 'Item.filename as filename',
			'Item.starting_price as starting_price', 'Item.increment as increment',
			'Item.user_id as creator_id', 'Item.created as created',
			'User.first_name as first_name', 'User.last_name as last_name',
			'User.username as username'))->where('Item.id = :id:',
			array('id' => $id))->leftjoin('User', 'Item.user_id = User.fb_id');
		return $builder->getQuery()->getSingleResult();
	}

	public static function get_item_stats($user_id)
	{
		$builder = DI::getDefault()->get('modelsManager')->createBuilder()->
			from('Item')->columns(array('COUNT(*) as num_items'))->
			where('user_id = :user_id:', array('user_id' => $user_id));
		return $builder->getQuery()->execute();
	}
	
	public static function user_profile_search($user_id)
	{
		$query = 'SELECT SQL_CALC_FOUND_ROWS Items.id as item_id,
			Items.name as item_name, Items.starting_price as item_starting_price,
			Items.filename as item_image, Users.first_name as item_creator_first_name,
			Users.last_name as item_creator_last_name, max_bid, num_bids,
			Users.username as item_creator_username
			FROM Items
			LEFT JOIN Users ON Users.fb_id = Items.user_id
			LEFT JOIN (SELECT Bids.item_id, MAX(Bids.amount) as max_bid,
			COUNT(*) as num_bids FROM Bids GROUP BY Bids.item_id) tmp
			ON tmp.item_id = Items.id WHERE Items.user_id = :user_id';
			
			$model = new Item();

			return new Resultset(null, $model, $model->getReadConnection()->
				query($query, array(':user_id' => $user_id)));
	}

	public static function search($search, $sort, $filter, $page)
	{
		$category = null;
		if ($filter != 'none')
		{
			$category = Category::findFirst(array('category = :category_id:',
				'bind' => array('category_id' => $filter)));
			if (!$category)
				return null;
		}

		$query = 'SELECT SQL_CALC_FOUND_ROWS Items.id as item_id,
			Items.name as item_name, Items.starting_price as item_starting_price,
			Items.filename as item_image, Users.first_name as item_creator_first_name,
			Users.last_name as item_creator_last_name, max_bid, num_bids,
			Users.username as item_creator_username,
			MATCH (Items.name, Items.description) AGAINST
			(:searchOne IN BOOLEAN MODE) AS score FROM Items
			LEFT JOIN Users ON Users.fb_id = Items.user_id
			LEFT JOIN (SELECT Bids.item_id, MAX(Bids.amount) as max_bid,
			COUNT(*) as num_bids FROM Bids GROUP BY Bids.item_id) tmp
			ON tmp.item_id = Items.id WHERE MATCH (Items.name, Items.description)
			AGAINST (:searchTwo IN BOOLEAN MODE)' . ($category ?
			' AND Items.category_id = :category_id' : '');

		switch ($sort)
		{
		case 'relevance':
			$query .= ' ORDER BY score DESC';
			break;
		case 'newest':
			$query .= ' ORDER BY item_id DESC';
			break;
		case 'oldest':
			$query .= ' ORDER BY item_id ASC';
			break;
		case 'highest-price':
			$query .= ' ORDER BY max_bid DESC';
			break;
		case 'lowest-price':
			$query .= ' ORDER BY max_bid ASC';
			break;
		default:
			return null;
		}

		$search_term = Common::parse_search($search);
		$params = array(
			':searchOne' => $search_term,
			':searchTwo' => $search_term);
		if ($category)
			$params[':category_id'] = $category->id;

		$paginator = new Paginator(array(
			'query' => $query,
			'params' => $params,
			'limit' => Common::ITEMS_PER_PAGE,
			'page' => $page));
		return $paginator->getPaginate(new Item());
	}

	public static function recommend($user_id,$num_items,$model)
	{
		$threshold = 1;
		$query = 'SELECT DISTINCT Items.id as item_id, Items.name AS item_name, 
			Items.filename AS item_image, Items.category_id AS item_category,
		       	Users.first_name, Users.last_name, Users.username,  
			MaxBids.amount as max_bid FROM Items LEFT JOIN Users ON
			Items.user_id = Users.fb_id INNER JOIN CategoryAffinities ON 
			Items.category_id = CategoryAffinities.category_id LEFT JOIN 
			(SELECT max_bid.* FROM Bids max_bid LEFT JOIN Bids filter ON 
			max_bid.item_id = filter.item_id AND max_bid.amount < filter.amount 
			WHERE filter.amount iS NULL) MaxBids On Items.id = MaxBids.item_id 
			LEFT JOIN (SELECT Items.category_id AS category_id, MAX(Bids.amount) AS max_per_category 
			FROM Items INNER JOIN Bids ON Bids.item_id = Items.id 
			WHERE Bids.user_id = :user_id GROUP BY Items.category_id) 
			MaxUserBid ON Items.category_id = MaxUserBid.category_id
		       	WHERE CategoryAffinities.affinity_score  > :threshold  AND 
			CategoryAffinities.user_id = :user_id AND 
			MaxBids.user_id != :user_id AND Items.user_id != :user_id AND 
			(MaxBids.amount IS NULL OR MaxBids.amount < MaxUserBid.max_per_category) ORDER BY RAND() LIMIT :num_items';
		$interesting_items = new stdClass();
		$interesting_items->items = new Resultset(null, $model, $model->getReadConnection()->query($query, array('user_id'=>$user_id, 'threshold'=>$threshold, 'user_id'=>$user_id, 'user_id'=>$user_id, 'user_id'=>$user_id, 'num_items'=>$num_items), array('user_id'=>\Phalcon\Db\Column::BIND_PARAM_INT, 'user_id'=>\Phalcon\Db\Column::BIND_PARAM_INT,'user_id'=>\Phalcon\Db\Column::BIND_PARAM_INT,'num_items'=>\Phalcon\Db\Column::BIND_PARAM_INT)));
		return $interesting_items->items;
	}

}