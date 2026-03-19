#!/usr/bin/env bash
# run-tests.sh – Convenience script to run the MC plugin test suite.
#
# Usage:
#   bash tests/run-tests.sh
#
# Environment variables:
#   WP_TESTS_DIR  – Path to WordPress test library (default: /tmp/wordpress-tests-lib)
#   WP_CORE_DIR   – Path to WordPress installation (default: /tmp/wordpress)

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

echo "🧪 Starting MC Plugin Test Suite..."
echo ""

# ── 1. PHP Code Standards ────────────────────────────────────────────────────
if [ -f "${REPO_ROOT}/vendor/bin/phpcs" ]; then
	echo "🔍 Running PHPCS..."
	"${REPO_ROOT}/vendor/bin/phpcs" \
		--standard="${REPO_ROOT}/.phpcs.xml.dist" \
		"${REPO_ROOT}/mc-remote-api/includes" \
		"${REPO_ROOT}/mc-woo-remote-automations/includes"
	echo "✅ PHPCS passed"
	echo ""
else
	echo "⚠️  PHPCS not found – skipping (run: composer install)"
	echo ""
fi

# ── 2. Unit Tests ─────────────────────────────────────────────────────────────
echo "🧪 Running Unit Tests..."
phpunit --bootstrap "${REPO_ROOT}/mc-remote-api/tests/bootstrap.php" \
	--testsuite unit \
	"${REPO_ROOT}/mc-remote-api/tests"
echo ""

phpunit --bootstrap "${REPO_ROOT}/mc-woo-remote-automations/tests/bootstrap.php" \
	--testsuite unit \
	"${REPO_ROOT}/mc-woo-remote-automations/tests"
echo ""

# ── 3. Integration Tests ──────────────────────────────────────────────────────
echo "🔗 Running Integration Tests..."
phpunit --bootstrap "${REPO_ROOT}/tests/bootstrap.php" \
	--testsuite integration \
	"${REPO_ROOT}/tests/integration"
echo ""

# ── 4. Coverage Report ────────────────────────────────────────────────────────
if [ "${GENERATE_COVERAGE:-false}" = "true" ]; then
	echo "📊 Generating Coverage Report..."
	phpunit --bootstrap "${REPO_ROOT}/tests/bootstrap.php" \
		--coverage-html "${REPO_ROOT}/coverage/"
	echo "✅ Coverage report saved to coverage/"
	echo ""
fi

echo "✅ All tests complete!"
