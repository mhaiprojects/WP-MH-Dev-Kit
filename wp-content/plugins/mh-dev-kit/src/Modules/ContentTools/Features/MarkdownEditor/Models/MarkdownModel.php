<?php
/**
 * Markdown parsing for frontend render.
 *
 * @package MH\DevKit\Modules\ContentTools\Features\MarkdownEditor\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\ContentTools\Features\MarkdownEditor\Models;

final class MarkdownModel {

	public const BLOCK_NAME = 'mh-dev-kit/markdown';

	public const DEMO_PAGE_SLUG = 'markdown-block-demo';

	public const DEMO_OPTION = 'mhdk_markdown_demo_page_id';

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

	public function demo_markdown(): string {
		return implode(
			"\n",
			array(
				'# Markdown Block Demo',
				'',
				'This page was created automatically to test the **MH Markdown** block.',
				'',
				'## Features',
				'',
				'- Edit Markdown in the block editor or sidebar',
				'- Live preview while editing',
				'- Rendered on the frontend via marked.js',
				'',
				'> The block is available in all block editors (pages, posts, CPTs, widgets, and site editor).',
				'',
				'### Sample table',
				'',
				'| Column | Value |',
				'| --- | --- |',
				'| Plugin | MH Dev Kit |',
				'| Block | MH Markdown |',
			)
		);
	}

	public function serialize_block( string $markdown ): string {
		if ( function_exists( 'serialize_block' ) ) {
			return serialize_block(
				array(
					'blockName'    => self::BLOCK_NAME,
					'attrs'        => array(
						'content' => $markdown,
					),
					'innerBlocks'  => array(),
					'innerHTML'    => '',
					'innerContent' => array(),
				)
			);
		}

		$attrs = wp_json_encode(
			array( 'content' => $markdown ),
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
		);

		if ( ! is_string( $attrs ) ) {
			$attrs = '{}';
		}

		return sprintf( '<!-- wp:%s %s /-->', self::BLOCK_NAME, $attrs );
	}
}
