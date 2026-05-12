<?php
// Staff HQ — site configuration template.
// Copy this file to staff-hq-sites.php and fill in real secrets.
// staff-hq-sites.php is gitignored — never commit it.
//
// 'secret' = contents of .deploy-secret on that server (same as DEPLOY_HMAC_SECRET in GitHub).
// 'color'  = badge text colour.  'bg' = badge background.

return [
    'hq_password' => 'change-this-password',   // Password to access the HQ dashboard

    'sites' => [
        [
            'name'   => 'Chennai Profile',
            'tag'    => 'CPA',
            'url'    => 'https://chennaiprofile.in/backend/staff-log-api.php',
            'secret' => 'PASTE_CPA_DEPLOY_SECRET_HERE',
            'color'  => '#1d4ed8',
            'bg'     => '#dbeafe',
        ],
        [
            'name'   => 'Kumbakonam Matrimony',
            'tag'    => 'KFH',
            'url'    => 'https://kumbakonamfreematrimony.com/backend/staff-log-api.php',
            'secret' => 'PASTE_KFH_DEPLOY_SECRET_HERE',
            'color'  => '#92400e',
            'bg'     => '#fef3c7',
        ],
        // Add more sites:
        // [
        //     'name'   => 'Site 3',
        //     'tag'    => 'S3',
        //     'url'    => 'https://site3.com/backend/staff-log-api.php',
        //     'secret' => 'PASTE_SECRET_HERE',
        //     'color'  => '#166534',
        //     'bg'     => '#dcfce7',
        // ],
    ],
];
