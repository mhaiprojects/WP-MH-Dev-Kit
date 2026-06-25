<?php
/**
 * Feature module contract.
 *
 * @package MH\DevKit\Core
 */

declare(strict_types=1);

namespace MH\DevKit\Core;

interface FeatureInterface {

	public static function id(): string;

	public static function name(): string;

	public function register(): void;
}
