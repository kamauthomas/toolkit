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
        within_timeout = datetime.now() - timedelta(hours=1)
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
