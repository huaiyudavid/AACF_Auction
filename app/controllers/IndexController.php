<?php

use Phalcon\Mvc\View;

class IndexController extends ControllerBase
{
	public function initialize()
	{
		$this->tag->setTitle('AACF Auction');
		parent::initialize();
	}

	public function indexAction()
	{
		if (!$this->session->get('user'))
		{
			$this->view->disableLevel(View::LEVEL_ACTION_VIEW);
			return;
		}
		$this->view->recommendations = Common::make_recommendation($this->session->get('user')->fb_id);
		$this->view->stories = Common::fill_newsfeed(
			$this->session->get('user')->fb_id, 30);
	}
}

?>
