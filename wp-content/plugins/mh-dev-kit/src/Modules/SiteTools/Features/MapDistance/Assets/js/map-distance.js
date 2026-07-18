( function () {
	function haversineMetres( lat1, lng1, lat2, lng2 ) {
		var R = 6371000;
		var dLat = ( ( lat2 - lat1 ) * Math.PI ) / 180;
		var dLng = ( ( lng2 - lng1 ) * Math.PI ) / 180;
		var a =
			Math.sin( dLat / 2 ) * Math.sin( dLat / 2 ) +
			Math.cos( ( lat1 * Math.PI ) / 180 ) *
				Math.cos( ( lat2 * Math.PI ) / 180 ) *
				Math.sin( dLng / 2 ) *
				Math.sin( dLng / 2 );
		var c = 2 * Math.atan2( Math.sqrt( a ), Math.sqrt( 1 - a ) );
		return R * c;
	}

	function formatResult( metres ) {
		var km = metres / 1000;
		return {
			km: km.toFixed( 2 ),
			m: Math.round( metres ).toLocaleString(),
		};
	}

	function initWidget( root ) {
		var cfg = window.mhdkMapDistance || {};
		var btn = root.querySelector( '.mhdk-map-distance-btn' );
		var result = root.querySelector( '.mhdk-map-distance-result' );

		if ( ! btn || ! result ) {
			return;
		}

		var startLat = parseFloat( root.getAttribute( 'data-start-lat' ) );
		var startLng = parseFloat( root.getAttribute( 'data-start-lng' ) );

		btn.addEventListener( 'click', function () {
			if ( ! navigator.geolocation ) {
				result.textContent = cfg.i18n ? cfg.i18n.unsupported : 'Geolocation unsupported.';
				return;
			}

			result.textContent = cfg.i18n ? cfg.i18n.waiting : 'Waiting…';

			navigator.geolocation.getCurrentPosition(
				function ( pos ) {
					var metres = haversineMetres(
						startLat,
						startLng,
						pos.coords.latitude,
						pos.coords.longitude
					);
					var formatted = formatResult( metres );
					var template = cfg.i18n ? cfg.i18n.result : 'Distance: %2$s km';
					result.textContent = template
						.replace( '%1$s', cfg.startLabel || '' )
						.replace( '%2$s', formatted.km )
						.replace( '%3$s', formatted.m );
				},
				function () {
					result.textContent = cfg.i18n ? cfg.i18n.error : 'Location error.';
				}
			);
		} );
	}

	function boot() {
		document.querySelectorAll( '.mhdk-map-distance' ).forEach( initWidget );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
} )();
