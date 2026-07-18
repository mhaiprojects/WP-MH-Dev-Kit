<?php
/**
 * Map distance shortcode markup.
 *
 * @var string $start_label Start point label.
 * @var float  $start_lat   Start latitude.
 * @var float  $start_lng   Start longitude.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div
	class="mhdk-map-distance"
	data-start-lat="<?php echo esc_attr( (string) $start_lat ); ?>"
	data-start-lng="<?php echo esc_attr( (string) $start_lng ); ?>"
>
	<p class="mhdk-map-distance-start">
		<strong><?php esc_html_e( 'Starting point:', 'mh-dev-kit' ); ?></strong>
		<?php echo esc_html( $start_label ); ?>
		<span class="mhdk-map-distance-coords">
			(<?php echo esc_html( (string) $start_lat ); ?>, <?php echo esc_html( (string) $start_lng ); ?>)
		</span>
	</p>
	<button type="button" class="button mhdk-map-distance-btn">
		<?php esc_html_e( 'Calculate distance to my location', 'mh-dev-kit' ); ?>
	</button>
	<p class="mhdk-map-distance-result" aria-live="polite"></p>
</div>
