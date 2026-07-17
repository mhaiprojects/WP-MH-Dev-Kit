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
			'name'        => __( 'Module', 'mh-dev-kit' ),
			'description' => __( 'Description', 'mh-dev-kit' ),
			'status'      => __( 'Status', 'mh-dev-kit' ),
			'enabled'     => __( 'Enabled', 'mh-dev-kit' ),
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

		return sprintf(
			'<label><input type="checkbox" name="%1$s" value="1" %2$s /> %3$s</label>',
			esc_attr( $name ),
			checked( $enabled, true, false ),
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
