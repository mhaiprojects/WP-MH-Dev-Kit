<?php
/**
 * Seeds the Markdown block demo page on first run.
 *
 * @package MH\DevKit\Modules\ContentTools\Features\MarkdownEditor\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\ContentTools\Features\MarkdownEditor\Presenters;

use MH\DevKit\Modules\ContentTools\Features\MarkdownEditor\Models\MarkdownModel;

final class MarkdownSeedPresenter {

	public function __construct(
		private MarkdownModel $model
	) {}

	public function register(): void {
		add_action( 'init', array( $this, 'maybe_create_demo_page' ), 20 );
	}

	public function maybe_create_demo_page(): void {
		$page_id = (int) get_option( MarkdownModel::DEMO_OPTION, 0 );

		if ( $page_id > 0 && get_post_status( $page_id ) ) {
			return;
		}

		$existing = get_page_by_path( MarkdownModel::DEMO_PAGE_SLUG );

		if ( $existing instanceof \WP_Post ) {
			update_option( MarkdownModel::DEMO_OPTION, $existing->ID, false );
			return;
		}

		$page_id = wp_insert_post(
			array(
				'post_title'   => __( 'Markdown Block Demo', 'mh-dev-kit' ),
				'post_name'    => MarkdownModel::DEMO_PAGE_SLUG,
				'post_content' => $this->model->serialize_block( $this->model->demo_markdown() ),
				'post_status'  => 'publish',
				'post_type'    => 'page',
			),
			true
		);

		if ( ! is_wp_error( $page_id ) && $page_id > 0 ) {
			update_option( MarkdownModel::DEMO_OPTION, $page_id, false );
		}
	}
}
