#!/usr/bin/env python3
"""Reconcile supplied attendance with dated work evidence; prepare only, never submit."""
import argparse
import csv
import datetime as dt
import hashlib
import json
import random
from collections import Counter, defaultdict
from pathlib import Path
from zoneinfo import ZoneInfo

ROOT = Path(__file__).resolve().parents[2]
ZONE = ZoneInfo('Africa/Nairobi')
HOLIDAYS = {'2026-10-20': 'Mashujaa Day'}


def write_csv(path, rows, fields=None):
    if not rows:
        return
    with path.open('w', newline='', encoding='utf-8') as stream:
        writer = csv.DictWriter(stream, fieldnames=fields or list(rows[0]))
        writer.writeheader()
        writer.writerows(rows)


def digest(path):
    value = hashlib.sha256()
    with path.open('rb') as stream:
        for block in iter(lambda: stream.read(1024 * 1024), b''):
            value.update(block)
    return value.hexdigest()


def prepare(start, out):
    out.mkdir(parents=True, exist_ok=True, mode=0o700)
    snapshot_path = out / 'WINGU-LIVE-RECONCILIATION.private.json'
    snapshot = json.loads(snapshot_path.read_text()) if snapshot_path.exists() else None
    live_rows = {r['date']: r for r in snapshot['rows']} if snapshot else {}
    if snapshot:
        verified_start = dt.date.fromisoformat(snapshot['new_report_start'])
        if start is not None and start != verified_start:
            raise ValueError('Requested start differs from the verified saved-row boundary')
        start = verified_start
    else:
        start = start or dt.date(2026, 9, 22)
    source = ROOT / 'reports/logs/Thomas 1 Sep - 23 Oct 2026.csv'
    work = json.loads((ROOT / 'docs/reporting/WORK-EVIDENCE-2026-09-22-TO-2026-10-23.json').read_text())
    entries = work['entries']
    events = defaultdict(list)
    source_rows = list(csv.DictReader(source.open(encoding='utf-8-sig')))
    for number, row in enumerate(source_rows, 2):
        assert row['Name'].strip().casefold() == 'thomas kamau', 'Unexpected employee in attendance export'
        timestamp = dt.datetime.fromisoformat(row['Time'])
        status = row['Attendance Status']
        assert status in {'Check-in', 'Check-out'}
        events[timestamp.date()].append((timestamp.time(), status, number))
    end = max(events)
    assert start <= end
    ledger_path = out / 'AUTHORIZED-ESTIMATES.json'
    ledger = json.loads(ledger_path.read_text()) if ledger_path.exists() else {}
    rng = random.Random(20261023)
    now = dt.datetime.now(ZONE)
    rows = []
    for offset in range((end - start).days + 1):
        day = start + dt.timedelta(days=offset)
        key = day.isoformat()
        raw = sorted(events.get(day, []))
        ins = [time for time, status, _ in raw if status == 'Check-in']
        outs = [time for time, status, _ in raw if status == 'Check-out']
        checkin = min(ins) if ins else None
        valid_outs = [time for time in outs if checkin is None or time > checkin]
        checkout = max(valid_outs) if valid_outs else None
        flags = []
        if len(ins) > 1:
            flags.append('multiple check-ins; earliest retained')
        if len(outs) > 1 or len(valid_outs) < len(outs):
            flags.append('extra/earlier checkout retained in source; latest valid checkout used')
        held = ''
        live = live_rows.get(key, {})
        if day.weekday() >= 5 and not raw:
            held = 'weekend work/time approval required'
        if key in HOLIDAYS and not raw:
            held = 'public holiday; work/time approval required'
        if live.get('day_label') == 'Annual Leave':
            held = 'Annual Leave recorded in Wingu; leave unchanged'
            if raw:
                flags.append('attendance conflicts with Wingu leave; employee review required')
        if live.get('start') or live.get('end') or live.get('notes_length') or live.get('status'):
            held = 'existing saved Wingu row; do not overwrite'
        start_basis = 'recorded' if checkin else ''
        end_basis = 'recorded' if checkout else ''
        if held:
            ledger.pop(key, None)
        chosen = ledger.setdefault(key, {}) if not held else {}
        if not held:
            if checkin is None:
                chosen.setdefault('start', f'08:{rng.randint(0, 15):02}:00')
                checkin = dt.time.fromisoformat(chosen['start'])
                start_basis = 'employee-authorized estimate'
            if checkout is None:
                chosen.setdefault('end', f'17:{rng.randint(12, 30):02}:00')
                checkout = dt.time.fromisoformat(chosen['end'])
                end_basis = 'employee-authorized estimate'
        if checkin and checkout and checkout <= checkin:
            flags.append('nonpositive interval; held for review')
            held = 'attendance interval review required'
        provisional = bool(checkout and end_basis != 'recorded' and day == now.date() and dt.datetime.combine(day, checkout, ZONE) > now)
        if provisional:
            flags.append('current-day estimated checkout is still in the future; do not file as completed')
        evidence = entries.get(key)
        note = '\n'.join('• ' + x for x in evidence['tasks']) if evidence else ''
        if not evidence and not held:
            flags.append('specific work description requires employee confirmation')
        if raw and not held and not evidence:
            note_status = 'attendance supported; task detail unverified'
        elif evidence:
            note_status = evidence['basis']
        else:
            note_status = 'no dated work evidence'
        elapsed = ''
        if checkin and checkout and not held:
            seconds = int((dt.datetime.combine(day, checkout) - dt.datetime.combine(day, checkin)).total_seconds())
            elapsed = f'{seconds//3600:02}:{seconds//60%60:02}:{seconds%60:02}'
        rows.append({
            'date': key, 'day': day.strftime('%A'), 'start_time': checkin.isoformat() if checkin else '',
            'end_time': checkout.isoformat() if checkout else '', 'start_basis': start_basis, 'end_basis': end_basis,
            'wingu_start_12h': checkin.strftime('%I:%M %p').lstrip('0') if checkin else '',
            'wingu_end_12h': checkout.strftime('%I:%M %p').lstrip('0') if checkout else '',
            'elapsed_hhmmss': elapsed, 'raw_record_count': len(raw), 'work_note': note,
            'work_evidence_status': note_status, 'delivery_state': evidence['state'] if evidence else '',
            'source_references': ' | '.join(evidence['sources']) if evidence else '',
            'review_flags': '; '.join(flags), 'held_reason': held,
            'current_day_provisional': 'yes' if provisional else 'no',
            'wingu_saved_status': (live.get('status') or live.get('day_label') or 'verified blank') if live else 'not inspected',
            'submission_authorized': 'no',
            'ready_to_submit': 'no; preparation only',
        })
    ledger_path.write_text(json.dumps(ledger, indent=2) + '\n')
    # Preserve every original timestamp/status, while excluding personal IDs and device information.
    observations = [{'date': day.isoformat(), 'time': time.isoformat(), 'status': status, 'source_row': number} for day in sorted(events) for time, status, number in sorted(events[day])]
    write_csv(out / 'ATTENDANCE-OBSERVATIONS.csv', observations)
    write_csv(out / 'WINGU-DRAFT.csv', rows)
    hashes = []
    for file in [source, ROOT / 'reports/logs/Thomas_recent.csv']:
        hashes.append({'path': str(file), 'sha256': digest(file)})
    # Record work sources, never raw student names, contact data or secret configuration.
    sources = {}
    for entry_date, entry in entries.items():
        for name in entry['sources']:
            path = Path(name) if Path(name).is_absolute() else ROOT / name
            assert path.is_file(), name
            if name not in sources:
                sources[name] = {'ref': 'E' + str(len(sources) + 1).zfill(3), 'path': str(path.resolve()), 'sha256': digest(path), 'basis': entry['basis'], 'dates': []}
            sources[name]['dates'].append(entry_date)
    write_csv(out / 'WORK-EVIDENCE-REGISTER.csv', [{'ref': r['ref'], 'path': r['path'], 'sha256': r['sha256'], 'dates': ';'.join(r['dates']), 'basis': r['basis']} for r in sources.values()])
    last_events = events[end]
    summary = {
        'prepared_by': 'Thomas', 'prepared_at_nairobi': now.isoformat(),
        'report_start': start.isoformat(), 'report_start_verified': snapshot is not None,
        'attendance_cutoff': end.isoformat(),
        'latest_actual_timestamp': end.isoformat() + ' ' + max(t for t, _, _ in last_events).isoformat(),
        'latest_actual_status': next(s for t, s, _ in sorted(last_events, reverse=True)),
        'source_observations': len(source_rows), 'calendar_rows': len(rows),
        'recorded_start_fields': sum(r['start_basis'] == 'recorded' for r in rows),
        'recorded_end_fields': sum(r['end_basis'] == 'recorded' for r in rows),
        'estimated_start_fields': sum(r['start_basis'] == 'employee-authorized estimate' for r in rows),
        'estimated_end_fields': sum(r['end_basis'] == 'employee-authorized estimate' for r in rows),
        'held_calendar_days': sum(bool(r['held_reason']) for r in rows),
        'evidenced_work_dates': sum(bool(r['work_note']) for r in rows),
        'missing_task_detail_dates': [r['date'] for r in rows if not r['work_note'] and not r['held_reason']],
        'provisional_dates': [r['date'] for r in rows if r['current_day_provisional'] == 'yes'],
        'live_wingu_boundary': (f"Approved through {snapshot['last_saved_work_date']}; later inspected working rows blank. Read-only check {snapshot['observed_at_utc']}.") if snapshot else 'pending employee sign-in and read-only saved-row inspection',
        'annual_leave_dates': [r['date'] for r in rows if 'Annual Leave' in r['held_reason']],
        'live_snapshot_sha256': digest(snapshot_path) if snapshot else None,
        'time_policy': 'Preserve recorded seconds. Use saved random choices only for missing working-day endpoints: 08:00–08:15, 17:12–17:30; estimates explicitly labelled. Existing saved rows, Annual Leave and weekends/holiday without punches held.',
        'holiday_source': 'https://www.klrc.go.ke/index.php/constitution-of-kenya/108-chapter-two-the-republic/175-9-national-symbols-and-national-days',
        'work_sources': len(sources), 'attendance_source_hashes': hashes, 'submitted_to_wingu': False,
    }
    (out / 'RECONCILIATION.json').write_text(json.dumps(summary, indent=2, ensure_ascii=False) + '\n')
    # Check calculations and limits against original observed fields, not implementation copies.
    for r in rows:
        day = dt.date.fromisoformat(r['date'])
        raw = events.get(day, [])
        if r['start_basis'] == 'recorded':
            assert r['start_time'] == min(t for t, s, _ in raw if s == 'Check-in').isoformat()
        if r['end_basis'] == 'recorded':
            assert r['end_time'] in [t.isoformat() for t, s, _ in raw if s == 'Check-out']
        for field, lo, hi in [('start', '08:00:00', '08:15:00'), ('end', '17:12:00', '17:30:00')]:
            if r[field + '_basis'] == 'employee-authorized estimate':
                assert lo <= r[field + '_time'] <= hi
        if r['held_reason']:
            assert not r['elapsed_hhmmss']
    print(json.dumps(summary, indent=2, ensure_ascii=False))


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--start', type=dt.date.fromisoformat, help='Must match verified live boundary when available')
    parser.add_argument('--output', type=Path, default=ROOT / 'reports/report-preparation-2026-10-23')
    args = parser.parse_args()
    prepare(args.start, args.output)
