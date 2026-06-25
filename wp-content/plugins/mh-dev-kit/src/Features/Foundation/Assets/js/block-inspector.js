( function ( wp ) {
	if ( ! wp || ! wp.hooks || ! wp.element || ! wp.blockEditor || ! wp.components ) {
		return;
	}

	var el = wp.element.createElement;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var ToggleControl = wp.components.ToggleControl;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var RangeControl = wp.components.RangeControl;

	function menuOptions() {
		var menus = ( window.mhdkFoundation && window.mhdkFoundation.hospitalityMenus ) || [];
		var options = [ { label: '— Select menu —', value: 0 } ];

		menus.forEach( function ( menu ) {
			options.push( { label: menu.title, value: menu.id } );
		} );

		return options;
	}

	wp.hooks.addFilter(
		'editor.BlockEdit',
		'mh-dev-kit/hospitality-menu-inspector',
		function ( BlockEdit ) {
			return function ( props ) {
				if ( props.name !== 'mh-dev-kit/hospitality-menu' ) {
					return el( BlockEdit, props );
				}

				return el(
					wp.element.Fragment,
					{},
					el( BlockEdit, props ),
					el(
						InspectorControls,
						{},
						el(
							PanelBody,
							{ title: 'Hospitality Menu', initialOpen: true },
							el( SelectControl, {
								label: 'Menu post',
								value: props.attributes.menuId || 0,
								options: menuOptions(),
								onChange: function ( value ) {
									props.setAttributes( { menuId: parseInt( value, 10 ) || 0 } );
								},
							} ),
							el( ToggleControl, {
								label: 'Show prices',
								checked: props.attributes.showPrices !== false,
								onChange: function ( value ) {
									props.setAttributes( { showPrices: value } );
								},
							} )
						)
					)
				);
			};
		}
	);

	wp.hooks.addFilter(
		'editor.BlockEdit',
		'mh-dev-kit/web-links-inspector',
		function ( BlockEdit ) {
			return function ( props ) {
				if ( props.name !== 'mh-dev-kit/web-links' ) {
					return el( BlockEdit, props );
				}

				return el(
					wp.element.Fragment,
					{},
					el( BlockEdit, props ),
					el(
						InspectorControls,
						{},
						el(
							PanelBody,
							{ title: 'Web Links', initialOpen: true },
							el( RangeControl, {
								label: 'Limit',
								value: props.attributes.limit || 6,
								onChange: function ( value ) {
									props.setAttributes( { limit: value } );
								},
								min: 1,
								max: 24,
							} ),
							el( SelectControl, {
								label: 'Layout',
								value: props.attributes.layout || 'list',
								options: [
									{ label: 'List', value: 'list' },
									{ label: 'Grid', value: 'grid' },
								],
								onChange: function ( value ) {
									props.setAttributes( { layout: value } );
								},
							} )
						)
					)
				);
			};
		}
	);

	wp.hooks.addFilter(
		'editor.BlockEdit',
		'mh-dev-kit/callout-inspector',
		function ( BlockEdit ) {
			return function ( props ) {
				if ( props.name !== 'mh-dev-kit/callout' ) {
					return el( BlockEdit, props );
				}

				return el(
					wp.element.Fragment,
					{},
					el( BlockEdit, props ),
					el(
						InspectorControls,
						{},
						el(
							PanelBody,
							{ title: 'Callout', initialOpen: true },
							el( TextControl, {
								label: 'Title',
								value: props.attributes.title || '',
								onChange: function ( value ) {
									props.setAttributes( { title: value } );
								},
							} ),
							el( TextareaControl, {
								label: 'Content',
								value: props.attributes.content || '',
								onChange: function ( value ) {
									props.setAttributes( { content: value } );
								},
							} ),
							el( SelectControl, {
								label: 'Style',
								value: props.attributes.style || 'info',
								options: [
									{ label: 'Info', value: 'info' },
									{ label: 'Warning', value: 'warning' },
									{ label: 'Success', value: 'success' },
								],
								onChange: function ( value ) {
									props.setAttributes( { style: value } );
								},
							} )
						)
					)
				);
			};
		}
	);
} )( window.wp );
