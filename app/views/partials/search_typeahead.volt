{% if user_page|length != 0 %}
<li class="search_typeahead_section_header">
	<span>People</span>
</li>
{% for user in user_page %}
	<li class="notification_row">
		<a class="notification_link" href="{{ config.application.website }}users/{{ user.username|e }}">
			<div class="user_image">
				<img height="50" width="50" src="{{ user.user_image|e }}">
			</div>
			<div class="notification_details">
				<span class="item_line center_left">{{ user.first_name|e }} {{ user.last_name|e }}</span>
			</div>
		</a>
	</li>
{% endfor %}
{% endif %}
{% if item_page.items|length != 0 %}
<li class="search_typeahead_section_header">
	<span>Items</span>
</li>
{% for item in item_page.items %}
	<li class="notification_row">
		<a class="notification_link" href="{{ config.application.website }}item/{{ item.item_id|e }}">
			<div class="user_image">
				<img height="50" width="50" src="{{ config.application.website }}images/items/{{ item.item_image|e }}">
			</div>
			<div class="notification_details">
				<span class="item_line center_left">{{ item.item_name|e }}</span>
			</div>
		</a>
	</li>
{% endfor %}
{% endif %}
