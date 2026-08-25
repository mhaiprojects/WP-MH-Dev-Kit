<?php
/**
 * Hybrid pricing settings and price list editor.
 *
 * @var array<string, mixed>              $settings
 * @var list<array<string, mixed>>       $price_lists
 * @var list<array{id: int, label: string}> $products
 * @var list<array{id: int, label: string}> $categories
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MH\DevKit\Modules\Commerce\Features\HybridPricing\Models\HybridPricingSettingsModel;

$list = $price_lists[0] ?? array(
	'id'      => 'wholesale-default',
	'name'    => __( 'Wholesale Default', 'mh-dev-kit' ),
	'enabled' => true,
	'rules'   => array(),
);
$rules = is_array( $list['rules'] ?? null ) ? $list['rules'] : array();
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<hr class="wp-header-end" />

	<?php if ( isset( $_GET['mhdk_saved'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Price lists saved.', 'mh-dev-kit' ); ?></p></div>
	<?php endif; ?>

	<?php settings_errors(); ?>

	<h2><?php esc_html_e( 'Visibility & price display', 'mh-dev-kit' ); ?></h2>
	<form method="post" action="options.php">
		<?php
		settings_fields( 'mhdk_hybrid_pricing' );
		do_settings_sections( HybridPricingSettingsModel::SETTINGS_SLUG );
		submit_button();
		?>
	</form>

	<h2><?php esc_html_e( 'B2B price list', 'mh-dev-kit' ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'Rules apply to B2B customers. When multiple rules match, the lowest price (highest discount) wins.', 'mh-dev-kit' ); ?>
	</p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'mhdk_save_hybrid_pricing_lists' ); ?>
		<input type="hidden" name="action" value="mhdk_save_hybrid_pricing_lists" />
		<input type="hidden" name="mhdk_price_lists[0][id]" value="<?php echo esc_attr( (string) ( $list['id'] ?? 'wholesale-default' ) ); ?>" />
		<p>
			<label>
				<?php esc_html_e( 'List name', 'mh-dev-kit' ); ?>
				<input type="text" class="regular-text" name="mhdk_price_lists[0][name]" value="<?php echo esc_attr( (string) ( $list['name'] ?? '' ) ); ?>" />
			</label>
			<label style="margin-left:1rem;">
				<input type="checkbox" name="mhdk_price_lists[0][enabled]" value="1" <?php checked( ! empty( $list['enabled'] ) ); ?> />
				<?php esc_html_e( 'Enabled', 'mh-dev-kit' ); ?>
			</label>
		</p>

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Type', 'mh-dev-kit' ); ?></th>
					<th><?php esc_html_e( 'Target', 'mh-dev-kit' ); ?></th>
					<th><?php esc_html_e( 'Fixed price', 'mh-dev-kit' ); ?></th>
					<th><?php esc_html_e( 'Discount %', 'mh-dev-kit' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$row_count = max( 3, count( $rules ) );
				for ( $i = 0; $i < $row_count; $i++ ) :
					$rule = $rules[ $i ] ?? array();
					$type = (string) ( $rule['type'] ?? 'category' );
					?>
					<tr>
						<td>
							<select name="mhdk_price_lists[0][rules][<?php echo esc_attr( (string) $i ); ?>][type]">
								<option value="category" <?php selected( $type, 'category' ); ?>><?php esc_html_e( 'Category', 'mh-dev-kit' ); ?></option>
								<option value="product" <?php selected( $type, 'product' ); ?>><?php esc_html_e( 'Product', 'mh-dev-kit' ); ?></option>
							</select>
						</td>
						<td>
							<select name="mhdk_price_lists[0][rules][<?php echo esc_attr( (string) $i ); ?>][product_id]" style="max-width:220px;">
								<option value="0"><?php esc_html_e( '— Product —', 'mh-dev-kit' ); ?></option>
								<?php foreach ( $products as $product ) : ?>
									<option value="<?php echo esc_attr( (string) $product['id'] ); ?>" <?php selected( (int) ( $rule['product_id'] ?? 0 ), (int) $product['id'] ); ?>>
										<?php echo esc_html( $product['label'] ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<select name="mhdk_price_lists[0][rules][<?php echo esc_attr( (string) $i ); ?>][term_id]" style="max-width:220px;">
								<option value="0"><?php esc_html_e( '— Category —', 'mh-dev-kit' ); ?></option>
								<?php foreach ( $categories as $category ) : ?>
									<option value="<?php echo esc_attr( (string) $category['id'] ); ?>" <?php selected( (int) ( $rule['term_id'] ?? 0 ), (int) $category['id'] ); ?>>
										<?php echo esc_html( $category['label'] ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
						<td>
							<input type="text" name="mhdk_price_lists[0][rules][<?php echo esc_attr( (string) $i ); ?>][fixed_price]" value="<?php echo esc_attr( (string) ( $rule['fixed_price'] ?? '' ) ); ?>" class="small-text" />
						</td>
						<td>
							<input type="number" step="0.01" min="0" max="100" name="mhdk_price_lists[0][rules][<?php echo esc_attr( (string) $i ); ?>][discount_percent]" value="<?php echo esc_attr( (string) ( $rule['discount_percent'] ?? '' ) ); ?>" class="small-text" />
						</td>
					</tr>
				<?php endfor; ?>
			</tbody>
		</table>

		<?php submit_button( __( 'Save price list', 'mh-dev-kit' ) ); ?>
	</form>

	<h2><?php esc_html_e( 'B2B role', 'mh-dev-kit' ); ?></h2>
	<p>
		<?php
		printf(
			/* translators: %s: role slug */
			esc_html__( 'Assign users the %s role (or manage_woocommerce) to access wholesale prices and B2B-only products.', 'mh-dev-kit' ),
			'<code>' . esc_html( HybridPricingSettingsModel::B2B_ROLE ) . '</code>'
		);
		?>
	</p>
</div>
