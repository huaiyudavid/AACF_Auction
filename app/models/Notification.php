<?php

use Phalcon\DI;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Mvc\Model\Transaction\Failed as TransactionFailure;

class Notification extends Model
{
	public $id;

	public $user_id;

	public $action_id;

	public $timestamp;

	public $is_read;

	public function initialize()
	{
		$this->setSource("Notifications");
	}

	public static function create_notification($user_id, $action_id, $timestamp,
		$is_read)
	{
		$notification = new Notification();
		$notification->user_id = $user_id;
		$notification->action_id = $action_id;
		$notification->timestamp = $timestamp;
		$notification->is_read = $is_read;
		return $notification;
	}

	// This function get all notifications for a user, user_id, after a certain
	// timestamp, timestamp, with a maximum of Common::MAX_NOTIFICATIONS
	// number of notificatins.
	public static function get_notifications($user_id, $timestamp, &$num_unread)
	{
		$logged_in_user = User::findFirst(array('fb_id = :user_id:',
			'bind' => array('user_id' => DI::getDefault()->get('session')->
			get('user')->fb_id)));
		$last_timestamp = $logged_in_user->last_notification;

		$new_builder = DI::getDefault()->get('modelsManager')->createBuilder()->
			from('Action')->columns(array('Action.user_id', 'User.first_name',
			'User.last_name', 'UNIX_TIMESTAMP(Action.timestamp) as timestamp',
			'Action.timestamp as date_time', 'Action.action_id',
			'ActionType.action_type', 'Item.id as item_id', 'Item.name as item_name',
			'User.profile_small as user_picture'))->
			where('Action.user_id != :user_id: AND Action.timestamp > :timestamp:
			AND Item.id IN (SELECT Watchlist.item_id FROM Watchlist WHERE
			Watchlist.user_id = :user_id_2:)', array('user_id' => $user_id,
			'timestamp' => $last_timestamp, 'user_id_2' => $user_id))->
			leftjoin('ActionType', 'Action.action_id = ActionType.id')->
			leftjoin('Item', 'ActionType.item_id = Item.id')->
			leftjoin('User', 'Action.user_id = User.fb_id')->
			orderBy('Action.timestamp DESC');
		$actions = $new_builder->getQuery()->execute();

		$current_timestamp = time();

		$notifications = array();
		$notification_users = array();
		foreach ($actions as $action)
		{
			$friend_id = $action->user_id;
			if (!array_key_exists($action->action_id, $notifications))
			{
				$action->user_id = $user_id;
				$action->is_read = 0;
				$notifications[$action->action_id] = $action;
			}

			if (!array_key_exists($action->action_id, $notification_users))
				$notification_users[$action->action_id] = array();

			if (!in_array($friend_id, $notification_users[$action->action_id]))
			{
				$notification_users[$action->action_id][] = $friend_id;
				$notifications[$action->action_id]->friend_names[] =
					$action->first_name . ' ' . $action->last_name;
			}
		}

		$num_unread = count($notifications);

		// Query for old notifications up to timestamp with limit
		$old_notifications_limit = Common::MAX_NOTIFICATIONS -
			count($notifications);
		$old_query = 'SELECT friends, Notifications.id,
			UNIX_TIMESTAMP(Notifications.timestamp) as timestamp,
			Notifications.is_read, ActionTypes.action_type, Items.id as item_id,
			Items.name as item_name, Users.profile_small as user_picture FROM
			(SELECT Notifications.id, GROUP_CONCAT(CONCAT_WS(" ", Users.first_name,
			Users.last_name) ORDER BY NotificationUsers.id) as friends FROM
			Notifications LEFT JOIN NotificationUsers ON Notifications.id =
			NotificationUsers.notification_id LEFT JOIN Users ON
			NotificationUsers.user_id = Users.fb_id GROUP BY Notifications.id) tmp
			JOIN Notifications ON tmp.id = Notifications.id LEFT JOIN ActionTypes ON
			Notifications.action_id = ActionTypes.id LEFT JOIN NotificationUsers ON
			Notifications.id = NotificationUsers.notification_id LEFT JOIN Users ON
			NotificationUsers.user_id = Users.fb_id LEFT JOIN Items ON
			ActionTypes.item_id = Items.id WHERE Notifications.user_id = :user_id
			AND Notifications.timestamp > FROM_UNIXTIME(:timestamp) GROUP BY
			Notifications.id ORDER BY Notifications.timestamp DESC LIMIT ' .
			$old_notifications_limit;
		$model = new Notification();
		$old_notifications = new ResultSet(null, $model,
			$model->getReadConnection()->query($old_query,
			array(':user_id' => $user_id, ':timestamp' => $timestamp)));
		
		// Write notifications into table
		try
		{
			$transaction = DI::getDefault()->get('transactions')->get();

			$update_user = 'UPDATE Users SET last_notification = FROM_UNIXTIME(:time)
				WHERE fb_id = :user_id';
			$write_model = new User();
			$write_model->setTransaction($transaction);
			if (!$write_model->getWriteConnection()->execute($update_user,
				array(':time' => $current_timestamp,
				':user_id' => DI::getDefault()->get('session')->get('user')->fb_id)))
			{
				$transaction->rollback();
			}

			$reverse_notifications = array_reverse($notifications);
			foreach($reverse_notifications as $notification_)
			{
				$notification = Notification::create_notification(
					$notification_->user_id, $notification_->action_id,
					$notification_->date_time, $notification_->is_read);
				$notification->setTransaction($transaction);
				if (!$notification->save())
					$transaction->rollback();

				$notifications[$notification->action_id]->id = $notification->id;

				foreach($notification_users[$notification_->action_id] as $user)
				{
					$notification_user = NotificationUser::
						create_notification_user($notification->id, $user);
					$notification_user->setTransaction($transaction);
					if (!$notification_user->save())
						$transaction->rollback();
				}
			}

			$transaction->commit();
		}
		catch (TransactionFailure $e)
		{
		}

		foreach($old_notifications as $notification)
		{
			if (!$notification->is_read)
				$num_unread++;

			// To instantiate, don't know why it won't work if this line isn't here
			$notification->friend_names = 0;
			$notification->friend_names = explode(',', $notification->friends);
			$notifications[] = $notification;
		}

		return $notifications;
	}

	public static function read_all_notifications()
	{
		$logged_in_user = DI::getDefault()->get('session')->get('user')->fb_id;

		$update = 'UPDATE Notifications SET is_read = 1 WHERE
			Notifications.user_id = :user_id';
		$model = new Notification();
		return $model->getWriteConnection()->execute($update,
			array(':user_id' => $logged_in_user));
	}

	public static function read_notification($id)
	{
		$logged_in_user = DI::getDefault()->get('session')->get('user')->fb_id;

		try
		{
			$transaction = DI::getDefault()->get('transactions')->get();

			$notification = Notification::findFirst(
				array('id = :id: AND user_id = :user_id:',
				'bind' => array('id' => $id, 'user_id' => $logged_in_user),
				'for_update' => true));
			if (!$notification)
				$transaction->rollback();

			if ($notification->is_read)
				$transaction->rollback();
			$notification->is_read = 1;
			if (!$notification->save())
				$transaction->rollback();

			$transaction->commit();
		}
		catch (TransactionFailure $e)
		{
			return false;
		}

		return true;
	}
}
