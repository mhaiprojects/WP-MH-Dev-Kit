( function ( wp ) {
	if ( ! wp || ! wp.blocks || ! wp.element || ! wp.blockEditor ) {
		return;
	}

	var el = wp.element.createElement;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var ServerSideRender = wp.serverSideRender;

	function dynamicEdit( blockName, placeholder ) {
		return function ( props ) {
			var blockProps = useBlockProps( { className: 'mhdk-block-preview' } );

			if ( ServerSideRender ) {
				return el(
					'div',
					blockProps,
					el( ServerSideRender, {
						block: blockName,
						attributes: props.attributes,
					} )
				);
			}

			return el( 'div', blockProps, placeholder );
		};
	}

	var blocks = [
		{
			name: 'mh-dev-kit/hospitality-menu',
			edit: dynamicEdit(
				'mh-dev-kit/hospitality-menu',
				'Hospitality Menu — configure in the sidebar.'
			),
		},
		{
			name: 'mh-dev-kit/web-links',
			edit: dynamicEdit( 'mh-dev-kit/web-links', 'Web Links — configure in the sidebar.' ),
		},
		{
			name: 'mh-dev-kit/callout',
			edit: dynamicEdit( 'mh-dev-kit/callout', 'Callout — configure in the sidebar.' ),
		},
	];

	blocks.forEach( function ( block ) {
		var settings = wp.blocks.getBlockType( block.name );

		if ( ! settings ) {
			return;
		}

		wp.blocks.unregisterBlockType( block.name );
		wp.blocks.registerBlockType( block.name, Object.assign( {}, settings, { edit: block.edit } ) );
	} );
} )( window.wp );
