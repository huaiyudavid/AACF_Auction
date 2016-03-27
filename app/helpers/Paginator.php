<?php

use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\AdapterInterface;

class Paginator implements AdapterInterface
{
	private $query;
	private $params;
	private $page;
	private $limit;

	public function __construct(array $config)
	{
		$this->query = $config['query'];
		$this->params = $config['params'];
		$this->limit = $config['limit'];
		$this->page = $config['page'];
	}

	public function setCurrentPage($page_)
	{
		$this->page = $page_;
	}

	public function getPaginate($model)
	{
		$result = new stdClass();
		$result->current = $this->page;

		$start = $this->limit * ($this->page - 1);
		$updatedQuery = $this->query . ' LIMIT ' . $this->limit . ' OFFSET ' .
			$start;
		$result->items = new Resultset(null, $model,
			$model->getReadConnection()->query($updatedQuery, $this->params));

		static $found_rows = 'SELECT FOUND_ROWS()';
		$result->total_items = $model->getReadConnection()->
			query($found_rows)->fetch()[0];
		$result->total_pages = ceil($result->total_items / $this->limit);
		return $result;
	}

	public function setLimit($limit_)
	{
		$this->limit = $limit_;
	}

	public function getLimit()
	{
		return $this->limit;
	}
}

?>
