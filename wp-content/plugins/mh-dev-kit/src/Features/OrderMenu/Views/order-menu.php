<?php
/**
 * Order Menu table view.
 *
 * @var array<int, array<string, mixed>> $rows
 * @var array<string, string>            $atts
 * @var string                           $cart_url
 * @var string                           $form_action
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="mhdk-order-menu">
	<form class="mhdk-order-menu__form" method="post" action="<?php echo esc_url( $form_action ); ?>">
		<?php wp_nonce_field( 'mhdk_order_menu_bulk_add', 'mhdk_order_menu_nonce' ); ?>
		<input type="hidden" name="mhdk_menu_category" value="<?php echo esc_attr( $atts['category'] ); ?>" />
		<input type="hidden" name="mhdk_menu_ids" value="<?php echo esc_attr( $atts['ids'] ); ?>" />
		<input type="hidden" name="mhdk_menu_slugs" value="<?php echo esc_attr( $atts['slugs'] ); ?>" />

		<table class="mhdk-order-menu__table">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Product Name', 'mh-dev-kit' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Description', 'mh-dev-kit' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Cost', 'mh-dev-kit' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Quantity', 'mh-dev-kit' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $row ) : ?>
					<tr class="mhdk-order-menu__row" data-product-id="<?php echo esc_attr( (string) $row['id'] ); ?>">
						<td class="mhdk-order-menu__name" data-label="<?php esc_attr_e( 'Product Name', 'mh-dev-kit' ); ?>">
							<strong><?php echo esc_html( (string) $row['name'] ); ?></strong>
						</td>
						<td class="mhdk-order-menu__description" data-label="<?php esc_attr_e( 'Description', 'mh-dev-kit' ); ?>">
							<?php echo esc_html( (string) $row['description'] ); ?>
						</td>
						<td class="mhdk-order-menu__cost" data-label="<?php esc_attr_e( 'Cost', 'mh-dev-kit' ); ?>">
							<?php echo wp_kses_post( (string) $row['price_html'] ); ?>
						</td>
						<td class="mhdk-order-menu__qty" data-label="<?php esc_attr_e( 'Quantity', 'mh-dev-kit' ); ?>">
							<div class="mhdk-order-menu__qty-panel">
								<label class="screen-reader-text" for="mhdk-qty-<?php echo esc_attr( (string) $row['id'] ); ?>">
									<?php
									printf(
										/* translators: %s: product name */
										esc_html__( 'Quantity for %s', 'mh-dev-kit' ),
										esc_html( (string) $row['name'] )
									);
									?>
								</label>
								<div class="mhdk-order-menu__qty-controls">
									<button
										type="button"
										class="mhdk-order-menu__qty-btn mhdk-order-menu__qty-btn--decrease"
										data-action="decrease"
										aria-label="<?php esc_attr_e( 'Decrease quantity', 'mh-dev-kit' ); ?>"
									>−</button>
									<input
										type="number"
										class="mhdk-order-menu__qty-input"
										id="mhdk-qty-<?php echo esc_attr( (string) $row['id'] ); ?>"
										name="mhdk_qty[<?php echo esc_attr( (string) $row['id'] ); ?>]"
										min="0"
										max="99"
										step="1"
										value="0"
										inputmode="numeric"
									/>
									<button
										type="button"
										class="mhdk-order-menu__qty-btn mhdk-order-menu__qty-btn--increase"
										data-action="increase"
										aria-label="<?php esc_attr_e( 'Increase quantity', 'mh-dev-kit' ); ?>"
									>+</button>
									<button
										type="button"
										class="mhdk-order-menu__qty-btn mhdk-order-menu__qty-btn--clear"
										data-action="clear"
									><?php esc_html_e( 'Clear', 'mh-dev-kit' ); ?></button>
								</div>
								<p class="mhdk-order-menu__cart-qty">
									<?php esc_html_e( 'Quantity in Cart', 'mh-dev-kit' ); ?>:
									<span class="mhdk-order-menu__cart-qty-value"><?php echo esc_html( (string) (int) $row['cart_qty'] ); ?></span>
								</p>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<p class="mhdk-order-menu__actions">
			<button type="submit" name="mhdk_order_menu_submit" value="1" class="button alt mhdk-order-menu__submit">
				<?php esc_html_e( 'Add selected to cart', 'mh-dev-kit' ); ?>
			</button>
			<a class="mhdk-order-menu__cart-link" href="<?php echo esc_url( $cart_url ); ?>">
				<?php esc_html_e( 'View cart', 'mh-dev-kit' ); ?>
			</a>
		</p>
	</form>
</div>
