/**
 * WPCalibrate Slider & Carousel Admin Controller Script
 *
 * @package WPCalibrate\SliderCarousel
 */

(function ($) {
	'use strict';

	$(function () {
		initTabs();
		initSourceSwitcher();
		initSlideManagement();
		initMediaUploaders();
		initShortcodeCopy();
		initHeadingTitleSync();
	});

	/**
	 * Tab Navigation.
	 *
	 * Allows standard URL link navigation for multi-page/server tabs (e.g., Settings page),
	 * while managing client-side panel switching for in-page tabs (e.g., Visual Slider Editor).
	 */
	function initTabs() {
		$(document).on('click', '.wpcalsc-nav-tabs .nav-tab, [data-tab-switch]', function (e) {
			var tabId = $(this).data('tab') || $(this).data('tab-switch');

			// If no data-tab is specified, or no matching in-page panel exists in the DOM,
			// allow standard browser link navigation (e.g. Settings page tabs).
			if (!tabId || !$('#' + tabId).length) {
				return true;
			}

			e.preventDefault();

			var $navWrapper = $(this).closest('.wpcalsc-nav-tabs');
			if ($navWrapper.length) {
				$navWrapper.find('.nav-tab').removeClass('nav-tab-active');
				$(this).addClass('nav-tab-active');
			} else {
				// If clicked from external button (e.g. header preview button)
				$('.wpcalsc-nav-tabs .nav-tab').removeClass('nav-tab-active');
				$('.wpcalsc-nav-tabs .nav-tab[data-tab="' + tabId + '"]').addClass('nav-tab-active');
			}

			var $container = $(this).closest('.wpcalsc-admin-wrap, form');
			if (!$container.length) {
				$container = $('body');
			}

			$container.find('.wpcalsc-tab-panel').removeClass('is-active');
			$('#' + tabId).addClass('is-active');

			// Synchronize location hash if href starts with '#'
			var href = $(this).attr('href');
			if (href && href.charAt(0) === '#' && window.history && window.history.replaceState) {
				window.history.replaceState(null, null, href);
			}

			// If switching to preview tab, trigger resize on any Swiper instances inside
			if (tabId === 'tab-preview' && window.WPCalibrateSlider) {
				setTimeout(function () {
					window.WPCalibrateSlider.initAll();
				}, 100);
			}
		});

		// Activate tab based on URL hash if present on initial load
		if (window.location.hash) {
			var hash = window.location.hash.substring(1);
			if (hash && $('#' + hash).length) {
				var $targetTab = $('.wpcalsc-nav-tabs .nav-tab[data-tab="' + hash + '"]');
				if ($targetTab.length) {
					$targetTab.trigger('click');
				}
			}
		}
	}

	/**
	 * Switch source views (Manual Slides vs Posts vs WooCommerce).
	 */
	function initSourceSwitcher() {
		$('#config_source').on('change', function () {
			var source = $(this).val();
			$('.wpcalsc-source-section').addClass('wpcalsc-hidden');
			$('#wpcalsc-source-' + source).removeClass('wpcalsc-hidden');
		});
	}

	/**
	 * Slide drag-and-drop, add, duplicate, delete, and collapse.
	 */
	function initSlideManagement() {
		var $container = $('#wpcalsc-slides-container');

		// Enable jQuery UI Sortable
		if ($container.length && $.fn.sortable) {
			$container.sortable({
				handle: '.wpcalsc-drag-handle',
				items: '.wpcalsc-slide-card',
				axis: 'y',
				opacity: 0.7,
				update: function () {
					reindexSlides();
				}
			});
		}

		// Add Slide Button
		$('.wpcalsc-add-slide-btn').on('click', function (e) {
			e.preventDefault();
			var type = $(this).data('type') || 'image';
			var newIndex = $container.find('.wpcalsc-slide-card').length;
			var tmplSelector = '#tmpl-wpcalsc-slide-' + type;
			var $tmpl = $(tmplSelector);
			if (!$tmpl.length) {
				$tmpl = $('#tmpl-wpcalsc-slide-item');
			}
			var tmplHtml = $tmpl.html();

			if (!tmplHtml) return;

			// Remove empty state message if present
			$('.wpcalsc-no-slides-msg').remove();

			var rendered = tmplHtml
				.replace(/{{data\.index}}/g, newIndex)
				.replace(/{{data\.displayIndex}}/g, newIndex + 1)
				.replace(/{{data\.type}}/g, type);

			var $newCard = $(rendered);
			$container.append($newCard);
			reindexSlides();

			// Scroll into view
			$('html, body').animate({
				scrollTop: $newCard.offset().top - 100
			}, 300);
		});

		// Collapse / Expand Slide
		$container.on('click', '.wpcalsc-toggle-slide', function (e) {
			e.preventDefault();
			$(this).closest('.wpcalsc-slide-card').toggleClass('is-collapsed');
		});

		// Delete Slide
		$container.on('click', '.wpcalsc-delete-slide', function (e) {
			e.preventDefault();
			var confirmMsg = (window.wpcalscAdmin && window.wpcalscAdmin.confirmDelete) || 'Are you sure you want to delete this slide?';
			if (window.confirm(confirmMsg)) {
				$(this).closest('.wpcalsc-slide-card').slideUp(200, function () {
					$(this).remove();
					reindexSlides();
					if ($container.find('.wpcalsc-slide-card').length === 0) {
						$container.html('<div class="wpcalsc-no-slides-msg"><p>No slides added yet. Click one of the buttons above to add your first slide.</p></div>');
					}
				});
			}
		});

		// Duplicate Slide
		$container.on('click', '.wpcalsc-dup-slide', function (e) {
			e.preventDefault();
			var $card = $(this).closest('.wpcalsc-slide-card');
			var $clone = $card.clone();
			$card.after($clone);
			reindexSlides();
		});
	}

	/**
	 * Re-index form input names and visual slide numbers after add/remove/sort.
	 */
	function reindexSlides() {
		$('#wpcalsc-slides-container .wpcalsc-slide-card').each(function (index) {
			$(this).attr('data-index', index);
			var heading = $(this).find('.wpcalsc-slide-heading-input').val();
			var displayLabel = heading ? ('Slide #' + (index + 1) + ': ' + heading) : ('Slide #' + (index + 1));
			$(this).find('.wpcalsc-slide-label').text(displayLabel);

			$(this).find('input, select, textarea').each(function () {
				var name = $(this).attr('name');
				if (name) {
					var newName = name.replace(/config\[slides\]\[\d+\]/, 'config[slides][' + index + ']');
					$(this).attr('name', newName);
				}
				var id = $(this).attr('id');
				if (id) {
					var newId = id.replace(/slide_\d+_/, 'slide_' + index + '_');
					$(this).attr('id', newId);
				}
			});
			$(this).find('label').each(function () {
				var forAttr = $(this).attr('for');
				if (forAttr) {
					var newFor = forAttr.replace(/slide_\d+_/, 'slide_' + index + '_');
					$(this).attr('for', newFor);
				}
			});
		});
	}

	/**
	 * WordPress Media Library uploader integration.
	 */
	function initMediaUploaders() {
		var mediaFrame;

		// Choose Image
		$(document).on('click', '.wpcalsc-upload-media-btn', function (e) {
			e.preventDefault();
			var $btn = $(this);
			var $card = $btn.closest('.wpcalsc-slide-card');
			var $idInput = $card.find('.wpcalsc-media-id');
			var $urlInput = $card.find('.wpcalsc-media-url');
			var $previewThumb = $card.find('.wpcalsc-preview-thumb');
			var $placeholder = $card.find('.wpcalsc-no-media-placeholder');
			var $removeBtn = $card.find('.wpcalsc-remove-media-btn');

			mediaFrame = wp.media({
				title: (window.wpcalscAdmin && window.wpcalscAdmin.mediaTitle) || 'Select or Upload Slide Image',
				button: {
					text: (window.wpcalscAdmin && window.wpcalscAdmin.mediaButton) || 'Use this image'
				},
				multiple: false,
				library: {
					type: 'image'
				}
			});

			mediaFrame.on('select', function () {
				var attachment = mediaFrame.state().get('selection').first().toJSON();
				$idInput.val(attachment.id);
				$urlInput.val(attachment.url);

				$previewThumb.attr('src', attachment.url).removeClass('wpcalsc-hidden');
				$placeholder.addClass('wpcalsc-hidden');
				$removeBtn.removeClass('wpcalsc-hidden');
			});

			mediaFrame.open();
		});

		// Remove Image
		$(document).on('click', '.wpcalsc-remove-media-btn', function (e) {
			e.preventDefault();
			var $btn = $(this);
			var $card = $btn.closest('.wpcalsc-slide-card');
			$card.find('.wpcalsc-media-id').val('0');
			$card.find('.wpcalsc-media-url').val('');
			$card.find('.wpcalsc-preview-thumb').attr('src', '').addClass('wpcalsc-hidden');
			$card.find('.wpcalsc-no-media-placeholder').removeClass('wpcalsc-hidden');
			$btn.addClass('wpcalsc-hidden');
		});

		// Choose Video
		$(document).on('click', '.wpcalsc-upload-video-btn', function (e) {
			e.preventDefault();
			var $btn = $(this);
			var $card = $btn.closest('.wpcalsc-slide-card');
			var $idInput = $card.find('.wpcalsc-video-id');
			var $urlInput = $card.find('.wpcalsc-video-url');

			var videoFrame = wp.media({
				title: 'Select Video File',
				button: { text: 'Use this video' },
				multiple: false,
				library: { type: 'video' }
			});

			videoFrame.on('select', function () {
				var attachment = videoFrame.state().get('selection').first().toJSON();
				$idInput.val(attachment.id);
				$urlInput.val(attachment.url);
			});

			videoFrame.open();
		});
	}

	/**
	 * Shortcode copy to clipboard.
	 */
	function initShortcodeCopy() {
		$(document).on('click', '.wpcalsc-copy-btn', function (e) {
			e.preventDefault();
			var $btn = $(this);
			var text = $btn.data('clipboard');

			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(text).then(function () {
					var origHtml = $btn.html();
					$btn.html('<span class="dashicons dashicons-yes"></span>');
					setTimeout(function () {
						$btn.html(origHtml);
					}, 1500);
				});
			}
		});
	}

	/**
	 * Real-time header title update when typing slide heading.
	 */
	function initHeadingTitleSync() {
		$(document).on('input', '.wpcalsc-slide-heading-input', function () {
			var heading = $(this).val();
			var $card = $(this).closest('.wpcalsc-slide-card');
			var index = parseInt($card.attr('data-index'), 10) + 1;
			var displayLabel = heading ? ('Slide #' + index + ': ' + heading) : ('Slide #' + index);
			$card.find('.wpcalsc-slide-label').text(displayLabel);
		});
	}

})(jQuery);
