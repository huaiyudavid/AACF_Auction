<nav class="navbar navbar-inverse navbar-fixed-top navigation_bar" role="navigation">
	<div class="content_container header_bar">
		<div class="navbar-header home_logo">
			<a href="{{ config.application.website }}">
				<img src="{{ config.application.website }}images/aacf.png" alt="AIVCF Auction" height="30">
			</a>
		</div>
		<div class="dropdown navbar-right profile_dropdown">
			<button id="user_dropdown" class="dropdown-toggle profile_link" data-toggle="dropdown">
				<img class="profile_picture" src="{{ session.user.profile_small|e }}" width="30" height="30">
			</button>
			<ul class="dropdown dropdown-menu">
				<li>
					<a id="dropdown_profile" href="{{ config.application.website }}users/{{ session.user.username|e }}">{{ session.user.first_name|e }} {{ session.user.last_name|e }}</a>
				</li>
				<li>
					<a id="dropdown_add_item" href="{{ config.application.website }}add">Add Item</a>
				</li>
				<li>
					<a id="dropdown_all_items" href="{{ config.application.website }}all/newest/none/1">Esther's Items</a>
				</li>
				<li>
					<a id="dropdown_settings" href="{{ config.application.website }}settings">Settings</a>
				</li>
				<li>
					<form method="post" action="{{ config.application.website }}logout">
						<input type="hidden" name="dt" value="{{ session.token|e }}" autocomplete="off">
						<label class="submit_label">
							<input class="submit_input" type="submit" value="Log Out">
						</label>
					</form>
				</li>
			</ul>
		</div>
		<div class="dropdown notification_dropdown">
			<button id="notification_dropdown" class="dropdown-toggle profile_link" data-toggle="dropdown">
				<div id="unread_notifications" class="unread_indication nodisplay">
					<span id="num_unread_notifications"></span>
				</div>
				<i class="util_images image_notification"></i>
			</button>
			<div class="dropdown dropdown-menu">
				<div>
					<form id="read_all_notifications_form" method="POST" action="{{ config.application.website }}ajax/read_all_notifications">
						<input type="submit" class="button_link" value="Mark all as read">
					</form>
				</div>
				<ul id="notifications_list">
				</ul>
			</div>
		</div>
		<div class="header_item">
			<a class="header_item_text" href="{{ config.application.website }}add">Add Item</a>
		</div>
		<div class="header_item">
			<a class="header_item_text" href="{{ config.application.website }}all/newest/none/1">All Items</a>
		</div>
		<div class="search_bar">
			<div class="search_bar_outline dropdown">
				<div id="search_dropdown" class="dropdown-toggle" data-toggle="dropdown">
					<form id="search_form" method="GET" action="{{ config.application.website }}">
						<input type="submit" class="util_images search_submit" value>
						<div style="margin-right:30px">
							<input id="search" type="text" class="search_input" name="search" placeholder="Search Auction" autocomplete="off" {% if search_query is defined %}value="{{ search_query|e }}"{% endif %}>
						</div>
					</form>
				</div>
				<div id="search_typeahead_dropdown" class="dropdown dropdown-menu">
					<ul id="search_typeahead_list">
					</ul>
				</div>
			</div>
		</div>
	</div>
</nav>
<div class="content_container">
	<div id="recommendations_section">
		<div id="recommendations_fixed">
		</div>
	</div>
	<div id="main_section">
		{{ content() }}
	</div>
</div>
