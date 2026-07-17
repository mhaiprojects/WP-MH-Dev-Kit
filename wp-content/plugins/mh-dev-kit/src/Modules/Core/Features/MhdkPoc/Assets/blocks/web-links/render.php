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

echo MH\DevKit\Modules\Core\Features\MhdkPoc\BlockRender::web_links( $attributes );
