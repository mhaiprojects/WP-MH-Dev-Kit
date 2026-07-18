<?php
/**
 * vCard frontend shortcode.
 *
 * @package MH\DevKit\Modules\UserTools\Features\VCard\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools\Features\VCard\Presenters;

use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\UserTools\Features\VCard\Models\VCardModel;
use MH\DevKit\Shared\AssetRegistrar;

final class VCardFrontendPresenter {

	public function __construct(
		private VCardModel $model
	) {}

	public function register(): void {
		add_shortcode( 'mhdk_vcard', array( $this, 'render_shortcode' ) );
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
			'mhdk_vcard'
		);

		$post_id = (int) $atts['id'];
		$post    = get_post( $post_id );

		if ( ! $post instanceof \WP_Post || VCardModel::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
			return '';
		}

		$fields = $this->model->get_fields( $post_id );

		return ViewRenderer::render(
			'Modules/UserTools/Features/VCard/Views/frontend/card.php',
			array(
				'title'        => get_the_title( $post ),
				'fields'       => $fields,
				'download_url' => VCardDownloadPresenter::download_url( $post_id ),
			)
		);
	}

	public function maybe_enqueue_assets(): void {
		AssetRegistrar::enqueue_style(
			'mhdk-vcard',
			'Modules/UserTools/Features/VCard',
			'css/vcard.css'
		);
	}
}
