{% for comment in comments %}
	<div class="comments_row">
		<div class="user_image">
			<img width="40" height="40" src="{{ comment.user_image|e }}">
		</div>
		<div class="notification_details">
			<a href="{{ config.application.website }}users/{{ comment.username|e }}">{{ comment.first_name|e }} {{ comment.last_name|e }}</a>
			<span class="comments_time gray" data-unix="{{ comment.timestamp|e }}">{{ utils.convert_time(comment.timestamp) }}</span>
			<span class="item_line">{{ comment.text|e }}</span>
		</div>
	</div>
{% endfor %}

