( function () {
	var MAX_QTY = 99;

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

	function setInputQty( input, qty ) {
		input.value = String( clampQty( qty ) );
	}

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '.mhdk-order-menu__qty-btn' );

		if ( ! button ) {
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

	document.addEventListener( 'change', function ( event ) {
		if ( ! event.target.matches( '.mhdk-order-menu__qty-input' ) ) {
			return;
		}

		setInputQty( event.target, event.target.value );
	} );
} )();
