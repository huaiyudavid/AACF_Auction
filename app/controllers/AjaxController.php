<?php

use Phalcon\Db\RawValue;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Transaction\Failed as TransactionFailure;
use Phalcon\Mvc\View;

class AjaxController extends Controller
{
	private function isTokenValid()
	{
		$token = $this->request->getPost('at');
		if ($token !== $this->session->get('token'))
			return false;
		return true;
	}

	private function check_bid($user_id, $item_id, $bid, $last_bid_time, &$response)
	{
		if (!ctype_digit($user_id))
			return;
		if (!ctype_digit($item_id))
			return;
		if (!ctype_digit($last_bid_time))
			return;

		if (!is_numeric($bid))
		{
			$response['bid'] = 'Please enter a number.';
			$response['valid'] = false;
			return;
		}
		if (!ctype_digit($bid))
		{
			$response['bid'] = 'Please enter a dollar amount.';
			$response['valid'] = false;
			return;
		}

		try
		{
			$transaction = $this->transactions->get();
			$user = User::find_by_id($user_id);
			if (!$user)
				$transaction->rollback();

			$item = Item::findFirst(array('id = :id:',
				'bind' => array('id' => $item_id), 'for_update' => true));
			if (!$item)
				$transaction->rollback();
			$item->setTransaction($transaction);

			$last_bid = Bid::find(array('item_id = :item_id:',
				'bind' => array('item_id' => $item_id), 'order' => 'id DESC',
				'limit' => 1));
			if (count($last_bid) != 1)
			{
				if (intval($bid) < $item->starting_price)
				{
					$response['fail'] = 'Please enter a valid amount.';
					$response['valid'] = false;
					$transaction->rollback();
				}
			}
			else
			{
				if (intval($bid) <= $last_bid->getFirst()->amount)
				{
					$this->get_bids_after_id($item_id, $last_bid_time, $response);
					$response['fail'] =
						'Uh oh. It looks like someone has stolen your bid.';
					$response['valid'] = false;
					$transaction->rollback();
				}
			}

			$this->add_to_watchlist($transaction, $response, $item_id,
				$user_id);

			$current_bid = new Bid();
			$current_bid->setTransaction($transaction);
			$current_bid->user_id = $user_id;
			$current_bid->item_id = $item_id;
			$current_bid->timestamp = new RawValue('default');
			$current_bid->amount = $bid;
			if (!$current_bid->save())
			{
				$response['fail'] =
					'Your bid could not be processed. Please try again.';
				$response['valid'] = false;
				$transaction->rollback();
			}
			$this->save_action($response, $transaction, $user_id, "bid", $item_id, $current_bid->timestamp);
		
			$transaction->commit();
			$this->get_bids_after_id($item_id, $last_bid_time, $response);
			$response['success'] = 'Your bid has successfully been placed.';
			$response['valid'] = true;
		}
		catch (TransactionFailure $e)
		{
		}
	}

	private function check_email($email, &$response)
	{
		if (!preg_match('/[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$/', $email))
			$response['email'] = 'Please enter a valid email address.';
	}

	private function check_item_description(&$description, &$response)
	{
		if (strlen($description) == 0)
			$response['description'] = 'This field cannot be left empty.';
		else
		{
			if (strlen($description) > 65535)
				$response['description'] = 'Please enter a shorter description.';
		}
	}

	private function check_item_increment($increment, &$response)
	{
		if (!is_numeric($increment))
			$response['increment'] = 'Please enter a number.';
		else
		{
			if (!ctype_digit($increment))
				$response['increment'] = 'Please enter a dollar amount.';
			else
			{
				$increment_int = intval($increment);
				if ($increment_int < 1 || $increment_int > 20)
					$response['increment'] = 'Please enter an increment between $1 and $20';
			}
		}
	}

	private function check_item_name($name, &$response)
	{
		if (strlen($name) == 0)
			$response['name'] = 'This field cannot be left empty.';
		else
		{
			if (strlen($name) > 100)
				$response['name'] = 'Please enter a name less than 100 characters.';
		}
	}

	private function check_item_price($price, &$response)
	{
		if (!is_numeric($price))
			$response['starting_price'] = 'Please enter a number.';
		else
		{
			if (!ctype_digit($price))
				$response['starting_price'] = 'Please enter a dollar amount.';
			else
			{
				$price_int = intval($price);
				if ($price_int < 1 || $price_int > 100)
					$response['starting_price'] = 'Please enter a price between $1 and $100';
			}
		}
	}

	private function check_username($username, &$response)
	{
		if (strlen($username) < 4 || strlen($username) > 50)
			$response['username'] = 'Please enter a username between 4 and 50 characters.';
		else
		{
			if (!preg_match('/^[a-zA-Z0-9]{4,50}$/', $username))
				$response['username'] = 'Please use only letters and numbers.';
			else
			{
				$current_username = null;
				if ($this->session->has('user'))
					$current_username = $this->session->get('user')->username;
				if ($current_username !== $username &&
					User::find_by_username($username))
				{
					$response['username'] = 'This username is already being used.';
				}
			}
		}
	}

	private function check_comment($user_id, $item_id, $comment, $last_timestamp,
		&$response)
	{
		if (!ctype_digit($user_id))
			return;
		if (!ctype_digit($item_id))
			return;
		try
		{
			$transaction = $this->transactions->get();
			$user = User::find_by_id($user_id);
			if (!$user)
				$transaction->rollback();

			$item = Item::findFirst(array('id = :id:',
				'bind' => array('id' => $item_id), 'for_update' => true));
			if (!$item)
				$transaction->rollback();
			$item->setTransaction($transaction);

			$this->add_to_watchlist($transaction, $response, $item_id,
				$user_id);

			$new_comment = new Comment();
			$new_comment->user_id = $user_id;
			$new_comment->item_id = $item_id;
			$new_comment->text = $comment;
			$new_comment->timestamp = new RawValue('default'); 
			$new_comment->setTransaction($transaction);
			if (!$new_comment->save())
			{
				$response['valid'] = false;
				$transaction->rollback();
			}
			$this->save_action($response, $transaction, $user_id, "comment", $item_id, $new_comment->timestamp);

			$transaction->commit();
			$this->get_comments_after_id($item_id, $last_timestamp, $response);
			$response['valid'] = true;
		}
		catch (TransactionFailure $e)
		{
		}
	}
	private function check_item_category($category, &$response)
	{
		$categories = Category::find();
		$category_ids= array();
		foreach($categories as $category_name)
		{
			$category_ids[] = $category_name->id;
		}	
		if(!in_array($category, $category_ids))
		{
			$response['category'] = "Please don't add your own categories. Category with id " . $category ." does not exist. You're not that clever";
			$response['categories'] = $category_ids;
		}
	}
	private function &complete_response(&$response)
	{
		$response = 'for(;;);' . $response;
		return $response;
	}

	private function add_to_watchlist($transaction, &$response, $item_id,
		$user_id)
	{
		$watchlist = Watchlist::findFirst(array(
			'item_id = :item_id: AND user_id = :user_id:',
			'bind' => array('item_id' => $item_id, 'user_id' => $user_id)));

		if (!$watchlist)
		{
			$watchlist = new Watchlist();
			$watchlist->setTransaction($transaction);
			$watchlist->user_id = $user_id;
			$watchlist->item_id = $item_id;
			$watchlist->is_user_created = 0;
			if (!$watchlist->save())
			{
				$response['fail'] =
					'Your request could not be processed. Please try again.';
				$response['valid'] = false;
				$transaction->rollback();
			}
		}
	}

	private function get_comments_after_id($item_id, $timestamp, &$response)
	{
		$item = Item::find_by_id($item_id);
		if(!$item)
			return;

		$comments = Comment::get_from_item($item_id, $timestamp);
		$num_comments = count($comments);
		if($num_comments > 0)
		{
			$rows = new View();
			$rows->setViewsDir(APP_PATH . $this->config->application->viewsDir);
			$rows->setDI($this->di);
			$rows->registerEngines(array(".volt" => 'volt'));
			$rows->comments= $comments;
		
			$response['rows'] = $rows->getPartial('partials/comments');
			$response['num_rows'] = $num_comments;
		}
	}

	private function save_action(&$response, $transaction, $user_id, $action_type, $item_id, $timestamp)
	{
		$action_type_row = ActionType::find(array('item_id = :item_id: AND action_type = :action_type:', 'bind'=>array('item_id'=>$item_id, 'action_type'=>$action_type))); 
//			$item = Item::findFirst(array('id = :id:',
//				'bind' => array('id' => $item_id), 'for_update' => true));
//		$action_type_row = array();
		$id = 0;
		if(count($action_type_row) < 1)
		{
			$new_action_type = new ActionType();
			$new_action_type->setTransaction($transaction);
			$new_action_type->item_id = $item_id;
			$new_action_type->action_type = $action_type;
			if(!$new_action_type->save())
			{
				$response['fail'] = "something went wrong please try again";
				$failstring = "";
				foreach($new_action_type->getMessages() as $message)
				{
					$failstring = $failstring . $message . " ";
				}
				$response['fail'] = $failstring;
				$response['action_type_fail'] = "action_type failed";

				$transaction->rollback();
			}
			else
			{
				$id = $new_action_type->id;
			}
		}
		else
		{
			$id = $action_type_row->getFirst()->id;
		}
		$new_action = new Action();
		$new_action->setTransaction($transaction);
		$new_action->user_id = $user_id;
		$new_action->action_id = $id;
		$new_action->timestamp = $timestamp;
		if(!$new_action->save())
		{
			$response['fail'] = "something went wrong please try again";
			$transaction->rollback();
		}

	}

	private function save_item_picture($picture, &$response)
	{
		$image = imagecreatefromstring($picture);

		/*if (!$image)
		{
			$response['fail'] = 'Unable to create image';
			$response['valid'] = false;
			return null;
		}*/


		$filename = hash('sha256', openssl_random_pseudo_bytes(512));
		$filename .= '.jpg';
		/*$width = imagesx($image);
		$height = imagesy($image);

		if ($width > $height)
		{
			$old_x = ($width - $height) / 2;
			$old_y = 0;
			$old_size = $height;
		}
		else
		{
			$old_x = 0;
			$old_y = ($height - $width) / 2;
			$old_size = $width;
		}

		$new_size = ($old_size > Common::IMAGE_SIZE) ? Common::IMAGE_SIZE :
			$old_size;*/

		/*$final_image = imagecreatetruecolor($new_size, $new_size);
		if (!imagecopyresampled($final_image, $image, 0, 0, $old_x, $old_y,
			$new_size, $new_size, $old_size, $old_size))
		{
			imagedestroy($image);
			imagedestroy($final_image);
			$response['fail'] = 'Unable to resize image';
			$response['valid'] = false;
			return null;
		}

		if (!imagejpeg($final_image, Common::IMAGE_FOLDER . $filename))
		{
			imagedestroy($image);
			imagedestroy($final_image);
			$response['fail'] = 'Unable to save image';
			$response['valid'] = false;
			return null;
		}

		imagedestroy($image);
		imagedestroy($final_image);*/
		return $filename;
	}

	private function get_bids_after_id($item_id, $bid_time, &$response)
	{
		$item = Item::find_by_id($item_id);
		if (!$item)
			return;

		$bids = Bid::get_all($item_id, $bid_time);
		$num_bids = count($bids);
		if ($num_bids > 0)
		{
			$rows = new View();
			$rows->setViewsDir(APP_PATH . $this->config->application->viewsDir);
			$rows->setDI($this->di);
			$rows->registerEngines(array(".volt" => 'volt'));
			$rows->bids = $bids;
			$response['rows'] = $rows->getPartial('partials/bids');

			$response['num_rows'] = $num_bids;
			$response['price'] = $bids->getFirst()->amount;
			$response['min_bid'] = $bids->getFirst()->amount + $item->increment;
		}
	}

	public function add_itemAction()
	{
		$httpResponse = new Response();
		if (!$this->request->isPost())
			return $httpResponse;
		if (!$this->isTokenValid())
			return $httpResponse;

		$name = $this->request->getPost('name', 'trim');
		$description = $this->request->getPost('description', 'trim');
		$price = $this->request->getPost('starting_price', 'trim');
		$increment = $this->request->getPost('increment', 'trim');
		$category = $this->request->getPost('category','trim');
		$picture = $this->request->getPost('file', 'trim');
		if (!is_string($name))
			return $httpResponse;
		if (!is_string($description))
			return $httpResponse;

		$has_picture = !empty($picture);

		if ($has_picture)
		{
			$picture = substr($picture, strpos($picture, ',') + 1);
			$picture = base64_decode($picture);
		}

		$response = array();
		$this->check_item_name($name, $response);
		$this->check_item_description($description, $response);
		$this->check_item_price($price, $response);
		$this->check_item_increment($increment, $response);
		$this->check_item_category($category, $response);
		$filename = $has_picture ? $this->save_item_picture($picture, $response) :
			str_replace(' ', '_', Category::findFirst(array('id=:id:',
			'bind' => array('id' => $category)))->category) . '.jpg';
		
		if (count($response) > 0)
			$response['valid'] = false;
		else
		{
			$transaction = $this->transactions->get();
			$item = new Item();
			$item->setTransaction($transaction);
			$item->name = $name;
			$item->description = $description;
			$item->user_id = $this->session->get('user')->fb_id;
			$item->created = new RawValue('default');
			$item->starting_price = $price;
			$item->increment = $increment;
			$item->category_id = $category;
			$item->filename = $filename;
			if ($item->create())
			{
				// invalidate cache items
				$this->cache->delete('Item.count');
				$response['valid'] = true;
				$response['id'] = $item->id;
		
				$this->add_to_watchlist($transaction, $response, $item->id,
					$item->user_id);
				$this->save_action($response, $transaction, $item->user_id, "create",
					$item->id, $item->created);
				$transaction->commit();
			}
			else
			{
				$transaction->rollback();
				$response['valid'] = false;
				$response['item'] = $item;
				$response['error'] = 'Failed to create item';
			}
		}

		$httpResponse->setContent($this->complete_response(json_encode($response)));
		return $httpResponse;
	}
	public function edit_itemAction()
	{
		$httpResponse = new Response();
		if (!$this->request->isPost())
			return $httpResponse;
		if (!$this->isTokenValid())
			return $httpResponse;
		$item_id = $this->request->getPost('item_id', 'trim');
		$user_id = $this->session->get('user')->fb_id;
		$name = $this->request->getPost('name', 'trim');
		$description = $this->request->getPost('description', 'trim');
		$increment = $this->request->getPost('increment', 'trim');
		$category = $this->request->getPost('category','trim');
		if (!is_string($name))
			return $httpResponse;
		if (!is_string($description))
			return $httpResponse;

		$response = array();
		$this->check_item_name($name, $response);
		$this->check_item_description($description, $response);
		$this->check_item_increment($increment, $response);
		$this->check_item_category($category, $response);
		if (count($response) > 0)
		{
			$response['valid'] = false;
		}
		else
		{
			try
			{
				$transaction = $this->transactions->get();

				$current_item = Item::find_by_id($item_id);
				if (!$current_item || $current_item->user_id != $user_id)
				{
					$response['valid'] = false;
					$response['error'] = 'Failed to edit item';				
					$transaction->rollback();
				}

				$edit_item = 'UPDATE Items SET name = :item_name, description = :item_description,
								category_id = :category, increment = :item_increment
					WHERE id = :item_id AND user_id=:user_id';
				$write_model = new Item();
				$write_model->setTransaction($transaction);
				if (!$write_model->getWriteConnection()->execute($edit_item,
					array('item_name' => $name, 'item_description'=>$description, 
					'category' => $category, 'item_increment'=>$increment, 'item_id'=>$item_id, 'user_id'=>$user_id)))
				{
					$transaction->rollback();
					$response['valid'] = false;
					$response['error'] = "failed to edit item";
				}
				else
				{
					$action = new Action();
					$action->timestamp = new RawValue('default'); 
					$this->save_action($response, $transaction, $user_id, "change_description",
							$item_id, $action->timestamp);
					$response['id'] = $item_id;
					$transaction->commit();
				}
			}
			catch (TransactionFailure $e)
			{
			}
		}

		$httpResponse->setcontent($this->complete_response(json_encode($response)));
		return $httpResponse;
	}
	public function check_emailaction()
	{
		$httpresponse = new response();
		if (!$this->request->ispost())
			return $httpresponse;
		if (!$this->istokenvalid())
			return $httpresponse;

		$email = $this->request->getPost('email', 'trim');
		if (!is_string($email))
			return $httpResponse;

		$response = array();
		$this->check_email($email, $response);
		if (count($response) > 0)
			$response['valid'] = false;
		else
			$response['valid'] = true;

		$httpResponse->setContent($this->complete_response(json_encode($response)));
		return $httpResponse;
	}

	public function check_item_descriptionAction()
	{
		$httpResponse = new Response();
		if (!$this->request->isPost())
			return $httpResponse;
		if (!$this->isTokenValid())
			return $httpResponse;

		$description = $this->request->getPost('description', 'trim');
		if (!is_string($description))
			return $httpResponse;

		$response = array();
		$this->check_item_description($description, $response);
		if (count($response) > 0)
			$response['valid'] = false;
		else
			$response['valid'] = true;

		$httpResponse->setContent($this->complete_response(json_encode($response)));
		return $httpResponse;
	}

	public function check_item_incrementAction()
	{
		$httpResponse = new Response();
		if (!$this->request->isPost())
			return $httpResponse;
		if (!$this->isTokenValid())
			return $httpResponse;

		$increment = $this->request->getPost('increment', 'trim');

		$response = array();
		$this->check_item_increment($increment, $response);
		if (count($response) > 0)
			$response['valid'] = false;
		else
			$response['valid'] = true;

		$httpResponse->setContent($this->complete_response(json_encode($response)));
		return $httpResponse;
	}
	public function check_item_categoryAction()
	{
		$httpResponse = new Response();
		if(!$this->request->isPost())
			return $httpResponse;
		if(!$this->isTokenValid())
			return $httpResponse;

		$category = $this->request->getPost('category', 'trim');
		$response = array();
		$this->check_item_category($category, $response);
		if(count($response) > 0)
			$response['valid'] = false;
		else
			$response['valid'] = true;
		$httpResponse->setContent($this->complete_response(json_encode($response)));
		return $httpResponse;
	}
	public function check_item_nameAction()
	{
		$httpResponse = new Response();
		if (!$this->request->isPost())
			return $httpResponse;
		if (!$this->isTokenValid())
			return $httpResponse;

		$name = $this->request->getPost('name', 'trim');
		if (!is_string($name))
			return $httpResponse;

		$response = array();
		$this->check_item_name($name, $response);
		if (count($response) > 0)
			$response['valid'] = false;
		else
			$response['valid'] = true;

		$httpResponse->setContent($this->complete_response(json_encode($response)));
		return $httpResponse;
	}

	public function check_item_priceAction()
	{
		$httpResponse = new Response();
		if (!$this->request->isPost())
			return $httpResponse;
		if (!$this->isTokenValid())
			return $httpResponse;

		$price = $this->request->getPost('starting_price', 'trim');

		$response = array();
		$this->check_item_price($price, $response);
		if (count($response) > 0)
			$response['valid'] = false;
		else
			$response['valid'] = true;

		$httpResponse->setContent($this->complete_response(json_encode($response)));
		return $httpResponse;
	}

	public function check_usernameAction()
	{
		$httpResponse = new Response();
		if (!$this->request->isPost())
			return $httpResponse;
		if (!$this->isTokenValid())
			return $httpResponse;

		$username = $this->request->getPost('username', 'trim');
		if (!is_string($username))
			return $httpResponse;

		$response = array();
		$this->check_username($username, $response);
		if (count($response) > 0)
			$response['valid'] = false;
		else
			$response['valid'] = true;
		
		$httpResponse->setContent($this->complete_response(json_encode($response)));
		return $httpResponse;
	}

	public function get_notificationsAction()
	{
		$httpResponse = new Response();
		if (!$this->request->isPost())
			return $httpResponse;
		if (!$this->isTokenValid())
			return $httpResponse;

		$timestamp = $this->request->getPost('timestamp', 'trim');
		if (!ctype_digit($timestamp))
			return $httpResponse;

		$notifications = Notification::get_notifications(
			$this->session->get('user')->fb_id, $timestamp, $num_unread);

		$rows = new View();
		$rows->setViewsDir(APP_PATH . $this->config->application->viewsDir);
		$rows->setDI($this->di);
		$rows->registerEngines(array('.volt' => 'volt'));
		$rows->notifications = $notifications;
		$response['rows'] = $rows->getPartial('partials/notifications');
		$response['new'] = !empty($response['rows']);
		$response['new_unread'] = $num_unread;

		$httpResponse->setContent($this->complete_response(json_encode($response)));
		return $httpResponse;
	}

	public function get_recommendationsAction()
	{
		$httpResponse = new Response();
		if (!$this->request->isPost())
			return $httpResponse;
		if (!$this->isTokenValid())
			return $httpResponse;

		$recommendations = Common::make_recommendation(
			$this->session->get('user')->fb_id);

		$rows = new View();
		$rows->setViewsDir(APP_PATH . $this->config->application->viewsDir);
		$rows->setDI($this->di);
		$rows->registerEngines(array('.volt' => 'volt'));
		$rows->recommendations = $recommendations;
		$response['rows'] = $rows->getPartial('partials/recommendations');
		$response['valid'] = true;

		$httpResponse->setContent($this->complete_response(json_encode($response)));
		return $httpResponse;
	}

	public function place_bidAction()
	{
		$httpResponse = new Response();
		if (!$this->request->isPost())
			return $httpResponse;
		if (!$this->isTokenValid())
			return $httpResponse;

		$user_id = $this->request->getPost('user_id', 'trim');
		$item_id = $this->request->getPost('item_id', 'trim');
		$amount = $this->request->getPost('bid', 'trim');
		$last_bid_time = $this->request->getPost('last', 'trim');
		if ($last_bid_time === null)
			$last_bid_time = '0';

		$response = array();
		$this->check_bid($user_id, $item_id, $amount, $last_bid_time, $response);
		
		$httpResponse->setContent($this->complete_response(json_encode($response)));
		return $httpResponse;
	}

	public function add_commentAction()
	{
		$httpResponse = new Response();
		if(!$this->request->isPost())
			return $httpResponse;
		if(!$this->isTokenValid())
		{
			return $httpResponse;
		}

		$userid = $this->request->getPost('user_id', 'trim');
		$item_id = $this->request->getPost('item_id','trim');
		$comment = $this->request->getPost('comment','trim');
		$last_timestamp = $this->request->getPost('last','trim');
		if($last_timestamp === null)
			$last_timestamp = '0';

		$response = array();

		$this->check_comment($userid, $item_id, $comment, $last_timestamp,
			$response);
		$httpResponse->setContent($this->complete_response(json_encode($response)));
		return $httpResponse;
	}

	public function read_all_notificationsAction()
	{
		$httpResponse = new Response();
		if (!$this->request->isPost())
			return $httpResponse;
		if (!$this->isTokenValid())
			return $httpResponse;

		$response['success'] = Notification::read_all_notifications();

		$httpResponse->setContent($this->complete_response(json_encode($response)));
		return $httpResponse;
	}

	public function read_notificationAction()
	{
		$httpResponse = new Response();
		if (!$this->request->isPost())
			return $httpResponse;
		if (!$this->isTokenValid())
			return $httpResponse;

		$id = $this->request->getPost('id', 'trim');
		if (!ctype_digit($id))
			return $httpResponse;

		$response['success'] = Notification::read_notification($id);

		$httpResponse->setContent($this->complete_response(json_encode($response)));
		return $httpResponse;
	}

	public function refresh_itemAction()
	{
		$httpResponse = new Response();
		if (!$this->request->isPost())
			return $httpResponse;
		if (!$this->isTokenValid())
			return $httpResponse;

		$last_bid_time = $this->request->getPost('last', 'trim');
		$item_id = $this->request->getPost('item_id' ,'trim');
		if ($last_bid_time === null)
			$last_bid_time = '0';
		if (!ctype_digit($last_bid_time))
			return $httpResponse;
		if (!ctype_digit($item_id))
			return $httpResponse;

		$response = array();
		$this->get_bids_after_id($item_id, $last_bid_time, $response);

		if (count($response) > 0)
			$response['latest'] = false;
		else
			$response['latest'] = true;

		$httpResponse->setContent($this->complete_response(json_encode($response)));
		return $httpResponse;
	}

	public function search_typeaheadAction()
	{
		$httpResponse = new Response();
		if (!$this->request->isPost())
			return $httpResponse;
		if (!$this->isTokenValid())
			return $httpResponse;

		$query = $this->request->getPost('query', 'trim');

		if (empty($query))
			return $httpResponse;

		$user_page = User::search($query);
		$item_page = Item::search($query, 'relevance', 'none', 1);

		$response = array();
		$rows = new View();
		$rows->setViewsDir(APP_PATH . $this->config->application->viewsDir);
		$rows->setDI($this->di);
		$rows->registerEngines(array(".volt" => 'volt'));
		$rows->user_page = $user_page;
		$rows->item_page = $item_page;
		$response['rows'] = $rows->getPartial('partials/search_typeahead');

		$httpResponse->setContent($this->complete_response(json_encode($response)));
		return $httpResponse;
	}

	public function signupAction()
	{
		$httpResponse = new Response();
		if (!$this->request->isPost())
			return $httpResponse;
		if (!$this->isTokenValid())
			return $httpResponse;

		// Check if the user is in the right state
		$fb_session = $this->session->get('facebook');
		$fb_user = $this->session->get('fb_user');
		if (!$fb_session || !$fb_user)
			return;

		$username = $this->request->getPost('username', 'trim');
		$email = $this->request->getPost('email', 'trim');
		if (!is_string($username))
			return;
		if (!is_string($email))
			return;

		$response = array();
		$this->check_username($username, $response);
		$this->check_email($email, $response);

		if (count($response) > 0)
			$response['valid'] = false;
		else
		{
			$fb_user = $this->session->get('fb_user');
			$user = new User();
			$user->fb_id = $fb_user->id;
			$user->first_name = $fb_user->first_name;
			$user->last_name = $fb_user->last_name;
			$user->username = $username;
			$user->email = $email;
			$user->profile_small = $this->session->get('profile_picture');
			$user->profile_large = $this->session->get('profile_picture_large');
			$user->last_notification = new RawValue('default');
			if ($user->create())
			{
				$this->session->remove('facebook');
				$this->session->remove('fb_user');
				$this->session->remove('profile_picture');
				$this->session->remove('profile_picture_large');
				$this->session->set('user', $user);
				$this->session->remove('token');
				session_regenerate_id(true);

				$response['valid'] = true;
			}
			else
			{
				$response['valid'] = false;
				$response['error'] = 'Unable to create entry';
			}
		}

		$httpResponse->setContent($this->complete_response(json_encode($response)));
		return $httpResponse;
	}

	public function update_accountAction()
	{
		$httpResponse = new Response();
		if (!$this->request->isPost())
			return $httpResponse;
		if (!$this->isTokenValid())
			return $httpResponse;
		if (!$this->session->has('user'))
			return $httpResponse;

		$username = $this->request->getPost('username', 'trim');
		$email = $this->request->getPost('email', 'trim');
		if (!is_string($username))
			return;
		if (!is_string($email))
			return;

		$response = array();
		$this->check_username($username, $response);
		$this->check_email($email, $response);

		if (count($response) > 0)
			$response['valid'] = false;
		else
		{
			$user = $this->session->get('user');
			if ($user->save(array('username' => $username, 'email' => $email)))
				$response['valid'] = true;
			else
			{
				$response['valid'] = false;
				$response['error'] = 'Unable to save user';
			}
		}

		$httpResponse->setContent($this->complete_response(json_encode($response)));
		return $httpResponse;
	}

	public function watchlistAction()
	{
		$httpResponse = new Response();
		if (!$this->request->isPost())
			return $httpResponse;
		if (!$this->isTokenValid())
			return $httpResponse;

		$user_id = $this->request->getPost('user_id', 'trim');
		$item_id = $this->request->getPost('item_id', 'trim');
		if (!ctype_digit($user_id))
			return $httpResponse;
		if (!ctype_digit($item_id))
			return $httpResponse;

		$response = array();
		try
		{
			$transaction = $this->transactions->get();
			$user = User::find_by_id($user_id);
			if (!$user)
				$transaction->rollback();

			$item = Item::findFirst(array('id = :id:',
				'bind' => array('id' => $item_id), 'for_update' => true));
			if (!$item)
				$transaction->rollback();
			$item->setTransaction($transaction);

			$previous_watchlist = Watchlist::findFirst(array(
				'item_id = :item_id: AND user_id = :user_id:',
				'bind' => array('item_id' => $item_id, 'user_id' => $user_id)));
			if ($previous_watchlist != null &&
				$previous_watchlist->is_user_created == 1)
			{
				$response['fail'] =
					'It looks like this item is already on your watchlist.';
				$response['valid'] = false;
				$transaction->rollback();
			}

			if ($previous_watchlist == null)
			{
				$watchlist = new Watchlist();
				$watchlist->setTransaction($transaction);
				$watchlist->user_id = $user_id;
				$watchlist->item_id = $item_id;
				$watchlist->is_user_created = 1;
				if (!$watchlist->save())
				{
					$response['fail'] =
						'Item could not be added to watchlist. Please try again.';
					$response['valid'] = false;
					$transaction->rollback();
				}
			}
			else
			{
				$previous_watchlist->setTransaction($transaction);
				$previous_watchlist->is_user_created = 1;
				if (!$previous_watchlist->save())
				{
					$response['fail'] =
						'Item could not be added to watchlist. Please try again.';
					$response['valid'] = false;
					$transaction->rollback();
				}
			}
			$transaction->commit();

			$response['success'] =
				'Item has been successfully added to your watchlist.';
			$response['valid'] = true;
		}
		catch (TransactionFailure $e)
		{
		}

		$httpResponse->setContent($this->complete_response(json_encode($response)));
		return $httpResponse;
	}
}

?>
