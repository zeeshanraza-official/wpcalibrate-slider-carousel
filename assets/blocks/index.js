/**
 * Gutenberg Block Editor Registration for WPCalibrate Slider & Carousel
 */

(function (wp) {
	'use strict';

	var registerBlockType = wp.blocks.registerBlockType;
	var el = wp.element.createElement;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var Placeholder = wp.components.Placeholder;
	var ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender;

	var slidersList = (window.wpcalscBlockData && window.wpcalscBlockData.sliders) || [];

	var options = [
		{ label: '-- Select a Slider --', value: 0 }
	];

	slidersList.forEach(function (s) {
		options.push({
			label: s.title + ' (#' + s.id + ')',
			value: s.id
		});
	});

	registerBlockType('wpcalibrate/slider', {
		edit: function (props) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var sliderId = parseInt(attributes.sliderId, 10) || 0;

			var inspector = el(
				InspectorControls,
				{},
				el(
					PanelBody,
					{ title: 'Slider Settings', initialOpen: true },
					el(SelectControl, {
						label: 'Select Slider',
						value: sliderId,
						options: options,
						onChange: function (newVal) {
							setAttributes({ sliderId: parseInt(newVal, 10) || 0 });
						}
					})
				)
			);

			if (sliderId <= 0) {
				return el(
					'div',
					{},
					inspector,
					el(
						Placeholder,
						{
							icon: 'images-alt2',
							label: 'WPCalibrate Slider & Carousel',
							instructions: 'Select an existing slider from the sidebar settings or create one under WPCalibrate > Slider & Carousel.'
						},
						el(SelectControl, {
							value: sliderId,
							options: options,
							onChange: function (newVal) {
								setAttributes({ sliderId: parseInt(newVal, 10) || 0 });
							}
						})
					)
				);
			}

			// Render Server-Side Preview
			return el(
				'div',
				{ className: 'wpcalsc-block-preview-wrap' },
				inspector,
				ServerSideRender ? el(ServerSideRender, {
					block: 'wpcalibrate/slider',
					attributes: attributes
				}) : el('div', { className: 'wpcalsc-block-placeholder' }, 'Slider ID: ' + sliderId)
			);
		},

		save: function () {
			// Dynamic block renders server-side
			return null;
		}
	});

})(window.wp);
