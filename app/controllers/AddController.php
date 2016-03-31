<?php

class AddController extends ControllerBase
{
	public function initialize()
	{
		$this->tag->setTitle('Add Item');
		parent::initialize();
	}

	public function indexAction()
	{
		$categories = $this->getCategories();
		$this->view->categories = $categories;
	}
	private function getCategories()
	{
		$categories = Category::find();

		return $categories;	
	}
}