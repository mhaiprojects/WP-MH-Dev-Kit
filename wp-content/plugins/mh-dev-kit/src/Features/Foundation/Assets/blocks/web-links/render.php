<?php
/**
 * Web Links block server render.
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MH_DEV_KIT_PATH . 'vendor/autoload.php';

echo MH\DevKit\Features\Foundation\BlockRender::web_links( $attributes );
