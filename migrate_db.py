"""
DB Migration Script — exports local SQLite data for production import.
Run locally:  python3 migrate_db.py export
Run on production via cPanel Python App execute: python3 migrate_db.py import

Usage:
  python3 migrate_db.py export    # creates migration_data.sql
  python3 migrate_db.py import    # imports data into production DB (requires ENV=production)
"""
import sqlite3
import json
import os
import sys
from pathlib import Path

BASE = Path(__file__).resolve().parent
LOCAL_DB = BASE / "instance" / "toolkit_reports.sqlite3"


def export():
    if not LOCAL_DB.exists():
        print(f"Local DB not found at {LOCAL_DB}")
        sys.exit(1)
    db = sqlite3.connect(str(LOCAL_DB))
    db.row_factory = sqlite3.Row
    lines = []

    # users
    users = db.execute("SELECT * FROM users").fetchall()
    for u in users:
        cols = ", ".join(u.keys())
        placeholders = ", ".join("?" for _ in u.keys())
        values = [u[k] for k in u.keys()]
        lines.append({
            "table": "users",
            "cols": list(u.keys()),
            "values": values,
        })

    # reports
    reports = db.execute("SELECT * FROM reports").fetchall()
    for r in reports:
        lines.append({
            "table": "reports",
            "cols": list(r.keys()),
            "values": [r[k] for k in r.keys()],
        })

    # report_access
    access = db.execute("SELECT * FROM report_access").fetchall()
    for a in access:
        lines.append({
            "table": "report_access",
            "cols": list(a.keys()),
            "values": [a[k] for k in a.keys()],
        })

    # report_edits
    edits = db.execute("SELECT * FROM report_edits").fetchall()
    for e in edits:
        lines.append({
            "table": "report_edits",
            "cols": list(e.keys()),
            "values": [e[k] for k in e.keys()],
        })

    # report_comments
    comments = db.execute("SELECT * FROM report_comments").fetchall()
    for c in comments:
        lines.append({
            "table": "report_comments",
            "cols": list(c.keys()),
            "values": [c[k] for k in c.keys()],
        })

    db.close()

    out = BASE / "migration_data.json"
    with open(out, "w") as f:
        json.dump(lines, f, indent=2, default=str)
    print(f"Exported {len(lines)} rows to {out}")
    print(f"  Users: {len(users)}")
    print(f"  Reports: {len(reports)}")
    print(f"  Access: {len(access)}")
    print(f"  Edits: {len(edits)}")
    print(f"  Comments: {len(comments)}")


def import_data():
    src = BASE / "migration_data.json"
    if not src.exists():
        print(f"Migration file not found at {src}")
        sys.exit(1)

    db_path = BASE / "instance" / "toolkit_reports.sqlite3"
    if not db_path.exists():
        print(f"Production DB not found at {db_path}")
        print("Has the app been started at least once?")
        sys.exit(1)

    conn = sqlite3.connect(str(db_path))
    conn.row_factory = sqlite3.Row
    data = json.loads(src.read_text())
    imported = 0

    for row in data:
        table = row["table"]
        cols = row["cols"]
        values = row["values"]
        placeholders = ", ".join("?" for _ in cols)
        col_str = ", ".join(cols)
        try:
            conn.execute(f"INSERT OR IGNORE INTO {table} ({col_str}) VALUES ({placeholders})", values)
            imported += 1
        except Exception as e:
            print(f"  Error inserting into {table}: {e}")
    conn.commit()
    conn.close()
    print(f"Imported {imported}/{len(data)} rows into {db_path}")


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(__doc__)
        sys.exit(1)
    cmd = sys.argv[1]
    if cmd == "export":
        export()
    elif cmd == "import":
        import_data()
    else:
        print(f"Unknown command: {cmd}")
        sys.exit(1)
