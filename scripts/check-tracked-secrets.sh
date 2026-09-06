#!/usr/bin/env sh
set -eu

tracked_env_files="$(git ls-files | grep -E '(^|/)\.env($|\.)' | grep -vE '(^|/)\.env\.example$|(^|/)\.env\.production\.example$' || true)"
if [ -n "$tracked_env_files" ]; then
  echo "Tracked environment files are not allowed:" >&2
  echo "$tracked_env_files" >&2
  exit 1
fi

tracked_sensitive_files="$(git ls-files | grep -Ei '\.(pem|key|p12|pfx|sqlite|db)$' || true)"
if [ -n "$tracked_sensitive_files" ]; then
  echo "Tracked private keys or local databases are not allowed:" >&2
  echo "$tracked_sensitive_files" >&2
  exit 1
fi

if git grep -IEn '(github_pat_[A-Za-z0-9_]{20,}|gh[pousr]_[A-Za-z0-9]{20,}|AKIA[A-Z0-9]{16}|-----BEGIN [A-Z ]*PRIVATE KEY-----|sk_(live|test)_[A-Za-z0-9]{16,}|xox[baprs]-[A-Za-z0-9-]{16,})' -- ':!pnpm-lock.yaml'; then
  echo "A tracked file matches a high-confidence secret pattern." >&2
  exit 1
fi
