#!/usr/bin/env php
<?php
/**
 * Extract Divi Builder module metadata from theme PHP for docs and agent skills.
 *
 * Usage: php scripts/extract-divi-module-reference.php [--out=path.json]
 *
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

$root     = dirname( __DIR__ );
$moduleDir = $root . '/wp-content/themes/Divi/includes/builder/module';
$wooDir   = $moduleDir . '/woocommerce';

if ( ! is_dir( $moduleDir ) ) {
	fwrite( STDERR, "Divi module directory not found: {$moduleDir}\n" );
	exit( 1 );
}

/** @return list<string> */
function divi_demo_module_files( string $dir ): array {
	$files = glob( $dir . '/*.php' ) ?: array();
	sort( $files );
	return $files;
}

/** @return array<string, mixed> */
function divi_demo_parse_module_file( string $file ): array {
	$content = file_get_contents( $file );
	if ( false === $content ) {
		return array();
	}

	$basename = basename( $file, '.php' );
	$data     = array(
		'class_file' => $basename,
		'slug'       => '',
		'name'       => '',
		'plural'     => '',
		'child_slug' => null,
		'type'       => 'parent',
		'fullwidth'  => false,
		'vb_support' => '',
		'settings_modal_toggles' => array(),
		'advanced_field_groups'  => array(),
	);

	if ( preg_match( "/\\\$this->slug\s*=\s*'([^']+)'/", $content, $m ) ) {
		$data['slug'] = $m[1];
	}
	if ( preg_match( "/\\\$this->name\s*=\s*esc_html__\(\s*'([^']+)'/", $content, $m ) ) {
		$data['name'] = $m[1];
	}
	if ( preg_match( "/\\\$this->plural\s*=\s*esc_html__\(\s*'([^']+)'/", $content, $m ) ) {
		$data['plural'] = $m[1];
	}
	if ( preg_match( "/\\\$this->child_slug\s*=\s*'([^']+)'/", $content, $m ) ) {
		$data['child_slug'] = $m[1];
	}
	if ( preg_match( "/\\\$this->type\s*=\s*'child'/", $content ) ) {
		$data['type'] = 'child';
	}
	if ( preg_match( "/\\\$this->fullwidth\s*=\s*true/", $content ) ) {
		$data['fullwidth'] = true;
	}
	if ( preg_match( "/\\\$this->vb_support\s*=\s*'([^']+)'/", $content, $m ) ) {
		$data['vb_support'] = $m[1];
	}

	if ( preg_match( "/\\\$this->settings_modal_toggles\s*=\s*array\s*\(([\s\S]*?)\n\t\t\);/m", $content, $m ) ) {
		$block = $m[1];
		if ( preg_match_all( "/'(\w+)'\s*=>\s*array\s*\(\s*'toggles'\s*=>\s*array\s*\(([\s\S]*?)\n\t\t\t\t\),/m", $block, $groups, PREG_SET_ORDER ) ) {
			foreach ( $groups as $group ) {
				$tab = $group[1];
				preg_match_all( "/'(\w+)'\s*=>/", $group[2], $toggles );
				$data['settings_modal_toggles'][ $tab ] = $toggles[1] ?? array();
			}
		}
	}

	if ( preg_match( "/\\\$this->advanced_fields\s*=\s*array\s*\(([\s\S]*?)\n\t\t\);/m", $content, $m ) ) {
		preg_match_all( "/^\s*'(\w+)'\s*=>/m", $m[1], $fields );
		$data['advanced_field_groups'] = $fields[1] ?? array();
	}

	return $data;
}

/** @return array<string, mixed> */
function divi_demo_build_catalog( string $moduleDir, string $wooDir ): array {
	$modules = array();

	foreach ( divi_demo_module_files( $moduleDir ) as $file ) {
		$parsed = divi_demo_parse_module_file( $file );
		if ( '' === $parsed['slug'] ) {
			continue;
		}
		$modules[ $parsed['slug'] ] = $parsed;
	}

	$woocommerce = array();
	if ( is_dir( $wooDir ) ) {
		foreach ( divi_demo_module_files( $wooDir ) as $file ) {
			$parsed = divi_demo_parse_module_file( $file );
			if ( '' === $parsed['slug'] ) {
				continue;
			}
			$parsed['woocommerce'] = true;
			$woocommerce[ $parsed['slug'] ] = $parsed;
		}
	}

	$row_structures = array(
		'4_4'           => '1 column (100%)',
		'1_2,1_2'       => '2 equal columns',
		'1_3,1_3,1_3'   => '3 equal columns',
		'1_4,1_4,1_4,1_4' => '4 equal columns',
		'2_3,1_3'       => '2/3 + 1/3',
		'1_3,2_3'       => '1/3 + 2/3',
		'1_4,3_4'       => '1/4 + 3/4',
		'3_4,1_4'       => '3/4 + 1/4',
		'1_2,1_4,1_4'   => '1/2 + 1/4 + 1/4',
		'1_4,1_4,1_2'   => '1/4 + 1/4 + 1/2',
	);

	$column_types = array( '4_4', '1_2', '1_3', '1_4', '2_3', '3_4' );

	$shared_advanced = array(
		'background', 'borders', 'box_shadow', 'filters', 'fonts', 'margin_padding',
		'max_width', 'height', 'scroll', 'animation', 'transform', 'link_options',
		'button', 'form_field', 'text_shadow', 'z_index', 'position_fields',
	);

	return array(
		'generated_at'     => gmdate( 'c' ),
		'divi_theme_path'  => 'wp-content/themes/Divi',
		'builder_version'  => '4.27.7',
		'structure'        => array(
			'section' => 'et_pb_section',
			'row'     => 'et_pb_row',
			'column'  => 'et_pb_column',
			'row_structures' => $row_structures,
			'column_types'   => $column_types,
			'row_attributes' => array(
				'column_structure', 'make_equal', 'use_custom_gutter', 'gutter_width',
				'custom_padding', 'custom_margin', 'background_color', 'background_image',
				'parallax', 'parallax_method', 'width', 'max_width', 'module_id', 'module_class',
			),
			'section_attributes' => array(
				'fullwidth', 'specialty', 'background_color', 'background_image',
				'background_video_mp4', 'use_background_color_gradient',
				'custom_padding', 'custom_margin', 'inner_shadow', 'parallax',
				'divider', 'divider_color', 'divider_position', 'z_index',
			),
			'column_attributes' => array(
				'type', 'custom_padding', 'custom_margin', 'background_color',
				'parallax', 'css_main_element',
			),
		),
		'shared_advanced_field_groups' => $shared_advanced,
		'modules'          => $modules,
		'woocommerce_modules' => $woocommerce,
		'module_count'     => count( $modules ),
		'woocommerce_module_count' => count( $woocommerce ),
	);
}

$outArg = null;
foreach ( array_slice( $argv, 1 ) as $arg ) {
	if ( str_starts_with( $arg, '--out=' ) ) {
		$outArg = substr( $arg, 6 );
	}
}

$catalog = divi_demo_build_catalog( $moduleDir, $wooDir );
$json    = json_encode( $catalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n";

$defaultOuts = array(
	$root . '/docs/divi/module-catalog.json',
	$root . '/.cursor/skills/divi-builder/module-catalog.json',
);

if ( $outArg ) {
	$defaultOuts = array( $outArg );
}

foreach ( $defaultOuts as $out ) {
	$dir = dirname( $out );
	if ( ! is_dir( $dir ) ) {
		mkdir( $dir, 0755, true );
	}
	file_put_contents( $out, $json );
	echo "Wrote {$out}\n";
}

echo sprintf(
	"Catalog: %d modules, %d WooCommerce modules\n",
	$catalog['module_count'],
	$catalog['woocommerce_module_count']
);
