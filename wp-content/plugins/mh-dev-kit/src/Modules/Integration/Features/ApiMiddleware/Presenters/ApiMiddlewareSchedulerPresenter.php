<?php
/**
 * Scheduled API middleware runs via WP-Cron.
 *
 * @package MH\DevKit\Modules\Integration\Features\ApiMiddleware\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Integration\Features\ApiMiddleware\Presenters;

use MH\DevKit\Modules\Integration\Features\ApiMiddleware\Models\ApiMiddlewareModel;

final class ApiMiddlewareSchedulerPresenter {

	public function __construct(
		private ApiMiddlewareModel $model
	) {}

	public function register(): void {
		add_filter( 'cron_schedules', array( $this, 'add_hourly_schedule' ) );
		add_action( ApiMiddlewareModel::CRON_HOOK, array( $this, 'run_scheduled_connections' ) );
		add_action( 'init', array( $this, 'ensure_cron_event' ) );
	}

	/**
	 * @param array<string, array<string, int|string>> $schedules Cron schedules.
	 * @return array<string, array<string, int|string>>
	 */
	public function add_hourly_schedule( array $schedules ): array {
		if ( ! isset( $schedules['mhdk_hourly'] ) ) {
			$schedules['mhdk_hourly'] = array(
				'interval' => HOUR_IN_SECONDS,
				'display'  => __( 'MH Dev Kit Hourly', 'mh-dev-kit' ),
			);
		}

		return $schedules;
	}

	public function ensure_cron_event(): void {
		if ( ! wp_next_scheduled( ApiMiddlewareModel::CRON_HOOK ) ) {
			wp_schedule_event( time() + MINUTE_IN_SECONDS, 'mhdk_hourly', ApiMiddlewareModel::CRON_HOOK );
		}
	}

	public function run_scheduled_connections(): void {
		foreach ( $this->model->get_connections() as $connection ) {
			if ( empty( $connection['enabled'] ) || 'manual' === $connection['schedule'] ) {
				continue;
			}

			if ( 'daily' === $connection['schedule'] && ( time() - (int) $connection['last_run'] ) < DAY_IN_SECONDS ) {
				continue;
			}

			$this->model->run_connection( (string) $connection['id'] );
		}
	}
}
