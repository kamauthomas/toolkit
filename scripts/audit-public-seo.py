#!/usr/bin/env python3
"""Audit public sitemap URLs for essential rendered SEO signals.

Reports are written outside Git by default because operational evidence under
``reports/`` is intentionally ignored in this repository.
"""

from __future__ import annotations

import argparse
import concurrent.futures
import html
import json
import re
import subprocess
import urllib.parse
import xml.etree.ElementTree as ET
from collections import Counter
from dataclasses import asdict, dataclass
from pathlib import Path


USER_AGENT = "ToolkitSEOAudit/1.0 (+https://toolkitafrica.ac.ke/)"


@dataclass
class Result:
    url: str
    status: int
    final_url: str
    title: str
    description: str
    canonical: str
    robots: str
    h1_count: int
    json_ld_count: int
    og_title: str
    og_description: str
    og_image: str
    issues: list[str]


def fetch(url: str, timeout: int = 15, attempts: int = 2) -> tuple[int, str, str, dict[str, str]]:
    marker = "\n__TOOLKIT_CURL_RESULT__"
    command = [
        "curl", "-sS", "-L", "--compressed", "--max-time", str(timeout),
        "--retry", str(max(0, attempts - 1)), "--retry-delay", "1",
        "-A", USER_AGENT, "-w", marker + "%{http_code}\t%{url_effective}", url,
    ]
    completed = subprocess.run(command, check=False, capture_output=True)
    output = completed.stdout.decode("utf-8", errors="replace")
    if marker not in output:
        detail = completed.stderr.decode("utf-8", errors="replace").strip()
        raise RuntimeError(detail or f"curl exited {completed.returncode}")
    body, result = output.rsplit(marker, 1)
    status, final_url = result.split("\t", 1)
    return int(status), final_url.strip(), body, {}


def sitemap_urls(index_url: str) -> list[str]:
    _, _, body, _ = fetch(index_url)
    root = ET.fromstring(body)
    if root.tag.endswith("sitemapindex"):
        locations = [node.text.strip() for node in root.findall("./{*}sitemap/{*}loc") if node.text]
        urls: list[str] = []
        for sitemap in locations:
            _, _, child, _ = fetch(sitemap)
            child_root = ET.fromstring(child)
            urls.extend(node.text.strip() for node in child_root.findall("./{*}url/{*}loc") if node.text)
        return urls
    return [node.text.strip() for node in root.findall("./{*}url/{*}loc") if node.text]


def first(pattern: str, body: str) -> str:
    match = re.search(pattern, body, re.I | re.S)
    return html.unescape(re.sub(r"\s+", " ", match.group(1)).strip()) if match else ""


def audit(url: str) -> Result:
    try:
        status, final_url, body, headers = fetch(url)
    except Exception as exc:  # surfaced as an auditable result
        return Result(url, 0, "", "", "", "", "", 0, 0, "", "", "", [f"fetch_error:{exc}"])

    title = first(r"<title[^>]*>(.*?)</title>", body)
    description = first(r'<meta[^>]+name=["\']description["\'][^>]+content=["\'](.*?)["\']', body)
    canonical = first(r'<link[^>]+rel=["\']canonical["\'][^>]+href=["\'](.*?)["\']', body)
    robots = first(r'<meta[^>]+name=["\']robots["\'][^>]+content=["\'](.*?)["\']', body)
    og_title = first(r'<meta[^>]+property=["\']og:title["\'][^>]+content=["\'](.*?)["\']', body)
    og_description = first(r'<meta[^>]+property=["\']og:description["\'][^>]+content=["\'](.*?)["\']', body)
    og_image = first(r'<meta[^>]+property=["\']og:image["\'][^>]+content=["\'](.*?)["\']', body)
    h1_count = len(re.findall(r"<h1(?:\s|>)", body, re.I))
    json_ld_count = len(re.findall(r'<script[^>]+type=["\']application/ld\+json["\']', body, re.I))

    issues: list[str] = []
    if status != 200:
        issues.append(f"http_{status}")
    if final_url.rstrip("/") != url.rstrip("/"):
        issues.append("redirected")
    if not title:
        issues.append("missing_title")
    elif len(title) < 25 or len(title) > 65:
        issues.append(f"title_length_{len(title)}")
    if not description:
        issues.append("missing_description")
    elif len(description) < 100 or len(description) > 165:
        issues.append(f"description_length_{len(description)}")
    if canonical.rstrip("/") != url.rstrip("/"):
        issues.append("canonical_mismatch" if canonical else "missing_canonical")
    if "noindex" in robots.lower() or "noindex" in headers.get("X-Robots-Tag", "").lower():
        issues.append("noindex")
    if h1_count != 1:
        issues.append(f"h1_count_{h1_count}")
    if not json_ld_count:
        issues.append("missing_json_ld")
    if not og_title:
        issues.append("missing_og_title")
    if not og_description:
        issues.append("missing_og_description")
    if not og_image:
        issues.append("missing_og_image")

    return Result(url, status, final_url, title, description, canonical, robots,
                  h1_count, json_ld_count, og_title, og_description, og_image, issues)


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("base_url", nargs="?", default="https://toolkitafrica.ac.ke")
    parser.add_argument("--sitemap-url", help="Audit one sitemap instead of the site's sitemap index")
    parser.add_argument("--output", type=Path, default=Path("reports/seo-public-audit.json"))
    parser.add_argument("--workers", type=int, default=8)
    args = parser.parse_args()
    sitemap = args.sitemap_url or urllib.parse.urljoin(args.base_url.rstrip("/") + "/", "sitemap_index.xml")
    urls = sitemap_urls(sitemap)
    with concurrent.futures.ThreadPoolExecutor(max_workers=max(1, args.workers)) as pool:
        results = list(pool.map(audit, urls))
    issue_counts = Counter(issue.split(":", 1)[0].split("_length_", 1)[0] for result in results for issue in result.issues)
    payload = {
        "base_url": args.base_url,
        "sitemap": sitemap,
        "url_count": len(urls),
        "clean_count": sum(not result.issues for result in results),
        "issue_counts": dict(sorted(issue_counts.items())),
        "results": [asdict(result) for result in results],
    }
    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text(json.dumps(payload, indent=2, ensure_ascii=False) + "\n")
    print(json.dumps({key: payload[key] for key in ("url_count", "clean_count", "issue_counts")}, indent=2))
    return 1 if any(result.issues for result in results) else 0


if __name__ == "__main__":
    raise SystemExit(main())
