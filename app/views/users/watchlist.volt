{{ content() }}

{{ partial('partials/user_info', ['username': username, 'prof_pic_url': prof_pic_url, 'first_name': first_name, 'last_name': last_name, 'num_items': num_items, 'num_bids': num_bids, 'highest_bid': highest_bid]) }}
<div class="tab_section">
		<a class="tab" href="{{ config.application.website }}users/{{ username|e }}">Items</a>
		<a class="tab tab_border" href="{{ config.application.website }}users/{{ username|e }}/bids">Bids</a>
		<a class="tab tab_border tab_selected" href="{{ config.application.website }}users/{{ username|e }}/watchlist">Watchlist</a>
</div>
{% for item in watchlist %}
<div class="item_section">
	<div class="item_inner" style="overflow:auto">
		{{ partial('partials/story_middle', ['story': item]) }}
	</div>
</div>
{% endfor %}
