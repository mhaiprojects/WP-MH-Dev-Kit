<?php
/**
 * Royalty-free media catalog and WordPress sideload for adamat subsite.
 *
 * Sources: Unsplash (https://unsplash.com/license) — download and self-host.
 *
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

final class AdamatMediaAssets {

	private const OPTION_KEY = 'adamat_media_map';

	/**
	 * @return array<string, array{url:string,file:string,alt:string,source:string,credit:string}>
	 */
	public static function catalog(): array {
		$unsplash = static fn( string $id, int $w = 1600 ): string => sprintf(
			'https://images.unsplash.com/%s?auto=format&fit=crop&w=%d&q=80',
			$id,
			$w
		);

		return array(
			'hero'                  => array(
				'url'    => $unsplash( 'photo-1551288049-bebda4e38f71', 1920 ),
				'file'   => 'adamat-hero-analytics.jpg',
				'alt'    => 'Team reviewing analytics dashboards on screen',
				'source' => 'Unsplash',
				'credit' => 'Lukas Blazek / Unsplash',
			),
			'about-team'            => array(
				'url'    => $unsplash( 'photo-1522071820081-009f0129c71c', 1200 ),
				'file'   => 'adamat-about-team.jpg',
				'alt'    => 'Collaborative team planning around a laptop',
				'source' => 'Unsplash',
				'credit' => 'Unsplash',
			),
			'contact'               => array(
				'url'    => $unsplash( 'photo-1521791136064-7986c2920216', 1200 ),
				'file'   => 'adamat-contact.jpg',
				'alt'    => 'Professional team ready to help clients',
				'source' => 'Unsplash',
				'credit' => 'Unsplash',
			),
			'service-web-design'    => array(
				'url'    => $unsplash( 'photo-1467232004584-a241de8bcf5d', 800 ),
				'file'   => 'adamat-service-web-design.jpg',
				'alt'    => 'Modern website layout on desktop monitor',
				'source' => 'Unsplash',
				'credit' => 'Unsplash',
			),
			'service-ecommerce'     => array(
				'url'    => $unsplash( 'photo-1556742049-0cfed4f6a45d', 800 ),
				'file'   => 'adamat-service-ecommerce.jpg',
				'alt'    => 'Mobile payment and online shopping',
				'source' => 'Unsplash',
				'credit' => 'Unsplash',
			),
			'service-ai-services'   => array(
				'url'    => $unsplash( 'photo-1677442136019-21780ecad995', 800 ),
				'file'   => 'adamat-service-ai.jpg',
				'alt'    => 'Abstract artificial intelligence visualization',
				'source' => 'Unsplash',
				'credit' => 'Unsplash',
			),
			'service-pim'           => array(
				'url'    => $unsplash( 'photo-1553877522-43269d4ea984', 800 ),
				'file'   => 'adamat-service-pim.jpg',
				'alt'    => 'Business team reviewing structured product data',
				'source' => 'Unsplash',
				'credit' => 'Unsplash',
			),
			'service-hosting'       => array(
				'url'    => $unsplash( 'photo-1558494949-ef010cbdcc31', 800 ),
				'file'   => 'adamat-service-hosting.jpg',
				'alt'    => 'Server room and cloud hosting infrastructure',
				'source' => 'Unsplash',
				'credit' => 'Unsplash',
			),
			'service-graphic-design'=> array(
				'url'    => $unsplash( 'photo-1561070791-2526d30994b5', 800 ),
				'file'   => 'adamat-service-design.jpg',
				'alt'    => 'Designer working on brand and color palette',
				'source' => 'Unsplash',
				'credit' => 'Unsplash',
			),
			'project-corporate'     => array(
				'url'    => $unsplash( 'photo-1498050108023-c5249f4df085', 800 ),
				'file'   => 'adamat-project-corporate.jpg',
				'alt'    => 'Developer laptop with code editor open',
				'source' => 'Unsplash',
				'credit' => 'Unsplash',
			),
			'project-ecommerce'     => array(
				'url'    => $unsplash( 'photo-1472851294608-062f824d29cc', 800 ),
				'file'   => 'adamat-project-ecommerce.jpg',
				'alt'    => 'Retail storefront and online commerce',
				'source' => 'Unsplash',
				'credit' => 'Unsplash',
			),
			'project-ai-content'    => array(
				'url'    => $unsplash( 'photo-1620712943543-bcc4688e7485', 800 ),
				'file'   => 'adamat-project-ai.jpg',
				'alt'    => 'AI and automation technology concept',
				'source' => 'Unsplash',
				'credit' => 'Unsplash',
			),
			'project-pim'           => array(
				'url'    => $unsplash( 'photo-1551288049-bebda4e38f71', 800 ),
				'file'   => 'adamat-project-pim.jpg',
				'alt'    => 'Data dashboard for product catalog management',
				'source' => 'Unsplash',
				'credit' => 'Lukas Blazek / Unsplash',
			),
			'project-brand'         => array(
				'url'    => $unsplash( 'photo-1558655146-d09347e92766', 800 ),
				'file'   => 'adamat-project-brand.jpg',
				'alt'    => 'Brand identity and design materials',
				'source' => 'Unsplash',
				'credit' => 'Unsplash',
			),
			'project-hosting'       => array(
				'url'    => $unsplash( 'photo-1451187580459-43490279c0fa', 800 ),
				'file'   => 'adamat-project-hosting.jpg',
				'alt'    => 'Global network and hosting connectivity',
				'source' => 'Unsplash',
				'credit' => 'Unsplash',
			),
		);
	}

	public static function seed(): void {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		foreach ( self::catalog() as $key => $asset ) {
			self::ensure( $key, $asset );
		}
	}

	public static function url( string $key ): string {
		$map = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $map ) || empty( $map[ $key ] ) ) {
			return '';
		}

		$url = wp_get_attachment_url( (int) $map[ $key ] );

		return is_string( $url ) ? $url : '';
	}

	public static function attachment_id( string $key ): int {
		$map = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $map ) || empty( $map[ $key ] ) ) {
			return 0;
		}

		return (int) $map[ $key ];
	}

	/**
	 * @param array{url:string,file:string,alt:string,source:string,credit:string} $asset
	 */
	private static function ensure( string $key, array $asset ): void {
		$map = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $map ) ) {
			$map = array();
		}

		if ( ! empty( $map[ $key ] ) ) {
			$existing = get_post( (int) $map[ $key ] );
			if ( $existing instanceof WP_Post && 'attachment' === $existing->post_type ) {
				return;
			}
		}

		$attachment_id = self::sideload( $asset );
		if ( $attachment_id > 0 ) {
			$map[ $key ] = $attachment_id;
			update_option( self::OPTION_KEY, $map, false );
		}
	}

	/**
	 * @param array{url:string,file:string,alt:string,source:string,credit:string} $asset
	 */
	private static function sideload( array $asset ): int {
		$tmp = download_url( $asset['url'], 30 );
		if ( is_wp_error( $tmp ) ) {
			if ( class_exists( 'WP_CLI' ) ) {
				WP_CLI::warning( 'Media download failed for ' . $asset['file'] . ': ' . $tmp->get_error_message() );
			}
			return 0;
		}

		$file_array = array(
			'name'     => $asset['file'],
			'tmp_name' => $tmp,
		);

		$attachment_id = media_handle_sideload(
			$file_array,
			0,
			$asset['alt'],
			array(
				'post_content' => sprintf(
					'Source: %s — %s. Royalty-free for commercial use; see docs/wordpress/royalty-free-assets.md.',
					$asset['source'],
					$asset['credit']
				),
			)
		);

		if ( is_wp_error( $attachment_id ) ) {
			wp_delete_file( $tmp );
			if ( class_exists( 'WP_CLI' ) ) {
				WP_CLI::warning( 'Media sideload failed for ' . $asset['file'] . ': ' . $attachment_id->get_error_message() );
			}
			return 0;
		}

		update_post_meta( (int) $attachment_id, '_wp_attachment_image_alt', $asset['alt'] );

		return (int) $attachment_id;
	}
}
