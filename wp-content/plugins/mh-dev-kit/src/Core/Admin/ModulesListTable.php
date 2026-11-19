<?php
/**
 * Modules list table for MHDK Settings > Modules.
 *
 * @package MH\DevKit\Core\Admin
 */

declare(strict_types=1);

namespace MH\DevKit\Core\Admin;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

final class ModulesListTable extends \WP_List_Table {

	/**
	 * @param array<int, array<string, mixed>>         $modules    Module rows.
	 * @param array<string, array{enabled: bool}>      $settings   Module settings.
	 * @param string                                   $option_key Option name.
	 */
	public function __construct(
		private array $modules,
		private array $settings,
		private string $option_key
	) {
		parent::__construct(
			array(
				'singular' => 'module',
				'plural'   => 'modules',
				'ajax'     => false,
			)
		);
	}

	public function get_columns(): array {
		return array(
			'name'           => __( 'Module', 'mh-dev-kit' ),
			'description'    => __( 'Description', 'mh-dev-kit' ),
			'dependencies'   => __( 'Dependencies', 'mh-dev-kit' ),
			'status'         => __( 'Status', 'mh-dev-kit' ),
			'enabled'        => __( 'Enabled', 'mh-dev-kit' ),
		);
	}

	protected function get_sortable_columns(): array {
		return array();
	}

	protected function get_table_classes(): array {
		return array( 'wp-list-table', 'widefat', 'fixed', 'striped', $this->_args['plural'] );
	}

	public function prepare_items(): void {
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		$this->items           = $this->modules;
	}

	/**
	 * @param array<string, mixed> $item Module row.
	 */
	public function column_name( array $item ): string {
		return '<strong>' . esc_html( (string) $item['name'] ) . '</strong>';
	}

	/**
	 * @param array<string, mixed> $item Module row.
	 */
	public function column_description( array $item ): string {
		return esc_html( (string) $item['description'] );
	}

	/**
	 * @param array<string, mixed> $item Module row.
	 */
	public function column_dependencies( array $item ): string {
		$lines = array();

		if ( ! empty( $item['required_modules'] ) ) {
			foreach ( (array) $item['required_modules'] as $dep ) {
				if ( ! is_array( $dep ) ) {
					continue;
				}

				$label   = (string) ( $dep['name'] ?? $dep['id'] ?? '' );
				$enabled = ! empty( $dep['enabled'] );
				$icon    = $enabled ? 'dashicons-yes' : 'dashicons-warning';

				$lines[] = sprintf(
					'<span class="dashicons %1$s" aria-hidden="true"></span> %2$s',
					esc_attr( $icon ),
					esc_html(
						$enabled
							/* translators: %s: module name */
							? sprintf( __( 'Requires %s', 'mh-dev-kit' ), $label )
							/* translators: %s: module name */
							: sprintf( __( 'Requires %s (disabled)', 'mh-dev-kit' ), $label )
					)
				);
			}
		}

		if ( ! empty( $item['required_plugins'] ) ) {
			foreach ( (array) $item['required_plugins'] as $plugin ) {
				$lines[] = '<span class="dashicons dashicons-admin-plugins" aria-hidden="true"></span> ' . esc_html(
					sprintf(
						/* translators: %s: plugin basename */
						__( 'Plugin: %s', 'mh-dev-kit' ),
						(string) $plugin
					)
				);
			}
		}

		if ( ! empty( $item['dependents'] ) ) {
			foreach ( (array) $item['dependents'] as $dependent ) {
				$lines[] = '<span class="dashicons dashicons-networking" aria-hidden="true"></span> ' . esc_html(
					sprintf(
						/* translators: %s: module name */
						__( 'Required by %s', 'mh-dev-kit' ),
						(string) $dependent
					)
				);
			}
		}

		if ( array() === $lines ) {
			return '<span class="description">' . esc_html__( 'None', 'mh-dev-kit' ) . '</span>';
		}

		return implode( '<br />', $lines );
	}

	/**
	 * @param array<string, mixed> $item Module row.
	 */
	public function column_status( array $item ): string {
		$id      = (string) $item['id'];
		$enabled = ! empty( $this->settings[ $id ]['enabled'] );
		$met     = ! empty( $item['requirements_met'] );

		if ( ! $met ) {
			$html = '<span class="dashicons dashicons-warning" aria-hidden="true"></span> ';
			$html .= esc_html__( 'Requirements not met', 'mh-dev-kit' );

			if ( ! empty( $item['requirements_messages'] ) ) {
				$html .= '<p class="description">' . esc_html( implode( ' ', (array) $item['requirements_messages'] ) ) . '</p>';
			}

			return $html;
		}

		if ( $enabled ) {
			return '<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span> ' . esc_html__( 'Active', 'mh-dev-kit' );
		}

		return '<span class="dashicons dashicons-marker" aria-hidden="true"></span> ' . esc_html__( 'Disabled', 'mh-dev-kit' );
	}

	/**
	 * @param array<string, mixed> $item Module row.
	 */
	public function column_enabled( array $item ): string {
		$id      = (string) $item['id'];
		$enabled = ! empty( $this->settings[ $id ]['enabled'] );
		$name    = $this->option_key . '[' . $id . '][enabled]';
		$met     = ! empty( $item['requirements_met'] );
		$deps_ok = empty( $item['module_dependencies_met'] ) ? true : ! empty( $item['module_dependencies_met'] );

		$disabled_attr = ( $met && $deps_ok ) ? '' : ' disabled="disabled"';

		return sprintf(
			'<label><input type="checkbox" name="%1$s" value="1" %2$s%3$s /> %4$s</label>',
			esc_attr( $name ),
			checked( $enabled, true, false ),
			$disabled_attr,
			esc_html__( 'Enabled', 'mh-dev-kit' )
		);
	}

	/**
	 * @param array<string, mixed> $item        Module row.
	 * @param string               $column_name Column key.
	 */
	protected function column_default( $item, $column_name ): string {
		return isset( $item[ $column_name ] ) ? esc_html( (string) $item[ $column_name ] ) : '';
	}
}
