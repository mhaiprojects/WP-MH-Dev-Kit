<?php
/**
 * Archive Web Link template.
 *
 * @package MH_Dev_Kit
 */

get_header();
?>
<main id="primary" class="site-main mhdk-template mhdk-web-link-archive">
	<header class="page-header">
		<h1 class="page-title"><?php post_type_archive_title(); ?></h1>
	</header>
	<div class="mhdk-card-grid mhdk-web-links--grid">
		<?php if ( have_posts() ) : ?>
			<ul class="mhdk-web-links__list">
				<?php
				while ( have_posts() ) {
					the_post();
					$url         = get_post_meta( get_the_ID(), '_mhdk_url', true );
					$description = get_post_meta( get_the_ID(), '_mhdk_description', true );
					?>
					<li class="mhdk-web-links__item mhdk-card">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'thumbnail', array( 'class' => 'mhdk-web-links__thumb' ) ); ?>
						<?php endif; ?>
						<div class="mhdk-web-links__body">
							<h2 class="entry-title">
								<a class="mhdk-web-links__link" href="<?php echo esc_url( $url ?: get_permalink() ); ?>">
									<?php the_title(); ?>
								</a>
							</h2>
							<?php if ( $description ) : ?>
								<p class="mhdk-web-links__desc"><?php echo esc_html( $description ); ?></p>
							<?php endif; ?>
						</div>
					</li>
					<?php
				}
				?>
			</ul>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'No web links found.', 'mh-dev-kit' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
