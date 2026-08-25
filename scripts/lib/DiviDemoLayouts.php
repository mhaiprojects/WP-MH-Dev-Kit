<?php
/**
 * Divi Builder shortcode helpers for the divi-demo multisite showcase.
 *
 * Covers structure (sections/rows/columns), grid layouts, and all standard
 * Divi 4 modules. Reference: docs/divi/ and wp-content/themes/Divi/includes/builder/module/
 *
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

final class DiviDemoLayouts {

	private const BV = '4.27.7';

	private const ACCENT  = '#7c3aed';

	private const INK     = '#1e1b4b';

	private const SLATE   = '#64748b';

	private const LIGHT   = '#f8fafc';

	private const WHITE   = '#ffffff';

	private const IMG = 'https://picsum.photos/seed/dividemo/960/640';

	private const IMG2 = 'https://picsum.photos/seed/dividemo2/960/640';

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

		et_update_option( 'accent_color', self::ACCENT );
		et_update_option( 'secondary_accent_color', '#a78bfa' );
		et_update_option( 'header_color', self::INK );
		et_update_option( 'font_color', self::SLATE );
		et_update_option( 'link_color', self::ACCENT );
		et_update_option( 'primary_nav_bg', self::INK );
		et_update_option( 'menu_link', self::WHITE );
		et_update_option( 'menu_link_active', '#c4b5fd' );
		et_update_option( 'divi_fixed_nav', 'on' );
		et_update_option( 'body_font_size', '16' );
	}

	/** @return array<string, array{title: string, nav_title: string, group: string, excerpt: string}> */
	public static function page_definitions(): array {
		return array(
			'divi-demo'                 => array(
				'title'     => 'Divi Builder Demo Hub',
				'nav_title' => 'Home',
				'group'     => 'home',
				'excerpt'   => 'Overview and index of every Divi demo section.',
			),
			'structure-and-grids'       => array(
				'title'     => 'Structure, Rows & Grids',
				'nav_title' => 'Structure & Grids',
				'group'     => 'layout',
				'excerpt'   => 'Section, row, and column layouts with sizing and spacing settings.',
			),
			'advanced-builder-settings' => array(
				'title'     => 'Advanced Builder Settings',
				'nav_title' => 'Advanced Settings',
				'group'     => 'layout',
				'excerpt'   => 'Animation, scroll effects, gradients, borders, shadows, and equal columns.',
			),
			'content-modules'           => array(
				'title'     => 'Content Modules',
				'nav_title' => 'Content',
				'group'     => 'modules',
				'excerpt'   => 'Text, media, buttons, blurbs, and basic content blocks.',
			),
			'interactive-modules'       => array(
				'title'     => 'Interactive Modules',
				'nav_title' => 'Interactive',
				'group'     => 'modules',
				'excerpt'   => 'Accordions, tabs, sliders, counters, pricing, and social proof.',
			),
			'forms-and-utility'         => array(
				'title'     => 'Forms & Utility Modules',
				'nav_title' => 'Forms & Utility',
				'group'     => 'modules',
				'excerpt'   => 'Contact forms, signup, search, login, map, and navigation modules.',
			),
			'blog-and-post-modules'     => array(
				'title'     => 'Blog & Post Modules',
				'nav_title' => 'Blog & Posts',
				'group'     => 'modules',
				'excerpt'   => 'Blog feeds, portfolios, and single-post layout modules.',
			),
			'fullwidth-modules'         => array(
				'title'     => 'Fullwidth Modules',
				'nav_title' => 'Fullwidth',
				'group'     => 'modules',
				'excerpt'   => 'Fullwidth header, slider, map, menu, portfolio, and post modules.',
			),
			'woocommerce-modules'       => array(
				'title'     => 'WooCommerce Modules',
				'nav_title' => 'WooCommerce',
				'group'     => 'woocommerce',
				'excerpt'   => 'Divi WooCommerce shop, product, cart, and checkout modules with live grocery products.',
			),
		);
	}

	/** @return array<string, string> */
	private static function page_layouts(): array {
		return array(
			'divi-demo'                 => self::page_hub(),
			'structure-and-grids'       => self::page_structure_grids(),
			'advanced-builder-settings' => self::page_advanced_settings(),
			'content-modules'           => self::page_content_modules(),
			'interactive-modules'       => self::page_interactive_modules(),
			'forms-and-utility'         => self::page_forms_utility(),
			'blog-and-post-modules'     => self::page_blog_post_modules(),
			'fullwidth-modules'         => self::page_fullwidth_modules(),
			'woocommerce-modules'       => self::page_woocommerce_modules(),
		);
	}

	/** @return array<string, array{title: string, nav_title: string, group: string, excerpt: string, layout: string}> */
	public static function all_pages(): array {
		$pages   = array();
		$layouts = self::page_layouts();

		foreach ( self::page_definitions() as $slug => $definition ) {
			$pages[ $slug ] = array_merge(
				$definition,
				array( 'layout' => $layouts[ $slug ] ?? '' )
			);
		}

		return $pages;
	}

	/** @return array<string, array{label: string, description: string, slugs: list<string>}> */
	public static function demo_page_groups(): array {
		return array(
			'layout'  => array(
				'label'       => 'Layout & Structure',
				'description' => 'Rows, columns, grids, and cross-cutting builder settings.',
				'slugs'       => array( 'structure-and-grids', 'advanced-builder-settings' ),
			),
			'modules' => array(
				'label'       => 'Builder Modules',
				'description' => 'Every standard Divi module grouped by purpose.',
				'slugs'       => array(
					'content-modules',
					'interactive-modules',
					'forms-and-utility',
					'blog-and-post-modules',
					'fullwidth-modules',
				),
			),
			'woocommerce' => array(
				'label'       => 'WooCommerce',
				'description' => 'Live grocery catalog with Divi WooCommerce builder modules.',
				'slugs'       => array( 'woocommerce-modules' ),
			),
		);
	}

	/**
	 * Primary navigation tree for wp-admin menu seeding.
	 *
	 * @return list<array{type: string, title?: string, slug?: string, children?: list<array{type: string, slug: string}>}>
	 */
	public static function primary_nav_tree(): array {
		return array(
			array(
				'type'  => 'page',
				'slug'  => 'divi-demo',
				'title' => 'Home',
			),
			array(
				'type'  => 'link',
				'title' => 'Shop',
				'url'   => '/shop/',
			),
			array(
				'type'     => 'group',
				'title'    => 'Layout',
				'children' => array(
					array( 'type' => 'page', 'slug' => 'structure-and-grids' ),
					array( 'type' => 'page', 'slug' => 'advanced-builder-settings' ),
				),
			),
			array(
				'type'     => 'group',
				'title'    => 'Modules',
				'children' => array(
					array( 'type' => 'page', 'slug' => 'content-modules' ),
					array( 'type' => 'page', 'slug' => 'interactive-modules' ),
					array( 'type' => 'page', 'slug' => 'forms-and-utility' ),
					array( 'type' => 'page', 'slug' => 'blog-and-post-modules' ),
					array( 'type' => 'page', 'slug' => 'fullwidth-modules' ),
					array( 'type' => 'page', 'slug' => 'woocommerce-modules' ),
				),
			),
		);
	}

	/** @return list<array{slug: string, title: string, excerpt: string}> */
	public static function demo_nav_pages(): array {
		$pages = array();
		foreach ( self::page_definitions() as $slug => $meta ) {
			if ( 'divi-demo' === $slug ) {
				continue;
			}
			$pages[] = array(
				'slug'    => $slug,
				'title'   => $meta['title'],
				'excerpt' => $meta['excerpt'],
			);
		}
		return $pages;
	}

	private static function demo_page_url( string $slug ): string {
		if ( function_exists( 'home_url' ) ) {
			return home_url( '/' . $slug . '/' );
		}

		return 'https://divi-demo.cursor-wpdev2.ddev.site/' . $slug . '/';
	}

	private static function page_hub(): string {
		$all   = self::page_definitions();
		$body  = '';

		foreach ( self::demo_page_groups() as $group ) {
			$cards = '';
			foreach ( $group['slugs'] as $slug ) {
				if ( ! isset( $all[ $slug ] ) ) {
					continue;
				}
				$meta = $all[ $slug ];
				$cards .= self::blurb(
					$meta['title'],
					$meta['excerpt'],
					'%%3%%',
					self::demo_page_url( $slug )
				);
			}

			$body .= self::section(
				self::row(
					self::column(
						'4_4',
						self::heading( $group['label'], 'h2' )
						. self::text( '<p>' . $group['description'] . '</p>' )
					)
				)
				. self::row(
					self::column( '4_4', $cards ),
					'4_4',
					array( 'custom_padding' => '0|0|20px|0' )
				),
				array(
					'background_color' => self::WHITE,
					'custom_padding'   => '20px|0|40px|0',
				)
			);
		}

		return self::section(
			self::row(
				self::column(
					'4_4',
					self::mod(
						'et_pb_heading',
						array(
							'title'             => 'Divi Builder Complete Demo',
							'header_level'      => 'h1',
							'header_font_color' => self::WHITE,
							'_builder_version'  => self::BV,
						)
					)
					. self::text(
						'<p>This subsite is organised into focused demo pages — each covers one area of the Divi 4 builder. Use the primary navigation or the sections below to jump to structure layouts, individual modules, and advanced settings.</p>',
						array(
							'text_text_color' => '#c4b5fd',
							'_builder_version' => self::BV,
						)
					)
				)
			),
			array(
				'background_color'                => self::INK,
				'custom_padding'                  => '60px|0|40px|0',
				'use_background_color_gradient'   => 'on',
				'background_color'                => self::INK,
				'background_color_gradient_stops' => self::INK . ' 0%|#312e81 100%',
			)
		)
		. self::section(
			self::row(
				self::column(
					'4_4',
					self::heading( 'Demo sections', 'h2' )
					. self::text(
						'<p><strong>Layout</strong> — row/column grids and builder-wide settings.<br>'
						. '<strong>Modules</strong> — content, interactive, forms, blog, and fullwidth blocks.</p>'
					)
				)
			),
			array(
				'background_color' => self::LIGHT,
				'custom_padding'   => '40px|0|0|0',
			)
		)
		. $body;
	}

	private static function page_structure_grids(): string {
		$structures = array(
			'4_4'             => '1 column',
			'1_2,1_2'         => '2 equal columns',
			'1_3,1_3,1_3'     => '3 equal columns',
			'1_4,1_4,1_4,1_4' => '4 equal columns',
			'2_3,1_3'         => '2/3 + 1/3',
			'1_3,2_3'         => '1/3 + 2/3',
			'1_4,3_4'         => '1/4 + 3/4',
			'3_4,1_4'         => '3/4 + 1/4',
		);

		$content = self::intro_section(
			'Structure & Grid Layouts',
			'Every row below uses a different <code>column_structure</code> attribute. Columns use matching <code>type</code> values (4_4, 1_2, 1_3, 1_4, 2_3, 3_4).'
		);

		foreach ( $structures as $structure => $label ) {
			$types = explode( ',', $structure );
			$cols  = '';
			foreach ( $types as $type ) {
				$cols .= self::column(
					$type,
					self::text( '<p><strong>' . $type . '</strong> column in <em>' . $label . '</em> row.</p>' ),
					array(
						'background_color' => self::LIGHT,
						'custom_padding'   => '16px|16px|16px|16px',
					)
				);
			}
			$content .= self::section(
				self::row( $cols, $structure, array( 'custom_padding' => '10px|0|10px|0' ) ),
				array(
					'background_color' => self::WHITE,
					'custom_padding'   => '0|0|30px|0',
				)
			);
		}

		$content .= self::section(
			self::row(
				self::column(
					'1_2',
					self::heading( 'Equal Column Heights', 'h3' )
					. self::text( '<p><code>make_equal="on"</code> stretches columns to equal height — useful for card grids.</p>' )
					. self::blurb( 'Card A', 'Shorter blurb in equal-height row.', '%%47%%' )
				)
				. self::column(
					'1_2',
					self::blurb(
						'Card B',
						'Taller blurb with more body copy to demonstrate equal column stretching when make_equal is enabled on the parent row.',
						'%%48%%'
					)
				),
				'1_2,1_2',
				array(
					'make_equal'     => 'on',
					'custom_padding' => '20px|0|20px|0',
				)
			)
		);

		$content .= self::section(
			self::row(
				self::column(
					'4_4',
					self::heading( 'Custom Gutter & Padding', 'h3' )
					. self::text( '<p>Row: <code>use_custom_gutter="on" gutter_width="2"</code>. Section/row/column padding via <code>custom_padding</code> (top|right|bottom|left).</p>' )
				),
				'4_4'
			),
			array(
				'custom_padding'                  => '40px|0|40px|0',
				'use_background_color_gradient'   => 'on',
				'background_color'                => '#ede9fe',
				'background_color_gradient_stops' => '#ede9fe 0%|#f8fafc 100%',
			)
		);

		return $content;
	}

	private static function page_content_modules(): string {
		$modules = array(
			self::showcase(
				'Text (et_pb_text)',
				self::text(
					'<p>Rich text with <strong>bold</strong>, <em>italic</em>, and <a href="#">links</a>. Supports custom padding, background, and text orientation.</p>',
					array(
						'background_color' => self::LIGHT,
						'custom_padding'   => '20px|20px|20px|20px',
					)
				)
			),
			self::showcase(
				'Heading (et_pb_heading)',
				self::mod(
					'et_pb_heading',
					array(
						'title'            => 'Semantic Heading Module',
						'header_level'     => 'h2',
						'_builder_version' => self::BV,
					)
				)
			),
			self::showcase(
				'Image (et_pb_image)',
				self::mod(
					'et_pb_image',
					array(
						'src'              => self::IMG,
						'alt'              => 'Demo image',
						'align'            => 'center',
						'border_radii'     => 'on|12px|12px|12px|12px',
						'box_shadow_style' => 'preset1',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::showcase(
				'Blurb (et_pb_blurb)',
				self::blurb( 'Icon Blurb', 'Blurb with Font Awesome icon, title, body, and optional URL.', '%%3%%', '#' )
			),
			self::showcase(
				'Button (et_pb_button)',
				self::mod(
					'et_pb_button',
					array(
						'button_text'      => 'Primary Button',
						'button_url'       => '#',
						'button_alignment' => 'center',
						'custom_button'    => 'on',
						'button_bg_color'  => self::ACCENT,
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::showcase(
				'Icon (et_pb_icon)',
				self::mod(
					'et_pb_icon',
					array(
						'font_icon'        => '%%3%%',
						'icon_color'       => self::ACCENT,
						'icon_width'       => '96px',
						'alignment'        => 'center',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::showcase(
				'Divider (et_pb_divider)',
				self::mod(
					'et_pb_divider',
					array(
						'color'            => self::ACCENT,
						'divider_weight'   => '3px',
						'width'            => '50%',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::showcase(
				'Code (et_pb_code)',
				self::mod(
					'et_pb_code',
					array( '_builder_version' => self::BV ),
					'<pre style="background:#1e1b4b;color:#e9d5ff;padding:16px;border-radius:8px;">// Custom HTML/JS/CSS block</pre>'
				)
			),
			self::showcase(
				'Call To Action (et_pb_cta)',
				self::mod(
					'et_pb_cta',
					array(
						'title'            => 'Ready to explore Divi?',
						'button_text'      => 'View Hub',
						'button_url'       => self::demo_page_url( 'divi-demo' ),
						'use_background_color' => 'on',
						'background_color' => self::INK,
						'header_font_color'=> self::WHITE,
						'body_font_color'  => '#c4b5fd',
						'custom_button'    => 'on',
						'button_bg_color'  => self::ACCENT,
						'_builder_version' => self::BV,
					),
					'CTA module combines headline, body, and button with background controls.'
				)
			),
			self::showcase(
				'Gallery (et_pb_gallery)',
				self::mod(
					'et_pb_gallery',
					array(
						'gallery_ids'      => '',
						'fullwidth'        => 'off',
						'show_title_and_caption' => 'off',
						'posts_number'     => '4',
						'show_pagination'  => 'off',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::showcase(
				'Video (et_pb_video)',
				self::mod(
					'et_pb_video',
					array(
						'src'              => 'https://www.youtube.com/watch?v=FkQu3H5kAF4',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::showcase(
				'Audio (et_pb_audio)',
				self::mod(
					'et_pb_audio',
					array(
						'title'            => 'Sample Audio Track',
						'artist_name'      => 'Divi Demo',
						'audio'            => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
						'image'            => self::IMG2,
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
		);

		return self::intro_section( 'Content Modules', 'Standard content blocks used in regular (non-fullwidth) sections.' )
			. implode( '', $modules );
	}

	private static function page_interactive_modules(): string {
		$accordion = self::mod(
			'et_pb_accordion',
			array( '_builder_version' => self::BV, 'open_toggle_text_color' => self::ACCENT ),
			self::mod(
				'et_pb_accordion_item',
				array(
					'title'            => 'Accordion Item One',
					'open'             => 'on',
					'_builder_version' => self::BV,
				),
				'First accordion panel with open state.'
			)
			. self::mod(
				'et_pb_accordion_item',
				array(
					'title'            => 'Accordion Item Two',
					'_builder_version' => self::BV,
				),
				'Second accordion panel.'
			)
		);

		$tabs = self::mod(
			'et_pb_tabs',
			array( '_builder_version' => self::BV ),
			self::mod(
				'et_pb_tab',
				array(
					'title'            => 'Tab One',
					'_builder_version' => self::BV,
				),
				'First tab content.'
			)
			. self::mod(
				'et_pb_tab',
				array(
					'title'            => 'Tab Two',
					'_builder_version' => self::BV,
				),
				'Second tab content.'
			)
		);

		$slider = self::mod(
			'et_pb_slider',
			array( '_builder_version' => self::BV, 'show_arrows' => 'on', 'show_pagination' => 'on' ),
			self::mod(
				'et_pb_slide',
				array(
					'heading'          => 'Slide One',
					'button_text'      => 'Learn More',
					'button_link'      => '#',
					'background_image' => self::IMG,
					'_builder_version' => self::BV,
				),
				'Slider child module (et_pb_slide).'
			)
			. self::mod(
				'et_pb_slide',
				array(
					'heading'          => 'Slide Two',
					'background_image' => self::IMG2,
					'_builder_version' => self::BV,
				),
				'Second slide with background image.'
			)
		);

		$video_slider = self::mod(
			'et_pb_video_slider',
			array( '_builder_version' => self::BV ),
			self::mod(
				'et_pb_video_slider_item',
				array(
					'title'            => 'Video Slide 1',
					'src'              => 'https://www.youtube.com/watch?v=FkQu3H5kAF4',
					'_builder_version' => self::BV,
				),
				'',
				true
			)
		);

		$pricing = self::mod(
			'et_pb_pricing_tables',
			array( '_builder_version' => self::BV ),
			self::mod(
				'et_pb_pricing_table',
				array(
					'title'            => 'Starter',
					'subtitle'         => 'Basic',
					'currency'         => '$',
					'sum'              => '29',
					'period'           => '/mo',
					'button_text'      => 'Buy Now',
					'button_url'       => '#',
					'_builder_version' => self::BV,
				),
				'<ul><li>Feature one</li><li>Feature two</li></ul>'
			)
			. self::mod(
				'et_pb_pricing_table',
				array(
					'title'            => 'Pro',
					'subtitle'         => 'Popular',
					'currency'         => '$',
					'sum'              => '59',
					'period'           => '/mo',
					'button_text'      => 'Buy Now',
					'button_url'       => '#',
					'featured'         => 'on',
					'_builder_version' => self::BV,
				),
				'<ul><li>Everything in Starter</li><li>Priority support</li></ul>'
			)
		);

		$bar_counters = self::mod(
			'et_pb_counters',
			array( '_builder_version' => self::BV, 'background_color' => self::LIGHT ),
			self::mod(
				'et_pb_counter',
				array(
					'title'            => 'Design',
					'percent'          => '90',
					'_builder_version' => self::BV,
				),
				'',
				true
			)
			. self::mod(
				'et_pb_counter',
				array(
					'title'            => 'Development',
					'percent'          => '85',
					'_builder_version' => self::BV,
				),
				'',
				true
			)
		);

		$modules = array(
			self::showcase( 'Accordion (et_pb_accordion + et_pb_accordion_item)', $accordion ),
			self::showcase( 'Tabs (et_pb_tabs + et_pb_tab)', $tabs ),
			self::showcase(
				'Toggle (et_pb_toggle)',
				self::mod(
					'et_pb_toggle',
					array(
						'title'            => 'Toggle Title',
						'open'             => 'on',
						'_builder_version' => self::BV,
					),
					'Single expandable toggle module.'
				)
			),
			self::showcase( 'Slider (et_pb_slider + et_pb_slide)', $slider ),
			self::showcase( 'Video Slider (et_pb_video_slider + et_pb_video_slider_item)', $video_slider ),
			self::showcase(
				'Number Counter (et_pb_number_counter)',
				self::mod(
					'et_pb_number_counter',
					array(
						'title'            => 'Projects',
						'number'           => '128',
						'percent_sign'     => 'off',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::showcase( 'Bar Counters (et_pb_counters + et_pb_counter)', $bar_counters ),
			self::showcase(
				'Circle Counter (et_pb_circle_counter)',
				self::mod(
					'et_pb_circle_counter',
					array(
						'title'            => 'Success Rate',
						'number'           => '92',
						'bar_bg_color'     => self::ACCENT,
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::showcase( 'Pricing Tables (et_pb_pricing_tables + et_pb_pricing_table)', $pricing ),
			self::showcase(
				'Testimonial (et_pb_testimonial)',
				self::mod(
					'et_pb_testimonial',
					array(
						'author'           => 'Jane Doe',
						'job_title'        => 'Designer',
						'company_name'     => 'Divi Demo Co',
						'portrait_url'     => self::IMG,
						'_builder_version' => self::BV,
					),
					'“Divi makes layout authoring predictable when you know the shortcode attributes.”'
				)
			),
			self::showcase(
				'Team Member (et_pb_team_member)',
				self::mod(
					'et_pb_team_member',
					array(
						'name'             => 'Alex Builder',
						'position'         => 'Lead Developer',
						'image_url'        => self::IMG2,
						'facebook_url'     => '#',
						'twitter_url'      => '#',
						'_builder_version' => self::BV,
					),
					'Team member module with social links and image.'
				)
			),
			self::showcase(
				'Countdown Timer (et_pb_countdown_timer)',
				self::mod(
					'et_pb_countdown_timer',
					array(
						'date_time'        => gmdate( 'Y-m-d H:i:s', strtotime( '+90 days' ) ),
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::showcase(
				'Social Media Follow (et_pb_social_media_follow + network)',
				self::mod(
					'et_pb_social_media_follow',
					array( '_builder_version' => self::BV, 'icon_color' => self::ACCENT ),
					self::mod(
						'et_pb_social_media_follow_network',
						array(
							'social_network'   => 'facebook',
							'url'              => 'https://facebook.com',
							'_builder_version' => self::BV,
						),
						'',
						true
					)
					. self::mod(
						'et_pb_social_media_follow_network',
						array(
							'social_network'   => 'twitter',
							'url'              => 'https://twitter.com',
							'_builder_version' => self::BV,
						),
						'',
						true
					)
				)
			),
		);

		return self::intro_section( 'Interactive Modules', 'Modules with nested child items or dynamic UI behavior.' )
			. implode( '', $modules );
	}

	private static function page_forms_utility(): string {
		$contact = self::mod(
			'et_pb_contact_form',
			array(
				'title'            => 'Contact Us',
				'email'            => 'admin@example.com',
				'captcha'          => 'off',
				'_builder_version' => self::BV,
			),
			self::mod(
				'et_pb_contact_field',
				array(
					'field_id'         => 'Name',
					'field_title'      => 'Name',
					'field_type'       => 'input',
					'required_mark'    => 'on',
					'fullwidth_field'  => 'on',
					'_builder_version' => self::BV,
				),
				'',
				true
			)
			. self::mod(
				'et_pb_contact_field',
				array(
					'field_id'         => 'Email',
					'field_title'      => 'Email',
					'field_type'       => 'email',
					'required_mark'    => 'on',
					'fullwidth_field'  => 'on',
					'_builder_version' => self::BV,
				),
				'',
				true
			)
		);

		$signup = self::mod(
			'et_pb_signup',
			array(
				'title'            => 'Newsletter',
				'button_text'      => 'Subscribe',
				'provider'         => 'mailchimp',
				'list_id'          => 'demo',
				'_builder_version' => self::BV,
			),
			'Email signup module with provider integration settings.'
		);

		$map = self::mod(
			'et_pb_map',
			array(
				'address'          => 'San Francisco, CA',
				'zoom_level'       => '12',
				'address_lat'      => '37.7749',
				'address_lng'      => '-122.4194',
				'mouse_wheel'      => 'off',
				'_builder_version' => self::BV,
			),
			self::mod(
				'et_pb_map_pin',
				array(
					'title'            => 'Demo Pin',
					'pin_address'      => 'San Francisco, CA',
					'pin_address_lat'  => '37.7749',
					'pin_address_lng'  => '-122.4194',
					'_builder_version' => self::BV,
				),
				'Map pin info window content.'
			)
		);

		$modules = array(
			self::showcase( 'Contact Form (et_pb_contact_form + et_pb_contact_field)', $contact ),
			self::showcase( 'Signup (et_pb_signup)', $signup ),
			self::showcase(
				'Search (et_pb_search)',
				self::mod(
					'et_pb_search',
					array(
						'placeholder'      => 'Search demo site…',
						'button_text'      => 'Search',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::showcase(
				'Login (et_pb_login)',
				self::mod(
					'et_pb_login',
					array(
						'title'            => 'Member Login',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::showcase( 'Map (et_pb_map + et_pb_map_pin)', $map ),
			self::showcase(
				'Menu (et_pb_menu)',
				self::mod(
					'et_pb_menu',
					array(
						'menu_id'          => 'primary-menu',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::showcase(
				'Sidebar (et_pb_sidebar)',
				self::mod(
					'et_pb_sidebar',
					array(
						'area'             => 'sidebar-1',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
		);

		return self::intro_section( 'Forms & Utility', 'Lead capture, search, auth, navigation, and map modules.' )
			. implode( '', $modules );
	}

	private static function page_blog_post_modules(): string {
		$modules = array(
			self::showcase(
				'Blog (et_pb_blog)',
				self::mod(
					'et_pb_blog',
					array(
						'posts_number'     => '3',
						'include_categories' => '',
						'show_thumbnail'   => 'on',
						'show_excerpt'     => 'on',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::showcase(
				'Portfolio (et_pb_portfolio)',
				self::mod(
					'et_pb_portfolio',
					array(
						'posts_number'     => '4',
						'fullwidth'        => 'off',
						'show_title'       => 'on',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::showcase(
				'Filterable Portfolio (et_pb_filterable_portfolio)',
				self::mod(
					'et_pb_filterable_portfolio',
					array(
						'posts_number'     => '6',
						'show_title'       => 'on',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::showcase(
				'Post Slider (et_pb_post_slider)',
				self::mod(
					'et_pb_post_slider',
					array(
						'posts_number'     => '3',
						'show_arrows'      => 'on',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::showcase(
				'Post Title (et_pb_post_title)',
				self::mod(
					'et_pb_post_title',
					array(
						'title'            => 'on',
						'meta'             => 'on',
						'featured_image'   => 'on',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::showcase(
				'Post Content (et_pb_post_content)',
				self::mod(
					'et_pb_post_content',
					array( '_builder_version' => self::BV ),
					'',
					true
				)
			),
			self::showcase(
				'Comments (et_pb_comments)',
				self::mod(
					'et_pb_comments',
					array(
						'show_count'       => 'on',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::showcase(
				'Posts Navigation (et_pb_posts_nav)',
				self::mod(
					'et_pb_posts_nav',
					array(
						'show_prev'        => 'on',
						'show_next'        => 'on',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
		);

		return self::intro_section( 'Blog & Post Modules', 'Archive and single-post modules; some require posts/projects in the library.' )
			. implode( '', $modules );
	}

	private static function page_fullwidth_modules(): string {
		$sections = array(
			self::fullwidth_section(
				self::mod(
					'et_pb_fullwidth_header',
					array(
						'title'            => 'Fullwidth Header',
						'subhead'          => 'et_pb_fullwidth_header in a fullwidth section',
						'text_orientation' => 'center',
						'button_one_text'  => 'Primary',
						'button_one_url'   => '#',
						'background_image' => self::IMG,
						'background_overlay_color' => 'rgba(30,27,75,0.55)',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::fullwidth_section(
				self::mod(
					'et_pb_fullwidth_slider',
					array( '_builder_version' => self::BV, 'show_arrows' => 'on' ),
					self::mod(
						'et_pb_slide',
						array(
							'heading'          => 'Fullwidth Slide',
							'background_image' => self::IMG2,
							'_builder_version' => self::BV,
						),
						'Fullwidth slider uses et_pb_slide children.'
					)
				)
			),
			self::fullwidth_section(
				self::mod(
					'et_pb_fullwidth_image',
					array(
						'src'              => self::IMG,
						'title_text'       => 'Fullwidth Image',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::fullwidth_section(
				self::mod(
					'et_pb_fullwidth_code',
					array( '_builder_version' => self::BV ),
					'<div style="text-align:center;padding:40px;background:#ede9fe;">Fullwidth custom code block</div>'
				)
			),
			self::fullwidth_section(
				self::mod(
					'et_pb_fullwidth_portfolio',
					array(
						'posts_number'     => '4',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::fullwidth_section(
				self::mod(
					'et_pb_fullwidth_post_slider',
					array(
						'posts_number'     => '3',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::fullwidth_section(
				self::mod(
					'et_pb_fullwidth_menu',
					array(
						'menu_id'          => 'primary-menu',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::fullwidth_section(
				self::mod(
					'et_pb_fullwidth_post_title',
					array(
						'title'            => 'on',
						'meta'             => 'on',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::fullwidth_section(
				self::mod(
					'et_pb_fullwidth_post_content',
					array( '_builder_version' => self::BV ),
					'',
					true
				)
			),
		);

		$intro = self::intro_section(
			'Fullwidth Modules',
			'Place fullwidth modules inside <code>[et_pb_section fullwidth="on"]</code>. Slugs are prefixed with <code>et_pb_fullwidth_</code>.'
		);

		return $intro . implode( '', $sections );
	}

	private static function page_woocommerce_modules(): string {
		$modules = array(
			self::showcase(
				'Shop (et_pb_wc_shop)',
				self::mod(
					'et_pb_wc_shop',
					array(
						'type'             => 'recent',
						'posts_number'     => '8',
						'columns_number'   => '4',
						'_builder_version' => self::BV,
					),
					'',
					true
				)
			),
			self::showcase(
				'Products by Category',
				self::text(
					'<p>Browse seeded grocery categories: Fresh Produce, Dairy &amp; Eggs, Pantry, Beverages, Frozen.</p>'
					. '<p><a href="' . self::demo_page_url( 'shop' ) . '">View full shop →</a></p>'
				)
			),
			self::showcase(
				'Cart Products (et_pb_wc_cart_products)',
				self::mod(
					'et_pb_wc_cart_products',
					array( '_builder_version' => self::BV ),
					'',
					true
				)
			),
			self::showcase(
				'Checkout Billing (et_pb_wc_checkout_billing)',
				self::mod(
					'et_pb_wc_checkout_billing',
					array( '_builder_version' => self::BV ),
					'',
					true
				)
			),
		);

		return self::intro_section(
			'WooCommerce Modules',
			'Divi WooCommerce modules wired to the live grocery catalog (20 products, 5 categories). Requires WooCommerce active on this subsite.'
		) . implode( '', $modules );
	}

	private static function page_advanced_settings(): string {
		return self::intro_section(
			'Advanced Builder Settings',
			'Animation, scroll transforms, gradients, borders, shadows, filters, and responsive spacing on standard modules.'
		)
		. self::section(
			self::row(
				self::column(
					'1_2',
					self::heading( 'Animation', 'h3' )
					. self::blurb(
						'Fade Up',
						'Uses animation_style, animation_direction, animation_duration, animation_delay.',
						'%%3%%',
						'',
						array(
							'animation_style'     => 'fade',
							'animation_direction' => 'bottom',
							'animation_duration'  => '800ms',
							'animation_delay'     => '100ms',
						)
					)
				)
				. self::column(
					'1_2',
					self::heading( 'Scroll Effects', 'h3' )
					. self::text(
						'<p>Scroll module settings: vertical motion, opacity, blur, scale, rotate.</p>',
						array(
							'scroll_vertical_motion_enable' => 'on',
							'scroll_vertical_motion'        => '-2|2',
							'scroll_motion_trigger_start'   => 'middle',
							'background_color'              => self::LIGHT,
							'custom_padding'                => '24px|24px|24px|24px',
						)
					)
				),
				'1_2,1_2'
			)
		)
		. self::section(
			self::row(
				self::column(
					'1_3',
					self::mod(
						'et_pb_blurb',
						array(
							'title'            => 'Gradient BG',
							'use_icon'         => 'on',
							'font_icon'        => '%%3%%',
							'_builder_version' => self::BV,
							'use_background_color_gradient' => 'on',
							'background_color' => '#7c3aed',
							'background_color_gradient_stops' => '#7c3aed 0%|#a78bfa 100%',
							'header_font_color'=> self::WHITE,
							'body_font_color'  => '#ede9fe',
						),
						'Linear gradient background via background_color_gradient_stops.'
					)
				)
				. self::column(
					'1_3',
					self::mod(
						'et_pb_blurb',
						array(
							'title'            => 'Border & Radius',
							'use_icon'         => 'on',
							'font_icon'        => '%%47%%',
							'_builder_version' => self::BV,
							'border_radii'     => 'on|16px|16px|16px|16px',
							'border_width_all' => '2px',
							'border_color_all' => self::ACCENT,
						),
						'Border width, color, and corner radii.'
					)
				)
				. self::column(
					'1_3',
					self::mod(
						'et_pb_blurb',
						array(
							'title'            => 'Box Shadow',
							'use_icon'         => 'on',
							'font_icon'        => '%%48%%',
							'_builder_version' => self::BV,
							'box_shadow_style' => 'preset2',
							'box_shadow_vertical' => '12px',
							'box_shadow_blur'  => '24px',
							'box_shadow_color' => 'rgba(124,58,237,0.25)',
						),
						'Preset and custom box shadow controls.'
					)
				),
				'1_3,1_3,1_3',
				array( 'make_equal' => 'on' )
			),
			array(
				'custom_padding' => '30px|0|30px|0',
			)
		)
		. self::section(
			self::row(
				self::column(
					'4_4',
					self::heading( 'Responsive Spacing & Sizing', 'h3' )
					. self::text(
						'<p><code>custom_padding</code> and <code>custom_margin</code> accept desktop values and can be overridden per breakpoint in the builder. <code>max_width</code> constrains module width.</p>',
						array(
							'custom_padding' => '32px|48px|32px|48px',
							'max_width'      => '720px',
							'module_alignment' => 'center',
							'background_color' => self::LIGHT,
						)
					)
				)
			)
		);
	}

	private static function intro_section( string $title, string $description ): string {
		return self::section(
			self::row(
				self::column(
					'4_4',
					self::heading( $title, 'h1' )
					. self::text( '<p>' . $description . '</p>' )
				)
			),
			array(
				'background_color' => self::LIGHT,
				'custom_padding'   => '40px|0|40px|0',
			)
		);
	}

	private static function showcase( string $label, string $module_markup ): string {
		return self::section(
			self::row(
				self::column(
					'4_4',
					self::heading( $label, 'h3' )
					. $module_markup
				)
			),
			array( 'custom_padding' => '10px|0|20px|0' )
		);
	}

	private static function blurb(
		string $title,
		string $body,
		string $icon,
		string $url = '',
		array $extra = array()
	): string {
		$attrs = array_merge(
			array(
				'title'            => $title,
				'use_icon'         => 'on',
				'font_icon'        => $icon,
				'icon_color'       => self::ACCENT,
				'_builder_version' => self::BV,
				'background_color' => self::WHITE,
				'custom_padding'   => '24px|24px|24px|24px',
				'border_radii'     => 'on|8px|8px|8px|8px',
			),
			$extra
		);

		if ( '' !== $url ) {
			$attrs['url'] = $url;
		}

		return self::mod( 'et_pb_blurb', $attrs, $body );
	}

	private static function heading( string $text, string $level = 'h2' ): string {
		return self::mod(
			'et_pb_heading',
			array(
				'title'            => $text,
				'header_level'     => $level,
				'_builder_version' => self::BV,
			)
		);
	}

	private static function text( string $html, array $attrs = array() ): string {
		return self::mod(
			'et_pb_text',
			array_merge( array( '_builder_version' => self::BV ), $attrs ),
			$html
		);
	}

	private static function section( string $content, array $attrs = array() ): string {
		$defaults = array(
			'fb_built'         => '1',
			'_builder_version' => self::BV,
			'custom_padding'   => '0|0|0|0',
		);

		return '[et_pb_section ' . self::attrs( array_merge( $defaults, $attrs ) ) . ']' . $content . '[/et_pb_section]';
	}

	private static function fullwidth_section( string $content, array $attrs = array() ): string {
		return self::section(
			$content,
			array_merge(
				array(
					'fullwidth'      => 'on',
					'custom_padding' => '0|0|0|0',
				),
				$attrs
			)
		);
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

	/** @param array<string, scalar> $attrs */
	private static function mod(
		string $slug,
		array $attrs,
		string $content = '',
		bool $self_closing = false
	): string {
		if ( $self_closing || '' === $content ) {
			return '[' . $slug . ' ' . self::attrs( $attrs ) . ' /]';
		}

		return '[' . $slug . ' ' . self::attrs( $attrs ) . ']' . $content . '[/' . $slug . ']';
	}

	/** @param array<string, scalar|null> $attrs */
	private static function attrs( array $attrs ): string {
		$parts = array();
		foreach ( $attrs as $key => $value ) {
			if ( null === $value || '' === $value ) {
				continue;
			}
			$parts[] = $key . '="' . self::esc_attr_value( (string) $value ) . '"';
		}

		return implode( ' ', $parts );
	}

	private static function esc_attr_value( string $value ): string {
		if ( function_exists( 'esc_attr' ) ) {
			return esc_attr( $value );
		}

		return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
	}
}
