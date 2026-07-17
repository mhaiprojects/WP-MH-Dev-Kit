<?php
/**
 * Renders Settings API field controls using WordPress admin classes.
 *
 * @package MH\DevKit\Core\Admin
 */

declare(strict_types=1);

namespace MH\DevKit\Core\Admin;

final class SettingsFieldRenderer {

	public static function checkbox( string $name, bool $checked, string $label, ?string $description = null ): void {
		printf(
			'<label><input type="checkbox" name="%1$s" value="1" %2$s /> %3$s</label>',
			esc_attr( $name ),
			checked( $checked, true, false ),
			esc_html( $label )
		);

		if ( null !== $description && '' !== $description ) {
			printf( '<p class="description">%s</p>', esc_html( $description ) );
		}
	}

	public static function text( string $name, string $value, string $input_class = 'regular-text', ?string $description = null ): void {
		printf(
			'<input type="text" class="%1$s" name="%2$s" value="%3$s" />',
			esc_attr( $input_class ),
			esc_attr( $name ),
			esc_attr( $value )
		);

		if ( null !== $description && '' !== $description ) {
			printf( '<p class="description">%s</p>', esc_html( $description ) );
		}
	}

	public static function number( string $name, int $value, int $min, int $max, ?string $description = null ): void {
		printf(
			'<input type="number" min="%1$d" max="%2$d" class="small-text" name="%3$s" value="%4$d" />',
			$min,
			$max,
			esc_attr( $name ),
			$value
		);

		if ( null !== $description && '' !== $description ) {
			printf( '<p class="description">%s</p>', esc_html( $description ) );
		}
	}

	/**
	 * @param array<string, string> $options Value => label.
	 */
	public static function select( string $name, array $options, string $value ): void {
		printf( '<select name="%s">', esc_attr( $name ) );

		foreach ( $options as $key => $label ) {
			printf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( (string) $key ),
				selected( $value, (string) $key, false ),
				esc_html( $label )
			);
		}

		echo '</select>';
	}
}
