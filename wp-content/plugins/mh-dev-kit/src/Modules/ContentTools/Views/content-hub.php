<?php
/**
 * Content Tools hub.
 *
 * @var string $version Plugin version.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Models\LocalAdPageModel;
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<hr class="wp-header-end" />
	<p><?php esc_html_e( 'Content authoring blocks, shortcodes, and structured landing pages.', 'mh-dev-kit' ); ?></p>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Feature', 'mh-dev-kit' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Description', 'mh-dev-kit' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php esc_html_e( 'Children Pages', 'mh-dev-kit' ); ?></td>
				<td><?php esc_html_e( 'Gutenberg block and [mhdk_children_pages] shortcode for child page lists.', 'mh-dev-kit' ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Markdown Editor', 'mh-dev-kit' ); ?></td>
				<td><?php esc_html_e( 'Dynamic Gutenberg block for Markdown content (marked.js).', 'mh-dev-kit' ); ?></td>
			</tr>
			<tr>
				<td>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . LocalAdPageModel::POST_TYPE ) ); ?>">
						<?php esc_html_e( 'Local Ad Pages', 'mh-dev-kit' ); ?>
					</a>
				</td>
				<td><?php esc_html_e( 'Auto-generated URLs from country/state/suburb/postcode. REST API at /wp-json/mhdk/v1/local-ads', 'mh-dev-kit' ); ?></td>
			</tr>
		</tbody>
	</table>
	<p class="description"><?php echo esc_html( sprintf( 'MH Dev Kit %s', $version ) ); ?></p>
</div>
