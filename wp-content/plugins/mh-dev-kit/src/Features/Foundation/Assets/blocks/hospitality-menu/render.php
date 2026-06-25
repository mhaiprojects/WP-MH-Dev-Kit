<?php
/**
 * Hospitality Menu block server render.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MH_DEV_KIT_PATH . 'vendor/autoload.php';

echo MH\DevKit\Features\Foundation\BlockRender::hospitality_menu( $attributes );
