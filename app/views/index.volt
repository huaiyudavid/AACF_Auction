<!DOCTYPE html>
<html>
  <head>
		<meta charset="utf-8">
    {{ get_title() }}
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<link rel="shortcut icon" href="{{ config.application.website }}images/icon.ico">
		<link rel="stylesheet" type="text/css" href="{{config.application.website}}css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="{{config.application.website}}css/perfect-scrollbar.min.css">
		<link rel="stylesheet" type="text/css" href="{{config.application.website}}css/app.css">
  </head>
  <body>
    {{ content() }}
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
		<script type="text/javascript" src="{{ config.application.website }}js/jquery.cookie.js"></script>
		<script type="text/javascript" src="{{ config.application.website }}js/bootstrap.min.js"></script>
		<script type="text/javascript" src="{{ config.application.website }}js/perfect-scrollbar.jquery.min.js"></script>
		<script type="text/javascript" src="{{ config.application.website }}js/utils.js"></script>
		{% if session.user is defined %}
		<script type="text/javascript" src="{{ config.application.website }}js/user.js"></script>
		{% else %}
		<script type="text/javascript" src="{{ config.application.website }}js/guest.js"></script>
		{% endif %}
	</body>
</html>
