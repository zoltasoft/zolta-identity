#!/usr/bin/env sh
set -eu

base_ref="${1:?Usage: scripts/check-dco.sh <base-ref>}"
range="$(git merge-base "$base_ref" HEAD)..HEAD"
missing=""

for commit in $(git rev-list "$range"); do
  if ! git show -s --format=%B "$commit" | grep -Eq '^Signed-off-by: .+ <[^>]+>$'; then
    missing="${missing}${commit}\n"
  fi
done

if [ -n "$missing" ]; then
  echo "Every pull-request commit must include a Signed-off-by line:" >&2
  printf '%b' "$missing" >&2
  exit 1
fi
