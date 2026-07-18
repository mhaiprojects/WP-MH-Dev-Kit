<?php
/**
 * QR Code generator feature.
 *
 * @package MH\DevKit\Modules\UserTools\Features\QrCode
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools\Features\QrCode;

use MH\DevKit\Core\FeatureInterface;
use MH\DevKit\Modules\UserTools\Features\QrCode\Models\QrCodeModel;
use MH\DevKit\Modules\UserTools\Features\QrCode\Presenters\QrCodeAdminPresenter;
use MH\DevKit\Modules\UserTools\Features\QrCode\Presenters\QrCodeFrontendPresenter;
use MH\DevKit\Modules\UserTools\Features\QrCode\Presenters\QrCodePostTypePresenter;

final class QrCodeFeature implements FeatureInterface {

	public static function id(): string {
		return 'qr-code';
	}

	public static function name(): string {
		return 'QR Code Generator';
	}

	public function register(): void {
		$model = new QrCodeModel();

		( new QrCodePostTypePresenter( $model ) )->register();
		( new QrCodeAdminPresenter( $model ) )->register();
		( new QrCodeFrontendPresenter( $model ) )->register();
	}
}
