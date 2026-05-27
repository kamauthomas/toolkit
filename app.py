import csv
import io
import json
import os
import re
import secrets
import sqlite3
import textwrap
import zlib
from datetime import datetime
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


app = Flask(__name__)
app.config["SECRET_KEY"] = os.environ.get("SECRET_KEY", "local-dev-change-me")
app.config["MAX_CONTENT_LENGTH"] = 512 * 1024
app.config["SESSION_COOKIE_HTTPONLY"] = True
app.config["SESSION_COOKIE_SAMESITE"] = "Lax"
app.config["SESSION_COOKIE_SECURE"] = os.environ.get("ENV") == "production"

RATE_LIMIT_SECONDS = 30


def generate_csrf_token():
    if "_csrf_token" not in session:
        session["_csrf_token"] = secrets.token_hex(32)
    return session["_csrf_token"]


def csrf_protect():
    token = session.get("_csrf_token")
    given = request.form.get("_csrf_token")
    if not token or not given or not secrets.compare_digest(token, given):
        abort(400)


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


def get_db():
    if "db" not in g:
        g.db = sqlite3.connect(DB_PATH)
        g.db.row_factory = sqlite3.Row
    return g.db


@app.teardown_appcontext
def close_db(_error):
    db = g.pop("db", None)
    if db is not None:
        db.close()


def init_db():
    db = sqlite3.connect(DB_PATH)
    db.executescript(
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
        """
    )
    db.commit()

    # migrate: add status column if missing
    try:
        db.execute("ALTER TABLE reports ADD COLUMN status TEXT NOT NULL DEFAULT 'submitted'")
    except sqlite3.OperationalError:
        pass
    try:
        db.execute("ALTER TABLE reports ADD COLUMN archived INTEGER NOT NULL DEFAULT 0")
    except sqlite3.OperationalError:
        pass

    admin_email = os.environ.get("ADMIN_EMAIL", "admin@toolkit.local").strip().lower()
    admin_password = os.environ.get("ADMIN_PASSWORD", "ChangeMe123!")
    exists = db.execute("SELECT id FROM users WHERE role = 'superadmin' LIMIT 1").fetchone()
    if not exists:
        db.execute(
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
        db.commit()

    db.close()


def now():
    return datetime.now().strftime("%Y-%m-%d %H:%M:%S")


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
    return dict(csrf_token=generate_csrf_token())


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


def admin_required(view):
    @wraps(view)
    def wrapped(*args, **kwargs):
        if g.user is None:
            return redirect(url_for("login"))
        if g.user["role"] not in ("admin", "superadmin"):
            abort(403)
        return view(*args, **kwargs)

    return wrapped


def superadmin_required(view):
    @wraps(view)
    def wrapped(*args, **kwargs):
        if g.user is None:
            return redirect(url_for("login"))
        if g.user["role"] != "superadmin":
            abort(403)
        return view(*args, **kwargs)

    return wrapped


def can_view_report(user, report):
    if user["role"] == "superadmin":
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

    lines = [
        ("TOOLKIT AFRICA", "title"),
        ("EMPLOYEE DAILY REPORT", "subtitle"),
        ("", "normal"),
        (f"Employee: {report['full_name']}", "normal"),
        (f"Email: {report['email']}", "normal"),
        (f"Phone: {report['phone'] or 'Not provided'}", "normal"),
        (f"Position: {report['position']}", "normal"),
        (f"Department: {report['department']}", "normal"),
        (f"Branch / Work Location: {report['branch']}", "normal"),
        (f"Report Date: {report['report_date']}", "normal"),
        (f"Submitted At: {report['created_at']}", "normal"),
        ("", "normal"),
        ("Daily Summary", "heading"),
        (report["day_summary"], "normal"),
        ("", "normal"),
        ("Key Tasks / Activities Performed", "heading"),
    ]

    if tasks:
        for idx, task in enumerate(tasks, 1):
            lines.extend(
                [
                    (f"{idx}. {task.get('activity', '')}", "bold"),
                    (f"Description: {task.get('description', '') or 'Not provided'}", "normal"),
                    (f"Time Spent: {task.get('time', '') or 'Not provided'}", "normal"),
                    (f"Status: {task.get('status', '') or 'Not provided'}", "normal"),
                    ("", "normal"),
                ]
            )
    else:
        lines.append(("No tasks recorded.", "normal"))

    lines.extend([("Challenges / Blockers", "heading")])
    if challenges:
        for idx, challenge in enumerate(challenges, 1):
            lines.extend(
                [
                    (f"{idx}. {challenge.get('challenge', '')}", "bold"),
                    (f"Impact: {challenge.get('impact', '') or 'Not provided'}", "normal"),
                    (f"Action Taken: {challenge.get('action', '') or 'Not provided'}", "normal"),
                    (f"Support Needed: {challenge.get('support', '') or 'Not provided'}", "normal"),
                    ("", "normal"),
                ]
            )
    else:
        lines.append(("No challenges recorded.", "normal"))

    lines.extend(
        [
            ("Key Decisions", "heading"),
            (report["decisions"] or "No key decisions recorded.", "normal"),
            ("Tomorrow's Plan", "heading"),
        ]
    )
    if tomorrow:
        for idx, item in enumerate(tomorrow, 1):
            lines.extend(
                [
                    (f"{idx}. {item.get('task', '')}", "bold"),
                    (f"Planned Activities: {item.get('activities', '') or 'Not provided'}", "normal"),
                    (f"Goal: {item.get('objective', '') or 'Not provided'}", "normal"),
                    ("", "normal"),
                ]
            )
    else:
        lines.append(("No plan recorded.", "normal"))

    metric_lines = [("Operational Metrics", "heading")]
    for key, value in metrics.items():
        label = get_metric_label(key)
        metric_lines.append((f"{label}: {value}", "normal"))
    metric_lines.append(("", "normal"))
    metric_lines.append(("Comments / Recommendations", "heading"))
    metric_lines.append((report["comments"] or "No comments recorded.", "normal"))
    lines.extend(metric_lines)

    filename = f"Toolkit_Report_{report['full_name'].replace(' ', '_')}_{report['report_date']}_{report['id']}.pdf"
    filename = secure_filename(filename)
    path = REPORT_DIR / filename
    write_pdf(path, lines, LOGO_PATH if LOGO_PATH.exists() else None)
    db.execute("UPDATE reports SET pdf_filename = ? WHERE id = ?", (filename, report_id))
    db.commit()
    return path


def pdf_escape(text):
    return str(text).replace("\\", "\\\\").replace("(", "\\(").replace(")", "\\)")


def write_pdf(path, lines, logo_path=None):
    page_width = 595
    page_height = 842
    margin = 54
    y_start = 760
    y = y_start
    pages = []
    current = []

    def add_line(text, style="normal"):
        nonlocal y, current
        size = {"title": 18, "subtitle": 13, "heading": 12, "bold": 10, "normal": 10}[style]
        leading = 18 if style in ("title", "subtitle", "heading") else 14
        if y < 70:
            pages.append(current)
            current = []
            y = y_start
        current.append((text, style, size, y))
        y -= leading

    for text, style in lines:
        width = 52 if style in ("normal", "bold") else 44
        for part in str(text).split("\n"):
            wrapped = textwrap.wrap(part, width=width) or [""]
            for chunk in wrapped:
                add_line(chunk, style)
        if style == "heading":
            y -= 4
    pages.append(current)

    objects = []
    logo_obj_id = None
    logo_meta = None

    if logo_path and Path(logo_path).exists():
        try:
            img = Image.open(logo_path).convert("RGB")
            img.thumbnail((150, 60))
            raw = img.tobytes()
            compressed = zlib.compress(raw)
            logo_meta = (img.width, img.height, compressed)
        except Exception:
            logo_meta = None

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
    content_ids = []
    for page in pages:
        commands = ["q"]
        if logo_obj_id:
            commands.append("150 0 0 37 54 785 cm /Logo Do")
        commands.append("Q")
        for text, style, size, ypos in page:
            font = "F2" if style in ("title", "subtitle", "heading", "bold") else "F1"
            x = margin
            if style in ("title", "subtitle"):
                x = 180 if style == "title" else 190
            commands.append(f"BT /{font} {size} Tf {x} {ypos} Td ({pdf_escape(text)}) Tj ET")
        stream = "\n".join(commands).encode("utf-8")
        resources = f"<< /Font << /F1 {font_regular} 0 R /F2 {font_bold} 0 R >>"
        if logo_obj_id:
            resources += f" /XObject << /Logo {logo_obj_id} 0 R >>"
        resources += " >>"
        content_id = add_object(
            b"<< /Length " + str(len(stream)).encode() + b" >>\nstream\n" + stream + b"\nendstream"
        )
        content_ids.append(content_id)
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
def login():
    if request.method == "POST":
        csrf_protect()
        email = clean_text(request.form.get("email"), 160).lower()
        password = request.form.get("password", "")
        user = get_db().execute("SELECT * FROM users WHERE email = ?", (email,)).fetchone()
        if user and check_password_hash(user["password_hash"], password):
            if not user["is_active"]:
                flash("Your account is pending activation by your department admin.", "warning")
                return redirect(url_for("login"))
            session.clear()
            session["user_id"] = user["id"]
            flash("Signed in successfully.", "success")
            return redirect(url_for("dashboard"))
        flash("Invalid email or password.", "danger")
    return render_template("login.html")


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
    show_archived = archived == 1
    if g.user["role"] == "employee":
        reports = db.execute(
            "SELECT * FROM reports WHERE user_id = ? AND archived = ? ORDER BY report_date DESC, id DESC LIMIT 20",
            (g.user["id"], show_archived),
        ).fetchall()
    elif g.user["role"] == "admin":
        reports = db.execute(
            """
            SELECT reports.*, users.full_name
            FROM reports
            JOIN users ON users.id = reports.user_id
            JOIN report_access ON report_access.employee_id = reports.user_id
            WHERE report_access.admin_id = ? AND reports.archived = ?
            ORDER BY reports.report_date DESC, reports.id DESC
            LIMIT 40
            """,
            (g.user["id"], show_archived),
        ).fetchall()
    else:
        reports = db.execute(
            """
            SELECT reports.*, users.full_name
            FROM reports
            JOIN users ON users.id = reports.user_id
            WHERE reports.archived = ?
            ORDER BY reports.report_date DESC, reports.id DESC
            LIMIT 50
            """,
            (show_archived,),
        ).fetchall()
    return render_template("dashboard.html", reports=reports, show_archived=show_archived)


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
                user_id, report_date, branch, department, position, day_summary,
                tasks_json, challenges_json, decisions, tomorrow_json, comments,
                metrics_json, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            """,
            (
                g.user["id"],
                request.form.get("report_date"),
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
                now(),
            ),
        )
        get_db().commit()
        make_report_pdf(cursor.lastrowid)
        session["last_submit_at"] = now()
        flash("Report submitted and PDF generated.", "success")
        return redirect(url_for("view_report", report_id=cursor.lastrowid))

    return render_template("report_form.html", departments=DEPARTMENTS, branches=BRANCHES, today=datetime.now().strftime("%Y-%m-%d"), metrics_config=METRICS_CONFIG)


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
    return render_template(
        "report_detail.html",
        report=report,
        tasks=json.loads(report["tasks_json"]),
        challenges=json.loads(report["challenges_json"]),
        tomorrow=json.loads(report["tomorrow_json"]),
        metrics=json.loads(report["metrics_json"]),
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
        if role not in ("employee", "admin", "superadmin"):
            abort(400)
        db.execute("UPDATE users SET role = ?, is_active = ? WHERE id = ?", (role, is_active, user_id))
        db.commit()
        flash("User updated.", "success")
        return redirect(url_for("admin_users"))

    users = db.execute("SELECT * FROM users ORDER BY role DESC, full_name ASC").fetchall()
    return render_template("admin_users.html", users=users)


@app.route("/admin/access/<int:admin_id>", methods=["GET", "POST"])
@superadmin_required
def admin_access(admin_id):
    db = get_db()
    admin = db.execute("SELECT * FROM users WHERE id = ? AND role IN ('admin', 'superadmin')", (admin_id,)).fetchone()
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
                "SELECT id FROM users WHERE department = ? AND role IN ('admin', 'superadmin') AND id != ?",
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


@app.route("/reports/<int:report_id>/status", methods=["POST"])
@login_required
def update_status(report_id):
    csrf_protect()
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
    flash(f"Report marked as {new_status}.", "success")
    return redirect(url_for("view_report", report_id=report_id))


@app.route("/reports/export.csv")
@login_required
def export_csv():
    db = get_db()
    if g.user["role"] == "employee":
        rows = db.execute(
            "SELECT reports.*, users.full_name FROM reports JOIN users ON users.id = reports.user_id WHERE reports.user_id = ? AND reports.archived = 0 ORDER BY reports.report_date DESC",
            (g.user["id"],),
        ).fetchall()
    elif g.user["role"] == "admin":
        rows = db.execute(
            """
            SELECT reports.*, users.full_name FROM reports
            JOIN users ON users.id = reports.user_id
            JOIN report_access ON report_access.employee_id = reports.user_id
            WHERE report_access.admin_id = ? AND reports.archived = 0
            ORDER BY reports.report_date DESC
            """,
            (g.user["id"],),
        ).fetchall()
    else:
        rows = db.execute(
            "SELECT reports.*, users.full_name FROM reports JOIN users ON users.id = reports.user_id WHERE reports.archived = 0 ORDER BY reports.report_date DESC"
        ).fetchall()

    output = io.StringIO()
    writer = csv.writer(output)
    writer.writerow(["Date", "Employee", "Department", "Position", "Branch", "Summary", "Status", "Submitted At"])
    for r in rows:
        writer.writerow([r["report_date"], r["full_name"], r["department"], r["position"], r["branch"], r["day_summary"], r["status"], r["created_at"]])

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
    return render_template("error.html", title="Server error", message="Something went wrong. Please try again or contact the administrator."), 500


if __name__ == "__main__":
    init_db()
    if app.config["SECRET_KEY"] == "local-dev-change-me":
        import warnings
        warnings.warn("Using insecure default SECRET_KEY. Set the SECRET_KEY environment variable for production.")
    port = int(os.environ.get("PORT", "5055"))
    debug = os.environ.get("DEBUG", "").lower() in ("1", "true", "yes")
    app.run(host=os.environ.get("HOST", "127.0.0.1"), port=port, debug=debug)
