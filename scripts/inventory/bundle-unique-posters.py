#!/usr/bin/env python3
"""Build a private poster ZIP from an explicitly reviewed selection.

Usage: python3 scripts/inventory/bundle-unique-posters.py REVIEW.json OUTPUT.zip
Requires Pillow. Selection belongs in the private reports directory, not Git.
Visual similarity is a review aid; this utility never drops a poster by a
perceptual-hash threshold. It verifies the chosen files and records provenance.
"""

import argparse
import csv
import hashlib
import io
import json
from pathlib import Path, PurePosixPath
import re
import zipfile

from PIL import Image


def sha256(data):
    return hashlib.sha256(data).hexdigest()


def csv_bytes(rows, fields):
    stream = io.StringIO(newline="")
    writer = csv.DictWriter(stream, fieldnames=fields, extrasaction="ignore")
    writer.writeheader()
    writer.writerows(rows)
    return stream.getvalue().encode("utf-8-sig")


def safe_name(value):
    slug = re.sub(r"[^a-zA-Z0-9-]+", "-", value).strip("-")
    if not slug:
        raise ValueError("Empty filename after sanitising")
    return slug[:100]


def build(review_path, output):
    review = json.loads(review_path.read_text())
    if output.exists():
        raise FileExistsError(f"Refusing to replace existing archive: {output}")
    output.parent.mkdir(parents=True, exist_ok=True)
    records = review["posters"]
    index, binaries, pixels, names, ids = [], set(), set(), set(), set()
    archive_ids = {}
    temporary = output.with_suffix(".zip.part")
    try:
        with zipfile.ZipFile(temporary, "x", allowZip64=True) as archive:
            for number, row in enumerate(records, 1):
                source_id = row["source_id"]
                if source_id in ids:
                    raise ValueError(f"Repeated source ID: {source_id}")
                ids.add(source_id)
                # XCF exports carry separate original and rendered hashes.
                source = Path(row["source"])
                if sha256(source.read_bytes()) != row["source_sha256"]:
                    raise ValueError(f"Source changed since review: {source}")
                payload = Path(row["payload"])
                data = payload.read_bytes()
                digest = sha256(data)
                if digest != row["payload_sha256"]:
                    raise ValueError(f"Poster changed since review: {payload}")
                with Image.open(io.BytesIO(data)) as im:
                    im.load()
                    size = im.size
                    rgba = im.convert("RGBA")
                    pixel_hash = sha256(str(size).encode() + rgba.tobytes())
                if digest in binaries or pixel_hash in pixels:
                    raise ValueError(f"Repeated poster bytes or decoded pixels: {source_id}")
                binaries.add(digest)
                pixels.add(pixel_hash)
                poster_id = f"P{number:03d}"
                archive_ids[source_id] = poster_id
                ext = payload.suffix.lower()
                if ext not in {".png", ".jpg", ".jpeg", ".webp"}:
                    raise ValueError(f"Unsupported viewable poster: {payload}")
                member = str(PurePosixPath("Toolkit-Posters", row["category"],
                             f"{poster_id}-{safe_name(row['title'])}{ext}"))
                if ".." in PurePosixPath(member).parts or member in names:
                    raise ValueError(f"Unsafe or repeated ZIP path: {member}")
                names.add(member)
                # PNG/JPEG/WebP are already compressed; store without recompressing.
                archive.writestr(member, data, compress_type=zipfile.ZIP_STORED)
                index.append({
                    "poster_id": poster_id, "zip_path": member,
                    "title": row["title"], "source_id": source_id,
                    "source_collection": row["collection"],
                    "original_relative_path": row["relative"],
                    "representation": row["representation"],
                    "source_sha256": row["source_sha256"],
                    "poster_sha256": digest, "width": size[0], "height": size[1],
                    "notes": row.get("notes", ""),
                })
            duplicates = []
            for row in review["duplicates"]:
                if row["kept_source_id"] not in archive_ids:
                    raise ValueError(f"Duplicate points to an omitted poster: {row}")
                duplicates.append({**row, "kept_poster_id": archive_ids[row["kept_source_id"]]})
            archive.writestr("Toolkit-Posters/INDEX.csv", csv_bytes(index, list(index[0])),
                             compress_type=zipfile.ZIP_DEFLATED)
            archive.writestr("Toolkit-Posters/DUPLICATES.csv", csv_bytes(duplicates, [
                "source_collection", "original_relative_path", "kept_poster_id", "reason"]),
                compress_type=zipfile.ZIP_DEFLATED)
            archive.writestr("Toolkit-Posters/README.txt", review["readme"],
                             compress_type=zipfile.ZIP_DEFLATED)
        with zipfile.ZipFile(temporary) as archive:
            if archive.testzip() is not None:
                raise ValueError("ZIP integrity check failed")
            if len(archive.namelist()) != len(set(archive.namelist())):
                raise ValueError("Repeated ZIP member names")
            for row in index:
                if sha256(archive.read(row["zip_path"])) != row["poster_sha256"]:
                    raise ValueError(f"ZIP payload mismatch: {row['poster_id']}")
        # Recheck all inventoried originals, including omitted copies and assets.
        for row in review["source_review"]:
            if sha256(Path(row["source"]).read_bytes()) != row["sha256"]:
                raise ValueError(f"Inventoried original changed: {row['source']}")
        temporary.rename(output)
    except BaseException:
        temporary.unlink(missing_ok=True)
        raise
    output.with_suffix(".index.csv").write_bytes(csv_bytes(index, list(index[0])))
    audit_rows = [{k: v for k, v in r.items() if k != "source"}
                  for r in review["source_review"]]
    output.with_suffix(".source-review.csv").write_bytes(csv_bytes(audit_rows, [
        "collection", "relative", "sha256", "bytes", "decision", "kept_source_id", "reason"]))
    result = {"archive": str(output.resolve()), "posters": len(index),
              "duplicate_sources_omitted": len(duplicates),
              "sources_reviewed": len(audit_rows), "bytes": output.stat().st_size,
              "sha256": sha256(output.read_bytes()),
              "verification": "ZIP CRC, unique names/bytes/pixels, payload hashes, original hashes passed"}
    output.with_suffix(".verification.json").write_text(json.dumps(result, indent=2) + "\n")
    print(json.dumps(result, indent=2))


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("review", type=Path)
    parser.add_argument("output", type=Path)
    args = parser.parse_args()
    build(args.review, args.output)
