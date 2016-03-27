<div class="user_section">
	<div class="item_image">
		<a href="{{ config.application.website }}users/{{ username|e }}">
			<img width="150" height="150" src="{{ prof_pic_url }}" style="object-fit:cover">
		</a>
	</div>

	<div class="all_user_details">
		<h3><a href="{{ config.application.website }}users/{{ username|e }}">{{ first_name|e }} {{ last_name|e }}</a></h3>
		<span class="item_line item_user"By <a href="{{ config.application.website }}users/{{ username|e }}">{{ username|e }}</a></span>
		<div class="user_highlights">
			<div class="user_highlight">
				<span class="item_line item_highlight_name">ITEMS</span>
				<span class="item_line item_highlight_value"> {{ num_items|e }}</span>
			</div>
			<div class="user_highlight">
				<span class="item_line item_highlight_name">BIDS</span>
				<span class="item_line item_highlight_value">{{ num_bids|e }}</span>
			</div>

			<div class="user_highlight">
				<span class="item_line item_highlight_name">HIGHEST BID</span>
				<span class="item_line item_highlight_value">${{ highest_bid|e }}</span>
			</div>
		</div>
	</div>
</div>
