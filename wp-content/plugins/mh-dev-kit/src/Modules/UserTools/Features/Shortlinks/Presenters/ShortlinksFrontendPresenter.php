<?php
/**
 * Shortlinks public page routing.
 *
 * @package MH\DevKit\Modules\UserTools\Features\Shortlinks\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools\Features\Shortlinks\Presenters;

use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\UserTools\Features\Shortlinks\Models\ShortlinksModel;
use MH\DevKit\Shared\AssetRegistrar;

final class ShortlinksFrontendPresenter {

	public function __construct(
		private ShortlinksModel $model
	) {}

	public function register(): void {
		add_action( 'init', array( $this, 'add_rewrite' ) );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'template_redirect', array( $this, 'render_link_page' ) );
	}

	public function add_rewrite(): void {
		add_rewrite_rule( '^l/([^/]+)/?$', 'index.php?mhdk_link_slug=$matches[1]', 'top' );
	}

	/**
	 * @param string[] $vars Query vars.
	 * @return string[]
	 */
	public function query_vars( array $vars ): array {
		$vars[] = 'mhdk_link_slug';

		return $vars;
	}

	public function render_link_page(): void {
		$slug = sanitize_title( (string) get_query_var( 'mhdk_link_slug' ) );

		if ( '' === $slug ) {
			return;
		}

		$post = $this->model->find_by_slug( $slug );

		if ( ! $post instanceof \WP_Post ) {
			status_header( 404 );
			get_template_part( '404' );
			exit;
		}

		AssetRegistrar::enqueue_style(
			'mhdk-shortlinks',
			'Modules/UserTools/Features/Shortlinks',
			'css/shortlinks.css'
		);

		status_header( 200 );

		ViewRenderer::display(
			'Modules/UserTools/Features/Shortlinks/Views/templates/link-page.php',
			array(
				'post'  => $post,
				'bio'   => $this->model->get_bio( (int) $post->ID ),
				'links' => $this->model->get_links( (int) $post->ID ),
			)
		);
		exit;
	}
}
