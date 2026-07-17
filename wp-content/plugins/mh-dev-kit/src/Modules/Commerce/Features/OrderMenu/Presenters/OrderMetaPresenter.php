<?php
/**
 * Order meta for bulk menu orders (#20).
 *
 * @package MH\DevKit\Modules\Commerce\Features\OrderMenu\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\OrderMenu\Presenters;

use MH\DevKit\Modules\Commerce\Features\OrderMenu\Models\OrderMenuModel;

final class OrderMetaPresenter {

	public function register(): void {
		add_action( 'woocommerce_checkout_create_order', array( $this, 'tag_order' ), 10, 2 );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'show_admin_badge' ) );
	}

	public function tag_order( \WC_Order $order, array $data ): void {
		unset( $data );

		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			return;
		}

		if ( WC()->session->get( OrderMenuModel::SESSION_FLAG ) ) {
			$order->update_meta_data( '_mhdk_bulk_menu_source', 'order-menu' );
			WC()->session->set( OrderMenuModel::SESSION_FLAG, null );
		}
	}

	public function show_admin_badge( \WC_Order $order ): void {
		if ( 'order-menu' !== $order->get_meta( '_mhdk_bulk_menu_source' ) ) {
			return;
		}

		echo '<p><strong>' . esc_html__( 'Order source:', 'mh-dev-kit' ) . '</strong> ';
		echo esc_html__( 'Bulk Order Menu', 'mh-dev-kit' ) . '</p>';
	}
}
