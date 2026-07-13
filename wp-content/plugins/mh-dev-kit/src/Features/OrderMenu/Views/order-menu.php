<?php
/**
 * Order Menu table view.
 *
 * @var array<int, array<string, mixed>>  $groups
 * @var array<string, string>             $atts
 * @var string                            $cart_url
 * @var string                            $form_action
 * @var array<string, mixed>              $settings
 * @var bool                              $show_unit
 * @var bool                              $show_stock
 * @var bool                              $show_image
 * @var int                               $max_qty
 * @var bool                              $ajax_enabled
 * @var bool                              $preserve_qty
 * @var bool                              $show_reorder
 * @var string                            $reorder_url
 * @var string                            $currency_symbol
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$colspan = 5 + ( $show_unit ? 1 : 0 ) + ( $show_stock ? 1 : 0 ) + ( $show_image ? 1 : 0 );
?>
<div class="mhdk-order-menu" data-preserve-qty="<?php echo esc_attr( $preserve_qty ? '1' : '0' ); ?>" data-ajax="<?php echo esc_attr( $ajax_enabled ? '1' : '0' ); ?>">
	<div class="mhdk-order-menu__toolbar">
		<label class="mhdk-order-menu__search-wrap">
			<span class="screen-reader-text"><?php esc_html_e( 'Search products', 'mh-dev-kit' ); ?></span>
			<input type="search" class="mhdk-order-menu__search" placeholder="<?php esc_attr_e( 'Search products…', 'mh-dev-kit' ); ?>" />
		</label>
		<?php if ( $show_reorder ) : ?>
			<a class="button mhdk-order-menu__reorder" href="<?php echo esc_url( $reorder_url ); ?>"><?php esc_html_e( 'Reorder last order', 'mh-dev-kit' ); ?></a>
		<?php endif; ?>
		<button type="button" class="button mhdk-order-menu__clear-all"><?php esc_html_e( 'Clear all', 'mh-dev-kit' ); ?></button>
	</div>

	<form class="mhdk-order-menu__form" method="post" action="<?php echo esc_url( $form_action ); ?>">
		<?php wp_nonce_field( 'mhdk_order_menu_bulk_add', 'mhdk_order_menu_nonce' ); ?>
		<input type="hidden" name="mhdk_menu_category" value="<?php echo esc_attr( $atts['category'] ); ?>" />
		<input type="hidden" name="mhdk_menu_ids" value="<?php echo esc_attr( $atts['ids'] ); ?>" />
		<input type="hidden" name="mhdk_menu_slugs" value="<?php echo esc_attr( $atts['slugs'] ); ?>" />
		<input type="hidden" name="mhdk_menu_menu" value="<?php echo esc_attr( $atts['menu'] ); ?>" />
		<input type="hidden" name="mhdk_menu_orderby" value="<?php echo esc_attr( $atts['orderby'] ); ?>" />
		<input type="hidden" name="mhdk_menu_order" value="<?php echo esc_attr( $atts['order'] ); ?>" />

		<table class="mhdk-order-menu__table">
			<thead>
				<tr>
					<?php if ( $show_image ) : ?>
						<th scope="col"><?php esc_html_e( 'Image', 'mh-dev-kit' ); ?></th>
					<?php endif; ?>
					<th scope="col"><?php esc_html_e( 'Product Name', 'mh-dev-kit' ); ?></th>
					<?php if ( $show_unit ) : ?>
						<th scope="col"><?php esc_html_e( 'Unit', 'mh-dev-kit' ); ?></th>
					<?php endif; ?>
					<th scope="col"><?php esc_html_e( 'Description', 'mh-dev-kit' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Cost', 'mh-dev-kit' ); ?></th>
					<?php if ( $show_stock ) : ?>
						<th scope="col"><?php esc_html_e( 'Availability', 'mh-dev-kit' ); ?></th>
					<?php endif; ?>
					<th scope="col"><?php esc_html_e( 'Quantity', 'mh-dev-kit' ); ?></th>
					<th scope="col" class="mhdk-order-menu__subtotal-col"><?php esc_html_e( 'Subtotal', 'mh-dev-kit' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $groups as $group ) : ?>
					<tr class="mhdk-order-menu__section">
						<th colspan="<?php echo esc_attr( (string) $colspan ); ?>" scope="colgroup"><?php echo esc_html( (string) $group['label'] ); ?></th>
					</tr>
					<?php foreach ( $group['rows'] as $row ) : ?>
						<tr
							class="mhdk-order-menu__row<?php echo $row['purchasable'] ? '' : ' is-unavailable'; ?>"
							data-product-id="<?php echo esc_attr( (string) $row['id'] ); ?>"
							data-price="<?php echo esc_attr( (string) $row['price'] ); ?>"
							data-name="<?php echo esc_attr( (string) $row['name'] ); ?>"
						>
							<?php if ( $show_image ) : ?>
								<td class="mhdk-order-menu__image" data-label="<?php esc_attr_e( 'Image', 'mh-dev-kit' ); ?>">
									<?php echo wp_kses_post( (string) $row['image_html'] ); ?>
								</td>
							<?php endif; ?>
							<td class="mhdk-order-menu__name" data-label="<?php esc_attr_e( 'Product Name', 'mh-dev-kit' ); ?>">
								<strong><?php echo esc_html( (string) $row['name'] ); ?></strong>
							</td>
							<?php if ( $show_unit ) : ?>
								<td class="mhdk-order-menu__unit" data-label="<?php esc_attr_e( 'Unit', 'mh-dev-kit' ); ?>">
									<?php echo esc_html( (string) $row['unit'] ); ?>
								</td>
							<?php endif; ?>
							<td class="mhdk-order-menu__description" data-label="<?php esc_attr_e( 'Description', 'mh-dev-kit' ); ?>">
								<?php echo esc_html( (string) $row['description'] ); ?>
							</td>
							<td class="mhdk-order-menu__cost" data-label="<?php esc_attr_e( 'Cost', 'mh-dev-kit' ); ?>">
								<?php echo wp_kses_post( (string) $row['price_html'] ); ?>
							</td>
							<?php if ( $show_stock ) : ?>
								<td class="mhdk-order-menu__stock" data-label="<?php esc_attr_e( 'Availability', 'mh-dev-kit' ); ?>">
									<span class="mhdk-order-menu__badge mhdk-order-menu__badge--<?php echo esc_attr( (string) $row['stock']['class'] ); ?>">
										<?php echo esc_html( (string) $row['stock']['label'] ); ?>
									</span>
								</td>
							<?php endif; ?>
							<td class="mhdk-order-menu__qty" data-label="<?php esc_attr_e( 'Quantity', 'mh-dev-kit' ); ?>">
								<div class="mhdk-order-menu__qty-panel">
									<label class="screen-reader-text" for="mhdk-qty-<?php echo esc_attr( (string) $row['id'] ); ?>">
										<?php
										printf(
											esc_html__( 'Quantity for %s', 'mh-dev-kit' ),
											esc_html( (string) $row['name'] )
										);
										?>
									</label>
									<div class="mhdk-order-menu__qty-controls">
										<button type="button" class="mhdk-order-menu__qty-btn mhdk-order-menu__qty-btn--decrease" data-action="decrease" aria-label="<?php esc_attr_e( 'Decrease quantity', 'mh-dev-kit' ); ?>">−</button>
										<input
											type="number"
											class="mhdk-order-menu__qty-input"
											id="mhdk-qty-<?php echo esc_attr( (string) $row['id'] ); ?>"
											name="mhdk_qty[<?php echo esc_attr( (string) $row['id'] ); ?>]"
											min="0"
											max="<?php echo esc_attr( (string) $max_qty ); ?>"
											step="1"
											value="<?php echo esc_attr( (string) (int) $row['initial_qty'] ); ?>"
											inputmode="numeric"
											<?php disabled( ! $row['purchasable'] ); ?>
										/>
										<button type="button" class="mhdk-order-menu__qty-btn mhdk-order-menu__qty-btn--increase" data-action="increase" aria-label="<?php esc_attr_e( 'Increase quantity', 'mh-dev-kit' ); ?>">+</button>
										<button type="button" class="mhdk-order-menu__qty-btn mhdk-order-menu__qty-btn--clear" data-action="clear"><?php esc_html_e( 'Clear', 'mh-dev-kit' ); ?></button>
										<button
											type="<?php echo $ajax_enabled ? 'button' : 'submit'; ?>"
											name="mhdk_order_menu_single"
											value="<?php echo esc_attr( (string) $row['id'] ); ?>"
											class="mhdk-order-menu__qty-btn mhdk-order-menu__qty-btn--add"
											data-product-id="<?php echo esc_attr( (string) $row['id'] ); ?>"
											<?php disabled( ! $row['purchasable'] ); ?>
										><?php esc_html_e( 'Add Item', 'mh-dev-kit' ); ?></button>
									</div>
									<p class="mhdk-order-menu__cart-qty">
										<?php esc_html_e( 'Quantity in Cart', 'mh-dev-kit' ); ?>:
										<span class="mhdk-order-menu__cart-qty-value"><?php echo esc_html( (string) (int) $row['cart_qty'] ); ?></span>
									</p>
									<p class="mhdk-order-menu__row-message" aria-live="polite"></p>
								</div>
							</td>
							<td class="mhdk-order-menu__subtotal" data-label="<?php esc_attr_e( 'Subtotal', 'mh-dev-kit' ); ?>">
								<span class="mhdk-order-menu__subtotal-value"><?php echo esc_html( $currency_symbol ); ?>0.00</span>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr>
					<th colspan="<?php echo esc_attr( (string) ( $colspan - 1 ) ); ?>" scope="row"><?php esc_html_e( 'Order total (selected quantities)', 'mh-dev-kit' ); ?></th>
					<td class="mhdk-order-menu__order-total"><strong><span class="mhdk-order-menu__order-total-value"><?php echo esc_html( $currency_symbol ); ?>0.00</span></strong></td>
				</tr>
			</tfoot>
		</table>

		<p class="mhdk-order-menu__actions">
			<button type="<?php echo $ajax_enabled ? 'button' : 'submit'; ?>" name="mhdk_order_menu_submit" value="1" class="button alt mhdk-order-menu__submit mhdk-order-menu__bulk-add">
				<?php esc_html_e( 'Add selected to cart', 'mh-dev-kit' ); ?>
			</button>
			<a class="mhdk-order-menu__cart-link" href="<?php echo esc_url( $cart_url ); ?>">
				<?php esc_html_e( 'View cart', 'mh-dev-kit' ); ?>
			</a>
			<span class="mhdk-order-menu__notice" aria-live="polite"></span>
		</p>
	</form>
</div>
