from datetime import datetime, timedelta

import app as app_module


class TestReports:
    def _login_as_admin(self, client):
        with client.session_transaction() as sess:
            sess["user_id"] = 1
            sess["_created_at"] = "2026-06-09 00:00:00"
            sess["_csrf_token"] = "test-token"
            sess.permanent = True

    def test_dashboard_requires_login(self, client):
        resp = client.get("/dashboard", follow_redirects=False)
        assert resp.status_code == 302

    def test_dashboard_when_logged_in(self, client):
        self._login_as_admin(client)
        resp = client.get("/dashboard", follow_redirects=False)
        assert resp.status_code in (200, 302)

    def test_new_report_form_requires_login(self, client):
        resp = client.get("/reports/new", follow_redirects=False)
        assert resp.status_code == 302

    def test_new_report_page(self, client):
        self._login_as_admin(client)
        resp = client.get("/reports/new", follow_redirects=False)
        assert resp.status_code in (200, 302)

    def test_export_csv_requires_login(self, client):
        resp = client.get("/reports/export.csv", follow_redirects=False)
        assert resp.status_code == 302

    def test_export_csv(self, client):
        self._login_as_admin(client)
        resp = client.get("/reports/export.csv", follow_redirects=False)
        assert resp.status_code in (200, 302)

    def test_export_csv_escapes_formula_cells(self, client):
        self._login_as_admin(client)
        with app_module.app.app_context():
            app_module.get_db().execute(
                """
                INSERT INTO reports (
                    user_id, report_date, reporting_period, branch, department, position,
                    day_summary, tasks_json, challenges_json, decisions, tomorrow_json,
                    comments, metrics_json, status, archived, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                """,
                (
                    1,
                    "2026-06-09",
                    "08 Jun 2026 - 14 Jun 2026",
                    "Toolkit Africa Main Office",
                    "Management",
                    "System Administrator",
                    "=HYPERLINK(\"https://example.test\")",
                    "[]",
                    "[]",
                    "",
                    "[]",
                    "",
                    "{}",
                    "submitted",
                    0,
                    app_module.now(),
                ),
            )
            app_module.get_db().commit()

        resp = client.get("/reports/export.csv")
        assert resp.status_code == 200
        assert b"\"'=HYPERLINK(\"\"https://example.test\"\")\"" in resp.data

    def test_submit_report_creates_visible_report(self, client):
        self._login_as_admin(client)

        resp = client.post(
            "/reports/new",
            data={
                "_csrf_token": "test-token",
                "report_date": "2026-06-09",
                "reporting_period": "08 Jun 2026 - 14 Jun 2026",
                "branch": "Toolkit Africa Main Office",
                "department": "Management",
                "position": "System Administrator",
                "day_summary": "Reviewed operations and prepared management updates.",
                "task_activity_1": "Prepared daily management report",
                "task_description_1": "Compiled updates for the reporting dashboard.",
                "task_time_1": "1 hour",
                "task_status_1": "Completed",
            },
            follow_redirects=False,
        )

        assert resp.status_code == 302
        with app_module.app.app_context():
            report = app_module.get_db().execute(
                "SELECT * FROM reports WHERE user_id = ?",
                (1,),
            ).fetchone()
        assert report is not None
        assert report["day_summary"] == "Reviewed operations and prepared management updates."

        dashboard = client.get("/dashboard")
        assert b"Reviewed operations and prepared management updates." in dashboard.data

    def test_report_form_session_survives_long_entry_window(self, client):
        self._login_as_admin(client)
        within_timeout = datetime.now() - timedelta(minutes=5)
        with client.session_transaction() as sess:
            sess["_last_active"] = within_timeout.strftime("%Y-%m-%d %H:%M:%S")

        resp = client.post(
            "/reports/new",
            data={
                "_csrf_token": "test-token",
                "report_date": "2026-06-09",
                "reporting_period": "08 Jun 2026 - 14 Jun 2026",
                "branch": "Toolkit Africa Main Office",
                "department": "Management",
                "position": "System Administrator",
                "day_summary": "Submitted after a longer editing session.",
                "task_activity_1": "Completed delayed submission",
            },
            follow_redirects=False,
        )

        assert resp.status_code == 302
        assert "/login" not in resp.headers["Location"]

    def test_dashboard_treats_blank_archived_as_active(self, client):
        self._login_as_admin(client)
        with app_module.app.app_context():
            app_module.get_db().execute(
                """
                INSERT INTO reports (
                    user_id, report_date, reporting_period, branch, department, position,
                    day_summary, tasks_json, challenges_json, decisions, tomorrow_json,
                    comments, metrics_json, status, archived, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                """,
                (
                    1,
                    "2026-06-09",
                    "08 Jun 2026 - 14 Jun 2026",
                    "Toolkit Africa Main Office",
                    "Management",
                    "System Administrator",
                    "Legacy blank archived report",
                    "[]",
                    "[]",
                    "",
                    "[]",
                    "",
                    "{}",
                    "submitted",
                    "",
                    app_module.now(),
                ),
            )
            app_module.get_db().commit()

        dashboard = client.get("/dashboard")
        assert b"Legacy blank archived report" in dashboard.data

    def test_principal_overview_requires_executive_role(self, client):
        self._login_as_admin(client)
        with app_module.app.app_context():
            app_module.get_db().execute("UPDATE users SET role = 'employee' WHERE id = 1")
            app_module.get_db().commit()
        resp = client.get("/principal")
        assert resp.status_code == 403

    def test_principal_overview_for_principal(self, client):
        self._login_as_admin(client)
        with app_module.app.app_context():
            app_module.get_db().execute("UPDATE users SET role = 'principal' WHERE id = 1")
            app_module.get_db().commit()
        resp = client.get("/principal")
        assert resp.status_code == 200
        assert b"Institution executive overview" in resp.data

    def test_admission_record_is_captured_with_pending_history(self, client):
        self._login_as_admin(client)
        resp = client.post(
            "/admissions",
            data={
                "_csrf_token": "test-token",
                "applicant_name": "Amina Example",
                "contact": "+254700000001",
                "course": "Digital Marketing",
                "intake": "September 2026",
                "source": "Website",
                "notes": "Requested a call back.",
            },
            follow_redirects=False,
        )
        assert resp.status_code == 302
        with app_module.app.app_context():
            admission = app_module.get_db().execute("SELECT * FROM admissions").fetchone()
            history = app_module.get_db().execute(
                "SELECT * FROM admission_verification_history WHERE admission_id = ?",
                (admission["id"],),
            ).fetchall()
        assert admission["status"] == "pending"
        assert admission["created_by"] == 1
        assert len(history) == 1
        assert history[0]["status"] == "pending"

    def test_admission_verification_requires_reason_for_rejection(self, client):
        self._login_as_admin(client)
        with app_module.app.app_context():
            cursor = app_module.get_db().execute(
                """INSERT INTO admissions (applicant_name, course, status, created_by, created_at, updated_at)
                   VALUES (?, ?, 'pending', ?, ?, ?)""",
                ("Test Applicant", "Welding", 1, app_module.now(), app_module.now()),
            )
            admission_id = cursor.lastrowid
            app_module.get_db().commit()

        response = client.post(
            f"/admissions/{admission_id}/verify",
            data={"_csrf_token": "test-token", "status": "rejected", "notes": ""},
            follow_redirects=True,
        )
        assert response.status_code == 200
        assert b"note explaining the verification decision" in response.data
        with app_module.app.app_context():
            row = app_module.get_db().execute("SELECT status FROM admissions WHERE id = ?", (admission_id,)).fetchone()
        assert row["status"] == "pending"

    def test_admission_verification_records_decision_and_history(self, client):
        self._login_as_admin(client)
        with app_module.app.app_context():
            cursor = app_module.get_db().execute(
                """INSERT INTO admissions (applicant_name, course, status, created_by, created_at, updated_at)
                   VALUES (?, ?, 'pending', ?, ?, ?)""",
                ("Verified Applicant", "Plumbing", 1, app_module.now(), app_module.now()),
            )
            admission_id = cursor.lastrowid
            app_module.get_db().commit()

        response = client.post(
            f"/admissions/{admission_id}/verify",
            data={"_csrf_token": "test-token", "status": "verified", "notes": "Documents checked by admissions."},
            follow_redirects=False,
        )
        assert response.status_code == 302
        with app_module.app.app_context():
            row = app_module.get_db().execute("SELECT status, notes FROM admissions WHERE id = ?", (admission_id,)).fetchone()
            history = app_module.get_db().execute(
                "SELECT status, reviewer_id, notes FROM admission_verification_history WHERE admission_id = ? ORDER BY id",
                (admission_id,),
            ).fetchall()
        assert row["status"] == "verified"
        assert row["notes"] == "Documents checked by admissions."
        assert history[-1]["status"] == "verified"
        assert history[-1]["reviewer_id"] == 1
