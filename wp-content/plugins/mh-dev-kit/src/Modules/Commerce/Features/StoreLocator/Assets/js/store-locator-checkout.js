(function ($) {
	'use strict';

	var cfg = window.mhdkStoreLocatorCheckout || {};
	var $field = $('#' + cfg.fieldId);
	var $distance = $('.mhdk-checkout-distance');
	var $hidden = $('input[name="mhdk_pickup_distance_m"]');
	var $wrapper = $('.mhdk-checkout-store-locator');
	var hasGeolocation = !!(navigator.geolocation);

	if (!$field.length) {
		return;
	}

	function setDistanceText(text) {
		if ($distance.length) {
			$distance.text(text || '');
		}
	}

	function storeLabel(store, stockInfo) {
		var label = store.name + ' — ' + store.address;
		if (!stockInfo) {
			return label;
		}
		if (stockInfo.available) {
			return label + ' (' + stockInfo.minimum + ' in stock)';
		}
		return label + ' (' + (cfg.i18n && cfg.i18n.outOfStock ? cfg.i18n.outOfStock : 'Out of stock') + ')';
	}

	function rebuildOptions(stores) {
		var selected = $field.val();
		var placeholder = $field.find('option:first').text();

		$field.empty();
		$field.append($('<option>', { value: '', text: placeholder }));

		stores.forEach(function (store) {
			var stockInfo = cfg.storeStock ? cfg.storeStock[store.id] : null;
			var disabled = stockInfo && !stockInfo.available;
			var $option = $('<option>', {
				value: store.id,
				text: storeLabel(store, stockInfo),
				disabled: disabled
			});

			if (disabled) {
				$option.addClass('mhdk-checkout-store-option-disabled');
			}

			$field.append($option);
		});

		if (selected && $field.find('option[value="' + selected + '"]').length) {
			$field.val(selected);
		}
	}

	function sortStoresByDistance() {
		if (!hasGeolocation || !cfg.nearbyUrl) {
			return;
		}

		setDistanceText(cfg.i18n && cfg.i18n.sorting ? cfg.i18n.sorting : 'Sorting stores…');

		navigator.geolocation.getCurrentPosition(
			function (position) {
				var url = new URL(cfg.nearbyUrl, window.location.origin);
				url.searchParams.set('lat', String(position.coords.latitude));
				url.searchParams.set('lng', String(position.coords.longitude));

				fetch(url.toString(), {
					headers: {
						'X-WP-Nonce': cfg.nonce || ''
					}
				})
					.then(function (response) {
						return response.json();
					})
					.then(function (stores) {
						if (Array.isArray(stores) && stores.length) {
							rebuildOptions(stores);
						}
						setDistanceText('');
						refreshDistanceOnly();
					})
					.catch(function () {
						setDistanceText('');
					});
			},
			function () {
				setDistanceText(cfg.i18n && cfg.i18n.prompt ? cfg.i18n.prompt : 'Allow location to sort by distance.');
			}
		);
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
				var template = cfg.i18n && cfg.i18n.result ? cfg.i18n.result : 'About %1$s km from this store.';
				setDistanceText(template.replace('%1$s', String(payload.distance_km)));
			})
			.catch(function () {
				setDistanceText(cfg.i18n && cfg.i18n.error ? cfg.i18n.error : 'Could not calculate distance.');
			});
	}

	function refreshDistanceOnly() {
		var storeId = $field.val();
		if (!storeId || !hasGeolocation) {
			if (!storeId) {
				setDistanceText('');
				$hidden.val('');
			}
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

	function refresh() {
		refreshDistanceOnly();
	}

	$field.on('change', refresh);

	try {
		var saved = sessionStorage.getItem('mhdk_selected_store_id');
		if (saved && $field.find('option[value="' + saved + '"]').length) {
			$field.val(saved);
		}
	} catch (e) {
		// Ignore storage errors.
	}

	sortStoresByDistance();
})(jQuery);
