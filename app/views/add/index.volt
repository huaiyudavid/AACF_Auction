{{ content() }}

<div class="item_section">
	<div class="form_header">
		<h3>Add Item</h3>
		<p>Add an item to the auction.</p>
	</div>
	<div class="form_inner">
		<form id="add_form" method="post" action="{{ config.application.website }}add/">
			<div class="form_item">
				<label class="form_label" for="name">Name</label>
				<input id="name" class="form_textfield" type="text" name="name">
				<span id="name_error" class="form_error nodisplay"></span>
			</div>
			<div class="form_item">
				<label class="form_label" for="picture">Picture</label>
				<div class="generated_image">
					<img id="image_preview" class="user_image" height="100" width="100" src>
					<button id="remove_generated_image" class="util_images delete_tick remove_generated_image"></button>
				</div>
				<div class="notification_details">
					<div class="btn btn-default upload_file_button">
						<span>Upload picture</span>
						<input id="picture" class="form_file upload_file_input" type="file" name="picture">
					</div>
					<span class="form_help">If no picture is specified, the default picture of the category will be used.</span>
					<span id="picture_error" class="form_error nodisplay"></span>
				</div>
			</div>
			<div class="form_item">
				<label class="form_label" for="description">Description</label>
				<textarea id="description" class="form_textarea" name="description"></textarea>
				<span id="description_error" class="form_error nodisplay"></span>
			</div>
			<div class="form_item">
				<div class="form_mid">
					<label class="form_label" for="category">Category</label>
					<select class="form_select" name="category" id="category">
						{%for category in categories%}
							<option value={{category.id}}>{{category.category}}</option>
						{%endfor%}
					</select>
					<span id="category_error" class="form_error nodisplay"></span>
				</div>
			</div>
			<div class="form_item">
				<div class="form_left">
					<label class="form_label" for="starting_price">Starting Price</label>
					<input id="starting_price" class="form_textfield" type="text" name="starting_price">
					<span class="dollar_sign">$</span>
					<span id="starting_price_error" class="form_error nodisplay"></span>
				</div>
				<div class="form_right">
					<label class="form_label" for="increment">Increment</label>
					<input id="increment" class="form_textfield" type="text" name="increment">
					<span class="dollar_sign">$</span>
					<span id="increment_error" class="form_error nodisplay"></span>
				</div>
			</div>
			<input id="add_submit" type="submit" value="Add Item" class="form_submit btn btn-primary">
		</form>
	</div>
</div>
