<?php
/**
 * API Middleware admin page.
 *
 * @var array<int, array<string, mixed>> $connections
 * @var array<int, array<string, mixed>> $log
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<?php if ( isset( $_GET['updated'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Connection saved.', 'mh-dev-kit' ); ?></p></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['ran'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Connection run triggered.', 'mh-dev-kit' ); ?></p></div>
	<?php endif; ?>

	<h2><?php esc_html_e( 'Connections', 'mh-dev-kit' ); ?></h2>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Name', 'mh-dev-kit' ); ?></th>
				<th><?php esc_html_e( 'Endpoint', 'mh-dev-kit' ); ?></th>
				<th><?php esc_html_e( 'Auth', 'mh-dev-kit' ); ?></th>
				<th><?php esc_html_e( 'Schedule', 'mh-dev-kit' ); ?></th>
				<th><?php esc_html_e( 'Last run', 'mh-dev-kit' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'mh-dev-kit' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $connections ) ) : ?>
				<tr><td colspan="6"><?php esc_html_e( 'No connections yet.', 'mh-dev-kit' ); ?></td></tr>
			<?php endif; ?>
			<?php foreach ( $connections as $connection ) : ?>
				<tr>
					<td><strong><?php echo esc_html( (string) $connection['name'] ); ?></strong></td>
					<td><code><?php echo esc_html( (string) $connection['method'] . ' ' . (string) $connection['endpoint'] ); ?></code></td>
					<td><?php echo esc_html( (string) $connection['auth_type'] ); ?></td>
					<td><?php echo esc_html( (string) $connection['schedule'] ); ?></td>
					<td>
						<?php
						echo (int) $connection['last_run'] > 0
							? esc_html( wp_date( 'Y-m-d H:i', (int) $connection['last_run'] ) . ' (' . (string) $connection['last_status'] . ')' )
							: esc_html__( 'Never', 'mh-dev-kit' );
						?>
					</td>
					<td>
						<a class="button button-small" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=mhdk_run_api_connection&id=' . rawurlencode( (string) $connection['id'] ) ), 'mhdk_run_api_connection' ) ); ?>">
							<?php esc_html_e( 'Run now', 'mh-dev-kit' ); ?>
						</a>
						<a class="button button-small" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=mhdk_delete_api_connection&id=' . rawurlencode( (string) $connection['id'] ) ), 'mhdk_delete_api_connection' ) ); ?>">
							<?php esc_html_e( 'Delete', 'mh-dev-kit' ); ?>
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<h2><?php esc_html_e( 'Add or update connection', 'mh-dev-kit' ); ?></h2>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="mhdk-api-connection-form">
		<?php wp_nonce_field( 'mhdk_save_api_connection' ); ?>
		<input type="hidden" name="action" value="mhdk_save_api_connection" />
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><label for="mhdk-api-id"><?php esc_html_e( 'Connection ID', 'mh-dev-kit' ); ?></label></th>
					<td><input name="id" id="mhdk-api-id" type="text" class="regular-text" required placeholder="my-api" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="mhdk-api-name"><?php esc_html_e( 'Name', 'mh-dev-kit' ); ?></label></th>
					<td><input name="name" id="mhdk-api-name" type="text" class="regular-text" required /></td>
				</tr>
				<tr>
					<th scope="row"><label for="mhdk-api-base"><?php esc_html_e( 'Base URL', 'mh-dev-kit' ); ?></label></th>
					<td><input name="base_url" id="mhdk-api-base" type="url" class="regular-text" required /></td>
				</tr>
				<tr>
					<th scope="row"><label for="mhdk-api-endpoint"><?php esc_html_e( 'Endpoint path', 'mh-dev-kit' ); ?></label></th>
					<td><input name="endpoint" id="mhdk-api-endpoint" type="text" class="regular-text" value="/" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="mhdk-api-method"><?php esc_html_e( 'Method', 'mh-dev-kit' ); ?></label></th>
					<td>
						<select name="method" id="mhdk-api-method">
							<option value="GET">GET</option>
							<option value="POST">POST</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="mhdk-api-auth-type"><?php esc_html_e( 'Auth type', 'mh-dev-kit' ); ?></label></th>
					<td>
						<select name="auth_type" id="mhdk-api-auth-type">
							<option value="none"><?php esc_html_e( 'None', 'mh-dev-kit' ); ?></option>
							<option value="bearer"><?php esc_html_e( 'Bearer token', 'mh-dev-kit' ); ?></option>
							<option value="basic"><?php esc_html_e( 'Basic', 'mh-dev-kit' ); ?></option>
							<option value="api_key"><?php esc_html_e( 'API key header', 'mh-dev-kit' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="mhdk-api-auth-header"><?php esc_html_e( 'Auth header', 'mh-dev-kit' ); ?></label></th>
					<td><input name="auth_header" id="mhdk-api-auth-header" type="text" class="regular-text" value="Authorization" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="mhdk-api-auth-value"><?php esc_html_e( 'Auth value', 'mh-dev-kit' ); ?></label></th>
					<td><input name="auth_value" id="mhdk-api-auth-value" type="password" class="regular-text" autocomplete="off" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="mhdk-api-schedule"><?php esc_html_e( 'Schedule', 'mh-dev-kit' ); ?></label></th>
					<td>
						<select name="schedule" id="mhdk-api-schedule">
							<option value="manual"><?php esc_html_e( 'Manual only', 'mh-dev-kit' ); ?></option>
							<option value="hourly"><?php esc_html_e( 'Hourly', 'mh-dev-kit' ); ?></option>
							<option value="daily"><?php esc_html_e( 'Daily', 'mh-dev-kit' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="mhdk-api-target-type"><?php esc_html_e( 'Store response in', 'mh-dev-kit' ); ?></label></th>
					<td>
						<select name="target_type" id="mhdk-api-target-type">
							<option value="option"><?php esc_html_e( 'WordPress option', 'mh-dev-kit' ); ?></option>
							<option value="transient"><?php esc_html_e( 'Transient', 'mh-dev-kit' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="mhdk-api-target-key"><?php esc_html_e( 'Target key', 'mh-dev-kit' ); ?></label></th>
					<td><input name="target_key" id="mhdk-api-target-key" type="text" class="regular-text" placeholder="mhdk_api_data_my_api" /></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Enabled', 'mh-dev-kit' ); ?></th>
					<td><label><input type="checkbox" name="enabled" value="1" checked /> <?php esc_html_e( 'Active', 'mh-dev-kit' ); ?></label></td>
				</tr>
			</tbody>
		</table>
		<?php submit_button( __( 'Save connection', 'mh-dev-kit' ) ); ?>
	</form>

	<h2><?php esc_html_e( 'Recent run log', 'mh-dev-kit' ); ?></h2>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Connection', 'mh-dev-kit' ); ?></th>
				<th><?php esc_html_e( 'Time', 'mh-dev-kit' ); ?></th>
				<th><?php esc_html_e( 'Status', 'mh-dev-kit' ); ?></th>
				<th><?php esc_html_e( 'Message', 'mh-dev-kit' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $log ) ) : ?>
				<tr><td colspan="4"><?php esc_html_e( 'No runs logged yet.', 'mh-dev-kit' ); ?></td></tr>
			<?php endif; ?>
			<?php foreach ( $log as $entry ) : ?>
				<tr>
					<td><?php echo esc_html( (string) $entry['connection_id'] ); ?></td>
					<td><?php echo esc_html( wp_date( 'Y-m-d H:i:s', (int) $entry['timestamp'] ) ); ?></td>
					<td><?php echo ! empty( $entry['success'] ) ? esc_html__( 'Success', 'mh-dev-kit' ) : esc_html__( 'Error', 'mh-dev-kit' ); ?></td>
					<td><?php echo esc_html( (string) $entry['message'] ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
