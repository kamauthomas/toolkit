#!/usr/bin/env python3
"""Inventory a Wingu page through an explicitly isolated localhost CDP session.

This tool is discovery-only. It never reads input values, cookies, storage,
response bodies or credentials and has no submit/edit capability.
"""

import argparse
import json
import sys
import urllib.error
import urllib.parse
import urllib.request

import websocket


DISCOVERY_EXPRESSION = r"""
(() => {
  const clean = (value, limit = 200) => String(value || '').replace(/\s+/g, ' ').trim().slice(0, limit);
  const labelFor = (element) => {
    if (element.id) {
      const explicit = document.querySelector(`label[for="${CSS.escape(element.id)}"]`);
      if (explicit) return clean(explicit.textContent);
    }
    const parent = element.closest('label');
    return parent ? clean(parent.textContent) : '';
  };
  const safePath = (raw) => {
    try {
      const parsed = new URL(raw, location.href);
      const path = parsed.pathname.split('/').map(segment =>
        (/^\d+$/.test(segment) || /^[0-9a-f]{8}-[0-9a-f-]{27,}$/i.test(segment)) ? ':id' : segment
      ).join('/');
      return `${parsed.origin}${path}`;
    }
    catch (_) { return ''; }
  };
  return {
    page: safePath(location.href),
    forms: Array.from(document.forms).map((form, index) => ({
      index, id: clean(form.id), name: clean(form.getAttribute('name')),
      method: clean(form.method).toUpperCase(), action: safePath(form.action)
    })),
    fields: Array.from(document.querySelectorAll('input, textarea')).map((field, index) => ({
      index, tag: field.tagName.toLowerCase(),
      type: clean(field.getAttribute('type') || field.tagName.toLowerCase()),
      id: clean(field.id), name: clean(field.getAttribute('name')),
      label: labelFor(field), placeholder: clean(field.getAttribute('placeholder')),
      required: Boolean(field.required), disabled: Boolean(field.disabled)
    })),
    selects: Array.from(document.querySelectorAll('select')).map((field, index) => ({
      index, id: clean(field.id), name: clean(field.getAttribute('name')),
      label: labelFor(field), required: Boolean(field.required), disabled: Boolean(field.disabled),
      option_count: field.options.length,
      options: /project/i.test(`${field.id} ${field.name} ${labelFor(field)}`)
        ? Array.from(field.options).map(option => ({text: clean(option.textContent), value: clean(option.value)}))
        : []
    })),
    buttons: Array.from(document.querySelectorAll('button, input[type="submit"]')).map((button, index) => ({
      index, tag: button.tagName.toLowerCase(), id: clean(button.id),
      name: clean(button.getAttribute('name')), type: clean(button.getAttribute('type')),
      text: clean(button.textContent || button.getAttribute('aria-label') || button.getAttribute('value')),
      disabled: Boolean(button.disabled)
    }))
  };
})()
"""


def endpoint(port, path="/json"):
    if not 1024 <= port <= 65535:
        raise ValueError("CDP port must be between 1024 and 65535")
    return f"http://127.0.0.1:{port}{path}"


def read_targets(port):
    try:
        with urllib.request.urlopen(endpoint(port), timeout=3) as response:
            return json.load(response)
    except (OSError, urllib.error.URLError, json.JSONDecodeError) as exc:
        raise RuntimeError(
            f"No isolated browser debugging session answered on 127.0.0.1:{port}."
        ) from exc


def safe_tab_summary(target):
    parsed = urllib.parse.urlsplit(target.get("url", ""))
    segments = [":id" if segment.isdigit() else segment for segment in parsed.path.split("/")]
    page = f"{parsed.scheme}://{parsed.netloc}{'/'.join(segments)}" if parsed.scheme and parsed.netloc else ""
    return {"page": page[:500]}


def select_target(targets, host_hint):
    candidates = [target for target in targets if target.get("type") == "page"]
    matching = [target for target in candidates if host_hint.lower() in target.get("url", "").lower()]
    if len(matching) != 1:
        summaries = [safe_tab_summary(target) for target in candidates]
        raise RuntimeError(
            f"Expected exactly one page matching {host_hint!r}; found {len(matching)}. "
            f"Open only the approved Wingu page in the isolated profile. Tabs: {json.dumps(summaries)}"
        )
    if not matching[0].get("webSocketDebuggerUrl"):
        raise RuntimeError("The matching page does not expose a CDP WebSocket endpoint.")
    return matching[0]


def discover(target):
    connection = websocket.create_connection(
        target["webSocketDebuggerUrl"], timeout=5, suppress_origin=True
    )
    try:
        request_id = 1
        connection.send(json.dumps({
            "id": request_id,
            "method": "Runtime.evaluate",
            "params": {"expression": DISCOVERY_EXPRESSION, "returnByValue": True},
        }))
        while True:
            message = json.loads(connection.recv())
            if message.get("id") != request_id:
                continue
            if "error" in message:
                raise RuntimeError(f"CDP discovery failed: {message['error'].get('message', 'unknown error')}")
            result = message.get("result", {}).get("result", {})
            if result.get("subtype") == "error" or "value" not in result:
                raise RuntimeError("The page rejected the credential-safe discovery expression.")
            return result["value"]
    finally:
        connection.close()


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--port", type=int, default=9223, help="localhost CDP port (default: 9223)")
    parser.add_argument("--host-hint", default="wingu", help="substring that must identify exactly one approved tab")
    parser.add_argument("--output", help="optional output path; use /tmp for live discovery artifacts")
    args = parser.parse_args()
    try:
        target = select_target(read_targets(args.port), args.host_hint)
        result = {
            "schema": "toolkit-wingu-field-discovery-v1",
            "warning": "Names, labels and select options only; no values, cookies, storage or credentials captured.",
            "page": discover(target),
        }
        payload = json.dumps(result, indent=2, ensure_ascii=False) + "\n"
        if args.output:
            if not args.output.startswith("/tmp/"):
                raise RuntimeError("Live discovery output must be written under /tmp so it cannot be committed.")
            with open(args.output, "w", encoding="utf-8") as handle:
                handle.write(payload)
            print(f"Wrote credential-safe field discovery to {args.output}")
        else:
            sys.stdout.write(payload)
    except (RuntimeError, ValueError) as exc:
        print(f"ERROR: {exc}", file=sys.stderr)
        return 2
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
