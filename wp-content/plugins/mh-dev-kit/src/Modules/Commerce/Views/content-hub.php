<?php
/**
 * Commerce module content hub.
 *
 * @var string $version Plugin version.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$content_types = array(
	array(
		'name'      => __( 'Saved Menus', 'mh-dev-kit' ),
		'post_type' => 'mhdk_saved_menu',
	),
);
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Commerce Content', 'mh-dev-kit' ); ?></h1>
	<hr class="wp-header-end" />
	<p><?php esc_html_e( 'Custom content types registered by the Commerce module.', 'mh-dev-kit' ); ?></p>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col" class="column-primary"><?php esc_html_e( 'Name', 'mh-dev-kit' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Post type', 'mh-dev-kit' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $content_types as $type ) : ?>
				<?php
				$post_type = (string) $type['post_type'];
				$list_url  = admin_url( 'edit.php?post_type=' . $post_type );
				?>
				<tr>
					<td class="column-primary">
						<strong>
							<a class="row-title" href="<?php echo esc_url( $list_url ); ?>">
								<?php echo esc_html( (string) $type['name'] ); ?>
							</a>
						</strong>
						<div class="row-actions">
							<span class="view">
								<a href="<?php echo esc_url( $list_url ); ?>"><?php esc_html_e( 'View all', 'mh-dev-kit' ); ?></a>
							</span>
						</div>
					</td>
					<td><code><?php echo esc_html( $post_type ); ?></code></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<p class="description"><?php echo esc_html( sprintf( __( 'Version %s', 'mh-dev-kit' ), $version ) ); ?></p>
</div>
