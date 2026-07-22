<?php
/**
 * API Middleware feature entry.
 *
 * @package MH\DevKit\Modules\Integration\Features\ApiMiddleware
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Integration\Features\ApiMiddleware;

use MH\DevKit\Core\FeatureInterface;
use MH\DevKit\Modules\Integration\Features\ApiMiddleware\Models\ApiMiddlewareModel;
use MH\DevKit\Modules\Integration\Features\ApiMiddleware\Presenters\ApiMiddlewareRestPresenter;
use MH\DevKit\Modules\Integration\Features\ApiMiddleware\Presenters\ApiMiddlewareSchedulerPresenter;
use MH\DevKit\Modules\Integration\Features\ApiMiddleware\Presenters\ApiMiddlewareSeedPresenter;

final class ApiMiddlewareFeature implements FeatureInterface {

	public static function id(): string {
		return 'api-middleware';
	}

	public static function name(): string {
		return 'API Middleware';
	}

	public function register(): void {
		$model = new ApiMiddlewareModel();

		( new ApiMiddlewareSeedPresenter( $model ) )->register();
		( new ApiMiddlewareSchedulerPresenter( $model ) )->register();
		( new ApiMiddlewareRestPresenter( $model ) )->register();
	}
}
