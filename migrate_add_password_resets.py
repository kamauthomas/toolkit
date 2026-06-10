"""Run this against the live database to add the password_resets table without restarting."""
import sqlite3
from pathlib import Path

DB_PATH = Path(__file__).resolve().parent / "instance" / "toolkit_reports.sqlite3"
db = sqlite3.connect(DB_PATH)
db.execute("""
    CREATE TABLE IF NOT EXISTS password_resets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        token TEXT NOT NULL UNIQUE,
        expires_at TEXT NOT NULL,
        used INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL,
        FOREIGN KEY(user_id) REFERENCES users(id)
    )
""")
db.commit()
db.close()
print("password_resets table created (or already exists).")
