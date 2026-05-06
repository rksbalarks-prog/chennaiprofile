<?php
// OTP wrapper — bridges sendSMS() (defined in sms.php) to sendOTP() callers.
// sms.php defines credentials + sendSMS(); this file provides the sendOTP shim.
// Include this AFTER sms.php in any file that calls sendOTP().
if (!function_exists('sendOTP')) {
    function sendOTP(string $mobile, string $otp): array {
        if (!function_exists('sendSMS')) {
            return ['success' => false, 'error' => 'SMS not configured'];
        }
        $tpl = defined('SMS_OTP_TEMPLATE') ? SMS_OTP_TEMPLATE : 'Your OTP is {OTP}. Valid for 2 minutes. Do not share.';
        return sendSMS($mobile, str_replace('{OTP}', $otp, $tpl));
    }
}
