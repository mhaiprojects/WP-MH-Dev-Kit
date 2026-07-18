#!/usr/bin/env bash
# Parse PHPUnit JUnit XML into per-module test-results reports.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
JUNIT_FILE="${1:-$ROOT/test-results/mh-dev-kit/junit.xml}"
OUTPUT_LOG="${2:-$ROOT/test-results/mh-dev-kit/output.log}"
TEST_EXIT="${3:-0}"
RESULTS_ROOT="$ROOT/test-results"

if [ ! -f "$JUNIT_FILE" ]; then
	echo "JUnit file not found: $JUNIT_FILE"
	exit 1
fi

export JUNIT_FILE OUTPUT_LOG TEST_EXIT RESULTS_ROOT
python3 <<'PY'
import os
import xml.etree.ElementTree as ET
from datetime import datetime, timezone
from pathlib import Path

junit_file = Path(os.environ["JUNIT_FILE"])
results_root = Path(os.environ["RESULTS_ROOT"])
output_log = Path(os.environ["OUTPUT_LOG"])
test_exit = int(os.environ.get("TEST_EXIT", "0"))

MODULES = ["Shared", "Core", "Commerce", "UserTools", "ContentTools", "SiteTools"]

def classify(name: str) -> str:
    normalized = name.replace(".", "\\")
    for module in MODULES:
        if f"\\Unit\\{module}\\" in normalized or f"\\Integration\\{module}\\" in normalized:
            return module
        if f".Unit.{module}." in name or f".Integration.{module}." in name:
            return module
    if "Shared" in name:
        return "Shared"
    return "Other"

tree = ET.parse(junit_file)
root = tree.getroot()

suites = []
if root.tag == "testsuite":
    suites = [root]
else:
    suites = list(root.findall("testsuite"))

module_stats = {m: {"tests": 0, "failures": 0, "errors": 0, "skipped": 0, "time": 0.0, "cases": []} for m in MODULES}

def walk(suite):
    for case in suite.findall("testcase"):
        classname = case.get("classname", "")
        name = case.get("name", "")
        full = f"{classname}::{name}"
        module = classify(classname + name)
        stats = module_stats.setdefault(module, {"tests": 0, "failures": 0, "errors": 0, "skipped": 0, "time": 0.0, "cases": []})
        stats["tests"] += 1
        stats["time"] += float(case.get("time", 0) or 0)
        status = "passed"
        if case.find("failure") is not None:
            stats["failures"] += 1
            status = "failed"
        elif case.find("error") is not None:
            stats["errors"] += 1
            status = "error"
        elif case.find("skipped") is not None:
            stats["skipped"] += 1
            status = "skipped"
        stats["cases"].append({"name": full, "status": status, "time": case.get("time", "0")})
    for child in suite.findall("testsuite"):
        walk(child)

for suite in suites:
    walk(suite)

now = datetime.now(timezone.utc).strftime("%Y-%m-%d %H:%M:%S UTC")
log_text = output_log.read_text(encoding="utf-8", errors="replace") if output_log.exists() else ""

for module, stats in module_stats.items():
    if stats["tests"] == 0 or module == "Other":
        continue
    slug = module.lower()
    mod_dir = results_root / slug
    mod_dir.mkdir(parents=True, exist_ok=True)

    passed = stats["tests"] - stats["failures"] - stats["errors"] - stats["skipped"]
    overall = "PASS" if stats["failures"] == 0 and stats["errors"] == 0 else "FAIL"

    lines = [
        f"# {module} Module Tests",
        "",
        f"- **Status:** {overall}",
        f"- **Generated:** {now}",
        f"- **Tests:** {stats['tests']}",
        f"- **Passed:** {passed}",
        f"- **Failed:** {stats['failures']}",
        f"- **Errors:** {stats['errors']}",
        f"- **Skipped:** {stats['skipped']}",
        f"- **Duration:** {stats['time']:.3f}s",
        "",
        "## Test cases",
        "",
    ]
    for case in stats["cases"]:
        icon = {"passed": "✓", "failed": "✗", "error": "!", "skipped": "○"}.get(case["status"], "?")
        lines.append(f"- {icon} `{case['name']}` ({case['time']}s)")

    (mod_dir / "report.md").write_text("\n".join(lines) + "\n", encoding="utf-8")

    if module != "Other":
        mod_output = mod_dir / "output.log"
        mod_output.write_text(log_text, encoding="utf-8")

total_tests = sum(s["tests"] for s in module_stats.values())
total_failures = sum(s["failures"] for s in module_stats.values())
total_errors = sum(s["errors"] for s in module_stats.values())
total_skipped = sum(s["skipped"] for s in module_stats.values())
total_time = sum(s["time"] for s in module_stats.values())
total_passed = total_tests - total_failures - total_errors - total_skipped
overall_status = "PASS" if test_exit == 0 and total_failures == 0 and total_errors == 0 else "FAIL"

summary_lines = [
    "# MH Dev Kit Test Summary",
    "",
    f"- **Overall status:** {overall_status}",
    f"- **Generated:** {now}",
    f"- **Exit code:** {test_exit}",
    f"- **Total tests:** {total_tests}",
    f"- **Passed:** {total_passed}",
    f"- **Failed:** {total_failures}",
    f"- **Errors:** {total_errors}",
    f"- **Skipped:** {total_skipped}",
    f"- **Duration:** {total_time:.3f}s",
    "",
    "## Per module",
    "",
    "| Module | Tests | Passed | Failed | Errors | Skipped | Duration | Status |",
    "|--------|------:|-------:|-------:|-------:|--------:|---------:|--------|",
]

for module in MODULES:
    stats = module_stats.get(module, {})
    if stats.get("tests", 0) == 0:
        continue
    passed = stats["tests"] - stats["failures"] - stats["errors"] - stats["skipped"]
    status = "PASS" if stats["failures"] == 0 and stats["errors"] == 0 else "FAIL"
    summary_lines.append(
        f"| {module} | {stats['tests']} | {passed} | {stats['failures']} | {stats['errors']} | {stats['skipped']} | {stats['time']:.3f}s | {status} |"
    )

summary_lines.extend(
    [
        "",
        "## Run tests",
        "",
        "```bash",
        "./scripts/test-mh-dev-kit.sh",
        "```",
        "",
        "JUnit XML: `test-results/mh-dev-kit/junit.xml`",
    ]
)

(results_root / "SUMMARY.md").write_text("\n".join(summary_lines) + "\n", encoding="utf-8")
print(f"Reports written under {results_root}")
PY
