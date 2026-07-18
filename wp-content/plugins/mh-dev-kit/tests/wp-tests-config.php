<?php
/**
 * WordPress test suite database configuration (DDEV defaults).
 *
 * Uses the shared `db` database with the `wptests_` table prefix so no
 * separate test database is required in DDEV.
 *
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

$wp_root = getenv( 'WP_ROOT_DIR' ) ?: '/var/www/html';

define( 'ABSPATH', rtrim( $wp_root, '/' ) . '/' );
define( 'WP_DEFAULT_THEME', 'default' );

define( 'DB_NAME', getenv( 'WP_TESTS_DB_NAME' ) ?: 'db' );
define( 'DB_USER', getenv( 'WP_TESTS_DB_USER' ) ?: 'db' );
define( 'DB_PASSWORD', getenv( 'WP_TESTS_DB_PASSWORD' ) ?: 'db' );
define( 'DB_HOST', getenv( 'WP_TESTS_DB_HOST' ) ?: 'db' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

define( 'AUTH_KEY', 'test-auth-key' );
define( 'SECURE_AUTH_KEY', 'test-secure-auth-key' );
define( 'LOGGED_IN_KEY', 'test-logged-in-key' );
define( 'NONCE_KEY', 'test-nonce-key' );
define( 'AUTH_SALT', 'test-auth-salt' );
define( 'SECURE_AUTH_SALT', 'test-secure-auth-salt' );
define( 'LOGGED_IN_SALT', 'test-logged-in-salt' );
define( 'NONCE_SALT', 'test-nonce-salt' );

$table_prefix = 'wptests_';

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'MH Dev Kit Tests' );

define( 'WP_PHP_BINARY', 'php' );
define( 'WPLANG', '' );

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', true );
define( 'WP_CACHE', false );

define( 'WP_TESTS_FORCE_KNOWN_BUGS', true );
