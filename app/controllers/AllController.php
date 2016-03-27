<?php

class AllController extends ControllerBase
{
	public function initialize()
	{
		$this->tag->setTitle('All Items');
		parent::initialize();
	}

	public function indexAction()
	{
		$sort = $this->dispatcher->getParam('sort', 'trim', 'newest');
		$filter = $this->dispatcher->getParam('filter', 'trim', 'none');
		$page = $this->dispatcher->getParam('page', 'trim', 1);

		$item_page = Item::get_all($sort, $filter, $page);
		$categories = Category::find();

		if (!$item_page)
		{
			$this->dispatcher->forward(array(
				'controller' => 'error',
				'action' => 'not_found'));
			return;
		}

		$this->view->has_entries = count($item_page->items) != 0;
		$this->view->item_page = $item_page;
		$this->view->sort_by = $sort;
		$this->view->filter_by = $filter;
		$this->view->categories = $categories;
	}
}

?>
