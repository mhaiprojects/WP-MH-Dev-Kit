<?php
/**
 * MHDK Settings > Modules page.
 *
 * @var string $option_key Option name.
 * @var string $page_slug  Page slug.
 * @var array<string, array{enabled: bool}> $settings Module settings.
 * @var array<int, array<string, mixed>> $modules Module metadata.
 * @var string $version Plugin version.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap mhdk-admin">
	<h1><?php esc_html_e( 'Modules', 'mh-dev-kit' ); ?></h1>
	<p><?php esc_html_e( 'Enable or disable MH Dev Kit modules. Disabled modules do not register features, admin pages, or blocks.', 'mh-dev-kit' ); ?></p>

	<form method="post" action="options.php">
		<?php settings_fields( 'mhdk_modules' ); ?>

		<table class="widefat striped">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Module', 'mh-dev-kit' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Description', 'mh-dev-kit' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Status', 'mh-dev-kit' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Enabled', 'mh-dev-kit' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $modules as $module ) : ?>
					<?php
					$id      = (string) $module['id'];
					$enabled = ! empty( $settings[ $id ]['enabled'] );
					$met     = ! empty( $module['requirements_met'] );
					?>
					<tr>
						<td><strong><?php echo esc_html( (string) $module['name'] ); ?></strong></td>
						<td><?php echo esc_html( (string) $module['description'] ); ?></td>
						<td>
							<?php if ( ! $met ) : ?>
								<span class="mhdk-module-status mhdk-module-status--unmet"><?php esc_html_e( 'Requirements not met', 'mh-dev-kit' ); ?></span>
								<?php if ( ! empty( $module['requirements_messages'] ) ) : ?>
									<p class="description"><?php echo esc_html( implode( ' ', (array) $module['requirements_messages'] ) ); ?></p>
								<?php endif; ?>
							<?php elseif ( $enabled ) : ?>
								<span class="mhdk-module-status mhdk-module-status--active"><?php esc_html_e( 'Active', 'mh-dev-kit' ); ?></span>
							<?php else : ?>
								<span class="mhdk-module-status mhdk-module-status--disabled"><?php esc_html_e( 'Disabled', 'mh-dev-kit' ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<label>
								<input
									type="checkbox"
									name="<?php echo esc_attr( $option_key . '[' . $id . '][enabled]' ); ?>"
									value="1"
									<?php checked( $enabled ); ?>
								/>
								<?php esc_html_e( 'Enabled', 'mh-dev-kit' ); ?>
							</label>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php submit_button(); ?>
	</form>

	<p class="description"><?php echo esc_html( sprintf( __( 'Version %s', 'mh-dev-kit' ), $version ) ); ?></p>
</div>
