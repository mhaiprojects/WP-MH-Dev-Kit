<?php
/**
 * Product meta box — stock per store.
 *
 * @var array<int, array<string, mixed>> $stores
 * @var array<string, int>               $levels
 * @var int                              $product_id
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
