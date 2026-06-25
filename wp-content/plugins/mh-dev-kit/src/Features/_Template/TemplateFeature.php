<?php
/**
 * Copy this folder to create a new feature module.
 *
 * @package MH\DevKit\Features\_Template
 */

declare(strict_types=1);

namespace MH\DevKit\Features\_Template;

use MH\DevKit\Core\FeatureInterface;
use MH\DevKit\Features\_Template\Models\TemplateModel;
use MH\DevKit\Features\_Template\Presenters\TemplatePresenter;

final class TemplateFeature implements FeatureInterface {

	public static function id(): string {
		return 'template';
	}

	public static function name(): string {
		return 'Template';
	}

	public function register(): void {
		( new TemplatePresenter( new TemplateModel() ) )->register();
	}
}
