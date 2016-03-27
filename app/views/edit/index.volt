{{ content() }}

<div class="item_section">
	<div class="form_header">
		<h3>Edit Item</h3>
		<p>Edit your item</p>
	</div>
	<div class="form_inner">
		<form id="edit_form" method="post" action="{{ config.application.website }}edit/">
			<div class="form_item">
				<label class="form_label" for="name">Name</label>
				<input id="name" class="form_textfield" type="text" name="name" value="{{item_name | e}}">
				<span id="name_error" class="form_error nodisplay"></span>
			</div>
			<div class="form_item">
				<label class="form_label" for="description">Description</label>
				<textarea id="description" class="form_textarea" name="description">{{description|e}}</textarea>
				<span id="description_error" class="form_error nodisplay"></span>
			</div>
			<div class="form_item">
				<div class="form_mid">
					<label class="form_label" for="category">Category</label>
					<select class="form_select" name="category" id="category">
						{%for category in categories%}
							<option value={{category.id}} {% if category.id == selected_category %}selected{% endif %}>{{category.category}}</option>
						{%endfor%}
					</select>
					<span id="category_error" class="form_error nodisplay"></span>
				</div>
			</div>
			<div class="form_item">
				<div class="form_right">
					<label class="form_label" for="increment">Increment</label>
					<input id="increment" class="form_textfield" type="text" name="increment" value={{increment|e}}>
					<span class="dollar_sign">$</span>
					<span id="increment_error" class="form_error nodisplay"></span>
				</div>
			</div>
			<input id="item_id" type="hidden" value="{{item_id|e}}">
			<input id="edit_submit" type="submit" value="Edit Item" class="form_submit btn btn-primary">
		</form>
	</div>
</div>
