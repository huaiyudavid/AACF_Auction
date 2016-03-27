var signup_form = signup_form || {};
signup_form.username_focusout = function()
{
	$('#username_help').text(website + 'user/' + $('#username').val());
	$.post(website + 'ajax/check_username/',
		{at: $.cookie('a'), username: $('#username').val()})
		.done(function(data, textStatus, a)
		{
			var response = parseResponse(data);
			if (response['valid'] == false)
			{
				$('#username').addClass('redborder');
				$('#username_error').text(response['username']);
				$('#username_error').removeClass('nodisplay');
			}
			else
			{
				$('#username').removeClass('redborder');
				$('#username_error').addClass('nodisplay');
			}
		}).fail(function(a, textStatus, errorThrown)
		{
		});
};
signup_form.email_focusout = function()
{
	$.post(website + 'ajax/check_email/',
		{at: $.cookie('a'), email: $('#email').val()})
		.done(function(data, textStatus, a)
		{
			var response = parseResponse(data);
			if (response['valid'] == false)
			{
				$('#email').addClass('redborder');
				$('#email_error').text(response['email']);
				$('#email_error').removeClass('nodisplay');
			}
			else
			{
				$('#email').removeClass('redborder');
				$('#email_error').addClass('nodisplay');
			}
		}).fail(function(a, textStatus, errorThrown)
		{
		});
};
signup_form.submit = function()
{
	$.post(website + 'ajax/signup/',
		{at: $.cookie('a'), username: $('#username').val(), email: $('#email').val()})
		.done(function(data, textStatus, a)
		{
			var response = parseResponse(data);
			if (response['valid'] == true)
				window.location.href = website;
			else
			{
				if (response['username'])
				{
					$('#username_error').text(response['username']);
					$('#username_error').removeClass('nodisplay');
					$('#username').addClass('redborder');
				}
				else
				{
					$('#username_error').addClass('nodisplay');
					$('#username').removeClass('redborder');
				}

				if (response['email'])
				{
					$('#email_error').text(response['email']);
					$('#email_error').removeClass('nodisplay');
					$('#email').addClass('redborder');
				}
				else
				{
					$('#email_error').addClass('nodisplay');
					$('#email').removeClass('redborder');
				}
			}
		}).fail(function(a, textStatus, errorThrown)
		{
		});
	return false;
};

$(document).ready(function ()
{
	$(document).on('focusout', 'input', function ()
	{
		this.id && window[this.form.id][this.id + '_focusout'] &&
			window[this.form.id][this.id + '_focusout']();
	});
	$(document).on('submit', 'form', function ()
	{
		if (this.id && window[this.id]['submit'])
			return window[this.id]['submit']();
		return true;
	});
});
