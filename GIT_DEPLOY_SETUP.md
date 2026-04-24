# Git + Auto-Deploy Setup

This repo auto-deploys to kumbakonamfreematrimony.com via GitHub Actions
on every push to `main`. The workflow lives in `.github/workflows/deploy.yml`.

## One-time setup (do this now)

### 1. Create a private GitHub repo

- Go to https://github.com/new
- Name: `matrimony` (or whatever you like)
- **Private** (don't expose source publicly)
- Do NOT initialize with README/.gitignore/LICENSE (we already have local history)
- Copy the repo URL (e.g., `git@github.com:YOUR_USER/matrimony.git`)

### 2. Push this repo to GitHub

From `c:/xampp/htdocs/matrimony`:

```bash
git remote add origin git@github.com:YOUR_USER/matrimony.git
git push -u origin main
```

If you use HTTPS instead of SSH: `https://github.com/YOUR_USER/matrimony.git`.
GitHub will prompt for a personal access token (not your password).

### 3. Gather FTP credentials from DirectAdmin

In the DirectAdmin control panel (host4.hosteasy.in:2222):

- **FTP Accounts** → note / create the account used for
  `kumbakonamfreematrimony.com`
- Record: hostname, username, password, port (usually 21 for FTPS)
- Find the absolute server paths for:
  - **Frontend web root** — e.g., `/domains/kumbakonamfreematrimony.com/public_html/`
  - **Backend folder** — e.g., `/domains/kumbakonamfreematrimony.com/public_html/backend/`
  - (If your FTP account is already chroot'd to the site root, these can be
    `./` and `./backend/` respectively.)

### 4. Add GitHub Actions secrets

Repo page → **Settings** → **Secrets and variables** → **Actions** → **New repository secret**.
Add each of these:

| Name                        | Value                                                     |
|-----------------------------|-----------------------------------------------------------|
| `FTP_HOST`                  | `host4.hosteasy.in` (or the hostname your FTP client uses)|
| `FTP_USER`                  | FTP username                                              |
| `FTP_PASSWORD`              | FTP password                                              |
| `FTP_PORT`                  | `21` (FTPS) — omit if default is fine                     |
| `FTP_SERVER_DIR_FRONTEND`   | Web root, e.g. `/domains/kumbakonamfreematrimony.com/public_html/` or `./` |
| `FTP_SERVER_DIR_BACKEND`    | Backend dir, e.g. `/domains/kumbakonamfreematrimony.com/public_html/backend/` or `./backend/` |

Paths **must end with a trailing slash**.

### 5. Bootstrap server-side secrets ONCE

These files are in `.gitignore` and are never deployed — the server keeps its
own copies. On the server, confirm each of these exists with real credentials:

- `backend/config.php`            (DB credentials) — see `backend/config.example.php`
- `backend/sms.php`               (SMS API key) — see `backend/sms.example.php`
- `backend/payu-config.php`       (PayU key + salt) — see `backend/payu-config.example.php`
- `frontend/API/db.php`           (separate `chennai_profiles` DB) — see `frontend/API/db.example.php`

If any are missing after your first deploy, upload them manually via FTP and
they will stay put on subsequent deploys.

### 6. First deploy

```bash
git push origin main
```

- GitHub → **Actions** tab shows the run.
- Build step (~1 min) installs npm deps and runs `vite build`.
- Two FTP steps upload frontend dist/ and backend/.
- Total ~3–5 min depending on server.

## Daily workflow

```bash
# Make changes locally
git add -A
git commit -m "fix: describe the change"
git push
# → auto-deploys in ~5 min
```

## What is NEVER deployed (safe on server)

- `backend/config.php`, `backend/config.production.php`
- `backend/sms.php`
- `backend/payu-config.php`
- `frontend/API/db.php`
- `backend/api/uploads/**` (user-uploaded photos)
- `backend/logs/**`
- `uploads/**`

If you need to change one of these, edit it on the server directly (DirectAdmin
File Manager) — git will ignore the change locally too.

## Rolling back a bad deploy

```bash
git revert HEAD
git push
# → auto-deploys the revert
```

Or manually re-run an older successful workflow: Actions → pick a run → "Re-run jobs".

## Troubleshooting

**Workflow fails with "FTP connection timeout"** → hosting may block FTPS from
GitHub IPs. Switch `protocol: ftps` to `protocol: ftp` (less secure) or ask
HostEasy for an IP whitelist / SFTP access.

**Frontend deploys but backend doesn't find DB** → `backend/config.php` missing
on server. Upload manually using the `.example.php` template.

**Some PHP files vanish** → the deploy step found them locally but your `exclude`
list should protect them. Check the workflow's `exclude:` blocks.
