<?php
/**
 * Family Tree Vue app mount point.
 *
 * @var int    $post_id   Tree post ID.
 * @var string $title     Tree title.
 * @var string $tree_json Initial tree JSON.
 * @var bool   $editable  Whether editing is enabled.
 * @var string $rest_url  REST save endpoint.
 * @var string $nonce     REST nonce.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div
	class="mhdk-family-tree-app"
	id="mhdk-family-tree-<?php echo esc_attr( (string) $post_id ); ?>"
	data-post-id="<?php echo esc_attr( (string) $post_id ); ?>"
	data-tree="<?php echo esc_attr( $tree_json ); ?>"
	data-editable="<?php echo esc_attr( $editable ? '1' : '0' ); ?>"
	data-rest-url="<?php echo esc_url( $rest_url ); ?>"
	data-nonce="<?php echo esc_attr( $nonce ); ?>"
>
	<h3 class="mhdk-family-tree-title"><?php echo esc_html( $title ); ?></h3>
	<div class="mhdk-family-tree-mount"></div>
</div>
