<div class="item_image">
	<a href="{{ config.application.website }}item/{{ story.item_id|e }}">
		<img width="150" height="150" src="{{ config.application.website }}images/items/{{ story.item_image|e }}">
	</a>
</div>
<div class="all_item_details">
	<h5><a href="{{ config.application.website }}item/{{ story.item_id|e }}">{{ story.item_name|e }}</a></h5>
	<span class="item_line item_user">By <a href="{{ config.application.website }}users/{{ story.item_creator_username|e }}">{{ story.item_creator_first_name|e }} {{ story.item_creator_last_name|e }}</a></span>
	<div class="item_highlights">
		<div class="item_highlight{% if user_bid is defined %}_three{% endif %}">
			<span class="item_line item_highlight_name">CURRENT BID</span>
			<span class="item_line item_highlight_value">${% if story.max_bid is empty %}{{ story.item_starting_price }}{% else %}{{ story.max_bid|e }}{% endif %}</span>
		</div>
		{% if user_bid is defined %}
		<div class="item_highlight_three">
			<span class="item_line item_highlight_name">{% if me %}YOUR BID{% else %}{{ first_name|e|upper }}'S BID{% endif %}</span>
			<span class="item_line item_highlight_value">${{ story.user_bid|e }}</span>
		</div>
		{% endif %}
		<div class="item_highlight{% if user_bid is defined %}_three{% endif %}">
			<span class="item_line item_highlight_name">BIDS</span>
			<span class="item_line item_highlight_value">{% if story.num_bids is empty %}0{% else %}{{ story.num_bids|e }}{% endif %}</span>
		</div>
	</div>
</div>
