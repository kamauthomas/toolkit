# Toolkit Report Submission System

Local-first employee report submission system for Toolkit Africa.

## Features

- Employee account registration and login.
- Daily report submissions with guided fields for non-technical users.
- PDF generation for each submitted report.
- Admin dashboard for assigned report visibility.
- Super admin account for user and report-access management.
- SQLite storage for local development.

## Local Development

```bash
cd /home/t316/Desktop/Projects_father/toolkit/report-system
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
python app.py
```

Open:

```text
http://127.0.0.1:5055
```

## Default Local Admin

The app creates a local super admin if no admin exists:

```text
Email: admin@toolkit.local
Password: ChangeMe123!
```

Override with:

```bash
export ADMIN_EMAIL="your-admin@example.com"
export ADMIN_PASSWORD="strong-password"
```

Change the default credentials before exposing this system beyond local development.

## Data Locations

- SQLite database: `instance/toolkit_reports.sqlite3`
- Generated PDFs: `generated_reports/`
- Toolkit logo: `static/toolkit-logo.png`

## Roles

- `employee`: can create and view their own reports.
- `admin`: can view only reports for employees explicitly assigned by a super admin.
- `superadmin`: can view all reports and manage users/report access.
