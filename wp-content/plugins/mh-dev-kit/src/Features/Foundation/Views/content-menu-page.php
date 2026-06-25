<?php
/**
 * MH Dev Kit Content menu landing page.
 *
 * @var string $menu_slug Admin menu slug.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap mhdk-admin">
	<h1><?php esc_html_e( 'MH Dev Kit Content', 'mh-dev-kit' ); ?></h1>
	<p><?php esc_html_e( 'Custom content types for MH Dev Kit live here. Use the submenus to manage Hospitality Menus and Web Links.', 'mh-dev-kit' ); ?></p>
	<ul>
		<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=mhdk_hosp_menu' ) ); ?>"><?php esc_html_e( 'Hospitality Menus', 'mh-dev-kit' ); ?></a></li>
		<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=mhdk_web_link' ) ); ?>"><?php esc_html_e( 'Web Links', 'mh-dev-kit' ); ?></a></li>
	</ul>
</div>
