# Commerce Module Tests

- **Status:** FAIL
- **Generated:** 2026-08-25 07:27:18 UTC
- **Tests:** 25
- **Passed:** 19
- **Failed:** 1
- **Errors:** 0
- **Skipped:** 5
- **Duration:** 0.114s

## Test cases

- ✓ `MH.DevKit.Tests.Integration.Commerce.StoreLocatorRestIntegrationTest::test_nearby_rest_route_returns_sorted_stores` (0.017006s)
- ✓ `MH.DevKit.Tests.Unit.Commerce.OrderMenuModelTest::test_menu_token_round_trip` (0.003524s)
- ○ `MH.DevKit.Tests.Unit.Commerce.OrderMenuModelTest::test_parse_unit_from_product_name` (0.000868s)
- ✓ `MH.DevKit.Tests.Unit.Commerce.OrderMenuModelTest::test_normalize_atts_sanitizes_values` (0.000463s)
- ✓ `MH.DevKit.Tests.Unit.Commerce.OrderMenuModelTest::test_resolve_filter_atts_uses_default_category` (0.002293s)
- ✓ `MH.DevKit.Tests.Unit.Commerce.OrderMenuModelTest::test_verify_menu_token_rejects_tampered_signature` (0.001129s)
- ✓ `MH.DevKit.Tests.Unit.Commerce.OrderMenuModelTest::test_sanitize_quantities` (0.001512s)
- ✓ `MH.DevKit.Tests.Integration.Commerce.CommerceModulesCompatibilityTest::test_inventory_stores_endpoint_uses_store_locator_ids` (0.005609s)
- ✓ `MH.DevKit.Tests.Integration.Commerce.CommerceModulesCompatibilityTest::test_nearby_stores_and_inventory_share_store_catalog` (0.007413s)
- ○ `MH.DevKit.Tests.Integration.Commerce.OrderMenuIntegrationTest::test_saved_menu_cpt_exists_when_commerce_booted` (0.000310s)
- ○ `MH.DevKit.Tests.Integration.Commerce.OrderMenuIntegrationTest::test_is_seasonally_available_respects_meta_dates` (0.000276s)
- ✓ `MH.DevKit.Tests.Unit.Commerce.HybridPricing.CatalogVisibilityServiceTest::test_hides_b2b_only_product_from_retail_user` (0.002131s)
- ✗ `MH.DevKit.Tests.Unit.Commerce.HybridPricing.CatalogVisibilityServiceTest::test_shows_b2b_only_product_to_b2b_user` (0.056734s)
- ○ `MH.DevKit.Tests.Unit.Commerce.HybridPricing.PriceResolverTest::test_picks_lowest_fixed_price_for_product_rules` (0.000621s)
- ○ `MH.DevKit.Tests.Unit.Commerce.HybridPricing.PriceResolverTest::test_category_discount_picks_lowest_price` (0.000025s)
- ✓ `MH.DevKit.Tests.Unit.Commerce.HybridPricing.PriceResolverTest::test_per_product_hide_price_for_b2c` (0.002214s)
- ✓ `MH.DevKit.Tests.Unit.Commerce.InventoryModelTest::test_set_and_get_stock_per_store` (0.001855s)
- ✓ `MH.DevKit.Tests.Unit.Commerce.InventoryModelTest::test_total_stock_sums_all_stores` (0.002785s)
- ✓ `MH.DevKit.Tests.Unit.Commerce.InventoryModelTest::test_get_stores_with_stock_links_to_store_locator` (0.002422s)
- ✓ `MH.DevKit.Tests.Unit.Commerce.OrderMenuSettingsModelTest::test_sanitize_clamps_max_qty` (0.000423s)
- ✓ `MH.DevKit.Tests.Unit.Commerce.OrderMenuSettingsModelTest::test_max_qty_per_line_bounds` (0.000808s)
- ✓ `MH.DevKit.Tests.Unit.Commerce.StoreLocatorModelTest::test_defaults_include_four_australian_stores` (0.000519s)
- ✓ `MH.DevKit.Tests.Unit.Commerce.StoreLocatorModelTest::test_sort_by_distance_orders_nearest_first` (0.001653s)
- ✓ `MH.DevKit.Tests.Unit.Commerce.StoreLocatorModelTest::test_get_by_id_returns_store` (0.000740s)
- ✓ `MH.DevKit.Tests.Unit.Commerce.StoreLocatorModelTest::test_seed_defaults_if_empty_writes_option` (0.000909s)
