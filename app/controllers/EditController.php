<?php

class EditController extends ControllerBase
{
	public function initialize()
	{
		$this->tag->setTitle('Edit Item');
		parent::initialize();
	}

	public function indexAction()
	{
		$item_id = $this->dispatcher->getParam('id');
		$item = Item::find_by_id($item_id);
		if (!$item || $this->session->get('user')->fb_id != $item->user_id)
		{
			$this->dispatcher->forward(array(
				'controller' => 'error',
				'action' => 'not_found'));
			return;
		}
		$categories = $this->getCategories();
		$this->view->categories = $categories;
		$this->view->selected_category = $item->category_id;
		$this->view->description = $item->description;
		$this->view->item_name = $item->name;
		$this->view->increment = $item->increment;
		$this->view->item_id = $item_id;
	}
	private function getCategories()
	{
		$categories = Category::find();

		return $categories;	
	}
}

?>
