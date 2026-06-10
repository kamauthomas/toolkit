import os
import tempfile
from pathlib import Path

import pytest

os.environ["SECRET_KEY"] = "test-secret-key-not-for-prod"
os.environ["ENV"] = "test"

import app as app_module
from app import app as _app


@pytest.fixture(autouse=True)
def app():
    db_fd, db_path = tempfile.mkstemp()
    db_path_obj = Path(db_path)

    app_module.DB_PATH = db_path_obj
    app_module._rate_limit_store.clear()
    app_module._login_attempts_cache.clear()

    _app.config.update({
        "TESTING": True,
        "WTF_CSRF_ENABLED": False,
        "SERVER_NAME": "localhost",
    })

    with _app.app_context():
        app_module.init_db()

    yield _app

    os.close(db_fd)
    db_path_obj.unlink(missing_ok=True)


@pytest.fixture
def client(app):
    return app.test_client()


@pytest.fixture
def auth_headers():
    return {"X-CSRF-Token": "test"}