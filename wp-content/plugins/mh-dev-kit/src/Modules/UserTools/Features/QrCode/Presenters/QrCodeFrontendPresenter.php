<?php
/**
 * QR Code frontend shortcode and assets.
 *
 * @package MH\DevKit\Modules\UserTools\Features\QrCode\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools\Features\QrCode\Presenters;

use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\UserTools\Features\QrCode\Models\QrCodeModel;
use MH\DevKit\Shared\AssetRegistrar;
use MH\DevKit\Shared\UserScopedCptHelper;

final class QrCodeFrontendPresenter {

	public function __construct(
		private QrCodeModel $model
	) {}

	public function register(): void {
		add_shortcode( 'mhdk_qr_code', array( $this, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_assets' ) );
	}

	/**
	 * @param array<string, string>|string $atts Shortcode attributes.
	 */
	public function render_shortcode( $atts = array() ): string {
		$atts = shortcode_atts(
			array(
				'id' => '0',
			),
			(array) $atts,
			'mhdk_qr_code'
		);

		$post_id = (int) $atts['id'];

		if ( $post_id <= 0 ) {
			return '';
		}

		$post = get_post( $post_id );

		if ( ! $post instanceof \WP_Post || QrCodeModel::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
			return '';
		}

		if ( ! UserScopedCptHelper::current_user_owns_post( $post_id ) && ! current_user_can( 'manage_options' ) ) {
			return '';
		}

		$target = $this->model->get_target( $post_id );

		if ( '' === $target ) {
			return '';
		}

		$this->enqueue_qr_assets();

		return ViewRenderer::render(
			'Modules/UserTools/Features/QrCode/Views/frontend/qr-display.php',
			array(
				'target' => $target,
				'size'   => $this->model->get_size( $post_id ),
				'title'  => get_the_title( $post ),
			)
		);
	}

	public function maybe_enqueue_assets(): void {
		if ( ! is_singular() ) {
			return;
		}

		$post = get_post();

		if ( $post instanceof \WP_Post && has_shortcode( $post->post_content, 'mhdk_qr_code' ) ) {
			$this->enqueue_qr_assets();
		}
	}

	private function enqueue_qr_assets(): void {
		wp_enqueue_script(
			'mhdk-qrcode',
			'https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js',
			array(),
			'1.0.0',
			true
		);

		AssetRegistrar::enqueue_script(
			'mhdk-qr-frontend',
			'Modules/UserTools/Features/QrCode',
			'js/qr-frontend.js',
			array( 'mhdk-qrcode' )
		);

		AssetRegistrar::enqueue_style(
			'mhdk-qr-code',
			'Modules/UserTools/Features/QrCode',
			'css/qr-code.css'
		);
	}
}
