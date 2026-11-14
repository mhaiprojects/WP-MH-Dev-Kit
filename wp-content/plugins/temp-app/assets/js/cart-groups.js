(function ($) {
	'use strict';

	function escapeKey(key) {
		if (typeof CSS !== 'undefined' && typeof CSS.escape === 'function') {
			return CSS.escape(key);
		}
		return String(key).replace(/[^a-zA-Z0-9_\-]/g, '');
	}

	function getGroupsData() {
		var $json = $('#temp-app-cart-groups-data');
		if ($json.length) {
			try {
				var parsed = JSON.parse($json.text());
				if (parsed && Array.isArray(parsed.groups)) {
					return parsed.groups;
				}
			} catch (e) {
				/* fall through */
			}
		}
		if (window.TempAppCartGroups && Array.isArray(window.TempAppCartGroups.groups)) {
			return window.TempAppCartGroups.groups;
		}
		return [];
	}

	function columnCount($table) {
		var n = $table.find('thead th').length;
		return n > 0 ? n : 6;
	}

	function cleanup($tbody) {
		$tbody.find('tr.temp-app-group-head, tr.temp-app-group-spacer, tr.temp-app-clone').remove();
		$tbody.find('tr.cart_item, tr.woocommerce-cart-form__cart-item').each(function () {
			var $row = $(this);
			$row.removeClass('temp-app-group-row temp-app-group-last temp-app-group--set temp-app-group--leftover temp-app-group--other');
			$row.removeAttr('data-temp-app-group');
			$row.find('.temp-app-set-qty-badge').remove();
		});
	}

	function findRowByKey($tbody, key) {
		return $tbody.find('tr.temp-app-ci-' + escapeKey(key)).filter(':not(.temp-app-clone)').first();
	}

	function setQtyLabel(qty) {
		return '\u00d7 ' + qty + ' in this group';
	}

	function makeCloneRow($orig, qty, cols, type, groupId) {
		var $clone = $('<tr class="cart_item temp-app-clone woocommerce-cart-form__cart-item"></tr>');
		$clone.addClass('temp-app-group-row temp-app-group--' + type);
		$clone.attr('data-temp-app-group', groupId);

		$clone.append($('<td class="product-remove" aria-hidden="true"></td>'));

		var $thumb = $orig.find('td.product-thumbnail').first().clone();
		var $name = $orig.find('td.product-name').first().clone();
		$name.find('input, select, textarea, button, .temp-app-set-qty-badge').remove();
		$name.find('[name],[id]').removeAttr('name').removeAttr('id');
		$clone.append($thumb).append($name);

		var span = cols - 3;
		if (span < 1) {
			span = 1;
		}
		$clone.append(
			$('<td class="temp-app-clone-qty" colspan="' + span + '"></td>').html(
				'<span class="temp-app-set-qty-label">' + setQtyLabel(qty) + '</span>'
			)
		);

		return $clone;
	}

	function makeHeadingRow(group, cols) {
		var type = group.type || 'other';
		var $tr = $('<tr class="temp-app-group-head temp-app-group--' + type + '"></tr>');
		var $td = $('<td colspan="' + cols + '"></td>');
		$td.append($('<span class="temp-app-group-title"></span>').text(group.heading || ''));
		if (group.subheading) {
			$td.append($('<span class="temp-app-group-sub"></span>').text(group.subheading));
		}
		$tr.append($td);
		return $tr;
	}

	function makeSpacerRow(cols) {
		return $('<tr class="temp-app-group-spacer"><td colspan="' + cols + '"></td></tr>');
	}

	function decorateRow($row, group, isLastInGroup) {
		var type = group.type || 'other';
		$row.addClass('temp-app-group-row temp-app-group--' + type);
		$row.attr('data-temp-app-group', group.id || '');
		if (isLastInGroup) {
			$row.addClass('temp-app-group-last');
		}
	}

	function addBadge($row, qty) {
		var $name = $row.find('td.product-name').first();
		if (!$name.length) {
			return;
		}
		if ($name.find('.temp-app-set-qty-badge').length) {
			return;
		}
		$name.append($('<span class="temp-app-set-qty-badge"></span>').text(setQtyLabel(qty)));
	}

	function rebuild() {
		var $form = $('form.woocommerce-cart-form');
		if (!$form.length) {
			return;
		}

		var groups = getGroupsData();
		if (!groups.length) {
			return;
		}

		var $table = $form.find('table.shop_table').first();
		var $tbody = $table.find('tbody').first();
		if (!$tbody.length) {
			return;
		}

		var cols = columnCount($table);
		var $actions = $tbody.find('tr').filter(function () {
			return $(this).find('td.actions').length > 0;
		}).first();

		cleanup($tbody);

		var seenKeys = {};
		var keyAppearances = {};
		groups.forEach(function (group) {
			(group.items || []).forEach(function (item) {
				if (item && item.key) {
					keyAppearances[item.key] = (keyAppearances[item.key] || 0) + 1;
				}
			});
		});

		var insertBefore = $actions.length ? $actions : null;

		groups.forEach(function (group, gi) {
			var $head = makeHeadingRow(group, cols);
			if (insertBefore) {
				$head.insertBefore(insertBefore);
			} else {
				$tbody.append($head);
			}

			var items = group.items || [];
			items.forEach(function (item, ii) {
				var key = item.key;
				var qty = parseInt(item.qty, 10) || 0;
				if (!key || qty < 1) {
					return;
				}

				var isLastItem = ii === items.length - 1;
				var $row = findRowByKey($tbody, key);
				if (!$row.length) {
					return;
				}

				if (!seenKeys[key]) {
					seenKeys[key] = true;
					decorateRow($row, group, isLastItem);
					if ((keyAppearances[key] || 0) > 1) {
						addBadge($row, qty);
					}
					if (insertBefore) {
						$row.insertBefore(insertBefore);
					} else {
						$tbody.append($row);
					}
				} else {
					var $clone = makeCloneRow($row, qty, cols, group.type, group.id);
					if (isLastItem) {
						$clone.addClass('temp-app-group-last');
					}
					if (insertBefore) {
						$clone.insertBefore(insertBefore);
					} else {
						$tbody.append($clone);
					}
				}
			});

			if (gi < groups.length - 1) {
				var $spacer = makeSpacerRow(cols);
				if (insertBefore) {
					$spacer.insertBefore(insertBefore);
				} else {
					$tbody.append($spacer);
				}
			}
		});
	}

	function scheduleRebuild() {
		window.requestAnimationFrame(function () {
			rebuild();
		});
	}

	$(function () {
		scheduleRebuild();
	});

	$(document.body).on('updated_wc_div updated_cart_totals', function () {
		scheduleRebuild();
	});
})(jQuery);