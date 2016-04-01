<nav class="navbar navbar-inverse navbar-fixed-top navigation_bar" role="navigation">
	<div class="content_container header_bar">
		<div class="navbar-header home_logo">
			<a href="{{ config.application.website }}">
				<img src="{{ config.application.website }}images/aacf.png" alt="AIVCF Auction" height="30">
			</a>
		</div>
		<div class="header_item_persist">
			<a class="header_item_text" href="{{ config.application.website }}login">Log In</a>
		</div>
	</div>
</nav>
<div class="content_container">
	{{ content() }}
</div>
