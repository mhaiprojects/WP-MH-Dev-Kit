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
	 * @param string $handle   Script handle.
	 * @param string $feature  Feature folder name, e.g. Foundation.
	 * @param string $relative Path under Assets/, e.g. css/foo.css.
	 * @param array  $deps     Style dependencies.
	 */
	public static function enqueue_style(
		string $handle,
		string $feature,
		string $relative,
		array $deps = array()
	): void {
		$path = MH_DEV_KIT_PATH . 'src/Features/' . $feature . '/Assets/' . ltrim( $relative, '/' );
		$url  = MH_DEV_KIT_URL . 'src/Features/' . $feature . '/Assets/' . ltrim( $relative, '/' );

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
	 * @param string $handle   Script handle.
	 * @param string $feature  Feature folder name.
	 * @param string $relative Path under Assets/.
	 * @param array  $deps     Script dependencies.
	 * @param bool   $in_footer Load in footer.
	 */
	public static function enqueue_script(
		string $handle,
		string $feature,
		string $relative,
		array $deps = array(),
		bool $in_footer = true
	): void {
		$path = MH_DEV_KIT_PATH . 'src/Features/' . $feature . '/Assets/' . ltrim( $relative, '/' );
		$url  = MH_DEV_KIT_URL . 'src/Features/' . $feature . '/Assets/' . ltrim( $relative, '/' );

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
}
