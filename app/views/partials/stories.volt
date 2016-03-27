{% if stories is empty %}
	{{partial('partials/empty_newsfeed')}}
{% else %}
	{% for story in stories %}
		<div class="item_section">
			<div class="item_inner">
				<div class="story_top">
					<span>{{ utils.get_newsfeed_story_description(story) }}</span>
				</div>
				<div class="story_middle">
					{{ partial('partials/story_middle', ['story': story]) }}
				</div>
				{% if story.action_type is 'bid' %}
				<div class="story_bottom">
					{{ partial('partials/bids', ['bids': story.bids]) }}
				</div>
				{% elseif story.action_type is 'comment' %}
				<div class="story_bottom">
					<div style="margin-top:5px">
						{{ partial('partials/comments', ['comments': story.comments]) }}
					</div>
				</div>
				{% endif %}
			</div>
		</div>
	{% endfor %}
{% endif %}
