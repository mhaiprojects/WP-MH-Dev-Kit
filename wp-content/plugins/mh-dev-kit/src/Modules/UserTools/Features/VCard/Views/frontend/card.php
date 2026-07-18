<?php
/**
 * vCard frontend card display.
 *
 * @var string               $title        Card title.
 * @var array<string,string> $fields       Contact fields.
 * @var string               $download_url Download link.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$name = trim( ( $fields['first_name'] ?? '' ) . ' ' . ( $fields['last_name'] ?? '' ) );
?>
<div class="mhdk-vcard">
	<h3 class="mhdk-vcard-name"><?php echo esc_html( '' !== $name ? $name : $title ); ?></h3>
	<?php if ( ! empty( $fields['title'] ) || ! empty( $fields['org'] ) ) : ?>
		<p class="mhdk-vcard-subtitle">
			<?php echo esc_html( trim( ( $fields['title'] ?? '' ) . ( ! empty( $fields['org'] ) ? ' · ' . $fields['org'] : '' ) ) ); ?>
		</p>
	<?php endif; ?>
	<ul class="mhdk-vcard-details">
		<?php if ( ! empty( $fields['phone'] ) ) : ?>
			<li><?php esc_html_e( 'Phone:', 'mh-dev-kit' ); ?> <?php echo esc_html( $fields['phone'] ); ?></li>
		<?php endif; ?>
		<?php if ( ! empty( $fields['email'] ) ) : ?>
			<li><?php esc_html_e( 'Email:', 'mh-dev-kit' ); ?> <a href="mailto:<?php echo esc_attr( $fields['email'] ); ?>"><?php echo esc_html( $fields['email'] ); ?></a></li>
		<?php endif; ?>
		<?php if ( ! empty( $fields['url'] ) ) : ?>
			<li><a href="<?php echo esc_url( $fields['url'] ); ?>"><?php echo esc_html( $fields['url'] ); ?></a></li>
		<?php endif; ?>
		<?php if ( ! empty( $fields['address'] ) ) : ?>
			<li><?php echo esc_html( $fields['address'] ); ?></li>
		<?php endif; ?>
	</ul>
	<p><a class="button mhdk-vcard-download" href="<?php echo esc_url( $download_url ); ?>"><?php esc_html_e( 'Download vCard (.vcf)', 'mh-dev-kit' ); ?></a></p>
</div>
