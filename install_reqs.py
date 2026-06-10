import subprocess
import sys

pip_path = "/home/bfyigiln/virtualenv/reports.toolkitafrica.ac.ke/3.11/bin/pip3"
req_path = "/home/bfyigiln/reports.toolkitafrica.ac.ke/requirements.txt"

result = subprocess.run(
    [pip_path, "install", "-r", req_path],
    capture_output=True, text=True
)
print(result.stdout)
if result.stderr:
    print("STDERR:", result.stderr)
print(f"\nExit code: {result.returncode}")
