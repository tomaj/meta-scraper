#!/usr/bin/env bash
# Helpers for the e2e.sh scripts of the examples. Sourced, not run. Needs php.

# serve <port> <document root>: starts the PHP built-in web server for the fixture site in the
# background (with <document root>/router.php when there is one) and waits until it accepts
# connections. Sets $BASE to its URL; the server is stopped when the script exits.
serve() {
    local port="$1" root="$2" router=()
    BASE="http://127.0.0.1:$port"
    [[ -f "$root/router.php" ]] && router=("$root/router.php")
    PHP_CLI_SERVER_WORKERS=4 php -S "127.0.0.1:$port" -t "$root" ${router[@]+"${router[@]}"} > "server-$port.log" 2>&1 &
    SERVER_PID=$!
    # with PHP_CLI_SERVER_WORKERS the server forks workers, which outlive a killed parent
    trap 'kill $(pgrep -P "$SERVER_PID") "$SERVER_PID" 2>/dev/null || true' EXIT
    for _ in $(seq 1 50); do
        kill -0 "$SERVER_PID" 2>/dev/null || break
        php -r 'exit(@fsockopen("127.0.0.1", (int) $argv[1]) ? 0 : 1);' "$port" 2>/dev/null && return 0
        sleep 0.1
    done
    echo "fail: the PHP web server did not start on port $port" >&2
    cat "server-$port.log" >&2
    exit 1
}

# check_output <command...>: runs the command, writes its output to output.txt and compares it
# with expected-output.txt. Prints the difference and fails when they differ.
check_output() {
    "$@" > output.txt
    if ! diff -u expected-output.txt output.txt; then
        echo "fail: the output differs from expected-output.txt (- expected, + actual)" >&2
        exit 1
    fi
    cat output.txt
    echo "ok: the output matches expected-output.txt"
}
