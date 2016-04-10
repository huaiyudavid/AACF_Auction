{{ content() }}
<div class="success_flash nodisplay">
	<span class="check_wrap">
		<i class="util_images image_check"></i>
	</span>
	<span id="success_flash" class="flash_content"></span>
</div>
<div class="error_flash nodisplay">
	<span class="x_wrap">
		<i class="util_images image_x"></i>
	</span>
	<span id="error_flash" class="flash_content"></span>
</div>
<div class="item_section">
	<div class="item_inner">
		<div style="overflow:auto">
			<div class="item_image">
				<img height="200" width="200" src="{{ config.application.website }}images/items/{{ item.filename|e }}">
			</div>
			<div class="item_details">
				<h4 class="item_title">{{ item.name|e }}</h4>
				<span class="item_line item_user">By <a href="{{ config.application.website }}users/{{ item.username|e }}">{{ item.first_name|e }} {{ item.last_name|e }}</a></span>
				<hr>
				<div style="overflow:auto">
					<div style="float:left">
						<span class="item_line">Current Bid: <span class="item_price">$<span id="price">{{ current_price|e }}</span></span></span>
						<span class="item_line" style="color: #858585"><span id="bid_count">{{ bid_count|e }}</span> Bids</span>
					</div>
					<div style="display:table-cell; width:10000px">
						<!-- <form id="bid_form" method="post" action="{{ config.application.website }}item/{{ item.id|e }}" style="float:right">
							<input id="user_id" type="hidden" name="user_id" value="{{ logged_in_user_id|e }}">
							<input id="item_id" type="hidden" name="item_id" value="{{ item.id|e }}">
							<div style="position:relative">
								<input id="bid" class="form_textfield item_bid" type="text" name="bid" placeholder="Your Bid">
								<span style="position:absolute; left:5px; top:8px">$</span>
								<input type="submit" class="btn btn-primary" value="Place Bid">
								<span id="min_bid_line" class="form_help">Minimum Bid: $<span id="min_bid">{{ min_bid|e }}</span></span>
							</div>
						</form> -->
					</div>
				</div>
				<hr>
				<div>
					<span id="on_watchlist" class="green{% if is_on_watchlist is false %} nodisplay{% endif %}">On watchlist</span>
					<form id="watchlist_form" {% if is_on_watchlist is true %}class="nodisplay" {% endif %}method="post" action="{{ config.application.website }}ajax/watchlist">
						<input type="submit" class="link" value="Add to watchlist">
					</form>
				</div>
				<div>
					{%if logged_in_user_id == item.creator_id%}
						<a class="link" href="{{config.application.website}}edit/{{item.id|e}}">Edit Item</a>
					{%endif%}
				</div>
			</div>
		</div>
		<hr>
		<h6 class="item_title">Description</h6>
		<div class="item_description">
			<p>{{ item.description|e|nl2br }}</p>
		</div>
	</div>
</div>
<div class="item_section">
	<div class="item_inner">
		<h4 class="item_title">Bid History</h4>
		<div id="item_bids" style="margin-top:5px;">
			{{ partial('partials/bids', ['bids': bids]) }}
		</div>
	</div>
</div>
<div class="item_section" style="margin:0">
	<div class="item_inner">
		<h4 class="item_title">Comments</h4>
		<div id="item_comments" style="margin-top:5px;">
			{{partial('partials/comments', ['comments':comments]) }} 
		</div>
		<form id="add_comment" method="post" action="{{ config.application.website }}item/{{ item.id|e }}">
			<div class="user_image">
				<img width="40" height="40" src="{{ logged_in_user_image|e }}">
			</div>
			<input id="user_id" type="hidden" name="user_id" value="{{ logged_in_user_id|e }}">
			<input id="item_id" type="hidden" name="item_id" value="{{ item.id|e }}">
			<div class="notification_details">
				<input id="comment" class="comment_textfield" type="text" name="comment" placeholder="Comment...">
			</div>
			<input type="submit" class="nodisplay" style="float:right; height=5px; width=7px;" value="Comment">
		</form>
	</div>
</div>
