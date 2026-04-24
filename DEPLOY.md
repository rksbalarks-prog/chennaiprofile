# Kumbakonam Free Matrimony - Deployment Guide

## Files to Upload

### Root Directory (public_html or /var/www/html/)
Copy everything from `frontend/dist/`:
```
index.html
assets/          (JS, CSS, images)
.htaccess        (SPA routing)
```

### Backend Directory (public_html/backend/ or /var/www/html/backend/)
Copy entire `backend/` folder:
```
backend/
├── admin-panel.php
├── user-panel.php
├── config.php          ← UPDATE DB CREDENTIALS
├── admin-config.php
├── sms.php             ← UPDATE SMS CREDENTIALS
├── .htaccess
├── *.js                (all utility JS files)
├── address-extract.js
├── dob-age.js
├── form-autosave.js
├── partner-caste.js
├── ... (all .js files)
└── api/
    ├── auth.php
    ├── public.php
    ├── activity.php
    ├── payment.php
    ├── logs.php
    ├── admin/
    │   ├── auth.php
    │   ├── profiles.php
    │   ├── settings.php
    │   ├── bills.php
    │   └── followups.php
    └── uploads/         ← ALL profile photos (copy entire folder)
```

## Database Setup

1. Create MySQL database: `matrimony`
2. Create MySQL user with full privileges
3. Import: `data/matrimony_production.sql`
4. Update `backend/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'matrimony');
```

## Server Requirements

- PHP 7.4+ (8.x recommended)
- PHP Extensions: pdo_mysql, curl, gd, mbstring, json
- MySQL/MariaDB 10.x+
- Apache with mod_rewrite enabled
- SSL certificate (HTTPS required)

## Post-Deploy Checklist

- [ ] Update DB credentials in config.php
- [ ] Update SMS credentials in sms.php
- [ ] Set `session.cookie_secure = 1` (HTTPS)
- [ ] Set proper file permissions (755 dirs, 644 files)
- [ ] Set uploads/ folder to 777 or writable by web server
- [ ] Change admin password via admin panel
- [ ] Test OTP sending
- [ ] Test photo upload
- [ ] Enable SSL/HTTPS
- [ ] Update CORS origins if using custom domain

## URLs After Deploy

- Frontend: https://yourdomain.com/
- Admin Panel: https://yourdomain.com/backend/admin-panel.php
- User Panel: https://yourdomain.com/backend/user-panel.php

## Tables (25 tables)

profiles, admins, bills, bill_history, follow_ups, otp_sessions, otp_logs,
usage_activity, subscription_plans, payment_options, restrictions,
user_panel_ctrl, deleted_profiles, expired_profiles, success_stories,
notifications, admin_log, role_permissions, alert_thresholds,
plan_history, up_ctrl_history, update_history, mobile_requests,
profile_reports, user_orders, order_archive, direct_login, 
direct_login_log, profile_tags, accounts
