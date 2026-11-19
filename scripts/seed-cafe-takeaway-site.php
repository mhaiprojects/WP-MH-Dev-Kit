<?php
/**
 * Seed cafe-takeaway subsite: Shepparton-style cafe & takeaway demo.
 *
 * @package IndustryDemos
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/lib/IndustryDemoHelpers.php';

IndustryDemoHelpers::assert_host( 'cafe-takeaway.cursor-wpdev2.ddev.site' );
IndustryDemoHelpers::ensure_woocommerce_pages();
IndustryDemoHelpers::apply_divi_colors( '#c2410c', '#1c1917', '#292524' );

$bv = IndustryDemoHelpers::BV;

// Categories.
$cat_coffee   = IndustryDemoHelpers::ensure_product_cat( 'coffee-drinks', 'Coffee & Drinks' );
$cat_food     = IndustryDemoHelpers::ensure_product_cat( 'cafe-food', 'Cafe Food' );
$cat_takeaway = IndustryDemoHelpers::ensure_product_cat( 'takeaway', 'Takeaway Meals' );
$cat_sweet    = IndustryDemoHelpers::ensure_product_cat( 'bakery-sweets', 'Bakery & Sweets' );

$products = array(
	array(
		'slug'              => 'flat-white',
		'name'              => 'Flat White',
		'price'             => '4.50',
		'sku'               => 'CAFE-FW',
		'cats'              => array( $cat_coffee ),
		'short_description' => 'Double shot with silky steamed milk.',
		'description'       => 'Classic Aussie flat white made with locally roasted beans.',
	),
	array(
		'slug'              => 'cappuccino',
		'name'              => 'Cappuccino',
		'price'             => '4.80',
		'sku'               => 'CAFE-CAP',
		'cats'              => array( $cat_coffee ),
		'short_description' => 'Espresso, steamed milk, and foam.',
	),
	array(
		'slug'              => 'long-black',
		'name'              => 'Long Black',
		'price'             => '4.20',
		'sku'               => 'CAFE-LB',
		'cats'              => array( $cat_coffee ),
		'short_description' => 'Double espresso over hot water.',
	),
	array(
		'slug'              => 'chai-latte',
		'name'              => 'Chai Latte',
		'price'             => '5.00',
		'sku'               => 'CAFE-CHAI',
		'cats'              => array( $cat_coffee ),
		'short_description' => 'Spiced chai with steamed milk.',
	),
	array(
		'slug'              => 'smashed-avo',
		'name'              => 'Smashed Avo on Sourdough',
		'price'             => '16.50',
		'sku'               => 'CAFE-AVO',
		'cats'              => array( $cat_food ),
		'short_description' => 'Local avocado, feta, chilli flakes, lemon.',
	),
	array(
		'slug'              => 'big-breakfast',
		'name'              => 'Riverbank Big Breakfast',
		'price'             => '22.00',
		'sku'               => 'CAFE-BB',
		'cats'              => array( $cat_food ),
		'short_description' => 'Eggs, bacon, mushrooms, toast, hash brown.',
	),
	array(
		'slug'              => 'bacon-egg-roll',
		'name'              => 'Bacon & Egg Roll',
		'price'             => '12.50',
		'sku'               => 'CAFE-BER',
		'cats'              => array( $cat_takeaway, $cat_food ),
		'short_description' => 'Soft roll, crispy bacon, fried egg, BBQ sauce.',
	),
	array(
		'slug'              => 'chicken-wrap',
		'name'              => 'Grilled Chicken Wrap',
		'price'             => '14.00',
		'sku'               => 'CAFE-CW',
		'cats'              => array( $cat_takeaway ),
		'short_description' => 'Chicken, salad, aioli — ready for the road.',
	),
	array(
		'slug'              => 'soup-of-day',
		'name'              => 'Soup of the Day + Roll',
		'price'             => '11.50',
		'sku'               => 'CAFE-SOUP',
		'cats'              => array( $cat_takeaway, $cat_food ),
		'short_description' => 'Ask staff for today’s pot — always local produce.',
	),
	array(
		'slug'              => 'banana-bread',
		'name'              => 'Toasted Banana Bread',
		'price'             => '7.50',
		'sku'               => 'CAFE-BBD',
		'cats'              => array( $cat_sweet ),
		'short_description' => 'House-baked, served with butter.',
	),
	array(
		'slug'              => 'lamington',
		'name'              => 'Lamington',
		'price'             => '4.50',
		'sku'               => 'CAFE-LAM',
		'cats'              => array( $cat_sweet ),
		'short_description' => 'Chocolate coconut sponge — takeaway favourite.',
	),
	array(
		'slug'              => 'kids-hot-choc',
		'name'              => 'Kids Hot Chocolate',
		'price'             => '3.50',
		'sku'               => 'CAFE-KHC',
		'cats'              => array( $cat_coffee ),
		'short_description' => 'Warm cocoa with marshmallows.',
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
		'Riverbank Cafe & Takeaway',
		'Coffee, breakfast & quick bites in the heart of Shepparton',
		array(
			'background_color'              => '#44403c',
			'text_orientation'              => 'center',
			'header_fullscreen'             => 'off',
			'content_max_width'             => '900px',
			'title_font_color'              => '#ffffff',
			'content_font_color'            => '#f5f5f4',
			'background_image'              => 'https://picsum.photos/seed/riverbankcafe/1600/900',
			'background_blend'              => 'overlay',
			'button_one_text'               => 'View menu',
			'button_one_url'                => home_url( '/menu/' ),
			'button_two_text'               => 'Order takeaway',
			'button_two_url'                => $shop_url,
		)
	),
	array( 'fullwidth' => 'on' )
) . IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'1_3',
			IndustryDemoHelpers::blurb(
				'Local roast',
				'Beans roasted nearby — flat whites the Goulburn Valley way.',
				array( 'use_icon' => 'on', 'font_icon' => '&#xe0eb;', 'icon_color' => '#c2410c' )
			)
		) .
		IndustryDemoHelpers::column(
			'1_3',
			IndustryDemoHelpers::blurb(
				'Fast takeaway',
				'Order ahead for school runs, tradies, and lunch on the go.',
				array( 'use_icon' => 'on', 'font_icon' => '&#xe016;', 'icon_color' => '#c2410c' )
			)
		) .
		IndustryDemoHelpers::column(
			'1_3',
			IndustryDemoHelpers::blurb(
				'All-day breakfast',
				'From smashed avo to the big breakfast until 2pm.',
				array( 'use_icon' => 'on', 'font_icon' => '&#xe014;', 'icon_color' => '#c2410c' )
			)
		),
		array( 'column_structure' => '1_3,1_3,1_3', 'make_equal' => 'on' )
	),
	array( 'background_color' => '#fafaf9' )
) . IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'4_4',
			IndustryDemoHelpers::text(
				'<h2>Today’s favourites</h2><p>Order online for pickup — or browse the full cafe menu.</p>'
			) .
			'[products limit="4" columns="4" orderby="menu_order" order="ASC"]' .
			IndustryDemoHelpers::button( 'Full menu', home_url( '/menu/' ), array( 'button_alignment' => 'center', 'custom_margin' => '20px||||false|false' ) )
		)
	)
) . IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'1_2',
			IndustryDemoHelpers::text(
				'<h2>Visit us</h2><p><strong>48 Wyndham Street, Shepparton VIC 3630</strong></p><p>Mon–Fri 6:30am–3:00pm<br>Sat–Sun 7:30am–2:00pm</p><p>Phone: (03) 5550 0148</p>'
			)
		) .
		IndustryDemoHelpers::column(
			'1_2',
			IndustryDemoHelpers::text(
				'<h2>Takeaway tips</h2><p>Call ahead for large coffee runs. Gluten-free bread available on request. Kids’ cups half price with any meal.</p>'
			)
		),
		array( 'column_structure' => '1_2,1_2' )
	),
	array( 'background_color' => '#292524' )
);

$menu_page = IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'4_4',
			IndustryDemoHelpers::text( '<h1>Cafe menu</h1><p>Coffee, breakfast, takeaway meals, and sweets — priced for everyday Shepparton locals.</p>' )
		)
	)
) . IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'4_4',
			IndustryDemoHelpers::text( '<h2>Coffee & drinks</h2>' ) .
			'[product_category category="coffee-drinks" columns="3" limit="12"]'
		)
	)
) . IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'4_4',
			IndustryDemoHelpers::text( '<h2>Cafe food</h2>' ) .
			'[product_category category="cafe-food" columns="3" limit="12"]'
		)
	),
	array( 'background_color' => '#fafaf9' )
) . IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'4_4',
			IndustryDemoHelpers::text( '<h2>Takeaway meals</h2>' ) .
			'[product_category category="takeaway" columns="3" limit="12"]'
		)
	)
) . IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'4_4',
			IndustryDemoHelpers::text( '<h2>Bakery & sweets</h2>' ) .
			'[product_category category="bakery-sweets" columns="3" limit="12"]'
		)
	),
	array( 'background_color' => '#fafaf9' )
);

$about = IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'2_3',
			IndustryDemoHelpers::text(
				'<h1>About Riverbank</h1><p>We are a neighbourhood cafe inspired by Shepparton favourites — strong coffee, honest breakfasts, and takeaway that actually travels well.</p><p>This is a <strong>demo site</strong> for layout and WooCommerce menu ordering. Opening hours and address are fictional.</p>'
			)
		) .
		IndustryDemoHelpers::column(
			'1_3',
			IndustryDemoHelpers::blurb(
				'Pickup ready',
				'Demo checkout uses cash-on-pickup style flow for local testing.',
				array( 'use_icon' => 'on', 'font_icon' => '&#xe0e5;', 'icon_color' => '#c2410c' )
			)
		),
		array( 'column_structure' => '2_3,1_3' )
	)
);

$contact = IndustryDemoHelpers::section(
	IndustryDemoHelpers::row(
		IndustryDemoHelpers::column(
			'4_4',
			IndustryDemoHelpers::text(
				'<h1>Contact</h1><p>Email: hello@riverbank-cafe.demo<br>Phone: (03) 5550 0148<br>48 Wyndham Street, Shepparton VIC 3630</p><p>[et_pb_contact_form captcha="off" email="admin@example.com" title="Send a message"][/et_pb_contact_form]</p>'
			)
		)
	)
);

$page_ids = array(
	'home'    => IndustryDemoHelpers::upsert_page( 'home', 'Home', $home, 'Riverbank Cafe & Takeaway home' ),
	'menu'    => IndustryDemoHelpers::upsert_page( 'menu', 'Menu', $menu_page, 'Full cafe menu' ),
	'about'   => IndustryDemoHelpers::upsert_page( 'about', 'About', $about ),
	'contact' => IndustryDemoHelpers::upsert_page( 'contact', 'Contact', $contact ),
);

IndustryDemoHelpers::set_front_page( $page_ids['home'] );

IndustryDemoHelpers::rebuild_primary_menu(
	'Cafe Primary',
	$page_ids,
	array(
		array( 'type' => 'page', 'slug' => 'home', 'title' => 'Home' ),
		array( 'type' => 'page', 'slug' => 'menu', 'title' => 'Menu' ),
		array( 'type' => 'link', 'title' => 'Shop', 'url' => '/shop/' ),
		array( 'type' => 'page', 'slug' => 'about', 'title' => 'About' ),
		array( 'type' => 'page', 'slug' => 'contact', 'title' => 'Contact' ),
		array( 'type' => 'link', 'title' => 'Cart', 'url' => '/cart/' ),
	)
);

// COD for pickup-friendly demo.
$gateways = get_option( 'woocommerce_cod_settings', array() );
if ( ! is_array( $gateways ) ) {
	$gateways = array();
}
$gateways['enabled'] = 'yes';
$gateways['title']   = 'Pay on pickup';
update_option( 'woocommerce_cod_settings', $gateways );

WP_CLI::success( 'Cafe takeaway site seeded. Products: ' . count( $product_ids ) );
