<?php
/**
 * Module (sub-plugin) contract.
 *
 * @package MH\DevKit\Core
 */

declare(strict_types=1);

namespace MH\DevKit\Core;

interface ModuleInterface {

	public static function id(): string;

	public static function name(): string;

	public static function description(): string;

	/**
	 * @return array<int, class-string<FeatureInterface>>
	 */
	public static function features(): array;

	public function requirements(): ModuleRequirements;

	public function register(): void;
}
