<?php
/**
 * Seed factory-sales subsite: B2C factory outlet style (SPC Factory Sales inspired).
 * Includes Mix and Match Bundle quantity packages and buy-any-N tag deals.
 *
 * @package IndustryDemos
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/lib/IndustryDemoHelpers.php';

IndustryDemoHelpers::assert_host( 'factory-sales.cursor-wpdev2.ddev.site' );
IndustryDemoHelpers::ensure_woocommerce_pages();
IndustryDemoHelpers::apply_divi_colors( '#b45309', '#1c1917', '#78350f' );

$cat_spc     = IndustryDemoHelpers::ensure_product_cat( 'spc-tinned', 'SPC & Tinned Fruit' );
$cat_grocery = IndustryDemoHelpers::ensure_product_cat( 'grocery-specials', 'Grocery Specials' );
$cat_pantry  = IndustryDemoHelpers::ensure_product_cat( 'pantry-staples', 'Pantry Staples' );
$cat_drinks  = IndustryDemoHelpers::ensure_product_cat( 'drinks-juice', 'Drinks & Juice' );

$tag_any3 = IndustryDemoHelpers::ensure_product_tag( 'deal-any-3', 'Deal: Any 3 for $7' );
$tag_any4 = IndustryDemoHelpers::ensure_product_tag( 'deal-any-4', 'Deal: Any 4 for $5' );

// Configure Mix and Match Bundle tag bundles (requires Mix and Match Bundle active).
if ( class_exists( 'MMB_Bundle_Tags' ) ) {
	MMB_Bundle_Tags::save_bundles(
		array(
			array(
				'tag'   => 'deal-any-3',
				'qty'   => 3,
				'price' => 7.0,
				'label' => 'Any 3 for $7',
			),
			array(
				'tag'   => 'deal-any-4',
				'qty'   => 4,
				'price' => 5.0,
				'label' => 'Any 4 for $5',
			),
		)
	);
	WP_CLI::log( 'Mix and Match Bundle tag bundles configured.' );
} else {
	WP_CLI::warning( 'Mix and Match Bundle not active — tag bundles skipped. Activate mix-and-match-bundle and re-run seed.' );
}

$products = array(
	// --- Qty package demos (no mix tags) ---
	array(
		'slug'              => 'tomato-sauce-value-pack',
		'name'              => 'Tomato Sauce Value Pack',
		'price'             => '10.00',
		'sku'               => 'FS-SAUCE-VP',
		'cats'              => array( $cat_grocery ),
		'tags'              => array(),
		'short_description' => 'PoC qty packages: 1 for $10, 2 for $15, 5 for $32.',
		'qty_breaks'        => array(
			array( 'qty' => 1, 'price' => 10.00 ),
			array( 'qty' => 2, 'price' => 15.00 ),
			array( 'qty' => 5, 'price' => 32.00 ),
		),
	),
	array(
		'slug'              => 'spc-peach-slices-825g',
		'name'              => 'SPC Peach Slices 825g',
		'price'             => '3.50',
		'sku'               => 'FS-PEACH-825',
		'cats'              => array( $cat_spc ),
		'tags'              => array(),
		'short_description' => 'Qty packages: 1/$3.50, 2/$6, 6/$16.50.',
		'qty_breaks'        => array(
			array( 'qty' => 1, 'price' => 3.50 ),
			array( 'qty' => 2, 'price' => 6.00 ),
			array( 'qty' => 6, 'price' => 16.50 ),
		),
	),
	array(
		'slug'              => 'spc-baked-beans-420g',
		'name'              => 'SPC Baked Beans 420g',
		'price'             => '1.80',
		'sku'               => 'FS-BEANS-420',
		'cats'              => array( $cat_spc, $cat_pantry ),
		'tags'              => array(),
		'short_description' => 'Qty packages: 1/$1.80, 4/$6, 12/$16.',
		'qty_breaks'        => array(
			array( 'qty' => 1, 'price' => 1.80 ),
			array( 'qty' => 4, 'price' => 6.00 ),
			array( 'qty' => 12, 'price' => 16.00 ),
		),
	),
	array(
		'slug'              => 'rice-2kg',
		'name'              => 'Long Grain Rice 2kg',
		'price'             => '4.50',
		'sku'               => 'FS-RICE-2KG',
		'cats'              => array( $cat_pantry ),
		'tags'              => array(),
		'short_description' => 'Qty packages: 1/$4.50, 2/$8.',
		'qty_breaks'        => array(
			array( 'qty' => 1, 'price' => 4.50 ),
			array( 'qty' => 2, 'price' => 8.00 ),
		),
	),
	array(
		'slug'              => 'biscuit-assortment',
		'name'              => 'Biscuit Assortment Tin',
		'price'             => '8.00',
		'sku'               => 'FS-BISC',
		'cats'              => array( $cat_grocery ),
		'tags'              => array(),
		'short_description' => 'Qty packages: 1/$8, 2/$12.',
		'qty_breaks'        => array(
			array( 'qty' => 1, 'price' => 8.00 ),
			array( 'qty' => 2, 'price' => 12.00 ),
		),
	),
	array(
		'slug'              => 'olive-oil-1l',
		'name'              => 'Olive Oil 1L',
		'price'             => '9.50',
		'sku'               => 'FS-OIL-1L',
		'cats'              => array( $cat_grocery, $cat_pantry ),
		'tags'              => array(),
		'short_description' => 'Qty packages: 1/$9.50, 2/$17.',
		'qty_breaks'        => array(
			array( 'qty' => 1, 'price' => 9.50 ),
			array( 'qty' => 2, 'price' => 17.00 ),
		),
	),
	// --- Mix & match Any 3 for $7 (no qty breaks) ---
	array(
		'slug'              => 'spc-fruit-salad-825g',
		'name'              => 'SPC Fruit Salad 825g',
		'price'             => '3.50',
		'sku'               => 'FS-FSALAD-825',
		'cats'              => array( $cat_spc ),
		'tags'              => array( $tag_any3 ),
		'short_description' => 'Mix & match: Any 3 for $7.',
	),
	array(
		'slug'              => 'pasta-1kg',
		'name'              => 'Dried Pasta 1kg',
		'price'             => '2.20',
		'sku'               => 'FS-PASTA-1KG',
		'cats'              => array( $cat_pantry, $cat_grocery ),
		'tags'              => array( $tag_any3 ),
		'short_description' => 'Mix & match: Any 3 for $7.',
	),
	array(
		'slug'              => 'apple-juice-2l',
		'name'              => 'Apple Juice 2L',
		'price'             => '3.00',
		'sku'               => 'FS-AJ-2L',
		'cats'              => array( $cat_drinks ),
		'tags'              => array( $tag_any3 ),
		'short_description' => 'Mix & match: Any 3 for $7.',
	),
	array(
		'slug'              => 'orange-juice-2l',
		'name'              => 'Orange Juice 2L',
		'price'             => '3.00',
		'sku'               => 'FS-OJ-2L',
		'cats'              => array( $cat_drinks ),
		'tags'              => array( $tag_any3 ),
		'short_description' => 'Mix & match: Any 3 for $7.',
	),
	// --- Mix & match Any 4 for $5 (no qty breaks) ---
	array(
		'slug'              => 'spc-spaghetti-420g',
		'name'              => 'SPC Spaghetti 420g',
		'price'             => '1.80',
		'sku'               => 'FS-SPAG-420',
		'cats'              => array( $cat_spc, $cat_pantry ),
		'tags'              => array( $tag_any4 ),
		'short_description' => 'Mix & match: Any 4 for $5.',
	),
	array(
		'slug'              => 'tinned-tomatoes-400g',
		'name'              => 'Tinned Tomatoes 400g',
		'price'             => '1.20',
		'sku'               => 'FS-TOM-400',
		'cats'              => array( $cat_pantry ),
		'tags'              => array( $tag_any4 ),
		'short_description' => 'Mix & match: Any 4 for $5.',
	),
	array(
		'slug'              => 'corn-kernels-420g',
		'name'              => 'Sweet Corn Kernels 420g',
		'price'             => '1.50',
		'sku'               => 'FS-CORN-420',
		'cats'              => array( $cat_pantry ),
		'tags'              => array( $tag_any4 ),
		'short_description' => 'Mix & match: Any 4 for $5.',
	),
	array(
		'slug'              => 'chickpeas-400g',
		'name'              => 'Chickpeas 400g',
		'price'             => '1.40',
		'sku'               => 'FS-CHICK-400',
		'cats'              => array( $cat_pantry ),
		'tags'              => array( $tag_any4 ),
		'short_description' => 'Mix & match: Any 4 for $5.',
	),
);

$product_ids = array();
foreach ( $products as $p ) {
	$id = IndustryDemoHelpers::upsert_product( $p );
	if ( $id > 0 ) {
		$product_ids[ $p['slug'] ] = $id;
		WP_CLI::log( "Product: {$p['name']} (#{$id})" );
	}
}

$shop_url = get_permalink( (int) get_option( 'woocommerce_shop_page_id' ) ) ?: home_url( '/shop/' );

$home = IndustryDemoHelpers::section(
	IndustryDemoHelpers::fullwidth_header(
		'Goulburn Factory Sales',
		'Helping you save on the weekly shop — factory outlet style, Shepparton',
		array(
			'background_color'   => '#78350f',
			'text_orientation'   => 'center',
			'title_font_color'   => '#ffffff',
			'content_font_color' => '#fef3c7',
			'background_image'   => 'https://picsum.photos/seed/factorysales/1600/900',
			'background_blend'   => 'overlay',
			'button_one_text'    => 'Current specials',
			'button_one_url'     => home_url( '/specials/' ),
			'button_two_text'    => 'Shop price list',
			'button_two_url'     => $shop_url,
		)
	),
	array( 'fullwidth' => 'on' )
) . IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'1_3',
			IndustryDemoHelpers::blurb(
				'Package pricing',
				'Buy 1 for $10, buy 2 for $15 — volume breaks on selected lines (Mix and Match Bundle).',
				array( 'use_icon' => 'on', 'font_icon' => '&#xe0f0;', 'icon_color' => '#b45309' )
			)
		) .
		IndustryDemoHelpers::column(
			'1_3',
			IndustryDemoHelpers::blurb(
				'Mix & match',
				'Any 3 for $7 / Any 4 for $5 — linked by product tags.',
				array( 'use_icon' => 'on', 'font_icon' => '&#xe07c;', 'icon_color' => '#b45309' )
			)
		) .
		IndustryDemoHelpers::column(
			'1_3',
			IndustryDemoHelpers::blurb(
				'Changing stock',
				'Like a real factory outlet — range moves weekly. This demo is a sample catalogue.',
				array( 'use_icon' => 'on', 'font_icon' => '&#xe005;', 'icon_color' => '#b45309' )
			)
		),
		array( 'column_structure' => '1_3,1_3,1_3', 'make_equal' => 'on' )
	),
	array( 'background_color' => '#fffbeb' )
) . IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'4_4',
			IndustryDemoHelpers::text( '<h2>Featured specials</h2><p>Try the Tomato Sauce Value Pack (1/$10 · 2/$15) and mix-and-match juice/tinned fruit deals.</p>' ) .
			'[products limit="4" columns="4" orderby="date" order="DESC"]'
		)
	)
) . IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'1_2',
			IndustryDemoHelpers::text(
				'<h2>Opening hours</h2><p>Mon–Fri 9am–6pm<br>Sat–Sun 9am–5pm</p><p>197 Corio Street style location (demo): Shepparton VIC 3630</p>'
			)
		) .
		IndustryDemoHelpers::column(
			'1_2',
			IndustryDemoHelpers::text(
				'<h2>VIP alerts</h2><p>In a live build this would capture emails for weekly specials. For now, browse Specials and Shop.</p>'
			) .
			IndustryDemoHelpers::button( 'Browse specials', home_url( '/specials/' ) )
		),
		array( 'column_structure' => '1_2,1_2' )
	),
	array( 'background_color' => '#1c1917' )
);

$specials = IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'4_4',
			IndustryDemoHelpers::text(
				'<h1>Current specials</h1><p><strong>Package deals:</strong> products with quantity breaks show “Buy X for $Y” on the product card.</p><p><strong>Mix & match:</strong> look for the green deal badge — add any tagged products to unlock Any 3 for $7 / Any 4 for $5 in the cart.</p>'
			) .
			'[products limit="12" columns="3" orderby="popularity"]'
		)
	)
);

$about = IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'4_4',
			IndustryDemoHelpers::text(
				'<h1>About Goulburn Factory Sales</h1><p>Demo B2C outlet inspired by Shepparton factory sales experiences — bargain grocery, tinned fruit, and pantry multi-buys.</p><p>Not affiliated with SPC Factory Sales. Product names are illustrative for WooCommerce demos.</p><p>Pricing tech lives in the <strong>Mix and Match Bundle</strong> plugin (quantity packages + tag bundles).</p>'
			)
		)
	)
);

$contact = IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'4_4',
			IndustryDemoHelpers::text(
				'<h1>Find us</h1><p>Shepparton VIC 3630 (demo address)<br>Phone: (03) 5550 0197<br>Email: specials@factory-sales.demo</p>'
			)
		)
	)
);


$demo_guide = IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'4_4',
			IndustryDemoHelpers::text(
				'<h1>PoC demo guide</h1>
				<h2>1. Quantity packages</h2>
				<ol>
				<li>Open <a href="' . esc_url( home_url( '/product/tomato-sauce-value-pack/' ) ) . '">Tomato Sauce Value Pack</a>.</li>
				<li>Add <strong>1</strong> → cart total <strong>$10</strong>.</li>
				<li>Change qty to <strong>2</strong> → <strong>$15</strong>.</li>
				<li>Qty <strong>3</strong> → <strong>$25</strong> (one “buy 2” + one single).</li>
				<li>Qty <strong>5</strong> → <strong>$32</strong>.</li>
				</ol>
				<h2>2. Mix &amp; match (product tags)</h2>
				<ol>
				<li>Add any 3 of: Apple Juice, Orange Juice, Pasta, Fruit Salad.</li>
				<li>Cart shows fee <strong>Any 3 for $7</strong> and total <strong>$7</strong> for those three.</li>
				<li>Add any 4 of: Spaghetti, Tomatoes, Corn, Chickpeas → <strong>Any 4 for $5</strong>.</li>
				</ol>
				<p>Qty-package products are excluded from mix &amp; match so the two engines do not stack on one SKU.</p>
				<p><a class="et_pb_button" href="' . esc_url( $shop_url ) . '">Go to shop</a></p>'
			)
		)
	)
);

$page_ids = array(
	'home'     => IndustryDemoHelpers::upsert_page( 'home', 'Home', $home ),
	'specials' => IndustryDemoHelpers::upsert_page( 'specials', 'Current Specials', $specials ),
	'about'    => IndustryDemoHelpers::upsert_page( 'about', 'About Us', $about ),
	'contact'  => IndustryDemoHelpers::upsert_page( 'contact', 'Find Us', $contact ),
	'demo-guide' => IndustryDemoHelpers::upsert_page( 'demo-guide', 'PoC Demo Guide', $demo_guide ),
);

IndustryDemoHelpers::set_front_page( $page_ids['home'] );
IndustryDemoHelpers::rebuild_primary_menu(
	'Factory Sales Primary',
	$page_ids,
	array(
		array( 'type' => 'page', 'slug' => 'home', 'title' => 'Home' ),
		array( 'type' => 'page', 'slug' => 'specials', 'title' => 'Specials' ),
		array( 'type' => 'link', 'title' => 'Shop', 'url' => '/shop/' ),
		array( 'type' => 'page', 'slug' => 'about', 'title' => 'About' ),
		array( 'type' => 'page', 'slug' => 'contact', 'title' => 'Find Us' ),
		array( 'type' => 'page', 'slug' => 'demo-guide', 'title' => 'Demo guide' ),
		array( 'type' => 'link', 'title' => 'Cart', 'url' => '/cart/' ),
	)
);

$gateways = get_option( 'woocommerce_cod_settings', array() );
if ( ! is_array( $gateways ) ) {
	$gateways = array();
}
$gateways['enabled'] = 'yes';
$gateways['title']   = 'Pay in store';
update_option( 'woocommerce_cod_settings', $gateways );
update_option( 'woocommerce_enable_guest_checkout', 'yes' );
update_option( 'woocommerce_enable_checkout_login_reminder', 'no' );
update_option( 'woocommerce_ship_to_countries', 'disabled' );

// Remove leftover Sample Page if present.
$sample = get_page_by_path( 'sample-page' );
if ( $sample instanceof WP_Post ) {
	wp_trash_post( $sample->ID );
}

WP_CLI::success( 'Factory sales site seeded. Products: ' . count( $product_ids ) );
