<?php
/**
 * Local Ad Page public URL routing.
 *
 * @package MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Presenters;

use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Models\LocalAdPageModel;
use MH\DevKit\Shared\AssetRegistrar;

final class LocalAdPageFrontendPresenter {

	public function __construct(
		private LocalAdPageModel $model
	) {}

	public function register(): void {
		add_action( 'init', array( $this, 'add_rewrite' ) );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'template_redirect', array( $this, 'render_page' ) );
	}

	public function add_rewrite(): void {
		add_rewrite_rule(
			'^local-([^-]+)-([^-]+)-(.+)-([0-9]{3,10})/?$',
			'index.php?mhdk_local_country=$matches[1]&mhdk_local_state=$matches[2]&mhdk_local_suburb=$matches[3]&mhdk_local_postcode=$matches[4]',
			'top'
		);

		add_rewrite_rule(
			'^local/([^/]+)/([^/]+)/([^/]+)-([^/]+)/?$',
			'index.php?mhdk_local_country=$matches[1]&mhdk_local_state=$matches[2]&mhdk_local_suburb=$matches[3]&mhdk_local_postcode=$matches[4]',
			'top'
		);
	}

	/**
	 * @param string[] $vars Query vars.
	 * @return string[]
	 */
	public function query_vars( array $vars ): array {
		return array_merge(
			$vars,
			array(
				'mhdk_local_country',
				'mhdk_local_state',
				'mhdk_local_suburb',
				'mhdk_local_postcode',
			)
		);
	}

	public function render_page(): void {
		$country  = sanitize_title( (string) get_query_var( 'mhdk_local_country' ) );
		$state    = sanitize_title( (string) get_query_var( 'mhdk_local_state' ) );
		$suburb   = sanitize_title( (string) get_query_var( 'mhdk_local_suburb' ) );
		$postcode = sanitize_text_field( (string) get_query_var( 'mhdk_local_postcode' ) );

		if ( '' === $country || '' === $state || '' === $suburb || '' === $postcode ) {
			return;
		}

		$post = $this->model->find_by_path( $country, $state, $suburb, $postcode );

		if ( ! $post instanceof \WP_Post ) {
			status_header( 404 );
			get_template_part( '404' );
			exit;
		}

		AssetRegistrar::enqueue_style(
			'mhdk-local-ad',
			'Modules/ContentTools/Features/LocalAdPage',
			'css/local-ad.css'
		);

		status_header( 200 );

		ViewRenderer::display(
			'Modules/ContentTools/Features/LocalAdPage/Views/templates/single.php',
			array(
				'post'     => $post,
				'location' => $this->model->get_location( (int) $post->ID ),
				'headline' => (string) get_post_meta( $post->ID, LocalAdPageModel::META_HEADLINE, true ),
				'body'     => (string) get_post_meta( $post->ID, LocalAdPageModel::META_BODY, true ),
			)
		);
		exit;
	}
}
