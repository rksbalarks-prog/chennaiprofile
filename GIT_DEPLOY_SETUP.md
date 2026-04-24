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

## Fallback: HTTPS deploy (when FTP/SSH is firewalled)

HostEasy's shared-hosting firewall blocks ports 21, 22, and 990 globally, so
the FTPS workflow above can't reach the server. The branch `feat/https-deploy`
contains a working alternative that uploads a signed zip over port 443 (HTTPS),
which is always open because the site is reachable from browsers.

### How it works

- `backend/deploy.php` — lives on the server. Receives a POST with a zip body,
  authenticates it via HMAC-SHA256 + timestamp (rejects >5 min old), and
  extracts files into the site. Protected paths (`config.php`, `sms.php`,
  `payu-config.php`, `frontend/API/db.php`, `uploads/`) are never overwritten.
- `.github/workflows/deploy-https.yml` — builds the frontend, zips payload,
  signs it, curls it to `deploy.php`.

### Activation (one-time)

1. **Generate a shared HMAC secret** on your local machine:
   ```bash
   openssl rand -hex 32
   ```
   Copy the 64-char hex output.

2. **On the server** (via DirectAdmin File Manager, since FTP is blocked):
   - Upload `backend/deploy.php` from this branch into the site's
     `public_html/backend/` folder.
   - Create a new file `public_html/backend/.deploy-secret` containing only
     the 64-char hex value from step 1 (no quotes, no newline at end if your
     editor lets you avoid it — but trailing whitespace is stripped anyway).
   - Make sure `.deploy-secret` is NOT web-accessible. `backend/.htaccess`
     already blocks dotfiles in most setups; if in doubt, add a line:
     ```
     <Files ".deploy-secret">
         Require all denied
     </Files>
     ```

3. **In GitHub → repo Settings → Secrets and variables → Actions**, add:
   | Name | Value |
   |---|---|
   | `DEPLOY_URL` | `https://kumbakonamfreematrimony.com/backend/deploy.php` |
   | `DEPLOY_HMAC_SECRET` | the same 64-char hex from step 1 |

4. **Merge `feat/https-deploy` to `main`** (or cherry-pick its three files):
   ```bash
   git checkout main
   git merge feat/https-deploy
   git push
   ```

5. **First run** — manually trigger from Actions tab:
   - Go to https://github.com/rksbalarks-prog/matrimony/actions
   - Select "Deploy via HTTPS (HMAC-authenticated)"
   - Click "Run workflow" → Run
   - Watch the logs. A successful run ends with `{"ok":true,"written":N,...}`.

6. **Once verified**, uncomment the `push: branches: [main]` trigger in
   `deploy-https.yml` to get full auto-deploy on every push, and either
   disable or delete the FTPS `deploy.yml` workflow so it stops failing.

### Troubleshooting HTTPS deploy

- **`signature mismatch`** → `DEPLOY_HMAC_SECRET` in GitHub and
  `.deploy-secret` on the server must be byte-identical. Re-create both from
  the same `openssl rand -hex 32` output.
- **`timestamp outside ±300s window`** → server clock skew. Either fix NTP on
  the server or widen the window in `deploy.php` (not recommended).
- **`HTTP 413` or `HTTP 500 deploy-secret missing`** → shared hosting's
  default `upload_max_filesize` / `post_max_size` (often 2–8 MB) is too
  small. Add to `backend/.htaccess`:
  ```
  php_value upload_max_filesize 32M
  php_value post_max_size 32M
  php_value max_execution_time 120
  ```
- **`ZipArchive unavailable on server`** → rare; ask HostEasy to enable the
  PHP `zip` extension.

## Troubleshooting

**Workflow fails with "FTP connection timeout"** → hosting may block FTPS from
GitHub IPs. Switch `protocol: ftps` to `protocol: ftp` (less secure) or ask
HostEasy for an IP whitelist / SFTP access.

**Frontend deploys but backend doesn't find DB** → `backend/config.php` missing
on server. Upload manually using the `.example.php` template.

**Some PHP files vanish** → the deploy step found them locally but your `exclude`
list should protect them. Check the workflow's `exclude:` blocks.
