<?php
/**
 * Family Tree Builder feature.
 *
 * @package MH\DevKit\Modules\UserTools\Features\FamilyTree
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools\Features\FamilyTree;

use MH\DevKit\Core\FeatureInterface;
use MH\DevKit\Modules\UserTools\Features\FamilyTree\Models\FamilyTreeModel;
use MH\DevKit\Modules\UserTools\Features\FamilyTree\Presenters\FamilyTreeAdminPresenter;
use MH\DevKit\Modules\UserTools\Features\FamilyTree\Presenters\FamilyTreeFrontendPresenter;
use MH\DevKit\Modules\UserTools\Features\FamilyTree\Presenters\FamilyTreePostTypePresenter;
use MH\DevKit\Modules\UserTools\Features\FamilyTree\Presenters\FamilyTreeRestPresenter;

final class FamilyTreeFeature implements FeatureInterface {

	public static function id(): string {
		return 'family-tree';
	}

	public static function name(): string {
		return 'Family Tree Builder';
	}

	public function register(): void {
		$model = new FamilyTreeModel();

		( new FamilyTreePostTypePresenter( $model ) )->register();
		( new FamilyTreeAdminPresenter( $model ) )->register();
		( new FamilyTreeFrontendPresenter( $model ) )->register();
		( new FamilyTreeRestPresenter( $model ) )->register();
	}
}
