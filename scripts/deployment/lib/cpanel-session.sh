#!/usr/bin/env bash

# This host accepts form/session authentication but rejects HTTP Basic UAPI.
cpanel_session_open() {
	local credentials=${1:?credentials in username:password format are required}
	local base=${2:-https://toolkitafrica.ac.ke:2083}
	local username=${credentials%%:*} password=${credentials#*:}
	CPANEL_SESSION_DIR=$(mktemp -d); chmod 700 "$CPANEL_SESSION_DIR"
	CPANEL_SESSION_COOKIE="$CPANEL_SESSION_DIR/cookies"
	local response="$CPANEL_SESSION_DIR/login.json"
	curl -fsS -c "$CPANEL_SESSION_COOKIE" -o "$response" --data-urlencode "user=$username" --data-urlencode "pass=$password" "$base/login/?login_only=1"
	local token
	token=$(php -r '$j=json_decode(file_get_contents($argv[1]),true);if(($j["status"]??0)!==1||empty($j["security_token"]))exit(2);echo $j["security_token"];' "$response")
	CPANEL_SESSION_BASE=$base; CPANEL_SESSION_API="$base$token/execute"; CPANEL_SESSION_API2="$base$token/json-api/cpanel"
	export CPANEL_SESSION_DIR CPANEL_SESSION_COOKIE CPANEL_SESSION_BASE CPANEL_SESSION_API CPANEL_SESSION_API2
}

cpanel_session_close() {
	[ -z "${CPANEL_SESSION_DIR:-}" ] || [ ! -d "$CPANEL_SESSION_DIR" ] || rm -rf -- "$CPANEL_SESSION_DIR"
	unset CPANEL_SESSION_DIR CPANEL_SESSION_COOKIE CPANEL_SESSION_BASE CPANEL_SESSION_API CPANEL_SESSION_API2
}
