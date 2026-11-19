(function ($) {
	'use strict';

	var $form = $('#mhdk-inventory-product-form');
	var $select = $('#mhdk-inventory-product');

	if (!$form.length || !$select.length) {
		return;
	}

	if ($.fn.selectWoo) {
		$select.selectWoo({
			allowClear: false,
			width: '400px'
		});
	}

	$select.on('change', function () {
		$form.submit();
	});
})(jQuery);
