class TestSecurityHeaders:
    def test_security_headers_present(self, client):
        resp = client.get("/login")
        assert resp.headers.get("X-Content-Type-Options") == "nosniff"
        assert resp.headers.get("X-Frame-Options") == "DENY"
        assert resp.headers.get("Referrer-Policy") == "strict-origin-when-cross-origin"

    def test_rate_limiting(self, client):
        with client.session_transaction() as sess:
            sess["_csrf_token"] = "test-token"
        for _ in range(12):
            client.post("/login", data={
                "_csrf_token": "test-token",
                "email": "test@example.com",
                "password": "wrong",
            })
        resp = client.post("/login", data={
            "_csrf_token": "test-token",
            "email": "test@example.com",
            "password": "wrong",
        }, follow_redirects=False)
        assert resp.status_code == 302


class TestAllowedHosts:
    def test_production_rejects_main_website_host(self, app, monkeypatch):
        monkeypatch.setenv("ENV", "production")
        with app.test_client() as client:
            resp = client.get("/login", base_url="https://toolkitafrica.ac.ke")
        assert resp.status_code == 421
        assert b"Toolkit Staff Reports" not in resp.data

    def test_production_allows_report_subdomain(self, app, monkeypatch):
        monkeypatch.setenv("ENV", "production")
        with app.test_client() as client:
            resp = client.get("/login", base_url="https://reports.toolkitafrica.ac.ke")
        assert resp.status_code == 200


class TestHealthCheck:
    def test_health_endpoint(self, client):
        resp = client.get("/health")
        assert resp.status_code in (200, 503)
        data = resp.get_json()
        assert "status" in data
        assert "database" in data


class TestDraftCsrf:
    def _login(self, client):
        with client.session_transaction() as sess:
            sess["user_id"] = 1
            sess["_csrf_token"] = "test-token"
            sess.permanent = True

    def test_draft_save_requires_csrf(self, client):
        self._login(client)
        resp = client.post("/api/reports/draft", json={"report_date": "2026-06-09"})
        assert resp.status_code == 400

    def test_draft_save_accepts_csrf_header(self, client):
        self._login(client)
        resp = client.post(
            "/api/reports/draft",
            json={"report_date": "2026-06-09", "department": "Management"},
            headers={"X-CSRF-Token": "test-token"},
        )
        assert resp.status_code == 200
        assert resp.get_json()["ok"] is True
