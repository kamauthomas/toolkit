#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TOOLKIT_ROOT="$(cd "${SCRIPT_DIR}/../../.." && pwd)"
LINUX_DOCUMENTS="/home/t316/Documents"
DEFAULT_DESTINATION="/media/t316/16A0C8F7A0C8DE7D/Users/user/Desktop/Toolkit_Work_Archive_2026-08-21"
DESTINATION="${1:-${DEFAULT_DESTINATION}}"

fail() {
    printf 'ERROR: %s\n' "$*" >&2
    exit 1
}

for command in git rsync tar find sort sha256sum; do
    command -v "${command}" >/dev/null 2>&1 || fail "Required command not found: ${command}"
done

[[ -d "${TOOLKIT_ROOT}/wordpress" ]] || fail "Toolkit source root was not resolved correctly."
[[ -d "${LINUX_DOCUMENTS}" ]] || fail "Linux Documents folder is unavailable: ${LINUX_DOCUMENTS}"
[[ -d "$(dirname "${DESTINATION}")" ]] || fail "Destination parent is unavailable: $(dirname "${DESTINATION}")"
[[ -w "$(dirname "${DESTINATION}")" ]] || fail "Destination parent is not writable."
[[ ! -e "${DESTINATION}" ]] || fail "Destination already exists; refusing to merge or overwrite: ${DESTINATION}"

case "$(realpath -m "${DESTINATION}")" in
    "$(realpath "${TOOLKIT_ROOT}")"/*)
        fail "Destination must be outside the Toolkit source tree."
        ;;
esac

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

    mkdir -p "${destination_path}"
    git -C "${source_path}" archive --format=tar HEAD | tar -xf - -C "${destination_path}"
}

copy_tree() {
    local source_path="$1"
    local destination_path="$2"
    shift 2

    mkdir -p "${destination_path}"
    rsync -rt --modify-window=1 "$@" "${source_path}/" "${destination_path}/"
}

# Git archives provide reproducible source snapshots without .git directories,
# untracked credentials, dependency trees, caches, or local agent state.
export_git_snapshot "${TOOLKIT_ROOT}/wordpress" "WordPress_Website"
export_git_snapshot "${TOOLKIT_ROOT}/report-system" "Report_System"
export_git_snapshot "${TOOLKIT_ROOT}/reception-system" "Reception_System"
export_git_snapshot "${TOOLKIT_ROOT}/Chat-bot" "Website_Chatbot"
export_git_snapshot "${TOOLKIT_ROOT}/web-redesign/toolkit-platform" "Web_Redesign_Platform"
export_git_snapshot "${TOOLKIT_ROOT}/SmartLecturer_VirtualCampus" "Smart_Lecturer"

# Virtual Campus is meaningful untracked application work. Copy its source but
# deliberately omit runtime credentials, databases, dependencies, and caches.
copy_tree \
    "${TOOLKIT_ROOT}/SmartLecturer_VirtualCampus/virtual-campus" \
    "${DESTINATION}/01_Projects/Smart_Lecturer_Virtual_Campus" \
    --exclude='.env' \
    --exclude='.env.*' \
    --exclude='.phpunit.result.cache' \
    --exclude='database/database.sqlite' \
    --exclude='node_modules/' \
    --exclude='vendor/' \
    --exclude='storage/framework/cache/' \
    --exclude='storage/framework/sessions/' \
    --exclude='storage/framework/views/' \
    --exclude='storage/logs/'

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
find "${LINUX_DOCUMENTS}" -maxdepth 1 -type f -iname '*.xcf' -exec cp -p -t "${DESTINATION}/04_XCF_Source_Files/Linux_Documents" -- {} +

# Keep reusable admissions tooling, not applicant records or generated letters.
cp -p "${TOOLKIT_ROOT}/sms/calling/Calling Letter 2026 UPDATED.docx" \
    "${DESTINATION}/05_Admissions_Templates_and_Tools/"
cp -p "${TOOLKIT_ROOT}/sms/calling/generate_calling_letters.py" \
    "${DESTINATION}/05_Admissions_Templates_and_Tools/"

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

(
    cd "${DESTINATION}"
    find . -type f ! -path './00_READ_ME/FILES_SHA256.txt' -print0 \
        | sort -z \
        | xargs -0 sha256sum > "00_READ_ME/FILES_SHA256.txt"
)

{
    printf 'Toolkit Windows work archive\n'
    printf 'Created: 2026-08-21\n'
    printf 'Files: %s\n' "$(find "${DESTINATION}" -type f | wc -l)"
    printf 'Directories: %s\n' "$(find "${DESTINATION}" -type d | wc -l)"
    printf 'XCF files from Linux Documents: %s\n' "$(find "${DESTINATION}/04_XCF_Source_Files/Linux_Documents" -type f -iname '*.xcf' | wc -l)"
    printf 'Total size: %s\n' "$(du -sh "${DESTINATION}" | cut -f1)"
} > "${DESTINATION}/00_READ_ME/INVENTORY.txt"

(
    cd "${DESTINATION}"
    sha256sum "00_READ_ME/INVENTORY.txt" >> "00_READ_ME/FILES_SHA256.txt"
)

printf 'Export complete: %s\n' "${DESTINATION}"
