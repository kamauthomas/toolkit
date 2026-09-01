#!/usr/bin/env bash
set -euo pipefail

STATE_DIR="/tmp/toolkit-wingu-discovery-session"
STATE_FILE="$STATE_DIR/session.env"
DEFAULT_PORT="9223"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

fail() {
  echo "ERROR: $*" >&2
  exit 2
}

load_state() {
  [[ -f "$STATE_FILE" ]] || fail "No isolated Wingu discovery session is recorded."
  # The file contains only tool-generated numeric/path values under /tmp.
  # shellcheck disable=SC1090
  source "$STATE_FILE"
  [[ "${WINGU_PORT:-}" =~ ^[0-9]+$ ]] || fail "Invalid saved port."
  [[ "${WINGU_PID:-}" =~ ^[0-9]+$ ]] || fail "Invalid saved process ID."
  [[ "${WINGU_PROFILE:-}" == /tmp/toolkit-wingu-profile.* ]] || fail "Invalid saved profile path."
}

start_session() {
  local url="${1:-}"
  local port="${2:-$DEFAULT_PORT}"
  [[ "$url" == https://* ]] || fail "Usage: $0 start https://<approved-wingu-url> [port]"
  [[ "$port" =~ ^[0-9]+$ ]] || fail "Port must be numeric."
  (( port >= 1024 && port <= 65535 )) || fail "Port must be between 1024 and 65535."
  [[ ! -e "$STATE_DIR" ]] || fail "A discovery session already exists; use '$0 status' or '$0 stop'."
  command -v brave-browser >/dev/null || fail "brave-browser is not installed."

  local profile
  profile="$(mktemp -d /tmp/toolkit-wingu-profile.XXXXXX)"
  mkdir -p "$STATE_DIR"
  setsid brave-browser \
    --user-data-dir="$profile" \
    --remote-debugging-address=127.0.0.1 \
    --remote-debugging-port="$port" \
    --no-first-run \
    --no-default-browser-check \
    --app="$url" >"$STATE_DIR/browser.log" 2>&1 &
  local browser_pid="$!"
  {
    printf 'WINGU_PORT=%q\n' "$port"
    printf 'WINGU_PID=%q\n' "$browser_pid"
    printf 'WINGU_PROFILE=%q\n' "$profile"
  } >"$STATE_FILE"
  chmod 600 "$STATE_FILE"
  echo "Isolated Wingu window started. Sign in yourself, open Edit Time Sheet, then run:"
  echo "  $0 discover"
  echo "This profile is temporary and is not copied from your normal browser."
}

session_status() {
  load_state
  if kill -0 "$WINGU_PID" 2>/dev/null; then
    echo "Isolated process $WINGU_PID is running on localhost port $WINGU_PORT."
  else
    echo "The recorded isolated process is no longer running. Use '$0 stop' to clean its profile."
    return 1
  fi
}

discover_fields() {
  load_state
  kill -0 "$WINGU_PID" 2>/dev/null || fail "The isolated browser is not running."
  python "$SCRIPT_DIR/wingu-discover.py" \
    --port "$WINGU_PORT" \
    --host-hint wingu \
    --output /tmp/toolkit-wingu-field-discovery.json
  echo "Review artifact: /tmp/toolkit-wingu-field-discovery.json"
  echo "It contains field names, labels and project options, but no input values or credentials."
}

stop_session() {
  load_state
  if kill -0 "$WINGU_PID" 2>/dev/null; then
    local command_line
    command_line="$(tr '\0' ' ' <"/proc/$WINGU_PID/cmdline" 2>/dev/null || true)"
    [[ "$command_line" == *"$WINGU_PROFILE"* ]] || fail "Refusing to stop a process that does not own the recorded temporary profile."
    kill -TERM -- "-$WINGU_PID" 2>/dev/null || true
  fi
  rm -rf -- "$WINGU_PROFILE" "$STATE_DIR" /tmp/toolkit-wingu-field-discovery.json
  echo "Removed the isolated browser process, temporary profile and discovery artifact."
}

case "${1:-}" in
  start) start_session "${2:-}" "${3:-$DEFAULT_PORT}" ;;
  status) session_status ;;
  discover) discover_fields ;;
  stop) stop_session ;;
  *) fail "Usage: $0 {start https://<approved-wingu-url> [port]|status|discover|stop}" ;;
esac
