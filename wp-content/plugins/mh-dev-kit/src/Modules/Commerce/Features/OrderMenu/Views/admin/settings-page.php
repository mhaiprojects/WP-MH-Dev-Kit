<?php
/**
 * Order Menu settings page — uses shared Settings API shell.
 *
 * @var string $page_slug
 * @var string $option_group
 * @var string $footer_note
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

\MH\DevKit\Core\ViewRenderer::display(
	'Core/Views/admin/partials/settings-page.php',
	array(
		'page_slug'    => $page_slug,
		'option_group' => $option_group,
		'footer_note'  => $footer_note,
	)
);
