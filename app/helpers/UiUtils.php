<?php

use Phalcon\Mvc\User\Component;

class UiUtils extends Component
{
	public function convert_time($unix_time)
	{
		$timezone = $this->session->get('timezone');

		$current_time = time() + $timezone * 3600;
		$unix_time = $unix_time + $timezone * 3600;
		$difference = $current_time - $unix_time;

		// if more than 12 hrs ago rounded down
		if ($difference >= 46800)
		{
			$day = (int)($unix_time / 86400);
			$cur_day = (int)($current_time / 86400);
			// is still today
			if ($cur_day == $day)
				return 'Today at ' . date('g:ia', $unix_time);
			// yesterday
			if ($cur_day - $day == 1)
				return 'Yesterday at ' . date('g:ia', $unix_time);

			$year = date('Y', $unix_time);
			$cur_year = date('Y', $current_time);
			// same year
			if ($year == $cur_year)
				return date('F j \a\t g:ia', $unix_time);
			return date('F j, Y \a\t g:ia', $unix_time);
		}
		else
		{
			// less than a minute ago
			if ($difference < 60)
				return 'Less than a minute ago';
			// less than an hour ago
			if ($difference < 3600)
			{
				return (int)($difference / 60) == 1 ?
					'1 min ago' : (int)($difference / 60) . ' mins ago';
			}
			return (int)($difference / 3600) == 1 ?
				'1 hr ago' : (int)($difference / 3600) . ' hrs ago';
		}
	}

	public function get_newsfeed_story_description($story)
	{
		switch ($story->action_type)
		{
		case ActionType::BID:
			return $this->format_newsfeed_interaction_description($story, 'bid');
		case ActionType::COMMENT:
			return $this->format_newsfeed_interaction_description($story,
				'commented');
		case ActionType::CHANGE_DESCRIPTION:
			return 'The details of this item has changed';
		case ActionType::CREATE:
			return '<a href="' . $this->config->application->website .
				'users/' . htmlspecialchars($story->item_creator_username) . '">' .
				htmlspecialchars($story->item_creator_first_name . ' ' .
				$story->item_creator_last_name) . '</a>' . ' added a new item';
		}
	}

	public function get_story_description($story)
	{
		switch ($story->action_type)
		{
		case ActionType::BID:
			return $this->format_interaction_description($story, 'bid');
		case ActionType::COMMENT:
			return $this->format_interaction_description($story, 'commented');
		case ActionType::CHANGE_DESCRIPTION:
			return 'The details of <b>' . htmlspecialchars($story->item_name) .
				'</b> has changed';
		}
	}

	public function get_story_picture($story)
	{
		if (!empty($story->user_picture))
			return htmlspecialchars($story->user_picture);

		if (!empty($story->item_picture))
			return htmlspecialchars($story->item_picture);

		return '';
	}

	private function format_newsfeed_interaction_description($story, $interaction)
	{
		$num_friends = count($story->friend_names);

		$keys = array_keys($story->friend_names);
		if ($num_friends == 1)
		{
			$description = '<a href="' . $this->config->application->website .
				'users/' . htmlspecialchars($keys[0]) . '">' .
				htmlspecialchars($story->friend_names[$keys[0]]) . '</a>';
		}
		else if ($num_friends == 2)
		{
			$description = '<a href="' . $this->config->application->website .
				'users/' . htmlspecialchars($keys[0]) . '">' .
				htmlspecialchars($story->friend_names[$keys[0]]) .
				'</a> and <a href="' . $this->config->application->website .
				'users/' . htmlspecialchars($keys[1]) . '">' .
				htmlspecialchars($story->friend_names[$keys[1]]) . '</a>';
		}
		else if ($num_friends == 3)
		{
			$description = '<a href="' . $this->config->application->website .
				'users/' . htmlspecialchars($keys[0]) . '">' .
				htmlspecialchars($story->friend_names[$keys[0]]) .
				'</a>, <a href="' . $this->config->application->website .
				'users/' . htmlspecialchars($keys[1]) . '">' .
				htmlspecialchars($story->friend_names[$keys[1]]) .
				'</a> and <a href="' . $this->config->application->website .
				'users/' . htmlspecialchars($keys[2]) . '">' .
				htmlspecialchars($story->friend_names[$keys[2]]) . '</a>';
		}
		else if ($num_friends == 4)
		{
			$description = '<a href="' . $this->config->application->website .
				'users/' . htmlspecialchars($keys[0]) . '">' .
				htmlspecialchars($story->friend_names[$keys[0]]) .
				'</a>, <a href="' . $this->config->application->website .
				'users/' . htmlspecialchars($keys[1]) . '">' .
				htmlspecialchars($story->friend_names[$keys[1]]) .
				'</a>, <a href="' . $this->config->application->website .
				'users/' . htmlspecialchars($keys[2]) . '">' .
				htmlspecialchars($story->friend_names[$keys[2]]) . '</a> and 1 other';
		}
		else if ($num_friends > 4)
		{
			$description = '<a href="' . $this->config->application->website .
				'users/' . htmlspecialchars($keys[0]) . '">' .
				htmlspecialchars($story->friend_names[$keys[0]]) .
				'</a>, <a href="' . $this->config->application->website .
				'users/' . htmlspecialchars($keys[1]) . '">' .
				htmlspecialchars($story->friend_names[$keys[1]]) .
				'</a>, <a href="' . $this->config->application->website .
				'users/' . htmlspecialchars($keys[2]) . '">' .
				htmlspecialchars($story->friend_names[$keys[2]]) . '</a> and' .
				($num_friends - 3) . 'others';
		}

		$description .= ' ' . $interaction . ' on this item';
		return $description;
	}

	private function format_interaction_description($story, $interaction)
	{
		$num_friends = count($story->friend_names);

		if ($num_friends == 1)
		{
			$description = '<b>' . htmlspecialchars($story->friend_names[0]) .
				'</b>';
		}
		else if ($num_friends == 2)
		{
			$description = '<b>' . htmlspecialchars($story->friend_names[0]) .
				'</b> and <b>' . htmlspecialchars($story->friend_names[1]) . '</b>';
		}
		else if ($num_friends == 3)
		{
			$description = '<b>' . htmlspecialchars($story->friend_names[0]) .
				'</b>, <b>' . htmlspecialchars($story->friend_names[1]) .
				'</b>, and <b>' . htmlspecialchars($story->friend_names[2]) . '</b>';
		}
		else if ($num_friends == 4)
		{
			$description = '<b>' . htmlspecialchars($story->friend_names[0]) .
				'</b>, <b>' . htmlspecialchars($story->friend_names[1]) .
				'</b>, <b>' . htmlspecialchars($story->friend_names[2]) .
				'</b> and 1 other';
		}
		else if ($num_friends > 4)
		{
			$description = '<b>' . htmlspecialchars($story->friend_names[0]) .
				'</b>, <b>' . htmlspecialchars($story->friend_names[1]) .
				'</b>, <b>' . htmlspecialchars($story->friend_names[2]) .
				'</b> and ' . ($num_friends - 3) . ' others';
		}

		$description .= ' ' . $interaction . ' on <b>' . $story->item_name . '</b>';
		return $description;
	}
}

?>
