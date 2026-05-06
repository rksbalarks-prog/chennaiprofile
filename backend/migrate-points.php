<?php
// One-shot: creates the points tables. Run once then delete.
require_once __DIR__ . '/config.php';
$token = $_GET['t'] ?? '';
if ($token !== 'pts2026init') { http_response_code(403); exit('forbidden'); }

$db = getDB();

$db->exec("CREATE TABLE IF NOT EXISTS user_points (
  mobile       VARCHAR(15)  NOT NULL PRIMARY KEY,
  balance      INT          NOT NULL DEFAULT 0,
  total_bought INT          NOT NULL DEFAULT 0,
  total_used   INT          NOT NULL DEFAULT 0,
  created_at   DATETIME     DEFAULT NOW(),
  updated_at   DATETIME     DEFAULT NOW() ON UPDATE NOW()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->exec("CREATE TABLE IF NOT EXISTS point_transactions (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  mobile       VARCHAR(15)  NOT NULL,
  type         ENUM('purchase','deduct','admin_credit','admin_debit') NOT NULL,
  points       INT          NOT NULL,
  balance_after INT         NOT NULL,
  description  VARCHAR(255),
  ref_id       VARCHAR(100),
  created_at   DATETIME     DEFAULT NOW(),
  KEY idx_mobile  (mobile),
  KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Pending points purchases (awaiting PayU callback)
$db->exec("CREATE TABLE IF NOT EXISTS point_orders (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  mobile       VARCHAR(15)  NOT NULL,
  txn_id       VARCHAR(100) NOT NULL UNIQUE,
  pkg_id       VARCHAR(20)  NOT NULL,
  points       INT          NOT NULL,
  amount       DECIMAL(10,2) NOT NULL,
  status       ENUM('pending','success','failed') NOT NULL DEFAULT 'pending',
  payu_txn_id  VARCHAR(100),
  created_at   DATETIME     DEFAULT NOW(),
  updated_at   DATETIME     DEFAULT NOW() ON UPDATE NOW(),
  KEY idx_mobile (mobile)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

header('Content-Type: text/plain');
echo "Points tables created.\n";
