<?php
/**
 * User profile fields for B2B price list assignment.
 *
 * @package MH\DevKit\Modules\Commerce\Features\HybridPricing\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\HybridPricing\Presenters;

use MH\DevKit\Modules\Commerce\Features\HybridPricing\Models\HybridPricingSettingsModel;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Models\PriceListModel;

final class HybridPricingUserPresenter {

	public function __construct(
		private PriceListModel $price_lists
	) {
	}

	public function register(): void {
		add_action( 'show_user_profile', array( $this, 'render_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'render_fields' ) );
		add_action( 'personal_options_update', array( $this, 'save_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_fields' ) );
	}

	public function render_fields( \WP_User $user ): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$assigned = get_user_meta( $user->ID, 'mhdk_price_list_ids', true );
		if ( ! is_array( $assigned ) ) {
			$assigned = array();
		}

		$lists = $this->price_lists->get_all_lists();
		if ( array() === $lists ) {
			$this->price_lists->ensure_defaults();
			$lists = $this->price_lists->get_all_lists();
		}

		$default_id = (string) ( new HybridPricingSettingsModel() )->get( 'default_b2b_list_id', 'wholesale-default' );
		?>
		<h2><?php esc_html_e( 'MH Dev Kit — B2B pricing', 'mh-dev-kit' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Price lists', 'mh-dev-kit' ); ?></th>
				<td>
					<p class="description">
						<?php
						printf(
							/* translators: %s: default list id */
							esc_html__( 'Assign wholesale price lists. Leave empty to use the default list (%s) when the user has B2B access.', 'mh-dev-kit' ),
							esc_html( $default_id )
						);
						?>
					</p>
					<?php foreach ( $lists as $list ) : ?>
						<?php
						$list_id = (string) ( $list['id'] ?? '' );
						if ( '' === $list_id ) {
							continue;
						}
						?>
						<label style="display:block;margin:.25rem 0;">
							<input
								type="checkbox"
								name="mhdk_price_list_ids[]"
								value="<?php echo esc_attr( $list_id ); ?>"
								<?php checked( in_array( $list_id, $assigned, true ) ); ?>
							/>
							<?php echo esc_html( (string) ( $list['name'] ?? $list_id ) ); ?>
							<?php if ( empty( $list['enabled'] ) ) : ?>
								<em>(<?php esc_html_e( 'disabled', 'mh-dev-kit' ); ?>)</em>
							<?php endif; ?>
						</label>
					<?php endforeach; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'B2B role', 'mh-dev-kit' ); ?></th>
				<td>
					<p>
						<?php
						printf(
							/* translators: %s: role slug */
							esc_html__( 'Assign the %s role under Account Management for wholesale catalog and pricing access.', 'mh-dev-kit' ),
							'<code>' . esc_html( HybridPricingSettingsModel::B2B_ROLE ) . '</code>'
						);
						?>
					</p>
				</td>
			</tr>
		</table>
		<?php
	}

	public function save_fields( int $user_id ): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$raw = isset( $_POST['mhdk_price_list_ids'] ) && is_array( $_POST['mhdk_price_list_ids'] )
			? wp_unslash( $_POST['mhdk_price_list_ids'] )
			: array();

		$valid_ids = array_map(
			static fn( array $list ): string => (string) ( $list['id'] ?? '' ),
			$this->price_lists->get_all_lists()
		);

		$clean = array();
		foreach ( $raw as $list_id ) {
			$list_id = sanitize_key( (string) $list_id );
			if ( '' !== $list_id && in_array( $list_id, $valid_ids, true ) ) {
				$clean[] = $list_id;
			}
		}

		if ( array() === $clean ) {
			delete_user_meta( $user_id, 'mhdk_price_list_ids' );
			return;
		}

		update_user_meta( $user_id, 'mhdk_price_list_ids', array_values( array_unique( $clean ) ) );
	}
}
