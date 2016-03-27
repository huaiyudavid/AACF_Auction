<?php

use Phalcon\Acl;
use Phalcon\Acl\Adapter\Memory as AclList;
use Phalcon\Acl\Resource;
use Phalcon\Acl\Role;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\User\Plugin;

class AuthPlugin extends Plugin
{
	private function getAcl()
	{
/*		if (!isset($this->persistent->acl))
		{*/
			$acl = new AclList();

			$acl->setDefaultAction(Acl::DENY);

			$roles = array(
				'user' => new Role('User'),
				'guest' => new Role('Guest')
			);
			foreach ($roles as $role)
				$acl->addRole($role);

			$useronly = array
			(
				'add' => array('index'),
				'all' => array('index'),
				'ajax' => array('add_item', 'add_comment', 'check_item_description',
					'check_item_increment', 'check_item_name', 'check_item_price',
					'edit_item', 'get_notifications', 'get_recommendations',
				       	'read_all_notifications','read_notification', 'place_bid', 
					'refresh_item', 'search_typeahead', 'update_account', 'watchlist'),
				'error' => array('not_found'),
				'edit' => array('index'),
				'index' => array('index'),
				'item' => array('index'),
				'logout' => array('index'),
				'search' => array('index'),
				'settings' => array('index'),
				'users' => array('bids', 'index', 'watchlist')
			);
			foreach ($useronly as $resource => $action)
				$acl->addResource(new Resource($resource), $action);

			$guestonly = array
			(
				'ajax' => array('signup'),
				'login' => array('index', 'login'),
				'signup' => array('index')
			);
			foreach ($guestonly as $resource => $action)
				$acl->addResource(new Resource($resource), $action);

			$all = array
			(
				'ajax' => array('check_email', 'check_username')
			);
			foreach ($all as $resource => $action)
				$acl->addResource(new Resource($resource), $action);

			foreach ($useronly as $resource => $actions)
			{
				foreach ($actions as $action)
					$acl->allow('User', $resource, $action);
			}

			foreach ($guestonly as $resource => $actions)
			{
				foreach ($actions as $action)
					$acl->allow('Guest', $resource, $action);
			}

			foreach ($roles as $role)
			{
				foreach ($all as $resource => $actions)
				{
					foreach ($actions as $action)
						$acl->allow($role->getName(), $resource, $action);
				}
			}

			$this->persistent->acl = $acl;
		//}

		return $this->persistent->acl;
	}

	public function beforeDispatch(Event $event, Dispatcher $dispatcher)
	{
		$loggedIn = $this->session->get('user');
		if ($loggedIn)
			$role = 'User';
		else
			$role = 'Guest';

		$controller = $dispatcher->getControllerName();
		$action = $dispatcher->getActionName();

		$acl = $this->getAcl();

		$allowed = $acl->isAllowed($role, $controller, $action);
		if ($allowed != Acl::ALLOW)
		{
			if ($role == 'User')
			{
				$dispatcher->forward(array(
					'controller' => 'error',
					'action' => 'not_found'
				));
			}
			else
			{
				$dispatcher->forward(array(
					'controller' => 'login',
					'action' => 'index',
					'params' => array(
						'redirect_controller' => $controller,
						'redirect_action' => $action,
						'redirect_params' => $dispatcher->getParams()
					)
				));
			}
			return false;
		}
	}
}

?>
