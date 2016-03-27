{{ content() }}

{% if not has_entries %}
<div class="item_section">
	<div class="item_inner error">
		<h4 class="item_title">No Items Found</h4>
		<span class="gray">No items matched the search parameters entered. Please try a different search or look through all items to find what you are looking for.</span>
	</div>
</div>
{% else %}
{% if user_page|length != 0 %}
<div class="item_section">
	<div class="item_inner">
		<h6>People</h6>
	</div>
</div>
{{ partial('partials/users_list', ['user_page': user_page]) }}
{% endif %}
{% if item_page.items|length != 0 %}
<div class="item_section">
	<div class="item_inner">
		<h6>Items</h6>
	</div>
</div>
{% set handler = 'search/' ~ search_query|url_encode|e %}
{{ partial('partials/items_list', ['item_page': item_page, 'sort_by': sort_by,
	'filter_by': filter_by, 'categories': categories, 'handler': handler]) }}
{% endif %}
{% endif %}
