<?php
/**
 * @package MH\DevKit\Tests\Unit\ContentTools
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Unit\ContentTools;

use MH\DevKit\Modules\ContentTools\Features\MarkdownEditor\Models\MarkdownModel;
use PHPUnit\Framework\TestCase;

final class MarkdownModelTest extends TestCase {

	private MarkdownModel $model;

	protected function setUp(): void {
		parent::setUp();
		$this->model = new MarkdownModel();
	}

	public function test_serialize_block_includes_block_name_and_content(): void {
		$content = $this->model->serialize_block( '# Hello' );

		$this->assertStringContainsString( 'wp:mh-dev-kit/markdown', $content );
		$this->assertStringContainsString( '# Hello', $content );
	}

	public function test_demo_markdown_is_non_empty(): void {
		$this->assertNotSame( '', trim( $this->model->demo_markdown() ) );
	}
}
