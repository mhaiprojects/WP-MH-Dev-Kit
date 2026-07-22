(function () {
	'use strict';

	var cfg = window.mhdkStoreLocator || {};
	var root = document.querySelector('.mhdk-store-locator');

	if (!root || !navigator.geolocation) {
		return;
	}

	var statusEl = root.querySelector('.mhdk-store-locator-status');
	var locateBtn = root.querySelector('.mhdk-store-locator-locate');
	var items = Array.prototype.slice.call(root.querySelectorAll('.mhdk-store-locator-item'));

	function setStatus(text) {
		if (statusEl) {
			statusEl.textContent = text || '';
		}
	}

	function formatDistance(km) {
		return (cfg.i18n && cfg.i18n.distance ? cfg.i18n.distance : '%1$s km away').replace('%1$s', String(km));
	}

	function applyStores(stores) {
		var byId = {};
		stores.forEach(function (store) {
			byId[store.id] = store;
		});

		items.forEach(function (item) {
			var id = item.getAttribute('data-store-id');
			var store = byId[id];
			var distanceEl = item.querySelector('.mhdk-store-locator-distance');

			if (!store) {
				item.hidden = true;
				return;
			}

			item.hidden = false;
			if (distanceEl) {
				distanceEl.textContent = formatDistance(store.distance_km);
			}
		});

		items.sort(function (a, b) {
			var aStore = byId[a.getAttribute('data-store-id')];
			var bStore = byId[b.getAttribute('data-store-id')];
			if (!aStore || !bStore) {
				return 0;
			}
			return aStore.distance_metres - bStore.distance_metres;
		}).forEach(function (item) {
			root.querySelector('.mhdk-store-locator-list').appendChild(item);
		});
	}

	function fetchNearby(lat, lng) {
		var url = new URL(cfg.restUrl, window.location.origin);
		url.searchParams.set('lat', String(lat));
		url.searchParams.set('lng', String(lng));

		return fetch(url.toString(), {
			headers: {
				'X-WP-Nonce': cfg.nonce || ''
			}
		}).then(function (response) {
			return response.json();
		});
	}

	function locate() {
		setStatus(cfg.i18n && cfg.i18n.waiting ? cfg.i18n.waiting : 'Finding stores…');

		navigator.geolocation.getCurrentPosition(
			function (position) {
				fetchNearby(position.coords.latitude, position.coords.longitude)
					.then(function (stores) {
						if (!stores.length) {
							setStatus(cfg.i18n && cfg.i18n.noStores ? cfg.i18n.noStores : 'No stores found.');
							return;
						}
						applyStores(stores);
						setStatus('');
					})
					.catch(function () {
						setStatus(cfg.i18n && cfg.i18n.error ? cfg.i18n.error : 'Could not load stores.');
					});
			},
			function () {
				setStatus(cfg.i18n && cfg.i18n.error ? cfg.i18n.error : 'Could not read location.');
			}
		);
	}

	if (locateBtn) {
		locateBtn.addEventListener('click', locate);
	}

	items.forEach(function (item) {
		var selectBtn = item.querySelector('.mhdk-store-locator-select');
		if (!selectBtn) {
			return;
		}
		selectBtn.addEventListener('click', function () {
			items.forEach(function (el) {
				el.classList.remove('is-selected');
			});
			item.classList.add('is-selected');
		});
	});
})();
