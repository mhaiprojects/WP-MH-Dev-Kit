<?php
/**
 * Template module scaffold.
 *
 * @package MH\DevKit\Modules\_Template
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\_Template;

use MH\DevKit\Core\ModuleInterface;
use MH\DevKit\Core\ModuleRequirements;
use MH\DevKit\Modules\_Template\Features\_Template\TemplateFeature;

final class TemplateModule implements ModuleInterface {

	public static function id(): string {
		return 'template';
	}

	public static function name(): string {
		return 'Template';
	}

	public static function description(): string {
		return __( 'Copy this module scaffold when adding a new module.', 'mh-dev-kit' );
	}

	public static function features(): array {
		return array(
			TemplateFeature::class,
		);
	}

	public function requirements(): ModuleRequirements {
		return ModuleRequirements::none();
	}

	public function register(): void {
		foreach ( self::features() as $feature_class ) {
			( new $feature_class() )->register();
		}
	}
}
