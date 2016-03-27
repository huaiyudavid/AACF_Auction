{% for notification in notifications %}
	<li class="notification_row{% if not notification.is_read %} light_blue_background{% endif %}" data-id="{{ notification.id|e }}">
		<a class="notification_link" href="{{ config.application.website }}item/{{ notification.item_id|e }}">
			<div class="user_image">
				<img height="50" width="50" src="{{ utils.get_story_picture(notification) }}">
			</div>
			<div class="notification_details">
				<span class="item_line">{{ utils.get_story_description(notification) }}</span>
				<span id="notification_timestamp" class="item_line gray" data-unix="{{ notification.timestamp|e }}">{{ utils.convert_time(notification.timestamp) }}</span>
			</div>
		</a>
	</li>
{% endfor %}
