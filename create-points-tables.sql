-- Run this in phpMyAdmin on the live server (database: matrimony)
-- Creates the three tables needed for the points system.
-- Safe to run again — uses CREATE TABLE IF NOT EXISTS.

CREATE TABLE IF NOT EXISTS `user_points` (
  `mobile`       VARCHAR(15)  NOT NULL,
  `balance`      INT          NOT NULL DEFAULT 0,
  `total_bought` INT          NOT NULL DEFAULT 0,
  `total_used`   INT          NOT NULL DEFAULT 0,
  `updated_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `point_transactions` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mobile`       VARCHAR(15)  NOT NULL,
  `type`         VARCHAR(30)  NOT NULL COMMENT 'purchase | deduct | admin_credit | admin_debit',
  `points`       INT          NOT NULL,
  `balance_after` INT         NOT NULL DEFAULT 0,
  `description`  VARCHAR(255) NOT NULL DEFAULT '',
  `ref_id`       VARCHAR(100) NOT NULL DEFAULT '',
  `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mobile` (`mobile`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `point_orders` (
  `id`            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `mobile`        VARCHAR(15)    NOT NULL,
  `txn_id`        VARCHAR(100)   NOT NULL,
  `pkg_id`        VARCHAR(20)    NOT NULL,
  `points`        INT            NOT NULL,
  `amount`        DECIMAL(10,2)  NOT NULL,
  `status`        ENUM('pending','success','failed') NOT NULL DEFAULT 'pending',
  `target_cp_id`  VARCHAR(20)    NOT NULL DEFAULT '',
  `created_at`    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_txn_id` (`txn_id`),
  KEY `idx_mobile` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- If the table already exists, add the missing column (safe to run):
ALTER TABLE `point_orders` ADD COLUMN IF NOT EXISTS `target_cp_id` VARCHAR(20) NOT NULL DEFAULT '' AFTER `status`;
