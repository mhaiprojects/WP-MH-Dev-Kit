<?php
/**
 * Public link page template.
 *
 * @var \WP_Post $post  Link page post.
 * @var string   $bio   Bio text.
 * @var array    $links Link items.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html( get_the_title( $post ) ); ?> — <?php bloginfo( 'name' ); ?></title>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'mhdk-link-page-body' ); ?>>
	<main class="mhdk-link-page">
		<h1 class="mhdk-link-page-title"><?php echo esc_html( get_the_title( $post ) ); ?></h1>
		<?php if ( '' !== $bio ) : ?>
			<p class="mhdk-link-page-bio"><?php echo esc_html( $bio ); ?></p>
		<?php endif; ?>
		<nav class="mhdk-link-page-links" aria-label="<?php esc_attr_e( 'Links', 'mh-dev-kit' ); ?>">
			<?php foreach ( $links as $link ) : ?>
				<a class="mhdk-link-page-btn" href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo esc_html( $link['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
	</main>
	<?php wp_footer(); ?>
</body>
</html>
