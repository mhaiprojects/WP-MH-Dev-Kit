( function ( wp ) {
	if ( ! wp || ! wp.blocks || ! wp.element || ! wp.blockEditor ) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var SelectControl = wp.components.SelectControl;
	var ServerSideRender = wp.serverSideRender;

	var blockName = 'mh-dev-kit/children-pages';
	var settings = wp.blocks.getBlockType( blockName );

	if ( ! settings ) {
		return;
	}

	wp.blocks.unregisterBlockType( blockName );

	wp.blocks.registerBlockType(
		blockName,
		Object.assign( {}, settings, {
			edit: function ( props ) {
				var blockProps = useBlockProps( { className: 'mhdk-block-preview' } );
				var attrs = props.attributes;

				return el(
					Fragment,
					null,
					el(
						InspectorControls,
						null,
						el(
							PanelBody,
							{ title: 'Children Pages', initialOpen: true },
							el( TextControl, {
								label: 'Parent page ID (0 = current page)',
								type: 'number',
								value: String( attrs.parentId || 0 ),
								onChange: function ( val ) {
									props.setAttributes( { parentId: parseInt( val, 10 ) || 0 } );
								},
							} ),
							el( SelectControl, {
								label: 'Order by',
								value: attrs.orderby || 'menu_order',
								options: [
									{ label: 'Menu order', value: 'menu_order' },
									{ label: 'Title', value: 'title' },
									{ label: 'Date', value: 'date' },
									{ label: 'ID', value: 'ID' },
								],
								onChange: function ( val ) {
									props.setAttributes( { orderby: val } );
								},
							} ),
							el( SelectControl, {
								label: 'Order',
								value: attrs.order || 'ASC',
								options: [
									{ label: 'Ascending', value: 'ASC' },
									{ label: 'Descending', value: 'DESC' },
								],
								onChange: function ( val ) {
									props.setAttributes( { order: val } );
								},
							} ),
							el( TextControl, {
								label: 'Limit',
								type: 'number',
								value: String( attrs.limit || 50 ),
								onChange: function ( val ) {
									props.setAttributes( { limit: parseInt( val, 10 ) || 50 } );
								},
							} )
						)
					),
					el(
						'div',
						blockProps,
						ServerSideRender
							? el( ServerSideRender, {
									block: blockName,
									attributes: attrs,
							  } )
							: el( 'p', null, 'Children Pages preview' )
					)
				);
			},
		} )
	);
} )( window.wp );
