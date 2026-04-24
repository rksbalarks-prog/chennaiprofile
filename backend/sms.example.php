<?php
// matrimony/backend/sms.example.php  —  TEMPLATE. Copy to sms.php on the server and fill in real credentials.

define('SMS_ENABLED',     true);
define('SMS_API_URL',     'https://www.smsidea.co.in/smsstatuswithid.aspx');
define('SMS_USERNAME',    'REPLACE_ME');
define('SMS_API_KEY',     'REPLACE_ME');
define('SMS_SENDER_ID',   'REPLACE_ME');
define('SMS_DLT_TE_ID',   'REPLACE_ME');
define('SMS_OTP_TEMPLATE', 'Your Login OTP {OTP} ...');

function sendSMS(string $mobile, string $message): array {
    return ['success' => false, 'response' => '', 'error' => 'sms.php not configured'];
}
