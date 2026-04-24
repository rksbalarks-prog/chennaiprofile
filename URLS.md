# Project URLs — Quick Reference

Domain: **kumbakonamfreematrimony.com**

| Environment | Base URL |
|---|---|
| Local (XAMPP) | `http://localhost/matrimony/` |
| Production    | `https://kumbakonamfreematrimony.com/` |

Everywhere below, `{BASE}` = one of the above.

---

## 🟢 Public site (React SPA)

| Page | URL |
|---|---|
| Home (profile feed) | `{BASE}` |
| Search | `{BASE}search` |
| Profile detail | `{BASE}detail/:cpId` |
| Registration form | `{BASE}registration` |
| Contact us | `{BASE}contact` |
| About us | `{BASE}about-us` |
| Privacy policy | `{BASE}privacy-policy` |
| Terms & conditions | `{BASE}terms-and-conditions` |
| Google Form (EN) | `{BASE}google-form` |
| Google Form (TA) | `{BASE}google-form-ta` |

---

## 👤 User Portal (PHP)

| What | URL |
|---|---|
| Member portal | `{BASE}backend/user-panel.php` |
| Login | mobile + OTP flow (auto-opens on portal load) |

---

## 🔒 Admin Panel

| What | URL |
|---|---|
| Admin login + dashboard | `{BASE}backend/admin-panel.php` |

---

## 🔌 Public API (`backend/api/public.php`)

Single endpoint, action-based. Examples:

| Action | Full URL |
|---|---|
| Homepage bootstrap | `{BASE}backend/api/public.php?action=bootstrap&limit=12` |
| Search (paginated) | `{BASE}backend/api/public.php?action=search&gender=Female&limit=15&offset=0` |
| Profile detail | `{BASE}backend/api/public.php?action=detail&cp_id=CM2011234` |
| Mobile duplicate check | `{BASE}backend/api/public.php?checkMobile=9876543210` |

POST actions (Content-Type: application/json):
`contact_otp_send`, `contact_otp_verify`, `contact_check`, `contact_logout`, `contact_mobile_typed`, `register`, `track_view`, `report_profile`, `tag_profile`, `remove_tag`, `place_order`, `suggestions`, `user_limits`, `my_orders`, `my_tags`, `my_reports`, `upload_proof`.

---

## 🛠️ Admin API (`backend/api/admin/*`) — requires admin session

| Endpoint | URL | Method |
|---|---|---|
| Profile list + stats | `{BASE}backend/api/admin/profiles.php` | GET |
| Profile create/update | `{BASE}backend/api/admin/profiles.php` | POST |
| Bills | `{BASE}backend/api/admin/bills.php` | GET/POST |
| Follow-ups | `{BASE}backend/api/admin/followups.php` | GET/POST |
| Settings | `{BASE}backend/api/admin/settings.php` | GET/POST |
| Admin auth | `{BASE}backend/api/admin/auth.php` | POST |
| **Error log viewer** | `{BASE}backend/api/admin/errors.php?date=YYYY-MM-DD&level=error` | GET |
| **IP blocklist manager** | `{BASE}backend/api/admin/blocklist.php` | GET/POST/DELETE |

---

## 🧰 Other PHP endpoints

| What | URL |
|---|---|
| PayU initiate | `{BASE}backend/api/payu-initiate.php` |
| PayU return (callback from PayU) | `{BASE}backend/api/payu-return.php` |
| Contact form | `{BASE}backend/api/contact.php` |
| Photo scanner (admin utility) | `{BASE}backend/api/photo-scanner.php` |
| User login logs | `{BASE}backend/api/logs.php` |

---

## 🖼️ User uploads

| What | URL |
|---|---|
| Original | `{BASE}backend/api/uploads/<filename>.jpg` |
| WebP (1200px) | `{BASE}backend/api/uploads/<filename>.webp` |
| WebP thumbnail (400px) | `{BASE}backend/api/uploads/<filename>.thumb.webp` |
| Default avatar fallback | `{BASE}default-female.svg` / `{BASE}default-male.svg` |

---

## 🔧 One-off maintenance (localhost only — delete after use)

| What | URL |
|---|---|
| Generate thumbnails for existing uploads | `http://localhost/matrimony/backend/api/generate-thumbnails.php` |

---

## 📁 Access controls

| Path | Access |
|---|---|
| `backend/config.php`, `admin-config.php`, `sms.php` | Denied via `.htaccess` |
| `backend/db/` | Denied via `.htaccess` |
| `backend/logs/` | Denied via `.htaccess` (auto-written on first request) |
| `backend/api/uploads/*.php` | Denied (cannot execute PHP in uploads) |
| `*.sql`, `*.log`, `*.env`, `*.zip` at web root | Denied |
