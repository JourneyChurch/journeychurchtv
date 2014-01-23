//
// ACS Bridge 1.5
// http://www.hicksondesign.com
//
// Copyright 2012, Ron Hickson
//

(function ($) {
	$.fn.extend({
		acsView: function (settings) {
			// Options and default values
			var defaults = {
				start: null,
				loaderObj: "/themes/third_party/acs_bridge/ajax-loader.gif",
				showNav: true,
				navPosition: "both",
				prevText: "previous",
				prev: "acs_prev",
				nextText: "next",
				next: "acs_next",
				opacity: 0.8,
				overlay: "mask",
				dialog: "dialog",
				listId: "acs_events"
			};
			var settings = $.extend(defaults, settings),
				divId = '#' + this.attr('id'),
				divH = this.height(),
				divW = this.width(),
				winH,
				winW,
				dialogH,
				dialogW,
				docH,
				docW,
				loaderShow,
				position,
				fetch;
				
			if (typeof settings.template !== 'undefined') {

				// Position and show the loading graphic.
				loaderShow = function() {
					divH = $('#loader').parent().height();
					divW = $('#loader').parent().width();
					
					$('#loader').css({
						'position': 'absolute',
						'height': divH,
						'width': divW,
						'top': 0,
						'left': 0,
						'z-index': 100
					});
					$('#loader img').css({
						'position': 'absolute',
						'top': (divH / 2),
						'left': (divW / 2)
					});
					$('#loader').fadeIn('slow');
				};
				
				// Set positioning of overlay and the popup
				position = function () {
					winH = $(window).height();
					winW = $(window).width();
					dialogH = $('.' + settings.dialog).height();
					dialogW = $('.' + settings.dialog).width();
					docH = $(document).height();
					docW = $(document).width();
					$('#' + settings.listId).children('.' + settings.dialog).css({
						'position': 'fixed',
						'top': (winH / 2) - (dialogH / 2),
						'left': (winW / 2) - (dialogW / 2)
					});
					$('#' + settings.listId).children('.' + settings.overlay).css({'width': docW, 'height': docH});
				};

				// Get data
				fetch = function (start) {

					$.ajax({
						url: settings.template,
						data: {start: start, id: settings.id},
						crossDomain: true,
						dataType: "json",
						beforeSend: function(xhr) {
        					xhr.setRequestHeader('Cache-Control', 'max-age=600');
        					xhr.setRequestHeader('Pragma', 'cache');
        				},
						error: function (data) {
							console.log(data)
						},
						success: function (data) {
							// console.log(data);
							
							// YAY! So take away that loader and replace it with real content
							$('#loader').fadeOut('slow').remove();
							$('#' + settings.listId).hide().html(data.output + '<div class="' + settings.overlay + '"></div><div class="' + settings.dialog + '"></div>').fadeTo('slow', 1);
							
							if (data.origStart) {
								settings.start = data.origStart;
								settings.origStart = data.origStart;
								settings.length = data.displayLength;
							}

							// If nav is set to be shown
							if ((settings.showNav === true) && (!data.error)) {
								if (settings.navPosition === "top" || settings.navPosition === "both") {
									if (settings.start > settings.origStart) {
										$('#' + settings.listId).prepend('<button type="button" class="' + settings.prev + '" value="' + (settings.start - settings.length) + '">' + settings.prevText + '</button>');
									}
									$('#' + settings.listId).prepend('<button type="button" class="' + settings.next + '" value="' + (settings.start + settings.length) + '">' + settings.nextText + '</button>');
								}
								if ((settings.navPosition === "bottom") || (settings.navPosition === "both")) {
									if (settings.start > settings.origStart) {
										$('#' + settings.listId).append('<button type="button" class="' + settings.prev + '" value="' + (settings.start - settings.length) + '">' + settings.prevText + '</button>');
									}
									$('#' + settings.listId).append('<button type="button" class="' + settings.next + '" value="' + (settings.start + settings.length) + '">' + settings.nextText + '</button>');
								}
							}
							
							// The popup trigger
							$(settings.trigger).bind('click', function () {
								var url = $(this).attr('href');
								position();
								$('#' + settings.listId).children('.' + settings.overlay).fadeTo('fast', 0.8);
								$('#' + settings.listId).children('.' + settings.dialog).html('<div id="loader" style="display:none;"><img src="' + settings.loaderObj + '" /></div>').fadeIn('slow');
								loaderShow();
								$.ajax({
									url: url,
									dataType: "json",
									success: function (data) {
										$('#loader').remove();
										$('#' + settings.listId).children('.' + settings.dialog).html(data.output);
									}
								});
								return false;
							});
							$('.' + settings.prev).on('click', function () {
								var prevTime = parseInt($('.' + settings.prev).attr('value'), 10);
								$('#' + settings.listId).fadeTo('slow', 0.2);
								$(divId).append('<div id="loader" style="display:none;"><img src="' + settings.loaderObj + '" /></div>');
								loaderShow();
								settings.start = prevTime;
								fetch(prevTime);
							});
							$('.' + settings.next).on('click', function () {
								var nextTime = parseInt($('.' + settings.next).attr('value'), 10);
								$('#' + settings.listId).fadeTo('slow', 0.2);
								$(divId).append('<div id="loader" style="display:none;"><img src="' + settings.loaderObj + '" /></div>');
								loaderShow();
								settings.start = nextTime;
								fetch(nextTime);
							});
							$('#' + settings.listId).children('.' + settings.overlay).click(function () {
								$('#' + settings.listId).children('.' + settings.dialog).fadeOut();
								$(this).fadeOut();
							});
							return false;
						}
					});
				};

				// Create the loading div on the page
				this.html('<div id="' + settings.listId + '" style="min-height:' + divH +'px; min-width:' +divW + 'px; position: relative;"><div id="loader" style="display:none;"><img src="' + settings.loaderObj + '" /></div></div>');
				loaderShow();
				fetch(settings.start);
				$(window).resize(function () {
					position();
				});
			}
		}	
	});

}(jQuery));