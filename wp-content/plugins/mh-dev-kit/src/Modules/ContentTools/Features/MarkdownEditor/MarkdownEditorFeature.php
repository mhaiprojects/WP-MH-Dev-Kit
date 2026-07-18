<?php
/**
 * Markdown Editor Gutenberg block.
 *
 * @package MH\DevKit\Modules\ContentTools\Features\MarkdownEditor
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\ContentTools\Features\MarkdownEditor;

use MH\DevKit\Core\FeatureInterface;
use MH\DevKit\Modules\ContentTools\Features\MarkdownEditor\Models\MarkdownModel;
use MH\DevKit\Modules\ContentTools\Features\MarkdownEditor\Presenters\MarkdownBlockPresenter;

final class MarkdownEditorFeature implements FeatureInterface {

	public static function id(): string {
		return 'markdown-editor';
	}

	public static function name(): string {
		return 'Markdown Editor';
	}

	public function register(): void {
		$model = new MarkdownModel();

		( new MarkdownBlockPresenter( $model ) )->register();
	}
}
