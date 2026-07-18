#!/usr/bin/env bash
# Install WordPress test library for MH Dev Kit (DDEV-compatible, no svn required).
set -euo pipefail

if [ $# -lt 3 ]; then
	echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation]"
	exit 1
fi

DB_NAME="$1"
DB_USER="$2"
DB_PASSWORD="$3"
DB_HOST="${4-db}"
WP_VERSION="${5-latest}"
SKIP_DB_CREATE="${6-}"

WP_TESTS_DIR="${WP_TESTS_DIR-/tmp/wordpress-tests-lib}"

download() {
	if command -v curl >/dev/null 2>&1; then
		curl -fsSL "$1"
	elif command -v wget >/dev/null 2>&1; then
		wget -q -O - "$1"
	else
		echo "curl or wget required"
		exit 1
	fi
}

install_db() {
	if [ -n "$SKIP_DB_CREATE" ]; then
		return 0
	fi

	if ! mysql --host="$DB_HOST" --user="$DB_USER" --password="$DB_PASSWORD" \
		-e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;"; then
		echo "Could not create test database ${DB_NAME}"
		exit 1
	fi
}

install_test_suite() {
	if [ -f "$WP_TESTS_DIR/includes/functions.php" ]; then
		echo "WordPress test library already present at ${WP_TESTS_DIR}"
		return 0
	fi

	mkdir -p "$WP_TESTS_DIR"
	local tmp
	tmp="$(mktemp -d)"

	if command -v svn >/dev/null 2>&1; then
		if [ "$WP_VERSION" = "latest" ] || [ "$WP_VERSION" = "trunk" ]; then
			svn co --quiet https://develop.svn.wordpress.org/trunk/tests/phpunit/ "$WP_TESTS_DIR"
		else
			svn co --quiet "https://develop.svn.wordpress.org/tags/${WP_VERSION}/tests/phpunit/" "$WP_TESTS_DIR"
		fi
		rm -rf "$tmp"
		return 0
	fi

	echo "Downloading WordPress PHPUnit library (version: ${WP_VERSION})..."
	local archive_url=""
	if [ "$WP_VERSION" = "latest" ] || [ "$WP_VERSION" = "trunk" ]; then
		archive_url="https://codeload.github.com/WordPress/wordpress-develop/tar.gz/refs/heads/trunk"
	else
		archive_url="https://codeload.github.com/WordPress/wordpress-develop/tar.gz/refs/tags/${WP_VERSION}"
	fi

	if ! download "$archive_url" | tar xz -C "$tmp" --strip-components=2 --wildcards '*/tests/phpunit' 2>/dev/null; then
		echo "Tagged archive unavailable; falling back to trunk..."
		download "https://codeload.github.com/WordPress/wordpress-develop/tar.gz/refs/heads/trunk" \
			| tar xz -C "$tmp" --strip-components=2 --wildcards '*/tests/phpunit'
	fi

	if [ ! -f "$tmp/includes/functions.php" ]; then
		# Tar layout varies; try full extract then move.
		rm -rf "$tmp"/*
		download "https://codeload.github.com/WordPress/wordpress-develop/tar.gz/refs/heads/trunk" \
			| tar xz -C "$tmp" --strip-components=1
		local extracted
		extracted="$(find "$tmp" -type d -path '*/tests/phpunit' | head -1)"
		if [ -z "$extracted" ] || [ ! -f "$extracted/includes/functions.php" ]; then
			echo "Failed to extract WordPress PHPUnit library"
			exit 1
		fi
		cp -a "$extracted/." "$WP_TESTS_DIR/"
	else
		cp -a "$tmp/." "$WP_TESTS_DIR/"
	fi

	rm -rf "$tmp"
}

install_db
install_test_suite

echo "WordPress test library installed at ${WP_TESTS_DIR}"
