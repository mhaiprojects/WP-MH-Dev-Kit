<?php
/**
 * Template presenter — register hooks here.
 *
 * @package MH\DevKit\Features\_Template\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Features\_Template\Presenters;

use MH\DevKit\Features\_Template\Models\TemplateModel;

final class TemplatePresenter {

	public function __construct(
		private TemplateModel $model
	) {}

	public function register(): void {
		// add_action( 'init', array( $this, 'on_init' ) );
	}
}
