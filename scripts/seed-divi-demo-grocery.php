<?php
/**
 * Seed 20 grocery products with categories on divi-demo subsite.
 *
 * @package MH_Dev_Kit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	WP_CLI::error( 'WooCommerce is not active on this site.' );
}

$expected_host = 'divi-demo.cursor-wpdev2.ddev.site';
$current_host  = (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST );
if ( $current_host !== $expected_host ) {
	WP_CLI::error( 'Run on divi-demo subsite only.' );
}

/**
 * @return int Term ID.
 */
function divi_demo_ensure_product_cat( string $name, string $slug ): int {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	if ( $term instanceof WP_Term ) {
		return (int) $term->term_id;
	}

	$result = wp_insert_term(
		$name,
		'product_cat',
		array( 'slug' => $slug )
	);
	if ( is_wp_error( $result ) ) {
		WP_CLI::error( $result->get_error_message() );
	}

	return (int) $result['term_id'];
}

/**
 * @param list<int> $category_ids
 */
function divi_demo_upsert_product(
	string $slug,
	string $name,
	string $price,
	string $short,
	string $description,
	array $category_ids
): int {
	$existing = get_page_by_path( $slug, OBJECT, 'product' );
	if ( $existing instanceof WP_Post ) {
		WP_CLI::log( "Skip (exists): {$name}" );
		return (int) $existing->ID;
	}

	$product = new WC_Product_Simple();
	$product->set_name( $name );
	$product->set_slug( $slug );
	$product->set_regular_price( $price );
	$product->set_short_description( $short );
	$product->set_description( $description );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_category_ids( $category_ids );
	$product_id = $product->save();

	if ( $product_id <= 0 ) {
		WP_CLI::warning( "Failed: {$name}" );
		return 0;
	}

	update_post_meta( $product_id, '_divi_demo_grocery_seed', '1' );
	WP_CLI::log( "Created #{$product_id}: {$name} — \${$price}" );

	return $product_id;
}

$categories = array(
	'fresh-produce' => divi_demo_ensure_product_cat( 'Fresh Produce', 'fresh-produce' ),
	'dairy-eggs'    => divi_demo_ensure_product_cat( 'Dairy & Eggs', 'dairy-eggs' ),
	'pantry'        => divi_demo_ensure_product_cat( 'Pantry', 'pantry' ),
	'beverages'     => divi_demo_ensure_product_cat( 'Beverages', 'beverages' ),
	'frozen'        => divi_demo_ensure_product_cat( 'Frozen', 'frozen' ),
);

$catalog = array(
	array( 'organic-bananas-1kg', 'Organic Bananas (1 kg)', '4.20', 'Ripe organic bananas.', 'Fresh organic bananas — demo grocery item.', array( 'fresh-produce' ), '4.20' ),
	array( 'baby-spinach-200g', 'Baby Spinach (200 g)', '3.80', 'Washed baby spinach.', 'Tender baby spinach leaves.', array( 'fresh-produce' ), '3.80' ),
	array( 'vine-tomatoes-500g', 'Vine Tomatoes (500 g)', '5.50', 'On-the-vine tomatoes.', 'Sweet vine-ripened tomatoes.', array( 'fresh-produce' ), '5.50' ),
	array( 'avocado-hass-each', 'Hass Avocado (each)', '2.90', 'Creamy Hass avocado.', 'Perfect for salads and toast.', array( 'fresh-produce' ), '2.90' ),
	array( 'carrots-1kg', 'Carrots (1 kg)', '2.60', 'Local carrots.', 'Crunchy orange carrots.', array( 'fresh-produce' ), '2.60' ),
	array( 'free-range-eggs-12', 'Free Range Eggs (12 pack)', '6.90', 'Dozen free-range eggs.', 'Large free-range eggs.', array( 'dairy-eggs' ), '6.90' ),
	array( 'whole-milk-2l', 'Whole Milk (2 L)', '3.40', 'Full-cream milk.', 'Fresh whole milk 2 litre.', array( 'dairy-eggs' ), '3.40' ),
	array( 'greek-yoghurt-500g', 'Greek Yoghurt (500 g)', '4.50', 'Thick Greek style yoghurt.', 'Natural Greek yoghurt.', array( 'dairy-eggs' ), '4.50' ),
	array( 'cheddar-block-500g', 'Vintage Cheddar (500 g)', '8.20', 'Sharp cheddar block.', 'Aged cheddar for cooking and snacking.', array( 'dairy-eggs' ), '8.20' ),
	array( 'spaghetti-500g', 'Spaghetti (500 g)', '2.10', 'Durum wheat pasta.', 'Pantry staple spaghetti.', array( 'pantry' ), '2.10' ),
	array( 'extra-virgin-olive-oil-750ml', 'Extra Virgin Olive Oil (750 ml)', '12.50', 'Cold-pressed olive oil.', 'Premium EVOO for dressings.', array( 'pantry' ), '12.50' ),
	array( 'basmati-rice-1kg', 'Basmati Rice (1 kg)', '4.80', 'Long-grain basmati.', 'Fragrant basmati rice.', array( 'pantry' ), '4.80' ),
	array( 'peanut-butter-crunchy-400g', 'Crunchy Peanut Butter (400 g)', '5.60', 'No added sugar.', 'Crunchy natural peanut butter.', array( 'pantry' ), '5.60' ),
	array( 'sparkling-mineral-water-6pk', 'Sparkling Mineral Water (6 pack)', '5.90', '600 ml bottles.', 'Chilled sparkling water multipack.', array( 'beverages' ), '5.90' ),
	array( 'cold-brew-coffee-1l', 'Cold Brew Coffee (1 L)', '6.50', 'Ready to drink.', 'Smooth cold brew coffee.', array( 'beverages' ), '6.50' ),
	array( 'orange-juice-2l', 'Orange Juice (2 L)', '4.70', 'No pulp.', '100% orange juice.', array( 'beverages' ), '4.70' ),
	array( 'craft-ginger-beer-4pk', 'Craft Ginger Beer (4 pack)', '7.80', 'Small batch.', 'Spicy craft ginger beer.', array( 'beverages' ), '7.80' ),
	array( 'frozen-mixed-berries-500g', 'Frozen Mixed Berries (500 g)', '6.20', 'Blueberry, raspberry, strawberry.', 'IQF mixed berries for smoothies.', array( 'frozen' ), '6.20' ),
	array( 'frozen-peas-1kg', 'Frozen Peas (1 kg)', '3.90', 'Garden peas.', 'Sweet frozen peas.', array( 'frozen' ), '3.90' ),
	array( 'margherita-pizza-frozen', 'Margherita Pizza (frozen)', '7.50', 'Stone-baked base.', 'Frozen Margherita pizza.', array( 'frozen' ), '7.50' ),
);

$created = 0;
foreach ( $catalog as $row ) {
	list( $slug, $name, $price, $short, $desc, $cat_slugs ) = $row;
	$cat_ids = array();
	foreach ( $cat_slugs as $cat_slug ) {
		if ( isset( $categories[ $cat_slug ] ) ) {
			$cat_ids[] = $categories[ $cat_slug ];
		}
	}
	$id = divi_demo_upsert_product( $slug, $name, $price, $short, $desc, $cat_ids );
	if ( $id > 0 && ! get_page_by_path( $slug, OBJECT, 'product' ) ) {
		++$created;
	}
}

WP_CLI::success( sprintf( 'Grocery catalog ready (%d categories, %d products in catalog).', count( $categories ), count( $catalog ) ) );
