<div class="recommendation_header">
	<h5>   Items recommended for you!   </h5>
</div>
{% for recommendation in recommendations %}
	<div class="recommend_item">
		<div class="recommend_inner" style="overflow:auto" >
			<div class="item_image">
				<a href="{{ config.application.website }}item/{{ recommendation.item_id|e }}">
					<img width="80" height="80" src="{{ config.application.website }}images/items/{{ recommendation.item_image|e }}">
				</a>
			</div>
			<div>
				<h6><a href="{{ config.application.website }}item/{{ recommendation.item_id|e }}">{{ recommendation.item_name|e }}</a></h6>
				<span class="item_line item_user">By <a href="{{ config.application.website }}users/{{ recommendation.username|e }}">{{ recommendation.first_name|e }} {{ recommendation.last_name|e }}</a></span>
				<div class="item_highlight">
					<span class="item_line item_highlight_name">CURRENT BID</span>
					<span class="item_line item_highlight_value" style="font-size:20px">${{recommendation.max_bid|e }}</span>
				</div>

			</div>
		</div>
	</div>
{% endfor %}
