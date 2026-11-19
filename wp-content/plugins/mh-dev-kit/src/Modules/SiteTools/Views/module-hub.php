<?php
/**
 * Site Tools module hub.
 *
 * @var string $version Plugin version.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Site Tools', 'mh-dev-kit' ); ?></h1>
	<hr class="wp-header-end" />
	<p><?php esc_html_e( 'Site-wide utilities registered by the Site Tools module.', 'mh-dev-kit' ); ?></p>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col" class="column-primary"><?php esc_html_e( 'Feature', 'mh-dev-kit' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Settings', 'mh-dev-kit' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="column-primary"><strong><?php esc_html_e( 'Backend Restrict Origin', 'mh-dev-kit' ); ?></strong></td>
				<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=mhdk-settings-restrict-origin' ) ); ?>"><?php esc_html_e( 'Configure', 'mh-dev-kit' ); ?></a></td>
			</tr>
			<tr>
				<td class="column-primary"><strong><?php esc_html_e( 'Map Distance', 'mh-dev-kit' ); ?></strong></td>
				<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=mhdk-settings-map-distance' ) ); ?>"><?php esc_html_e( 'Configure', 'mh-dev-kit' ); ?></a></td>
			</tr>
		</tbody>
	</table>

	<p class="description"><?php echo esc_html( sprintf( __( 'Version %s', 'mh-dev-kit' ), $version ) ); ?></p>
</div>
