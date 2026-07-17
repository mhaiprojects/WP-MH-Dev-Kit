<?php
/**
 * Admin menu slugs and labels.
 *
 * @package MH\DevKit\Core\Admin
 */

declare(strict_types=1);

namespace MH\DevKit\Core\Admin;

final class AdminMenu {

	public const SETTINGS_SLUG = 'mhdk-settings';

	/** Core module content hub submenu slug (under MHDK Settings). */
	public const CORE_CONTENT_SLUG = 'mhdk-core-content';

	/** Commerce module content submenu slug (under MHDK Settings). */
	public const COMMERCE_CONTENT_SLUG = 'mhdk-commerce-content';

	public const BLOCK_CATEGORY = 'mhdk';

	public const TEXT_DOMAIN = 'mh-dev-kit';
}
