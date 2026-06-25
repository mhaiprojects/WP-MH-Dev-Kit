<?php
/**
 * Renders feature view partials.
 *
 * @package MH\DevKit\Core
 */

declare(strict_types=1);

namespace MH\DevKit\Core;

final class ViewRenderer {

	/**
	 * Render a view and return HTML.
	 *
	 * @param string               $relative_path Path under src/Features/, e.g. Foundation/Views/foo.php
	 * @param array<string, mixed> $data          Variables extracted into the partial.
	 */
	public static function render( string $relative_path, array $data = array() ): string {
		$file = self::resolve_path( $relative_path );

		if ( ! is_readable( $file ) ) {
			return '';
		}

		ob_start();
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- MVP view layer.
		extract( $data, EXTR_SKIP );
		include $file;

		return (string) ob_get_clean();
	}

	/**
	 * Echo rendered view.
	 *
	 * @param string               $relative_path Path under src/Features/.
	 * @param array<string, mixed> $data          View data.
	 */
	public static function display( string $relative_path, array $data = array() ): void {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- partials escape output.
		echo self::render( $relative_path, $data );
	}

	private static function resolve_path( string $relative_path ): string {
		$relative_path = ltrim( $relative_path, '/' );

		if ( str_starts_with( $relative_path, 'Core/' ) ) {
			return MH_DEV_KIT_PATH . 'src/' . $relative_path;
		}

		return MH_DEV_KIT_PATH . 'src/Features/' . $relative_path;
	}
}
