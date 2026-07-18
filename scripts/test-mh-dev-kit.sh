#!/usr/bin/env bash
# Run MH Dev Kit PHPUnit tests inside DDEV and generate test-results reports.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PLUGIN_REL="wp-content/plugins/mh-dev-kit"
RESULTS_DIR="$ROOT/test-results/mh-dev-kit"
JUNIT_FILE="$RESULTS_DIR/junit.xml"
OUTPUT_LOG="$RESULTS_DIR/output.log"

run_ddev() {
	if docker info >/dev/null 2>&1; then
		ddev exec "$1"
	elif getent group docker | grep -qE "[[:space:]]${USER}(,|$)"; then
		sg docker -c "cd '$ROOT' && ddev exec \"$1\""
	else
		echo "ERROR: Docker not available. See docs/setup.md"
		exit 1
	fi
}

mkdir -p "$RESULTS_DIR"

echo "Installing Composer dev dependencies..."
run_ddev "cd ${PLUGIN_REL} && composer install --no-interaction"

echo "Ensuring WordPress test library..."
run_ddev "bash ${PLUGIN_REL}/tests/bin/install-wp-tests.sh db db db db 7.0 skip || bash ${PLUGIN_REL}/tests/bin/install-wp-tests.sh db db db db latest skip"

echo "Running PHPUnit..."
set +e
run_ddev "cd ${PLUGIN_REL} && vendor/bin/phpunit --configuration phpunit.xml.dist --log-junit /var/www/html/test-results/mh-dev-kit/junit.xml" 2>&1 | tee "$OUTPUT_LOG"
TEST_EXIT=${PIPESTATUS[0]}
set -e

echo "Generating structured reports..."
bash "$ROOT/scripts/generate-mh-dev-kit-test-report.sh" "$JUNIT_FILE" "$OUTPUT_LOG" "$TEST_EXIT"

exit "$TEST_EXIT"
