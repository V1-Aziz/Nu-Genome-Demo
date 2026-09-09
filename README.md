# GenomePlatform NU

A PHP application for classifying genetic variants by pathogenic risk, built on a
small hand-rolled MVC framework. Runs on XAMPP with MySQL/MariaDB.

Given a variant's **allele frequency**, **CADD score** and **consequence type**, the
platform derives a rarity classification, a pathogenic score and a risk level, stores
the result against the user's account, and renders a chart-backed report.

> Research and teaching tool. The scoring is a set of threshold heuristics, not a
> clinical variant classification, and must not be used for diagnosis.

---

## Requirements

- XAMPP for macOS (Apache with `mod_rewrite`, PHP 8.1+, MySQL/MariaDB)
- `AllowOverride All` on the `htdocs` directory, so `.htaccess` is honoured

Both are on by default in a stock XAMPP install.

---

## Setup

```bash
# 1. Start Apache and MySQL
sudo /Applications/XAMPP/xamppfiles/bin/xampp start

# 2. Create the schema and demo accounts
/Applications/XAMPP/xamppfiles/bin/mysql -u root < setup.sql
```

Then open <http://localhost/>.

### Demo accounts

| Email | Password | Role |
|---|---|---|
| `admin@genomeplatform.local` | `asas1212` | admin |
| `researcher@genomeplatform.local` | `ChangeMe!Research1` | researcher |
| `user@genomeplatform.local` | `ChangeMe!User1` | user |

Change or delete these before the app is reachable by anyone else.

---

## Structure

```
htdocs/
├── index.php              Front controller — the only entry point
├── .htaccess              Rewrites all non-file requests to index.php
│
├── config/
│   ├── config.php         Settings; every value overridable by env var
│   └── routes.php         The route table
│
├── core/                  Framework (namespace Core\)
│   ├── bootstrap.php      Autoloader, config boot, exception handler
│   ├── App.php            Config access, logging, URL building, session
│   ├── Router.php         Path matching and controller dispatch
│   ├── Controller.php     Base controller: views, redirects, guards, CSRF
│   ├── Model.php          Base model
│   ├── Database.php       PDO wrapper; prepared statements only
│   ├── View.php           View + layout rendering
│   ├── Auth.php           Session authentication
│   ├── Csrf.php           Per-session CSRF tokens
│   └── helpers.php        e(), url(), csrf_field(), risk_color()
│
├── app/                   Application (namespace App\)
│   ├── Controllers/       Home, Auth, Dashboard, Analysis, Report, Page, Error
│   ├── Models/            User, AnalysisResult
│   └── Views/
│       ├── layouts/main.php
│       ├── partials/      nav, footer, flash, interpretation
│       ├── home/ auth/ dashboard/ analysis/ report/ pages/ errors/
│
├── assets/css/style.css   All styling
├── storage/logs/          Application log
└── setup.sql              Schema + demo accounts
```

`app/`, `core/`, `config/`, `storage/` and `bin/` each carry a
`Require all denied` `.htaccess`, and the root rewrite rejects those prefixes too.
Only `index.php` and `assets/` are reachable.

---

## Routes

| Method | Path | Controller | Access |
|---|---|---|---|
| GET | `/` | `HomeController::index` | public |
| GET | `/how-it-works` | `PageController::howItWorks` | public |
| GET/POST | `/login` | `AuthController` | guest only |
| GET/POST | `/register` | `AuthController` | guest only |
| GET | `/logout` | `AuthController::logout` | any |
| GET | `/dashboard` | `DashboardController::index` | signed in |
| GET/POST | `/analysis` | `AnalysisController::create` / `store` | signed in |
| GET | `/results` | `AnalysisController::history` | signed in |
| GET | `/report/{id}` | `ReportController::show` | owner only |

Add a route in `config/routes.php`; no other file needs to know about it.

---

## How a request flows

```
Browser → .htaccess → index.php → core/bootstrap.php   (autoload, config, session)
                                → config/routes.php    (route table)
                                → Router::dispatch     (match path)
                                → Controller action    (guards, validate, call model)
                                → Model                (prepared SQL via PDO)
                                → View::render         (view inside layouts/main)
```

---

## Conventions

**Controllers** validate input, call models, pick a view. No SQL, no HTML.
Guard with `$this->requireAuth()` or `$this->requireRole('researcher', 'admin')`,
and call `$this->verifyCsrf()` first in every POST action.

**Models** own all SQL and all domain rules. The scoring thresholds live in
`AnalysisResult::score()` so nothing else re-implements them. Every query is a
prepared statement — no string interpolation of user input, ever.

**Views** escape their own output with `e()`. Build links with `url()`.
Add a form's CSRF field with `csrf_field()`.

**Scoping to the owner.** Anything that loads a user's record takes the user id as
part of the query, e.g. `AnalysisResult::findForUser($id, $userId)`. Never look a
result up by id alone.

---

## Configuration

`config/config.php` reads environment variables with sensible local defaults:

| Variable | Default |
|---|---|
| `DB_HOST` | `127.0.0.1` |
| `DB_PORT` | `3306` |
| `DB_NAME` | `genome_platform` |
| `DB_USER` | `root` |
| `DB_PASS` | *(empty)* |
| `DB_SOCKET` | *(unset — uses TCP)* |
| `APP_DEBUG` | `true` |
| `APP_BASE_URL` | *(empty — served at the domain root)* |

Set `APP_DEBUG=0` for anything non-local: errors then go to
`storage/logs/app.log` instead of the page.

If TCP fails, point `DB_SOCKET` at
`/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock`.

---

## The ML service

`api.py` is a standalone Flask service that trains a RandomForest on
`Model/Model.csv` and exposes `POST /api/predict`. **It is not wired into the PHP
application** — `/analysis` uses the threshold rules in `AnalysisResult::score()`.

Two things to know before connecting them:

- The inputs disagree. The web form collects allele frequency, CADD (0–60) and
  labels like `Missense Variant`; the model wants CADD 0–40, a `GERP_Score`, and
  bare labels like `Missense`.
- `Model/Model.csv` is 100 rows generated by `Model/Model.py` from
  `np.random.uniform`, labelled by a hand-written scoring rule. The model is
  learning to reproduce that rule over noise, not anything biological.

```bash
pip install -r requirements.txt
python3 api.py          # listens on port 5001
```

---

## Notes on the rebuild

This replaced a set of flat PHP pages. Beyond the restructure, these defects were
fixed:

- `visual-report.php` looked results up by id alone, with no login check — any
  visitor could read any user's results. Reports are now owner-scoped.
- `config.php` defined `get_current_user()` inside `if (!function_exists(...))`,
  but that is a PHP built-in, so the function never existed. Callers got a string
  instead of a user row; `results.php` consequently redirected to login every
  time. Authentication is now `Core\Auth`, which cannot collide.
- The home page listed other users' results and usernames publicly. It now shows
  aggregate counts only.
- Auth queries were built by string concatenation, and `sanitize()` HTML-escaped
  values *before* storing them, corrupting any name or email containing `&` or
  `'`. All queries are prepared statements; escaping happens on output.
- `logout.php` destroyed the session but left the cookie. `Auth::logout()` expires it.
- The demo password hashes in the old `setup.sql` were malformed, so those
  accounts could never be logged into. The ones above are real.
- Added: CSRF tokens on every POST, `session_regenerate_id()` on login, and
  same-application-only redirect validation.

### Live schema differs from `setup.sql`

The `genome_platform` tables in MySQL were created from the original schema and
have never been migrated. They differ from `setup.sql` in two cosmetic ways:

| | live database | `setup.sql` |
|---|---|---|
| `id`, `user_id` | `INT` (signed) | `INT UNSIGNED` |
| `allele_frequency` | `DECIMAL(5,4)` | `DECIMAL(6,5)` |

The application runs correctly against both — every column name matches, and
`DECIMAL(5,4)` holds any allele frequency in the 0-1 range the form accepts.
Loading `setup.sql` over the existing database will not reconcile this: the
tables use `CREATE TABLE IF NOT EXISTS` and the seed ends in
`ON DUPLICATE KEY UPDATE`, so both are no-ops once the data is there.
