#!/usr/bin/env bash
# Tương thích lệnh cũ; mọi deploy/update dùng cùng quy trình production.
set -Eeuo pipefail
exec bash "$(dirname -- "${BASH_SOURCE[0]}")/deploy.sh" "$@"
