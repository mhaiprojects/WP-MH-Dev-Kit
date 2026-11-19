<?php
/**
 * Seed hospitality-supplies subsite: B2B hospitality catalogue (In Hospitality inspired).
 *
 * @package IndustryDemos
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/lib/IndustryDemoHelpers.php';

IndustryDemoHelpers::assert_host( 'hospitality-supplies.cursor-wpdev2.ddev.site' );
IndustryDemoHelpers::ensure_woocommerce_pages();
IndustryDemoHelpers::apply_divi_colors( '#0369a1', '#0c4a6e', '#0c4a6e' );

// Catalogue categories mirrored from inhospitality.com.au product range.
$categories = array(
	'catering-equipment'   => 'Catering Equipment',
	'chemicals'            => 'Chemicals',
	'cleaning-equipment'   => 'Cleaning Equipment',
	'coffee-tea'           => 'Coffee & Tea',
	'crockery'             => 'Crockery',
	'cutlery'              => 'Cutlery',
	'glassware'            => 'Glassware',
	'kitchenware'          => 'Kitchenware & Cookware',
	'packaging-bags'       => 'Packaging & Bags',
	'shelving-storage'     => 'Shelving & Storage',
	'uniforms'             => 'Uniforms',
	'thermometers-scales'  => 'Thermometers & Scales',
);

$cat_ids = array();
foreach ( $categories as $slug => $name ) {
	$cat_ids[ $slug ] = IndustryDemoHelpers::ensure_product_cat( $slug, $name );
}

$products = array(
	array(
		'slug'              => 'bain-marie-wet-well',
		'name'              => 'Wet Well Bain Marie 4×1/1 GN',
		'price'             => '1890.00',
		'sku'               => 'HS-BM-4',
		'cats'              => array( $cat_ids['catering-equipment'] ),
		'short_description' => 'Stainless wet well for buffet lines.',
	),
	array(
		'slug'              => 'commercial-dishwasher-hood',
		'name'              => 'Hood-Type Dishwasher',
		'price'             => '4250.00',
		'sku'               => 'HS-DW-HOOD',
		'cats'              => array( $cat_ids['catering-equipment'], $cat_ids['cleaning-equipment'] ),
		'short_description' => 'High-throughput dishwasher for busy kitchens.',
	),
	array(
		'slug'              => 'degreaser-5l',
		'name'              => 'Kitchen Degreaser 5L',
		'price'             => '28.50',
		'sku'               => 'HS-DEG-5L',
		'cats'              => array( $cat_ids['chemicals'] ),
		'short_description' => 'Heavy-duty commercial degreaser.',
		'qty_breaks'        => array(
			array( 'qty' => 1, 'price' => 28.50 ),
			array( 'qty' => 4, 'price' => 100.00 ),
		),
	),
	array(
		'slug'              => 'sanitiser-5l',
		'name'              => 'Food-Safe Sanitiser 5L',
		'price'             => '24.00',
		'sku'               => 'HS-SAN-5L',
		'cats'              => array( $cat_ids['chemicals'] ),
		'short_description' => 'No-rinse sanitiser for food contact surfaces.',
	),
	array(
		'slug'              => 'mop-bucket-combo',
		'name'              => 'Mop & Wringer Combo',
		'price'             => '89.00',
		'sku'               => 'HS-MOP',
		'cats'              => array( $cat_ids['cleaning-equipment'] ),
		'short_description' => 'Commercial mop kit for BOH floors.',
	),
	array(
		'slug'              => 'espresso-beans-1kg',
		'name'              => 'House Espresso Beans 1kg',
		'price'             => '32.00',
		'sku'               => 'HS-BEANS-1KG',
		'cats'              => array( $cat_ids['coffee-tea'] ),
		'short_description' => 'Blend suited to cafe machines.',
		'qty_breaks'        => array(
			array( 'qty' => 1, 'price' => 32.00 ),
			array( 'qty' => 5, 'price' => 145.00 ),
		),
	),
	array(
		'slug'              => 'tea-bags-catering',
		'name'              => 'English Breakfast Tea Bags (500)',
		'price'             => '18.50',
		'sku'               => 'HS-TEA-500',
		'cats'              => array( $cat_ids['coffee-tea'] ),
		'short_description' => 'Catering pack for roomservice & buffets.',
	),
	array(
		'slug'              => 'dinner-plate-white-27cm',
		'name'              => 'White Dinner Plate 27cm (Box 12)',
		'price'             => '64.00',
		'sku'               => 'HS-PLATE-27',
		'cats'              => array( $cat_ids['crockery'] ),
		'short_description' => 'Vitrified hotelware dinner plate.',
	),
	array(
		'slug'              => 'bowl-soup-white',
		'name'              => 'Soup Bowl White (Box 12)',
		'price'             => '48.00',
		'sku'               => 'HS-BOWL',
		'cats'              => array( $cat_ids['crockery'] ),
		'short_description' => 'Stackable soup bowls for a la carte.',
	),
	array(
		'slug'              => 'cutlery-set-18-10',
		'name'              => '18/10 Cutlery Set (Dozen forks)',
		'price'             => '42.00',
		'sku'               => 'HS-CUT-FORK',
		'cats'              => array( $cat_ids['cutlery'] ),
		'short_description' => 'Stainless table forks — dozen.',
	),
	array(
		'slug'              => 'wine-glass-450ml',
		'name'              => 'Wine Glass 450ml (Box 6)',
		'price'             => '36.00',
		'sku'               => 'HS-WG-450',
		'cats'              => array( $cat_ids['glassware'] ),
		'short_description' => 'Restaurant wine glass — tempered stem.',
	),
	array(
		'slug'              => 'tumbler-330ml',
		'name'              => 'Stackable Tumbler 330ml (Box 12)',
		'price'             => '28.00',
		'sku'               => 'HS-TUM-330',
		'cats'              => array( $cat_ids['glassware'] ),
		'short_description' => 'Durable water/soft-drink tumblers.',
	),
	array(
		'slug'              => 'sauce-pan-set',
		'name'              => 'Aluminium Saucepan Set (3pc)',
		'price'             => '125.00',
		'sku'               => 'HS-PAN-3',
		'cats'              => array( $cat_ids['kitchenware'] ),
		'short_description' => 'Commercial saucepans with lids.',
	),
	array(
		'slug'              => 'chef-knife-25cm',
		'name'              => 'Chef Knife 25cm',
		'price'             => '79.00',
		'sku'               => 'HS-KNIFE-25',
		'cats'              => array( $cat_ids['kitchenware'] ),
		'short_description' => 'High-carbon stainless chef knife.',
	),
	array(
		'slug'              => 'takeaway-containers-500',
		'name'              => 'Takeaway Containers 500pc',
		'price'             => '55.00',
		'sku'               => 'HS-TA-500',
		'cats'              => array( $cat_ids['packaging-bags'] ),
		'short_description' => 'Hinged containers for hot meals.',
	),
	array(
		'slug'              => 'paper-bags-kraft',
		'name'              => 'Kraft Paper Bags (Pack 250)',
		'price'             => '22.00',
		'sku'               => 'HS-BAG-250',
		'cats'              => array( $cat_ids['packaging-bags'] ),
		'short_description' => 'Branded-ready kraft bags.',
	),
	array(
		'slug'              => 'wire-shelving-unit',
		'name'              => 'Chrome Wire Shelving Unit 4-Tier',
		'price'             => '245.00',
		'sku'               => 'HS-SHELF-4',
		'cats'              => array( $cat_ids['shelving-storage'] ),
		'short_description' => 'Adjustable dry-store shelving.',
	),
	array(
		'slug'              => 'chef-jacket-white',
		'name'              => 'Chef Jacket White (Unisex)',
		'price'             => '45.00',
		'sku'               => 'HS-JACKET',
		'cats'              => array( $cat_ids['uniforms'] ),
		'short_description' => 'Double-breasted chef jacket.',
	),
	array(
		'slug'              => 'apron-black',
		'name'              => 'Black Bib Apron',
		'price'             => '18.00',
		'sku'               => 'HS-APRON',
		'cats'              => array( $cat_ids['uniforms'] ),
		'short_description' => 'Poly-cotton FOH/BOH apron.',
	),
	array(
		'slug'              => 'probe-thermometer',
		'name'              => 'Digital Probe Thermometer',
		'price'             => '35.00',
		'sku'               => 'HS-THERM',
		'cats'              => array( $cat_ids['thermometers-scales'] ),
		'short_description' => 'HACCP-friendly instant-read probe.',
	),
	array(
		'slug'              => 'portion-scale-5kg',
		'name'              => 'Portion Scale 5kg',
		'price'             => '95.00',
		'sku'               => 'HS-SCALE-5',
		'cats'              => array( $cat_ids['thermometers-scales'] ),
		'short_description' => 'Stainless portion control scale.',
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

$cat_blurbs = '';
$cols       = array_chunk( $categories, 3, true );
foreach ( array_slice( $categories, 0, 6 ) as $slug => $name ) {
	$cat_blurbs .= IndustryDemoHelpers::column(
		'1_3',
		IndustryDemoHelpers::blurb(
			$name,
			'Browse ' . $name . ' for venues across the Goulburn Valley.',
			array(
				'url'      => get_term_link( (int) $cat_ids[ $slug ], 'product_cat' ) ?: $shop_url,
				'use_icon' => 'on',
				'font_icon'=> '&#xe090;',
				'icon_color'=> '#0369a1',
			)
		)
	);
}

$home = IndustryDemoHelpers::section(
	IndustryDemoHelpers::fullwidth_header(
		'GV Hospitality Supplies',
		'Professional catering goods for venues across the Goulburn Valley',
		array(
			'background_color'   => '#0c4a6e',
			'text_orientation'   => 'center',
			'title_font_color'   => '#ffffff',
			'content_font_color' => '#e0f2fe',
			'background_image'   => 'https://picsum.photos/seed/hospitalsupplies/1600/900',
			'background_blend'   => 'overlay',
			'button_one_text'    => 'Product catalogue',
			'button_one_url'     => home_url( '/catalogue/' ),
			'button_two_text'    => 'Request a quote',
			'button_two_url'     => home_url( '/quote/' ),
		)
	),
	array( 'fullwidth' => 'on' )
) . IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'4_4',
			IndustryDemoHelpers::text( '<h2>Our product range</h2><p>Wholesale and retail hospitality supplies — catalogue inspired by regional B2B suppliers (not a design clone).</p>' )
		)
	) .
	IndustryDemoHelpers::row(
		$cat_blurbs,
		array( 'column_structure' => '1_3,1_3,1_3', 'make_equal' => 'on' )
	),
	array( 'background_color' => '#f0f9ff' )
) . IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'4_4',
			IndustryDemoHelpers::text( '<h2>Featured products</h2>' ) .
			'[products limit="4" columns="4" orderby="menu_order"]' .
			IndustryDemoHelpers::button( 'Full catalogue', home_url( '/catalogue/' ), array( 'button_alignment' => 'center', 'custom_margin' => '24px||||false|false' ) )
		)
	)
) . IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'1_2',
			IndustryDemoHelpers::text(
				'<h2>About us</h2><p>GV Hospitality Supplies is a demo B2B catalogue for hotels, cafes, clubs, and caterers — crockery through chemicals, shelving to uniforms.</p>'
			)
		) .
		IndustryDemoHelpers::column(
			'1_2',
			IndustryDemoHelpers::text(
				'<h2>Find us</h2><p>76 Stewart St style location (demo)<br>Shepparton VIC 3630<br>T: (03) 5550 4000<br>E: quotes@gv-hospitality.demo</p>'
			)
		),
		array( 'column_structure' => '1_2,1_2' )
	),
	array( 'background_color' => '#0c4a6e' )
);

$catalogue_sections = IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'4_4',
			IndustryDemoHelpers::text( '<h1>Product catalogue</h1><p>Browse by category. Add items to cart for a trade-style order, or use the quote page for larger lists.</p>' )
		)
	)
);

$i = 0;
foreach ( $categories as $slug => $name ) {
	$bg = ( 0 === $i % 2 ) ? array() : array( 'background_color' => '#f8fafc' );
	$catalogue_sections .= IndustryDemoHelpers::section(
		IndustryDemoHelpers::row(
			IndustryDemoHelpers::column(
				'4_4',
				IndustryDemoHelpers::text( '<h2>' . esc_html( $name ) . '</h2>' ) .
				'[product_category category="' . esc_attr( $slug ) . '" columns="3" limit="6"]'
			)
		),
		$bg
	);
	++$i;
}

$quote = IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'4_4',
			IndustryDemoHelpers::text(
				'<h1>Request a quote</h1><p>For B2B pricing on large orders, send your product list. (Demo form — emails go to the site admin.)</p><p>Tip: add items to the WooCommerce cart first, then note SKUs in your message.</p>'
			) .
			'[et_pb_contact_form captcha="off" email="admin@example.com" title="Quote request"][/et_pb_contact_form]'
		)
	)
);

$about = IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'4_4',
			IndustryDemoHelpers::text(
				'<h1>About GV Hospitality Supplies</h1><p>Demo site modelled on regional hospitality wholesalers such as In Hospitality (Shepparton) — focusing on <strong>catalogue breadth</strong>, not visual design.</p><p>Categories include catering equipment, chemicals, cleaning, coffee & tea, crockery, cutlery, glassware, kitchenware, packaging, shelving, uniforms, and thermometers & scales.</p>'
			)
		)
	)
);

$contact = IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'4_4',
			IndustryDemoHelpers::text(
				'<h1>Contact</h1><p>Shepparton VIC 3630<br>Phone: (03) 5550 4000<br>Email: quotes@gv-hospitality.demo</p>'
			)
		)
	)
);

$page_ids = array(
	'home'      => IndustryDemoHelpers::upsert_page( 'home', 'Home', $home ),
	'catalogue' => IndustryDemoHelpers::upsert_page( 'catalogue', 'Our Products', $catalogue_sections ),
	'quote'     => IndustryDemoHelpers::upsert_page( 'quote', 'Request a Quote', $quote ),
	'about'     => IndustryDemoHelpers::upsert_page( 'about', 'About Us', $about ),
	'contact'   => IndustryDemoHelpers::upsert_page( 'contact', 'Contact Us', $contact ),
);

IndustryDemoHelpers::set_front_page( $page_ids['home'] );
IndustryDemoHelpers::rebuild_primary_menu(
	'Hospitality Primary',
	$page_ids,
	array(
		array( 'type' => 'page', 'slug' => 'home', 'title' => 'Home' ),
		array( 'type' => 'page', 'slug' => 'catalogue', 'title' => 'Products' ),
		array( 'type' => 'link', 'title' => 'Shop', 'url' => '/shop/' ),
		array( 'type' => 'page', 'slug' => 'quote', 'title' => 'Quote cart' ),
		array( 'type' => 'page', 'slug' => 'about', 'title' => 'About' ),
		array( 'type' => 'page', 'slug' => 'contact', 'title' => 'Contact' ),
	)
);

// Enable COD as “Account / invoice placeholder” for B2B demo.
$gateways = get_option( 'woocommerce_cod_settings', array() );
if ( ! is_array( $gateways ) ) {
	$gateways = array();
}
$gateways['enabled']     = 'yes';
$gateways['title']       = 'Account / arrange payment';
$gateways['description'] = 'Demo B2B checkout — arrange trade account payment offline.';
update_option( 'woocommerce_cod_settings', $gateways );

WP_CLI::success( 'Hospitality supplies site seeded. Products: ' . count( $product_ids ) . ', categories: ' . count( $cat_ids ) );
