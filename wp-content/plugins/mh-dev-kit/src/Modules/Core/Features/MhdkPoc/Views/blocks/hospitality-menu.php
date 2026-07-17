<?php
/**
 * Hospitality menu block partial.
 *
 * @var WP_Post     $post          Menu post.
 * @var string      $subtitle      Subtitle meta.
 * @var string      $menu_note     Footnote meta.
 * @var string      $currency      Currency symbol.
 * @var bool        $show_prices   Show prices flag.
 * @var string|false $thumbnail_url Featured image URL.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="mhdk-block mhdk-hospitality-menu wp-block-group">
	<?php if ( $thumbnail_url ) : ?>
		<div class="mhdk-hospitality-menu__image">
			<img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="" />
		</div>
	<?php endif; ?>
	<h2 class="mhdk-hospitality-menu__title"><?php echo esc_html( get_the_title( $post ) ); ?></h2>
	<?php if ( '' !== $subtitle ) : ?>
		<p class="mhdk-hospitality-menu__subtitle"><?php echo esc_html( $subtitle ); ?></p>
	<?php endif; ?>
	<div class="mhdk-hospitality-menu__content entry-content">
		<?php echo wp_kses_post( apply_filters( 'the_content', $post->post_content ) ); ?>
	</div>
	<?php if ( '' !== $menu_note ) : ?>
		<p class="mhdk-hospitality-menu__note"><em><?php echo esc_html( $menu_note ); ?></em></p>
	<?php endif; ?>
</div>
