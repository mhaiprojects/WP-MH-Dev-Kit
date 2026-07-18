( function () {
	function renderAll() {
		if ( typeof marked === 'undefined' || ! marked.parse ) {
			return;
		}

		document.querySelectorAll( '.mhdk-markdown[data-markdown]' ).forEach( function ( el ) {
			var raw = el.getAttribute( 'data-markdown' ) || '';

			if ( ! raw ) {
				return;
			}

			el.innerHTML = marked.parse( raw );
			el.removeAttribute( 'data-markdown' );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', renderAll );
	} else {
		renderAll();
	}
} )();
