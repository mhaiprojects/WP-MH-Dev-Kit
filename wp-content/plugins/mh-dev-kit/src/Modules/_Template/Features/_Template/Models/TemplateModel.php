<?php
/**
 * Template model — replace with feature data access.
 *
 * @package MH\DevKit\Modules\_Template\Features\_Template\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\_Template\Features\_Template\Models;

final class TemplateModel {

	public const OPTION_KEY = 'mhdk_template_settings';

	/**
	 * @return array<string, mixed>
	 */
	public function get_defaults(): array {
		return array(
			'example_text' => '',
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_all(): array {
		$stored = get_option( self::OPTION_KEY, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		return array_merge( $this->get_defaults(), $stored );
	}

	public function get( string $key, mixed $default = null ): mixed {
		$all = $this->get_all();

		return $all[ $key ] ?? $default;
	}

	/**
	 * @param array<string, mixed> $input Raw settings.
	 * @return array<string, mixed>
	 */
	public function sanitize( array $input ): array {
		$clean = $this->get_defaults();

		if ( isset( $input['example_text'] ) ) {
			$clean['example_text'] = sanitize_text_field( (string) $input['example_text'] );
		}

		return $clean;
	}

	public function get_message(): string {
		return 'Template feature placeholder';
	}
}
