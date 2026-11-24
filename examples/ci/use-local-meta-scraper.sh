#!/usr/bin/env bash
# Points a Composer project at this checkout of meta-scraper instead of Packagist:
#   examples/ci/use-local-meta-scraper.sh /path/to/project
# Adds a path repository for the repository root, requires tomaj/meta-scraper:dev-master from it and
# installs the project. Used by the examples workflow, so a pull request runs the examples against its
# own code. It rewrites the project's composer.json: run it on a copy (examples/ci/recipe.sh does).
set -euo pipefail
root="$(cd "$(dirname "$0")/../.." && pwd)"
project="${1:?usage: $0 /path/to/project}"
cd "$project"
# shellcheck disable=SC2016 # $argv is PHP, not shell
# json_encode, so a checkout path with quotes or backslashes still gives valid JSON
repository="$(php -r 'echo json_encode(["type" => "path", "url" => $argv[1], "options" => ["symlink" => false, "versions" => ["tomaj/meta-scraper" => "dev-master"]]], JSON_UNESCAPED_SLASHES);' "$root")"
composer config repositories.meta-scraper "$repository"
composer config minimum-stability dev
composer config prefer-stable true
composer require --no-interaction --no-progress --quiet --with-all-dependencies tomaj/meta-scraper:dev-master
# fail when Composer took meta-scraper from anywhere else
diff -rq "$root/src" vendor/tomaj/meta-scraper/src > /dev/null \
    || { echo "fail: vendor/tomaj/meta-scraper is not this checkout" >&2; exit 1; }
echo "tomaj/meta-scraper installed from $root (guzzlehttp/guzzle $(composer show guzzlehttp/guzzle | awk '/^versions/ {print $NF}'))"
