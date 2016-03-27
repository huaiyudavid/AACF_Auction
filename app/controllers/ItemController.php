<?php

class ItemController extends ControllerBase
{
	public function initialize()
	{
		$this->tag->setTitle('Item');
		parent::initialize();
	}

	public function indexAction()
	{
		$item_id = $this->dispatcher->getParam('id');
		$item = Item::get_details($item_id);

		if (!$item)
		{
			$this->dispatcher->forward(array(
				'controller' => 'error',
				'action' => 'not_found'));
			return;
		}

		$bids = Bid::get_all($item_id, 0);
		$num_bids = count($bids);

		$logged_in_user_id = $this->session->get('user')->fb_id;
		$watchlist_item = Watchlist::findFirst(array(
			'item_id = :item_id: AND user_id = :user_id: AND is_user_created = 1',
			'bind' => array('item_id' => $item_id, 'user_id' => $logged_in_user_id)));
		$is_on_watchlist = ($watchlist_item != null);

		$comments = Comment::get_from_item($item_id, 0);
		$num_comments = count($comments);

		$this->tag->setTitle($item->name);
		$this->view->search_query = $item->name;
		$this->view->item = $item;
		$this->view->current_price = count($bids) > 0 ?
			$bids->getFirst()->amount : $item->starting_price;
		$this->view->bid_count = $num_bids;
		$this->view->logged_in_user_id = $this->session->get('user')->fb_id;
		$this->view->min_bid = count($bids) > 0 ?
			$bids->getFirst()->amount + $item->increment : $item->starting_price;

		$this->view->bids = $bids;

		$this->view->is_on_watchlist = $is_on_watchlist;
		
		$this->view->comments = $comments;
		$this->view->num_comments = $num_comments;
		$this->view->logged_in_user_image =
			$this->session->get('user')->profile_small;
	}
}

?>
