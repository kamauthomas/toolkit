import os
from pathlib import Path

BASE = Path(__file__).resolve().parent
os.chdir(str(BASE))

from app import app


class ScriptNameMiddleware:
    def __init__(self, application):
        self.application = application

    def __call__(self, environ, start_response):
        script = environ.get("SCRIPT_NAME", "")
        if script:
            environ["PATH_INFO"] = environ["PATH_INFO"].removeprefix(script)
        return self.application(environ, start_response)


application = ScriptNameMiddleware(app)
