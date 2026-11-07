#!/usr/bin/env bash
# Tuong thich lenh cu; moi deploy/update dung cung quy trinh production.
set -Eeuo pipefail
exec bash "$(dirname -- "${BASH_SOURCE[0]}")/deploy.sh" "$@"
