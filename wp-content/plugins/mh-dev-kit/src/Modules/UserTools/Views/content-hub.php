<?php
/**
 * User Tools content hub.
 *
 * @var string $version Plugin version.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MH\DevKit\Modules\UserTools\Features\FamilyTree\Models\FamilyTreeModel;
use MH\DevKit\Modules\UserTools\Features\QrCode\Models\QrCodeModel;
use MH\DevKit\Modules\UserTools\Features\Shortlinks\Models\ShortlinksModel;
use MH\DevKit\Modules\UserTools\Features\VCard\Models\VCardModel;

$items = array(
	array(
		'label' => __( 'QR Codes', 'mh-dev-kit' ),
		'url'   => admin_url( 'edit.php?post_type=' . QrCodeModel::POST_TYPE ),
		'desc'  => __( 'Create and manage your QR codes.', 'mh-dev-kit' ),
	),
	array(
		'label' => __( 'vCards', 'mh-dev-kit' ),
		'url'   => admin_url( 'edit.php?post_type=' . VCardModel::POST_TYPE ),
		'desc'  => __( 'Manage downloadable contact cards.', 'mh-dev-kit' ),
	),
	array(
		'label' => __( 'Link Pages', 'mh-dev-kit' ),
		'url'   => admin_url( 'edit.php?post_type=' . ShortlinksModel::POST_TYPE ),
		'desc'  => __( 'Linktree-style pages at /l/your-slug/', 'mh-dev-kit' ),
	),
	array(
		'label' => __( 'Family Trees', 'mh-dev-kit' ),
		'url'   => admin_url( 'edit.php?post_type=' . FamilyTreeModel::POST_TYPE ),
		'desc'  => __( 'Interactive family tree builder.', 'mh-dev-kit' ),
	),
);
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<hr class="wp-header-end" />
	<p><?php esc_html_e( 'Each tool stores data scoped to the post author. Users manage only their own entries.', 'mh-dev-kit' ); ?></p>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Tool', 'mh-dev-kit' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Description', 'mh-dev-kit' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $items as $item ) : ?>
				<tr>
					<td><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a></td>
					<td><?php echo esc_html( $item['desc'] ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="description"><?php echo esc_html( sprintf( 'MH Dev Kit %s', $version ) ); ?></p>
</div>
