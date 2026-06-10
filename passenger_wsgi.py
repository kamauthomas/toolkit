import sys
import os
import traceback
from pathlib import Path

BASE = Path(__file__).resolve().parent
sys.path.insert(0, str(BASE))
os.chdir(str(BASE))

try:
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

except Exception:
    log = BASE / "logs" / "passenger_error.log"
    log.parent.mkdir(exist_ok=True)
    log.write_text(traceback.format_exc())
    raise