<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Unit\UserTools;

use MH\DevKit\Modules\UserTools\Features\VCard\Models\VCardModel;
use MH\DevKit\Tests\TestCase;

final class VCardModelTest extends TestCase {

	private VCardModel $model;

	protected function setUp(): void {
		parent::setUp();
		$this->model = new VCardModel();
	}

	public function test_build_vcf_includes_core_fields(): void {
		$post_id = self::factory()->post->create(
			array(
				'post_type'  => VCardModel::POST_TYPE,
				'post_title' => 'Jane Example',
			)
		);

		update_post_meta( $post_id, VCardModel::FIELDS['first_name'], 'Jane' );
		update_post_meta( $post_id, VCardModel::FIELDS['last_name'], 'Example' );
		update_post_meta( $post_id, VCardModel::FIELDS['email'], 'jane@example.com' );

		$vcf = $this->model->build_vcf( $post_id );

		$this->assertStringContainsString( 'BEGIN:VCARD', $vcf );
		$this->assertStringContainsString( 'FN:Jane Example', $vcf );
		$this->assertStringContainsString( 'EMAIL;TYPE=INTERNET:jane@example.com', $vcf );
		$this->assertStringContainsString( 'END:VCARD', $vcf );
	}
}
