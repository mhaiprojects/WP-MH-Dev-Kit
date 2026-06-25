<?php
/**
 * Callout block server render.
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MH_DEV_KIT_PATH . 'vendor/autoload.php';

echo MH\DevKit\Features\MhdkPoc\BlockRender::callout( $attributes );
