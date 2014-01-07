jQuery(document).ready(function($) {

	$ml_dialog      = $("div#ml_dialog");
	$entry_markup   = $('div#ml_entry_markup');
	$generic_markup = $("div#ml_generic_markup");

	$ml_dialog.dialog({
		width     : 240,
		modal     : true,
		draggable : false,
		autoOpen  : false,
		show      : 'clip',
		hide      : 'clip',
		resizable : false,
		minHeight : 50
	});

	// Store the default entry markup so it can be reverted to on dialogclose
	$entry_markup.data('default', $entry_markup.html());

	$ml_dialog.bind( "dialogopen", function(event, ui) {
		$(".ui-widget-overlay").css('opacity', 0).removeClass('hidden').fadeTo(500, 0.85);

		// disable scrolling on the underlying page
		$('body').addClass('ml_no_scroll');
		$('.ui-widget-overlay').css('width','100%');
	});

	$ml_dialog.bind( "dialogclose", function(event, ui) {
		$('body').removeClass('ml_no_scroll');
		$("#ml_toggle_content, #ml_reload_notice, #ml_gohome_notice").css('display', 'none');

		$entry_markup.html($entry_markup.data('default'));
		$generic_markup.show();

		$ml_dialog.closest('.ui-dialog').css('marginTop', '0px'); // publish dropdown reset
	});

	$ml_dialog.bind( "dialogbeforeclose", function(event, ui) {
		if(! $(".ui-widget-overlay").hasClass('hidden'))
		{
			$(".ui-widget-overlay").fadeTo(150, 0);
			$(".ui-widget-overlay").addClass('hidden');
			setTimeout("$ml_dialog.dialog('close');", 150);
			return false;
		}
	});

	$("#ml_trigger, .ml_trigger_inline").on('click', function() {

		var button_id = $(this).attr('id');

		// If an inline button has been triggered, update the menu markup and hide the non-entry specific links
		if(button_id != 'ml_trigger')
		{
			entry_id = button_id.substr(20);
			$entry_markup.html(ml_entry_links[entry_id]);
			$generic_markup.hide();

			if($(this).hasClass('instant_edit'))
			{
				$("#edit_this_entry a", $entry_markup).click();
			}
			else if ($(this).hasClass('ml_link'))
			{
				setTimeout(function() {
					$link = $('a:first', $entry_markup);
					$link.click();
					setTimeout(function() { $link.parent().remove(); }, 1000);

				}, 150);
			}

			menu_type = 'inline';
		}
		else
		{
			menu_type = 'normal';
		}

		$ml_dialog.dialog('open').data('menu_type', menu_type);
		return false;
	});

	// Toggle the publish links.
	// If this results in the bottom of the dialog moving below the fold, reposition the dialog at the vertical centre of the page.
	$("#ml_publish_link a").click(function() {
		var $dialog		= $ml_dialog.closest('.ui-dialog');
		var orig_height	= $dialog.height();

		$("#ml_toggle_content").slideToggle('500', function() {
			if(($dialog.height() + $dialog.offset().top - $(window).scrollTop()) > $(window).height())
			{
				topMargin = (orig_height - $dialog.height()) / 2;
				$dialog.animate( {marginTop: topMargin }, 'fast');
			}
			else
			{
				$dialog.animate( {marginTop: '0px' }, 'fast');
			}
		});

		return false;
	});

	$("#ml_dialog").on('click', 'a:not([class=nofb])', function() {
		$link = $(this);

		$('body').append('<div style="display:none"><a id="ml_dummy_link" href="' + $link.attr('href') + '"></a></div>');

		$('#ml_dummy_link').fancybox({
			'width'				: '95%',
			'height'			: '95%',
			'autoScale'			: false,
			'transitionIn'		: 'fade',
			'transitionOut'		: 'fade',
			'type'				: 'iframe',
			'padding'			: 0,
			'overlayColor'		: '#000',
			'overlayOpacity'	: 0.1,
			'hideOnOverlayClick': false,
			'centerOnScroll'	: true,
			'onComplete'		: function() {
				if($link.parent().attr('id') == 'ml_delete_link')
				{
					$("form#ml_delete_form").attr('target', $("#fancybox-inner iframe").attr('name')).submit();
				}

				// delete entry via normal Admin button, get homepage link instead of reload link (so we don't try and reload a just-deleted permalink)
				if($ml_dialog.data('menu_type') == 'normal' && $link.parent().attr('id') == 'ml_delete_link')
				{
					$("#ml_reload_notice").hide();
					$("#ml_gohome_notice").show();
				}
				else
				{
					$("#ml_reload_notice").show();
					$("#ml_gohome_notice").hide();
				}
			}
		}).click().remove();

		$ml_dialog.data('active_link', ($(this).attr('href')));

		return false;
	});

	$("#ml_reload_notice a").click(function() {
		do_reload();
		return false;
	});

	// Recentre the ML menu as the window resizes
	$(window).resize(function() {
		$ml_dialog.dialog('option', 'position', $ml_dialog.dialog('option', 'position'));
	});

	function toggle_ml() {
		if($("#ml_dialog:visible").length < 1) {
			$ml_dialog.dialog('open');
		}
		else {
			$ml_dialog.dialog('close');
		}
	}

	// ============================
	// ! Switchboard search field
	// ============================
	var $search_field = $("input#ml_search_field");

	$("#ml_search").submit(function() {
		$("#ml_search_link").attr('href', $("input#switchboard_url").val() + $search_field.val() ).click();
		$search_field.val('');
		return false;
	});


	// placeholders for ye olde browsers
	var i = document.createElement('input');
	var place_text = 'Search';
	var place_class = 'ml_placeholder';
	if('placeholder' in i === false)
	{
		$search_field.val(place_text).addClass(place_class);

		$search_field.focusin(function() {
			if($(this).val() == place_text)	{ $(this).val('').removeClass(place_class); }
		});

		$search_field.focusout(function() {
			if($(this).val() == place_text || $(this).val() === '') { $(this).val(place_text).addClass(place_class); }
		});
	}

	// ======================
	// ! Keyboard shortcuts
	// ======================

	function check_modifier(event)
	{
		if(ml_modifier == 'altKey' && event.altKey)
		{
			return true;
		}
		else if (ml_modifier == 'ctrlKey' && event.ctrlKey)
		{
			return true;
		}
		return false;
	}

	if(typeof ml_modifier != 'undefined')
	{
		// shortcut - toggle ML menu visiblility
		$(document).keydown(function(event) {

			if (event.which == 71 && check_modifier(event)) { // modifier+g
				toggle_ml();
				return false;
			}

			// Switchboard users only: mod + s to toggle ML menu, focus search field
			if($search_field.length === 1 && event.which == 83 && check_modifier(event))
			{
				toggle_ml();
				$search_field.val('').focus();
				return false;
			}
		});

		// shortcut - edit this entry
		$(document).keydown(function(event) {
			if ((event.which == 69 && check_modifier(event))){ // modifier+e
				if($("#edit_this_entry a").length > 0 && $("#ml_dialog:visible").length < 1) {
					toggle_ml();
					$('.ui-widget-overlay').css('z-index', 1092);
					$ml_dialog.parents('.ui-dialog').css('z-index', 1093);
					setTimeout('$("#edit_this_entry a").click()', 500);
					return false; // stop default browser keydown behaviour
				}
			}
		});
	}

});

function do_reload(delay) {
	if(typeof delay == 'undefined') { delay = 0; }
	setTimeout('window.location.reload();', delay);
	return false;
}