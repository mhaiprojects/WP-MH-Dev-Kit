(function () {
	'use strict';

	var cfg = window.mhdkStoreLocator || {};
	var root = document.querySelector('.mhdk-store-locator');
	var SESSION_KEY = 'mhdk_selected_store_id';

	if (!root) {
		return;
	}

	var statusEl = root.querySelector('.mhdk-store-locator-status');
	var locateBtn = root.querySelector('.mhdk-store-locator-locate');
	var filterState = root.querySelector('.mhdk-store-locator-filter-state');
	var filterPostcode = root.querySelector('.mhdk-store-locator-filter-postcode');
	var filterBtn = root.querySelector('.mhdk-store-locator-filter-apply');
	var items = Array.prototype.slice.call(root.querySelectorAll('.mhdk-store-locator-item'));
	var productId = parseInt(root.getAttribute('data-product-id') || '0', 10);
	var hasGeolocation = !!(navigator.geolocation);

	function setStatus(text) {
		if (statusEl) {
			statusEl.textContent = text || '';
		}
	}

	function setLoading(loading) {
		if (!locateBtn) {
			return;
		}

		locateBtn.disabled = !!loading;
		locateBtn.setAttribute('aria-busy', loading ? 'true' : 'false');

		if (loading) {
			locateBtn.classList.add('is-loading');
			setStatus(cfg.i18n && cfg.i18n.waiting ? cfg.i18n.waiting : 'Finding stores…');
		} else {
			locateBtn.classList.remove('is-loading');
		}
	}

	function formatDistance(km) {
		return (cfg.i18n && cfg.i18n.distance ? cfg.i18n.distance : 'About %1$s km from you').replace('%1$s', String(km));
	}

	function formatOutside(km) {
		var template = cfg.i18n && cfg.i18n.outsideRadius ? cfg.i18n.outsideRadius : 'Outside delivery area (%1$s km)';
		return template.replace('%1$s', String(km));
	}

	function persistSelection(storeId) {
		try {
			sessionStorage.setItem(SESSION_KEY, storeId);
		} catch (e) {
			// Ignore storage errors.
		}
	}

	function restoreSelection() {
		var saved = '';

		try {
			saved = sessionStorage.getItem(SESSION_KEY) || '';
		} catch (e) {
			saved = '';
		}

		if (!saved) {
			return;
		}

		items.forEach(function (item) {
			if (item.getAttribute('data-store-id') === saved) {
				item.classList.add('is-selected');
			}
		});
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
			var stockEl = item.querySelector('.mhdk-store-locator-stock');

			item.classList.remove('is-out-of-range', 'is-no-stock');
			item.hidden = false;

			if (!store) {
				item.classList.add('is-out-of-range');
				if (distanceEl) {
					distanceEl.textContent = '';
				}
				return;
			}

			if (distanceEl) {
				if (store.within_radius === false) {
					item.classList.add('is-out-of-range');
					distanceEl.textContent = formatOutside(store.distance_km);
				} else {
					distanceEl.textContent = formatDistance(store.distance_km);
				}
			}

			if (stockEl && typeof store.stock_quantity !== 'undefined') {
				if (store.stock_quantity <= 0) {
					item.classList.add('is-no-stock');
					stockEl.textContent = cfg.i18n && cfg.i18n.outOfStock ? cfg.i18n.outOfStock : 'Out of stock';
				} else {
					var stockTemplate = cfg.i18n && cfg.i18n.inStock ? cfg.i18n.inStock : '%1$s in stock';
					stockEl.textContent = stockTemplate.replace('%1$s', String(store.stock_quantity));
				}
			}

			if (productId > 0 && store.has_stock === false) {
				item.classList.add('is-no-stock');
			}
		});

		var list = root.querySelector('.mhdk-store-locator-list');
		if (!list) {
			return;
		}

		items.sort(function (a, b) {
			var aStore = byId[a.getAttribute('data-store-id')];
			var bStore = byId[b.getAttribute('data-store-id')];
			if (!aStore && !bStore) {
				return 0;
			}
			if (!aStore) {
				return 1;
			}
			if (!bStore) {
				return -1;
			}
			return aStore.distance_metres - bStore.distance_metres;
		}).forEach(function (item) {
			list.appendChild(item);
		});
	}

	function fetchNearby(lat, lng) {
		var url = new URL(cfg.restUrl, window.location.origin);
		url.searchParams.set('lat', String(lat));
		url.searchParams.set('lng', String(lng));

		if (productId > 0) {
			url.searchParams.set('product_id', String(productId));
		}

		return fetch(url.toString(), {
			headers: {
				'X-WP-Nonce': cfg.nonce || ''
			}
		}).then(function (response) {
			return response.json();
		});
	}

	function locate() {
		if (!hasGeolocation) {
			setStatus(cfg.i18n && cfg.i18n.unsupported ? cfg.i18n.unsupported : 'Geolocation is not supported. Use the filters below.');
			return;
		}

		setLoading(true);

		navigator.geolocation.getCurrentPosition(
			function (position) {
				fetchNearby(position.coords.latitude, position.coords.longitude)
					.then(function (stores) {
						setLoading(false);
						if (!stores.length) {
							setStatus(cfg.i18n && cfg.i18n.noStores ? cfg.i18n.noStores : 'No stores found.');
							return;
						}
						applyStores(stores);
						setStatus('');
					})
					.catch(function () {
						setLoading(false);
						setStatus(cfg.i18n && cfg.i18n.error ? cfg.i18n.error : 'Could not load stores.');
					});
			},
			function () {
				setLoading(false);
				setStatus(cfg.i18n && cfg.i18n.denied ? cfg.i18n.denied : 'Location access denied. Use state or postcode filters below.');
			}
		);
	}

	function filterByText() {
		var state = filterState ? filterState.value.trim().toLowerCase() : '';
		var postcode = filterPostcode ? filterPostcode.value.trim().toLowerCase() : '';

		items.forEach(function (item) {
			var itemState = (item.getAttribute('data-state') || '').toLowerCase();
			var itemPostcode = (item.getAttribute('data-postcode') || '').toLowerCase();
			var match = true;

			if (state && itemState.indexOf(state) === -1) {
				match = false;
			}

			if (postcode && itemPostcode.indexOf(postcode) === -1) {
				match = false;
			}

			item.hidden = !match;
		});

		setStatus(cfg.i18n && cfg.i18n.filtered ? cfg.i18n.filtered : 'Showing stores matching your filters.');
	}

	if (locateBtn) {
		locateBtn.addEventListener('click', locate);
	}

	if (filterBtn) {
		filterBtn.addEventListener('click', filterByText);
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
			persistSelection(item.getAttribute('data-store-id') || '');
		});
	});

	restoreSelection();

	if (!hasGeolocation) {
		setStatus(cfg.i18n && cfg.i18n.unsupported ? cfg.i18n.unsupported : 'Geolocation is not supported. Use the filters below.');
	}
})();
