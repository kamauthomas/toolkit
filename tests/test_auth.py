def _init_csrf(client):
    with client.session_transaction() as sess:
        sess["_csrf_token"] = "test-token"


class TestAuth:
    def test_login_page(self, client):
        resp = client.get("/login")
        assert resp.status_code == 200
        assert b"Sign in" in resp.data

    def test_register_page(self, client):
        resp = client.get("/register")
        assert resp.status_code == 200
        assert b"Create employee account" in resp.data

    def test_register_and_login_flow(self, client):
        _init_csrf(client)
        resp = client.post("/register", data={
            "_csrf_token": "test-token",
            "full_name": "Test User",
            "email": "test@example.com",
            "phone": "+254700000000",
            "department": "ICT",
            "position": "Developer",
            "branch": "Toolkit Africa Main Office",
            "password": "password123",
        }, follow_redirects=True)
        assert resp.status_code == 200
        assert b"Account created" in resp.data

        _init_csrf(client)
        resp = client.post("/login", data={
            "_csrf_token": "test-token",
            "email": "test@example.com",
            "password": "password123",
        }, follow_redirects=False)
        assert resp.status_code == 302

    def test_login_with_wrong_password(self, client):
        _init_csrf(client)
        resp = client.post("/login", data={
            "_csrf_token": "test-token",
            "email": "admin@toolkit.local",
            "password": "wrongpassword",
        }, follow_redirects=True)
        assert resp.status_code == 200
        assert b"Invalid email or password" in resp.data

    def test_logout(self, client):
        resp = client.get("/logout", follow_redirects=True)
        assert resp.status_code == 200

    def test_change_password_page_requires_login(self, client):
        resp = client.get("/change-password", follow_redirects=False)
        assert resp.status_code == 302
        assert "/login" in resp.location

    def test_protected_route_redirects(self, client):
        resp = client.get("/dashboard", follow_redirects=False)
        assert resp.status_code == 302
        assert "/login" in resp.location