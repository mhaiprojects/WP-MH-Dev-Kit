<?php
/**
 * Frontend product store availability list.
 *
 * @var array<int, array<string, mixed>> $stores
 * @var int                              $threshold
 * @var int                              $product_id
 * @var bool                             $has_any_stock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$locator_url = add_query_arg(
	array(
		'product_id' => $product_id,
	),
	home_url( '/store-locator/' )
);
?>
<div class="mhdk-product-store-availability">
	<h3><?php esc_html_e( 'Available at these stores', 'mh-dev-kit' ); ?></h3>

	<?php if ( ! $has_any_stock ) : ?>
		<div class="mhdk-product-store-availability-empty">
			<p><?php esc_html_e( 'This product is not currently in stock at any store location.', 'mh-dev-kit' ); ?></p>
			<a class="mhdk-btn mhdk-find-store-btn" href="<?php echo esc_url( $locator_url ); ?>">
				<?php esc_html_e( 'Check other locations', 'mh-dev-kit' ); ?>
			</a>
		</div>
	<?php else : ?>
		<ul>
			<?php foreach ( $stores as $store ) : ?>
				<?php
				$qty   = (int) ( $store['stock_quantity'] ?? 0 );
				$class = $qty <= $threshold ? 'is-low-stock' : '';
				?>
				<li class="<?php echo esc_attr( $class ); ?>">
					<strong><?php echo esc_html( (string) $store['name'] ); ?></strong>
					— <?php echo esc_html( (string) $store['city'] . ', ' . (string) $store['state'] ); ?>
					(<?php echo esc_html( sprintf( _n( '%d unit', '%d units', $qty, 'mh-dev-kit' ), $qty ) ); ?>)
				</li>
			<?php endforeach; ?>
		</ul>
		<a class="mhdk-btn is-secondary mhdk-find-store-btn" href="<?php echo esc_url( $locator_url ); ?>">
			<?php esc_html_e( 'Find nearest store with stock', 'mh-dev-kit' ); ?>
		</a>
	<?php endif; ?>
</div>
