<?php
/**
 * Children Pages block server render.
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MH_DEV_KIT_PATH . 'vendor/autoload.php';

use MH\DevKit\Modules\ContentTools\Features\ChildrenPages\Models\ChildrenPagesModel;

$model = new ChildrenPagesModel();

$atts = array(
	'parent_id' => (string) ( $attributes['parentId'] ?? 0 ),
	'orderby'   => (string) ( $attributes['orderby'] ?? 'menu_order' ),
	'order'     => (string) ( $attributes['order'] ?? 'ASC' ),
	'limit'     => (string) ( $attributes['limit'] ?? 50 ),
);

$parent_id = $model->resolve_parent_id( $atts, (int) get_the_ID() );
$children  = $model->get_children( $atts, $parent_id > 0 ? $parent_id : null );

if ( array() === $children ) {
	echo '<p class="mhdk-children-pages-empty">' . esc_html__( 'No child pages found.', 'mh-dev-kit' ) . '</p>';
	return;
}

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in model.
echo $model->render_list( $children );
