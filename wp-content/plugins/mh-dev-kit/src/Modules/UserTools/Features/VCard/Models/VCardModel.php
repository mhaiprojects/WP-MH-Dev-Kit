<?php
/**
 * vCard data model.
 *
 * @package MH\DevKit\Modules\UserTools\Features\VCard\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools\Features\VCard\Models;

use MH\DevKit\Shared\UserScopedCptHelper;

final class VCardModel {

	public const POST_TYPE = 'mhdk_vcard';

	public const FIELDS = array(
		'first_name' => 'mhdk_vcard_first_name',
		'last_name'  => 'mhdk_vcard_last_name',
		'org'        => 'mhdk_vcard_org',
		'title'      => 'mhdk_vcard_title',
		'phone'      => 'mhdk_vcard_phone',
		'email'      => 'mhdk_vcard_email',
		'url'        => 'mhdk_vcard_url',
		'address'    => 'mhdk_vcard_address',
	);

	/**
	 * @return array<string, mixed>
	 */
	public function get_post_type_args(): array {
		return UserScopedCptHelper::author_caps_args(
			__( 'vCard', 'mh-dev-kit' ),
			__( 'vCards', 'mh-dev-kit' ),
			'dashicons-id-alt'
		);
	}

	/**
	 * @return array<string, string>
	 */
	public function get_fields( int $post_id ): array {
		$data = array();

		foreach ( self::FIELDS as $key => $meta_key ) {
			$data[ $key ] = (string) get_post_meta( $post_id, $meta_key, true );
		}

		return $data;
	}

	public function build_vcf( int $post_id ): string {
		$fields = $this->get_fields( $post_id );
		$title  = get_the_title( $post_id );

		$lines = array(
			'BEGIN:VCARD',
			'VERSION:3.0',
			'FN:' . $this->escape( trim( $fields['first_name'] . ' ' . $fields['last_name'] ) ?: $title ),
			'N:' . $this->escape( $fields['last_name'] ) . ';' . $this->escape( $fields['first_name'] ) . ';;;',
		);

		if ( '' !== $fields['org'] ) {
			$lines[] = 'ORG:' . $this->escape( $fields['org'] );
		}

		if ( '' !== $fields['title'] ) {
			$lines[] = 'TITLE:' . $this->escape( $fields['title'] );
		}

		if ( '' !== $fields['phone'] ) {
			$lines[] = 'TEL;TYPE=CELL:' . $this->escape( $fields['phone'] );
		}

		if ( '' !== $fields['email'] ) {
			$lines[] = 'EMAIL;TYPE=INTERNET:' . $this->escape( $fields['email'] );
		}

		if ( '' !== $fields['url'] ) {
			$lines[] = 'URL:' . $this->escape( $fields['url'] );
		}

		if ( '' !== $fields['address'] ) {
			$lines[] = 'ADR;TYPE=HOME:;;' . $this->escape( $fields['address'] ) . ';;;;';
		}

		$lines[] = 'END:VCARD';

		return implode( "\r\n", $lines );
	}

	private function escape( string $value ): string {
		return str_replace( array( "\r", "\n", ';', ',' ), array( '', '', '\\;', '\\,' ), $value );
	}
}
