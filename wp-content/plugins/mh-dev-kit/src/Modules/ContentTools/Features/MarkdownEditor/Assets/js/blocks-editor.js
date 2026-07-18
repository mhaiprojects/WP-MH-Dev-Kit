( function ( wp ) {
	if ( ! wp || ! wp.blocks || ! wp.element || ! wp.blockEditor ) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var TextareaControl = wp.components.TextareaControl;

	function renderPreview( markdown ) {
		if ( typeof marked !== 'undefined' && marked.parse ) {
			return marked.parse( markdown || '' );
		}

		return '<pre>' + ( markdown || '' ) + '</pre>';
	}

	var blockName = 'mh-dev-kit/markdown';
	var settings = wp.blocks.getBlockType( blockName );

	if ( ! settings ) {
		return;
	}

	wp.blocks.unregisterBlockType( blockName );

	wp.blocks.registerBlockType(
		blockName,
		Object.assign( {}, settings, {
			edit: function ( props ) {
				var blockProps = useBlockProps( {
					className: 'mhdk-markdown-editor',
				} );
				var content = props.attributes.content || '';

				return el(
					Fragment,
					null,
					el(
						InspectorControls,
						null,
						el(
							PanelBody,
							{ title: 'Markdown', initialOpen: true },
							el( TextareaControl, {
								label: 'Markdown source',
								value: content,
								onChange: function ( val ) {
									props.setAttributes( { content: val } );
								},
								rows: 12,
							} )
						)
					),
					el(
						'div',
						blockProps,
						el( TextareaControl, {
							label: 'Markdown',
							value: content,
							onChange: function ( val ) {
								props.setAttributes( { content: val } );
							},
							rows: 8,
						} ),
						el( 'div', {
							className: 'mhdk-markdown-preview',
							dangerouslySetInnerHTML: { __html: renderPreview( content ) },
						} )
					)
				);
			},
		} )
	);
} )( window.wp );
