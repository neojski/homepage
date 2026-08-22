#!/bin/sh
# Serve the map locally.  ./dev.sh [port]      Ctrl-C to stop.
#
# There is no authentication in front of api.php, so this binds to loopback
# only. Do not reach for this to serve the map to anything but this machine.
set -e
port=${1:-8000}
dir=$(cd "$(dirname "$0")" && pwd)

command -v php >/dev/null || { echo "php not found" >&2; exit 1; }
command -v rsvg-convert >/dev/null || command -v inkscape >/dev/null || \
  echo "note: no rsvg-convert or inkscape found - pdf.php will return 503"

echo "editor  http://127.0.0.1:$port/editor.php"
echo "map     http://127.0.0.1:$port/index.php"
echo "state   $dir/private/plots-state.json"
exec php -S "127.0.0.1:$port" -t "$dir/web"
