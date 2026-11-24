#!/usr/bin/env bash
# Runs run.php (no web server needed: Guzzle answers from a MockHandler) and compares its output.
set -euo pipefail
cd "$(dirname "$0")"
[[ -f vendor/autoload.php ]] || { echo "fail: vendor/ is missing, run ../ci/recipe.sh $(basename "$PWD")" >&2; exit 1; }
source ../ci/lib.sh
check_output php run.php
