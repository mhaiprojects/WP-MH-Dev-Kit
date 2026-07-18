<?php
/**
 * Markdown block server render.
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MH_DEV_KIT_PATH . 'vendor/autoload.php';

use MH\DevKit\Modules\ContentTools\Features\MarkdownEditor\Models\MarkdownModel;

$content = (string) ( $attributes['content'] ?? '' );
$model   = new MarkdownModel();

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in model.
echo $model->render_html( $content );
