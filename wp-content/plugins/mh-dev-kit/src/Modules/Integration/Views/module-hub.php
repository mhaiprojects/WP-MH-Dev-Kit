<?php
/**
 * Integration module hub.
 *
 * @var string $version Plugin version.
 * @var int    $connection_count
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Integration', 'mh-dev-kit' ); ?></h1>
	<hr class="wp-header-end" />
	<p><?php esc_html_e( 'Connect third-party APIs and pull data into WordPress on a schedule or manual trigger.', 'mh-dev-kit' ); ?></p>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col" class="column-primary"><?php esc_html_e( 'Feature', 'mh-dev-kit' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Description', 'mh-dev-kit' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Manage', 'mh-dev-kit' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="column-primary"><strong><?php esc_html_e( 'API Middleware', 'mh-dev-kit' ); ?></strong></td>
				<td>
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: number of connections */
							_n(
								'POC API pull with auth, scheduling, and %d connection configured.',
								'POC API pull with auth, scheduling, and %d connections configured.',
								$connection_count,
								'mh-dev-kit'
							),
							$connection_count
						)
					);
					?>
				</td>
				<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=mhdk-settings-api-middleware' ) ); ?>"><?php esc_html_e( 'Configure', 'mh-dev-kit' ); ?></a></td>
			</tr>
		</tbody>
	</table>

	<p class="description"><?php echo esc_html( sprintf( __( 'Version %s', 'mh-dev-kit' ), $version ) ); ?></p>
</div>
