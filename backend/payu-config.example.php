<?php
// matrimony/backend/payu-config.example.php  —  TEMPLATE. Copy to payu-config.php on the server and fill in.

define('PAYU_KEY',      'REPLACE_ME');
define('PAYU_SALT',     'REPLACE_ME');
define('PAYU_MID',      'REPLACE_ME');
define('PAYU_MODE',     'test'); // 'live' or 'test'
define('PAYU_ENDPOINT', PAYU_MODE === 'live'
    ? 'https://secure.payu.in/_payment'
    : 'https://test.payu.in/_payment');
