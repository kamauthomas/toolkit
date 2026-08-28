from datetime import datetime, timedelta

from werkzeug.security import generate_password_hash

import app as app_module


def login_session(client, user_id=1):
    with client.session_transaction() as sess:
        sess["user_id"] = user_id
        sess["_created_at"] = "2026-08-28 00:00:00"
        sess["_csrf_token"] = "test-token"
        sess.permanent = True


def add_employee(email="officer@example.test", department="Admissions", active=1, locked_at=None):
    db = app_module.get_db()
    cursor = db.execute(
        """INSERT INTO users
           (full_name, email, department, position, branch, password_hash, role,
            is_active, created_at, last_login, locked_at, lock_reason)
           VALUES (?, ?, ?, ?, ?, ?, 'employee', ?, ?, ?, ?, ?)""",
        (
            "Admissions Officer",
            email,
            department,
            "Admissions Officer",
            "Toolkit Africa Main Office",
            generate_password_hash("password123"),
            active,
            app_module.now(),
            (datetime.now() - timedelta(days=6)).strftime("%Y-%m-%d %H:%M:%S"),
            locked_at,
            "Auto-locked: inactive for 5+ days" if locked_at else "",
        ),
    )
    db.commit()
    return cursor.lastrowid


class TestIntakeTargets:
    def test_operational_module_pages_render_for_admin(self, client):
        login_session(client)
        expected = {
            "/admissions": b"Admission records",
            "/intake-targets": b"Monthly intake targets",
            "/meetings": b"Meetings and actions",
            "/modules": b"Operational workflow",
        }
        for path, marker in expected.items():
            response = client.get(path)
            assert response.status_code == 200
            assert marker in response.data

    def test_target_uses_verified_admissions_for_selected_month(self, client):
        login_session(client)
        response = client.post(
            "/intake-targets",
            data={
                "_csrf_token": "test-token",
                "officer_id": "1",
                "target_month": "2026-08",
                "target_count": "2",
            },
            follow_redirects=False,
        )
        assert response.status_code == 302
        with app_module.app.app_context():
            db = app_module.get_db()
            db.execute(
                """INSERT INTO admissions
                   (applicant_name, course, admission_date, status, created_by, created_at, updated_at)
                   VALUES (?, ?, ?, 'verified', ?, ?, ?)""",
                ("Verified Learner", "Welding", "2026-08-15", 1, app_module.now(), app_module.now()),
            )
            db.commit()

        page = client.get("/intake-targets?month=2026-08")
        assert page.status_code == 200
        assert b"Verified actual" in page.data
        assert b"50%" in page.data

    def test_employee_cannot_set_intake_target(self, client):
        with app_module.app.app_context():
            officer_id = add_employee()
        login_session(client, officer_id)
        response = client.post(
            "/intake-targets",
            data={
                "_csrf_token": "test-token",
                "officer_id": str(officer_id),
                "target_month": "2026-08",
                "target_count": "10",
            },
        )
        assert response.status_code == 403


class TestMeetings:
    def test_meeting_action_can_be_assigned_and_completed(self, client):
        login_session(client)
        response = client.post(
            "/meetings",
            data={
                "_csrf_token": "test-token",
                "title": "Admissions follow-up",
                "meeting_date": "2026-08-28",
                "department": "Admissions",
                "attendees": "Admissions team",
                "summary": "Agreed to follow up verified applicants.",
            },
            follow_redirects=False,
        )
        assert response.status_code == 302
        with app_module.app.app_context():
            meeting = app_module.get_db().execute("SELECT * FROM meetings").fetchone()

        response = client.post(
            f"/meetings/{meeting['id']}/actions",
            data={
                "_csrf_token": "test-token",
                "description": "Call verified applicants",
                "owner_id": "1",
                "due_date": "2026-08-29",
            },
            follow_redirects=False,
        )
        assert response.status_code == 302
        with app_module.app.app_context():
            action = app_module.get_db().execute("SELECT * FROM meeting_actions").fetchone()

        response = client.post(
            f"/meetings/{meeting['id']}/actions/{action['id']}/status",
            data={
                "_csrf_token": "test-token",
                "status": "completed",
                "completion_notes": "All calls completed.",
            },
            follow_redirects=False,
        )
        assert response.status_code == 302
        with app_module.app.app_context():
            updated = app_module.get_db().execute("SELECT * FROM meeting_actions WHERE id = ?", (action["id"],)).fetchone()
        assert updated["status"] == "completed"
        assert updated["completed_at"]

    def test_blocked_action_requires_explanation(self, client):
        login_session(client)
        with app_module.app.app_context():
            db = app_module.get_db()
            meeting_id = db.execute(
                """INSERT INTO meetings
                   (title, meeting_date, department, summary, created_by, created_at, updated_at)
                   VALUES (?, ?, ?, ?, ?, ?, ?)""",
                ("Review", "2026-08-28", "Management", "Review actions", 1, app_module.now(), app_module.now()),
            ).lastrowid
            action_id = db.execute(
                """INSERT INTO meeting_actions
                   (meeting_id, description, owner_id, status, created_by, created_at, updated_at)
                   VALUES (?, ?, ?, 'open', ?, ?, ?)""",
                (meeting_id, "Prepare evidence", 1, 1, app_module.now(), app_module.now()),
            ).lastrowid
            db.commit()
        response = client.post(
            f"/meetings/{meeting_id}/actions/{action_id}/status",
            data={"_csrf_token": "test-token", "status": "blocked", "completion_notes": ""},
            follow_redirects=True,
        )
        assert b"Explain what is blocking" in response.data


class TestAccountUnlock:
    def test_principal_cannot_manage_or_unlock_accounts(self, client):
        with app_module.app.app_context():
            db = app_module.get_db()
            db.execute("UPDATE users SET role = 'principal' WHERE id = 1")
            employee_id = add_employee(locked_at=app_module.now(), active=0)
        login_session(client)
        assert client.get("/admin/department").status_code == 403
        assert client.get(f"/admin/unlock/{employee_id}").status_code == 403

    def test_department_admin_can_unlock_employee_without_role_change(self, client):
        with app_module.app.app_context():
            db = app_module.get_db()
            db.execute("UPDATE users SET role = 'admin', department = 'Admissions' WHERE id = 1")
            employee_id = add_employee(locked_at=app_module.now(), active=0)
        login_session(client)
        response = client.post(
            f"/admin/unlock/{employee_id}",
            data={"_csrf_token": "test-token", "reason": "Employee identity confirmed by department lead."},
            follow_redirects=False,
        )
        assert response.status_code == 302
        assert "/admin/department" in response.headers["Location"]
        with app_module.app.app_context():
            user = app_module.get_db().execute("SELECT * FROM users WHERE id = ?", (employee_id,)).fetchone()
            event = app_module.get_db().execute(
                "SELECT * FROM account_events WHERE user_id = ? ORDER BY id DESC",
                (employee_id,),
            ).fetchone()
        assert user["role"] == "employee"
        assert user["is_active"] == 1
        assert user["locked_at"] is None
        assert event["event"] == "unlocked"
        assert event["actor_id"] == 1

    def test_promoting_locked_user_does_not_bypass_lock(self, client):
        with app_module.app.app_context():
            employee_id = add_employee(locked_at=app_module.now(), active=0)
        login_session(client)
        response = client.post(
            "/admin/users",
            data={
                "_csrf_token": "test-token",
                "user_id": str(employee_id),
                "role": "admin",
                "is_active": "on",
            },
            follow_redirects=False,
        )
        assert response.status_code == 302
        with app_module.app.app_context():
            user = app_module.get_db().execute("SELECT * FROM users WHERE id = ?", (employee_id,)).fetchone()
            app_module.init_db()
            refreshed = app_module.get_db().execute("SELECT * FROM users WHERE id = ?", (employee_id,)).fetchone()
        assert user["is_active"] == 0
        assert user["locked_at"] is not None
        assert refreshed["locked_at"] is not None

        with client.session_transaction() as sess:
            sess.clear()
            sess["_csrf_token"] = "test-token"
        login = client.post(
            "/login",
            data={
                "_csrf_token": "test-token",
                "email": "officer@example.test",
                "password": "password123",
            },
            follow_redirects=True,
        )
        assert b"locked due to inactivity" in login.data

    def test_five_day_inactivity_creates_auditable_lock(self, client):
        with app_module.app.app_context():
            employee_id = add_employee(active=1)
        with client.session_transaction() as sess:
            sess["_csrf_token"] = "test-token"
        response = client.post(
            "/login",
            data={
                "_csrf_token": "test-token",
                "email": "officer@example.test",
                "password": "password123",
            },
            follow_redirects=True,
        )
        assert b"locked due to 5 days of inactivity" in response.data
        with app_module.app.app_context():
            user = app_module.get_db().execute("SELECT * FROM users WHERE id = ?", (employee_id,)).fetchone()
            event = app_module.get_db().execute(
                "SELECT * FROM account_events WHERE user_id = ? ORDER BY id DESC",
                (employee_id,),
            ).fetchone()
        assert user["is_active"] == 0
        assert user["locked_at"] is not None
        assert event["event"] == "auto_locked"
