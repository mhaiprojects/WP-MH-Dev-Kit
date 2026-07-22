<?php
/**
 * Store Locator shortcode markup.
 *
 * @var array<int, array<string, mixed>> $stores
 * @var bool $show_phone
 * @var bool $show_hours
 * @var int  $max_distance_km
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div
	class="mhdk-store-locator"
	data-max-distance-km="<?php echo esc_attr( (string) $max_distance_km ); ?>"
>
	<div class="mhdk-store-locator-toolbar">
		<button type="button" class="button mhdk-store-locator-locate">
			<?php esc_html_e( 'Use my location', 'mh-dev-kit' ); ?>
		</button>
		<p class="mhdk-store-locator-status" aria-live="polite"></p>
	</div>

	<ul class="mhdk-store-locator-list">
		<?php foreach ( $stores as $store ) : ?>
			<li
				class="mhdk-store-locator-item"
				data-store-id="<?php echo esc_attr( (string) $store['id'] ); ?>"
				data-lat="<?php echo esc_attr( (string) $store['lat'] ); ?>"
				data-lng="<?php echo esc_attr( (string) $store['lng'] ); ?>"
			>
				<div class="mhdk-store-locator-item-head">
					<strong><?php echo esc_html( (string) $store['name'] ); ?></strong>
					<span class="mhdk-store-locator-distance"></span>
				</div>
				<p class="mhdk-store-locator-address">
					<?php
					echo esc_html(
						trim(
							sprintf(
								'%s, %s %s %s',
								(string) $store['address'],
								(string) $store['city'],
								(string) $store['state'],
								(string) $store['postcode']
							)
						)
					);
					?>
				</p>
				<?php if ( $show_phone && ! empty( $store['phone'] ) ) : ?>
					<p class="mhdk-store-locator-phone"><?php echo esc_html( (string) $store['phone'] ); ?></p>
				<?php endif; ?>
				<?php if ( $show_hours && ! empty( $store['hours'] ) ) : ?>
					<p class="mhdk-store-locator-hours"><?php echo esc_html( (string) $store['hours'] ); ?></p>
				<?php endif; ?>
				<button type="button" class="button button-secondary mhdk-store-locator-select">
					<?php esc_html_e( 'Select store', 'mh-dev-kit' ); ?>
				</button>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
