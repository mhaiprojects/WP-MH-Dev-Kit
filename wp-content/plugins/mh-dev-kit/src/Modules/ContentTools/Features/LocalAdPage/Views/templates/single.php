<?php
/**
 * Local Ad Page public template.
 *
 * @var \WP_Post             $post     Ad post.
 * @var array<string,string> $location Location fields.
 * @var string               $headline Headline.
 * @var string               $body     Body HTML.
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
<body <?php body_class( 'mhdk-local-ad-body' ); ?>>
	<main class="mhdk-local-ad">
		<p class="mhdk-local-ad-location">
			<?php
			echo esc_html(
				sprintf(
					'%s, %s %s (%s)',
					ucfirst( $location['suburb'] ),
					strtoupper( $location['state'] ),
					$location['postcode'],
					strtoupper( $location['country'] )
				)
			);
			?>
		</p>
		<?php if ( '' !== $headline ) : ?>
			<h1 class="mhdk-local-ad-headline"><?php echo esc_html( $headline ); ?></h1>
		<?php else : ?>
			<h1 class="mhdk-local-ad-headline"><?php echo esc_html( get_the_title( $post ) ); ?></h1>
		<?php endif; ?>
		<?php if ( has_post_thumbnail( $post ) ) : ?>
			<div class="mhdk-local-ad-image"><?php echo get_the_post_thumbnail( $post, 'large' ); ?></div>
		<?php endif; ?>
		<?php if ( '' !== $body ) : ?>
			<div class="mhdk-local-ad-body-text"><?php echo wp_kses_post( wpautop( $body ) ); ?></div>
		<?php endif; ?>
		<div class="mhdk-local-ad-content"><?php echo wp_kses_post( apply_filters( 'the_content', $post->post_content ) ); ?></div>
	</main>
	<?php wp_footer(); ?>
</body>
</html>
