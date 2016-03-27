{{ content() }}

<div class="signup_section">
	<div class="item_section">
		<div class="form_header">
			<h3>Account Setup</h3>
			<p>It looks like this is the first time you logged in! Please take a few moments to set up your account.</p>
		</div>
		<div class="form_inner">
			<form id="signup_form" method="post" action="{{ config.application.website }}signup/">
				<div class="form_item">
					<label class="form_label" for="first_name">Name</label>
					<input class="form_textfield form_left" type="text" name="first_name" value="{{ first_name|e }}" disabled>
					<input class="form_textfield form_right" type="text" name="last_name" value="{{ last_name|e }}" disabled>
					<span class="form_help">This is the name that will be associated with your account and seen by other users.</span>
				</div>
				<div class="form_item">
					<label class="form_label" for="username">Username</label>
					<input id="username" class="form_textfield" type="text" name="username">
					<span id="username_help" class="form_help">{{ config.application.website }}user/</span>
					<span id="username_error" class="form_error nodisplay"></span>
				</div>
				<div class="form_item">
					<label class="form_label" for="email">Email</label>
					<input id="email" class="form_textfield" type="text" name="email" value="{{ email|e }}">
					<span class="form_help">This email address will be used for all email notifications.</span>
					<span id="email_error" class="form_error nodisplay"></span>
				</div>
				<div class="form_item">
					<input type="submit" value="Submit" class="form_submit btn btn-primary">
				</div>
			</form>
		</div>
	</div>
</div>
