<?php
/**
 * Divi Builder shortcode helpers for adamat site seeding.
 *
 * Uses Divi 4 shortcode attributes documented in:
 * - includes/builder/class-et-builder-element.php (animation + scroll effects)
 * - includes/builder/module/field/Scroll.php (scroll transform effects)
 *
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

final class AdamatDiviLayouts {

	private const BV = '4.27.7';

	/** Brand palette */
	private const NAVY   = '#0f2744';

	private const BLUE   = '#1e6fd9';

	private const CYAN   = '#0ea5e9';

	private const TEAL   = '#14b8a6';

	private const SLATE  = '#64748b';

	private const INK    = '#1e293b';

	private const LIGHT  = '#f1f5f9';

	private const MIST   = '#e2e8f0';

	private const WHITE  = '#ffffff';

	private static function media( string $key ): string {
		if ( ! class_exists( 'AdamatMediaAssets' ) ) {
			return '';
		}

		return AdamatMediaAssets::url( $key );
	}

	public static function apply_builder_meta( int $post_id ): void {
		update_post_meta( $post_id, '_et_pb_use_builder', 'on' );
		update_post_meta( $post_id, '_et_pb_page_layout', 'et_full_width_page' );
		update_post_meta( $post_id, '_et_pb_built_for_post_type', 'page' );
		update_post_meta( $post_id, '_et_pb_ab_current_shortcode', 'off' );
		update_post_meta( $post_id, '_et_pb_enable_shortcode_tracking', '' );
	}

	public static function apply_theme_options(): void {
		if ( ! function_exists( 'et_update_option' ) ) {
			return;
		}

		et_update_option( 'accent_color', self::BLUE );
		et_update_option( 'secondary_accent_color', self::CYAN );
		et_update_option( 'header_color', self::NAVY );
		et_update_option( 'font_color', self::SLATE );
		et_update_option( 'link_color', self::BLUE );
		et_update_option( 'primary_nav_bg', self::NAVY );
		et_update_option( 'fixed_primary_nav_bg', self::NAVY );
		et_update_option( 'mobile_primary_nav_bg', self::NAVY );
		et_update_option( 'menu_link', self::WHITE );
		et_update_option( 'menu_link_active', self::CYAN );
		et_update_option( 'divi_fixed_nav', 'on' );
		et_update_option( 'divi_gallery_layout_enable', 'off' );
		et_update_option( 'body_font_size', '16' );
		et_update_option( 'body_font_height', '1.7' );

		// Footer styling aligned with adamat brand.
		et_update_option( 'footer_columns', '4' );
		et_update_option( 'footer_bg', self::NAVY );
		et_update_option( 'footer_widget_header_color', self::CYAN );
		et_update_option( 'footer_widget_text_color', '#cbd5e1' );
		et_update_option( 'footer_widget_link_color', self::WHITE );
		et_update_option( 'footer_widget_bullet_color', self::BLUE );
		et_update_option( 'show_footer_social_icons', false );
		et_delete_option( 'disable_custom_footer_credits' );
		et_update_option(
			'custom_footer_credits',
			'&copy; ' . gmdate( 'Y' ) . ' adamat. eCommerce, AI &amp; product data services · Australia.'
		);

		self::apply_card_styles();
		self::flush_divi_static_css();
	}

	/**
	 * Uniform card image sizing and equal-height card rows (Divi equal columns + flex fill).
	 */
	private static function apply_card_styles(): void {
		if ( ! function_exists( 'wp_get_custom_css' ) || ! function_exists( 'wp_update_custom_css_post' ) ) {
			return;
		}

		$marker = '/* adamat-card-styles */';
		$css    = self::card_styles_css();
		$existing = wp_get_custom_css();

		if ( str_contains( $existing, $marker ) ) {
			$existing = preg_replace(
				'/' . preg_quote( $marker, '/' ) . '[\s\S]*?' . preg_quote( $marker, '/' ) . '-end/u',
				'',
				$existing
			);
			$existing = trim( (string) $existing );
		}

		$combined = trim( $existing . "\n\n" . $css );

		wp_update_custom_css_post( $combined );
	}

	private static function card_styles_css(): string {
		return <<<'CSS'
/* adamat-card-styles */
.et_pb_row.et_pb_equal_columns > .et_pb_column {
	display: flex;
	flex-direction: column;
}

.et_pb_row.et_pb_equal_columns > .et_pb_column > .et_pb_module {
	flex: 1 1 auto;
	width: 100%;
}

.et_pb_blurb.adamat-card {
	display: flex;
	flex-direction: column;
	height: 100%;
	overflow: hidden;
}

.et_pb_blurb.adamat-card .et_pb_main_blurb_image {
	display: block;
	width: calc(100% + 56px);
	max-width: none;
	margin: -28px -28px 24px;
}

.et_pb_blurb.adamat-card .et_pb_image_wrap {
	display: block;
	width: 100%;
	aspect-ratio: 16 / 9;
	overflow: hidden;
}

.et_pb_blurb.adamat-card .et_pb_image_wrap img {
	display: block;
	width: 100%;
	height: 100%;
	object-fit: cover;
	object-position: center;
}

.et_pb_blurb.adamat-card .et_pb_blurb_content {
	flex: 1 1 auto;
	display: flex;
	flex-direction: column;
}

.et_pb_blurb.adamat-card .et_pb_blurb_container {
	flex: 1 1 auto;
}
/* adamat-card-styles-end */
CSS;
	}

	/** Row attrs for equal-height card grids. */
	private static function card_row_attrs(): array {
		return array(
			'make_equal' => 'on',
		);
	}

	private static function flush_divi_static_css(): void {
		et_update_option( 'et_pb_clear_templates_cache', true );

		if ( class_exists( 'ET_Core_PageResource' ) && is_callable( array( 'ET_Core_PageResource', 'remove_static_resources' ) ) ) {
			ET_Core_PageResource::remove_static_resources( 'all', 'all' );
		}

		$blog_id = get_current_blog_id();
		$paths   = array(
			WP_CONTENT_DIR . '/et-cache/home',
			WP_CONTENT_DIR . '/et-cache/global/et-divi-customizer-global.min.css',
			WP_CONTENT_DIR . '/et-cache/1/' . $blog_id,
		);

		foreach ( $paths as $path ) {
			if ( is_file( $path ) ) {
				wp_delete_file( $path );
				continue;
			}
			if ( is_dir( $path ) ) {
				self::delete_directory( $path );
			}
		}
	}

	private static function delete_directory( string $dir ): void {
		$items = scandir( $dir );
		if ( ! is_array( $items ) ) {
			return;
		}

		foreach ( $items as $item ) {
			if ( '.' === $item || '..' === $item ) {
				continue;
			}
			$path = $dir . DIRECTORY_SEPARATOR . $item;
			if ( is_dir( $path ) ) {
				self::delete_directory( $path );
			} else {
				wp_delete_file( $path );
			}
		}
	}

	public static function apply_footer_widgets(): void {
		$footer_sidebars = array( 'sidebar-2', 'sidebar-3', 'sidebar-4', 'sidebar-5' );
		$sidebars        = wp_get_sidebars_widgets();
		$block_widgets   = get_option( 'widget_block', array() );

		if ( ! is_array( $sidebars ) ) {
			$sidebars = array();
		}
		if ( ! is_array( $block_widgets ) ) {
			$block_widgets = array();
		}

		$remove_ids = array();
		foreach ( array_merge( $footer_sidebars, array( 'sidebar-1' ) ) as $sidebar_id ) {
			if ( empty( $sidebars[ $sidebar_id ] ) || ! is_array( $sidebars[ $sidebar_id ] ) ) {
				$sidebars[ $sidebar_id ] = array();
				continue;
			}
			foreach ( $sidebars[ $sidebar_id ] as $widget_id ) {
				if ( is_string( $widget_id ) && str_starts_with( $widget_id, 'block-' ) ) {
					$remove_ids[] = $widget_id;
				}
			}
			$sidebars[ $sidebar_id ] = array();
		}

		foreach ( array_unique( $remove_ids ) as $widget_id ) {
			$num = (int) str_replace( 'block-', '', $widget_id );
			unset( $block_widgets[ $num ] );
		}

		$numbers = array_filter(
			array_keys( $block_widgets ),
			static fn( $key ): bool => is_int( $key ) || ( is_string( $key ) && ctype_digit( $key ) )
		);
		$next    = $numbers ? ( (int) max( array_map( 'intval', $numbers ) ) + 1 ) : 1;

		$columns = array(
			array(
				'sidebar' => 'sidebar-2',
				'title'   => 'adamat',
				'html'    => '<p style="color:#cbd5e1;line-height:1.75;margin:0 0 14px;">We work closely with clients across Australia and worldwide — helping businesses handle their specific needs and move confidently into the <strong>digital AI age</strong>.</p>
					<p style="margin:0;"><a href="/contact/" style="color:' . self::CYAN . ';font-weight:600;text-decoration:none;">Start a project →</a></p>',
			),
			array(
				'sidebar' => 'sidebar-3',
				'title'   => 'eCommerce & AI Services',
				'html'    => self::footer_link_list(
					array(
						array( 'Web Design', '/services/#web-design' ),
						array( 'eCommerce', '/services/#ecommerce' ),
						array( 'AI Services', '/services/#ai-services' ),
						array( 'Product Data', '/services/#product-data-management' ),
						array( 'Domains & Hosting', '/services/#domains-hosting' ),
						array( 'Graphic Design', '/services/#graphic-design' ),
					)
				),
			),
			array(
				'sidebar' => 'sidebar-4',
				'title'   => 'Company',
				'html'    => self::footer_link_list(
					array(
						array( 'Home', '/' ),
						array( 'About Us', '/about-us/' ),
						array( 'Services', '/services/' ),
						array( 'Projects Showcase', '/projects-showcase/' ),
						array( 'Contact', '/contact/' ),
					)
				),
			),
			array(
				'sidebar' => 'sidebar-5',
				'title'   => 'Get in Touch',
				'html'    => '<p style="color:#cbd5e1;line-height:1.9;margin:0 0 12px;">
					<strong style="color:#fff;">Email</strong><br />
					<a href="mailto:hello@adamat.online" style="color:#fff;text-decoration:none;">hello@adamat.online</a></p>
					<p style="color:#cbd5e1;line-height:1.9;margin:0 0 12px;">
					<strong style="color:#fff;">Location</strong><br />
					Australia</p>
					<p style="color:#cbd5e1;line-height:1.9;margin:0;">
					<strong style="color:#fff;">We help with</strong><br />
					Web · eCommerce · AI · Product data · Hosting · Brand design</p>',
			),
		);

		foreach ( $columns as $column ) {
			$block_widgets[ $next ] = array(
				'content' => self::footer_widget_block( $column['title'], $column['html'] ),
			);
			$sidebars[ $column['sidebar'] ][] = 'block-' . $next;
			++$next;
		}

		$block_widgets['_multiwidget'] = 1;
		update_option( 'widget_block', $block_widgets );
		wp_set_sidebars_widgets( $sidebars );
	}

	private static function footer_widget_block( string $title, string $inner_html ): string {
		return '<!-- wp:heading {"level":4} -->'
			. '<h4 class="wp-block-heading">' . esc_html( $title ) . '</h4>'
			. '<!-- /wp:heading -->'
			. '<!-- wp:html -->'
			. $inner_html
			. '<!-- /wp:html -->';
	}

	/**
	 * @param array<int, array{0:string,1:string}> $links Label and URL pairs.
	 */
	private static function footer_link_list( array $links ): string {
		$items = '';
		foreach ( $links as $link ) {
			$items .= '<li><a href="' . esc_url( $link[1] ) . '">' . esc_html( $link[0] ) . '</a></li>';
		}

		return '<ul>' . $items . '</ul>';
	}

	/**
	 * @return array<string, array{title:string,headline:string,excerpt:string,intro:string,deliverables:array<int,string>,icon:string,color:string,image_key:string}>
	 */
	public static function services(): array {
		return array(
			'web-design'              => array(
				'title'        => 'Web Design',
				'headline'     => 'Websites that reflect your brand and drive action',
				'excerpt'      => 'Modern, mobile-first sites shaped around your customers and goals.',
				'intro'        => 'We design and build websites that tell your story clearly and guide visitors toward action. Every layout is shaped around your audience, your offer, and the outcomes you need — developed in close partnership with your team.',
				'deliverables' => array(
					'Discovery workshops aligned to your business goals',
					'UX-led page structure and content hierarchy',
					'Responsive design and development',
					'Performance and accessibility best practices',
					'Launch support and ongoing refinement',
				),
				'icon'         => '&#xe0e7;||divi',
				'color'        => self::BLUE,
				'image_key'    => 'service-web-design',
			),
			'ecommerce'               => array(
				'title'        => 'eCommerce',
				'headline'     => 'Online stores built for trust and conversion',
				'excerpt'      => 'Digital commerce experiences tailored to how your customers buy.',
				'intro'        => 'We help you sell online with stores that feel credible, simple to use, and ready to grow. From product presentation to checkout, we focus on the details that reduce friction and support your sales process.',
				'deliverables' => array(
					'Store strategy, catalog structure, and customer journeys',
					'Product pages and checkout flows optimised for conversion',
					'Payments, shipping, and fulfilment configuration',
					'Promotions, subscriptions, and custom ordering where needed',
					'Integrations with your existing business systems',
				),
				'icon'         => '&#xe015;||divi',
				'color'        => self::TEAL,
				'image_key'    => 'service-ecommerce',
			),
			'ai-services'             => array(
				'title'        => 'AI Services',
				'headline'     => 'Practical AI that supports your team and customers',
				'excerpt'      => 'Automation, smarter content, and AI-ready digital foundations.',
				'intro'        => 'We help you adopt AI where it genuinely helps — reducing repetitive work, improving discovery, and preparing your digital presence for the AI age. Human oversight and your brand voice stay central throughout.',
				'deliverables' => array(
					'AI-assisted content, product copy, and knowledge bases',
					'Workflow automation for everyday business tasks',
					'Structured information for search and AI discovery',
					'Assistants and tools connected to your content',
					'Guidelines for responsible, quality-controlled AI use',
				),
				'icon'         => '&#xe0bc;||divi',
				'color'        => self::CYAN,
				'image_key'    => 'service-ai-services',
			),
			'product-data-management' => array(
				'title'        => 'Product Data Management',
				'headline'     => 'Accurate product information across every channel',
				'excerpt'      => 'Clean, enriched catalog data for web, marketplaces, and partners.',
				'intro'        => 'Inconsistent product data slows sales and creates support overhead. We organise, enrich, and maintain your catalog so every channel presents your products clearly and consistently.',
				'deliverables' => array(
					'Product data audits, cleanup, and attribute mapping',
					'Practical workflows for managing catalog information',
					'Titles, descriptions, and specifications that support discovery',
					'Feed and marketplace preparation',
					'Imports, updates, and sync with your source systems',
				),
				'icon'         => '&#xe0e8;||divi',
				'color'        => self::NAVY,
				'image_key'    => 'service-pim',
			),
			'domains-hosting'         => array(
				'title'        => 'Domains & Hosting',
				'headline'     => 'Reliable infrastructure behind your digital presence',
				'excerpt'      => 'Domains, secure hosting, backups, and smooth migrations.',
				'intro'        => 'Your digital channels depend on dependable infrastructure. We handle domains, hosting, security, and monitoring so your team can focus on customers and growth.',
				'deliverables' => array(
					'Domain registration and DNS management',
					'Managed hosting for websites and online stores',
					'Security, SSL, and uptime monitoring',
					'Automated backups and staged update workflows',
					'Migrations from legacy platforms with minimal disruption',
				),
				'icon'         => '&#xe0fb;||divi',
				'color'        => self::BLUE,
				'image_key'    => 'service-hosting',
			),
			'graphic-design'          => array(
				'title'        => 'Graphic Design',
				'headline'     => 'Brand identity that works across every touchpoint',
				'excerpt'      => 'Visual systems and marketing assets that build trust.',
				'intro'        => 'Strong brands are consistent brands. We create visual identities and digital assets that carry through your website, store, campaigns, and sales materials — aligned with how you want to be seen in market.',
				'deliverables' => array(
					'Logo design and supporting brand marks',
					'Colour palettes, typography, and style guidelines',
					'Social, email, and campaign creative templates',
					'Icons, illustrations, and presentation assets',
					'Print-ready files and handoff for digital production',
				),
				'icon'         => '&#xe060;||divi',
				'color'        => self::TEAL,
				'image_key'    => 'service-graphic-design',
			),
		);
	}

	private static function format_service_body( string $intro, array $deliverables ): string {
		$html = '<p style="margin:0 0 14px;">' . esc_html( $intro ) . '</p>';
		if ( empty( $deliverables ) ) {
			return $html;
		}

		$html .= '<p style="margin:0 0 8px;font-weight:600;color:' . self::NAVY . ';">What you get</p><ul style="margin:0;padding-left:1.2em;line-height:1.85;">';
		foreach ( $deliverables as $item ) {
			$html .= '<li>' . esc_html( $item ) . '</li>';
		}
		$html .= '</ul>';

		return $html;
	}

	/**
	 * Build one services-page card row (two 1/2 blurbs inside a section).
	 *
	 * @param array<int, array{slug:string,service:array<string,mixed>}> $pair Indexed service entries.
	 */
	private static function build_service_card_section( array $pair, int $index ): string {
		$cols  = '';
		$delay = 0;

		foreach ( $pair as $entry ) {
			$slug    = $entry['slug'];
			$service = $entry['service'];
			$body    = '<p style="margin:0 0 10px;font-size:1.05em;color:' . self::NAVY . ';font-weight:600;">' . esc_html( $service['headline'] ) . '</p>'
				. self::format_service_body( $service['intro'], $service['deliverables'] );

			$cols .= self::column(
				'1_2',
				self::blurb(
					$service['title'],
					$body,
					$service['icon'],
					'',
					$slug,
					$service['color'],
					self::slide_up( (string) $delay ),
					self::media( $service['image_key'] )
				)
			);
			$delay += 180;
		}

		if ( 1 === count( $pair ) ) {
			$cols .= self::column( '1_2', '' );
		}

		$bg = ( 0 === $index % 2 ) ? self::LIGHT : self::WHITE;

		return self::section(
			self::row( $cols, '1_2,1_2', self::card_row_attrs() ),
			array(
				'background_color'   => $bg,
				'custom_padding'     => '30px|0|30px|0',
				'scroll_fade_enable' => 'on',
				'scroll_fade'        => '0|50|50|100|0|100|100',
			)
		);
	}

	/**
	 * Services card sections (full services-page format).
	 *
	 * @return array<int, string> Section shortcodes keyed by chunk index.
	 */
	public static function build_service_card_sections(): array {
		$sections = array();
		$chunks   = array_chunk( self::services(), 2, true );
		$index    = 0;

		foreach ( $chunks as $pair ) {
			$entries = array();
			foreach ( $pair as $slug => $service ) {
				$entries[] = array(
					'slug'    => $slug,
					'service' => $service,
				);
			}

			$sections[ $index ] = self::build_service_card_section( $entries, $index );
			++$index;
		}

		return $sections;
	}

	/**
	 * Inline services cards (fallback when global library IDs are unavailable).
	 */
	public static function services_cards_sections_layout(): string {
		return implode( '', self::build_service_card_sections() );
	}

	/**
	 * Global Divi section references for shared services cards.
	 */
	public static function services_cards_global_refs(): string {
		$map   = get_option( 'adamat_divi_global_layouts', array() );
		$refs  = '';
		$index = 0;

		foreach ( self::build_service_card_sections() as $section_content ) {
			$slug = 'adamat-services-cards-' . $index;
			$id   = isset( $map[ $slug ] ) ? (int) $map[ $slug ] : 0;

			if ( $id > 0 ) {
				$refs .= self::global_section_ref( $id );
			} else {
				$refs .= $section_content;
			}

			++$index;
		}

		return $refs;
	}

	/**
	 * Shared intro + global service cards (identical on Home, Services, and About).
	 */
	public static function services_cards_block(): string {
		return self::services_cards_intro_section() . self::services_cards_global_refs();
	}

	private static function services_cards_intro_section(): string {
		return self::section(
			self::row(
				self::column(
					'4_4',
					self::text(
						'<h2 style="text-align:center;color:' . self::NAVY . ';">How we work with you</h2>
						<p style="text-align:center;color:' . self::SLATE . ';max-width:780px;margin:0 auto;">We work closely with our clients to understand their specific business needs — then design, build, and support digital solutions that move your business into the <strong style="color:' . self::NAVY . ';">digital AI age</strong>.</p>',
						self::fade_in( '0ms' )
					)
				)
			),
			array(
				'background_color' => self::WHITE,
				'custom_padding'   => '50px|0|30px|0',
			)
		);
	}

	/**
	 * Seed or update shared global layouts in the Divi library.
	 */
	public static function seed_global_layouts(): void {
		if ( ! function_exists( 'et_pb_create_layout' ) ) {
			return;
		}

		$sections = self::build_service_card_sections();
		$map      = array();
		$combined = '';

		foreach ( $sections as $index => $section_content ) {
			$slug  = 'adamat-services-cards-' . $index;
			$title = sprintf( 'Adamat — Services Cards %d', $index + 1 );
			$id    = self::upsert_global_layout(
				$slug,
				$title,
				$section_content,
				'section'
			);

			if ( $id > 0 ) {
				$map[ $slug ] = $id;
			}

			$combined .= $section_content;
		}

		$all_id = self::upsert_global_layout(
			'adamat-services-cards-all',
			'Adamat — Services Cards (All)',
			$combined,
			'layout'
		);

		if ( $all_id > 0 ) {
			$map['adamat-services-cards-all'] = $all_id;
		}

		update_option( 'adamat_divi_global_layouts', $map );
	}

	/**
	 * @return int Layout post ID.
	 */
	private static function upsert_global_layout( string $slug, string $title, string $content, string $layout_type ): int {
		$existing = get_posts(
			array(
				'post_type'      => 'et_pb_layout',
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => 1,
				'meta_key'       => '_adamat_layout_slug',
				'meta_value'     => $slug,
				'fields'         => 'ids',
			)
		);

		$post_args = array(
			'post_title'   => $title,
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_type'    => 'et_pb_layout',
		);

		if ( ! empty( $existing ) ) {
			$post_args['ID'] = (int) $existing[0];
			$layout_id       = wp_update_post( $post_args, true );
		} else {
			$layout_id = et_pb_create_layout(
				$title,
				$content,
				array(
					'_et_pb_built_for_post_type' => 'page',
				),
				array(
					'scope'           => 'global',
					'layout_type'     => $layout_type,
					'module_width'    => 'regular',
					'layout_category' => array(),
					'layout_tag'      => array(),
					'layout_location' => 'local',
				)
			);
		}

		if ( is_wp_error( $layout_id ) || ! $layout_id ) {
			return 0;
		}

		update_post_meta( (int) $layout_id, '_adamat_layout_slug', $slug );
		update_post_meta( (int) $layout_id, '_et_pb_built_for_post_type', 'page' );
		wp_set_object_terms( (int) $layout_id, 'global', 'scope' );
		wp_set_object_terms( (int) $layout_id, $layout_type, 'layout_type' );

		return (int) $layout_id;
	}

	private static function global_section_ref( int $layout_id ): string {
		return '[et_pb_section ' . self::attrs(
			array(
				'global_module'    => (string) $layout_id,
				'fb_built'         => '1',
				'_builder_version' => self::BV,
			)
		) . '][/et_pb_section]';
	}

	/** @return array<string, string> Map of page slug => media catalog key for featured image + hero fallback. */
	public static function page_featured_images(): array {
		return array(
			'home'                => 'hero',
			'about-us'            => 'about-team',
			'services'            => 'hero',
			'projects-showcase'   => 'project-corporate',
			'contact'             => 'contact',
		);
	}

	/**
	 * Set the page featured image from the media catalog (used for hero backgrounds).
	 */
	public static function set_page_featured_image( int $post_id, string $media_key ): void {
		if ( ! class_exists( 'AdamatMediaAssets' ) ) {
			return;
		}

		$attachment_id = AdamatMediaAssets::attachment_id( $media_key );
		if ( $attachment_id > 0 ) {
			set_post_thumbnail( $post_id, $attachment_id );
		}
	}

	/**
	 * Update the first section's background_image from the page featured image.
	 */
	public static function sync_hero_background_from_featured_image( int $post_id ): void {
		if ( 'page' !== get_post_type( $post_id ) ) {
			return;
		}

		if ( 'on' !== get_post_meta( $post_id, '_et_pb_use_builder', true ) ) {
			return;
		}

		$url = get_the_post_thumbnail_url( $post_id, 'full' );
		if ( ! is_string( $url ) || '' === $url ) {
			return;
		}

		$content = get_post_field( 'post_content', $post_id );
		if ( ! is_string( $content ) || '' === $content || ! str_contains( $content, '[et_pb_section' ) ) {
			return;
		}

		$updated = preg_replace(
			'/(\[et_pb_section[^\]]*background_image=")[^"]*(")/',
			'$1' . esc_url( $url ) . '$2',
			$content,
			1
		);

		if ( ! is_string( $updated ) || $updated === $content ) {
			return;
		}

		wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => $updated,
			)
		);
	}

	public static function register_hero_sync_hooks(): void {
		add_action(
			'save_post_page',
			static function ( int $post_id ): void {
				if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
					return;
				}

				self::sync_hero_background_from_featured_image( $post_id );
			},
			30
		);

		add_action(
			'set_post_thumbnail',
			static function ( int $post_id ): void {
				if ( 'page' !== get_post_type( $post_id ) ) {
					return;
				}

				self::sync_hero_background_from_featured_image( $post_id );
			},
			10,
			1
		);
	}

	public static function home_layout( string $hero_media_key = 'hero' ): string {
		return self::section(
			self::row(
				self::column(
					'4_4',
					self::text(
						'<p style="letter-spacing:.12em;text-transform:uppercase;color:' . self::CYAN . ';font-weight:600;margin:0 0 12px;">adamat · Australia</p>
						<h1 style="font-size:3.4em;line-height:1.12;color:#fff;margin:0 0 16px;">Partners in your digital AI transformation</h1>
						<p style="font-size:1.2em;color:rgba(255,255,255,.92);max-width:720px;margin:0 0 28px;">We work closely with our clients across Australia and worldwide — handling your specific business needs with web, commerce, AI, data, hosting, and brand solutions built around you.</p>
						<p>' . self::cta_link( 'View Services', '/services/', 'light' ) . self::cta_link( 'Contact Us', '/contact/', 'outline' ) . '</p>',
						self::fade_in( '0ms', '800ms' )
					)
				)
			),
			array_merge(
				self::hero_section_attrs( $hero_media_key, 'center right' ),
				array(
					'custom_padding'                => '130px|0|110px|0',
					'scroll_vertical_motion_enable' => 'on',
					'scroll_vertical_motion'        => '0|50|50|100|3|0|-3',
				)
			)
		)
		. self::section(
			self::row(
				self::column(
					'1_3',
					self::counter( 'Projects delivered', '50', self::fade_in( '0ms' ) )
				)
				. self::column(
					'1_3',
					self::counter( 'Happy clients', '30', self::fade_in( '150ms' ) )
				)
				. self::column(
					'1_3',
					self::counter( 'Long-term partners', '15', self::fade_in( '300ms' ) )
				),
				'1_3,1_3,1_3'
			),
			array(
				'background_color' => self::WHITE,
				'custom_padding'   => '50px|0|50px|0',
				'box_shadow_style' => 'preset1',
			)
		)
		. self::services_cards_block()
		. self::section(
			self::row(
				self::column( '1_4', self::blurb( 'Discover', 'We listen first — goals, customers, constraints, and what success looks like for your business.', '&#xe037;||divi', '', '', self::BLUE, self::slide_up( '0ms' ) ) )
				. self::column( '1_4', self::blurb( 'Design & Build', 'Tailored web, store, catalog, and brand work shaped around your requirements.', '&#xe060;||divi', '', '', self::CYAN, self::slide_up( '120ms' ) ) )
				. self::column( '1_4', self::blurb( 'Data & AI', 'Product information, automation, and AI-ready structure for the digital AI age.', '&#xe0bc;||divi', '', '', self::TEAL, self::slide_up( '240ms' ) ) )
				. self::column( '1_4', self::blurb( 'Launch & Grow', 'Hosting, optimisation, and ongoing support as you scale.', '&#xe0e7;||divi', '', '', self::NAVY, self::slide_up( '360ms' ) ) ),
				'1_4,1_4,1_4,1_4'
			),
			array(
				'background_color' => self::WHITE,
				'custom_padding'   => '70px|0|70px|0',
			)
		)
		. self::section(
			self::row(
				self::column(
					'4_4',
					self::text(
						'<h2 style="text-align:center;color:#fff;">Ready to start your project?</h2>
						<p style="text-align:center;color:rgba(255,255,255,.92);max-width:640px;margin:0 auto 24px;">Tell us what you are trying to achieve — we will work with you to shape the right digital path into the AI age.</p>
						<p style="text-align:center;">' . self::cta_link( 'Get in Touch', '/contact/', 'light' ) . '</p>',
						self::fade_in( '0ms' )
					)
				)
			),
			array_merge(
				self::gradient_bg( self::BLUE, self::CYAN, '90deg' ),
				array(
					'custom_padding'           => '80px|0|80px|0',
					'scroll_scaling_enable'    => 'on',
					'scroll_scaling'           => '0|50|50|100|92|100|100',
				)
			)
		);
	}

	public static function about_layout( string $hero_media_key = 'about-team' ): string {
		return self::section(
			self::row(
				self::column(
					'4_4',
					self::text(
						'<h1 style="text-align:center;color:#fff;">About adamat</h1>
						<p style="text-align:center;color:rgba(255,255,255,.92);max-width:760px;margin:0 auto;">An Australian studio that works closely with clients to handle their specific business needs — and move them confidently into the digital AI age.</p>',
						self::fade_in( '0ms' )
					)
				)
			),
			array_merge(
				self::hero_section_attrs( $hero_media_key, 'center' ),
				array( 'custom_padding' => '90px|0|40px|0' )
			)
		)
		. self::section(
			self::row(
				self::column(
					'1_2',
					self::image(
						self::media( 'about-team' ),
						'Collaborative adamat team planning a digital project',
						array_merge( self::fade_in( '0ms' ), array( 'border_radii' => 'on|10px|10px|10px|10px' ) )
					)
					. self::text(
						'<h2 style="color:' . self::NAVY . ';">Our story</h2>
						<p style="color:' . self::SLATE . ';font-size:1.05em;">We partner with businesses to launch and grow online — combining websites, stores, product data, AI, hosting, and brand design around what each client actually needs.</p>
						<p style="color:' . self::INK . ';">You might start with one challenge or a full digital refresh. Either way, we stay close to your team and adapt as your business evolves.</p>',
						self::slide_in( 'left', '150ms' )
					)
				)
				. self::column(
					'1_2',
					self::text(
						'<h3 style="color:' . self::NAVY . ';">Why clients choose us</h3>
						<ul style="line-height:2;color:' . self::SLATE . ';">
							<li>One partner for web, commerce, data, AI, hosting &amp; brand</li>
							<li>Close collaboration — your goals drive every decision</li>
							<li>Conversion and performance built in, not bolted on</li>
							<li>Australian agency with global client experience</li>
						</ul>',
						self::slide_in( 'right', '150ms' )
					)
				),
				'1_2,1_2'
			),
			array(
				'background_color'   => self::WHITE,
				'custom_padding'     => '40px|0|40px|0',
				'scroll_fade_enable' => 'on',
				'scroll_fade'        => '0|50|50|100|0|100|100',
			)
		)
		. self::services_cards_block();
	}

	public static function services_layout( string $hero_media_key = 'hero' ): string {
		$content = self::section(
			self::row(
				self::column(
					'4_4',
					self::text(
						'<h1 style="text-align:center;color:#fff;">Services</h1>
						<p style="text-align:center;color:rgba(255,255,255,.9);max-width:820px;margin:0 auto 12px;">We work closely with our clients to handle their specific business needs — from first website to full digital and AI transformation.</p>
						<p style="text-align:center;color:rgba(255,255,255,.75);font-size:.95em;">Each section below outlines what we deliver and the outcomes you can expect.</p>',
						self::fade_in( '0ms' )
					)
				)
			),
			array_merge(
				self::hero_section_attrs( $hero_media_key, 'center' ),
				array( 'custom_padding' => '90px|0|50px|0' )
			)
		)
		. self::services_cards_block();

		return $content
		. self::section(
			self::row(
				self::column(
					'4_4',
					self::text(
						'<h2 style="text-align:center;color:#fff;">Not sure where to start?</h2>
						<p style="text-align:center;color:rgba(255,255,255,.9);margin-bottom:20px;">Share your goal — a new site, online store, catalog cleanup, AI workflow, or rebrand — and we will work with you on the right next step.</p>
						<p style="text-align:center;">' . self::cta_link( 'Book a free consultation', '/contact/', 'light' ) . '</p>',
						self::fade_in( '0ms' )
					)
				)
			),
			array_merge(
				self::gradient_bg( self::BLUE, self::TEAL, '90deg' ),
				array(
					'custom_padding'        => '60px|0|80px|0',
					'scroll_scaling_enable' => 'on',
					'scroll_scaling'        => '0|50|50|100|90|100|100',
				)
			)
		);
	}

	public static function projects_layout( string $hero_media_key = 'project-corporate' ): string {
		$projects = array(
			array(
				'title'     => 'adamat.online',
				'desc'      => 'Corporate website showcasing web, design, and eCommerce services for Australian and international clients.',
				'tag'       => 'Web Design',
				'color'     => self::BLUE,
				'image_key' => 'project-corporate',
			),
			array(
				'title'     => 'Goulburn Valley Produce',
				'desc'      => 'Online catalog and bulk order experience for local produce with category-driven ordering.',
				'tag'       => 'eCommerce',
				'color'     => self::TEAL,
				'image_key' => 'project-ecommerce',
			),
			array(
				'title'     => 'Local Ad Pages Platform',
				'desc'      => 'Location-based landing pages with AI-friendly structure and automated content management.',
				'tag'       => 'AI + Content',
				'color'     => self::CYAN,
				'image_key' => 'project-ai-content',
			),
			array(
				'title'     => 'Product Data Cleanup',
				'desc'      => 'Product data enrichment and normalization for multichannel catalogs and marketplace feeds.',
				'tag'       => 'Product Data',
				'color'     => self::NAVY,
				'image_key' => 'project-pim',
			),
			array(
				'title'     => 'Brand Identity Kit',
				'desc'      => 'Logo, color system, and marketing assets for a regional retail launch.',
				'tag'       => 'Graphic Design',
				'color'     => self::TEAL,
				'image_key' => 'project-brand',
			),
			array(
				'title'     => 'Managed Hosting Migration',
				'desc'      => 'Zero-downtime migration to secure hosting with SSL, backups, and performance tuning.',
				'tag'       => 'Hosting',
				'color'     => self::BLUE,
				'image_key' => 'project-hosting',
			),
		);

		$cards = '';
		$delay = 0;
		foreach ( array_chunk( $projects, 3 ) as $row_projects ) {
			$row_cols = '';
			foreach ( $row_projects as $project ) {
				$tag_html = '<span style="display:inline-block;background:' . self::LIGHT . ';color:' . $project['color'] . ';padding:4px 12px;border-radius:999px;font-size:12px;font-weight:600;margin-bottom:10px;">' . esc_html( $project['tag'] ) . '</span><br />' . esc_html( $project['desc'] );
				$row_cols .= self::column(
					'1_3',
					self::blurb(
						$project['title'],
						$tag_html,
						'&#xe0e8;||divi',
						'',
						'',
						$project['color'],
						self::zoom_in( (string) $delay ),
						self::media( $project['image_key'] )
					)
				);
				$delay += 120;
			}
			while ( substr_count( $row_cols, '[et_pb_column' ) < 3 ) {
				$row_cols .= self::column( '1_3', '' );
			}
			$cards .= self::row( $row_cols, '1_3,1_3,1_3', self::card_row_attrs() );
		}

		return self::section(
			self::row(
				self::column(
					'4_4',
					self::text(
						'<h1 style="text-align:center;color:#fff;">Projects Showcase</h1>
						<p style="text-align:center;color:rgba(255,255,255,.92);">Selected work across web, commerce, AI, and product data.</p>',
						self::fade_in( '0ms' )
					)
				)
			),
			array_merge(
				self::hero_section_attrs( $hero_media_key, 'center' ),
				array( 'custom_padding' => '90px|0|30px|0' )
			)
		)
		. self::section(
			$cards,
			array(
				'background_color'      => self::WHITE,
				'custom_padding'        => '20px|0|30px|0',
				'scroll_fade_enable'    => 'on',
				'scroll_fade'           => '0|50|50|100|0|100|100',
			)
		)
		. self::section(
			self::row(
				self::column(
					'4_4',
					self::text(
						'<p style="text-align:center;">' . self::cta_link( 'Start a project', '/contact/', 'primary' ) . '</p>',
						self::fade_in( '0ms' )
					)
				)
			),
			array(
				'background_color' => self::MIST,
				'custom_padding'   => '40px|0|80px|0',
			)
		);
	}

	public static function contact_layout( string $hero_media_key = 'contact' ): string {
		$form = '[et_pb_contact_form captcha="off" email="hello@adamat.online" title="Send a message" _builder_version="' . self::BV . '" background_color="' . self::WHITE . '" custom_padding="30px|30px|30px|30px" border_radii="on|8px|8px|8px|8px" box_shadow_style="preset1" ' . self::attrs( self::fade_in( '200ms' ) ) . '][et_pb_contact_field field_id="Name" field_title="Name" _builder_version="' . self::BV . '" /][et_pb_contact_field field_id="Email" field_title="Email Address" field_type="email" _builder_version="' . self::BV . '" /][et_pb_contact_field field_id="Message" field_title="Message" field_type="text" full_field="on" _builder_version="' . self::BV . '" /][/et_pb_contact_form]';

		return self::section(
			self::row(
				self::column(
					'4_4',
					self::text(
						'<h1 style="text-align:center;color:#fff;">Contact</h1>
						<p style="text-align:center;color:rgba(255,255,255,.9);">Web design, eCommerce, AI, product data, hosting, or graphic design — tell us what you need.</p>',
						self::fade_in( '0ms' )
					)
				)
			),
			array_merge(
				self::hero_section_attrs( $hero_media_key, 'center' ),
				array( 'custom_padding' => '90px|0|40px|0' )
			)
		)
		. self::section(
			self::row(
				self::column(
					'1_2',
					self::image(
						self::media( 'contact' ),
						'Contact adamat for eCommerce and AI project inquiries',
						array_merge( self::fade_in( '0ms' ), array( 'border_radii' => 'on|10px|10px|10px|10px' ) )
					)
					. self::text(
						'<h2 style="color:' . self::NAVY . ';">Get in touch</h2>
						<p style="color:' . self::SLATE . ';">Describe your project — new site, online store, catalog work, AI workflow, rebrand, or migration — and we will suggest how we can help.</p>
						<p style="color:' . self::INK . ';line-height:2;">
						<strong>Email:</strong> <a href="mailto:hello@adamat.online" style="color:' . self::BLUE . ';">hello@adamat.online</a><br />
						<strong>Location:</strong> Australia<br />
						<strong>Focus areas:</strong> Web · eCommerce · AI · Product data · Hosting · Brand design</p>',
						self::slide_in( 'left', '0ms' )
					)
					. self::blurb( 'Fast response', 'We typically respond within 24 hours on business days.', '&#xe0bc;||divi', '', '', self::CYAN, self::slide_up( '150ms' ) )
					. self::blurb( 'Remote-friendly', 'Collaborate from anywhere — video calls, shared boards, and clear milestones.', '&#xe0e7;||divi', '', '', self::TEAL, self::slide_up( '300ms' ) )
				)
				. self::column( '1_2', $form ),
				'1_2,1_2'
			),
			array(
				'background_color'   => self::LIGHT,
				'custom_padding'     => '50px|0|80px|0',
				'scroll_fade_enable' => 'on',
				'scroll_fade'        => '0|50|50|100|0|100|100',
			)
		);
	}

	/** @return array<string, string> */
	private static function fade_in( string $delay = '0ms', string $duration = '800ms' ): array {
		return array(
			'animation_style'    => 'fade',
			'animation_duration'   => $duration,
			'animation_delay'      => $delay,
			'animation_intensity'  => '50%',
			'animation_speed_curve'=> 'ease-out',
		);
	}

	/** @return array<string, string> */
	private static function slide_in( string $direction, string $delay = '0ms' ): array {
		return array(
			'animation_style'            => 'slide',
			'animation_direction'        => $direction,
			'animation_duration'         => '800ms',
			'animation_delay'            => $delay,
			'animation_intensity_slide'  => '30%',
			'animation_speed_curve'      => 'ease-out',
		);
	}

	/** @return array<string, string> */
	private static function slide_up( string $delay = '0ms' ): array {
		return self::slide_in( 'bottom', $delay );
	}

	/** @return array<string, string> */
	private static function zoom_in( string $delay = '0ms' ): array {
		return array(
			'animation_style'           => 'zoom',
			'animation_duration'        => '800ms',
			'animation_delay'           => $delay,
			'animation_intensity_zoom'  => '20%',
			'animation_speed_curve'     => 'ease-out',
		);
	}

	/** @return array<string, string> Hero section attrs: featured image bg + 70% black overlay. */
	private static function hero_section_attrs( string $media_key, string $position = 'center' ): array {
		return array_merge(
			self::hero_photo_bg( $media_key, $position ),
			self::hero_overlay()
		);
	}

	/** @return array<string, string> */
	private static function hero_photo_bg( string $media_key, string $position ): array {
		$url = self::media( $media_key );
		if ( '' === $url ) {
			return array(
				'background_color' => self::NAVY,
			);
		}

		return array(
			'background_image'    => $url,
			'background_size'     => 'cover',
			'background_position' => $position,
			'background_repeat'   => 'no-repeat',
			'parallax'            => 'on',
		);
	}

	/** @return array<string, string> */
	private static function hero_overlay(): array {
		return array(
			'use_background_color_gradient'          => 'on',
			'background_color'                       => 'rgba(0,0,0,0.7)',
			'background_color_gradient_stops'        => 'rgba(0,0,0,0.7) 0%|rgba(0,0,0,0.7) 100%',
			'background_color_gradient_type'           => 'linear',
			'background_color_gradient_direction'    => '180deg',
			'background_color_gradient_overlays_image' => 'on',
		);
	}

	/** @return array<string, string> */
	private static function photo_bg( string $url, string $position = 'center' ): array {
		if ( '' === $url ) {
			return array();
		}

		return array(
			'background_image'       => $url,
			'background_size'        => 'cover',
			'background_position'    => $position,
			'background_repeat'      => 'no-repeat',
			'parallax'               => 'on',
			'background_blend'       => 'multiply',
		);
	}

	private static function image( string $src, string $alt, array $attrs = array() ): string {
		if ( '' === $src ) {
			return '';
		}

		$defaults = array(
			'src'              => $src,
			'alt'              => $alt,
			'title_text'       => $alt,
			'align'            => 'center',
			'force_fullwidth'  => 'on',
			'_builder_version' => self::BV,
			'border_radii'     => 'on|10px|10px|10px|10px',
			'box_shadow_style' => 'preset1',
		);

		return '[et_pb_image ' . self::attrs( array_merge( $defaults, $attrs ) ) . ' /]';
	}

	/** @return array<string, string> */
	private static function gradient_bg( string $from, string $to, string $direction = '135deg' ): array {
		return array(
			'use_background_color_gradient'     => 'on',
			'background_color'                  => $from,
			'background_color_gradient_stops'   => $from . ' 0%|' . $to . ' 100%',
			'background_color_gradient_type'    => 'linear',
			'background_color_gradient_direction'=> $direction,
		);
	}

	private static function cta_link( string $label, string $url, string $style = 'primary' ): string {
		if ( 'light' === $style ) {
			return '<a href="' . esc_url( $url ) . '" style="display:inline-block;background:#fff;color:' . self::NAVY . ';padding:13px 30px;border-radius:6px;text-decoration:none;font-weight:600;margin:0 12px 12px 0;box-shadow:0 4px 14px rgba(0,0,0,.15);">' . esc_html( $label ) . '</a>';
		}
		if ( 'outline' === $style ) {
			return '<a href="' . esc_url( $url ) . '" style="display:inline-block;border:2px solid #fff;color:#fff;padding:11px 28px;border-radius:6px;text-decoration:none;font-weight:600;margin:0 0 12px;">' . esc_html( $label ) . '</a>';
		}

		return '<a href="' . esc_url( $url ) . '" style="display:inline-block;background:' . self::BLUE . ';color:#fff;padding:13px 30px;border-radius:6px;text-decoration:none;font-weight:600;box-shadow:0 4px 14px rgba(30,111,217,.35);">' . esc_html( $label ) . '</a>';
	}

	private static function counter( string $title, string $number, array $animation = array() ): string {
		$attrs = array_merge(
			array(
				'title'            => $title,
				'number'           => $number,
				'percent_sign'     => 'off',
				'_builder_version' => self::BV,
				'number_font_size' => '48px',
				'number_text_color'=> self::BLUE,
				'title_text_color' => self::SLATE,
				'background_color' => self::WHITE,
				'custom_padding'   => '24px|24px|24px|24px',
				'border_radii'     => 'on|8px|8px|8px|8px',
			),
			$animation
		);

		return '[et_pb_number_counter ' . self::attrs( $attrs ) . ' /]';
	}

	private static function section( string $content, array $attrs = array() ): string {
		$defaults = array(
			'fb_built'         => '1',
			'_builder_version' => self::BV,
			'custom_padding'   => '0|0|0|0',
		);

		return '[et_pb_section ' . self::attrs( array_merge( $defaults, $attrs ) ) . ']' . $content . '[/et_pb_section]';
	}

	private static function row( string $content, string $structure = '4_4', array $attrs = array() ): string {
		$defaults = array(
			'_builder_version' => self::BV,
			'column_structure' => $structure,
		);

		return '[et_pb_row ' . self::attrs( array_merge( $defaults, $attrs ) ) . ']' . $content . '[/et_pb_row]';
	}

	private static function column( string $type, string $content, array $attrs = array() ): string {
		$defaults = array(
			'type'             => $type,
			'_builder_version' => self::BV,
		);

		return '[et_pb_column ' . self::attrs( array_merge( $defaults, $attrs ) ) . ']' . $content . '[/et_pb_column]';
	}

	private static function text( string $html, array $attrs = array() ): string {
		$defaults = array( '_builder_version' => self::BV );

		return '[et_pb_text ' . self::attrs( array_merge( $defaults, $attrs ) ) . ']' . $html . '[/et_pb_text]';
	}

	private static function blurb(
		string $title,
		string $body,
		string $icon,
		string $url = '',
		string $module_id = '',
		string $icon_color = '',
		array $animation = array(),
		string $image_url = ''
	): string {
		$attrs = array(
			'title'             => $title,
			'_builder_version'  => self::BV,
			'background_color'  => self::WHITE,
			'custom_padding'    => '28px|28px|28px|28px',
			'border_radii'      => 'on|10px|10px|10px|10px',
			'box_shadow_style'  => 'preset1',
			'header_font_color' => self::NAVY,
			'body_font_color'   => self::SLATE,
		);

		if ( '' !== $image_url ) {
			$attrs['use_icon']          = 'off';
			$attrs['image']             = $image_url;
			$attrs['icon_placement']    = 'top';
			$attrs['image_icon_width']  = '100%';
			$attrs['module_class']      = 'adamat-card';
		} else {
			$attrs['use_icon']   = 'on';
			$attrs['font_icon']  = $icon;
			$attrs['icon_color'] = '' !== $icon_color ? $icon_color : self::BLUE;
		}

		$attrs = array_merge( $attrs, $animation );

		if ( '' !== $url ) {
			$attrs['url'] = $url;
		}

		if ( '' !== $module_id ) {
			$attrs['module_id'] = $module_id;
		}

		return '[et_pb_blurb ' . self::attrs( $attrs ) . ']' . $body . '[/et_pb_blurb]';
	}

	/**
	 * @param array<string, string> $attrs Attributes.
	 */
	private static function attrs( array $attrs ): string {
		$parts = array();

		foreach ( $attrs as $key => $value ) {
			$parts[] = $key . '="' . esc_attr( $value ) . '"';
		}

		return implode( ' ', $parts );
	}
}
