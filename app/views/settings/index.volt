{{ content() }}

<div class="success_flash nodisplay">
	<span class="check_wrap">
		<i class="util_images image_check"></i>
	</span>
	<span class="flash_content">Your account settings have been updated.</span>
</div>
<div class="error_flash nodisplay">
	<span class="x_wrap">
		<i class="util_images image_x"></i>
	</span>
	<span class="flash_content">There was an error updating your settings. Please try again later.</span>
</div>
<div class="item_section">
	<div class="form_header">
		<h3>Account</h3>
		<p>Edit your basic account settings</p>
	</div>
	<div class="form_inner">
		<form id="account_form" method="post" action="{{ config.application.website }}settings/">
			<div class="form_item">
				<label class="form_label" for="first_name">Name</label>
				<input class="form_textfield form_left" type="text" name="first_name" value="{{ first_name|e }}" disabled>
				<input class="form_textfield form_right" type="text" name="last_name" value="{{ last_name|e }}" disabled>
				<span class="form_help">This is the name that will be associated with your account and seen by other users.</span>
			</div>
			<div class="form_item">
				<label class="form_label" for="username">Username</label>
				<input id="username" class="form_textfield" type="text" name="username" value="{{ username|e }}">
				<span id="username_help" class="form_help">{{ config.application.website }}user/{{ username|e }}</span>
				<span id="username_error" class="form_error nodisplay"></span>
			</div>
			<div class="form_item">
				<label class="form_label" for="email">Email</label>
				<input id="email" class="form_textfield" type="text" name="email" value="{{ email|e }}">
				<span class="form_help">This email address will be used for all email notifications.</span>
				<span id="email_error" class="form_error nodisplay"></span>
			</div>
			<div class="form_item">
				<input id="account_submit" type="submit" value="Save Changes" class="form_submit btn btn-primary" disabled>
			</div>
		</form>
	</div>
</div>
<div class="item_section">
	<div class="form_header">
		<h3>Notifications</h3>
		<p>Edit your email and web notification settings</p>
	</div>
</div>
