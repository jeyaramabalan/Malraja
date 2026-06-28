#!/bin/bash
set -e

APP_DIR="${1:-$HOME/malraja.supersalessoft.com}"
cd "$APP_DIR" || exit 1

echo "==> Removing auto_prepend_file from ini/htaccess (malware cleanup)"
for f in .htaccess .user.ini public/.htaccess; do
  if [ -f "$f" ]; then
    sed -i '/auto_prepend_file/d' "$f"
    echo "  cleaned: $f"
  fi
done

echo "==> Syncing code from GitHub main"
git fetch origin
git reset --hard origin/main

echo "==> Clearing Laravel caches"
php artisan optimize:clear 2>/dev/null || true

echo "==> Fixing permissions"
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "==> Done. Test: https://malraja.supersalessoft.com/healthcheck.php"
