<?php
/**
 * Single Hospitality Menu template.
 *
 * @package MH_Dev_Kit
 */

get_header();

while ( have_posts() ) {
	the_post();
	$subtitle  = get_post_meta( get_the_ID(), '_mhdk_subtitle', true );
	$menu_note = get_post_meta( get_the_ID(), '_mhdk_menu_note', true );
	?>
	<main id="primary" class="site-main mhdk-template mhdk-hospitality-menu-single">
		<article <?php post_class( 'entry' ); ?>>
			<header class="entry-header">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				<?php if ( $subtitle ) : ?>
					<p class="mhdk-hospitality-menu__subtitle"><?php echo esc_html( $subtitle ); ?></p>
				<?php endif; ?>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="mhdk-hospitality-menu__image"><?php the_post_thumbnail( 'large' ); ?></div>
			<?php endif; ?>
			<div class="entry-content">
				<?php the_content(); ?>
			</div>
			<?php if ( $menu_note ) : ?>
				<footer class="entry-footer"><p class="mhdk-hospitality-menu__note"><em><?php echo esc_html( $menu_note ); ?></em></p></footer>
			<?php endif; ?>
		</article>
	</main>
	<?php
}

get_footer();
