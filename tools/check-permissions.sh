#!/usr/bin/env bash
#
# check-permissions.sh — audit/fix folder & file permissions for this Laravel app.
#
# Self-contained (no Laravel boot required), so it works even when storage perms
# are broken enough that `php artisan` can't run. Mirrors the rules in the
# `security:permissions` Artisan command.
#
#   Usage:
#     tools/check-permissions.sh            # fix problems (default)
#     tools/check-permissions.sh --dry-run  # report only; exits 1 if issues found
#
set -euo pipefail

DRY_RUN=0
if [ "${1:-}" = "--dry-run" ]; then
    DRY_RUN=1
elif [ -n "${1:-}" ]; then
    echo "Unknown argument: $1" >&2
    echo "Usage: $0 [--dry-run]" >&2
    exit 2
fi

# Project root = parent of this script's directory.
ROOT="$(cd "$(dirname "$0")/.." && pwd)"

DIR_MODE=775
SECRET_MODE=600
ISSUES=0

# Directories Laravel must be able to write to (created if missing).
WRITABLE_DIRS=(
    "storage"
    "storage/app"
    "storage/app/public"
    "storage/app/private"
    "storage/framework"
    "storage/framework/cache"
    "storage/framework/cache/data"
    "storage/framework/sessions"
    "storage/framework/views"
    "storage/logs"
    "bootstrap/cache"
)

# Directories excluded from the world-writable scan.
SCAN_EXCLUDES=("vendor" "node_modules" ".git" "tools" "public/build")

# Current permission bits (e.g. 775) of a path, portable across GNU/BSD stat.
mode_of() {
    stat -c '%a' "$1" 2>/dev/null || stat -f '%Lp' "$1"
}

note() {
    ISSUES=$((ISSUES + 1))
    echo "$1"
}

# --- 1. Writable directories -------------------------------------------------
for rel in "${WRITABLE_DIRS[@]}"; do
    path="$ROOT/$rel"
    if [ ! -d "$path" ]; then
        note "  [writable] missing dir: $rel$([ "$DRY_RUN" = 1 ] || echo ' — creating')"
        [ "$DRY_RUN" = 1 ] || mkdir -p "$path"
        [ "$DRY_RUN" = 1 ] || chmod "$DIR_MODE" "$path"
        continue
    fi
    if [ "$(mode_of "$path")" != "$DIR_MODE" ]; then
        note "  [writable] $rel is 0$(mode_of "$path")$([ "$DRY_RUN" = 1 ] || echo ' — chmod 0775')"
        [ "$DRY_RUN" = 1 ] || chmod "$DIR_MODE" "$path"
    fi
done

# --- 2. Secret lockdown (0600) ----------------------------------------------
SECRET_FILES=(".env" ".env.backup" ".env.production")

# Laravel encryption keys, if any.
while IFS= read -r key; do SECRET_FILES+=("${key#"$ROOT/"}"); done \
    < <(find "$ROOT/storage" -maxdepth 1 -name '*.key' 2>/dev/null || true)

# MyInvois signing certificate path from .env (absolute or project-relative).
if [ -f "$ROOT/.env" ]; then
    cert="$(grep -E '^MYINVOIS_CERT_PATH=' "$ROOT/.env" | tail -n1 | cut -d= -f2- | tr -d '"'\''' || true)"
    [ -n "$cert" ] && SECRET_FILES+=("$cert")
fi

for rel in "${SECRET_FILES[@]}"; do
    case "$rel" in
        /*) path="$rel" ;;
        *)  path="$ROOT/$rel" ;;
    esac
    [ -f "$path" ] || continue
    if [ "$(mode_of "$path")" != "$SECRET_MODE" ]; then
        note "  [secret] $rel is 0$(mode_of "$path")$([ "$DRY_RUN" = 1 ] || echo ' — chmod 0600')"
        [ "$DRY_RUN" = 1 ] || chmod "$SECRET_MODE" "$path"
    fi
done

# --- 3. World-writable scan (clear the others-write bit) ---------------------
prune=()
for ex in "${SCAN_EXCLUDES[@]}"; do
    prune+=(-path "$ROOT/$ex" -prune -o)
done

while IFS= read -r path; do
    [ -n "$path" ] || continue
    rel="${path#"$ROOT/"}"
    note "  [world-writable] $rel is 0$(mode_of "$path")$([ "$DRY_RUN" = 1 ] || echo ' — chmod o-w')"
    [ "$DRY_RUN" = 1 ] || chmod o-w "$path"
done < <(find "$ROOT" "${prune[@]}" -perm -0002 -print 2>/dev/null || true)

# --- Result ------------------------------------------------------------------
if [ "$ISSUES" -eq 0 ]; then
    echo "Permissions OK — no issues found."
    exit 0
fi

if [ "$DRY_RUN" = 1 ]; then
    echo "$ISSUES issue(s) found. Re-run without --dry-run to fix."
    exit 1
fi

echo "Fixed $ISSUES issue(s)."
exit 0
