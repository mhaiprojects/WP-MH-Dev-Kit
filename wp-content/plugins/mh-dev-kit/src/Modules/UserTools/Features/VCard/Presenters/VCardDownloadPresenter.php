<?php
/**
 * vCard .vcf download handler.
 *
 * @package MH\DevKit\Modules\UserTools\Features\VCard\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools\Features\VCard\Presenters;

use MH\DevKit\Modules\UserTools\Features\VCard\Models\VCardModel;

final class VCardDownloadPresenter {

	public function __construct(
		private VCardModel $model
	) {}

	public function register(): void {
		add_action( 'init', array( $this, 'add_rewrite' ) );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'template_redirect', array( $this, 'maybe_download' ) );
	}

	public function add_rewrite(): void {
		add_rewrite_rule( '^vcard/([0-9]+)/?$', 'index.php?mhdk_vcard_id=$matches[1]', 'top' );
	}

	/**
	 * @param string[] $vars Query vars.
	 * @return string[]
	 */
	public function query_vars( array $vars ): array {
		$vars[] = 'mhdk_vcard_id';

		return $vars;
	}

	public function maybe_download(): void {
		$post_id = (int) get_query_var( 'mhdk_vcard_id' );

		if ( $post_id <= 0 ) {
			return;
		}

		$post = get_post( $post_id );

		if ( ! $post instanceof \WP_Post || VCardModel::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
			status_header( 404 );
			exit;
		}

		$filename = sanitize_file_name( get_the_title( $post ) ?: 'contact' ) . '.vcf';
		$content  = $this->model->build_vcf( $post_id );

		header( 'Content-Type: text/vcard; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Content-Length: ' . strlen( $content ) );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- vCard binary/text output.
		echo $content;
		exit;
	}

	public static function download_url( int $post_id ): string {
		return home_url( 'vcard/' . $post_id . '/' );
	}
}
