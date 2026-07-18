<?php
/**
 * Markdown parsing for frontend render.
 *
 * @package MH\DevKit\Modules\ContentTools\Features\MarkdownEditor\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\ContentTools\Features\MarkdownEditor\Models;

final class MarkdownModel {

	public function render_html( string $markdown ): string {
		$markdown = trim( $markdown );

		if ( '' === $markdown ) {
			return '';
		}

		return sprintf(
			'<div class="mhdk-markdown" data-markdown="%1$s"></div>',
			esc_attr( $markdown )
		);
	}
}
