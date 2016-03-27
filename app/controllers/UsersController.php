<?php

class UsersController extends ControllerBase
{
	public function initialize()
	{
		$this->tag->setTitle('Profile');
		parent::initialize();
	}

	public function bidsAction()
	{
		$username = $this->dispatcher->getParam('username');
		//use this to know what user is getting viewed
		$this->view->username = $username;
		$user = User::find_by_username($username);

		if (!$user)
		{
			$this->dispatcher->forward(array(
				'controller' => 'error',
				'action' => 'not_found'));
			return;
		}

		$f_id = $user->fb_id;
		$f_name = $user->first_name;
		$l_name = $user->last_name;

		$bid_stats = Bid::get_bid_stats($f_id);

		$bid_count = $bid_stats[0]->num_bids;
		$item_stats = Item::get_item_stats($f_id);
		$items_count = $item_stats[0]->num_items;

		$this->view->highest_bid = $bid_stats[0]->max_bid == '' ? 0 :
			$bid_stats[0]->max_bid;

		$this->tag->setTitle($f_name . ' ' . $l_name);
		$this->view->search_query = $f_name . ' ' . $l_name;
		$this->view->user = $user;
		$this->view->first_name = $f_name;
		$this->view->last_name = $l_name;
		$this->view->num_bids = $bid_count;
		$this->view->num_items = $items_count;
		$this->view->prof_pic_url = $user->profile_large;
		$this->view->me = $username == $this->session->get('user')->username;

		$this->view->winning_bid_items = Bid::get_winning_bid_items($f_id);
		$this->view->other_bid_items = Bid::get_other_bid_items($f_id);
	}

	public function indexAction()
	{
	  //USER DETAILS
		$username = $this->dispatcher->getParam('username');
		//use this to know what user is getting viewed
		$this->view->username = $username;
		$user = User::find_by_username($username);
		
		if (!$user)
		{
			$this->dispatcher->forward(array(
				'controller' => 'error',
				'action' => 'not_found'));
			return;
		}
		
		$f_id = $user->fb_id;
		$f_name = $user->first_name;
		$l_name = $user->last_name;

		$bid_stats = Bid::get_bid_stats($f_id);

		$bid_count = $bid_stats[0]->num_bids;
		$item_stats = Item::get_item_stats($f_id);
		$items_count = $item_stats[0]->num_items;

		$this->view->highest_bid = $bid_stats[0]->max_bid == '' ? 0 :
			$bid_stats[0]->max_bid;

		$this->tag->setTitle($f_name . ' ' . $l_name);
		$this->view->search_query = $f_name . ' ' . $l_name;
		$this->view->user = $user;
		$this->view->first_name = $f_name;
		$this->view->last_name = $l_name;
		$this->view->num_bids = $bid_count;
		$this->view->num_items = $items_count;
		$this->view->prof_pic_url = $user->profile_large;
		$this->view->me = $username == $this->session->get('user')->username;

		//QUERY FOR ITEMS
		$user_item_page = Item::user_profile_search($f_id);

		$this->view->user_item_page = $user_item_page;
  }

	public function watchlistAction()
	{
		$username = $this->dispatcher->getParam('username');
		//use this to know what user is getting viewed
		$this->view->username = $username;
		$user = User::find_by_username($username);

		if (!$user)
		{
			$this->dispatcher->forward(array(
				'controller' => 'error',
				'action' => 'not_found'));
			return;
		}

		// Check if user is logged in
		if ($this->session->get('user')->username != $username)
		{
			$this->dispatcher->forward(array(
				'controller' => 'error',
				'action' => 'not_found'));
			return;
		}

		$f_id = $user->fb_id;
		$f_name = $user->first_name;
		$l_name = $user->last_name;

		$bid_stats = Bid::get_bid_stats($f_id);

		$bid_count = $bid_stats[0]->num_bids;
		$item_stats = Item::get_item_stats($f_id);
		$items_count = $item_stats[0]->num_items;

		$this->view->highest_bid = $bid_stats[0]->max_bid == '' ? 0 :
			$bid_stats[0]->max_bid;

		$this->tag->setTitle($f_name . ' ' . $l_name);
		$this->view->search_query = $f_name . ' ' . $l_name;
		$this->view->user = $user;
		$this->view->first_name = $f_name;
		$this->view->last_name = $l_name;
		$this->view->num_bids = $bid_count;
		$this->view->num_items = $items_count;
		$this->view->prof_pic_url = $user->profile_large;

		$this->view->watchlist = Watchlist::get_user_watchlist($f_id);
	}
}
?>
