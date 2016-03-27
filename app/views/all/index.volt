{{ content() }}

{% if not has_entries %}
<div class="item_section">
	<div class="item_inner error">
		<h4 class="item_title">No Items Found</h4>
		<span class="gray">No items matched the filters given. Please try different filters or add an item with these filters!</span>
	</div>
</div>
{% else %}
{% set handler = 'all' %}
{{ partial('partials/items_list', ['item_page': item_page, 'sort_by': sort_by,
	'filter_by': filter_by, 'categories': categories, 'handler': handler]) }}
{% endif %}
