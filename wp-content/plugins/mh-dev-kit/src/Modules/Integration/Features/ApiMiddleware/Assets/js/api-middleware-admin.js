(function ($) {
	'use strict';

	var cfg = window.mhdkApiMiddleware || {};
	var $form = $('#mhdk-api-connection-form');
	var $testResult = $('#mhdk-api-test-result');
	var $formTitle = $('#mhdk-api-form-title');
	var $formMode = $('#mhdk-api-form-mode');
	var $resetBtn = $('#mhdk-api-reset-form');

	if (!$form.length) {
		return;
	}

	function field(name) {
		return $form.find('[name="' + name + '"]');
	}

	function setMode(editing) {
		if (editing) {
			$formTitle.text('Edit connection');
			$formMode.text(cfg.i18n && cfg.i18n.editMode ? cfg.i18n.editMode : 'Editing connection.');
			$resetBtn.show();
			field('id').prop('readonly', true);
		} else {
			$formTitle.text('Add connection');
			$formMode.text(cfg.i18n && cfg.i18n.createMode ? cfg.i18n.createMode : 'Creating new connection.');
			$resetBtn.hide();
			field('id').prop('readonly', false);
		}
	}

	function resetForm() {
		$form[0].reset();
		field('endpoint').val('/');
		field('auth_header').val('Authorization');
		field('enabled').prop('checked', true);
		field('auth_value').val('');
		setMode(false);
		$testResult.text('');
	}

	function loadConnection(connection) {
		field('id').val(connection.id || '');
		field('name').val(connection.name || '');
		field('base_url').val(connection.base_url || '');
		field('endpoint').val(connection.endpoint || '/');
		field('method').val(connection.method || 'GET');
		field('auth_type').val(connection.auth_type || 'none');
		field('auth_header').val(connection.auth_header || 'Authorization');
		field('auth_value').val('');
		field('schedule').val(connection.schedule || 'manual');
		field('target_type').val(connection.target_type || 'option');
		field('target_key').val(connection.target_key || '');
		field('enabled').prop('checked', !!connection.enabled);
		setMode(true);
		$testResult.text('');
		$('html, body').animate({ scrollTop: $form.offset().top - 40 }, 200);
	}

	$('.mhdk-api-delete').on('click', function (event) {
		var message = cfg.i18n && cfg.i18n.confirmDelete ? cfg.i18n.confirmDelete : 'Delete this connection?';
		if (!window.confirm(message)) {
			event.preventDefault();
		}
	});

	$('.mhdk-api-edit').on('click', function () {
		var $row = $(this).closest('tr');
		var raw = $row.attr('data-connection');
		if (!raw) {
			return;
		}
		try {
			loadConnection(JSON.parse(raw));
		} catch (e) {
			return;
		}
	});

	$resetBtn.on('click', resetForm);

	$('#mhdk-api-test').on('click', function () {
		var payload = {
			id: field('id').val(),
			name: field('name').val(),
			base_url: field('base_url').val(),
			endpoint: field('endpoint').val(),
			method: field('method').val(),
			auth_type: field('auth_type').val(),
			auth_header: field('auth_header').val(),
			auth_value: field('auth_value').val(),
			schedule: field('schedule').val(),
			target_type: field('target_type').val(),
			target_key: field('target_key').val(),
			enabled: field('enabled').is(':checked')
		};

		$testResult.text(cfg.i18n && cfg.i18n.testing ? cfg.i18n.testing : 'Testing…');

		fetch(cfg.restUrl + 'test', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': cfg.nonce || ''
			},
			body: JSON.stringify(payload)
		})
			.then(function (response) {
				return response.json().then(function (data) {
					return { ok: response.ok, data: data };
				});
			})
			.then(function (result) {
				if (result.ok && result.data && result.data.success) {
					var msg = cfg.i18n && cfg.i18n.testOk ? cfg.i18n.testOk : 'Connection successful.';
					if (result.data.status) {
						msg += ' HTTP ' + result.data.status + '.';
					}
					$testResult.text(msg);
					return;
				}

				var fail = cfg.i18n && cfg.i18n.testFail ? cfg.i18n.testFail : 'Connection failed.';
				if (result.data && result.data.message) {
					fail += ' ' + result.data.message;
				}
				$testResult.text(fail);
			})
			.catch(function () {
				$testResult.text(cfg.i18n && cfg.i18n.testFail ? cfg.i18n.testFail : 'Connection failed.');
			});
	});
})(jQuery);
