/**
 * WPCalibrate Slider & Carousel Frontend Runtime Engine
 *
 * @package WPCalibrate\SliderCarousel
 */

(function () {
	'use strict';

	window.WPCalibrateSlider = window.WPCalibrateSlider || {
		instances: {},

		/**
		 * Initialize all uninitialized sliders on the page.
		 */
		initAll: function () {
			if (typeof window.Swiper === 'undefined') {
				return;
			}

			var containers = document.querySelectorAll('.wpcsc-slider-container[data-swiper-config]');
			containers.forEach(function (container) {
				if (container.getAttribute('data-wpcalsc-init') === 'true') {
					return;
				}
				window.WPCalibrateSlider.initContainer(container);
			});
		},

		/**
		 * Initialize a single slider container.
		 *
		 * @param {HTMLElement} container The slider container element.
		 */
		initContainer: function (container) {
			var configJson = container.getAttribute('data-swiper-config');
			if (!configJson) {
				return;
			}

			var config = {};
			try {
				config = JSON.parse(configJson);
			} catch (e) {
				return;
			}

			var containerId = container.id || 'wpcsc-' + Math.random().toString(36).substr(2, 9);
			var mainEl = container.querySelector('.wpcsc-main-swiper');
			if (!mainEl) {
				return;
			}

			// Accessibility: Check reduced motion preference
			var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			if (prefersReducedMotion && config.reducedMotion) {
				config.autoplay = false;
				config.speed = 0;
			}

			// Handle Synchronized Thumbnails Swiper
			var thumbsSwiper = null;
			var thumbsEl = container.querySelector('.wpcsc-thumbs-swiper');
			if (thumbsEl && config.hasThumbs) {
				var thumbsGap = parseInt(thumbsEl.getAttribute('data-thumbs-gap'), 10) || 10;
				var thumbsSpv = parseInt(thumbsEl.getAttribute('data-thumbs-spv'), 10) || 5;

				thumbsSwiper = new window.Swiper(thumbsEl, {
					spaceBetween: thumbsGap,
					slidesPerView: thumbsSpv,
					freeMode: true,
					watchSlidesProgress: true,
					slideToClickedSlide: true,
				});

				config.thumbs = {
					swiper: thumbsSwiper,
				};
			}

			// Scope navigation selectors to this specific container
			if (config.navigation) {
				config.navigation.nextEl = container.querySelector('.swiper-button-next');
				config.navigation.prevEl = container.querySelector('.swiper-button-prev');
			}

			// Scope pagination selector to this specific container
			if (config.pagination) {
				config.pagination.el = container.querySelector('.swiper-pagination');
			}

			// Scope scrollbar selector to this specific container
			if (config.scrollbar) {
				config.scrollbar.el = container.querySelector('.swiper-scrollbar');
			}

			// Initialize Main Swiper instance
			try {
				var swiperInstance = new window.Swiper(mainEl, config);
				container.setAttribute('data-wpcalsc-init', 'true');
				window.WPCalibrateSlider.instances[containerId] = swiperInstance;
			} catch (err) {
				if (window.console && window.console.error) {
					window.console.error('WPCalibrate Slider init error:', err);
				}
			}
		}
	};

	// Auto-init on DOMContentLoaded
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', window.WPCalibrateSlider.initAll);
	} else {
		window.WPCalibrateSlider.initAll();
	}

	// Re-init on window load (ensures full media and fonts load without layout shift)
	window.addEventListener('load', function () {
		window.WPCalibrateSlider.initAll();
	});

})();
