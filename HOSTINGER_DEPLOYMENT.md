# Hostinger Deployment Guide (Shared Hosting, No SSH)

This guide deploys the **Sales Management System SaaS** (Laravel 13 + Livewire/Volt, PHP 8.3)
to **Hostinger shared hosting** with **no SSH access** (e.g. the Single plan), using only
**hPanel**, **File Manager**, and a temporary web route. The server runs **LiteSpeed**
(Apache-compatible, honors `.htaccess`).

Because you can't run commands on the server, the strategy is:
- Build **everything locally** (frontend assets **and** `vendor/`) and upload the complete project.
- Generate `APP_KEY` **locally** and paste it into the server `.env`.
- Run database migrations through a **temporary web route**, then **delete it**.
- Queue set to **`sync`** (jobs run inline — no worker/cron needed).

> **First, double-check you really lack SSH.** hPanel → **Advanced → SSH Access**. Hostinger's
> **Premium** and **Business** plans include SSH; if you see it, the SSH method is much easier and
> safer — tell me and I'll switch this guide back. The steps below assume **no SSH**.

---

## 0. Prerequisites

- **PHP 8.3+** selectable in hPanel (required by Laravel 13). See Step 3.
- A domain or subdomain on your Hostinger account.
- Required PHP extensions (all available on Hostinger, confirm enabled in Step 3):
  `bcmath, ctype, curl, fileinfo, gd, json, mbstring, openssl, pdo_mysql, tokenizer, xml, zip`
  (`gd` + `zip` are needed by DomPDF and the Excel exporter.)
- On your own machine: PHP 8.3, Composer, and Node.js installed (to build the package).

---

## 1. Build the complete package locally

Since the server can't run Composer or npm, you must produce a fully self-contained project.
In the project root on your computer:

```bash
# 1. Build production frontend assets -> public/build/
npm run build

# 2. Install production PHP dependencies into vendor/ (MUST be uploaded)
composer install --no-dev --optimize-autoloader

# 3. Generate an app key and copy the output (starts with "base64:")
php artisan key:generate --show
```

Copy the `base64:...` value from step 3 — you'll paste it into the server `.env` in Step 4.

**Create the deployment zip:**
- **Include:** `app/`, `bootstrap/`, `config/`, `database/`, `public/` (with `public/build/`),
  `resources/`, `routes/`, `storage/`, `vendor/`, `artisan`, `composer.json`, `composer.lock`,
  and the `.htaccess` files (including `public/.htaccess`).
- **Exclude:** `node_modules/`, `.git/`, `.github/`, `tests/`, `.env`.

> `vendor/` **must** be included this time — there's no SSH to run `composer install` on the server.

---

## 2. Upload and extract on Hostinger

1. hPanel → **Files → File Manager**.
2. Recommended layout: put the project **next to** `public_html`, not inside it. Your home
   directory (e.g. `/home/u123456789/`) contains `public_html/`. Create `salesapp/` beside it:
   ```
   /home/u123456789/
   ├── public_html/        <- default web root (we will repoint this in Step 3)
   └── salesapp/           <- full Laravel project (upload + extract the zip here)
       ├── app/
       ├── public/         <- becomes the real document root
       ├── vendor/
       └── ...
   ```
3. Upload the zip into `~/salesapp/` and use File Manager's **Extract**.

---

## 3. Set PHP version + point the domain at `public/`

1. **PHP version:** hPanel → **Advanced → PHP Configuration** → select **PHP 8.3** (or newer).
   On the **PHP extensions** tab, confirm the extensions in Step 0 are enabled.
2. **Document root:** hPanel → **Websites → (your domain) → Website settings** (or **Domains →
   your domain**). Change the document root to:
   ```
   /home/u123456789/salesapp/public
   ```
   LiteSpeed will then serve the app via Laravel's existing `public/.htaccess`. **Done — skip the
   fallback below.**

### Fallback (only if your plan won't let you change the document root)

Leave the web root as `public_html` and instead:
1. Move the **contents** of `~/salesapp/public/` into `~/public_html/`.
2. Edit `~/public_html/index.php` in File Manager and fix the two require paths:
   ```php
   // change  __DIR__.'/../vendor/autoload.php'
   require __DIR__.'/../salesapp/vendor/autoload.php';

   // change  __DIR__.'/../bootstrap/app.php'
   $app = require_once __DIR__.'/../salesapp/bootstrap/app.php';
   ```
   (Prefer the document-root method — this path-editing approach is more fragile.)

---

## 4. Create the database + configure `.env`

### 4a. Create the database
hPanel → **Databases → MySQL Databases** → create a database and user, assign the user with all
privileges. Note Hostinger's prefixed names (e.g. `u123456789_sales`).

### 4b. Edit `.env`
In File Manager, open `~/salesapp/.env` (create it from `.env.example` if missing) and set:
```ini
APP_NAME="Sales SaaS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_KEY=base64:PASTE_THE_KEY_FROM_STEP_1_HERE

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u123456789_sales
DB_USERNAME=u123456789_salesuser
DB_PASSWORD=your_secure_password

# Database-backed drivers (app default) — no Redis needed.
SESSION_DRIVER=database
CACHE_STORE=database

# Jobs run inline. No worker/cron required.
QUEUE_CONNECTION=sync

# Configure real email before going live:
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=you@yourdomain.com
MAIL_PASSWORD=your_mailbox_password
MAIL_FROM_ADDRESS="you@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

> The shipped `.env.example` defaults `DB_CONNECTION=sqlite` and `APP_DEBUG=true` — make sure you
> switch to `mysql` and `false` as shown, and that `APP_KEY` is filled in.

---

## 5. Run migrations via a temporary web route (no SSH)

Since you can't run `php artisan migrate`, run it once through a temporary route, then remove it.

1. In File Manager, edit `~/salesapp/routes/web.php` and append at the very bottom:
   ```php
   // TEMPORARY install routes — DELETE after running (see step 4 below).
   Route::get('/__install', function () {
       try {
           \Artisan::call('migrate', ['--force' => true]);
           \Artisan::call('db:seed', ['--force' => true]);   // optional: default roles/users
           \Artisan::call('storage:link');                   // enable public uploads/logos
           return nl2br(e(\Artisan::output())) . '<br>DONE. Now DELETE this route.';
       } catch (\Throwable $e) {
           return 'ERROR: ' . e($e->getMessage());
       }
   });
   ```
2. Visit **`https://yourdomain.com/__install`** once in your browser. You should see the migration
   output ending in `DONE`.
3. If `storage:link` fails (some shared hosts block symlinks), see the note at the bottom.
4. **CRITICAL:** edit `routes/web.php` again and **delete the `/__install` route**. Leaving it lets
   anyone wipe/reseed your database.

> Already cached config can hide a fresh `.env`. If the route errors with stale settings, also add
> `\Artisan::call('optimize:clear');` as the first line inside the `try`.

---

## 6. Permissions (File Manager)

Set these folders inside `~/salesapp/` (and their contents, recursively) to **755** or **775** via
File Manager → right-click → **Permissions** → check "Apply to subdirectories":
- `storage/`
- `bootstrap/cache/`

---

## 7. Enable HTTPS

hPanel → **Security → SSL** → install the free certificate for your domain, then enable
**Force HTTPS**. Confirm `APP_URL` in `.env` uses `https://`.

---

## 8. Go-live checklist

- [ ] PHP 8.3+ selected and required extensions enabled.
- [ ] Domain document root points to `.../salesapp/public` (or fallback `index.php` paths fixed).
- [ ] `.env`: `APP_ENV=production`, `APP_DEBUG=false`, real `APP_URL`, **`APP_KEY` filled**, MySQL creds, `QUEUE_CONNECTION=sync`.
- [ ] `/__install` visited → migrations (+ optional seed + storage:link) succeeded.
- [ ] **`/__install` route deleted** from `routes/web.php`.
- [ ] `storage/` and `bootstrap/cache/` writable (755/775).
- [ ] HTTPS active and forced.
- [ ] Log in, create a sale, export an Excel/PDF report (confirms `gd`/`zip` work).

---

## Notes specific to this app

- **No SSH = build locally every time.** For any update, rebuild on your machine
  (`npm run build` + `composer install --no-dev --optimize-autoloader`) and re-upload the changed
  files (including `vendor/` and `public/build/` when those change).
- **Queue = `sync`:** Excel/PDF exports run inline within the request. No worker or cron needed,
  which suits a no-SSH plan. If a big export ever times out, the fix is moving to a plan with SSH/cron.
- **If `storage:link` is blocked:** point uploads at a public folder instead. Create
  `~/salesapp/public/storage/` manually in File Manager and set `FILESYSTEM_DISK=public` with the
  public disk root configured to that folder, or store uploaded files directly under `public/`.
  Re-run `/__install` (or clear cache) after changing disk config.
- **Clearing cache without SSH:** add `\Artisan::call('optimize:clear');` to the temporary route,
  hit it once, then remove the route again.
