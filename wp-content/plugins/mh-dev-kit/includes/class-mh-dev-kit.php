<?php
/**
 * Main plugin class.
 *
 * @package MH_Dev_Kit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MH Dev Kit singleton.
 */
final class MH_Dev_Kit {

	/**
	 * Plugin instance.
	 *
	 * @var MH_Dev_Kit|null
	 */
	private static ?MH_Dev_Kit $instance = null;

	/**
	 * Get singleton instance.
	 */
	public static function instance(): MH_Dev_Kit {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'admin_notices', array( $this, 'admin_notice_loaded' ) );
	}

	/**
	 * Show a notice in wp-admin when the plugin is active (dev visibility).
	 */
	public function admin_notice_loaded(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-info is-dismissible"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: %s: plugin version */
					__( 'MH Dev Kit %s is active.', 'mh-dev-kit' ),
					MH_DEV_KIT_VERSION
				)
			)
		);
	}
}
