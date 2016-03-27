{% for user in user_page %}
<div class="item_section">
	<div class="item_inner" style="overflow:auto">
		<div class="item_image">
			<a href="{{ config.application.website }}users/{{ user.username|e }}">
				<img width="75" height="75" src="{{ user.user_image|e }}">
			</a>
		</div>
		<div class="all_item_details">
			<h5><a href="{{ config.application.website }}users/{{ user.username|e }}">{{ user.first_name|e }} {{ user.last_name|e }}</a></h5>
			<span class="item_line item_user">{{ user.username|e }}</span>
		</div>
	</div>
</div>
{% endfor %}
