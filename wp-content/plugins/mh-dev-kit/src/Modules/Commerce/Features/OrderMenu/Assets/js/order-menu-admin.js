( function () {
	function buildShortcode() {
		var output = document.getElementById( 'mhdk-gen-output' );
		if ( ! output ) {
			return;
		}

		var parts = [];
		var menuEl = document.getElementById( 'mhdk-gen-menu' );
		var categoryEl = document.getElementById( 'mhdk-gen-category' );
		var idsEl = document.getElementById( 'mhdk-gen-ids' );
		var orderbyEl = document.getElementById( 'mhdk-gen-orderby' );
		var orderEl = document.getElementById( 'mhdk-gen-order' );

		var menu = menuEl ? menuEl.value : '';
		var category = categoryEl ? categoryEl.value : '';
		var ids = idsEl ? idsEl.value.trim() : '';
		var orderby = orderbyEl ? orderbyEl.value : 'title';
		var order = orderEl ? orderEl.value : 'ASC';

		if ( menu ) {
			parts.push( 'menu="' + menu + '"' );
		} else if ( ids ) {
			parts.push( 'ids="' + ids + '"' );
		} else if ( category ) {
			parts.push( 'category="' + category + '"' );
		}

		if ( orderby && orderby !== 'title' ) {
			parts.push( 'orderby="' + orderby + '"' );
		}

		if ( order && order !== 'ASC' ) {
			parts.push( 'order="' + order + '"' );
		}

		output.value = '[mhdk_order_menu ' + parts.join( ' ' ) + ']';
	}

	[
		'mhdk-gen-category',
		'mhdk-gen-menu',
		'mhdk-gen-ids',
		'mhdk-gen-orderby',
		'mhdk-gen-order',
	].forEach( function ( id ) {
		var el = document.getElementById( id );
		if ( ! el ) {
			return;
		}

		el.addEventListener( 'change', buildShortcode );
		el.addEventListener( 'input', buildShortcode );
	} );

	buildShortcode();
} )();
