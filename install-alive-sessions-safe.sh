#!/bin/bash
# Safe install of admin alive-sessions onto a WORKING product/ tree.
# Keeps your live App.php / Authentication.php / User.php and only inserts small hooks.
set -euo pipefail

cd "$(cd "$(dirname "$0")" && pwd)"

if [ ! -f product/index.php ]; then
  echo "Run from boybio.net root (directory that contains product/)"
  exit 1
fi

echo "=== Fetch origin/backup ==="
git fetch origin backup

BK="/tmp/boybio_sessions_safe_bak_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BK"
for rel in \
  app/models/UsersSessions.php \
  app/models/User.php \
  app/core/App.php \
  app/core/Authentication.php \
  app/controllers/admin/AdminUsers.php \
  'app/languages/admin/english#en.php' \
  themes/altum/views/admin/users/index.php \
  themes/altum/views/admin/users/user_sessions_modal.php
do
  if [ -f "product/$rel" ]; then
    mkdir -p "$BK/$(dirname "$rel")"
    cp -a "product/$rel" "$BK/$rel"
  fi
done
echo "Backup at: $BK"

echo "=== New + admin UI files from git ==="
git show origin/backup:product/app/models/UsersSessions.php > product/app/models/UsersSessions.php
mkdir -p product/themes/altum/views/admin/users
git show origin/backup:product/themes/altum/views/admin/users/user_sessions_modal.php \
  > product/themes/altum/views/admin/users/user_sessions_modal.php
git show origin/backup:product/app/controllers/admin/AdminUsers.php \
  > product/app/controllers/admin/AdminUsers.php
git show origin/backup:product/themes/altum/views/admin/users/index.php \
  > product/themes/altum/views/admin/users/index.php
git show 'origin/backup:product/app/languages/admin/english#en.php' \
  > 'product/app/languages/admin/english#en.php'

echo "=== Insert hooks into LIVE core files ==="
python3 - <<'PY'
from pathlib import Path

def ensure(path: Path, marker: str, old: str, new: str):
    text = path.read_text(encoding='utf-8', errors='replace')
    if marker in text:
        print('already ok', path)
        return
    if old not in text:
        raise SystemExit(f'needle missing in {path}\n--- looking for ---\n{old[:180]}...')
    path.write_text(text.replace(old, new, 1), encoding='utf-8')
    print('patched', path)

app = Path('product/app/core/App.php')
auth = Path('product/app/core/Authentication.php')
user = Path('product/app/models/User.php')

app_old = """            if(!$user->last_activity || (new \\DateTime($user->last_activity))->modify('+15 minutes') < (new \\DateTime()) && !session_has('admin_user_id')) {
                (new User())->update_last_activity(\\Altum\\Authentication::$user_id);
            }

            if(!isset($_COOKIE['set_language'])) {"""

app_new = """            if(!$user->last_activity || (new \\DateTime($user->last_activity))->modify('+15 minutes') < (new \\DateTime()) && !session_has('admin_user_id')) {
                (new User())->update_last_activity(\\Altum\\Authentication::$user_id);
            }

            /* Keep alive session row fresh (also when admin-impersonating) */
            try {
                if(class_exists('Altum\\\\Models\\\\UsersSessions')) {
                    \\Altum\\Models\\UsersSessions::touch_current((int) \\Altum\\Authentication::$user_id);
                }
            } catch(\\Throwable $e) {
                /* ignore */
            }

            if(!isset($_COOKIE['set_language'])) {"""

# also support $_SESSION variant of the admin_user_id check
app_old_alt = app_old.replace("!session_has('admin_user_id')", "!isset($_SESSION['admin_user_id'])")
app_new_alt = app_new.replace("!session_has('admin_user_id')", "!isset($_SESSION['admin_user_id'])")

text = app.read_text(encoding='utf-8', errors='replace')
if 'UsersSessions::touch_current' in text:
    print('already ok', app)
elif app_old in text:
    app.write_text(text.replace(app_old, app_new, 1), encoding='utf-8')
    print('patched', app)
elif app_old_alt in text:
    app.write_text(text.replace(app_old_alt, app_new_alt, 1), encoding='utf-8')
    print('patched alt', app)
else:
    raise SystemExit('FAIL: App.php needle not found — paste grep -n last_activity product/app/core/App.php')

ensure(
    auth,
    'UsersSessions::end_current',
    """    public static function logout($page = '') {

        if(self::check()) {
            db()->where('user_id', self::$user_id)->update('users', ['token_code' => '']);""",
    """    public static function logout($page = '') {

        if(self::check()) {
            try {
                if(class_exists('\\\\Altum\\\\Models\\\\UsersSessions')) {
                    \\Altum\\Models\\UsersSessions::end_current();
                }
            } catch(\\Throwable $e) {
                /* ignore */
            }

            db()->where('user_id', self::$user_id)->update('users', ['token_code' => '']);""",
)

ensure(
    user,
    'UsersSessions::upsert_current',
    """        Logger::users($user_id, 'login.' . $method . '.success');

        /* Clear the cache */
        cache()->deleteItemsByTag('user_id=' . $user_id);""",
    """        Logger::users($user_id, 'login.' . $method . '.success');

        /* Track alive session for admin users list */
        try {
            if(class_exists('\\\\Altum\\\\Models\\\\UsersSessions')) {
                $user_type = db()->where('user_id', $user_id)->getValue('users', 'type');
                \\Altum\\Models\\UsersSessions::upsert_current((int) $user_id, (bool) $user_type);
            }
        } catch(\\Throwable $e) {
            /* ignore */
        }

        /* Clear the cache */
        cache()->deleteItemsByTag('user_id=' . $user_id);""",
)
PY

echo "=== Lint ==="
php -l product/app/models/UsersSessions.php
php -l product/app/core/App.php
php -l product/app/core/Authentication.php
php -l product/app/models/User.php
php -l product/app/controllers/admin/AdminUsers.php

echo
echo "OK. Next:"
echo "  systemctl restart apache2"
echo "  curl -sI https://cloub.io/ | head -5"
echo "Rollback:"
echo "  rsync -a $BK/ product/"
echo "  rm -f product/app/models/UsersSessions.php product/themes/altum/views/admin/users/user_sessions_modal.php"
echo "  systemctl restart apache2"
