<?php
/**
 * Declarative module dependency checks.
 *
 * @package MH\DevKit\Core
 */

declare(strict_types=1);

namespace MH\DevKit\Core;

final class ModuleRequirements {

	/** @var array<int, string> */
	private array $plugins = array();

	private ?string $php = null;

	/**
	 * @param array<int, string> $plugins Plugin basename paths, e.g. woocommerce/woocommerce.php.
	 */
	public static function needs_plugins( array $plugins ): self {
		$req = new self();
		$req->plugins = $plugins;

		return $req;
	}

	public static function none(): self {
		return new self();
	}

	public function min_php( string $version ): self {
		$this->php = $version;

		return $this;
	}

	public function met(): bool {
		if ( null !== $this->php && version_compare( PHP_VERSION, $this->php, '<' ) ) {
			return false;
		}

		foreach ( $this->plugins as $plugin ) {
			if ( ! is_string( $plugin ) || '' === $plugin ) {
				continue;
			}

			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			if ( ! is_plugin_active( $plugin ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @return array<int, string>
	 */
	public function unmet_messages(): array {
		$messages = array();

		if ( null !== $this->php && version_compare( PHP_VERSION, $this->php, '<' ) ) {
			$messages[] = sprintf(
				/* translators: %s: required PHP version */
				__( 'PHP %s or newer is required.', 'mh-dev-kit' ),
				$this->php
			);
		}

		foreach ( $this->plugins as $plugin ) {
			if ( ! is_string( $plugin ) || '' === $plugin ) {
				continue;
			}

			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			if ( ! is_plugin_active( $plugin ) ) {
				$messages[] = sprintf(
					/* translators: %s: plugin basename */
					__( 'Required plugin is not active: %s', 'mh-dev-kit' ),
					$plugin
				);
			}
		}

		return $messages;
	}
}
