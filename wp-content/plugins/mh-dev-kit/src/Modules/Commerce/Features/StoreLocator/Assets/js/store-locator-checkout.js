(function ($) {
	'use strict';

	var cfg = window.mhdkStoreLocatorCheckout || {};
	var $field = $('#' + cfg.fieldId);
	var $distance = $('.mhdk-checkout-distance');
	var $hidden = $('input[name="mhdk_pickup_distance_m"]');

	if (!$field.length || !navigator.geolocation) {
		return;
	}

	function setDistanceText(text) {
		if ($distance.length) {
			$distance.text(text || '');
		}
	}

	function updateDistance(storeId, lat, lng) {
		if (!storeId) {
			setDistanceText('');
			$hidden.val('');
			return;
		}

		setDistanceText(cfg.i18n && cfg.i18n.waiting ? cfg.i18n.waiting : 'Calculating…');

		var url = cfg.restUrl + encodeURIComponent(storeId) + '/distance?lat=' + encodeURIComponent(String(lat)) + '&lng=' + encodeURIComponent(String(lng));

		fetch(url, {
			headers: {
				'X-WP-Nonce': cfg.nonce || ''
			}
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (payload) {
				if (!payload || !payload.distance_km) {
					setDistanceText(cfg.i18n && cfg.i18n.error ? cfg.i18n.error : 'Could not calculate distance.');
					return;
				}

				$hidden.val(String(payload.distance_metres || 0));
				var template = cfg.i18n && cfg.i18n.result ? cfg.i18n.result : 'You are %1$s km from this store.';
				setDistanceText(template.replace('%1$s', String(payload.distance_km)));
			})
			.catch(function () {
				setDistanceText(cfg.i18n && cfg.i18n.error ? cfg.i18n.error : 'Could not calculate distance.');
			});
	}

	function refresh() {
		var storeId = $field.val();
		if (!storeId) {
			setDistanceText('');
			$hidden.val('');
			return;
		}

		navigator.geolocation.getCurrentPosition(
			function (position) {
				updateDistance(storeId, position.coords.latitude, position.coords.longitude);
			},
			function () {
				setDistanceText(cfg.i18n && cfg.i18n.prompt ? cfg.i18n.prompt : 'Allow location to show distance.');
			}
		);
	}

	$field.on('change', refresh);
})(jQuery);
