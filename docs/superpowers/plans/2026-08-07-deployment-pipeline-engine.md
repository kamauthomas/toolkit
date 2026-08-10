# Deployment Pipeline Engine Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the manual, credential-archaeology-based cPanel deploy process with a reusable Python CLI (`toolkit_deploy`) that derives file sets from git diff, gates production behind a verified demo deploy, and catches the stale-page-cache bug class found on 2026-08-07.

**Architecture:** A stdlib-only Python package (`scripts/toolkit_deploy/`) with small, independently-testable modules (`state`, `config`, `release`, `cpanel`, `verify`, `deploy`) wired together by a `__main__.py` CLI. Network-calling code (`cpanel.py`, `verify.py`) is unit-tested against mocked HTTP responses; `deploy.py`'s orchestration is unit-tested against a mocked `CPanelClient`; only a manual live run against demo (outside this plan, per the spec's Rollout section) exercises the real network.

**Tech Stack:** Python 3.11+ stdlib only (`tomllib`, `urllib.request`, `subprocess`, `json`, `re`, `pathlib`, `dataclasses`). Tests with `pytest` (already available in this environment: 8.3.5).

## Global Constraints

- Spec: `docs/superpowers/specs/2026-08-07-deployment-pipeline-design.md` (all requirements below are copied verbatim from it; consult it directly for anything a task doesn't fully explain).
- Stdlib-only — no third-party dependencies (`requests`, `PyYAML`, etc.) may be added.
- Config file is TOML (`config.toml`, committed); credentials live in `.toolkit-deploy/secrets.env` (gitignored) as `CPANEL_AUTH=user:pass`; runtime state lives in `.toolkit-deploy/state.json` (gitignored).
- `git diff` calls always use `--no-renames --name-status` (never `--name-only`) — a renamed file must collapse to a delete+add pair, not a combined `R100` line.
- Deletions detected in a diff abort the deploy before any network call — this tool never deletes remote files as part of a normal deploy.
- `functions.php` is unconditionally included in the backup and upload set on every non-empty deploy (it always gets its release marker bumped).
- The release version for a deploy always comes from `state.json`'s current `<env>.version` plus one — never by reading `functions.php`'s existing marker.
- Verification checks each configured route twice: once cache-busted, once bare (after a short settle delay) — the bare recheck is what catches a stale page cache.
- `deploy production` refuses to run unless `state.json` shows `demo.commit == HEAD` and `demo.verified == true`.
- `rollback` never auto-deletes newly-introduced files; it lists them for manual removal.
- Empty diff on `deploy <env>` is a no-op (exit 0, no version bump, no state write, no network call).
- This repo's theme root is `wp-content/themes/eduma-child`; production/demo remote paths follow the pattern already used in this session (`public_html/wp-content/themes/eduma-child`, `demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child`); the cPanel host is `wp46.host-ww.net:2083` (UAPI over HTTP Basic auth, `/execute/Fileman/get_file_content` and `/execute/Fileman/upload_files`).
- Bootstrap values for this rollout: commit `3635019`, version
  `2026.08.04.21` (both demo and production are already deployed there
  manually, per the verified 4 August release evidence and current handoff).

---

### Task 1: `state.py` — state tracking, version computation, production gate

**Files:**
- Create: `scripts/toolkit_deploy/__init__.py` (empty)
- Create: `scripts/toolkit_deploy/state.py`
- Create: `scripts/toolkit_deploy/tests/__init__.py` (empty)
- Test: `scripts/toolkit_deploy/tests/test_state.py`

**Interfaces:**
- Consumes: nothing (first module, no dependencies on other `toolkit_deploy` code).
- Produces (used by later tasks):
  - `load_state(path: Path) -> dict` — `{"demo": dict | None, "production": dict | None}`, each non-`None` value shaped `{"commit": str, "version": str, "verified": bool, "timestamp": str}`.
  - `save_state(path: Path, state: dict) -> None`
  - `compute_next_version(current_version: str | None, today: date) -> str`
  - `record_deploy(state: dict, env: str, commit: str, version: str, verified: bool, timestamp: str) -> dict` (returns a new dict, does not mutate input)
  - `bootstrap_state(commit: str, version: str, timestamp: str) -> dict` (returns a fresh state dict with both `demo` and `production` seeded identically)
  - `class ProductionGateError(Exception)`
  - `check_production_gate(state: dict, head_commit: str) -> None` (raises `ProductionGateError` on failure, returns `None` on success)

- [ ] **Step 1: Create package skeleton**

```bash
mkdir -p /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts/toolkit_deploy/tests
touch /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts/toolkit_deploy/__init__.py
touch /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts/toolkit_deploy/tests/__init__.py
```

- [ ] **Step 2: Write the failing tests**

Create `scripts/toolkit_deploy/tests/test_state.py`:

```python
from datetime import date

import pytest

from toolkit_deploy.state import (
    ProductionGateError,
    bootstrap_state,
    check_production_gate,
    compute_next_version,
    load_state,
    record_deploy,
    save_state,
)


def test_load_state_missing_file_returns_default(tmp_path):
    path = tmp_path / "state.json"
    assert load_state(path) == {"demo": None, "production": None}


def test_save_then_load_round_trip(tmp_path):
    path = tmp_path / "nested" / "state.json"
    state = {
        "demo": {
            "commit": "abc123",
            "version": "2026.08.07.1",
            "verified": True,
            "timestamp": "2026-08-07T00:00:00+00:00",
        },
        "production": None,
    }
    save_state(path, state)
    assert load_state(path) == state


def test_compute_next_version_no_prior_version():
    assert compute_next_version(None, date(2026, 8, 7)) == "2026.08.07.1"


def test_compute_next_version_same_day_increments():
    assert compute_next_version("2026.08.07.1", date(2026, 8, 7)) == "2026.08.07.2"


def test_compute_next_version_different_day_resets():
    assert compute_next_version("2026.08.04.21", date(2026, 8, 7)) == "2026.08.07.1"


def test_record_deploy_does_not_mutate_input():
    state = {"demo": None, "production": None}
    new_state = record_deploy(
        state, "demo", "abc123", "2026.08.07.1", True, "2026-08-07T00:00:00+00:00"
    )
    assert state == {"demo": None, "production": None}
    assert new_state["demo"] == {
        "commit": "abc123",
        "version": "2026.08.07.1",
        "verified": True,
        "timestamp": "2026-08-07T00:00:00+00:00",
    }
    assert new_state["production"] is None


def test_bootstrap_state_seeds_both_environments_identically():
    state = bootstrap_state("dec1b6e", "2026.08.07.2", "2026-08-07T12:00:00+00:00")
    expected_entry = {
        "commit": "dec1b6e",
        "version": "2026.08.07.2",
        "verified": True,
        "timestamp": "2026-08-07T12:00:00+00:00",
    }
    assert state == {"demo": expected_entry, "production": expected_entry}


def test_production_gate_blocks_when_demo_never_deployed():
    state = {"demo": None, "production": None}
    with pytest.raises(ProductionGateError, match="never been deployed"):
        check_production_gate(state, "abc123")


def test_production_gate_blocks_when_demo_commit_differs():
    state = {
        "demo": {
            "commit": "old",
            "version": "2026.08.07.1",
            "verified": True,
            "timestamp": "t",
        },
        "production": None,
    }
    with pytest.raises(ProductionGateError, match="run `deploy demo` first"):
        check_production_gate(state, "new")


def test_production_gate_blocks_when_demo_not_verified():
    state = {
        "demo": {
            "commit": "abc123",
            "version": "2026.08.07.1",
            "verified": False,
            "timestamp": "t",
        },
        "production": None,
    }
    with pytest.raises(ProductionGateError, match="did not pass verification"):
        check_production_gate(state, "abc123")


def test_production_gate_passes_when_demo_verified_at_head():
    state = {
        "demo": {
            "commit": "abc123",
            "version": "2026.08.07.1",
            "verified": True,
            "timestamp": "t",
        },
        "production": None,
    }
    check_production_gate(state, "abc123")  # must not raise
```

- [ ] **Step 3: Run tests to verify they fail**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts && python3 -m pytest toolkit_deploy/tests/test_state.py -v
```

Expected: FAIL with `ModuleNotFoundError: No module named 'toolkit_deploy.state'`

- [ ] **Step 4: Write `state.py`**

Create `scripts/toolkit_deploy/state.py`:

```python
"""State tracking for toolkit_deploy: last deployed commit/version per
environment, stored in .toolkit-deploy/state.json (gitignored).

state.json is the single source of truth for "what version is actually
live" — the version for a new deploy is always computed from here, never
by reading functions.php's existing marker (which may hold an uncommitted
bump from a prior run).
"""
from __future__ import annotations

import json
from datetime import date
from pathlib import Path
from typing import Optional


def load_state(path: Path) -> dict:
    """Load state.json. Returns a default empty-per-environment structure
    if the file does not exist yet (before bootstrap has ever run)."""
    if not path.exists():
        return {"demo": None, "production": None}
    with path.open("r", encoding="utf-8") as f:
        return json.load(f)


def save_state(path: Path, state: dict) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    with path.open("w", encoding="utf-8") as f:
        json.dump(state, f, indent=2, sort_keys=True)
        f.write("\n")


def compute_next_version(current_version: Optional[str], today: date) -> str:
    """Given the current recorded version for an environment (or None if
    never deployed) and today's date, compute the next release version.

    Version format is YYYY.MM.DD.N. If the current version already has
    today's date prefix, increment the trailing N. Otherwise start a new
    version at N=1 for today.
    """
    today_prefix = today.strftime("%Y.%m.%d")
    if current_version is None:
        return f"{today_prefix}.1"
    parts = current_version.rsplit(".", 1)
    if len(parts) != 2:
        return f"{today_prefix}.1"
    prefix, trailing = parts
    if prefix != today_prefix:
        return f"{today_prefix}.1"
    try:
        n = int(trailing)
    except ValueError:
        return f"{today_prefix}.1"
    return f"{today_prefix}.{n + 1}"


def record_deploy(
    state: dict, env: str, commit: str, version: str, verified: bool, timestamp: str
) -> dict:
    """Return a new state dict with the given environment's entry updated.
    Does not mutate the input dict."""
    new_state = dict(state)
    new_state[env] = {
        "commit": commit,
        "version": version,
        "verified": verified,
        "timestamp": timestamp,
    }
    return new_state


def bootstrap_state(commit: str, version: str, timestamp: str) -> dict:
    """Seed a fresh state dict with both demo and production pointed at the
    same already-known-live commit/version. Used once when this tool is
    first adopted for a repo that already has manually-deployed
    environments (see the spec's Bootstrap section)."""
    entry = {
        "commit": commit,
        "version": version,
        "verified": True,
        "timestamp": timestamp,
    }
    return {"demo": dict(entry), "production": dict(entry)}


class ProductionGateError(Exception):
    """Raised when a production deploy is attempted without a verified
    demo deploy at the same commit."""


def check_production_gate(state: dict, head_commit: str) -> None:
    """Raise ProductionGateError if demo is not verified at head_commit."""
    demo = state.get("demo")
    if demo is None:
        raise ProductionGateError(
            "demo has never been deployed — run `deploy demo` first"
        )
    if demo.get("commit") != head_commit:
        raise ProductionGateError(
            f"demo is on {demo.get('commit')}, HEAD is {head_commit} — "
            "run `deploy demo` first"
        )
    if not demo.get("verified"):
        raise ProductionGateError(
            "demo deploy exists but did not pass verification — fix and "
            "re-run `deploy demo` first"
        )
```

- [ ] **Step 5: Run tests to verify they pass**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts && python3 -m pytest toolkit_deploy/tests/test_state.py -v
```

Expected: PASS (11 tests)

- [ ] **Step 6: Commit**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress
git add scripts/toolkit_deploy/__init__.py scripts/toolkit_deploy/tests/__init__.py scripts/toolkit_deploy/state.py scripts/toolkit_deploy/tests/test_state.py
git commit -m "feat(deploy): add state.py — version tracking and production gate"
```

---

### Task 2: `config.py` — TOML config + secrets.env loading

**Files:**
- Create: `scripts/toolkit_deploy/config.py`
- Test: `scripts/toolkit_deploy/tests/test_config.py`

**Interfaces:**
- Consumes: nothing from other `toolkit_deploy` modules.
- Produces (used by `deploy.py` and `__main__.py`):
  - `class ConfigError(Exception)`
  - `@dataclass(frozen=True) class RouteConfig: path: str; expect_status: int = 200; expect_release: bool = True`
  - `@dataclass(frozen=True) class EnvironmentConfig: name: str; host: str; remote_base_dir: str; base_url: str; routes: list[RouteConfig]`
  - `@dataclass(frozen=True) class Config: cpanel_host: str; cpanel_auth: str; environments: dict[str, EnvironmentConfig]`
  - `parse_env_file(path: Path) -> dict[str, str]`
  - `load_config(config_path: Path, secrets_path: Path) -> Config`

- [ ] **Step 1: Write the failing tests**

Create `scripts/toolkit_deploy/tests/test_config.py`:

```python
import pytest

from toolkit_deploy.config import ConfigError, load_config, parse_env_file

VALID_TOML = """
[cpanel]
host = "https://wp46.host-ww.net:2083"

[environments.demo]
remote_base_dir = "demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child"
base_url = "https://demo.toolkitafrica.ac.ke"

[[environments.demo.routes]]
path = "/"

[[environments.demo.routes]]
path = "/speak-up/"
expect_status = 200

[environments.production]
remote_base_dir = "public_html/wp-content/themes/eduma-child"
base_url = "https://toolkitafrica.ac.ke"

[[environments.production.routes]]
path = "/"
"""


def test_parse_env_file_missing_file_returns_empty(tmp_path):
    assert parse_env_file(tmp_path / "missing.env") == {}


def test_parse_env_file_reads_key_value_lines(tmp_path):
    path = tmp_path / "secrets.env"
    path.write_text("# comment\nCPANEL_AUTH=bfyigiln:secret\n\nOTHER=value\n")
    assert parse_env_file(path) == {"CPANEL_AUTH": "bfyigiln:secret", "OTHER": "value"}


def test_load_config_happy_path(tmp_path):
    config_path = tmp_path / "config.toml"
    config_path.write_text(VALID_TOML)
    secrets_path = tmp_path / "secrets.env"
    secrets_path.write_text("CPANEL_AUTH=bfyigiln:secret\n")

    config = load_config(config_path, secrets_path)

    assert config.cpanel_host == "https://wp46.host-ww.net:2083"
    assert config.cpanel_auth == "bfyigiln:secret"
    assert set(config.environments) == {"demo", "production"}
    demo = config.environments["demo"]
    assert demo.remote_base_dir == "demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child"
    assert demo.base_url == "https://demo.toolkitafrica.ac.ke"
    assert len(demo.routes) == 2
    assert demo.routes[0].path == "/"
    assert demo.routes[0].expect_status == 200
    assert demo.routes[0].expect_release is True


def test_load_config_missing_config_file_raises(tmp_path):
    with pytest.raises(ConfigError, match="not found"):
        load_config(tmp_path / "missing.toml", tmp_path / "secrets.env")


def test_load_config_missing_cpanel_auth_raises(tmp_path):
    config_path = tmp_path / "config.toml"
    config_path.write_text(VALID_TOML)
    with pytest.raises(ConfigError, match="CPANEL_AUTH"):
        load_config(config_path, tmp_path / "secrets.env")


def test_load_config_missing_cpanel_host_raises(tmp_path):
    config_path = tmp_path / "config.toml"
    config_path.write_text("[environments.demo]\nremote_base_dir = \"x\"\nbase_url = \"y\"\n")
    secrets_path = tmp_path / "secrets.env"
    secrets_path.write_text("CPANEL_AUTH=bfyigiln:secret\n")
    with pytest.raises(ConfigError, match="cpanel"):
        load_config(config_path, secrets_path)


def test_load_config_missing_environments_raises(tmp_path):
    config_path = tmp_path / "config.toml"
    config_path.write_text('[cpanel]\nhost = "https://example.com"\n')
    secrets_path = tmp_path / "secrets.env"
    secrets_path.write_text("CPANEL_AUTH=bfyigiln:secret\n")
    with pytest.raises(ConfigError, match="environments"):
        load_config(config_path, secrets_path)


def test_load_config_environment_missing_required_field_raises(tmp_path):
    config_path = tmp_path / "config.toml"
    config_path.write_text(
        '[cpanel]\nhost = "https://example.com"\n\n'
        '[environments.demo]\nremote_base_dir = "x"\n'
    )
    secrets_path = tmp_path / "secrets.env"
    secrets_path.write_text("CPANEL_AUTH=bfyigiln:secret\n")
    with pytest.raises(ConfigError, match="base_url"):
        load_config(config_path, secrets_path)


def test_load_config_route_missing_path_raises(tmp_path):
    config_path = tmp_path / "config.toml"
    config_path.write_text(
        '[cpanel]\nhost = "https://example.com"\n\n'
        '[environments.demo]\nremote_base_dir = "x"\nbase_url = "y"\n\n'
        "[[environments.demo.routes]]\nexpect_status = 200\n"
    )
    secrets_path = tmp_path / "secrets.env"
    secrets_path.write_text("CPANEL_AUTH=bfyigiln:secret\n")
    with pytest.raises(ConfigError, match="path"):
        load_config(config_path, secrets_path)
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts && python3 -m pytest toolkit_deploy/tests/test_config.py -v
```

Expected: FAIL with `ModuleNotFoundError: No module named 'toolkit_deploy.config'`

- [ ] **Step 3: Write `config.py`**

Create `scripts/toolkit_deploy/config.py`:

```python
"""Configuration loading for toolkit_deploy: config.toml (committed) +
secrets.env (gitignored)."""
from __future__ import annotations

import tomllib
from dataclasses import dataclass
from pathlib import Path


class ConfigError(Exception):
    """Raised when config.toml or secrets.env is missing required fields."""


@dataclass(frozen=True)
class RouteConfig:
    path: str
    expect_status: int = 200
    expect_release: bool = True


@dataclass(frozen=True)
class EnvironmentConfig:
    name: str
    host: str
    remote_base_dir: str
    base_url: str
    routes: list[RouteConfig]


@dataclass(frozen=True)
class Config:
    cpanel_host: str
    cpanel_auth: str
    environments: dict[str, EnvironmentConfig]


def parse_env_file(path: Path) -> dict[str, str]:
    """Parse a simple KEY=VALUE .env file. Blank lines and lines starting
    with # are ignored. No quoting/escaping support — values are used
    as-is."""
    result: dict[str, str] = {}
    if not path.exists():
        return result
    for line in path.read_text(encoding="utf-8").splitlines():
        line = line.strip()
        if not line or line.startswith("#"):
            continue
        if "=" not in line:
            continue
        key, _, value = line.partition("=")
        result[key.strip()] = value.strip()
    return result


def load_config(config_path: Path, secrets_path: Path) -> Config:
    if not config_path.exists():
        raise ConfigError(f"config file not found: {config_path}")
    with config_path.open("rb") as f:
        raw = tomllib.load(f)

    secrets = parse_env_file(secrets_path)
    cpanel_auth = secrets.get("CPANEL_AUTH")
    if not cpanel_auth:
        raise ConfigError(
            f"CPANEL_AUTH not set in {secrets_path} — expected a line like "
            "CPANEL_AUTH=user:pass"
        )

    if "cpanel" not in raw or "host" not in raw["cpanel"]:
        raise ConfigError("config.toml missing required [cpanel] host")
    cpanel_host = raw["cpanel"]["host"]

    if "environments" not in raw or not raw["environments"]:
        raise ConfigError("config.toml missing required [environments.*] entries")

    environments: dict[str, EnvironmentConfig] = {}
    for name, env_raw in raw["environments"].items():
        for required in ("remote_base_dir", "base_url"):
            if required not in env_raw:
                raise ConfigError(
                    f"config.toml environments.{name} missing required '{required}'"
                )
        routes_raw = env_raw.get("routes", [])
        routes: list[RouteConfig] = []
        for route_raw in routes_raw:
            if "path" not in route_raw:
                raise ConfigError(
                    f"config.toml environments.{name} has a route missing 'path'"
                )
            routes.append(
                RouteConfig(
                    path=route_raw["path"],
                    expect_status=route_raw.get("expect_status", 200),
                    expect_release=route_raw.get("expect_release", True),
                )
            )
        environments[name] = EnvironmentConfig(
            name=name,
            host=cpanel_host,
            remote_base_dir=env_raw["remote_base_dir"],
            base_url=env_raw["base_url"],
            routes=routes,
        )

    return Config(cpanel_host=cpanel_host, cpanel_auth=cpanel_auth, environments=environments)
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts && python3 -m pytest toolkit_deploy/tests/test_config.py -v
```

Expected: PASS (9 tests)

- [ ] **Step 5: Commit**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress
git add scripts/toolkit_deploy/config.py scripts/toolkit_deploy/tests/test_config.py
git commit -m "feat(deploy): add config.py — TOML config + secrets.env loading"
```

---

### Task 3: `release.py` — read/write the release marker in functions.php

**Files:**
- Create: `scripts/toolkit_deploy/release.py`
- Test: `scripts/toolkit_deploy/tests/test_release.py`

**Interfaces:**
- Consumes: nothing from other `toolkit_deploy` modules.
- Produces (used by `deploy.py`):
  - `class ReleaseMarkerError(Exception)`
  - `bump_release_marker(functions_php_path: Path, new_version: str) -> None`
  - `read_release_marker(functions_php_path: Path) -> str`

- [ ] **Step 1: Write the failing tests**

Create `scripts/toolkit_deploy/tests/test_release.py`:

```python
import pytest

from toolkit_deploy.release import ReleaseMarkerError, bump_release_marker, read_release_marker

FUNCTIONS_PHP_FIXTURE = """<?php

function toolkit_editorial_story_preview() {
\treturn null;
}

/**
 * Increment for every public demo/production release.
 */
function toolkit_theme_release() {
\treturn '2026.08.07.2';
}

function toolkit_is_demo_environment() {
\treturn false;
}
"""


def test_read_release_marker_returns_current_version(tmp_path):
    path = tmp_path / "functions.php"
    path.write_text(FUNCTIONS_PHP_FIXTURE)
    assert read_release_marker(path) == "2026.08.07.2"


def test_bump_release_marker_replaces_only_the_release_line(tmp_path):
    path = tmp_path / "functions.php"
    path.write_text(FUNCTIONS_PHP_FIXTURE)

    bump_release_marker(path, "2026.08.08.1")

    content = path.read_text()
    assert "return '2026.08.08.1';" in content
    assert "2026.08.07.2" not in content
    # everything else in the file is untouched
    assert "function toolkit_editorial_story_preview() {" in content
    assert "function toolkit_is_demo_environment() {" in content


def test_bump_release_marker_missing_function_raises(tmp_path):
    path = tmp_path / "functions.php"
    path.write_text("<?php\n// no release function here\n")
    with pytest.raises(ReleaseMarkerError, match="toolkit_theme_release"):
        bump_release_marker(path, "2026.08.08.1")


def test_read_release_marker_missing_function_raises(tmp_path):
    path = tmp_path / "functions.php"
    path.write_text("<?php\n// no release function here\n")
    with pytest.raises(ReleaseMarkerError, match="toolkit_theme_release"):
        read_release_marker(path)
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts && python3 -m pytest toolkit_deploy/tests/test_release.py -v
```

Expected: FAIL with `ModuleNotFoundError: No module named 'toolkit_deploy.release'`

- [ ] **Step 3: Write `release.py`**

Create `scripts/toolkit_deploy/release.py`:

```python
"""Read/rewrite the toolkit_theme_release() return line in functions.php.

The version written on a deploy always comes from state.py's next-version
computation — this module never computes what version comes next, only
locates and reads/replaces the existing return line.
"""
from __future__ import annotations

import re
from pathlib import Path

RELEASE_LINE_PATTERN = re.compile(
    r"(function toolkit_theme_release\(\)\s*\{\s*\n\s*return\s+')([^']*)(';\s*\n\s*\})"
)


class ReleaseMarkerError(Exception):
    """Raised when the toolkit_theme_release() return line cannot be found."""


def read_release_marker(functions_php_path: Path) -> str:
    content = functions_php_path.read_text(encoding="utf-8")
    match = RELEASE_LINE_PATTERN.search(content)
    if not match:
        raise ReleaseMarkerError(
            f"could not find toolkit_theme_release() return line in {functions_php_path}"
        )
    return match.group(2)


def bump_release_marker(functions_php_path: Path, new_version: str) -> None:
    content = functions_php_path.read_text(encoding="utf-8")
    if not RELEASE_LINE_PATTERN.search(content):
        raise ReleaseMarkerError(
            f"could not find toolkit_theme_release() return line in {functions_php_path}"
        )
    new_content = RELEASE_LINE_PATTERN.sub(rf"\g<1>{new_version}\g<3>", content, count=1)
    functions_php_path.write_text(new_content, encoding="utf-8")
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts && python3 -m pytest toolkit_deploy/tests/test_release.py -v
```

Expected: PASS (4 tests)

- [ ] **Step 5: Sanity-check the regex against the real functions.php**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress
python3 -c "
from pathlib import Path
import sys
sys.path.insert(0, 'scripts')
from toolkit_deploy.release import read_release_marker
print(read_release_marker(Path('wp-content/themes/eduma-child/functions.php')))
"
```

Expected output: `2026.08.07.2` (confirms the regex matches the actual live file, not just the test fixture)

- [ ] **Step 6: Commit**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress
git add scripts/toolkit_deploy/release.py scripts/toolkit_deploy/tests/test_release.py
git commit -m "feat(deploy): add release.py — read/bump the theme release marker"
```

---

### Task 4: `cpanel.py` — cPanel UAPI client (Fileman get/upload)

**Files:**
- Create: `scripts/toolkit_deploy/cpanel.py`
- Test: `scripts/toolkit_deploy/tests/test_cpanel.py`

**Interfaces:**
- Consumes: nothing from other `toolkit_deploy` modules.
- Produces (used by `deploy.py`):
  - `class CPanelError(Exception)`
  - `class CPanelClient: def __init__(self, host: str, auth: str, timeout: int = 30)`
  - `CPanelClient.get_file_content(remote_dir: str, filename: str) -> str | None` (`None` means the file does not exist remotely — a new file)
  - `CPanelClient.upload_file(remote_dir: str, filename: str, local_path: Path) -> None`

**Note on the "not found" heuristic:** cPanel's `Fileman/get_file_content` failure-response wording for a genuinely missing file has not been confirmed against a live call in this plan (this session only observed the error text for a different bug — a malformed `file` parameter containing a `/`). `_is_not_found_error` is written as an isolated, unit-tested pure function specifically so this heuristic can be corrected later without touching the rest of `cpanel.py`. **Before relying on this in a real deploy of a brand-new file, manually run `get_file_content` against a path you know doesn't exist on demo and confirm the error text matches** — this is a flagged assumption, not a guess presented as fact.

- [ ] **Step 1: Write the failing tests**

Create `scripts/toolkit_deploy/tests/test_cpanel.py`:

```python
import base64
import json
from unittest.mock import MagicMock, patch

import pytest

from toolkit_deploy.cpanel import CPanelClient, CPanelError, _build_multipart_body, _is_not_found_error


def _fake_response(payload: dict, status: int = 200):
    body = json.dumps(payload).encode("utf-8")
    mock_response = MagicMock()
    mock_response.read.return_value = body
    mock_response.status = status
    mock_response.__enter__.return_value = mock_response
    mock_response.__exit__.return_value = False
    return mock_response


def test_client_sends_basic_auth_header():
    client = CPanelClient("https://wp46.host-ww.net:2083", "bfyigiln:secret")
    expected = "Basic " + base64.b64encode(b"bfyigiln:secret").decode("ascii")
    assert client._auth_header == expected


def test_get_file_content_returns_content_on_success():
    client = CPanelClient("https://wp46.host-ww.net:2083", "bfyigiln:secret")
    payload = {"status": 1, "data": {"content": "<?php\n// hello\n"}}
    with patch("urllib.request.urlopen", return_value=_fake_response(payload)) as mock_urlopen:
        content = client.get_file_content("public_html/wp-content/themes/eduma-child", "functions.php")
    assert content == "<?php\n// hello\n"
    called_request = mock_urlopen.call_args[0][0]
    assert "Fileman/get_file_content" in called_request.full_url
    assert called_request.headers["Authorization"].startswith("Basic ")


def test_get_file_content_returns_none_for_not_found():
    client = CPanelClient("https://wp46.host-ww.net:2083", "bfyigiln:secret")
    payload = {"status": 0, "errors": ["The system could not find the file speak-up.php."]}
    with patch("urllib.request.urlopen", return_value=_fake_response(payload)):
        content = client.get_file_content("public_html/wp-content/themes/eduma-child", "new-file.php")
    assert content is None


def test_get_file_content_raises_on_other_error():
    client = CPanelClient("https://wp46.host-ww.net:2083", "bfyigiln:secret")
    payload = {"status": 0, "errors": ["Permission denied."]}
    with patch("urllib.request.urlopen", return_value=_fake_response(payload)):
        with pytest.raises(CPanelError, match="Permission denied"):
            client.get_file_content("public_html/wp-content/themes/eduma-child", "functions.php")


def test_upload_file_success(tmp_path):
    client = CPanelClient("https://wp46.host-ww.net:2083", "bfyigiln:secret")
    local_file = tmp_path / "functions.php"
    local_file.write_text("<?php\n")
    payload = {"status": 1, "data": {}}
    with patch("urllib.request.urlopen", return_value=_fake_response(payload)) as mock_urlopen:
        client.upload_file("public_html/wp-content/themes/eduma-child", "functions.php", local_file)
    called_request = mock_urlopen.call_args[0][0]
    assert "Fileman/upload_files" in called_request.full_url
    assert called_request.data is not None
    assert b"functions.php" in called_request.data


def test_upload_file_raises_on_failure(tmp_path):
    client = CPanelClient("https://wp46.host-ww.net:2083", "bfyigiln:secret")
    local_file = tmp_path / "functions.php"
    local_file.write_text("<?php\n")
    payload = {"status": 0, "errors": ["Disk quota exceeded."]}
    with patch("urllib.request.urlopen", return_value=_fake_response(payload)):
        with pytest.raises(CPanelError, match="Disk quota exceeded"):
            client.upload_file("public_html/wp-content/themes/eduma-child", "functions.php", local_file)


def test_is_not_found_error_matches_expected_phrasing():
    assert _is_not_found_error(["The system could not find the file x.php."]) is True
    assert _is_not_found_error(["does not exist"]) is True
    assert _is_not_found_error(["Permission denied."]) is False
    assert _is_not_found_error([]) is False


def test_build_multipart_body_contains_field_and_file():
    body = _build_multipart_body(
        boundary="TESTBOUNDARY",
        fields={"dir": "public_html/x", "overwrite": "1"},
        file_field="file-1",
        filename="functions.php",
        file_content=b"<?php\n",
    )
    assert b'name="dir"' in body
    assert b"public_html/x" in body
    assert b'name="file-1"; filename="functions.php"' in body
    assert b"<?php\n" in body
    assert body.startswith(b"--TESTBOUNDARY\r\n")
    assert body.endswith(b"--TESTBOUNDARY--\r\n")
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts && python3 -m pytest toolkit_deploy/tests/test_cpanel.py -v
```

Expected: FAIL with `ModuleNotFoundError: No module named 'toolkit_deploy.cpanel'`

- [ ] **Step 3: Write `cpanel.py`**

Create `scripts/toolkit_deploy/cpanel.py`:

```python
"""cPanel UAPI client: read/write theme files via Fileman's HTTP JSON API.

stdlib-only: uses urllib for HTTP and a hand-rolled multipart/form-data
encoder for uploads, since the standard library has no HTTP client with
built-in multipart support.
"""
from __future__ import annotations

import base64
import json
import mimetypes
import urllib.error
import urllib.request
import uuid
from pathlib import Path
from typing import Optional
from urllib.parse import urlencode


class CPanelError(Exception):
    """Raised when a cPanel UAPI call fails (network error, non-2xx
    status, or a JSON response whose status field indicates failure)."""


def _is_not_found_error(errors: list) -> bool:
    """Best-effort detection of a 'file does not exist' response, as
    opposed to some other failure (permissions, quota, etc.). See the
    module-level note in the implementation plan: this has not been
    confirmed against a live cPanel call for a genuinely missing file and
    should be verified/extended the first time this path is actually
    exercised against demo."""
    haystack = " ".join(str(e).lower() for e in errors)
    return "does not exist" in haystack or "could not find the file" in haystack


class CPanelClient:
    def __init__(self, host: str, auth: str, timeout: int = 30):
        """host: e.g. 'https://wp46.host-ww.net:2083'
        auth: 'user:password' HTTP Basic credential
        """
        self.host = host.rstrip("/")
        self._auth_header = "Basic " + base64.b64encode(auth.encode("utf-8")).decode("ascii")
        self.timeout = timeout

    def _request(self, url: str, data: Optional[bytes] = None, headers: Optional[dict] = None) -> dict:
        req_headers = {"Authorization": self._auth_header}
        if headers:
            req_headers.update(headers)
        request = urllib.request.Request(url, data=data, headers=req_headers)
        try:
            with urllib.request.urlopen(request, timeout=self.timeout) as response:
                body = response.read()
        except urllib.error.HTTPError as exc:
            raise CPanelError(f"HTTP {exc.code} from {url}: {exc.read()[:300]!r}") from exc
        except urllib.error.URLError as exc:
            raise CPanelError(f"network error calling {url}: {exc}") from exc
        try:
            parsed = json.loads(body)
        except json.JSONDecodeError as exc:
            raise CPanelError(f"non-JSON response from {url}: {body[:300]!r}") from exc
        return parsed

    def get_file_content(self, remote_dir: str, filename: str) -> Optional[str]:
        """Return the file's current content, or None if it does not
        exist remotely (a new file this deploy is introducing)."""
        query = urlencode({"dir": remote_dir, "file": filename})
        url = f"{self.host}/execute/Fileman/get_file_content?{query}"
        parsed = self._request(url)
        if not parsed.get("status"):
            errors = parsed.get("errors") or []
            if _is_not_found_error(errors):
                return None
            raise CPanelError(f"get_file_content failed for {remote_dir}/{filename}: {errors}")
        return parsed["data"]["content"]

    def upload_file(self, remote_dir: str, filename: str, local_path: Path) -> None:
        url = f"{self.host}/execute/Fileman/upload_files"
        boundary = uuid.uuid4().hex
        body = _build_multipart_body(
            boundary=boundary,
            fields={"dir": remote_dir, "overwrite": "1"},
            file_field="file-1",
            filename=filename,
            file_content=local_path.read_bytes(),
        )
        headers = {"Content-Type": f"multipart/form-data; boundary={boundary}"}
        parsed = self._request(url, data=body, headers=headers)
        if not parsed.get("status"):
            raise CPanelError(f"upload_file failed for {remote_dir}/{filename}: {parsed.get('errors')}")


def _build_multipart_body(
    boundary: str, fields: dict, file_field: str, filename: str, file_content: bytes
) -> bytes:
    parts: list = []
    for key, value in fields.items():
        parts.append(
            (
                f"--{boundary}\r\n"
                f'Content-Disposition: form-data; name="{key}"\r\n\r\n'
                f"{value}\r\n"
            ).encode("utf-8")
        )
    content_type = mimetypes.guess_type(filename)[0] or "application/octet-stream"
    parts.append(
        (
            f"--{boundary}\r\n"
            f'Content-Disposition: form-data; name="{file_field}"; filename="{filename}"\r\n'
            f"Content-Type: {content_type}\r\n\r\n"
        ).encode("utf-8")
    )
    parts.append(file_content)
    parts.append(b"\r\n")
    parts.append(f"--{boundary}--\r\n".encode("utf-8"))
    return b"".join(parts)
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts && python3 -m pytest toolkit_deploy/tests/test_cpanel.py -v
```

Expected: PASS (8 tests)

- [ ] **Step 5: Commit**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress
git add scripts/toolkit_deploy/cpanel.py scripts/toolkit_deploy/tests/test_cpanel.py
git commit -m "feat(deploy): add cpanel.py — Fileman UAPI client"
```

---

### Task 5: `verify.py` — cache-busted + bare recheck verification

**Files:**
- Create: `scripts/toolkit_deploy/verify.py`
- Test: `scripts/toolkit_deploy/tests/test_verify.py`

**Interfaces:**
- Consumes: nothing from other `toolkit_deploy` modules.
- Produces (used by `deploy.py`):
  - `class VerificationError(Exception)`
  - `@dataclass(frozen=True) class RouteCheck: path: str; expect_status: int = 200; expect_release: bool = True`
  - `verify_route(base_url: str, route: RouteCheck, expected_version: str, settle_seconds: float = 2.0) -> None`
  - `verify_environment(base_url: str, routes: list[RouteCheck], expected_version: str) -> None`

- [ ] **Step 1: Write the failing tests**

Create `scripts/toolkit_deploy/tests/test_verify.py`:

```python
from unittest.mock import MagicMock, patch

import pytest

from toolkit_deploy.verify import RouteCheck, VerificationError, verify_environment, verify_route


def _fake_response(status: int, headers: dict):
    mock_response = MagicMock()
    mock_response.status = status
    mock_response.headers = headers
    mock_response.__enter__.return_value = mock_response
    mock_response.__exit__.return_value = False
    return mock_response


def test_verify_route_passes_when_both_requests_match(monkeypatch):
    monkeypatch.setattr("time.sleep", lambda seconds: None)
    responses = [
        _fake_response(200, {"X-Toolkit-Release": "2026.08.07.2"}),
        _fake_response(200, {"X-Toolkit-Release": "2026.08.07.2"}),
    ]
    with patch("urllib.request.urlopen", side_effect=responses):
        verify_route("https://demo.toolkitafrica.ac.ke", RouteCheck(path="/"), "2026.08.07.2")


def test_verify_route_fails_on_wrong_status(monkeypatch):
    monkeypatch.setattr("time.sleep", lambda seconds: None)
    responses = [_fake_response(500, {})]
    with patch("urllib.request.urlopen", side_effect=responses):
        with pytest.raises(VerificationError, match="expected status 200, got 500"):
            verify_route("https://demo.toolkitafrica.ac.ke", RouteCheck(path="/"), "2026.08.07.2")


def test_verify_route_fails_when_bare_request_is_stale(monkeypatch):
    """Reproduces the 2026-08-07 bug: cache-busted request is correct,
    but the bare (real-visitor) request still returns the old cached
    response."""
    monkeypatch.setattr("time.sleep", lambda seconds: None)
    responses = [
        _fake_response(200, {"X-Toolkit-Release": "2026.08.07.2"}),  # cache-busted: fresh
        _fake_response(404, {}),  # bare: stale cached 404
    ]
    with patch("urllib.request.urlopen", side_effect=responses):
        with pytest.raises(VerificationError, match="bare request"):
            verify_route("https://toolkitafrica.ac.ke", RouteCheck(path="/speak-up/"), "2026.08.07.2")


def test_verify_route_skips_release_header_check_when_disabled(monkeypatch):
    monkeypatch.setattr("time.sleep", lambda seconds: None)
    responses = [
        _fake_response(404, {}),
        _fake_response(404, {}),
    ]
    with patch("urllib.request.urlopen", side_effect=responses):
        verify_route(
            "https://toolkitafrica.ac.ke",
            RouteCheck(path="/speak-up/", expect_status=404, expect_release=False),
            "2026.08.07.2",
        )


def test_verify_environment_checks_every_route(monkeypatch):
    monkeypatch.setattr("time.sleep", lambda seconds: None)
    responses = [
        _fake_response(200, {"X-Toolkit-Release": "2026.08.07.2"}),
        _fake_response(200, {"X-Toolkit-Release": "2026.08.07.2"}),
        _fake_response(200, {"X-Toolkit-Release": "2026.08.07.2"}),
        _fake_response(200, {"X-Toolkit-Release": "2026.08.07.2"}),
    ]
    with patch("urllib.request.urlopen", side_effect=responses):
        verify_environment(
            "https://demo.toolkitafrica.ac.ke",
            [RouteCheck(path="/"), RouteCheck(path="/our-ventures/")],
            "2026.08.07.2",
        )
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts && python3 -m pytest toolkit_deploy/tests/test_verify.py -v
```

Expected: FAIL with `ModuleNotFoundError: No module named 'toolkit_deploy.verify'`

- [ ] **Step 3: Write `verify.py`**

Create `scripts/toolkit_deploy/verify.py`:

```python
"""Post-deploy HTTP verification: cache-busted request then bare recheck.

Catches the class of bug where WordPress serves the correct page but a
stale cached response (e.g. LiteSpeed page cache) is still served to real
visitors on the bare URL — exactly the 2026-08-07 production incident this
tool exists to prevent.
"""
from __future__ import annotations

import time
import urllib.error
import urllib.request
import uuid
from dataclasses import dataclass


class VerificationError(Exception):
    """Raised when a route fails verification (wrong status or release
    header)."""


@dataclass(frozen=True)
class RouteCheck:
    path: str
    expect_status: int = 200
    expect_release: bool = True


def _fetch(base_url: str, path: str, cache_bust: bool) -> tuple:
    url = base_url.rstrip("/") + path
    if cache_bust:
        separator = "&" if "?" in url else "?"
        url = f"{url}{separator}cb={uuid.uuid4().hex}"
    request = urllib.request.Request(url, method="GET")
    try:
        with urllib.request.urlopen(request, timeout=30) as response:
            return response.status, dict(response.headers)
    except urllib.error.HTTPError as exc:
        return exc.code, dict(exc.headers or {})


def verify_route(
    base_url: str, route: RouteCheck, expected_version: str, settle_seconds: float = 2.0
) -> None:
    for cache_bust in (True, False):
        if not cache_bust:
            time.sleep(settle_seconds)
        status, headers = _fetch(base_url, route.path, cache_bust)
        phase = "cache-busted request" if cache_bust else "bare request"
        if status != route.expect_status:
            raise VerificationError(
                f"{route.path} ({phase}): expected status {route.expect_status}, got {status}"
            )
        if route.expect_release:
            release_header = headers.get("X-Toolkit-Release")
            if release_header != expected_version:
                raise VerificationError(
                    f"{route.path} ({phase}): expected X-Toolkit-Release "
                    f"{expected_version!r}, got {release_header!r}"
                )


def verify_environment(base_url: str, routes: list, expected_version: str) -> None:
    for route in routes:
        verify_route(base_url, route, expected_version)
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts && python3 -m pytest toolkit_deploy/tests/test_verify.py -v
```

Expected: PASS (5 tests)

- [ ] **Step 5: Commit**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress
git add scripts/toolkit_deploy/verify.py scripts/toolkit_deploy/tests/test_verify.py
git commit -m "feat(deploy): add verify.py — cache-bust + bare recheck verification"
```

---

### Task 6: `deploy.py` (part 1) — `compute_diff`

**Files:**
- Create: `scripts/toolkit_deploy/deploy.py`
- Test: `scripts/toolkit_deploy/tests/test_deploy_diff.py`

**Interfaces:**
- Consumes: nothing from other `toolkit_deploy` modules (pure git/subprocess wrapper).
- Produces (used later in this same module, Task 7):
  - `THEME_SUBPATH = "wp-content/themes/eduma-child"`
  - `class GitDiffError(Exception)`
  - `@dataclass(frozen=True) class DiffResult: modified_or_added: list[str]; deleted: list[str]` (paths are **relative to the theme root**, e.g. `"inc/support-hub.php"`, not repo-relative)
  - `compute_diff(repo_root: Path, baseline_commit: str) -> DiffResult`

- [ ] **Step 1: Write the failing tests**

Create `scripts/toolkit_deploy/tests/test_deploy_diff.py`:

```python
import subprocess
from pathlib import Path

import pytest

from toolkit_deploy.deploy import GitDiffError, compute_diff


def _run(cmd, cwd):
    subprocess.run(cmd, cwd=cwd, check=True, capture_output=True, text=True)


@pytest.fixture
def theme_repo(tmp_path):
    repo = tmp_path / "repo"
    repo.mkdir()
    _run(["git", "init"], repo)
    _run(["git", "config", "user.email", "test@example.com"], repo)
    _run(["git", "config", "user.name", "Test"], repo)

    theme = repo / "wp-content" / "themes" / "eduma-child"
    theme.mkdir(parents=True)
    (theme / "functions.php").write_text("<?php\n// v1\n")
    (theme / "inc").mkdir()
    (theme / "inc" / "support-hub.php").write_text("<?php\n// v1\n")
    (repo / "unrelated.md").write_text("not in theme\n")
    _run(["git", "add", "."], repo)
    _run(["git", "commit", "-m", "initial"], repo)
    baseline = subprocess.run(
        ["git", "rev-parse", "HEAD"], cwd=repo, capture_output=True, text=True, check=True
    ).stdout.strip()

    return repo, theme, baseline


def test_compute_diff_no_changes_returns_empty(theme_repo):
    repo, theme, baseline = theme_repo
    result = compute_diff(repo, baseline)
    assert result.modified_or_added == []
    assert result.deleted == []


def test_compute_diff_detects_modified_and_added_files(theme_repo):
    repo, theme, baseline = theme_repo
    (theme / "functions.php").write_text("<?php\n// v2\n")
    (theme / "template-parts").mkdir()
    (theme / "template-parts" / "new.php").write_text("<?php\n// new\n")
    _run(["git", "add", "."], repo)
    _run(["git", "commit", "-m", "second"], repo)

    result = compute_diff(repo, baseline)

    assert sorted(result.modified_or_added) == ["functions.php", "template-parts/new.php"]
    assert result.deleted == []


def test_compute_diff_detects_deleted_files(theme_repo):
    repo, theme, baseline = theme_repo
    (theme / "inc" / "support-hub.php").unlink()
    _run(["git", "add", "."], repo)
    _run(["git", "commit", "-m", "delete file"], repo)

    result = compute_diff(repo, baseline)

    assert result.deleted == ["inc/support-hub.php"]
    assert result.modified_or_added == []


def test_compute_diff_ignores_files_outside_theme(theme_repo):
    repo, theme, baseline = theme_repo
    (repo / "unrelated.md").write_text("changed\n")
    _run(["git", "add", "."], repo)
    _run(["git", "commit", "-m", "unrelated change"], repo)

    result = compute_diff(repo, baseline)

    assert result.modified_or_added == []
    assert result.deleted == []


def test_compute_diff_rename_collapses_to_delete_and_add(theme_repo):
    repo, theme, baseline = theme_repo
    (theme / "inc" / "support-hub.php").rename(theme / "inc" / "support-hub-renamed.php")
    _run(["git", "add", "."], repo)
    _run(["git", "commit", "-m", "rename"], repo)

    result = compute_diff(repo, baseline)

    # --no-renames means this shows as a delete + add, not a single R100 line
    assert "inc/support-hub.php" in result.deleted
    assert "inc/support-hub-renamed.php" in result.modified_or_added


def test_compute_diff_invalid_baseline_raises(theme_repo):
    repo, theme, baseline = theme_repo
    with pytest.raises(GitDiffError):
        compute_diff(repo, "not-a-real-commit")
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts && python3 -m pytest toolkit_deploy/tests/test_deploy_diff.py -v
```

Expected: FAIL with `ModuleNotFoundError: No module named 'toolkit_deploy.deploy'`

- [ ] **Step 3: Write `deploy.py` (diff portion only)**

Create `scripts/toolkit_deploy/deploy.py`:

```python
"""Diff computation, backup, release-bump, upload and verification
orchestration for a single environment deploy."""
from __future__ import annotations

import subprocess
from dataclasses import dataclass
from pathlib import Path

THEME_SUBPATH = "wp-content/themes/eduma-child"


class GitDiffError(Exception):
    pass


@dataclass(frozen=True)
class DiffResult:
    modified_or_added: list
    deleted: list


def compute_diff(repo_root: Path, baseline_commit: str) -> DiffResult:
    """Diff the theme directory between baseline_commit and HEAD.
    Returned paths are relative to the theme root (e.g.
    'inc/support-hub.php'), not repo-relative."""
    result = subprocess.run(
        ["git", "diff", "--no-renames", "--name-status", baseline_commit, "HEAD", "--", THEME_SUBPATH],
        cwd=repo_root,
        capture_output=True,
        text=True,
    )
    if result.returncode != 0:
        raise GitDiffError(f"git diff failed: {result.stderr.strip()}")

    prefix = THEME_SUBPATH + "/"
    modified_or_added: list = []
    deleted: list = []
    for line in result.stdout.splitlines():
        if not line.strip():
            continue
        status, _, path = line.partition("\t")
        rel_path = path[len(prefix):] if path.startswith(prefix) else path
        if status == "D":
            deleted.append(rel_path)
        else:
            modified_or_added.append(rel_path)
    return DiffResult(modified_or_added=modified_or_added, deleted=deleted)
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts && python3 -m pytest toolkit_deploy/tests/test_deploy_diff.py -v
```

Expected: PASS (6 tests)

- [ ] **Step 5: Commit**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress
git add scripts/toolkit_deploy/deploy.py scripts/toolkit_deploy/tests/test_deploy_diff.py
git commit -m "feat(deploy): add deploy.py compute_diff — theme-scoped git diff"
```

---

### Task 7: `deploy.py` (part 2) — `run_deploy` orchestration

**Files:**
- Modify: `scripts/toolkit_deploy/deploy.py`
- Test: `scripts/toolkit_deploy/tests/test_run_deploy.py`

**Interfaces:**
- Consumes:
  - From Task 1 (`state.py`): `load_state`, `save_state`, `compute_next_version`, `record_deploy`, `check_production_gate`, `ProductionGateError`
  - From Task 2 (`config.py`): `Config`, `EnvironmentConfig`
  - From Task 3 (`release.py`): `bump_release_marker`
  - From Task 4 (`cpanel.py`): `CPanelClient` (used via its public interface only — this task's tests use a hand-written fake, not the real class)
  - From Task 5 (`verify.py`): `RouteCheck`, `verify_environment`, `VerificationError`
  - From Task 6 (this module): `compute_diff`, `THEME_SUBPATH`
- Produces (used by `__main__.py` in Task 9):
  - `FUNCTIONS_PHP = "functions.php"`
  - `class DeployAbort(Exception)`
  - `@dataclass class DeployOutcome: version: str; uploaded: list`
  - `run_deploy(env: str, repo_root: Path, theme_root: Path, config: Config, state_path: Path, client, today) -> DeployOutcome`

- [ ] **Step 1: Write the failing tests**

Create `scripts/toolkit_deploy/tests/test_run_deploy.py`:

```python
import subprocess
from datetime import date
from pathlib import Path

import pytest

from toolkit_deploy.config import Config, EnvironmentConfig
from toolkit_deploy.deploy import DeployAbort, run_deploy
from toolkit_deploy.state import ProductionGateError, bootstrap_state, load_state, save_state


def _run(cmd, cwd):
    subprocess.run(cmd, cwd=cwd, check=True, capture_output=True, text=True)


class FakeCPanelClient:
    """Records calls; simulates remote file content by path. A path not
    present in `remote_files` simulates a brand-new file (get returns
    None)."""

    def __init__(self, remote_files: dict, fail_on: str = None):
        self.remote_files = dict(remote_files)
        self.fail_on = fail_on
        self.uploaded = []
        self.get_calls = []

    def get_file_content(self, remote_dir, filename):
        key = f"{remote_dir}/{filename}"
        self.get_calls.append(key)
        return self.remote_files.get(key)

    def upload_file(self, remote_dir, filename, local_path):
        key = f"{remote_dir}/{filename}"
        if self.fail_on and key.endswith(self.fail_on):
            raise RuntimeError(f"simulated failure uploading {key}")
        self.uploaded.append(key)


@pytest.fixture
def project(tmp_path, monkeypatch):
    repo = tmp_path / "repo"
    repo.mkdir()
    _run(["git", "init"], repo)
    _run(["git", "config", "user.email", "test@example.com"], repo)
    _run(["git", "config", "user.name", "Test"], repo)

    theme = repo / "wp-content" / "themes" / "eduma-child"
    (theme / "inc").mkdir(parents=True)
    functions_php = theme / "functions.php"
    functions_php.write_text(
        "<?php\n\nfunction toolkit_theme_release() {\n\treturn '2026.08.07.2';\n}\n"
    )
    (theme / "inc" / "support-hub.php").write_text("<?php\n// v1\n")
    _run(["git", "add", "."], repo)
    _run(["git", "commit", "-m", "initial"], repo)
    baseline_commit = subprocess.run(
        ["git", "rev-parse", "HEAD"], cwd=repo, capture_output=True, text=True, check=True
    ).stdout.strip()

    state_path = tmp_path / "state.json"
    save_state(state_path, bootstrap_state(baseline_commit, "2026.08.07.2", "2026-08-07T00:00:00+00:00"))

    env_config = EnvironmentConfig(
        name="demo",
        host="https://wp46.host-ww.net:2083",
        remote_base_dir="demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child",
        base_url="https://demo.toolkitafrica.ac.ke",
        routes=[],
    )
    prod_config = EnvironmentConfig(
        name="production",
        host="https://wp46.host-ww.net:2083",
        remote_base_dir="public_html/wp-content/themes/eduma-child",
        base_url="https://toolkitafrica.ac.ke",
        routes=[],
    )
    config = Config(
        cpanel_host="https://wp46.host-ww.net:2083",
        cpanel_auth="bfyigiln:secret",
        environments={"demo": env_config, "production": prod_config},
    )

    monkeypatch.setattr("toolkit_deploy.verify.verify_environment", lambda *a, **k: None)

    return {
        "repo": repo,
        "theme": theme,
        "functions_php": functions_php,
        "baseline_commit": baseline_commit,
        "state_path": state_path,
        "config": config,
    }


def test_run_deploy_no_op_on_empty_diff(project):
    client = FakeCPanelClient(remote_files={})
    with pytest.raises(DeployAbort, match="nothing to deploy"):
        run_deploy(
            "demo", project["repo"], project["theme"], project["config"], project["state_path"], client, date(2026, 8, 7)
        )
    assert client.uploaded == []


def test_run_deploy_uploads_changed_file_and_forces_functions_php(project):
    (project["theme"] / "inc" / "support-hub.php").write_text("<?php\n// v2\n")
    _run(["git", "add", "."], project["repo"])
    _run(["git", "commit", "-m", "change support-hub"], project["repo"])

    remote_files = {
        "demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child/inc/support-hub.php": "<?php\n// v1\n",
        "demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child/functions.php": (
            "<?php\n\nfunction toolkit_theme_release() {\n\treturn '2026.08.07.2';\n}\n"
        ),
    }
    client = FakeCPanelClient(remote_files=remote_files)

    outcome = run_deploy(
        "demo", project["repo"], project["theme"], project["config"], project["state_path"], client, date(2026, 8, 7)
    )

    assert outcome.version == "2026.08.07.3"
    assert set(outcome.uploaded) == {
        "demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child/inc/support-hub.php",
        "demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child/functions.php",
    }
    # functions.php uploaded last
    assert outcome.uploaded[-1].endswith("functions.php")
    # local functions.php was bumped
    assert "2026.08.07.3" in project["functions_php"].read_text()

    state = load_state(project["state_path"])
    assert state["demo"]["version"] == "2026.08.07.3"
    assert state["demo"]["verified"] is True

    backup_dir = project["repo"] / "rollbacks" / "demo-pre-2026.08.07.3"
    assert (backup_dir / "inc" / "support-hub.php").read_text() == "<?php\n// v1\n"
    assert "2026.08.07.2" in (backup_dir / "functions.php").read_text()


def test_run_deploy_backs_up_functions_php_even_when_not_in_diff(project):
    (project["theme"] / "inc" / "support-hub.php").write_text("<?php\n// v2\n")
    _run(["git", "add", "."], project["repo"])
    _run(["git", "commit", "-m", "change support-hub only"], project["repo"])

    remote_files = {
        "demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child/inc/support-hub.php": "<?php\n// v1\n",
        "demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child/functions.php": (
            "<?php\n\nfunction toolkit_theme_release() {\n\treturn '2026.08.07.2';\n}\n"
        ),
    }
    client = FakeCPanelClient(remote_files=remote_files)

    run_deploy(
        "demo", project["repo"], project["theme"], project["config"], project["state_path"], client, date(2026, 8, 7)
    )

    backup_dir = project["repo"] / "rollbacks" / "demo-pre-2026.08.07.3"
    # this is the exact bug fixed in the spec review: functions.php must be
    # backed up on every deploy, even one that didn't otherwise touch it
    assert backup_dir.joinpath("functions.php").exists()


def test_run_deploy_new_file_recorded_in_new_files_manifest(project):
    (project["theme"] / "template-parts").mkdir()
    (project["theme"] / "template-parts" / "new-page.php").write_text("<?php\n// new\n")
    _run(["git", "add", "."], project["repo"])
    _run(["git", "commit", "-m", "add new page"], project["repo"])

    remote_files = {
        "demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child/functions.php": (
            "<?php\n\nfunction toolkit_theme_release() {\n\treturn '2026.08.07.2';\n}\n"
        ),
        # no entry for template-parts/new-page.php -> simulates "not found" (new file)
    }
    client = FakeCPanelClient(remote_files=remote_files)

    run_deploy(
        "demo", project["repo"], project["theme"], project["config"], project["state_path"], client, date(2026, 8, 7)
    )

    backup_dir = project["repo"] / "rollbacks" / "demo-pre-2026.08.07.3"
    manifest = (backup_dir / "NEW_FILES.txt").read_text()
    assert "template-parts/new-page.php" in manifest
    assert not (backup_dir / "template-parts" / "new-page.php").exists()


def test_run_deploy_deletion_aborts_before_network_call(project):
    (project["theme"] / "inc" / "support-hub.php").unlink()
    _run(["git", "add", "."], project["repo"])
    _run(["git", "commit", "-m", "delete file"], project["repo"])

    client = FakeCPanelClient(remote_files={})

    with pytest.raises(DeployAbort, match="inc/support-hub.php"):
        run_deploy(
            "demo", project["repo"], project["theme"], project["config"], project["state_path"], client, date(2026, 8, 7)
        )
    assert client.get_calls == []
    assert client.uploaded == []


def test_run_deploy_partial_upload_failure_does_not_update_state(project):
    (project["theme"] / "inc" / "support-hub.php").write_text("<?php\n// v2\n")
    _run(["git", "add", "."], project["repo"])
    _run(["git", "commit", "-m", "change support-hub"], project["repo"])

    remote_files = {
        "demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child/inc/support-hub.php": "<?php\n// v1\n",
        "demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child/functions.php": (
            "<?php\n\nfunction toolkit_theme_release() {\n\treturn '2026.08.07.2';\n}\n"
        ),
    }
    client = FakeCPanelClient(remote_files=remote_files, fail_on="functions.php")

    with pytest.raises(DeployAbort, match="upload failed"):
        run_deploy(
            "demo", project["repo"], project["theme"], project["config"], project["state_path"], client, date(2026, 8, 7)
        )

    state = load_state(project["state_path"])
    assert state["demo"]["version"] == "2026.08.07.2"  # unchanged from bootstrap


def test_run_deploy_production_blocked_when_demo_not_yet_deployed_at_head(project):
    (project["theme"] / "inc" / "support-hub.php").write_text("<?php\n// v2\n")
    _run(["git", "add", "."], project["repo"])
    _run(["git", "commit", "-m", "change support-hub"], project["repo"])

    client = FakeCPanelClient(remote_files={})

    with pytest.raises(ProductionGateError):
        run_deploy(
            "production", project["repo"], project["theme"], project["config"], project["state_path"], client, date(2026, 8, 7)
        )
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts && python3 -m pytest toolkit_deploy/tests/test_run_deploy.py -v
```

Expected: FAIL with `ImportError: cannot import name 'run_deploy'`

- [ ] **Step 3: Extend `deploy.py` with `run_deploy`**

Append to `scripts/toolkit_deploy/deploy.py` (add these imports at the top of the file alongside the existing ones, and the new code below the existing `compute_diff`):

```python
# Add to the top imports:
from datetime import datetime, timezone
from pathlib import PurePosixPath

from toolkit_deploy import release as release_mod
from toolkit_deploy import state as state_mod
from toolkit_deploy import verify as verify_mod
```

```python
# Append below compute_diff:

FUNCTIONS_PHP = "functions.php"


class DeployAbort(Exception):
    """Raised for expected early-exit conditions: nothing to deploy,
    deletions present, or a partial upload failure."""


@dataclass
class DeployOutcome:
    version: str
    uploaded: list


def _split_remote_path(remote_base_dir: str, rel_path: str) -> tuple:
    p = PurePosixPath(rel_path)
    filename = p.name
    subdir = p.parent.as_posix()
    remote_dir = remote_base_dir if subdir == "." else f"{remote_base_dir}/{subdir}"
    return remote_dir, filename


def _now_iso() -> str:
    return datetime.now(timezone.utc).isoformat()


def _git_head(repo_root: Path) -> str:
    result = subprocess.run(
        ["git", "rev-parse", "HEAD"], cwd=repo_root, capture_output=True, text=True
    )
    if result.returncode != 0:
        raise GitDiffError(f"git rev-parse HEAD failed: {result.stderr.strip()}")
    return result.stdout.strip()


def run_deploy(env, repo_root: Path, theme_root: Path, config, state_path: Path, client, today) -> DeployOutcome:
    env_config = config.environments[env]
    state = state_mod.load_state(state_path)
    head_commit = _git_head(repo_root)

    if env == "production":
        state_mod.check_production_gate(state, head_commit)

    env_state = state.get(env)
    if env_state is None:
        raise DeployAbort(f"no baseline recorded for '{env}' — run `bootstrap` first")
    baseline_commit = env_state["commit"]

    diff = compute_diff(repo_root, baseline_commit)

    if diff.deleted:
        raise DeployAbort(
            "deletions are out of scope for this tool — remove these files "
            "manually first: " + ", ".join(diff.deleted)
        )

    if not diff.modified_or_added:
        raise DeployAbort("nothing to deploy")

    changed_paths = list(diff.modified_or_added)
    if FUNCTIONS_PHP not in changed_paths:
        changed_paths.append(FUNCTIONS_PHP)

    next_version = state_mod.compute_next_version(env_state.get("version"), today)
    version_dir = repo_root / "rollbacks" / f"{env}-pre-{next_version}"
    new_files = []

    for rel_path in changed_paths:
        remote_dir, filename = _split_remote_path(env_config.remote_base_dir, rel_path)
        content = client.get_file_content(remote_dir, filename)
        if content is None:
            new_files.append(rel_path)
            continue
        backup_path = version_dir / rel_path
        backup_path.parent.mkdir(parents=True, exist_ok=True)
        backup_path.write_text(content, encoding="utf-8")

    if new_files:
        manifest_path = version_dir / "NEW_FILES.txt"
        manifest_path.parent.mkdir(parents=True, exist_ok=True)
        manifest_path.write_text("\n".join(sorted(new_files)) + "\n", encoding="utf-8")

    release_mod.bump_release_marker(theme_root / FUNCTIONS_PHP, next_version)

    ordered_paths = [p for p in changed_paths if p != FUNCTIONS_PHP] + [FUNCTIONS_PHP]
    uploaded = []
    for rel_path in ordered_paths:
        remote_dir, filename = _split_remote_path(env_config.remote_base_dir, rel_path)
        local_path = theme_root / rel_path
        remote_key = f"{remote_dir}/{filename}"
        try:
            client.upload_file(remote_dir, filename, local_path)
        except Exception as exc:
            remaining = ordered_paths[len(uploaded) + 1 :]
            raise DeployAbort(
                f"upload failed at {rel_path}: {exc}. Succeeded: {uploaded}. "
                f"Not attempted: {remaining}. state.json was NOT updated."
            ) from exc
        uploaded.append(remote_key)

    routes = [
        verify_mod.RouteCheck(path=r.path, expect_status=r.expect_status, expect_release=r.expect_release)
        for r in env_config.routes
    ]
    verify_mod.verify_environment(env_config.base_url, routes, next_version)

    new_state = state_mod.record_deploy(state, env, head_commit, next_version, True, _now_iso())
    state_mod.save_state(state_path, new_state)

    return DeployOutcome(version=next_version, uploaded=uploaded)
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts && python3 -m pytest toolkit_deploy/tests/test_run_deploy.py -v
```

Expected: PASS (7 tests)

- [ ] **Step 5: Run the full suite so far to confirm nothing regressed**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts && python3 -m pytest toolkit_deploy/tests/ -v
```

Expected: PASS (all tests from Tasks 1–7)

- [ ] **Step 6: Commit**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress
git add scripts/toolkit_deploy/deploy.py scripts/toolkit_deploy/tests/test_run_deploy.py
git commit -m "feat(deploy): add run_deploy orchestration — backup, bump, upload, verify"
```

---

### Task 8: `deploy.py` (part 3) — `run_rollback`

**Files:**
- Modify: `scripts/toolkit_deploy/deploy.py`
- Test: `scripts/toolkit_deploy/tests/test_run_rollback.py`

**Interfaces:**
- Consumes: `release_mod.read_release_marker` (Task 3), `verify_mod.RouteCheck`/`verify_environment` (Task 5), the `FakeCPanelClient` pattern from Task 7's tests (each rollback test writes its own).
- Produces (used by `__main__.py` in Task 9):
  - `run_rollback(env: str, version: str, repo_root: Path, config, client) -> list` (returns the list of remote paths uploaded during the rollback)

- [ ] **Step 1: Write the failing tests**

Create `scripts/toolkit_deploy/tests/test_run_rollback.py`:

```python
from pathlib import Path

import pytest

from toolkit_deploy.config import Config, EnvironmentConfig
from toolkit_deploy.deploy import DeployAbort, run_rollback


class FakeCPanelClient:
    def __init__(self):
        self.uploaded = []

    def get_file_content(self, remote_dir, filename):
        raise AssertionError("rollback must not call get_file_content")

    def upload_file(self, remote_dir, filename, local_path):
        self.uploaded.append((f"{remote_dir}/{filename}", local_path.read_text()))


@pytest.fixture
def rollback_snapshot(tmp_path):
    repo = tmp_path / "repo"
    snapshot = repo / "rollbacks" / "demo-pre-2026.08.07.3"
    (snapshot / "inc").mkdir(parents=True)
    (snapshot / "inc" / "support-hub.php").write_text("<?php\n// old\n")
    (snapshot / "functions.php").write_text(
        "<?php\n\nfunction toolkit_theme_release() {\n\treturn '2026.08.07.2';\n}\n"
    )

    env_config = EnvironmentConfig(
        name="demo",
        host="https://wp46.host-ww.net:2083",
        remote_base_dir="demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child",
        base_url="https://demo.toolkitafrica.ac.ke",
        routes=[],
    )
    config = Config(
        cpanel_host="https://wp46.host-ww.net:2083",
        cpanel_auth="bfyigiln:secret",
        environments={"demo": env_config},
    )
    return repo, config


def test_run_rollback_reuploads_backed_up_files_functions_php_last(rollback_snapshot, monkeypatch):
    repo, config = rollback_snapshot
    monkeypatch.setattr("toolkit_deploy.verify.verify_environment", lambda *a, **k: None)
    client = FakeCPanelClient()

    uploaded = run_rollback("demo", "2026.08.07.3", repo, config, client)

    assert uploaded[-1].endswith("functions.php")
    paths = {key for key, _ in client.uploaded}
    assert "demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child/inc/support-hub.php" in paths
    assert "demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child/functions.php" in paths


def test_run_rollback_missing_snapshot_raises(rollback_snapshot, monkeypatch):
    repo, config = rollback_snapshot
    monkeypatch.setattr("toolkit_deploy.verify.verify_environment", lambda *a, **k: None)
    client = FakeCPanelClient()

    with pytest.raises(DeployAbort, match="no rollback snapshot"):
        run_rollback("demo", "2026.08.07.99", repo, config, client)


def test_run_rollback_prints_new_files_for_manual_removal(rollback_snapshot, monkeypatch, capsys):
    repo, config = rollback_snapshot
    (repo / "rollbacks" / "demo-pre-2026.08.07.3" / "NEW_FILES.txt").write_text(
        "template-parts/new-page.php\n"
    )
    monkeypatch.setattr("toolkit_deploy.verify.verify_environment", lambda *a, **k: None)
    client = FakeCPanelClient()

    run_rollback("demo", "2026.08.07.3", repo, config, client)

    captured = capsys.readouterr()
    assert "template-parts/new-page.php" in captured.out
    assert "NOT removed" in captured.out
    # confirm it really did not attempt to delete/upload the new file
    paths = {key for key, _ in client.uploaded}
    assert not any("new-page.php" in p for p in paths)
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts && python3 -m pytest toolkit_deploy/tests/test_run_rollback.py -v
```

Expected: FAIL with `ImportError: cannot import name 'run_rollback'`

- [ ] **Step 3: Append `run_rollback` to `deploy.py`**

```python
# Append to scripts/toolkit_deploy/deploy.py:

def run_rollback(env, version: str, repo_root: Path, config, client) -> list:
    env_config = config.environments[env]
    version_dir = repo_root / "rollbacks" / f"{env}-pre-{version}"
    if not version_dir.exists():
        raise DeployAbort(f"no rollback snapshot found at {version_dir}")

    manifest_path = version_dir / "NEW_FILES.txt"
    new_files = []
    if manifest_path.exists():
        new_files = [
            line.strip() for line in manifest_path.read_text(encoding="utf-8").splitlines() if line.strip()
        ]

    backup_files = sorted(
        p.relative_to(version_dir).as_posix()
        for p in version_dir.rglob("*")
        if p.is_file() and p.name != "NEW_FILES.txt"
    )
    ordered = [p for p in backup_files if p != FUNCTIONS_PHP]
    if FUNCTIONS_PHP in backup_files:
        ordered.append(FUNCTIONS_PHP)

    uploaded = []
    for rel_path in ordered:
        remote_dir, filename = _split_remote_path(env_config.remote_base_dir, rel_path)
        local_path = version_dir / rel_path
        client.upload_file(remote_dir, filename, local_path)
        uploaded.append(f"{remote_dir}/{filename}")

    if new_files:
        print(
            "The following files were introduced by the deploy you just "
            "rolled back and were NOT removed — remove them manually if "
            "they should no longer exist: " + ", ".join(sorted(new_files))
        )

    expected_version = None
    if FUNCTIONS_PHP in backup_files:
        expected_version = release_mod.read_release_marker(version_dir / FUNCTIONS_PHP)

    routes = [
        verify_mod.RouteCheck(
            path=r.path,
            expect_status=r.expect_status,
            expect_release=r.expect_release and expected_version is not None,
        )
        for r in env_config.routes
    ]
    verify_mod.verify_environment(env_config.base_url, routes, expected_version or "")

    return uploaded
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts && python3 -m pytest toolkit_deploy/tests/test_run_rollback.py -v
```

Expected: PASS (3 tests)

- [ ] **Step 5: Run the full suite to confirm nothing regressed**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts && python3 -m pytest toolkit_deploy/tests/ -v
```

Expected: PASS (all tests from Tasks 1–8)

- [ ] **Step 6: Commit**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress
git add scripts/toolkit_deploy/deploy.py scripts/toolkit_deploy/tests/test_run_rollback.py
git commit -m "feat(deploy): add run_rollback — restore snapshot, list new files, re-verify"
```

---

### Task 9: `__main__.py` — CLI wiring

**Files:**
- Create: `scripts/toolkit_deploy/__main__.py`
- Test: `scripts/toolkit_deploy/tests/test_cli.py`

**Interfaces:**
- Consumes: `config.load_config`, `state.load_state`/`save_state`/`bootstrap_state`, `deploy.compute_diff`/`run_deploy`/`run_rollback`/`DeployAbort`, `state.ProductionGateError`, `cpanel.CPanelClient`.
- Produces: a runnable `python3 -m toolkit_deploy <command>` entry point. `main(argv: list[str]) -> int` is the testable core (returns an exit code instead of calling `sys.exit` directly, so tests can call it without spawning a subprocess).

- [ ] **Step 1: Write the failing tests**

Create `scripts/toolkit_deploy/tests/test_cli.py`:

```python
from datetime import date
from pathlib import Path

import pytest

from toolkit_deploy.__main__ import main
from toolkit_deploy.state import load_state


TOML_CONFIG = """
[cpanel]
host = "https://wp46.host-ww.net:2083"

[environments.demo]
remote_base_dir = "demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child"
base_url = "https://demo.toolkitafrica.ac.ke"

[environments.production]
remote_base_dir = "public_html/wp-content/themes/eduma-child"
base_url = "https://toolkitafrica.ac.ke"
"""


@pytest.fixture
def cli_env(tmp_path, monkeypatch):
    config_path = tmp_path / "config.toml"
    config_path.write_text(TOML_CONFIG)
    secrets_path = tmp_path / "secrets.env"
    secrets_path.write_text("CPANEL_AUTH=bfyigiln:secret\n")
    state_path = tmp_path / "state.json"

    monkeypatch.setenv("TOOLKIT_DEPLOY_CONFIG", str(config_path))
    monkeypatch.setenv("TOOLKIT_DEPLOY_SECRETS", str(secrets_path))
    monkeypatch.setenv("TOOLKIT_DEPLOY_STATE", str(state_path))
    return {"config_path": config_path, "secrets_path": secrets_path, "state_path": state_path}


def test_bootstrap_seeds_state(cli_env, capsys):
    exit_code = main(["bootstrap", "--commit", "dec1b6e", "--version", "2026.08.07.2"])
    assert exit_code == 0
    state = load_state(cli_env["state_path"])
    assert state["demo"]["commit"] == "dec1b6e"
    assert state["production"]["version"] == "2026.08.07.2"
    captured = capsys.readouterr()
    assert "bootstrapped" in captured.out.lower()


def test_deploy_unknown_environment_returns_error(cli_env, capsys):
    exit_code = main(["deploy", "staging"])
    assert exit_code != 0
    captured = capsys.readouterr()
    assert "staging" in captured.err.lower() or "unknown" in captured.err.lower()


def test_missing_config_returns_clear_error(tmp_path, monkeypatch, capsys):
    # bootstrap deliberately does not require config.toml (it only writes
    # state.json), so it can't be used to exercise this path — use `diff`,
    # which does load config first.
    monkeypatch.setenv("TOOLKIT_DEPLOY_CONFIG", str(tmp_path / "missing.toml"))
    monkeypatch.setenv("TOOLKIT_DEPLOY_SECRETS", str(tmp_path / "missing.env"))
    monkeypatch.setenv("TOOLKIT_DEPLOY_STATE", str(tmp_path / "state.json"))

    exit_code = main(["diff", "demo"])

    assert exit_code != 0
    captured = capsys.readouterr()
    assert "not found" in captured.err.lower()


def test_deploy_success_prints_commit_reminder(cli_env, monkeypatch, capsys):
    import toolkit_deploy.deploy as deploy_mod

    fake_outcome = deploy_mod.DeployOutcome(version="2026.08.08.1", uploaded=["a", "b"])
    monkeypatch.setattr(deploy_mod, "run_deploy", lambda *a, **k: fake_outcome)

    exit_code = main(["deploy", "demo"])

    assert exit_code == 0
    captured = capsys.readouterr()
    assert "2026.08.08.1" in captured.out
    assert "reminder" in captured.out.lower()
    assert "commit" in captured.out.lower()
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts && python3 -m pytest toolkit_deploy/tests/test_cli.py -v
```

Expected: FAIL with `ModuleNotFoundError: No module named 'toolkit_deploy.__main__'`

- [ ] **Step 3: Write `__main__.py`**

Create `scripts/toolkit_deploy/__main__.py`:

```python
"""CLI entry point: python3 -m toolkit_deploy <command> [args]

Run from the scripts/ directory (or with scripts/ on PYTHONPATH):
    cd scripts && python3 -m toolkit_deploy diff demo
    cd scripts && python3 -m toolkit_deploy deploy demo
    cd scripts && python3 -m toolkit_deploy deploy production
    cd scripts && python3 -m toolkit_deploy rollback demo 2026.08.07.3
    cd scripts && python3 -m toolkit_deploy bootstrap --commit dec1b6e --version 2026.08.07.2
"""
from __future__ import annotations

import argparse
import os
import sys
from datetime import date
from pathlib import Path

from toolkit_deploy import deploy as deploy_mod
from toolkit_deploy import state as state_mod
from toolkit_deploy.config import ConfigError, load_config
from toolkit_deploy.cpanel import CPanelClient

REPO_ROOT = Path(__file__).resolve().parents[2]
THEME_ROOT = REPO_ROOT / "wp-content" / "themes" / "eduma-child"


def _config_path() -> Path:
    return Path(os.environ.get("TOOLKIT_DEPLOY_CONFIG", REPO_ROOT / "scripts" / "toolkit_deploy" / "config.toml"))


def _secrets_path() -> Path:
    return Path(os.environ.get("TOOLKIT_DEPLOY_SECRETS", REPO_ROOT / ".toolkit-deploy" / "secrets.env"))


def _state_path() -> Path:
    return Path(os.environ.get("TOOLKIT_DEPLOY_STATE", REPO_ROOT / ".toolkit-deploy" / "state.json"))


def _build_parser() -> argparse.ArgumentParser:
    parser = argparse.ArgumentParser(prog="toolkit_deploy")
    sub = parser.add_subparsers(dest="command", required=True)

    # env is deliberately NOT restricted with choices= here: argparse would
    # raise SystemExit itself on an invalid choice, bypassing our own
    # "unknown environment" check below and making main() uncallable as a
    # plain function that returns an exit code. A single validation path
    # (the config.environments membership check in main()) is simpler and
    # is what the CLI tests exercise.
    diff_parser = sub.add_parser("diff", help="show what would be deployed (dry run)")
    diff_parser.add_argument("env")

    deploy_parser = sub.add_parser("deploy", help="deploy to an environment")
    deploy_parser.add_argument("env")

    rollback_parser = sub.add_parser("rollback", help="restore a prior deployed snapshot")
    rollback_parser.add_argument("env")
    rollback_parser.add_argument("version")

    bootstrap_parser = sub.add_parser("bootstrap", help="seed state.json with a known-live commit/version")
    bootstrap_parser.add_argument("--commit", required=True)
    bootstrap_parser.add_argument("--version", required=True)

    return parser


def main(argv: list) -> int:
    parser = _build_parser()
    args = parser.parse_args(argv)

    if args.command == "bootstrap":
        from datetime import datetime, timezone

        state = state_mod.bootstrap_state(args.commit, args.version, datetime.now(timezone.utc).isoformat())
        state_mod.save_state(_state_path(), state)
        print(f"bootstrapped state.json: demo and production at {args.commit} / {args.version}")
        return 0

    try:
        config = load_config(_config_path(), _secrets_path())
    except ConfigError as exc:
        print(f"config error: {exc}", file=sys.stderr)
        return 1

    if args.command in ("diff", "deploy", "rollback") and args.env not in config.environments:
        print(f"unknown environment: {args.env}", file=sys.stderr)
        return 1

    if args.command == "diff":
        state = state_mod.load_state(_state_path())
        env_state = state.get(args.env)
        if env_state is None:
            print(f"no baseline recorded for '{args.env}' — run bootstrap first", file=sys.stderr)
            return 1
        result = deploy_mod.compute_diff(REPO_ROOT, env_state["commit"])
        if result.deleted:
            print("deleted (out of scope, would abort a real deploy):")
            for path in result.deleted:
                print(f"  - {path}")
        print("modified/added:")
        for path in result.modified_or_added:
            print(f"  - {path}")
        if not result.modified_or_added and not result.deleted:
            print("(nothing to deploy)")
        return 0

    client = CPanelClient(config.cpanel_host, config.cpanel_auth)

    if args.command == "deploy":
        try:
            outcome = deploy_mod.run_deploy(
                args.env, REPO_ROOT, THEME_ROOT, config, _state_path(), client, date.today()
            )
        except (deploy_mod.DeployAbort, state_mod.ProductionGateError) as exc:
            print(str(exc), file=sys.stderr)
            return 1
        print(f"deployed {args.env} to {outcome.version}: {len(outcome.uploaded)} files uploaded")
        print(
            f"reminder: commit the local functions.php release-marker bump "
            f"(now {outcome.version}) — it was written to disk but not "
            f"committed automatically"
        )
        return 0

    if args.command == "rollback":
        try:
            uploaded = deploy_mod.run_rollback(args.env, args.version, REPO_ROOT, config, client)
        except deploy_mod.DeployAbort as exc:
            print(str(exc), file=sys.stderr)
            return 1
        print(f"rolled back {args.env} to {args.version}: {len(uploaded)} files restored")
        return 0

    return 1


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts && python3 -m pytest toolkit_deploy/tests/test_cli.py -v
```

Expected: PASS (4 tests)

- [ ] **Step 5: Run the full suite**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts && python3 -m pytest toolkit_deploy/tests/ -v
```

Expected: PASS (all tests from Tasks 1–9)

- [ ] **Step 6: Commit**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress
git add scripts/toolkit_deploy/__main__.py scripts/toolkit_deploy/tests/test_cli.py
git commit -m "feat(deploy): add CLI entry point — diff/deploy/rollback/bootstrap"
```

---

### Task 10: `config.toml` template, `.gitignore`, usage README, final bootstrap

**Files:**
- Create: `scripts/toolkit_deploy/config.toml`
- Create: `scripts/toolkit_deploy/README.md`
- Modify: `.gitignore`

**Interfaces:**
- Consumes: `config.load_config` (Task 2) — this task's config.toml must actually parse successfully with that loader.
- Produces: nothing consumed by other tasks — this is the terminal documentation/config task.

- [ ] **Step 1: Write `config.toml`**

Create `scripts/toolkit_deploy/config.toml`:

```toml
[cpanel]
host = "https://wp46.host-ww.net:2083"

[environments.demo]
remote_base_dir = "demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child"
base_url = "https://demo.toolkitafrica.ac.ke"

[[environments.demo.routes]]
path = "/"

[[environments.demo.routes]]
path = "/our-ventures/"

[[environments.demo.routes]]
path = "/our-ventures/toolkit-courses-apply-today/"

[[environments.demo.routes]]
path = "/speak-up/"
expect_status = 200

[environments.production]
remote_base_dir = "public_html/wp-content/themes/eduma-child"
base_url = "https://toolkitafrica.ac.ke"

[[environments.production.routes]]
path = "/"

[[environments.production.routes]]
path = "/our-ventures/"

[[environments.production.routes]]
path = "/our-ventures/toolkit-courses-apply-today/"

[[environments.production.routes]]
path = "/speak-up/"
expect_status = 200
```

- [ ] **Step 2: Verify it loads with the real loader**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress
python3 -c "
import sys
sys.path.insert(0, 'scripts')
from pathlib import Path
from toolkit_deploy.config import load_config

secrets = Path('/tmp/toolkit_deploy_test_secrets.env')
secrets.write_text('CPANEL_AUTH=placeholder:placeholder\n')
config = load_config(Path('scripts/toolkit_deploy/config.toml'), secrets)
print('environments:', list(config.environments))
print('demo routes:', [r.path for r in config.environments['demo'].routes])
secrets.unlink()
"
```

Expected output:
```
environments: ['demo', 'production']
demo routes: ['/', '/our-ventures/', '/our-ventures/toolkit-courses-apply-today/', '/speak-up/']
```

- [ ] **Step 3: Update `.gitignore`**

Read the current `.gitignore` to find the existing `/rollbacks/` and `/reports/` entries, then add `.toolkit-deploy/` immediately after them:

```bash
grep -n "rollbacks\|reports" /home/t316/Desktop/Projects_father/toolkit/wordpress/.gitignore
```

Add this line right after those entries (use the Edit tool, anchoring on the exact surrounding lines found above):

```
/.toolkit-deploy/
```

- [ ] **Step 4: Write `scripts/toolkit_deploy/README.md`**

```markdown
# toolkit_deploy

A Python CLI that replaces the manual cPanel deploy process for this
repo's WordPress child theme. Full design rationale:
`docs/superpowers/specs/2026-08-07-deployment-pipeline-design.md`.

## Setup (one-time)

1. Create `.toolkit-deploy/secrets.env` at the repo root (gitignored):
   ```
   CPANEL_AUTH=bfyigiln:<password>
   ```
2. If `.toolkit-deploy/state.json` does not exist yet, seed it:
   ```bash
   cd scripts && python3 -m toolkit_deploy bootstrap --commit dec1b6e --version 2026.08.07.2
   ```

## Usage

Run all commands from the `scripts/` directory:

```bash
python3 -m toolkit_deploy diff demo          # dry run, no network calls
python3 -m toolkit_deploy deploy demo        # backup, bump release, upload, verify
python3 -m toolkit_deploy deploy production  # blocked unless demo is verified at HEAD
python3 -m toolkit_deploy rollback demo 2026.08.07.3
```

## What this does NOT do

- Does not delete remote files. A diff containing deletions aborts before
  any network call — remove files manually.
- Does not auto-remove files a rollback should undo the introduction of —
  it prints them for manual removal (see `NEW_FILES.txt` in the relevant
  `rollbacks/<env>-pre-<version>/` snapshot).
- Does not address cache/state bugs triggered by a wp-admin action (e.g. a
  settings toggle) rather than a file deploy — see
  `guide/07-DEPLOYMENT-SAFETY.md` for that convention.

## Running the tests

```bash
cd scripts && python3 -m pytest toolkit_deploy/tests/ -v
```
```

- [ ] **Step 5: Run the full test suite one final time**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress/scripts && python3 -m pytest toolkit_deploy/tests/ -v
```

Expected: PASS (every test from Tasks 1–9, no regressions)

- [ ] **Step 6: Commit**

```bash
cd /home/t316/Desktop/Projects_father/toolkit/wordpress
git add scripts/toolkit_deploy/config.toml scripts/toolkit_deploy/README.md .gitignore
git commit -m "feat(deploy): add config.toml template, README, and .gitignore entry"
```

---

## After this plan

Per the spec's Rollout section (not part of this plan — requires real
credentials and a real network, which this plan's tests deliberately
avoid):

1. Run `python3 -m toolkit_deploy bootstrap --commit dec1b6e --version 2026.08.07.2` for real against the real `.toolkit-deploy/state.json`.
2. Use `python3 -m toolkit_deploy diff demo` against a real pending change to sanity-check the file list before trusting it with an upload.
3. Exercise a real `deploy demo` and a real `rollback demo <version>` before ever running `deploy production`.
4. Manually confirm the `_is_not_found_error` heuristic in `cpanel.py` against a real "new file" `get_file_content` response — the flagged assumption in Task 4 — and extend it if the actual error text differs.
