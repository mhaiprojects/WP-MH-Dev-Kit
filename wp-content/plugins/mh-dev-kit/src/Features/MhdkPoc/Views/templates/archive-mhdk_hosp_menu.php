<?php
/**
 * Archive Hospitality Menu template.
 *
 * @package MH_Dev_Kit
 */

get_header();
?>
<main id="primary" class="site-main mhdk-template mhdk-hospitality-menu-archive">
	<header class="page-header">
		<h1 class="page-title"><?php post_type_archive_title(); ?></h1>
	</header>
	<div class="mhdk-card-grid">
		<?php if ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) {
				the_post();
				?>
				<article <?php post_class( 'mhdk-card entry' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'medium' ); ?></a>
					<?php endif; ?>
					<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<div class="entry-summary"><?php the_excerpt(); ?></div>
				</article>
				<?php
			}
			the_posts_pagination();
			?>
		<?php else : ?>
			<p><?php esc_html_e( 'No hospitality menus found.', 'mh-dev-kit' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
