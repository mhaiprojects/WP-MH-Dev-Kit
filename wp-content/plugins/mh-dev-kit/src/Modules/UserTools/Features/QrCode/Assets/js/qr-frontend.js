( function () {
	function renderQr( root ) {
		if ( typeof QRCode === 'undefined' ) {
			return;
		}

		var target = root.getAttribute( 'data-target' );
		var size = parseInt( root.getAttribute( 'data-size' ), 10 ) || 256;
		var canvas = root.querySelector( '.mhdk-qr-code-canvas' );

		if ( ! target || ! canvas ) {
			return;
		}

		new QRCode( canvas, {
			text: target,
			width: size,
			height: size,
		} );
	}

	function boot() {
		document.querySelectorAll( '.mhdk-qr-code' ).forEach( renderQr );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
} )();
