<?php
use Phalcon\DI;
use Phalcon\Mvc\Model;

class ActionType extends Model
{
	const BID                = 'bid';
	const COMMENT            = 'comment';
	const CREATE             = 'create';
	const REPLY              = 'reply';
	const CHANGE_DESCRIPTION = 'change_description';
	const ITEM_EXPIRE        = 'item_expire';

	public $id;
	
	public $item_id;

	public $action_type;

	public function initialize()
	{
		$this->setSource("ActionTypes");
	}
}
?>
