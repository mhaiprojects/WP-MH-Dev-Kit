<?php
/**
 * Store Locator settings + editable store catalog.
 *
 * @var \MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorSettingsModel $settings
 * @var \MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorModel         $stores_model
 * @var array<int, array<string, mixed>>                                                   $stores
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorSettingsModel;

$demo_url    = home_url( '/store-locator/' );
$rest_url    = rest_url( 'mhdk/v1/stores/nearby' );
$inventory_url = admin_url( 'admin.php?page=mhdk-settings-inventory' );
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<hr class="wp-header-end" />

	<?php settings_errors(); ?>

	<?php if ( isset( $_GET['stores_saved'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Stores saved.', 'mh-dev-kit' ); ?></p></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['stores_reset'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Stores reset to demo defaults.', 'mh-dev-kit' ); ?></p></div>
	<?php endif; ?>

	<h2><?php esc_html_e( 'Settings', 'mh-dev-kit' ); ?></h2>
	<form method="post" action="options.php">
		<?php
		settings_fields( 'mhdk_store_locator' );
		do_settings_sections( StoreLocatorSettingsModel::SETTINGS_SLUG );
		submit_button();
		?>
	</form>

	<h2><?php esc_html_e( 'Store catalog', 'mh-dev-kit' ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'Edit store details used by the locator shortcode and checkout pickup field.', 'mh-dev-kit' ); ?>
	</p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'mhdk_save_stores' ); ?>
		<input type="hidden" name="action" value="mhdk_save_stores" />
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Name', 'mh-dev-kit' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Address', 'mh-dev-kit' ); ?></th>
					<th scope="col"><?php esc_html_e( 'City / State / Postcode', 'mh-dev-kit' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Coordinates', 'mh-dev-kit' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Phone', 'mh-dev-kit' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Active', 'mh-dev-kit' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $stores as $index => $store ) : ?>
					<tr>
						<td>
							<input type="hidden" name="stores[<?php echo esc_attr( (string) $index ); ?>][id]" value="<?php echo esc_attr( (string) $store['id'] ); ?>" />
							<input type="text" class="regular-text" name="stores[<?php echo esc_attr( (string) $index ); ?>][name]" value="<?php echo esc_attr( (string) $store['name'] ); ?>" required />
						</td>
						<td>
							<input type="text" class="regular-text" name="stores[<?php echo esc_attr( (string) $index ); ?>][address]" value="<?php echo esc_attr( (string) $store['address'] ); ?>" />
						</td>
						<td>
							<input type="text" class="small-text" name="stores[<?php echo esc_attr( (string) $index ); ?>][city]" value="<?php echo esc_attr( (string) $store['city'] ); ?>" placeholder="<?php esc_attr_e( 'City', 'mh-dev-kit' ); ?>" />
							<input type="text" class="small-text" name="stores[<?php echo esc_attr( (string) $index ); ?>][state]" value="<?php echo esc_attr( (string) $store['state'] ); ?>" placeholder="<?php esc_attr_e( 'State', 'mh-dev-kit' ); ?>" />
							<input type="text" class="small-text" name="stores[<?php echo esc_attr( (string) $index ); ?>][postcode]" value="<?php echo esc_attr( (string) $store['postcode'] ); ?>" placeholder="<?php esc_attr_e( 'Postcode', 'mh-dev-kit' ); ?>" />
						</td>
						<td>
							<code>
								<input type="text" class="small-text" name="stores[<?php echo esc_attr( (string) $index ); ?>][lat]" value="<?php echo esc_attr( (string) $store['lat'] ); ?>" />
								,
								<input type="text" class="small-text" name="stores[<?php echo esc_attr( (string) $index ); ?>][lng]" value="<?php echo esc_attr( (string) $store['lng'] ); ?>" />
							</code>
							<?php if ( ! empty( $store['lat'] ) && ! empty( $store['lng'] ) ) : ?>
								<br />
								<a href="<?php echo esc_url( $stores_model->maps_url( $store ) ); ?>" target="_blank" rel="noopener noreferrer">
									<?php esc_html_e( 'Open in maps', 'mh-dev-kit' ); ?>
								</a>
							<?php endif; ?>
						</td>
						<td>
							<input type="text" class="regular-text" name="stores[<?php echo esc_attr( (string) $index ); ?>][phone]" value="<?php echo esc_attr( (string) $store['phone'] ); ?>" />
							<input type="hidden" name="stores[<?php echo esc_attr( (string) $index ); ?>][hours]" value="<?php echo esc_attr( (string) $store['hours'] ); ?>" />
						</td>
						<td>
							<label>
								<input type="checkbox" name="stores[<?php echo esc_attr( (string) $index ); ?>][active]" value="1" <?php checked( ! empty( $store['active'] ) ); ?> />
								<?php esc_html_e( 'Active', 'mh-dev-kit' ); ?>
							</label>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php submit_button( __( 'Save stores', 'mh-dev-kit' ) ); ?>
	</form>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top: 8px;">
		<?php wp_nonce_field( 'mhdk_reset_stores' ); ?>
		<input type="hidden" name="action" value="mhdk_reset_stores" />
		<?php
		submit_button(
			__( 'Reset to demo stores', 'mh-dev-kit' ),
			'secondary',
			'submit',
			false,
			array(
				'onclick' => "return confirm('" . esc_js( __( 'Replace all stores with demo defaults?', 'mh-dev-kit' ) ) . "');",
			)
		);
		?>
	</form>

	<hr />
	<p class="description">
		<?php
		printf(
			/* translators: 1: shortcode */
			esc_html__( 'Frontend shortcode: %1$s', 'mh-dev-kit' ),
			'[mhdk_store_locator]'
		);
		?>
		—
		<a href="<?php echo esc_url( $demo_url ); ?>"><?php esc_html_e( 'Demo page', 'mh-dev-kit' ); ?></a>
		—
		<a href="<?php echo esc_url( $rest_url ); ?>"><?php esc_html_e( 'REST nearby endpoint', 'mh-dev-kit' ); ?></a>
		—
		<a href="<?php echo esc_url( $inventory_url ); ?>"><?php esc_html_e( 'Manage stock per store', 'mh-dev-kit' ); ?></a>
	</p>
</div>
