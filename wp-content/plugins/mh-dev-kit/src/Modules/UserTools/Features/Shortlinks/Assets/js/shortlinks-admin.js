( function () {
	var table = document.getElementById( 'mhdk-link-items-table' );

	if ( ! table ) {
		return;
	}

	var tbody = table.querySelector( 'tbody' );
	var addBtn = document.getElementById( 'mhdk-add-link-row' );
	var rowIndex = tbody ? tbody.rows.length : 0;

	function bindRemove( row ) {
		var btn = row.querySelector( '.mhdk-remove-link-row' );

		if ( ! btn ) {
			return;
		}

		btn.addEventListener( 'click', function () {
			row.remove();
		} );
	}

	function addRow() {
		var row = document.createElement( 'tr' );
		row.innerHTML =
			'<td><input type="text" class="widefat" name="mhdk_link_items[' +
			rowIndex +
			'][label]" value="" /></td>' +
			'<td><input type="url" class="widefat" name="mhdk_link_items[' +
			rowIndex +
			'][url]" value="" /></td>' +
			'<td><button type="button" class="button mhdk-remove-link-row">Remove</button></td>';
		tbody.appendChild( row );
		bindRemove( row );
		rowIndex += 1;
	}

	if ( tbody ) {
		Array.prototype.forEach.call( tbody.rows, bindRemove );
	}

	if ( addBtn ) {
		addBtn.addEventListener( 'click', addRow );
	}
} )();
