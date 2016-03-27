<div class="item_section">
	<div class="item_inner">
		<div class="btn-group">
			<button class="selection_button" data-toggle="dropdown">
				<span>Sort By<i class="util_images dropdown_tick"></i></span>
			</button>
			<ul class="dropdown-menu" role="menu">
				{% if handler != "all" %}
				<li>
					<a href="{{ config.application.website }}{{ handler|e }}/relevance/{{ filter_by|e }}/1">Relevance</a>
				</li>
				{% endif %}
				<li>
					<a href="{{ config.application.website }}{{ handler|e }}/newest/{{ filter_by|e }}/1">Time added (newest - oldest)</a>
				</li>
				<li>
					<a href="{{ config.application.website }}{{ handler|e }}/oldest/{{ filter_by|e }}/1">Time added (oldest - newest)</a>
				</li>
				<li>
					<a href="{{ config.application.website }}{{ handler|e }}/highest-price/{{ filter_by|e }}/1">Price (highest - lowest)</a>
				</li>
				<li>
					<a href="{{ config.application.website }}{{ handler|e }}/lowest-price/{{ filter_by|e }}/1">Price (lowest - highest)</a>
				</li>
			</ul>
		</div>
		<div class="btn-group">
			<button class="selection_button" data-toggle="dropdown">
				<span>Filter By<i class="util_images dropdown_tick"></i></span>
			</button>
			<ul class="dropdown-menu" role="menu">
				<li>
					<a href="{{ config.application.website }}{{ handler|e }}/{{ sort_by|e }}/none/1">None</a>
				</li>
				{% for category in categories %}
				<li>
					<a href="{{ config.application.website }}{{ handler|e }}/{{ sort_by|e }}/{{ category.category|lower|e }}/1">{{ category.category|e }}</a>
				</li>
				{% endfor %}
			</ul>
		</div>
		<span class="all_num_items">{{ item_page.total_items }} items</span>
	</div>
</div>
{% for item in item_page.items %}
<div class="item_section">
	<div class="item_inner" style="overflow:auto">
			{{ partial('partials/story_middle', ['story': item]) }}
	</div>
</div>
{% endfor %}
{% if item_page.total_pages > 1 %}
<div class="item_section">
	<div class="item_inner">
		{% if item_page.current != 1 %}
		<a href="{{ config.application.website }}{{ handler|e }}/{{ sort_by|e }}/{{ filter_by|e }}/{{ item_page.current - 1 }}">&lt; previous</a>
		{% endif %}
		{% if item_page.current != item_page.total_pages %}
		<a href="{{ config.application.website }}{{ handler|e }}/{{ sort_by|e }}/{{ filter_by|e }}/{{ item_page.current + 1 }}" style="float:right">next &gt;</a>
		{% endif %}
		<div style="clear:both"></div>
	</div>
</div>
{% endif %}
