#!/usr/bin/env bash
# Runs one example against this checkout of meta-scraper: examples/ci/recipe.sh open-graph
# The example is copied to a temporary directory first, so the checkout stays untouched.
set -euo pipefail
examples="$(cd "$(dirname "$0")/.." && pwd)"
name="${1:?usage: $0 <example>}"
[[ -x "$examples/$name/e2e.sh" ]] || { echo "fail: $examples/$name/e2e.sh not found" >&2; exit 1; }
work="$(mktemp -d)"
trap 'rm -rf "$work"' EXIT
cp -R "$examples/$name" "$work/$name"
cp -R "$examples/ci" "$work/ci"
rm -rf "$work/$name/vendor" "$work/$name/composer.lock" "$work/$name/output.txt"
"$examples/ci/use-local-meta-scraper.sh" "$work/$name"
"$work/$name/e2e.sh"
