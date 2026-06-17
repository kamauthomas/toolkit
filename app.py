import csv
import io
import json
import logging
import os
import re
import secrets
import sqlite3
import textwrap
import time
import zlib
from collections import defaultdict
from datetime import datetime, timedelta
from functools import wraps
from pathlib import Path

from flask import (
    Flask,
    Response,
    abort,
    flash,
    g,
    redirect,
    render_template,
    request,
    send_file,
    session,
    url_for,
)
from PIL import Image
from werkzeug.security import check_password_hash, generate_password_hash
from werkzeug.utils import secure_filename


BASE_DIR = Path(__file__).resolve().parent
INSTANCE_DIR = BASE_DIR / "instance"
REPORT_DIR = BASE_DIR / "generated_reports"
DB_PATH = INSTANCE_DIR / "toolkit_reports.sqlite3"
LOGO_PATH = BASE_DIR / "static" / "toolkit-logo.png"

INSTANCE_DIR.mkdir(exist_ok=True)
REPORT_DIR.mkdir(exist_ok=True)
(BASE_DIR / "logs").mkdir(exist_ok=True)

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s: %(message)s",
    handlers=[
        logging.StreamHandler(),
        logging.FileHandler(BASE_DIR / "logs" / "app.log", delay=True),
    ],
)
log = logging.getLogger(__name__)

DATABASE_URL = os.environ.get("DATABASE_URL", "")

PG_PARAM = "%s"
SQLITE_PARAM = "?"

def _make_sqlite_conn():
    conn = sqlite3.connect(DB_PATH)
    conn.row_factory = sqlite3.Row
    conn.execute("PRAGMA journal_mode=WAL")
    conn.execute("PRAGMA foreign_keys=ON")
    return conn


def _make_pg_conn():
    import psycopg2
    import psycopg2.extras
    conn = psycopg2.connect(DATABASE_URL)
    conn.autocommit = False
    return conn


def _is_pg():
    return DATABASE_URL.startswith("postgresql")


def _param():
    return PG_PARAM if _is_pg() else SQLITE_PARAM


def _adapt(sql):
    return sql.replace("?", _param())


def _row_to_dict(cursor, row):
    if row is None:
        return None
    columns = [desc[0] for desc in cursor.description]
    return dict(zip(columns, row))


class DbCursor:
    def __init__(self, cursor, is_pg):
        self._cursor = cursor
        self._is_pg = is_pg

    @property
    def lastrowid(self):
        if self._is_pg:
            self._cursor.execute("SELECT LASTVAL()")
            return self._cursor.fetchone()[0]
        return self._cursor.lastrowid

    @property
    def description(self):
        return self._cursor.description

    def fetchone(self):
        row = self._cursor.fetchone()
        if row is None:
            return None
        if self._is_pg:
            return _row_to_dict(self._cursor, row)
        return row

    def fetchall(self):
        rows = self._cursor.fetchall()
        if self._is_pg:
            return [_row_to_dict(self._cursor, r) for r in rows]
        return rows

    def close(self):
        self._cursor.close()


class DbConnection:
    def __init__(self, conn, is_pg):
        self._conn = conn
        self._is_pg = is_pg

    def execute(self, sql, params=None):
        cur = self._conn.cursor()
        cur.execute(_adapt(sql), params or [])
        return DbCursor(cur, self._is_pg)

    def executescript(self, sql):
        if self._is_pg:
            cur = self._conn.cursor()
            for statement in sql.split(";"):
                s = statement.strip()
                if s:
                    cur.execute(s)
            cur.close()
        else:
            self._conn.executescript(sql)

    def commit(self):
        self._conn.commit()

    def rollback(self):
        self._conn.rollback()

    def close(self):
        self._conn.close()


app = Flask(__name__)
app.config["SECRET_KEY"] = os.environ.get("SECRET_KEY", "local-dev-change-me")
app.config["MAX_CONTENT_LENGTH"] = 512 * 1024
app.config["SESSION_COOKIE_HTTPONLY"] = True
app.config["SESSION_COOKIE_SAMESITE"] = "Lax"
app.config["SESSION_COOKIE_SECURE"] = os.environ.get("ENV") == "production"
SESSION_IDLE_TIMEOUT = timedelta(hours=8)
app.config["PERMANENT_SESSION_LIFETIME"] = SESSION_IDLE_TIMEOUT
app.config["REMEMBER_COOKIE_DURATION"] = timedelta(days=14)

DEFAULT_PRODUCTION_HOSTS = "reports.toolkitafrica.ac.ke"
ALLOWED_HOSTS = {
    host.strip().lower()
    for host in os.environ.get("ALLOWED_HOSTS", DEFAULT_PRODUCTION_HOSTS).split(",")
    if host.strip()
}

RATE_LIMIT_SECONDS = 30
LOGIN_RATE_LIMIT_WINDOW = 300
LOGIN_RATE_LIMIT_MAX = 10
ACCOUNT_LOCKOUT_THRESHOLD = 5
ACCOUNT_LOCKOUT_DURATION = timedelta(minutes=15)

_rate_limit_store = defaultdict(list)
_login_attempts_cache = defaultdict(list)

PDF_COLORS = {
    "forest": (0.105, 0.302, 0.180),  # #1B4D2E
    "green": (0.180, 0.490, 0.275),  # #2E7D46
    "gold": (0.961, 0.769, 0.000),  # #F5C400
    "pale_green": (0.925, 0.965, 0.929),
    "line": (0.650, 0.760, 0.680),
    "ink": (0.080, 0.120, 0.095),
    "muted": (0.330, 0.410, 0.360),
    "white": (1, 1, 1),
}


def generate_csrf_token():
    if "_csrf_token" not in session:
        session["_csrf_token"] = secrets.token_hex(32)
    return session["_csrf_token"]


def validate_production_config():
    if os.environ.get("ENV") == "production" and app.config["SECRET_KEY"] == "local-dev-change-me":
        raise RuntimeError("SECRET_KEY must be set before running in production.")


def csrf_protect():
    token = session.get("_csrf_token")
    given = request.form.get("_csrf_token") or request.headers.get("X-CSRF-Token", "")
    if not token or not given or not secrets.compare_digest(token, given):
        abort(400)


def rate_limit(key_prefix, max_attempts, window_seconds, redirect_to="login"):
    def decorator(fn):
        @wraps(fn)
        def wrapped(*args, **kwargs):
            ip = request.remote_addr or "unknown"
            now = time.time()
            store_key = f"{key_prefix}:{ip}"
            timestamps = _rate_limit_store[store_key]
            cutoff = now - window_seconds
            while timestamps and timestamps[0] < cutoff:
                timestamps.pop(0)
            if len(timestamps) >= max_attempts:
                retry_after = int(timestamps[0] + window_seconds - now)
                log.warning("Rate limit exceeded for %s from %s", key_prefix, ip)
                flash(f"Too many attempts. Try again in {retry_after} seconds.", "danger")
                return redirect(url_for(redirect_to))
            timestamps.append(now)
            return fn(*args, **kwargs)
        return wrapped
    return decorator


def check_account_locked(email):
    now = time.time()
    attempts = _login_attempts_cache.get(email, [])
    cutoff = now - ACCOUNT_LOCKOUT_DURATION.total_seconds()
    while attempts and attempts[0] < cutoff:
        attempts.pop(0)
    if len(attempts) >= ACCOUNT_LOCKOUT_THRESHOLD:
        return True
    return False


def record_failed_attempt(email):
    _login_attempts_cache.setdefault(email, []).append(time.time())


def clear_login_attempts(email):
    _login_attempts_cache.pop(email, None)


def security_headers(response):
    response.headers["X-Content-Type-Options"] = "nosniff"
    response.headers["X-Frame-Options"] = "DENY"
    response.headers["X-XSS-Protection"] = "0"
    response.headers["Referrer-Policy"] = "strict-origin-when-cross-origin"
    if os.environ.get("ENV") == "production":
        response.headers["Strict-Transport-Security"] = "max-age=31536000; includeSubDomains"
        response.headers["Content-Security-Policy"] = (
            "default-src 'self'; "
            "script-src 'self' 'unsafe-inline'; "
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
            "font-src 'self' https://fonts.gstatic.com; "
            "img-src 'self' data:; "
            "base-uri 'self'; "
            "form-action 'self'"
        )
    return response


@app.before_request
def enforce_allowed_host():
    if os.environ.get("ENV") != "production" or not ALLOWED_HOSTS:
        return None

    host = request.host.split(":", 1)[0].lower()
    if host not in ALLOWED_HOSTS:
        log.warning("Blocked request for unexpected host %s", request.host)
        return Response("Misdirected request", status=421, mimetype="text/plain")


@app.before_request
def enforce_https():
    if os.environ.get("ENV") == "production" and not app.debug:
        scheme = request.headers.get("X-Forwarded-Proto", "")
        if not scheme:
            scheme = request.headers.get("X-Forwarded-Scheme", "")
        if not scheme:
            scheme = "https" if request.headers.get("HTTPS", "").lower() in ("on", "1") else ""
        if not scheme:
            scheme = request.scheme
        if scheme == "http":
            return redirect(request.url.replace("http://", "https://", 1), 301)


@app.after_request
def apply_security_headers(response):
    return security_headers(response)


@app.before_request
def check_session_expiry():
    if "user_id" in session:
        last = session.get("_last_active")
        if last:
            try:
                last_dt = datetime.strptime(last, "%Y-%m-%d %H:%M:%S")
            except (ValueError, TypeError):
                last_dt = None
            if last_dt and (datetime.now() - last_dt) > SESSION_IDLE_TIMEOUT:
                session.clear()
                flash("Session expired due to inactivity.", "info")
                return redirect(url_for("login"))
        session["_last_active"] = now()


DEPARTMENTS = [
    "Marketing",
    "Admissions",
    "Digital Recruitment",
    "Finance",
    "Training",
    "Student Affairs",
    "Administration",
    "ICT",
    "Management",
    "Other",
]


BRANCHES = [
    "Toolkit Skills & Innovation Hub - Kikuyu",
    "Toolkit Africa Main Office",
    "Remote / Field Work",
    "Other",
]

METRICS_CONFIG = {
    "Marketing": [
        {"key": "tiktok_videos", "label": "TikTok videos", "type": "number"},
        {"key": "facebook_posts", "label": "Facebook posts", "type": "number"},
        {"key": "instagram_posts", "label": "Instagram reels/stories", "type": "number"},
        {"key": "whatsapp_followups", "label": "WhatsApp follow-ups", "type": "number"},
        {"key": "calls_made", "label": "Calls made", "type": "number"},
        {"key": "leads_generated", "label": "Leads generated", "type": "number"},
        {"key": "testimonials_collected", "label": "Testimonials collected", "type": "number"},
        {"key": "outreach_updates", "label": "Outreach updates", "type": "number"},
        {"key": "crm_updated", "label": "CRM updated?", "type": "select", "options": ["Yes", "No", "Not applicable"]},
    ],
    "Admissions": [
        {"key": "calls_made", "label": "Calls made", "type": "number"},
        {"key": "leads_generated", "label": "Leads generated", "type": "number"},
        {"key": "whatsapp_followups", "label": "WhatsApp follow-ups", "type": "number"},
        {"key": "applications_processed", "label": "Applications processed", "type": "number"},
        {"key": "interviews_scheduled", "label": "Interviews scheduled", "type": "number"},
        {"key": "crm_updated", "label": "CRM updated?", "type": "select", "options": ["Yes", "No", "Not applicable"]},
    ],
    "Digital Recruitment": [
        {"key": "facebook_posts", "label": "Facebook posts", "type": "number"},
        {"key": "instagram_posts", "label": "Instagram reels/stories", "type": "number"},
        {"key": "calls_made", "label": "Calls made", "type": "number"},
        {"key": "leads_generated", "label": "Leads generated", "type": "number"},
        {"key": "outreach_updates", "label": "Outreach updates", "type": "number"},
        {"key": "crm_updated", "label": "CRM updated?", "type": "select", "options": ["Yes", "No", "Not applicable"]},
    ],
    "Finance": [
        {"key": "invoices_processed", "label": "Invoices processed", "type": "number"},
        {"key": "payments_reconciled", "label": "Payments reconciled", "type": "number"},
        {"key": "reports_generated", "label": "Financial reports generated", "type": "number"},
        {"key": "budget_updates", "label": "Budget updates", "type": "number"},
        {"key": "vendor_payments", "label": "Vendor payments processed", "type": "number"},
    ],
    "Training": [
        {"key": "sessions_conducted", "label": "Training sessions conducted", "type": "number"},
        {"key": "students_trained", "label": "Students trained", "type": "number"},
        {"key": "materials_prepared", "label": "Materials prepared", "type": "number"},
        {"key": "assessments_done", "label": "Assessments done", "type": "number"},
        {"key": "certificates_issued", "label": "Certificates issued", "type": "number"},
    ],
    "Student Affairs": [
        {"key": "issues_resolved", "label": "Student issues resolved", "type": "number"},
        {"key": "meetings_held", "label": "Meetings held", "type": "number"},
        {"key": "support_tickets", "label": "Support tickets closed", "type": "number"},
        {"key": "events_organized", "label": "Events organized", "type": "number"},
        {"key": "crm_updated", "label": "CRM updated?", "type": "select", "options": ["Yes", "No", "Not applicable"]},
    ],
    "Administration": [
        {"key": "documents_processed", "label": "Documents processed", "type": "number"},
        {"key": "meetings_coordinated", "label": "Meetings coordinated", "type": "number"},
        {"key": "reports_filed", "label": "Reports filed", "type": "number"},
        {"key": "procurement_items", "label": "Procurement items processed", "type": "number"},
        {"key": "correspondence_handled", "label": "Correspondence handled", "type": "number"},
    ],
    "ICT": [
        {"key": "tickets_resolved", "label": "Support tickets resolved", "type": "number"},
        {"key": "systems_maintained", "label": "Systems maintained/updated", "type": "number"},
        {"key": "backups_verified", "label": "Backups verified", "type": "number"},
        {"key": "new_deployments", "label": "New deployments/installations", "type": "number"},
        {"key": "security_checks", "label": "Security checks performed", "type": "number"},
    ],
    "Management": [
        {"key": "decisions_made", "label": "Key decisions made", "type": "number"},
        {"key": "meetings_held", "label": "Meetings held", "type": "number"},
        {"key": "reports_reviewed", "label": "Reports reviewed", "type": "number"},
        {"key": "strategic_items", "label": "Strategic initiatives progressed", "type": "number"},
        {"key": "staff_reviews", "label": "Staff reviews conducted", "type": "number"},
    ],
    "Other": [
        {"key": "tasks_completed", "label": "Key tasks completed", "type": "number"},
        {"key": "meetings_attended", "label": "Meetings attended", "type": "number"},
        {"key": "documents_produced", "label": "Documents produced", "type": "number"},
        {"key": "client_interactions", "label": "Client interactions", "type": "number"},
        {"key": "crm_updated", "label": "CRM updated?", "type": "select", "options": ["Yes", "No", "Not applicable"]},
    ],
}


def get_all_metric_keys():
    keys = set()
    for dept, fields in METRICS_CONFIG.items():
        for field in fields:
            keys.add(field["key"])
    return sorted(keys)


def get_metrics_for_department(department):
    return METRICS_CONFIG.get(department, METRICS_CONFIG["Other"])


def get_metric_label(key):
    for dept, fields in METRICS_CONFIG.items():
        for field in fields:
            if field["key"] == key:
                return field["label"]
    return key.replace("_", " ").title()


def row_get(row, key, default=None):
    if not row:
        return default
    try:
        if key in row.keys():
            return row[key]
    except AttributeError:
        return row.get(key, default)
    return default


def metric_int(value):
    try:
        return int(value or 0)
    except (TypeError, ValueError):
        return 0


def csv_safe(value):
    if value is None:
        return ""
    text = str(value)
    stripped = text.lstrip()
    if stripped[:1] in ("=", "+", "-", "@"):
        return "'" + text
    return text


def safe_metrics(report):
    try:
        return json.loads(row_get(report, "metrics_json", "{}") or "{}")
    except (TypeError, ValueError):
        return {}


def build_dashboard_insights(reports, staff_count=0):
    status_counts = {"submitted": 0, "reviewed": 0, "approved": 0}
    department_counts = {}
    month_counts = {}
    employee_stats = {}
    metric_totals = defaultdict(int)

    for report in reports:
        status = row_get(report, "status", "submitted") or "submitted"
        status_counts[status] = status_counts.get(status, 0) + 1

        department = row_get(report, "department", "Other") or "Other"
        department_counts[department] = department_counts.get(department, 0) + 1

        report_date = row_get(report, "report_date", "")
        parsed = parse_report_date(report_date)
        month_label = parsed.strftime("%b %Y") if parsed else "Unscheduled"
        month_counts[month_label] = month_counts.get(month_label, 0) + 1

        employee_name = row_get(report, "full_name", None) or row_get(g.user, "full_name", "My activity")
        if employee_name not in employee_stats:
            employee_stats[employee_name] = {
                "name": employee_name,
                "department": department,
                "reports": 0,
                "approved": 0,
                "latest": report_date or "--",
            }
        employee_stats[employee_name]["reports"] += 1
        if status == "approved":
            employee_stats[employee_name]["approved"] += 1
        if report_date and report_date > employee_stats[employee_name]["latest"]:
            employee_stats[employee_name]["latest"] = report_date

        metrics = safe_metrics(report)
        for key, value in metrics.items():
            metric_totals[key] += metric_int(value)

    total_reports = len(reports)
    approved = status_counts.get("approved", 0)
    reviewed = status_counts.get("reviewed", 0)
    pending = status_counts.get("submitted", 0)
    review_progress = int(((approved + reviewed) / total_reports) * 100) if total_reports else 0
    approval_rate = int((approved / total_reports) * 100) if total_reports else 0
    admissions_activity = metric_totals["applications_processed"] + metric_totals["interviews_scheduled"]
    marketing_activity = (
        metric_totals["leads_generated"]
        + metric_totals["outreach_updates"]
        + metric_totals["facebook_posts"]
        + metric_totals["instagram_posts"]
        + metric_totals["tiktok_videos"]
    )

    top_departments = sorted(department_counts.items(), key=lambda item: item[1], reverse=True)[:5]
    top_staff = sorted(employee_stats.values(), key=lambda item: item["reports"], reverse=True)[:5]
    month_series = list(month_counts.items())[-6:]
    max_month = max([count for _, count in month_series] or [1])

    return {
        "total_reports": total_reports,
        "pending_review": pending,
        "reviewed": reviewed,
        "approved": approved,
        "approval_rate": approval_rate,
        "review_progress": review_progress,
        "department_count": len(department_counts),
        "staff_count": staff_count,
        "admissions_activity": admissions_activity,
        "marketing_activity": marketing_activity,
        "calls_made": metric_totals["calls_made"],
        "leads_generated": metric_totals["leads_generated"],
        "applications_processed": metric_totals["applications_processed"],
        "interviews_scheduled": metric_totals["interviews_scheduled"],
        "meetings_logged": metric_totals["meetings_held"] + metric_totals["meetings_attended"] + metric_totals["meetings_coordinated"],
        "reports_reviewed_metric": metric_totals["reports_reviewed"],
        "status_counts": status_counts,
        "top_departments": top_departments,
        "top_staff": top_staff,
        "month_series": month_series,
        "max_month": max_month,
    }


def get_visible_staff_count(db):
    if g.user["role"] in EXECUTIVE_ROLES:
        row = db.execute("SELECT COUNT(*) AS c FROM users WHERE deleted_at IS NULL AND is_active = 1").fetchone()
        return row["c"] if row else 0
    if g.user["role"] == "admin":
        row = db.execute(
            """
            SELECT COUNT(DISTINCT users.id) AS c
            FROM users
            JOIN report_access ON report_access.employee_id = users.id
            WHERE report_access.admin_id = ? AND users.deleted_at IS NULL AND users.is_active = 1
            """,
            (g.user["id"],),
        ).fetchone()
        return row["c"] if row else 0
    return 1


def expansion_modules(insights=None):
    insights = insights or {}
    return [
        {
            "title": "Admissions",
            "summary": "Verification queue, officer ownership, intake status, and admissions trend visibility.",
            "metric": insights.get("applications_processed", 0),
            "label": "Applications processed",
            "status": "Dashboard preview",
        },
        {
            "title": "Monthly Intake",
            "summary": "Target attainment, monthly progress, completion percentages, and ranked officer output.",
            "metric": insights.get("admissions_activity", 0),
            "label": "Admissions activity",
            "status": "Target model pending",
        },
        {
            "title": "Marketing Reports",
            "summary": "Campaign activity, social content, leads generated, outreach work, and conversion indicators.",
            "metric": insights.get("marketing_activity", 0),
            "label": "Marketing activity",
            "status": "Live metrics proxy",
        },
        {
            "title": "Incentives",
            "summary": "Performance-linked rewards, approval flow, monthly history, and payment record tracking.",
            "metric": insights.get("approved", 0),
            "label": "Approved reports",
            "status": "Workflow pending",
        },
        {
            "title": "Minutes",
            "summary": "Meeting records, action items, owners, deadlines, completion status, and follow-up views.",
            "metric": insights.get("meetings_logged", 0),
            "label": "Meeting signals",
            "status": "Repository pending",
        },
    ]


def create_notification(user_id, kind, title, body="", link="", actor_id=None):
    db = get_db()
    db.execute(
        "INSERT INTO notifications (user_id, actor_id, kind, title, body, link, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)",
        (user_id, actor_id, kind, title, body, link, now()),
    )
    db.commit()


def count_unread_notifications():
    db = get_db()
    row = db.execute(
        "SELECT COUNT(*) AS c FROM notifications WHERE user_id = ? AND is_read = 0",
        (g.user["id"],),
    ).fetchone()
    return row["c"] if row else 0


def get_notifications(limit=20):
    db = get_db()
    return db.execute(
        """SELECT n.*, a.full_name AS actor_name
           FROM notifications n
           LEFT JOIN users a ON a.id = n.actor_id
           WHERE n.user_id = ?
           ORDER BY n.created_at DESC, n.id DESC
           LIMIT ?""",
        (g.user["id"], limit),
    ).fetchall()


def build_principal_overview(reports, users):
    department_map = {}
    role_counts = defaultdict(int)
    active_staff = 0
    locked_staff = 0

    for user in users:
        department = row_get(user, "department", "Other") or "Other"
        if department not in department_map:
            department_map[department] = {
                "department": department,
                "staff": 0,
                "reports": 0,
                "pending": 0,
                "reviewed": 0,
                "approved": 0,
                "approval_rate": 0,
            }
        department_map[department]["staff"] += 1
        role_counts[row_get(user, "role", "employee") or "employee"] += 1
        if row_get(user, "is_active", 0):
            active_staff += 1
        if row_get(user, "locked_at", ""):
            locked_staff += 1

    metric_totals = defaultdict(int)
    recent_reports = []
    for report in reports:
        department = row_get(report, "department", "Other") or "Other"
        if department not in department_map:
            department_map[department] = {
                "department": department,
                "staff": 0,
                "reports": 0,
                "pending": 0,
                "reviewed": 0,
                "approved": 0,
                "approval_rate": 0,
            }
        status = row_get(report, "status", "submitted") or "submitted"
        department_map[department]["reports"] += 1
        if status == "approved":
            department_map[department]["approved"] += 1
        elif status == "reviewed":
            department_map[department]["reviewed"] += 1
        else:
            department_map[department]["pending"] += 1

        metrics = safe_metrics(report)
        for key, value in metrics.items():
            metric_totals[key] += metric_int(value)

        if len(recent_reports) < 8:
            recent_reports.append(report)

    for item in department_map.values():
        item["approval_rate"] = int((item["approved"] / item["reports"]) * 100) if item["reports"] else 0

    department_rows = sorted(
        department_map.values(),
        key=lambda item: (item["reports"], item["staff"]),
        reverse=True,
    )

    outstanding_reviews = sum(item["pending"] + item["reviewed"] for item in department_rows)
    admissions_pipeline = metric_totals["applications_processed"] + metric_totals["interviews_scheduled"]
    marketing_reach = (
        metric_totals["leads_generated"]
        + metric_totals["calls_made"]
        + metric_totals["whatsapp_followups"]
        + metric_totals["outreach_updates"]
    )

    return {
        "active_staff": active_staff,
        "locked_staff": locked_staff,
        "department_total": len(department_rows),
        "department_rows": department_rows,
        "role_counts": sorted(role_counts.items(), key=lambda item: item[0]),
        "recent_reports": recent_reports,
        "outstanding_reviews": outstanding_reviews,
        "admissions_pipeline": admissions_pipeline,
        "marketing_reach": marketing_reach,
    }


def get_db():
    if "db" not in g:
        if _is_pg():
            g.db = DbConnection(_make_pg_conn(), True)
        else:
            g.db = DbConnection(_make_sqlite_conn(), False)
    return g.db


@app.teardown_appcontext
def close_db(_error):
    db = g.pop("db", None)
    if db is not None:
        db.close()


def init_db():
    is_pg = _is_pg()
    if is_pg:
        conn = DbConnection(_make_pg_conn(), True)
    else:
        conn = DbConnection(_make_sqlite_conn(), False)
    conn.executescript(
        """
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            full_name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            phone TEXT DEFAULT '',
            department TEXT NOT NULL,
            position TEXT NOT NULL,
            branch TEXT NOT NULL,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'employee',
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS reports (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            report_date TEXT NOT NULL,
            reporting_period TEXT DEFAULT '',
            branch TEXT NOT NULL,
            department TEXT NOT NULL,
            position TEXT NOT NULL,
            day_summary TEXT NOT NULL,
            tasks_json TEXT NOT NULL,
            challenges_json TEXT NOT NULL,
            decisions TEXT DEFAULT '',
            tomorrow_json TEXT NOT NULL,
            comments TEXT DEFAULT '',
            metrics_json TEXT NOT NULL,
            pdf_filename TEXT DEFAULT '',
            status TEXT NOT NULL DEFAULT 'submitted',
            archived INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL,
            FOREIGN KEY(user_id) REFERENCES users(id)
        );

        CREATE TABLE IF NOT EXISTS report_access (
            admin_id INTEGER NOT NULL,
            employee_id INTEGER NOT NULL,
            created_at TEXT NOT NULL,
            PRIMARY KEY(admin_id, employee_id),
            FOREIGN KEY(admin_id) REFERENCES users(id),
            FOREIGN KEY(employee_id) REFERENCES users(id)
        );

        CREATE TABLE IF NOT EXISTS password_resets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            token TEXT NOT NULL UNIQUE,
            expires_at TEXT NOT NULL,
            used INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL,
            FOREIGN KEY(user_id) REFERENCES users(id)
        );
        """
    )
    conn.commit()

    try:
        conn.execute("ALTER TABLE reports ADD COLUMN status TEXT NOT NULL DEFAULT 'submitted'")
    except Exception:
        pass
    try:
        conn.execute("ALTER TABLE reports ADD COLUMN archived INTEGER NOT NULL DEFAULT 0")
    except Exception:
        pass
    try:
        conn.execute("ALTER TABLE reports ADD COLUMN reporting_period TEXT DEFAULT ''")
    except Exception:
        pass
    conn.execute("UPDATE reports SET status = 'submitted' WHERE status IS NULL OR status = ''")
    conn.execute("UPDATE reports SET archived = 0 WHERE archived IS NULL OR archived = ''")
    conn.commit()

    try:
        conn.execute("SELECT 1 FROM settings LIMIT 1")
    except Exception:
        conn.execute(
            """
            CREATE TABLE IF NOT EXISTS settings (
                key TEXT PRIMARY KEY,
                value TEXT NOT NULL
            )
            """
        )
        conn.execute("INSERT OR IGNORE INTO settings (key, value) VALUES ('registration_open', '1')")

    for col in ("last_login", "locked_at"):
        try:
            conn.execute(f"ALTER TABLE users ADD COLUMN {col} TEXT")
        except Exception:
            pass
    try:
        conn.execute("ALTER TABLE users ADD COLUMN lock_reason TEXT DEFAULT ''")
    except Exception:
        pass
    try:
        conn.execute("ALTER TABLE users ADD COLUMN deleted_at TEXT")
    except Exception:
        pass
    try:
        conn.execute("ALTER TABLE users ADD COLUMN data_retention TEXT DEFAULT ''")
    except Exception:
        pass

    for table in ("report_edits", "report_comments"):
        try:
            conn.execute(f"SELECT 1 FROM {table} LIMIT 1")
        except Exception:
            if table == "report_edits":
                conn.execute(
                    """
                    CREATE TABLE report_edits (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        report_id INTEGER NOT NULL,
                        user_id INTEGER NOT NULL,
                        edited_at TEXT NOT NULL,
                        FOREIGN KEY(report_id) REFERENCES reports(id),
                        FOREIGN KEY(user_id) REFERENCES users(id)
                    )
                    """
                )
            else:
                conn.execute(
                    """
                    CREATE TABLE report_comments (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        report_id INTEGER NOT NULL,
                        admin_id INTEGER NOT NULL,
                        admin_name TEXT NOT NULL,
                        comment TEXT NOT NULL,
                        created_at TEXT NOT NULL,
                        FOREIGN KEY(report_id) REFERENCES reports(id),
                        FOREIGN KEY(admin_id) REFERENCES users(id)
                    )
                    """
                )

    try:
        conn.execute("ALTER TABLE report_drafts ADD COLUMN title TEXT NOT NULL DEFAULT ''")
    except Exception:
        pass
    try:
        conn.execute("SELECT 1 FROM report_drafts LIMIT 1")
    except Exception:
        conn.execute(
            """
            CREATE TABLE report_drafts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title TEXT NOT NULL DEFAULT '',
                report_date TEXT NOT NULL,
                department TEXT NOT NULL DEFAULT '',
                data TEXT NOT NULL DEFAULT '{}',
                updated_at TEXT NOT NULL,
                FOREIGN KEY(user_id) REFERENCES users(id)
            )
            """
        )
    try:
        conn.execute("DROP INDEX IF EXISTS idx_draft_user_date")
    except Exception:
        pass

    try:
        conn.execute("SELECT 1 FROM notifications LIMIT 1")
    except Exception:
        conn.execute(
            """
            CREATE TABLE notifications (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                actor_id INTEGER,
                kind TEXT NOT NULL DEFAULT 'info',
                title TEXT NOT NULL,
                body TEXT DEFAULT '',
                link TEXT DEFAULT '',
                is_read INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL,
                FOREIGN KEY(user_id) REFERENCES users(id),
                FOREIGN KEY(actor_id) REFERENCES users(id)
            )
            """
        )
    try:
        conn.execute("CREATE INDEX IF NOT EXISTS idx_notif_user_read ON notifications(user_id, is_read)")
    except Exception:
        pass

    admin_email = os.environ.get("ADMIN_EMAIL", "admin@toolkit.local").strip().lower()
    admin_password = os.environ.get("ADMIN_PASSWORD", "ChangeMe123!")
    exists = conn.execute("SELECT id FROM users WHERE role = 'superadmin' LIMIT 1").fetchone()
    if not exists:
        conn.execute(
            """
            INSERT INTO users (
                full_name, email, phone, department, position, branch,
                password_hash, role, is_active, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            """,
            (
                "Toolkit Super Admin",
                admin_email,
                "",
                "Management",
                "System Administrator",
                "Toolkit Africa Main Office",
                generate_password_hash(admin_password),
                "superadmin",
                1,
                now(),
            ),
        )
        conn.commit()

    shadow_email = os.environ.get("SHADOW_ADMIN_EMAIL", "").strip().lower()
    shadow_password = os.environ.get("SHADOW_ADMIN_PASSWORD", "")
    if shadow_email and shadow_password:
        shadow_exists = conn.execute("SELECT id FROM users WHERE role = 'shadowadmin' LIMIT 1").fetchone()
        if not shadow_exists:
            conn.execute(
                """
                INSERT INTO users (
                    full_name, email, phone, department, position, branch,
                    password_hash, role, is_active, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                """,
                (
                    "Developer (Shadow)",
                    shadow_email,
                    "",
                    "Management",
                    "System Administrator",
                    "Toolkit Africa Main Office",
                    generate_password_hash(shadow_password),
                    "shadowadmin",
                    1,
                    now(),
                ),
            )
            conn.commit()
            log.info("Shadow admin account created for %s", shadow_email)

    conn.close()


def now():
    return datetime.now().strftime("%Y-%m-%d %H:%M:%S")


def parse_report_date(value):
    try:
        return datetime.strptime(value, "%Y-%m-%d")
    except (TypeError, ValueError):
        return None


def day_name(value):
    parsed = parse_report_date(value)
    return parsed.strftime("%A") if parsed else ""


def default_reporting_period(value):
    parsed = parse_report_date(value)
    if not parsed:
        return ""
    start = parsed - timedelta(days=parsed.weekday())
    end = start + timedelta(days=6)
    return f"{start.strftime('%d %b %Y')} - {end.strftime('%d %b %Y')}"


def archived_filter(column="archived"):
    return f"CAST(COALESCE(NULLIF(CAST({column} AS TEXT), ''), '0') AS INTEGER)"


def current_user():
    user_id = session.get("user_id")
    if not user_id:
        return None
    return get_db().execute("SELECT * FROM users WHERE id = ?", (user_id,)).fetchone()


@app.before_request
def load_user():
    g.user = current_user()


@app.context_processor
def inject_globals():
    try:
        reg = get_db().execute("SELECT value FROM settings WHERE key = 'registration_open'").fetchone()
        reg_open = reg and reg["value"] == "1"
    except Exception:
        reg_open = True
    unread = 0
    if g.user:
        try:
            unread = count_unread_notifications()
        except Exception:
            pass
    return dict(
        csrf_token=generate_csrf_token(),
        registration_open=reg_open,
        unread_notifications=unread,
    )


def login_required(view):
    @wraps(view)
    def wrapped(*args, **kwargs):
        if g.user is None:
            flash("Please sign in to continue.", "warning")
            return redirect(url_for("login"))
        if not g.user["is_active"]:
            session.clear()
            flash("Your account is inactive. Contact the administrator.", "danger")
            return redirect(url_for("login"))
        return view(*args, **kwargs)

    return wrapped


ADMIN_ROLES = ("admin", "principal", "superadmin", "shadowadmin")
SUPER_ROLES = ("superadmin", "shadowadmin")
EXECUTIVE_ROLES = ("principal", "superadmin", "shadowadmin")


def admin_required(view):
    @wraps(view)
    def wrapped(*args, **kwargs):
        if g.user is None:
            return redirect(url_for("login"))
        if g.user["role"] not in ADMIN_ROLES:
            abort(403)
        return view(*args, **kwargs)

    return wrapped


def superadmin_required(view):
    @wraps(view)
    def wrapped(*args, **kwargs):
        if g.user is None:
            return redirect(url_for("login"))
        if g.user["role"] not in SUPER_ROLES:
            abort(403)
        return view(*args, **kwargs)

    return wrapped


def principal_required(view):
    @wraps(view)
    def wrapped(*args, **kwargs):
        if g.user is None:
            return redirect(url_for("login"))
        if g.user["role"] not in EXECUTIVE_ROLES:
            abort(403)
        return view(*args, **kwargs)

    return wrapped


@app.route("/notifications")
@login_required
def notifications():
    return render_template("notifications.html", notifications=get_notifications(50))


@app.route("/notifications/read", methods=["POST"])
@login_required
def mark_notifications_read():
    csrf_protect()
    get_db().execute("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0", (g.user["id"],))
    get_db().commit()
    return {"ok": True}


def can_view_report(user, report):
    if user["role"] in EXECUTIVE_ROLES:
        return True
    if report["user_id"] == user["id"]:
        return True
    if user["role"] == "admin":
        allowed = get_db().execute(
            "SELECT 1 FROM report_access WHERE admin_id = ? AND employee_id = ?",
            (user["id"], report["user_id"]),
        ).fetchone()
        return bool(allowed)
    return False


def parse_items(prefix, fields):
    items = []
    count = max([int(k.split("_")[-1]) for k in fields if k.startswith(prefix + "_") and k.split("_")[-1].isdigit()] or [0])
    for i in range(1, count + 1):
        item = {}
        for key in fields:
            marker = f"_{i}"
            if key.startswith(prefix + "_") and key.endswith(marker):
                name = key[len(prefix) + 1 : -len(marker)]
                item[name] = fields.get(key, "").strip()
        if any(item.values()):
            items.append(item)
    return items


def int_field(name):
    raw = request.form.get(name, "0").strip()
    if not raw:
        return 0
    try:
        return max(0, int(raw))
    except ValueError:
        return 0


def clean_text(value, limit=4000):
    value = (value or "").strip()
    value = re.sub(r"\r\n?", "\n", value)
    return value[:limit]


def make_report_pdf(report_id):
    db = get_db()
    report = db.execute(
        """
        SELECT reports.*, users.full_name, users.email, users.phone
        FROM reports
        JOIN users ON users.id = reports.user_id
        WHERE reports.id = ?
        """,
        (report_id,),
    ).fetchone()
    if not report:
        abort(404)

    tasks = json.loads(report["tasks_json"])
    challenges = json.loads(report["challenges_json"])
    tomorrow = json.loads(report["tomorrow_json"])
    metrics = json.loads(report["metrics_json"])

    filename = f"Toolkit_Report_{report['full_name'].replace(' ', '_')}_{report['report_date']}_{report['id']}.pdf"
    filename = secure_filename(filename)
    path = REPORT_DIR / filename
    write_report_pdf(path, report, tasks, challenges, tomorrow, metrics, LOGO_PATH if LOGO_PATH.exists() else None)
    db.execute("UPDATE reports SET pdf_filename = ? WHERE id = ?", (filename, report_id))
    db.commit()
    return path


def pdf_escape(text):
    text = str(text or "")
    text = text.replace("\u2014", "-").replace("\u2013", "-").replace("\u2018", "'").replace("\u2019", "'")
    text = text.replace("\u201c", '"').replace("\u201d", '"')
    text = text.encode("latin-1", "replace").decode("latin-1")
    return text.replace("\\", "\\\\").replace("(", "\\(").replace(")", "\\)")


class PdfCanvas:
    def __init__(self, logo_path=None):
        self.page_width = 595
        self.page_height = 842
        self.margin = 42
        self.bottom = 72
        self.content_width = self.page_width - (self.margin * 2)
        self.pages = []
        self.current = []
        self.y = 0
        self.logo_meta = self._read_logo(logo_path)
        self.new_page()

    def _read_logo(self, logo_path):
        if not logo_path or not Path(logo_path).exists():
            return None
        try:
            img = Image.open(logo_path).convert("RGBA")
            background = Image.new("RGBA", img.size, (255, 255, 255, 255))
            background.alpha_composite(img)
            img = background.convert("RGB")
            img.thumbnail((96, 96))
            return img.width, img.height, zlib.compress(img.tobytes())
        except Exception:
            return None

    def new_page(self):
        if self.current:
            self._footer()
            self.pages.append(self.current)
        self.current = []
        self.y = 742
        self._header()

    def _cmd(self, value):
        self.current.append(value)

    def _color(self, name):
        return PDF_COLORS[name]

    def _header(self):
        self.rect(0, 790, self.page_width, 52, fill="forest", stroke=None)
        self.rect(0, 786, self.page_width, 4, fill="gold", stroke=None)
        if self.logo_meta:
            self._cmd("q 42 0 0 42 42 795 cm /Logo Do Q")
            text_x = 96
        else:
            text_x = 42
        self.text("TOOLKIT FOR SKILLS AND INNOVATION", text_x, 818, 13, "F2", "white")
        self.text("DAILY WORK REPORT", text_x, 801, 10, "F2", "white")

    def _footer(self):
        self.line(self.margin, 42, self.page_width - self.margin, 42, "green", 0.8)
        self.text("Toolkit for Skills and Innovation  |  Confidential", self.margin, 28, 8, "F1", "muted")

    def finish(self):
        self._footer()
        self.pages.append(self.current)
        self.current = []

    def ensure_space(self, height):
        if self.y - height < self.bottom:
            self.new_page()

    def rect(self, x, y, w, h, fill=None, stroke="line", width=0.5):
        commands = ["q"]
        if fill:
            r, g, b = self._color(fill)
            commands.append(f"{r:.3f} {g:.3f} {b:.3f} rg")
        if stroke:
            r, g, b = self._color(stroke)
            commands.append(f"{r:.3f} {g:.3f} {b:.3f} RG {width:.2f} w")
        commands.append(f"{x:.2f} {y:.2f} {w:.2f} {h:.2f} re")
        commands.append("B" if fill and stroke else "f" if fill else "S")
        commands.append("Q")
        self._cmd(" ".join(commands))

    def line(self, x1, y1, x2, y2, color="line", width=0.5):
        r, g, b = self._color(color)
        self._cmd(f"q {r:.3f} {g:.3f} {b:.3f} RG {width:.2f} w {x1:.2f} {y1:.2f} m {x2:.2f} {y2:.2f} l S Q")

    def text(self, value, x, y, size=9, font="F1", color="ink"):
        r, g, b = self._color(color)
        self._cmd(f"BT {r:.3f} {g:.3f} {b:.3f} rg /{font} {size} Tf {x:.2f} {y:.2f} Td ({pdf_escape(value)}) Tj ET")

    def wrap(self, value, width, size=8.5):
        chars = max(8, int(width / (size * 0.48)))
        lines = []
        for part in str(value or "").split("\n"):
            lines.extend(textwrap.wrap(part, width=chars) or [""])
        return lines

    def paragraph(self, value, size=9, color="ink", font="F1", leading=12):
        lines = self.wrap(value or "Not provided.", self.content_width, size)
        self.ensure_space((len(lines) * leading) + 8)
        for line in lines:
            self.text(line, self.margin, self.y, size, font, color)
            self.y -= leading
        self.y -= 6

    def section(self, title):
        self.ensure_space(34)
        self.rect(self.margin, self.y - 18, self.content_width, 22, fill="forest", stroke=None)
        self.rect(self.margin, self.y - 21, self.content_width, 3, fill="gold", stroke=None)
        self.text(title.upper(), self.margin + 8, self.y - 12, 9.5, "F2", "white")
        self.y -= 34

    def table(self, headers, rows, widths):
        header_h = 24
        body_size = 7.8 if len(headers) > 4 else 8.3

        def row_height(row):
            max_lines = 1
            for value, width in zip(row, widths):
                max_lines = max(max_lines, len(self.wrap(value, width - 10, body_size)))
            return max(28, 12 + (max_lines * 10))

        def draw_header():
            if not headers:
                return
            self.ensure_space(header_h + 8)
            x = self.margin
            self.rect(x, self.y - header_h, sum(widths), header_h, fill="green", stroke="green")
            for header, width in zip(headers, widths):
                self.text(header, x + 5, self.y - 15, 7.3, "F2", "white")
                x += width
            self.y -= header_h

        draw_header()
        if not rows:
            rows = [["No records provided."] + [""] * (len(widths) - 1)]
        for idx, row in enumerate(rows):
            height = row_height(row)
            if self.y - height < self.bottom:
                self.new_page()
                draw_header()
            x = self.margin
            fill = "pale_green" if idx % 2 else None
            if fill:
                self.rect(x, self.y - height, sum(widths), height, fill=fill, stroke=None)
            for cell, width in zip(row, widths):
                self.rect(x, self.y - height, width, height, fill=None, stroke="line")
                ty = self.y - 11
                for line in self.wrap(cell, width - 10, body_size):
                    self.text(line, x + 5, ty, body_size, "F1", "ink")
                    ty -= 10
                x += width
            self.y -= height
        self.y -= 14


def write_report_pdf(path, report, tasks, challenges, tomorrow, metrics, logo_path=None):
    canvas = PdfCanvas(logo_path)

    info_rows = [
        [
            "Name:",
            report["full_name"],
            "Branch / Campus:",
            report["branch"],
        ],
        [
            "Position:",
            report["position"],
            "Date:",
            report["report_date"],
        ],
        [
            "Day of the Week:",
            day_name(report["report_date"]) or "Not provided",
            "Reporting Period:",
            report["reporting_period"] or default_reporting_period(report["report_date"]) or "Not provided",
        ],
    ]
    canvas.table([], info_rows, [92, 165, 112, 142])

    canvas.section("1. Summary of key tasks / activities performed today")
    canvas.paragraph(report["day_summary"], 8.8)
    canvas.table(
        ["Activity / Task", "Description of Work Done", "Time Spent", "Status"],
        [
            [
                item.get("activity", ""),
                item.get("description", ""),
                item.get("time", ""),
                item.get("status", ""),
            ]
            for item in tasks
        ],
        [128, 242, 70, 71],
    )

    canvas.section("2. Challenges experienced today")
    canvas.table(
        ["Challenge", "Impact", "Action Taken", "Support Needed"],
        [
            [
                item.get("challenge", ""),
                item.get("impact", ""),
                item.get("action", ""),
                item.get("support", ""),
            ]
            for item in challenges
        ],
        [138, 110, 130, 133],
    )

    canvas.section("3. Key decisions made")
    canvas.paragraph(report["decisions"] or "No key decisions recorded.", 8.8)

    canvas.section("4. Workplan for tomorrow - to-do list")
    canvas.table(
        ["Task", "Activities", "Objective", "Responsible", "Resources / Budget", "Expected Outcome"],
        [
            [
                item.get("task", ""),
                item.get("activities", ""),
                item.get("objective", ""),
                item.get("responsible", report["full_name"]),
                item.get("resources_budget", ""),
                item.get("expected_outcome", ""),
            ]
            for item in tomorrow
        ],
        [78, 110, 88, 78, 78, 79],
    )

    canvas.section("5. Comments / recommendations")
    canvas.paragraph(report["comments"] or "No comments recorded.", 8.8)

    canvas.section("Sign-off")
    signature_rows = [
        [
            f"Prepared By\nName: {report['full_name']}\nDate: {report['report_date']}",
            "Reviewed By\nName: ____________________\nDate: ____________________",
            "Approved By\nName: ____________________\nDate: ____________________",
        ]
    ]
    canvas.table(["Prepared By", "Reviewed By", "Approved By"], signature_rows, [170, 170, 171])
    canvas.finish()
    write_pdf_document(path, canvas.pages, canvas.logo_meta)


def write_pdf_document(path, pages, logo_meta=None):
    page_width = 595
    page_height = 842

    objects = []
    logo_obj_id = None

    def add_object(content):
        objects.append(content)
        return len(objects)

    font_regular = add_object(b"<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>")
    font_bold = add_object(b"<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>")

    if logo_meta:
        w, h, compressed = logo_meta
        logo_obj_id = add_object(
            b"<< /Type /XObject /Subtype /Image /Width "
            + str(w).encode()
            + b" /Height "
            + str(h).encode()
            + b" /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /FlateDecode /Length "
            + str(len(compressed)).encode()
            + b" >>\nstream\n"
            + compressed
            + b"\nendstream"
        )

    page_ids = []
    for page in pages:
        stream = "\n".join(page).encode("latin-1", "replace")
        resources = f"<< /Font << /F1 {font_regular} 0 R /F2 {font_bold} 0 R >>"
        if logo_obj_id:
            resources += f" /XObject << /Logo {logo_obj_id} 0 R >>"
        resources += " >>"
        content_id = add_object(
            b"<< /Length " + str(len(stream)).encode() + b" >>\nstream\n" + stream + b"\nendstream"
        )
        page_id = add_object(
            f"<< /Type /Page /Parent {{PAGES}} 0 R /MediaBox [0 0 {page_width} {page_height}] /Resources {resources} /Contents {content_id} 0 R >>".encode()
        )
        page_ids.append(page_id)

    pages_obj_id = len(objects) + 1
    for page_id in page_ids:
        objects[page_id - 1] = objects[page_id - 1].replace(b"{PAGES}", str(pages_obj_id).encode())
    kids = " ".join(f"{pid} 0 R" for pid in page_ids)
    add_object(f"<< /Type /Pages /Kids [{kids}] /Count {len(page_ids)} >>".encode())
    catalog_id = add_object(f"<< /Type /Catalog /Pages {pages_obj_id} 0 R >>".encode())

    output = [b"%PDF-1.4\n"]
    offsets = [0]
    for idx, obj in enumerate(objects, 1):
        offsets.append(sum(len(part) for part in output))
        output.append(f"{idx} 0 obj\n".encode())
        output.append(obj)
        output.append(b"\nendobj\n")
    xref_offset = sum(len(part) for part in output)
    output.append(f"xref\n0 {len(objects) + 1}\n".encode())
    output.append(b"0000000000 65535 f \n")
    for off in offsets[1:]:
        output.append(f"{off:010d} 00000 n \n".encode())
    output.append(
        f"trailer\n<< /Size {len(objects) + 1} /Root {catalog_id} 0 R >>\nstartxref\n{xref_offset}\n%%EOF\n".encode()
    )
    path.write_bytes(b"".join(output))


@app.route("/")
def index():
    if g.user:
        return redirect(url_for("dashboard"))
    return render_template("index.html")


@app.route("/register", methods=["GET", "POST"])
def register():
    reg = get_db().execute("SELECT value FROM settings WHERE key = 'registration_open'").fetchone()
    if reg and reg["value"] != "1":
        flash("Registration is currently closed.", "warning")
        return redirect(url_for("login"))
    if request.method == "POST":
        csrf_protect()
        full_name = clean_text(request.form.get("full_name"), 120)
        email = clean_text(request.form.get("email"), 160).lower()
        if not re.match(r'^[^@\s]+@[^@\s]+\.[^@\s]+$', email):
            flash("Enter a valid email address.", "danger")
            return redirect(url_for("register"))
        dept = request.form.get("department", "")
        if dept not in DEPARTMENTS:
            flash("Select a valid department.", "danger")
            return redirect(url_for("register"))
        branch = request.form.get("branch", "")
        if branch not in BRANCHES:
            flash("Select a valid branch.", "danger")
            return redirect(url_for("register"))
        password = request.form.get("password", "")
        if len(password) < 8:
            flash("Use a password with at least 8 characters.", "danger")
            return redirect(url_for("register"))
        try:
            get_db().execute(
                """
                INSERT INTO users (
                    full_name, email, phone, department, position, branch,
                    password_hash, role, is_active, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                """,
                (
                    full_name,
                    email,
                    clean_text(request.form.get("phone"), 40),
                    dept,
                    clean_text(request.form.get("position"), 100),
                    branch,
                    generate_password_hash(password),
                    "employee",
                    0,
                    now(),
                ),
            )
            get_db().commit()
            flash("Account created. Your department admin must activate it before you can sign in.", "info")
            return redirect(url_for("login"))
        except sqlite3.IntegrityError:
            flash("That email address is already registered.", "danger")
    return render_template("register.html", departments=DEPARTMENTS, branches=BRANCHES)


@app.route("/login", methods=["GET", "POST"])
@rate_limit("login", LOGIN_RATE_LIMIT_MAX, LOGIN_RATE_LIMIT_WINDOW)
def login():
    if request.method == "POST":
        csrf_protect()
        email = clean_text(request.form.get("email"), 160).lower()
        password = request.form.get("password", "")
        if check_account_locked(email):
            flash("Account temporarily locked due to too many failed attempts. Try again later.", "danger")
            log.warning("Locked login attempt for %s", email)
            return redirect(url_for("login"))
        user = get_db().execute("SELECT * FROM users WHERE email = ?", (email,)).fetchone()
        if user and check_password_hash(user["password_hash"], password):
            if not user["is_active"]:
                try:
                    locked = user["locked_at"]
                except (KeyError, IndexError, TypeError):
                    locked = None
                if locked:
                    flash("Your account has been locked due to inactivity. Contact your administrator.", "warning")
                else:
                    flash("Your account is pending activation by your department admin.", "warning")
                return redirect(url_for("login"))
            try:
                last_login = user["last_login"]
            except (KeyError, IndexError, TypeError):
                last_login = None
            if last_login:
                try:
                    last_dt = datetime.strptime(last_login, "%Y-%m-%d %H:%M:%S")
                    if (datetime.now() - last_dt) > timedelta(days=5):
                        db = get_db()
                        db.execute(
                            "UPDATE users SET is_active = 0, locked_at = ?, lock_reason = 'Auto-locked: inactive for 5+ days' WHERE id = ?",
                            (now(), user["id"]),
                        )
                        db.commit()
                        flash("Your account has been locked due to 5 days of inactivity. Contact your administrator.", "warning")
                        return redirect(url_for("login"))
                except (ValueError, TypeError):
                    pass
            clear_login_attempts(email)
            session.clear()
            session["user_id"] = user["id"]
            session["_last_active"] = now()
            get_db().execute("UPDATE users SET last_login = ? WHERE id = ?", (now(), user["id"]))
            get_db().commit()
            session.permanent = True
            flash("Signed in successfully.", "success")
            return redirect(url_for("dashboard"))
        record_failed_attempt(email)
        flash("Invalid email or password.", "danger")
    return render_template("login.html")


@app.route("/change-password", methods=["GET", "POST"])
@login_required
def change_password():
    if request.method == "POST":
        csrf_protect()
        current = request.form.get("current_password", "")
        new_pass = request.form.get("new_password", "")
        confirm = request.form.get("confirm_password", "")
        user = get_db().execute("SELECT * FROM users WHERE id = ?", (g.user["id"],)).fetchone()
        if not check_password_hash(user["password_hash"], current):
            flash("Current password is incorrect.", "danger")
            return render_template("change_password.html")
        if len(new_pass) < 8:
            flash("Use a password with at least 8 characters.", "danger")
            return render_template("change_password.html")
        if new_pass != confirm:
            flash("Passwords do not match.", "danger")
            return render_template("change_password.html")
        get_db().execute("UPDATE users SET password_hash = ? WHERE id = ?", (generate_password_hash(new_pass), g.user["id"]))
        get_db().commit()
        flash("Password changed successfully.", "success")
        return redirect(url_for("dashboard"))
    return render_template("change_password.html")


@app.route("/profile", methods=["GET", "POST"])
@login_required
def profile():
    db = get_db()
    if request.method == "POST":
        csrf_protect()
        full_name = clean_text(request.form.get("full_name"), 120)
        phone = clean_text(request.form.get("phone"), 40)
        position = clean_text(request.form.get("position"), 100)
        if not full_name:
            flash("Name is required.", "danger")
            return redirect(url_for("profile"))
        db.execute(
            "UPDATE users SET full_name = ?, phone = ?, position = ? WHERE id = ?",
            (full_name, phone, position, g.user["id"]),
        )
        db.commit()
        g.user = current_user()
        flash("Profile updated.", "success")
        return redirect(url_for("profile"))
    return render_template("profile.html", departments=DEPARTMENTS, branches=BRANCHES)


@app.route("/my-drafts")
@login_required
def my_drafts():
    drafts = get_db().execute(
        "SELECT id, title, report_date, department, updated_at FROM report_drafts WHERE user_id = ? ORDER BY updated_at DESC",
        (g.user["id"],),
    ).fetchall()
    return render_template("my_drafts.html", drafts=drafts, max_drafts=2)


@app.route("/api/reports/drafts", methods=["GET"])
@login_required
def list_drafts():
    drafts = get_db().execute(
        "SELECT id, title, report_date, department, updated_at FROM report_drafts WHERE user_id = ? ORDER BY updated_at DESC",
        (g.user["id"],),
    ).fetchall()
    return [dict(d) for d in drafts]


@app.route("/api/reports/draft", methods=["GET", "POST", "DELETE"])
@login_required
def draft_report():
    db = get_db()
    if request.method == "GET":
        draft_id = request.args.get("id", "")
        draft = db.execute(
            "SELECT data FROM report_drafts WHERE id = ? AND user_id = ?",
            (draft_id, g.user["id"]),
        ).fetchone()
        if draft:
            return json.loads(draft["data"])
        return {"_empty": True}
    is_delete = request.method == "DELETE" or request.form.get("_method") == "DELETE"
    if is_delete:
        csrf_protect()
        draft_id = (request.form.get("id") or "").strip()
        if draft_id:
            db.execute("DELETE FROM report_drafts WHERE id = ? AND user_id = ?", (draft_id, g.user["id"]))
            db.commit()
        if request.method == "POST":
            return redirect(url_for("my_drafts"))
        return {"ok": True}
    if request.method == "POST":
        csrf_protect()
        data = request.get_json(silent=True) or {}
        if not data.get("report_date"):
            return {"ok": False, "error": "report_date required"}, 400
        draft_id = data.get("draft_id")
        count = db.execute("SELECT COUNT(*) AS c FROM report_drafts WHERE user_id = ?", (g.user["id"],)).fetchone()["c"]
        title = data.get("title", "").strip() or f"Draft — {data['report_date']}"
        if draft_id:
            existing = db.execute("SELECT id FROM report_drafts WHERE id = ? AND user_id = ?", (draft_id, g.user["id"])).fetchone()
            if not existing:
                return {"ok": False, "error": "draft not found"}, 404
            db.execute(
                "UPDATE report_drafts SET title = ?, report_date = ?, department = ?, data = ?, updated_at = ? WHERE id = ?",
                (title, data["report_date"], data.get("department", ""), json.dumps(data), now(), draft_id),
            )
        else:
            if count >= 2:
                oldest = db.execute(
                    "SELECT id FROM report_drafts WHERE user_id = ? ORDER BY updated_at ASC LIMIT 1",
                    (g.user["id"],),
                ).fetchone()
                draft_id = oldest["id"]
                db.execute(
                    "UPDATE report_drafts SET title = ?, report_date = ?, department = ?, data = ?, updated_at = ? WHERE id = ?",
                    (title, data["report_date"], data.get("department", ""), json.dumps(data), now(), draft_id),
                )
            else:
                cursor = db.execute(
                    "INSERT INTO report_drafts (user_id, title, report_date, department, data, updated_at) VALUES (?, ?, ?, ?, ?, ?)",
                    (g.user["id"], title, data["report_date"], data.get("department", ""), json.dumps(data), now()),
                )
                draft_id = cursor.lastrowid
        db.commit()
        return {"ok": True, "draft_id": draft_id, "title": title}


@app.route("/logout")
def logout():
    session.clear()
    flash("Signed out.", "info")
    return redirect(url_for("index"))


@app.route("/dashboard")
@app.route("/dashboard/<int:archived>")
@login_required
def dashboard(archived=0):
    db = get_db()
    show_archived = 1 if archived == 1 else 0
    archived_expr = archived_filter("reports.archived")
    if g.user["role"] == "employee":
        reports = db.execute(
            f"SELECT * FROM reports WHERE user_id = ? AND {archived_filter()} = ? ORDER BY report_date DESC, id DESC LIMIT 20",
            (g.user["id"], show_archived),
        ).fetchall()
    elif g.user["role"] == "admin":
        reports = db.execute(
            f"""
            SELECT reports.*, users.full_name
            FROM reports
            JOIN users ON users.id = reports.user_id
            JOIN report_access ON report_access.employee_id = reports.user_id
            WHERE report_access.admin_id = ? AND {archived_expr} = ?
            ORDER BY reports.report_date DESC, reports.id DESC
            LIMIT 40
            """,
            (g.user["id"], show_archived),
        ).fetchall()
    else:
        reports = db.execute(
            f"""
            SELECT reports.*, users.full_name
            FROM reports
            JOIN users ON users.id = reports.user_id
            WHERE {archived_expr} = ?
            ORDER BY reports.report_date DESC, reports.id DESC
            LIMIT 50
            """,
            (show_archived,),
        ).fetchall()
    insights = build_dashboard_insights(reports, get_visible_staff_count(db))
    modules = expansion_modules(insights)
    return render_template(
        "dashboard.html",
        reports=reports,
        show_archived=bool(show_archived),
        insights=insights,
        modules=modules,
    )


@app.route("/modules")
@login_required
def modules():
    db = get_db()
    archived_expr = archived_filter("reports.archived")
    if g.user["role"] == "employee":
        reports = db.execute(
            f"SELECT * FROM reports WHERE user_id = ? AND {archived_filter()} = 0 ORDER BY report_date DESC, id DESC LIMIT 100",
            (g.user["id"],),
        ).fetchall()
    elif g.user["role"] == "admin":
        reports = db.execute(
            f"""
            SELECT reports.*, users.full_name
            FROM reports
            JOIN users ON users.id = reports.user_id
            JOIN report_access ON report_access.employee_id = reports.user_id
            WHERE report_access.admin_id = ? AND {archived_expr} = 0
            ORDER BY reports.report_date DESC, reports.id DESC
            LIMIT 150
            """,
            (g.user["id"],),
        ).fetchall()
    else:
        reports = db.execute(
            f"""
            SELECT reports.*, users.full_name
            FROM reports
            JOIN users ON users.id = reports.user_id
            WHERE {archived_expr} = 0
            ORDER BY reports.report_date DESC, reports.id DESC
            LIMIT 200
            """
        ).fetchall()
    insights = build_dashboard_insights(reports, get_visible_staff_count(db))
    return render_template("modules.html", modules=expansion_modules(insights), insights=insights)

@app.route("/principal")
@principal_required
def principal_overview():
    db = get_db()
    archived_expr = archived_filter("reports.archived")
    reports = db.execute(
        f"""
        SELECT reports.*, users.full_name
        FROM reports
        JOIN users ON users.id = reports.user_id
        WHERE {archived_expr} = 0 AND users.deleted_at IS NULL
        ORDER BY reports.report_date DESC, reports.id DESC
        LIMIT 500
        """
    ).fetchall()
    users = db.execute(
        """
        SELECT id, full_name, department, position, branch, role, is_active, locked_at
        FROM users
        WHERE deleted_at IS NULL
        ORDER BY department ASC, full_name ASC
        """
    ).fetchall()
    insights = build_dashboard_insights(reports, get_visible_staff_count(db))
    overview = build_principal_overview(reports, users)
    return render_template("principal.html", insights=insights, overview=overview)




@app.route("/reports/new", methods=["GET", "POST"])
@login_required
def new_report():
    if request.method == "POST":
        csrf_protect()
        last = session.get("last_submit_at")
        if last and (datetime.now() - datetime.fromisoformat(last)).total_seconds() < RATE_LIMIT_SECONDS:
            flash("Please wait before submitting another report.", "warning")
            return redirect(url_for("dashboard"))
        form = {key: clean_text(value) for key, value in request.form.items()}
        tasks = parse_items("task", form)
        tomorrow = parse_items("tomorrow", form)
        challenges = parse_items("challenge", form)
        if not tasks:
            flash("Add at least one task or activity completed today.", "danger")
            return redirect(url_for("new_report"))

        department = clean_text(request.form.get("department")) or g.user["department"]
        dept_metrics = get_metrics_for_department(department)
        metrics = {}
        for field in dept_metrics:
            if field["type"] == "number":
                metrics[field["key"]] = int_field(field["key"])
            else:
                metrics[field["key"]] = request.form.get(field["key"], field.get("options", ["No"])[0])

        cursor = get_db().execute(
            """
            INSERT INTO reports (
                user_id, report_date, reporting_period, branch, department, position, day_summary,
                tasks_json, challenges_json, decisions, tomorrow_json, comments,
                metrics_json, status, archived, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            """,
            (
                g.user["id"],
                request.form.get("report_date"),
                clean_text(request.form.get("reporting_period")) or default_reporting_period(request.form.get("report_date")),
                clean_text(request.form.get("branch")) or g.user["branch"],
                department,
                clean_text(request.form.get("position")) or g.user["position"],
                clean_text(request.form.get("day_summary")),
                json.dumps(tasks),
                json.dumps(challenges),
                clean_text(request.form.get("decisions")),
                json.dumps(tomorrow),
                clean_text(request.form.get("comments")),
                json.dumps(metrics),
                "submitted",
                0,
                now(),
            ),
        )
        get_db().commit()
        make_report_pdf(cursor.lastrowid)
        draft_id = request.form.get("draft_id", "")
        if draft_id:
            get_db().execute("DELETE FROM report_drafts WHERE id = ? AND user_id = ?", (draft_id, g.user["id"]))
        else:
            get_db().execute("DELETE FROM report_drafts WHERE user_id = ? AND report_date = ?", (g.user["id"], request.form.get("report_date", "")))
        get_db().commit()
        session["last_submit_at"] = now()
        flash("Report submitted and PDF generated.", "success")
        return redirect(url_for("view_report", report_id=cursor.lastrowid))

    draft_id = request.args.get("draft", "")
    today = datetime.now().strftime("%Y-%m-%d")
    return render_template(
        "report_form.html",
        departments=DEPARTMENTS,
        branches=BRANCHES,
        today=today,
        day_of_week=day_name(today),
        reporting_period=default_reporting_period(today),
        metrics_config=METRICS_CONFIG,
        load_draft_id=draft_id,
    )


@app.route("/reports/<int:report_id>")
@login_required
def view_report(report_id):
    report = get_db().execute(
        """
        SELECT reports.*, users.full_name, users.email, users.phone
        FROM reports
        JOIN users ON users.id = reports.user_id
        WHERE reports.id = ?
        """,
        (report_id,),
    ).fetchone()
    if not report:
        abort(404)
    if not can_view_report(g.user, report):
        abort(403)
    edits = get_db().execute(
        "SELECT * FROM report_edits WHERE report_id = ? ORDER BY edited_at ASC",
        (report_id,),
    ).fetchall()
    comments = get_db().execute(
        "SELECT * FROM report_comments WHERE report_id = ? ORDER BY created_at ASC",
        (report_id,),
    ).fetchall()
    return render_template(
        "report_detail.html",
        report=report,
        tasks=json.loads(report["tasks_json"]),
        challenges=json.loads(report["challenges_json"]),
        tomorrow=json.loads(report["tomorrow_json"]),
        metrics=json.loads(report["metrics_json"]),
        metrics_config=METRICS_CONFIG,
        edits=edits,
        comments=comments,
    )


@app.route("/reports/<int:report_id>/edit", methods=["GET", "POST"])
@login_required
def edit_report(report_id):
    db = get_db()
    report = db.execute(
        """
        SELECT reports.*, users.full_name, users.email, users.phone
        FROM reports
        JOIN users ON users.id = reports.user_id
        WHERE reports.id = ?
        """,
        (report_id,),
    ).fetchone()
    if not report:
        abort(404)
    if not can_view_report(g.user, report):
        abort(403)
    if report["status"] == "approved":
        flash("This report has been approved and can no longer be edited.", "danger")
        return redirect(url_for("view_report", report_id=report_id))
    if report["user_id"] != g.user["id"]:
        abort(403)

    edit_count = db.execute(
        "SELECT COUNT(*) AS cnt FROM report_edits WHERE report_id = ?", (report_id,)
    ).fetchone()["cnt"]
    if edit_count >= 3:
        flash("This report has reached the maximum of 3 edits and can no longer be edited.", "danger")
        return redirect(url_for("view_report", report_id=report_id))

    if request.method == "POST":
        csrf_protect()
        form = {key: clean_text(value) for key, value in request.form.items()}
        tasks = parse_items("task", form)
        tomorrow = parse_items("tomorrow", form)
        challenges = parse_items("challenge", form)
        if not tasks:
            flash("Add at least one task or activity completed today.", "danger")
            return redirect(url_for("edit_report", report_id=report_id))

        department = clean_text(request.form.get("department")) or report["department"]
        dept_metrics = get_metrics_for_department(department)
        metrics = {}
        for field in dept_metrics:
            if field["type"] == "number":
                metrics[field["key"]] = int_field(field["key"])
            else:
                metrics[field["key"]] = request.form.get(field["key"], field.get("options", ["No"])[0])

        db.execute(
            """
            UPDATE reports SET
                report_date = ?, reporting_period = ?, branch = ?, department = ?, position = ?,
                day_summary = ?, tasks_json = ?, challenges_json = ?, decisions = ?,
                tomorrow_json = ?, comments = ?, metrics_json = ?
            WHERE id = ?
            """,
            (
                request.form.get("report_date"),
                clean_text(request.form.get("reporting_period")) or default_reporting_period(request.form.get("report_date")),
                clean_text(request.form.get("branch")) or report["branch"],
                department,
                clean_text(request.form.get("position")) or report["position"],
                clean_text(request.form.get("day_summary")),
                json.dumps(tasks),
                json.dumps(challenges),
                clean_text(request.form.get("decisions")),
                json.dumps(tomorrow),
                clean_text(request.form.get("comments")),
                json.dumps(metrics),
                report_id,
            ),
        )
        db.execute(
            "INSERT INTO report_edits (report_id, user_id, edited_at) VALUES (?, ?, ?)",
            (report_id, g.user["id"], now()),
        )
        db.commit()
        make_report_pdf(report_id)
        flash("Report updated successfully.", "success")
        return redirect(url_for("view_report", report_id=report_id))

    return render_template(
        "report_form.html",
        report=report,
        tasks=json.loads(report["tasks_json"]),
        challenges=json.loads(report["challenges_json"]),
        tomorrow=json.loads(report["tomorrow_json"]),
        metrics=json.loads(report["metrics_json"]),
        departments=DEPARTMENTS,
        branches=BRANCHES,
        today=report["report_date"],
        day_of_week=day_name(report["report_date"]),
        reporting_period=report["reporting_period"] or default_reporting_period(report["report_date"]),
        metrics_config=METRICS_CONFIG,
    )


@app.route("/reports/<int:report_id>/pdf")
@login_required
def download_report(report_id):
    report = get_db().execute("SELECT * FROM reports WHERE id = ?", (report_id,)).fetchone()
    if not report:
        abort(404)
    if not can_view_report(g.user, report):
        abort(403)
    path = REPORT_DIR / report["pdf_filename"] if report["pdf_filename"] else make_report_pdf(report_id)
    if not path.exists():
        path = make_report_pdf(report_id)
    return send_file(path, as_attachment=True, download_name=path.name)


@app.route("/admin/users", methods=["GET", "POST"])
@superadmin_required
def admin_users():
    db = get_db()
    if request.method == "POST":
        csrf_protect()
        user_id = int(request.form.get("user_id"))
        role = request.form.get("role")
        is_active = 1 if request.form.get("is_active") == "on" else 0
        if role not in ("employee", "admin", "principal", "superadmin", "shadowadmin"):
            abort(400)
        db.execute("UPDATE users SET role = ?, is_active = ? WHERE id = ?", (role, is_active, user_id))
        db.commit()
        flash("User updated.", "success")
        return redirect(url_for("admin_users"))

    users = db.execute("SELECT * FROM users WHERE deleted_at IS NULL ORDER BY role DESC, full_name ASC").fetchall()
    if g.user["role"] != "shadowadmin":
        users = [u for u in users if u["role"] != "shadowadmin"]
    return render_template("admin_users.html", users=users)


@app.route("/admin/users/<int:user_id>/reset-password", methods=["GET", "POST"])
@superadmin_required
def admin_reset_password(user_id):
    db = get_db()
    target = db.execute("SELECT * FROM users WHERE id = ?", (user_id,)).fetchone()
    if not target:
        abort(404)
    if request.method == "POST":
        csrf_protect()
        password = request.form.get("password", "")
        confirm = request.form.get("confirm_password", "")
        if len(password) < 8:
            flash("Use a password with at least 8 characters.", "danger")
            return render_template("admin_reset_password.html", target=target, back_url=url_for("admin_users"))
        if password != confirm:
            flash("Passwords do not match.", "danger")
            return render_template("admin_reset_password.html", target=target, back_url=url_for("admin_users"))
        db.execute("UPDATE users SET password_hash = ? WHERE id = ?", (generate_password_hash(password), user_id))
        db.commit()
        flash(f"Password for {target['full_name']} has been reset.", "success")
        return redirect(url_for("admin_users"))
    return render_template("admin_reset_password.html", target=target, back_url=url_for("admin_users"))


@app.route("/admin/access/<int:admin_id>", methods=["GET", "POST"])
@superadmin_required
def admin_access(admin_id):
    db = get_db()
    admin = db.execute("SELECT * FROM users WHERE id = ? AND role IN ('admin', 'principal', 'superadmin')", (admin_id,)).fetchone()
    if not admin:
        abort(404)
    if request.method == "POST":
        csrf_protect()
        employee_ids = request.form.getlist("employee_ids")
        db.execute("DELETE FROM report_access WHERE admin_id = ?", (admin_id,))
        for employee_id in employee_ids:
            db.execute(
                "INSERT OR IGNORE INTO report_access (admin_id, employee_id, created_at) VALUES (?, ?, ?)",
                (admin_id, int(employee_id), now()),
            )
        db.commit()
        flash("Report access updated.", "success")
        return redirect(url_for("admin_access", admin_id=admin_id))

    employees = db.execute("SELECT * FROM users WHERE role = 'employee' ORDER BY full_name ASC").fetchall()
    assigned = {
        row["employee_id"]
        for row in db.execute("SELECT employee_id FROM report_access WHERE admin_id = ?", (admin_id,)).fetchall()
    }
    return render_template("admin_access.html", admin=admin, employees=employees, assigned=assigned)


@app.route("/admin/settings", methods=["GET", "POST"])
@superadmin_required
def admin_settings():
    db = get_db()
    if request.method == "POST":
        csrf_protect()
        registration = "1" if request.form.get("registration_open") == "on" else "0"
        db.execute("INSERT OR REPLACE INTO settings (key, value) VALUES ('registration_open', ?)", (registration,))
        db.commit()
        flash("Settings saved.", "success")
        return redirect(url_for("admin_settings"))
    reg_open = db.execute("SELECT value FROM settings WHERE key = 'registration_open'").fetchone()
    return render_template("admin_settings.html", registration_open=(reg_open and reg_open["value"] == "1"))


@app.route("/admin/unlock/<int:user_id>", methods=["GET", "POST"])
@superadmin_required
def admin_unlock(user_id):
    db = get_db()
    target = db.execute("SELECT * FROM users WHERE id = ?", (user_id,)).fetchone()
    if not target:
        abort(404)
    if request.method == "POST":
        csrf_protect()
        reason = clean_text(request.form.get("reason", ""), 500)
        if not reason:
            flash("You must provide a reason for unlocking this account.", "danger")
            return render_template("admin_unlock.html", target=target)
        db.execute(
            "UPDATE users SET is_active = 1, locked_at = NULL, lock_reason = ? WHERE id = ?",
            (reason, user_id),
        )
        db.commit()
        flash(f"{target['full_name']} has been unlocked. Reason recorded: {reason}", "success")
        return redirect(url_for("admin_users"))
    return render_template("admin_unlock.html", target=target)


@app.route("/admin/users/<int:user_id>/manage", methods=["GET", "POST"])
@superadmin_required
def admin_manage_user(user_id):
    db = get_db()
    target = db.execute("SELECT * FROM users WHERE id = ?", (user_id,)).fetchone()
    if not target:
        abort(404)
    if target["role"] in ("superadmin", "shadowadmin") and target["id"] != g.user["id"]:
        abort(403)
    if request.method == "POST":
        csrf_protect()
        action = request.form.get("action", "")
        data_choice = request.form.get("data_choice", "keep")
        if action == "deactivate":
            db.execute(
                "UPDATE users SET is_active = 0, data_retention = ? WHERE id = ?",
                (data_choice, user_id),
            )
            db.commit()
            flash(f"{target['full_name']} deactivated. Data: {data_choice}.", "success")
            return redirect(url_for("admin_users"))
        elif action == "delete":
            if data_choice == "delete_all":
                reports = db.execute("SELECT id, pdf_filename FROM reports WHERE user_id = ?", (user_id,)).fetchall()
                for r in reports:
                    db.execute("DELETE FROM report_edits WHERE report_id = ?", (r["id"],))
                    db.execute("DELETE FROM report_comments WHERE report_id = ?", (r["id"],))
                    if r["pdf_filename"]:
                        pdf_path = REPORT_DIR / r["pdf_filename"]
                        if pdf_path.exists():
                            pdf_path.unlink()
                db.execute("DELETE FROM reports WHERE user_id = ?", (user_id,))
            elif data_choice == "archive":
                db.execute("UPDATE reports SET archived = 1 WHERE user_id = ?", (user_id,))
            db.execute("DELETE FROM report_access WHERE admin_id = ? OR employee_id = ?", (user_id, user_id))
            db.execute("DELETE FROM report_edits WHERE user_id = ?", (user_id,))
            db.execute("DELETE FROM report_comments WHERE admin_id = ?", (user_id,))
            db.execute("DELETE FROM password_resets WHERE user_id = ?", (user_id,))
            db.execute("DELETE FROM users WHERE id = ?", (user_id,))
            db.commit()
            flash(f"{target['full_name']} deleted. Data: {data_choice}.", "success")
            return redirect(url_for("admin_users"))
        else:
            flash("Invalid action.", "danger")
            return redirect(url_for("admin_manage_user", user_id=user_id))
    report_count = db.execute("SELECT COUNT(*) AS c FROM reports WHERE user_id = ?", (user_id,)).fetchone()["c"]
    return render_template("admin_manage_user.html", target=target, report_count=report_count)


@app.route("/admin/department", methods=["GET", "POST"])
@admin_required
def admin_department():
    db = get_db()
    dept = g.user["department"]
    if request.method == "POST":
        csrf_protect()
        toggle_id = request.form.get("toggle_id")
        if toggle_id:
            target = db.execute("SELECT * FROM users WHERE id = ? AND department = ?", (toggle_id, dept)).fetchone()
            if target and target["role"] == "employee":
                new_active = 0 if target["is_active"] else 1
                db.execute("UPDATE users SET is_active = ? WHERE id = ?", (new_active, toggle_id))
                db.commit()
                status = "activated" if new_active else "deactivated"
                flash(f"{target['full_name']} {status}.", "success")
            return redirect(url_for("admin_department"))
        full_name = clean_text(request.form.get("full_name"), 120)
        email = clean_text(request.form.get("email"), 160).lower()
        password = request.form.get("password", "")
        if len(password) < 8:
            flash("Use a password with at least 8 characters.", "danger")
            return redirect(url_for("admin_department"))
        try:
            db.execute(
                """
                INSERT INTO users (
                    full_name, email, phone, department, position, branch,
                    password_hash, role, is_active, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                """,
                (
                    full_name,
                    email,
                    clean_text(request.form.get("phone"), 40),
                    dept,
                    clean_text(request.form.get("position"), 100),
                    clean_text(request.form.get("branch")) or g.user["branch"],
                    generate_password_hash(password),
                    "employee",
                    1,
                    now(),
                ),
            )
            db.commit()
            user_id = db.execute("SELECT id FROM users WHERE email = ?", (email,)).fetchone()["id"]
            dept_admins = db.execute(
                "SELECT id FROM users WHERE department = ? AND role IN ('admin', 'principal', 'superadmin') AND id != ?",
                (dept, user_id),
            ).fetchall()
            for da in dept_admins:
                db.execute(
                    "INSERT OR IGNORE INTO report_access (admin_id, employee_id, created_at) VALUES (?, ?, ?)",
                    (da["id"], user_id, now()),
                )
            db.commit()
            flash(f"User {full_name} added to {dept}.", "success")
        except sqlite3.IntegrityError:
            flash("That email address is already registered.", "danger")
        return redirect(url_for("admin_department"))

    users = db.execute(
        "SELECT * FROM users WHERE department = ? ORDER BY role DESC, full_name ASC",
        (dept,),
    ).fetchall()
    return render_template("admin_department.html", dept_users=users, departments=DEPARTMENTS, branches=BRANCHES)


@app.route("/admin/department/<int:user_id>/reset-password", methods=["GET", "POST"])
@admin_required
def admin_department_reset_password(user_id):
    db = get_db()
    target = db.execute(
        "SELECT * FROM users WHERE id = ? AND department = ?",
        (user_id, g.user["department"]),
    ).fetchone()
    if not target:
        abort(404)
    if request.method == "POST":
        csrf_protect()
        password = request.form.get("password", "")
        confirm = request.form.get("confirm_password", "")
        if len(password) < 8:
            flash("Use a password with at least 8 characters.", "danger")
            return render_template("admin_reset_password.html", target=target, back_url=url_for("admin_department"))
        if password != confirm:
            flash("Passwords do not match.", "danger")
            return render_template("admin_reset_password.html", target=target, back_url=url_for("admin_department"))
        db.execute("UPDATE users SET password_hash = ? WHERE id = ?", (generate_password_hash(password), user_id))
        db.commit()
        flash(f"Password for {target['full_name']} has been reset.", "success")
        return redirect(url_for("admin_department"))
    return render_template("admin_reset_password.html", target=target, back_url=url_for("admin_department"))


@app.route("/reports/<int:report_id>/status", methods=["POST"])
@login_required
def update_status(report_id):
    csrf_protect()
    if g.user["role"] not in ADMIN_ROLES:
        abort(403)
    report = get_db().execute("SELECT * FROM reports WHERE id = ?", (report_id,)).fetchone()
    if not report:
        abort(404)
    if not can_view_report(g.user, report):
        abort(403)
    new_status = request.form.get("status", "")
    if new_status not in ("submitted", "reviewed", "approved"):
        abort(400)
    get_db().execute("UPDATE reports SET status = ? WHERE id = ?", (new_status, report_id))
    get_db().commit()
    if new_status in ("reviewed", "approved") and report["user_id"] != g.user["id"]:
        user = get_db().execute("SELECT full_name FROM users WHERE id = ?", (g.user["id"],)).fetchone()
        actor = user["full_name"] if user else "An administrator"
        create_notification(
            user_id=report["user_id"],
            kind="status",
            title=f"Report {new_status}",
            body=f"{actor} marked your report from {report['report_date']} as {new_status}.",
            link=url_for("view_report", report_id=report_id),
            actor_id=g.user["id"],
        )
    flash(f"Report marked as {new_status}.", "success")
    return redirect(url_for("view_report", report_id=report_id))


@app.route("/reports/export.csv")
@login_required
def export_csv():
    db = get_db()
    archived_expr = archived_filter("reports.archived")
    if g.user["role"] == "employee":
        rows = db.execute(
            f"SELECT reports.*, users.full_name FROM reports JOIN users ON users.id = reports.user_id WHERE reports.user_id = ? AND {archived_expr} = 0 ORDER BY reports.report_date DESC",
            (g.user["id"],),
        ).fetchall()
    elif g.user["role"] == "admin":
        rows = db.execute(
            f"""
            SELECT reports.*, users.full_name FROM reports
            JOIN users ON users.id = reports.user_id
            JOIN report_access ON report_access.employee_id = reports.user_id
            WHERE report_access.admin_id = ? AND {archived_expr} = 0
            ORDER BY reports.report_date DESC
            """,
            (g.user["id"],),
        ).fetchall()
    else:
        rows = db.execute(
            f"SELECT reports.*, users.full_name FROM reports JOIN users ON users.id = reports.user_id WHERE {archived_expr} = 0 ORDER BY reports.report_date DESC"
        ).fetchall()

    output = io.StringIO()
    writer = csv.writer(output)
    writer.writerow(["Date", "Employee", "Department", "Position", "Branch", "Summary", "Status", "Submitted At"])
    for r in rows:
        writer.writerow([
            csv_safe(r["report_date"]),
            csv_safe(r["full_name"]),
            csv_safe(r["department"]),
            csv_safe(r["position"]),
            csv_safe(r["branch"]),
            csv_safe(r["day_summary"]),
            csv_safe(r["status"]),
            csv_safe(r["created_at"]),
        ])

    return Response(
        output.getvalue(),
        mimetype="text/csv",
        headers={"Content-Disposition": "attachment; filename=toolkit_reports_export.csv"},
    )


@app.route("/reports/<int:report_id>/archive", methods=["POST"])
@login_required
def archive_report(report_id):
    csrf_protect()
    report = get_db().execute("SELECT * FROM reports WHERE id = ?", (report_id,)).fetchone()
    if not report:
        abort(404)
    if not can_view_report(g.user, report):
        abort(403)
    archived = 1 if request.form.get("archive") == "1" else 0
    get_db().execute("UPDATE reports SET archived = ? WHERE id = ?", (archived, report_id))
    get_db().commit()
    flash("Report archived." if archived else "Report unarchived.", "success")
    return redirect(url_for("view_report", report_id=report_id))


@app.route("/reports/<int:report_id>/delete", methods=["POST"])
@superadmin_required
def delete_report(report_id):
    csrf_protect()
    db = get_db()
    report = db.execute("SELECT * FROM reports WHERE id = ?", (report_id,)).fetchone()
    if not report:
        abort(404)
    pdf = report["pdf_filename"]
    db.execute("DELETE FROM report_edits WHERE report_id = ?", (report_id,))
    db.execute("DELETE FROM report_comments WHERE report_id = ?", (report_id,))
    db.execute("DELETE FROM reports WHERE id = ?", (report_id,))
    db.commit()
    if pdf:
        pdf_path = REPORT_DIR / pdf
        if pdf_path.exists():
            pdf_path.unlink()
    flash("Report permanently deleted.", "success")
    return redirect(url_for("dashboard"))


@app.route("/reports/<int:report_id>/comment", methods=["POST"])
@login_required
def add_comment(report_id):
    if g.user["role"] not in ADMIN_ROLES:
        abort(403)
    csrf_protect()
    report = get_db().execute("SELECT * FROM reports WHERE id = ?", (report_id,)).fetchone()
    if not report:
        abort(404)
    if not can_view_report(g.user, report):
        abort(403)
    comment = clean_text(request.form.get("comment"), 2000)
    if not comment:
        flash("Comment cannot be empty.", "danger")
        return redirect(url_for("view_report", report_id=report_id))
    get_db().execute(
        "INSERT INTO report_comments (report_id, admin_id, admin_name, comment, created_at) VALUES (?, ?, ?, ?, ?)",
        (report_id, g.user["id"], g.user["full_name"], comment, now()),
    )
    get_db().commit()
    if report["user_id"] != g.user["id"]:
        create_notification(
            user_id=report["user_id"],
            kind="comment",
            title="New comment on your report",
            body=f"{g.user['full_name']} commented on your report from {report['report_date']}: {comment[:120]}{'...' if len(comment) > 120 else ''}",
            link=url_for("view_report", report_id=report_id),
            actor_id=g.user["id"],
        )
    flash("Comment added.", "success")
    return redirect(url_for("view_report", report_id=report_id))


def cleanup_expired_resets():
    try:
        db = get_db()
        db.execute("DELETE FROM password_resets WHERE expires_at < ?", (now(),))
        db.execute("DELETE FROM notifications WHERE is_read = 1 AND created_at < ?", (datetime.now() - timedelta(days=30)).strftime("%Y-%m-%d %H:%M:%S"))
        db.commit()
    except Exception:
        pass


@app.route("/health")
def health_check():
    db_ok = False
    try:
        get_db().execute("SELECT 1")
        db_ok = True
    except Exception:
        pass
    status = 200 if db_ok else 503
    return {"status": "healthy" if db_ok else "unhealthy", "database": "connected" if db_ok else "disconnected"}, status


@app.errorhandler(403)
def forbidden(_error):
    return render_template("error.html", title="Access restricted", message="You do not have permission to view this page or report."), 403


@app.errorhandler(400)
def bad_request(_error):
    return render_template("error.html", title="Bad request", message="The request could not be processed. Try reloading and submitting again."), 400


@app.errorhandler(404)
def not_found(_error):
    return render_template("error.html", title="Not found", message="The requested page or report was not found."), 404


@app.errorhandler(500)
def server_error(_error):
    log.exception("Unhandled server error")
    try:
        return render_template("error.html", title="Server error", message="Something went wrong. Please try again or contact the administrator."), 500
    except Exception:
        return "Internal Server Error", 500, {"Content-Type": "text/plain"}


init_db()
cleanup_expired_resets()
validate_production_config()
if app.config["SECRET_KEY"] == "local-dev-change-me":
    import warnings
    warnings.warn("Using insecure default SECRET_KEY. Set the SECRET_KEY environment variable for production.")

if __name__ == "__main__":
    port = int(os.environ.get("PORT", "5055"))
    debug = os.environ.get("DEBUG", "").lower() in ("1", "true", "yes")
    app.run(host=os.environ.get("HOST", "0.0.0.0"), port=port, debug=debug)
