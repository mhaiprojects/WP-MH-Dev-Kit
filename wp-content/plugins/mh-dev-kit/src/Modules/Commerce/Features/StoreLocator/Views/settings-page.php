<?php
/**
 * Store Locator settings + store list overview.
 *
 * @var \MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorSettingsModel $settings
 * @var array<int, array<string, mixed>>                                                   $stores
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<?php settings_errors(); ?>
	<form method="post" action="options.php">
		<?php
		settings_fields( 'mhdk_store_locator' );
		do_settings_sections( \MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorSettingsModel::SETTINGS_SLUG );
		submit_button();
		?>
	</form>

	<h2><?php esc_html_e( 'Stores', 'mh-dev-kit' ); ?></h2>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Store', 'mh-dev-kit' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Location', 'mh-dev-kit' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Coordinates', 'mh-dev-kit' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Status', 'mh-dev-kit' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $stores as $store ) : ?>
				<tr>
					<td><strong><?php echo esc_html( (string) $store['name'] ); ?></strong></td>
					<td>
						<?php
						echo esc_html(
							trim(
								sprintf(
									'%s, %s %s %s',
									(string) $store['address'],
									(string) $store['city'],
									(string) $store['state'],
									(string) $store['postcode']
								)
							)
						);
						?>
					</td>
					<td><?php echo esc_html( (string) $store['lat'] . ', ' . (string) $store['lng'] ); ?></td>
					<td>
						<?php
						echo ! empty( $store['active'] )
							? esc_html__( 'Active', 'mh-dev-kit' )
							: esc_html__( 'Inactive', 'mh-dev-kit' );
						?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<p class="description">
		<?php
		printf(
			/* translators: %s: shortcode tag */
			esc_html__( 'Frontend shortcode: %s — demo page: /store-locator/', 'mh-dev-kit' ),
			'[mhdk_store_locator]'
		);
		?>
	</p>
</div>
