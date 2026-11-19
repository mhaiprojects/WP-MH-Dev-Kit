<?php
/**
 * Store Locator shortcode markup.
 *
 * @var array<int, array<string, mixed>> $stores
 * @var bool                             $show_phone
 * @var bool                             $show_hours
 * @var int                              $max_distance_km
 * @var int                              $product_id
 * @var \MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorModel $stores_model
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div
	class="mhdk-store-locator"
	data-max-distance-km="<?php echo esc_attr( (string) $max_distance_km ); ?>"
	data-product-id="<?php echo esc_attr( (string) $product_id ); ?>"
>
	<div class="mhdk-store-locator-toolbar">
		<button type="button" class="mhdk-btn mhdk-store-locator-locate">
			<?php esc_html_e( 'Use my location', 'mh-dev-kit' ); ?>
		</button>
		<p class="mhdk-store-locator-status" aria-live="polite"></p>
	</div>

	<div class="mhdk-store-locator-fallback">
		<p class="description"><?php esc_html_e( 'Without location access, filter stores by state or postcode:', 'mh-dev-kit' ); ?></p>
		<label for="mhdk-store-locator-filter-state"><?php esc_html_e( 'State', 'mh-dev-kit' ); ?></label>
		<input type="text" id="mhdk-store-locator-filter-state" class="mhdk-store-locator-filter-state" placeholder="<?php esc_attr_e( 'e.g. NSW', 'mh-dev-kit' ); ?>" />
		<label for="mhdk-store-locator-filter-postcode"><?php esc_html_e( 'Postcode', 'mh-dev-kit' ); ?></label>
		<input type="text" id="mhdk-store-locator-filter-postcode" class="mhdk-store-locator-filter-postcode" placeholder="<?php esc_attr_e( 'e.g. 2000', 'mh-dev-kit' ); ?>" />
		<button type="button" class="mhdk-btn is-secondary mhdk-store-locator-filter-apply">
			<?php esc_html_e( 'Apply filters', 'mh-dev-kit' ); ?>
		</button>
	</div>

	<ul class="mhdk-store-locator-list">
		<?php foreach ( $stores as $store ) : ?>
			<li
				class="mhdk-store-locator-item"
				data-store-id="<?php echo esc_attr( (string) $store['id'] ); ?>"
				data-lat="<?php echo esc_attr( (string) $store['lat'] ); ?>"
				data-lng="<?php echo esc_attr( (string) $store['lng'] ); ?>"
				data-state="<?php echo esc_attr( (string) $store['state'] ); ?>"
				data-postcode="<?php echo esc_attr( (string) $store['postcode'] ); ?>"
			>
				<div class="mhdk-store-locator-item-head">
					<strong><?php echo esc_html( (string) $store['name'] ); ?></strong>
					<span class="mhdk-store-locator-distance"></span>
				</div>
				<p class="mhdk-store-locator-address">
					<?php echo esc_html( $stores_model->format_address( $store ) ); ?>
				</p>
				<?php if ( $product_id > 0 && isset( $store['stock_quantity'] ) ) : ?>
					<span class="mhdk-store-locator-stock">
						<?php
						$qty = (int) $store['stock_quantity'];
						echo esc_html(
							$qty > 0
								? sprintf(
									/* translators: %d: stock quantity */
									_n( '%d in stock', '%d in stock', $qty, 'mh-dev-kit' ),
									$qty
								)
								: __( 'Out of stock', 'mh-dev-kit' )
						);
						?>
					</span>
				<?php endif; ?>
				<?php if ( $show_phone && ! empty( $store['phone'] ) ) : ?>
					<p class="mhdk-store-locator-phone">
						<a href="<?php echo esc_url( 'tel:' . preg_replace( '/\s+/', '', (string) $store['phone'] ) ); ?>">
							<?php echo esc_html( (string) $store['phone'] ); ?>
						</a>
					</p>
				<?php endif; ?>
				<?php if ( $show_hours && ! empty( $store['hours'] ) ) : ?>
					<p class="mhdk-store-locator-hours"><?php echo esc_html( (string) $store['hours'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $store['lat'] ) && ! empty( $store['lng'] ) ) : ?>
					<p class="mhdk-store-locator-map-link">
						<a href="<?php echo esc_url( $stores_model->maps_url( $store ) ); ?>" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Get directions', 'mh-dev-kit' ); ?>
						</a>
					</p>
				<?php endif; ?>
				<button type="button" class="mhdk-btn is-secondary mhdk-store-locator-select">
					<?php esc_html_e( 'Select store', 'mh-dev-kit' ); ?>
				</button>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
