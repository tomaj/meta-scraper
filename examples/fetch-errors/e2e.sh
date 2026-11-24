#!/usr/bin/env bash
# Serves the fixture site with the PHP built-in server, runs run.php and compares its output.
set -euo pipefail
cd "$(dirname "$0")"
[[ -f vendor/autoload.php ]] || { echo "fail: vendor/ is missing, run ../ci/recipe.sh $(basename "$PWD")" >&2; exit 1; }
source ../ci/lib.sh
serve 8405 site
check_output php run.php "$BASE"
