<?php
/**
 * Enqueues feature-scoped assets.
 *
 * @package MH\DevKit\Shared
 */

declare(strict_types=1);

namespace MH\DevKit\Shared;

final class AssetRegistrar {

	/**
	 * Enqueue a stylesheet from a feature Assets folder.
	 *
	 * @param string $handle    Script handle.
	 * @param string $asset_base Path under src/ to feature folder, e.g. Modules/Core/Features/MhdkPoc.
	 * @param string $relative  Path under Assets/, e.g. css/foo.css.
	 * @param array  $deps      Style dependencies.
	 */
	public static function enqueue_style(
		string $handle,
		string $asset_base,
		string $relative,
		array $deps = array()
	): void {
		$base = self::normalize_base( $asset_base );
		$path = MH_DEV_KIT_PATH . 'src/' . $base . '/Assets/' . ltrim( $relative, '/' );
		$url  = MH_DEV_KIT_URL . 'src/' . $base . '/Assets/' . ltrim( $relative, '/' );

		if ( ! is_readable( $path ) ) {
			return;
		}

		wp_enqueue_style(
			$handle,
			$url,
			$deps,
			(string) filemtime( $path )
		);
	}

	/**
	 * Enqueue a script from a feature Assets folder.
	 *
	 * @param string $handle    Script handle.
	 * @param string $asset_base Path under src/ to feature folder.
	 * @param string $relative  Path under Assets/.
	 * @param array  $deps      Script dependencies.
	 * @param bool   $in_footer Load in footer.
	 */
	public static function enqueue_script(
		string $handle,
		string $asset_base,
		string $relative,
		array $deps = array(),
		bool $in_footer = true
	): void {
		$base = self::normalize_base( $asset_base );
		$path = MH_DEV_KIT_PATH . 'src/' . $base . '/Assets/' . ltrim( $relative, '/' );
		$url  = MH_DEV_KIT_URL . 'src/' . $base . '/Assets/' . ltrim( $relative, '/' );

		if ( ! is_readable( $path ) ) {
			return;
		}

		wp_enqueue_script(
			$handle,
			$url,
			$deps,
			(string) filemtime( $path ),
			$in_footer
		);
	}

	/**
	 * Register a script from a feature Assets folder (for init-time registration).
	 *
	 * @param string $handle    Script handle.
	 * @param string $asset_base Path under src/ to feature folder.
	 * @param string $relative  Path under Assets/.
	 * @param array  $deps      Script dependencies.
	 * @param bool   $in_footer Load in footer.
	 */
	public static function register_script(
		string $handle,
		string $asset_base,
		string $relative,
		array $deps = array(),
		bool $in_footer = true
	): void {
		$base = self::normalize_base( $asset_base );
		$path = MH_DEV_KIT_PATH . 'src/' . $base . '/Assets/' . ltrim( $relative, '/' );
		$url  = MH_DEV_KIT_URL . 'src/' . $base . '/Assets/' . ltrim( $relative, '/' );

		if ( ! is_readable( $path ) ) {
			return;
		}

		wp_register_script(
			$handle,
			$url,
			$deps,
			(string) filemtime( $path ),
			$in_footer
		);
	}

	private static function normalize_base( string $asset_base ): string {
		return trim( str_replace( '\\', '/', $asset_base ), '/' );
	}
}
