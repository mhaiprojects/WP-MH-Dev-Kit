<?php
/**
 * Web links block partial.
 *
 * @var array<int, array<string, mixed>> $items  Link items.
 * @var string                           $layout list|grid.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="mhdk-block mhdk-web-links mhdk-web-links--<?php echo esc_attr( $layout ); ?> wp-block-group">
	<?php if ( empty( $items ) ) : ?>
		<p class="mhdk-web-links__empty"><?php esc_html_e( 'No web links published yet.', 'mh-dev-kit' ); ?></p>
	<?php else : ?>
		<ul class="mhdk-web-links__list">
			<?php foreach ( $items as $item ) : ?>
				<li class="mhdk-web-links__item">
					<?php if ( ! empty( $item['thumbnail'] ) ) : ?>
						<img class="mhdk-web-links__thumb" src="<?php echo esc_url( $item['thumbnail'] ); ?>" alt="" />
					<?php endif; ?>
					<div class="mhdk-web-links__body">
						<a class="mhdk-web-links__link" href="<?php echo esc_url( $item['url'] ); ?>" <?php echo ! empty( $item['new_tab'] ) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
							<?php echo esc_html( $item['title'] ); ?>
						</a>
						<?php if ( ! empty( $item['description'] ) ) : ?>
							<p class="mhdk-web-links__desc"><?php echo esc_html( $item['description'] ); ?></p>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
