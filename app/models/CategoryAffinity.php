<?php
use Phalcon\DI;
use Phalcon\Mvc\Model;

class CategoryAffinity extends Model
{

	public $id;
	public $user_id;
	public $category_id;
	public $affinity_score;

	public function initialize()
	{
		$this->setSource("CategoryAffinities");
	}
}
