function check_input(url, ids, file_reader, callback, callback_all)
{
	var args = {at: $.cookie('a')};
	var input_length = ids.length;
	for (var i = 0; i < input_length; ++i)
		args[ids[i]] = $('#' + ids[i]).val();
	if (file_reader) {
		args['file'] = file_reader.result;
	}
	$.post(url, args)
		.done(function(data, textStatus, a)
		{
			var response = parseResponse(data);
			if (response['valid'] == false)
			{
				for (var i = 0; i < input_length; ++i)
				{
					if (response[ids[i]])
					{
						$('#' + ids[i]).addClass('redborder');
						$('#' + ids[i] + '_error').text(response[ids[i]]);
						$('#' + ids[i] + '_error').removeClass('nodisplay');
					}
					else
					{
						$('#' + ids[i]).removeClass('redborder');
						$('#' + ids[i] + '_error').addClass('nodisplay');
					}
				}
			}
			else
			{
				for (var i = 0; i < input_length; ++i)
				{
					$('#' + ids[i]).removeClass('redborder');
					$('#' + ids[i] + '_error').addClass('nodisplay');
				}
				(typeof(callback) == 'function') && callback(response);
			}
			(typeof(callback_all) == 'function') && callback_all(response);
		}).fail(function(a, textStatus, errorThrown)
		{
			console.log(textStatus);
			console.log(errorThrown);
		});
}

function convert_time(created)
{
	var current = new Date();
	var current_unix = Math.floor(current.getTime() / 1000) -
		current.getTimezoneOffset() * 60;
	var past = new Date(created * 1000);
	var past_unix = Math.floor(past.getTime() / 1000) -
		past.getTimezoneOffset() * 60;
	var difference = current_unix - past_unix;

	if (difference >= 46800)
	{
		var current_day = Math.floor(current_unix / 86400);
		var past_day = Math.floor(past_unix / 86400);
		var hour = past.getHours() % 12;
		if (hour == 0)
			hour = 12;
		var ampm = past.getHours() >= 12 ? 'pm' : 'am';
		var minutes = past.getMinutes() >= 10 ?
			past.getMinutes() : '0' + past.getMinutes();
		if (current_day == past_day)
			return 'Today at ' + hour + ':' + minutes + ampm;
		if (current_day - past_day == 1)
			return 'Yesterday at ' + hour + ':' + minutes + ampm;

		var current_year = current.getYear();
		var past_year = past.getYear();
		var months = ['January', 'February', 'March', 'April', 'May', 'June',
			'July', 'August', 'September', 'October', 'November', 'December'];
		var month = months[past.getMonth()];
		if (current_year == past_year)
			return month + ' ' + past.getDate() + ' at ' + hour + ':' + minutes + ampm;
		return month + ' ' + past.getDate() + ', ' + past_year + ' at ' +
			hour + ':' + minutes + ampm;
	}
	else
	{
		if (difference < 60)
			return 'Less than a minute ago';
		if (difference < 3600)
		{
			return Math.floor(difference / 60) == 1 ? '1 min ago' :
				Math.floor(difference / 60) + ' mins ago';
		}
		return Math.floor(difference / 3600) == 1 ? '1 hr ago' :
			Math.floor(difference / 3600) + ' hrs ago';
	}
}

function get_content(url)
{
	$.get(url)
		.done(function(data, textStatus, a)
		{
			clear_intervals();
			clear_timeouts();
			intervals.refresh_page = ((window[page_from_url(location.href)] &&
				window[page_from_url(location.href)]['refresh']) ?
				setInterval(window[page_from_url(location.href)]['refresh'], 60000) :
				null);

			var parsed = $(data);
			document.title = parsed.filter('title').text();
			$('#search').val($('#search', parsed).val());
			$('#main_section').fadeOut(200, function()
			{
				window.scrollTo(0, 0);
				$('#main_section').html($('#main_section', parsed).html());
				$('#main_section').fadeIn(200);
			});

			$('form').each(function ()
			{
				this.id && window[this.id]['init'] && window[this.id]['init']();
			});
		}).fail(function(a, textStatus, errorThrown)
		{
		});
}

function get_hidden_name()
{
	if ('hidden' in document)
		return 'hidden';

	var prefix = ['webkit', 'moz', 'ms', 'o'];
	for (var i = 0; i < prefix.length; ++i)
	{
		if ((prefix[i] + 'Hidden') in document)
			return prefix[i] + 'Hidden';
	}
	return null;
}

function is_hidden()
{
	if (hidden_name)
		return document[hidden_name];
	return false;
}

function page_from_url(url)
{
	var end = url.indexOf('/', website.length);
	return 'page_' + ((end == -1) ? url.slice(website.length) :
		url.slice(website.length, end));
}

//=============================================================================
// Search Form
//=============================================================================
var search_form = search_form || {};
search_form.search_input = function()
{
	// come back here
	if (timeouts.search_typeahead)
	{
		clearTimeout(timeouts.search_typeahead);
		timeouts.search_typeahead = null;
	}

	timeouts.search_typeahead = setTimeout(function()
	{
		timeouts.search_typeahead = null;
		var args = { at: $.cookie('a'), query: $('#search').val() };
		$.post(website + 'ajax/search_typeahead', args)
			.done(function (data, text, a)
			{
				if (!$('#search_dropdown').parent().hasClass('open'))
					$('#search_dropdown').dropdown('toggle');

				var response = parseResponse(data);
				$('#search_typeahead_list').html(response['rows']);
			}).fail(function (a, text, error)
			{
			});
	}, 500);
};
search_form.submit = function()
{
	if (!$('#search').val())
		return false;

	var url = website + 'search/' + encodeURIComponent($('#search').val()) +
		'/relevance/none/1';
	history.pushState({}, '', url);
	get_content(url);
	return false;
};

//=============================================================================
// Account Form
//=============================================================================

var account_form = account_form || {};
account_form.old_username = $('#username').val();
account_form.old_email = $('#email').val();
account_form.init = function()
{
	this.old_username = $('#username').val();
	this.old_email = $('#email').val();
};
account_form.isChanged = function()
{
	if ($('#username').val() != this.old_username ||
		$('#email').val() != this.old_email)
	{
		$('#account_submit').prop('disabled', false);
		return true;
	}
	else
	{
		$('#account_submit').prop('disabled', true);
		return false;
	}
};
account_form.username_focusout = function()
{
	$('#username_help').text(website + 'user/' + $('#username').val());
	this.isChanged();
	check_input(website + 'ajax/check_username/', ['username'], null, null, null);
};
account_form.email_focusout = function()
{
	this.isChanged();
	check_input(website + 'ajax/check_email/', ['email'], null, null, null);
};
account_form.submit = function()
{
	if (!this.isChanged())
		return false;

	check_input(website + 'ajax/update_account/', ['username', 'email'], null,
		function()
		{
			$('#account_submit').prop('disabled', true);

			account_form.old_username = $('#username').val();
			account_form.old_email = $('#email').val();
			$('.success_flash').removeClass('nodisplay');
			timeouts.success = setTimeout(function()
			{
				timeouts.success = null;
				$('.success_flash').addClass('nodisplay');
			}, 3000);
		}, null);
	return false;
};

//=============================================================================
// Add Form
//=============================================================================

var add_form = add_form || {};
add_form.has_image = false;
add_form.picture = new FileReader();
add_form.picture.onloadend = function()
{
	$('#image_preview').attr('src', add_form.picture.result);
};
add_form.picture_change = function()
{
	if ($('#picture').val().length > 0)
	{
		var file = $('#picture')[0].files[0];
		if (file.type.match('image/jpeg') || file.type.match('image/png'))
		{
			$('#picture_error').addClass('nodisplay');
			$('#remove_generated_image').addClass('has_image');
			add_form.picture.readAsDataURL(file);
			add_form.has_image = true;
		}
		else
		{
			$('#picture_error').text('Please upload a jpeg or png image');
			$('#picture_error').removeClass('nodisplay');
			$('#remove_generated_image').removeClass('has_image');
			$('#image_preview').attr('src', null);
			add_form.has_image = false;
		}
		$('#picture').val('');
	}
};
add_form.name_focusout = function()
{
	check_input(website + 'ajax/check_item_name/', ['name'], null, null, null);
};
add_form.description_focusout = function()
{
	check_input(website + 'ajax/check_item_description/', ['description'], null,
		null, null);
};
add_form.starting_price_focusout = function()
{
	check_input(website + 'ajax/check_item_price/', ['starting_price'], null,
		null, null);
};
add_form.increment_focusout = function()
{
	check_input(website + 'ajax/check_item_increment/', ['increment'], null,
		null, null);
};
add_form.submit = function()
{
	$('#add_submit').prop('disabled', true);
	check_input(website + 'ajax/add_item/', ['name', 'description',
		'starting_price', 'increment', 'category'],
		add_form.has_image ? add_form.picture : null,
		function(response)
		{
			history.pushState({}, '', website + 'item/' + response['id']);
			get_content(website + 'item/' + response['id']);
		},
		function(response)
		{
			$('#add_submit').prop('disabled', false);
		});
	return false;
};

remove_generated_image_click = function()
{
	$('#remove_generated_image').removeClass('has_image');
	$('#image_preview').attr('src', null);
	add_form.has_image = false;
	return false;
};

//=============================================================================
// Edit Form
//=============================================================================

var edit_form = edit_form || {};
edit_form.name_focusout = function()
{
	check_input(website + 'ajax/check_item_name/', ['name'], null, null, null);
};
edit_form.description_focusout = function()
{
	check_input(website + 'ajax/check_item_description/', ['description'], null,
		null, null);
};
edit_form.increment_focusout = function()
{
	check_input(website + 'ajax/check_item_increment/', ['increment'], null,
		null, null);
};
edit_form.submit = function()
{
	$('#edit_submit').prop('disabled', true);
	check_input(website + 'ajax/edit_item/', ['name', 'description',
		 'increment', 'category', 'item_id', 'user_id'], null,
		function(response)
		{
			history.pushState({}, '', website + 'item/' + response['id']);
			get_content(website + 'item/' + response['id']);
		},
		function(response)
		{
			$('#edit_submit').prop('disabled', false);
		});
	return false;
};

//=============================================================================
// Item Page
//=============================================================================

var page_item = page_item || {};
page_item.refresh = function()
{	
	var args = { at: $.cookie('a'),
		last: $('#item_bids div:first-child .bid_time').data('unix'),
		item_id: $('#item_id').val() };
	$.post(website + 'ajax/refresh_item', args)
		.done(function(data, text, a)
		{
			var response = parseResponse(data);
			if (!response['latest'])
			{
				$('#item_bids').prepend(response['rows']);
				$('#price').text(response['price']);
				$('#bid_count').text(parseInt($('#bid_count').text()) +
					response['num_rows']);
				$('#min_bid').text(response['min_bid']);
			}
		}).fail(function(a, text, error)
		{
		});
};

//-----------------------------------------------------------------------------
// Bid Form
//-----------------------------------------------------------------------------

var bid_form = bid_form || {};
bid_form.submit = function()
{
	if ($('#bid').val() < parseInt($('#min_bid').text()))
	{
		$('#min_bid_line').addClass('red');
		$('#bid').addClass('redborder');
		return false;
	}

	$('#min_bid_line').removeClass('red');
	$('#bid').removeClass('redborder');

	var args = { at: $.cookie('a'), user_id: $('#user_id').val(),
		item_id: $('#item_id').val(), bid: $('#bid').val(),
		last: $('#item_bids div:first-child .bid_time').data('unix')};
	$('#bid').val('');
	$.post(website + 'ajax/place_bid', args)
		.done(function(data, text, a)
		{
			var response = parseResponse(data);
			if (response['valid'])
			{
				$('#item_bids').prepend(response['rows']);
				$('#price').text(response['price']);
				$('#bid_count').text(parseInt($('#bid_count').text()) +
					response['num_rows']);
				$('#min_bid').text(response['min_bid']);

				$('#success_flash').text(response['success']);
				$('.success_flash').removeClass('nodisplay');
				timeouts.success = setTimeout(function()
				{
					timeouts.success = null;
					$('.success_flash').addClass('nodisplay');
				}, 3000);
			}
			else
			{
				if ('rows' in response)
				{
					$('#item_bids').prepend(response['rows']);
					$('#price').text(response['price']);
					$('#bid_count').text(parseInt($('#bid_count').text()) +
						response['num_rows']);
					$('#min_bid').text(response['min_bid']);
				}

				$('#error_flash').text(response['fail']);
				$('.error_flash').removeClass('nodisplay');
				timeouts.error = setTimeout(function()
				{
					timeouts.error = null;
					$('.error_flash').addClass('nodisplay');
				}, 3000);
			}
		}).fail(function(a, text, error)
		{
		});
	return false;
};

//-----------------------------------------------------------------------------
// Watchlist Form
//-----------------------------------------------------------------------------

var watchlist_form = watchlist_form || {};
watchlist_form.submit = function()
{
	var args = { at: $.cookie('a'), user_id: $('#user_id').val(),
		item_id: $('#item_id').val() };
	$.post(website + 'ajax/watchlist', args)
		.done(function (data, text, a)
		{
			var response = parseResponse(data);
			if (response['valid'])
			{
				$('#on_watchlist').removeClass('nodisplay');
				$('#watchlist_form').addClass('nodisplay');
			}
		}).fail(function (a, text, error)
		{
		});
	return false;
};

//-----------------------------------------------------------------------------
// Comments 
//-----------------------------------------------------------------------------
var add_comment = add_comment || {};
add_comment.submit = function()
{
	if($('#comment').val().length > 0)
	{
		var args = { at: $.cookie('a'), user_id: $('#user_id').val(),
		item_id: $('#item_id').val(), comment: $('#comment').val(),
		last: $('#item_comments .comments_time:last').data('unix')};
		$('#comment').val('');
		$.post(website + 'ajax/add_comment', args)
			.done(function(data, text, a)
			{
				var response = parseResponse(data);
				if(response['valid'])
					$('#item_comments').append(response['rows']);
			}).fail(function(a, text, error)
			{
			});
	}
	return false;
};

//=============================================================================
// Notifications
//=============================================================================
var notifications = notifications || {};
notifications.num_unread = 0;

notifications.init = function()
{
	$('#notifications_list').on('click', 'a', function ()
	{
		if ($(this).parent().hasClass('light_blue_background'))
		{
			$(this).parent().removeClass('light_blue_background');
			notifications.decrease_unread();
			notifications.mark_as_read($(this).parent().data('id'));
		}
	});
	$('#notifications_list').perfectScrollbar();
	notifications.update();
};

notifications.decrease_unread = function()
{
	notifications.num_unread--;
	$('#num_unread_notifications').text(notifications.num_unread);
	if (notifications.num_unread <= 0)
		$('#unread_notifications').addClass('nodisplay');
}

notifications.mark_as_read = function(id)
{
	var args = { at: $.cookie('a'), id: id };
	$.post(website + 'ajax/read_notification', args);
}

notifications.update = function()
{
	var last = $('#notifications_list li:first #notification_timestamp')
		.data('unix');
	var args = { at: $.cookie('a'), timestamp: last ? last : 0 };
	$.post(website + 'ajax/get_notifications', args)
		.done(function (data, text, a)
		{
			var response = parseResponse(data);
			if (response['new'])
			{
				$('#notifications_list').prepend(response['rows']);
				notifications.num_unread += response['new_unread'];
				$('#num_unread_notifications').text(notifications.num_unread);
				if (notifications.num_unread > 0)
					$('#unread_notifications').removeClass('nodisplay');
			}
		}).fail(function (a, text, error)
		{
		});
};

notifications.interval = setInterval(notifications.update, 30000);

//=============================================================================
// Recommendations
//=============================================================================
var recommendations = recommendations || {}

recommendations.init = function()
{
	var args = { at: $.cookie('a') };
	$.post(website + 'ajax/get_recommendations', args)
		.done(function (data, text, a)
		{
			var response = parseResponse(data);
			if (response['valid'])
			{
				$('#recommendations_fixed').html(response['rows']);
			}
		}).fail(function (a, text, error)
		{
		});
};

//-----------------------------------------------------------------------------
// Read All Notifications Form
//-----------------------------------------------------------------------------

var read_all_notifications_form = read_all_notifications_form || {};
read_all_notifications_form.submit = function()
{
	notifications.num_unread = 0;
	$('#unread_notifications').addClass('nodisplay');

	$('#notifications_list .notification_row').each(function()
	{
		$(this).removeClass('light_blue_background');
	});

	var args = { at: $.cookie('a') };
	$.post(website + 'ajax/read_all_notifications', args);
	return false;
};

//=============================================================================
// Intervals
//=============================================================================
var intervals = intervals || {};
intervals.refresh_page = ((window[page_from_url(location.href)] &&
	window[page_from_url(location.href)]['refresh']) ?
	setInterval(window[page_from_url(location.href)]['refresh'], 60000) : null);

function clear_intervals()
{
	for (var index in intervals)
	{
		if (intervals[index])
		{
			clearInterval(intervals[index]);
			intervals[index] = null;
		}
	}
}

//=============================================================================
// Timeouts
//=============================================================================
var timeouts = timeouts || {};
timeouts.success = null;
timeouts.error = null;

function clear_timeouts()
{
	for (var index in timeouts)
	{
		if (timeouts[index])
		{
			clearTimeout(timeouts[index]);
			timeouts[index] = null;
		}
	}
}

//=============================================================================
// globals
//=============================================================================
var hidden_name = get_hidden_name();
unix_update = function()
{
	$('span[data-unix]').each(function()
	{
		$(this).text(convert_time($(this).data('unix')));
	});
};
var unix_interval = setInterval(unix_update, 60000);

//=============================================================================

$(document).ready(function ()
{
	if (hidden_name)
	{
		var change = hidden_name.replace(/[H|h]idden/,'') + 'visibilitychange';
		document.addEventListener(change, function()
		{
			if (intervals.refresh_page)
			{
				clearInterval(intervals.refresh_page);
				if (is_hidden())
				{
					intervals.refresh_page = setInterval(
						window[page_from_url(location.href)]['refresh'], 1800000);
				}
				else
				{
					window[page_from_url(location.href)]['refresh']();
					intervals.refresh_page = setInterval(
						window[page_from_url(location.href)]['refresh'], 60000);
				}
			}
		});
	}

	window.addEventListener('popstate', function(event)
	{
		get_content(event.target.location);
	});

	$('#dropdown_profile').click(function ()
	{
		$('#user_dropdown').dropdown('toggle');
	});
	$('#dropdown_settings').click(function ()
	{
		$('#user_dropdown').dropdown('toggle');
	});
	$('#notifications_list').click(function ()
	{
		$('#notification_dropdown').dropdown('toggle');
	});
	$('#search_typeahead_list').click(function()
	{
		$('#search_dropdown').dropdown('toggle');
	});
	$('#search').keydown(function(e)
	{
		if (e.keyCode == 13)
		{
			$('#search_form').submit();
			if (!$('#search_dropdown').parent().hasClass('open'))
				return false;
		}
		return true;
	});
	
	$(document).on('click', 'a', function ()
	{
		if (this.href.slice(-1) == '#')
			return true;

		if ($('#user_dropdown').attr('aria-expanded') == 'true')
			$('#user_dropdown').dropdown('toggle');
		if ($('#notification_dropdown').attr('aria-expanded') == 'true')
			$('#notification_dropdown').dropdown('toggle');

		history.pushState({}, '', this.href);

		get_content(this.href);
		return false;
	});

	$(document).on('textInput input', 'input', function ()
	{
		this.id && window[this.form.id][this.id + '_input'] &&
			window[this.form.id][this.id + '_input']();
	});
	$(document).on('focusout', 'input', function ()
	{
		this.id && window[this.form.id][this.id + '_focusout'] &&
			window[this.form.id][this.id + '_focusout']();
	});
	$(document).on('change', 'input:file', function ()
	{
		this.id && window[this.form.id][this.id + '_change'] &&
			window[this.form.id][this.id + '_change']();
	});
	$(document).on('focusout', 'textarea', function ()
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
	$(document).on('click', 'button', function ()
	{
		if (this.id && window[this.id + '_click'])
			return window[this.id + '_click']();
		return true;
	});

	$('#search_typeahead_list').perfectScrollbar();
	notifications.init();
	recommendations.init();
});
