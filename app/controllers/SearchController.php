<?php

class SearchController extends ControllerBase
{
	public function indexAction()
	{
		$query = $this->dispatcher->getParam('query', 'trim', '');
		$sort = $this->dispatcher->getParam('sort', 'trim', 'relevance');
		$filter = $this->dispatcher->getParam('filter', 'trim', 'none');
		$page = $this->dispatcher->getParam('page', 'trim', 1);
		
		$item_page = Item::search($query, $sort, $filter, $page);
		$user_search = User::search($query);
		$categories = Category::find();

		if (!$item_page)
		{
			$this->dispatcher->forward(array(
				'controller' => 'error',
				'action' => 'not_found'));
			return;
		}

		$this->tag->setTitle('Search - ' . $query);
		$this->view->search_query = $query;
		$this->view->sort_by = $sort;
		$this->view->filter_by = $filter;

		$this->view->has_entries =
			count($item_page->items) + count($user_search) != 0;
		$this->view->item_page = $item_page;
		$this->view->categories = $categories;
		$this->view->user_page = $user_search;
	}
}

?>
