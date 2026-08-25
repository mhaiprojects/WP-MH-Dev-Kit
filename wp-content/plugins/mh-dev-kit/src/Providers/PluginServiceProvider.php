<?php

declare(strict_types=1);

namespace MH\DevKit\Providers;

use Illuminate\Support\ServiceProvider;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Services\CatalogVisibilityService;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Services\PriceResolver;

final class PluginServiceProvider extends ServiceProvider {

	/**
	 * @var list<class-string>
	 */
	public array $singletons = array(
		PriceResolver::class          => PriceResolver::class,
		CatalogVisibilityService::class => CatalogVisibilityService::class,
	);

	public function register(): void {
		//
	}

	public function boot(): void {
		//
	}
}
