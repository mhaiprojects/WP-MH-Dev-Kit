<?php
/**
 * Frontend product store availability list.
 *
 * @var array<int, array<string, mixed>> $stores
 * @var int                              $threshold
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="mhdk-product-store-availability">
	<h3><?php esc_html_e( 'Available at these stores', 'mh-dev-kit' ); ?></h3>
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
</div>
