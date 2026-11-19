<?php
/**
 * Product meta box — stock per store.
 *
 * @var array<int, array<string, mixed>> $stores
 * @var array<string, int>               $levels
 * @var int                              $product_id
 * @var string                           $bulk_edit_url
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="widefat striped">
	<thead>
		<tr>
			<th><?php esc_html_e( 'Store', 'mh-dev-kit' ); ?></th>
			<th><?php esc_html_e( 'Quantity', 'mh-dev-kit' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $stores as $store ) : ?>
			<tr>
				<td><?php echo esc_html( (string) $store['name'] ); ?></td>
				<td>
					<input
						type="number"
						min="0"
						class="small-text"
						name="mhdk_store_stock[<?php echo esc_attr( (string) $store['id'] ); ?>]"
						value="<?php echo esc_attr( (string) ( $levels[ (string) $store['id'] ] ?? 0 ) ); ?>"
					/>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<p class="description">
	<a href="<?php echo esc_url( $bulk_edit_url ); ?>"><?php esc_html_e( 'Bulk edit all products →', 'mh-dev-kit' ); ?></a>
</p>
