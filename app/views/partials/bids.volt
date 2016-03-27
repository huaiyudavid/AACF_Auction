{% for bid in bids %}
	<div class="item_bid_history_row">
		<div class="item_bid_history_name">
			<a class="center_left" href="{{ config.application.website }}users/{{ bid.username|e }}">{{ bid.first_name|e }} {{ bid.last_name|e }}</a>
		</div>
		<div class="item_bid_history_time">
			<span class="center_center bid_time" data-unix="{{ bid.timestamp|e }}">{{ utils.convert_time(bid.timestamp) }}</span>
		</div>
		<div class="item_bid_history_price">
			<span class="center_right" style="font-weight:bold">${{ bid.amount|e }}</span>
		</div>
	</div>
{% endfor %}
