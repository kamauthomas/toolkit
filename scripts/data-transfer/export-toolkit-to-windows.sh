#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TOOLKIT_ROOT="${TOOLKIT_SYNC_SOURCE_ROOT:-$(cd "${SCRIPT_DIR}/../../.." && pwd)}"
LINUX_DOCUMENTS="${TOOLKIT_SYNC_DOCUMENTS:-/home/t316/Documents}"
WINDOWS_DESKTOP="/media/t316/16A0C8F7A0C8DE7D/Users/user/Desktop"
DEFAULT_DESTINATION="${WINDOWS_DESKTOP}/Toolkit_Workspace"
LEGACY_DESTINATION="${WINDOWS_DESKTOP}/Toolkit_Work_Archive_2026-08-21"
PREPARE_ONLY=false
if [[ "${1:-}" == "--prepare-only" ]]; then
    [[ -n "${2:-}" && "$#" == 2 ]] || { printf 'Usage: %s --prepare-only OUTPUT_DIRECTORY\n' "$0" >&2; exit 2; }
    PREPARE_ONLY=true
    FINAL_DESTINATION="$2"
else
    [[ "$#" -le 1 ]] || { printf 'Usage: %s [WINDOWS_WORKSPACE_DIRECTORY]\n' "$0" >&2; exit 2; }
    FINAL_DESTINATION="${1:-${DEFAULT_DESTINATION}}"
fi
DESTINATION="${FINAL_DESTINATION}"
STAGING_ROOT=""

# Apply these to every source, including tracked snapshots. A Git archive alone
# does not protect us from a mistakenly tracked credential or runtime file.
COMMON_EXCLUDES=(
    --exclude='.env' --exclude='.env.*' --exclude='wp-config.php'
    --exclude='.DS_Store' --exclude='.git/' --exclude='.agents/' --exclude='.codex/' --exclude='.aws/' --exclude='.ssh/'
    --exclude='.superpowers/' --exclude='.tools/' --exclude='.venv/' --exclude='venv/'
    --exclude='node_modules/' --exclude='vendor/' --exclude='__pycache__/'
    --exclude='.pytest_cache/' --exclude='.phpunit.cache/' --exclude='.phpunit.result.cache'
    --exclude='**/storage/***' --exclude='**/bootstrap/cache/***'
    --exclude='private/' --exclude='.private/' --exclude='.toolkit-deploy/'
    --exclude='rollbacks/' --exclude='backups/' --exclude='report-preparation-*/'
    --exclude='*.log' --exclude='*.pyc' --exclude='*.bak' --exclude='*.swp' --exclude='*.swo' --exclude='*.kate-swp' --exclude='*~'
    --exclude='*.sql' --exclude='*.sql.gz' --exclude='*.dump' --exclude='*.db'
    --exclude='*.sqlite*' --exclude='*.db-shm' --exclude='*.db-wal' --exclude='*.pem' --exclude='*.key' --exclude='*.p12' --exclude='*.pfx'
    --exclude='credentials*' --exclude='secrets*' --exclude='auth.json' --exclude='id_rsa' --exclude='id_ed25519' --exclude='.~lock.*'
    --exclude='*Mzizi Applicants*' --exclude='*Calling Letters - 20*'
    --exclude='*Excluded - Non-Kenyan*' --exclude='*PDF.zip'
)

fail() {
    printf 'ERROR: %s\n' "$*" >&2
    exit 1
}

for command in cp date git mktemp mv rg rm rsync tar find findmnt realpath sort sha256sum; do
    command -v "${command}" >/dev/null 2>&1 || fail "Required command not found: ${command}"
done

[[ -d "${TOOLKIT_ROOT}/wordpress" ]] || fail "Toolkit source root was not resolved correctly."
[[ -d "${LINUX_DOCUMENTS}" ]] || fail "Linux Documents folder is unavailable: ${LINUX_DOCUMENTS}"
if [[ "${PREPARE_ONLY}" == false ]]; then
    [[ -d "$(dirname "${DESTINATION}")" ]] || fail "Destination parent is unavailable: $(dirname "${DESTINATION}")"
    findmnt -T "$(dirname "${DESTINATION}")" -t ntfs,ntfs3,exfat,vfat,fuseblk -no OPTIONS | rg -q '(^|,)rw(,|$)' \
        || fail "Destination is not on a mounted read/write Windows volume. Mount it normally first."
    [[ -w "$(dirname "${DESTINATION}")" ]] || fail "Destination parent is not writable."
fi
[[ "$(basename "${DESTINATION}")" == Toolkit_Workspace || "${PREPARE_ONLY}" == true ]] \
    || fail "Use the managed Toolkit_Workspace directory, not an unrelated Desktop folder."

[[ ! -L "${FINAL_DESTINATION}" ]] || fail "Destination must not be a symlink."
case "$(realpath -m "${DESTINATION}")" in
    "$(realpath "${TOOLKIT_ROOT}")"/toolkit-private-storage/windows-sync-preview/*)
        [[ "${PREPARE_ONLY}" == true ]] || fail "Local preview paths require --prepare-only."
        ;;
    "$(realpath "${TOOLKIT_ROOT}")"|"$(realpath "${TOOLKIT_ROOT}")"/*)
        fail "Destination must be outside source folders (or the dedicated private preview directory)."
        ;;
esac
for folder in 00_READ_ME 01_Projects 02_Reports_and_Planning 03_Design_Posters_and_Media 04_XCF_Source_Files 05_Admissions_Templates_and_Tools 06_Reference_Material; do
    [[ ! -L "${FINAL_DESTINATION}/${folder}" ]] || fail "Managed destination folders must not be symlinks."
done

# Stage outside every copied source, avoiding both public files and large /tmp
# memory use. Validate the entire handoff before writing to a Windows volume.
STAGING_PARENT="${TOOLKIT_ROOT}/toolkit-private-storage/windows-sync-staging"
mkdir -p "${STAGING_PARENT}"
chmod 700 "${STAGING_PARENT}"
STAGING_ROOT="$(mktemp -d "${STAGING_PARENT}/sync.XXXXXX")"
DESTINATION="${STAGING_ROOT}/Toolkit_Workspace"
cleanup() {
    [[ -n "${STAGING_ROOT}" && -d "${STAGING_ROOT}" ]] && rm -rf -- "${STAGING_ROOT}"
}
trap cleanup EXIT

mkdir -p \
    "${DESTINATION}/00_READ_ME" \
    "${DESTINATION}/01_Projects" \
    "${DESTINATION}/02_Reports_and_Planning" \
    "${DESTINATION}/03_Design_Posters_and_Media" \
    "${DESTINATION}/04_XCF_Source_Files/Linux_Documents" \
    "${DESTINATION}/05_Admissions_Templates_and_Tools" \
    "${DESTINATION}/06_Reference_Material"

export_git_snapshot() {
    local source_path="$1"
    local destination_name="$2"
    local destination_path="${DESTINATION}/01_Projects/${destination_name}"
    local staging_path="${STAGING_ROOT}/projects/${destination_name}"

    mkdir -p "${staging_path}"
    git -C "${source_path}" archive --format=tar HEAD | tar -xf - -C "${staging_path}"
    # Only tracked setup examples are eligible for the .env.example exception.
    copy_tree "${staging_path}" "${destination_path}" --include='.env.example'
}

copy_tree() {
    local source_path="$1"
    local destination_path="$2"
    shift 2

    mkdir -p "${destination_path}"
    rsync -rt --delete --modify-window=1 "$@" "${COMMON_EXCLUDES[@]}" "${source_path}/" "${destination_path}/"
}

# Git archives provide reproducible source snapshots without .git directories,
# untracked credentials, dependency trees, caches, or local agent state.
export_git_snapshot "${TOOLKIT_ROOT}/wordpress" "WordPress_Website"
export_git_snapshot "${TOOLKIT_ROOT}/report-system" "Report_System"
export_git_snapshot "${TOOLKIT_ROOT}/reception-system" "Reception_System"
export_git_snapshot "${TOOLKIT_ROOT}/Chat-bot" "Website_Chatbot"
export_git_snapshot "${TOOLKIT_ROOT}/web-redesign/toolkit-platform" "Web_Redesign_Platform"
export_git_snapshot "${TOOLKIT_ROOT}/SmartLecturer_VirtualCampus" "Smart_Lecturer"

# Campus is now tracked. Export the same recorded commit as Smart Lecturer;
# copying its working tree would include private deployment/local-review state.
mkdir -p "${STAGING_ROOT}/campus"
git -C "${TOOLKIT_ROOT}/SmartLecturer_VirtualCampus" archive --format=tar HEAD:virtual-campus \
    | tar -xf - -C "${STAGING_ROOT}/campus"
copy_tree "${STAGING_ROOT}/campus" "${DESTINATION}/01_Projects/Smart_Lecturer_Virtual_Campus" --include='.env.example'

# Institutional reports and planning records are kept separately from source.
copy_tree \
    "${TOOLKIT_ROOT}/wordpress/reports" \
    "${DESTINATION}/02_Reports_and_Planning/Website_Reports" \
    --exclude='.~lock.*' \
    --exclude='*.lock'
copy_tree \
    "${TOOLKIT_ROOT}/report-system/generated_reports" \
    "${DESTINATION}/02_Reports_and_Planning/Report_System_Exports" \
    --exclude='.~lock.*' \
    --exclude='*.lock'
# Retain final management/daily documents, not private Wingu/attendance receipts.
for name in ICT-WORK-REPORT DAILY-REPORT-DRAFTS; do
    for extension in md docx pdf; do
        final_report="${TOOLKIT_ROOT}/wordpress/reports/report-preparation-2026-10-23/${name}.${extension}"
        if [[ -f "${final_report}" ]]; then
            mkdir -p "${DESTINATION}/02_Reports_and_Planning/Website_Reports/Prepared_Management_Reports"
            cp -p "${final_report}" "${DESTINATION}/02_Reports_and_Planning/Website_Reports/Prepared_Management_Reports/"
        fi
    done
done
copy_tree "${TOOLKIT_ROOT}/SmartLecturer_VirtualCampus/docs/reporting" "${DESTINATION}/02_Reports_and_Planning/Campus_Progress_Reports"
copy_tree "${TOOLKIT_ROOT}/documents" "${DESTINATION}/02_Reports_and_Planning/Toolkit_Documents"
copy_tree "${TOOLKIT_ROOT}/docs" "${DESTINATION}/02_Reports_and_Planning/Toolkit_Technical_Documents"
copy_tree "${TOOLKIT_ROOT}/strategic-ict-roadmap" "${DESTINATION}/02_Reports_and_Planning/Strategic_ICT_Roadmap"
cp -p "${TOOLKIT_ROOT}/TOOLKIT_90_DAY_EXECUTION_PLAN.md" "${DESTINATION}/02_Reports_and_Planning/"

# Preserve original design sources, exports, posters, photos, and prospectus work.
copy_tree "${TOOLKIT_ROOT}/imgs" "${DESTINATION}/03_Design_Posters_and_Media/Images_and_Posters" --exclude='.~lock.*'
copy_tree \
    "${TOOLKIT_ROOT}/graphics" \
    "${DESTINATION}/03_Design_Posters_and_Media/Graphics_Workspace" \
    --exclude='.env' \
    --exclude='.env.*' \
    --exclude='node_modules/' \
    --exclude='.~lock.*'
copy_tree "${TOOLKIT_ROOT}/prospectus" "${DESTINATION}/03_Design_Posters_and_Media/Prospectus" --exclude='.~lock.*'
cp -p "${TOOLKIT_ROOT}/Poster Social Media 2025 Comms (1080 x 1080 px) (727 x 911 px).pdf" \
    "${DESTINATION}/03_Design_Posters_and_Media/"

# These are the editable XCF files requested from the Linux Documents folder.
rsync -rt --delete --modify-window=1 --include='*.xcf' --include='*.XCF' --exclude='*' \
    "${LINUX_DOCUMENTS}/" "${DESTINATION}/04_XCF_Source_Files/Linux_Documents/"

# Keep reusable admissions tooling, not applicant records or generated letters.
mkdir -p "${STAGING_ROOT}/admissions"
cp -p "${TOOLKIT_ROOT}/sms/calling/Calling Letter 2026 UPDATED.docx" "${STAGING_ROOT}/admissions/"
cp -p "${TOOLKIT_ROOT}/sms/calling/generate_calling_letters.py" "${STAGING_ROOT}/admissions/"
copy_tree "${STAGING_ROOT}/admissions" "${DESTINATION}/05_Admissions_Templates_and_Tools"

# Research/reference material is useful context but is not a deployable project.
copy_tree \
    "${TOOLKIT_ROOT}/web-redesign" \
    "${DESTINATION}/06_Reference_Material/Web_Redesign_Research" \
    --exclude='toolkit-platform/' \
    --exclude='wget_crawl.log' \
    --exclude='.env' \
    --exclude='.env.*'
copy_tree "${TOOLKIT_ROOT}/redesign" "${DESTINATION}/06_Reference_Material/Redesign_Notes"
copy_tree \
    "${TOOLKIT_ROOT}/plugins" \
    "${DESTINATION}/06_Reference_Material/WordPress_Plugin_Reference" \
    --exclude='.env' \
    --exclude='.env.*'

cp -p "${TOOLKIT_ROOT}/wordpress/docs/WINDOWS-WORK-TRANSFER.md" "${DESTINATION}/00_READ_ME/README.md"
cp -p "${TOOLKIT_ROOT}/report-system/REPORT-SYSTEM-QUICK-GUIDE.md" \
    "${DESTINATION}/02_Reports_and_Planning/REPORT-SYSTEM-QUICK-GUIDE.md"

if find "${DESTINATION}" -type f | rg -qi \
    '(^|/)(\.env($|\.(local|production|development)$)|wp-config\.php$|.*\.(sql|sqlite|sqlite3|pem|key|p12|pfx)$|credentials?[^/]*|secrets?[^/]*)(/|$)'; then
    fail "A prohibited secret/database path was found in the synchronized workspace."
fi

if find "${DESTINATION}" -type f | rg -qi \
    '(Mzizi Applicants|Calling Letters - 20|Excluded - Non-Kenyan|PDF\.zip)'; then
    fail "Applicant data or generated calling-letter output was found in the synchronized workspace."
fi

{
    printf 'Source\tBranch\tCommit\n'
    for project in \
        wordpress \
        report-system \
        reception-system \
        Chat-bot \
        web-redesign/toolkit-platform \
        SmartLecturer_VirtualCampus; do
        printf '%s\t%s\t%s\n' \
            "${project}" \
            "$(git -C "${TOOLKIT_ROOT}/${project}" branch --show-current)" \
            "$(git -C "${TOOLKIT_ROOT}/${project}" rev-parse HEAD)"
    done
} > "${DESTINATION}/00_READ_ME/SOURCE_COMMITS.tsv"

if [[ -f "${FINAL_DESTINATION}/00_READ_ME/SYNC_HISTORY.tsv" ]]; then
    cp -p "${FINAL_DESTINATION}/00_READ_ME/SYNC_HISTORY.tsv" "${DESTINATION}/00_READ_ME/SYNC_HISTORY.tsv"
fi
if [[ ! -f "${DESTINATION}/00_READ_ME/SYNC_HISTORY.tsv" ]]; then
    printf 'Synchronized_at\tWordPress_commit\tReport_System_commit\n' \
        > "${DESTINATION}/00_READ_ME/SYNC_HISTORY.tsv"
fi
if [[ "${PREPARE_ONLY}" == false ]]; then
printf '%s\t%s\t%s\n' \
    "$(date --iso-8601=seconds)" \
    "$(git -C "${TOOLKIT_ROOT}/wordpress" rev-parse HEAD)" \
    "$(git -C "${TOOLKIT_ROOT}/report-system" rev-parse HEAD)" \
    >> "${DESTINATION}/00_READ_ME/SYNC_HISTORY.tsv"
else
    printf 'Prepared_at\tStatus\n%s\tLocal handoff only; not synchronized to Windows\n' "$(date --iso-8601=seconds)" > "${DESTINATION}/00_READ_ME/PREPARATION.tsv"
fi

(
    cd "${DESTINATION}"
    find . -type f \
        ! -path './00_READ_ME/FILES_SHA256.txt' \
        ! -path './00_READ_ME/INVENTORY.txt' \
        -print0 \
        | sort -z \
        | xargs -0 sha256sum > "00_READ_ME/FILES_SHA256.txt"
)

{
    printf 'Toolkit Windows workspace mirror\n'
    printf 'Prepared: %s\n' "$(date --iso-8601=seconds)"
    printf 'Windows synchronization performed: %s\n' "$([[ ${PREPARE_ONLY} == false ]] && printf yes || printf no)"
    printf 'Files: %s\n' "$(find "${DESTINATION}" -type f | wc -l)"
    printf 'Directories: %s\n' "$(find "${DESTINATION}" -type d | wc -l)"
    printf 'XCF files from Linux Documents: %s\n' "$(find "${DESTINATION}/04_XCF_Source_Files/Linux_Documents" -type f -iname '*.xcf' | wc -l)"
    printf 'Total size: %s\n' "$(du -sh "${DESTINATION}" | cut -f1)"
} > "${DESTINATION}/00_READ_ME/INVENTORY.txt"

(
    cd "${DESTINATION}"
    sha256sum "00_READ_ME/INVENTORY.txt" >> "00_READ_ME/FILES_SHA256.txt"
)

# Detect private-key/token signatures without printing any matching content.
scan_status=0
rg -l --hidden --no-ignore -- '-----BEGIN (RSA |DSA |EC |OPENSSH )?PRIVATE KEY-----|AKIA[0-9A-Z]{16}|ghp_[A-Za-z0-9]{36}' "${DESTINATION}" >/dev/null 2>&1 || scan_status=$?
[[ "$scan_status" -ne 0 ]] || fail "A private-key/token signature was found in staging; Windows was not changed."
[[ "$scan_status" -eq 1 ]] || fail "The staging content scan failed; Windows was not changed."

if [[ "${PREPARE_ONLY}" == false && "${FINAL_DESTINATION}" == "${DEFAULT_DESTINATION}" && ! -e "${FINAL_DESTINATION}" && -d "${LEGACY_DESTINATION}" ]]; then
    mv -- "${LEGACY_DESTINATION}" "${FINAL_DESTINATION}"
fi
mkdir -p "${FINAL_DESTINATION}"
for folder in 00_READ_ME 01_Projects 02_Reports_and_Planning 03_Design_Posters_and_Media 04_XCF_Source_Files 05_Admissions_Templates_and_Tools 06_Reference_Material; do
    mkdir -p "${FINAL_DESTINATION}/${folder}"
    rsync -rt --delete --modify-window=1 "${DESTINATION}/${folder}/" "${FINAL_DESTINATION}/${folder}/"
done
(
    cd "${FINAL_DESTINATION}"
    sha256sum --check --quiet 00_READ_ME/FILES_SHA256.txt
)
if [[ "${PREPARE_ONLY}" == true ]]; then
    printf 'Prepared and checksum-verified locally; Windows was not changed: %s\n' "${FINAL_DESTINATION}"
else
    printf 'Synchronization complete and checksums verified: %s\n' "${FINAL_DESTINATION}"
fi
