<?php

use Phalcon\DI;
use Phalcon\Mvc\Model;

class Category extends Model
{
	public $id;

	public $category;

	public function initialize()
	{
		$this->setSource("Categories");
	}	
}
