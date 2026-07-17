( function () {
	var cfg = window.mhdkOrderMenu || {};
	var MAX_QTY = cfg.maxQty || 99;

	function clampQty( value ) {
		var qty = parseInt( value, 10 );
		if ( isNaN( qty ) || qty < 0 ) {
			return 0;
		}
		if ( qty > MAX_QTY ) {
			return MAX_QTY;
		}
		return qty;
	}

	function formatMoney( amount ) {
		var symbol = cfg.currency || '$';
		return symbol + amount.toFixed( 2 );
	}

	function updateTotals( root ) {
		var total = 0;

		root.querySelectorAll( '.mhdk-order-menu__row' ).forEach( function ( row ) {
			if ( row.classList.contains( 'mhdk-order-menu__section' ) ) {
				return;
			}

			var price = parseFloat( row.getAttribute( 'data-price' ) || '0' );
			var input = row.querySelector( '.mhdk-order-menu__qty-input' );
			var subtotalEl = row.querySelector( '.mhdk-order-menu__subtotal-value' );
			var qty = input ? clampQty( input.value ) : 0;
			var subtotal = price * qty;

			if ( subtotalEl ) {
				subtotalEl.textContent = formatMoney( subtotal );
			}

			total += subtotal;
		} );

		var totalEl = root.querySelector( '.mhdk-order-menu__order-total-value' );
		if ( totalEl ) {
			totalEl.textContent = formatMoney( total );
		}
	}

	function setInputQty( input, qty ) {
		input.value = String( clampQty( qty ) );
		var root = input.closest( '.mhdk-order-menu' );
		if ( root ) {
			updateTotals( root );
		}
	}

	function formDataFromRow( row, form ) {
		var data = new FormData( form );
		var productId = row.getAttribute( 'data-product-id' );
		var input = row.querySelector( '.mhdk-order-menu__qty-input' );
		data.set( 'action', cfg.ajaxAction );
		data.set( 'nonce', cfg.nonce );
		data.set( 'product_id', productId );
		data.set( 'quantity', input ? input.value : '0' );
		data.delete( 'mhdk_order_menu_submit' );
		return data;
	}

	function formDataBulk( form ) {
		var data = new FormData( form );
		data.set( 'action', cfg.ajaxAction );
		data.set( 'nonce', cfg.nonce );
		data.set( 'bulk', '1' );
		return data;
	}

	function updateCartQtyDisplay( root, cartQuantities ) {
		if ( ! cartQuantities ) {
			return;
		}

		root.querySelectorAll( '.mhdk-order-menu__row' ).forEach( function ( row ) {
			var id = row.getAttribute( 'data-product-id' );
			var el = row.querySelector( '.mhdk-order-menu__cart-qty-value' );
			if ( el && id && typeof cartQuantities[ id ] !== 'undefined' ) {
				el.textContent = String( cartQuantities[ id ] );
			}
		} );
	}

	function setLoading( button, loading ) {
		if ( ! button ) {
			return;
		}

		if ( loading ) {
			button.disabled = true;
			button.classList.add( 'is-loading' );
			if ( ! button.dataset.originalText ) {
				button.dataset.originalText = button.textContent;
			}
			button.textContent = ( cfg.i18n && cfg.i18n.adding ) || 'Adding…';
		} else {
			button.disabled = false;
			button.classList.remove( 'is-loading' );
			if ( button.dataset.originalText ) {
				button.textContent = button.dataset.originalText;
			}
		}
	}

	function showRowMessage( row, message, isError ) {
		var el = row.querySelector( '.mhdk-order-menu__row-message' );
		if ( ! el ) {
			return;
		}
		el.textContent = message || '';
		el.classList.toggle( 'is-error', !! isError );
	}

	function ajaxAdd( form, row, button ) {
		var data = row ? formDataFromRow( row, form ) : formDataBulk( form );

		setLoading( button, true );

		fetch( cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: data,
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( payload ) {
				setLoading( button, false );

				var root = form.closest( '.mhdk-order-menu' );

				if ( payload.success ) {
					var notice = form.querySelector( '.mhdk-order-menu__notice' );
					if ( notice ) {
						notice.textContent = payload.data.message || '';
					}

					if ( row ) {
						showRowMessage( row, payload.data.message, false );
					}

					if ( root ) {
						updateCartQtyDisplay( root, payload.data.cart_quantities );
					}

					if ( payload.data.clear_inputs && root ) {
						root.querySelectorAll( '.mhdk-order-menu__qty-input' ).forEach( function ( input ) {
							input.value = '0';
						} );
						updateTotals( root );
					}
				} else {
					var msg = ( payload.data && payload.data.message ) || ( cfg.i18n && cfg.i18n.error ) || 'Error';
					if ( row ) {
						showRowMessage( row, msg, true );
					} else {
						var noticeErr = form.querySelector( '.mhdk-order-menu__notice' );
						if ( noticeErr ) {
							noticeErr.textContent = msg;
						}
					}
				}
			} )
			.catch( function () {
				setLoading( button, false );
				if ( row ) {
					showRowMessage( row, ( cfg.i18n && cfg.i18n.error ) || 'Error', true );
				}
			} );
	}

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '.mhdk-order-menu__qty-btn' );

		if ( button && button.classList.contains( 'mhdk-order-menu__qty-btn--add' ) && cfg.ajaxEnabled ) {
			event.preventDefault();
			var row = button.closest( '.mhdk-order-menu__row' );
			var form = button.closest( '.mhdk-order-menu__form' );
			if ( row && form ) {
				ajaxAdd( form, row, button );
			}
			return;
		}

		if ( ! button || button.classList.contains( 'mhdk-order-menu__qty-btn--add' ) ) {
			if ( event.target.closest( '.mhdk-order-menu__bulk-add' ) && cfg.ajaxEnabled ) {
				event.preventDefault();
				var bulkForm = event.target.closest( '.mhdk-order-menu__form' );
				if ( bulkForm ) {
					ajaxAdd( bulkForm, null, event.target.closest( '.mhdk-order-menu__bulk-add' ) );
				}
			}
			return;
		}

		var panel = button.closest( '.mhdk-order-menu__qty-panel' );
		if ( ! panel ) {
			return;
		}

		var input = panel.querySelector( '.mhdk-order-menu__qty-input' );
		if ( ! input ) {
			return;
		}

		var action = button.getAttribute( 'data-action' );
		var current = clampQty( input.value );

		if ( action === 'increase' ) {
			setInputQty( input, current + 1 );
		} else if ( action === 'decrease' ) {
			setInputQty( input, current - 1 );
		} else if ( action === 'clear' ) {
			setInputQty( input, 0 );
		}
	} );

	document.addEventListener( 'input', function ( event ) {
		if ( event.target.matches( '.mhdk-order-menu__search' ) ) {
			var query = event.target.value.toLowerCase();
			var root = event.target.closest( '.mhdk-order-menu' );
			if ( ! root ) {
				return;
			}
			root.querySelectorAll( '.mhdk-order-menu__row[data-name]' ).forEach( function ( row ) {
				var name = ( row.getAttribute( 'data-name' ) || '' ).toLowerCase();
				row.style.display = name.indexOf( query ) !== -1 ? '' : 'none';
			} );
		}

		if ( event.target.matches( '.mhdk-order-menu__qty-input' ) ) {
			setInputQty( event.target, event.target.value );
		}
	} );

	document.addEventListener( 'click', function ( event ) {
		if ( ! event.target.closest( '.mhdk-order-menu__clear-all' ) ) {
			return;
		}

		var root = event.target.closest( '.mhdk-order-menu' );
		if ( ! root ) {
			return;
		}

		root.querySelectorAll( '.mhdk-order-menu__qty-input' ).forEach( function ( input ) {
			input.value = '0';
		} );
		updateTotals( root );
	} );

	document.querySelectorAll( '.mhdk-order-menu' ).forEach( function ( root ) {
		updateTotals( root );
	} );
} )();
