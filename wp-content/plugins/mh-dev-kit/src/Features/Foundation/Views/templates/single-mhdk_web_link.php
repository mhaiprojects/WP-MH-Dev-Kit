<?php
/**
 * Single Web Link template.
 *
 * @package MH_Dev_Kit
 */

get_header();

while ( have_posts() ) {
	the_post();
	$url         = get_post_meta( get_the_ID(), '_mhdk_url', true );
	$description = get_post_meta( get_the_ID(), '_mhdk_description', true );
	$new_tab     = (bool) get_post_meta( get_the_ID(), '_mhdk_open_new_tab', true );
	?>
	<main id="primary" class="site-main mhdk-template mhdk-web-link-single">
		<article <?php post_class( 'entry mhdk-web-link-card' ); ?>>
			<header class="entry-header">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="mhdk-web-link__image"><?php the_post_thumbnail( 'medium' ); ?></div>
			<?php endif; ?>
			<?php if ( $description ) : ?>
				<div class="entry-content"><p><?php echo esc_html( $description ); ?></p></div>
			<?php endif; ?>
			<?php if ( $url ) : ?>
				<p class="mhdk-web-link__cta">
					<a class="button mhdk-button" href="<?php echo esc_url( $url ); ?>" <?php echo $new_tab ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
						<?php esc_html_e( 'Visit link', 'mh-dev-kit' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</article>
	</main>
	<?php
}

get_footer();
