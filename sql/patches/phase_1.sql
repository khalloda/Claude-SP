-- Phase 1 Migration Patch
-- Database schema updates for Phase 1
-- This script is idempotent and safe to re-run

USE `sp_main`;

-- Check if this patch has already been applied
CREATE TABLE IF NOT EXISTS `sp_migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `migration` (`migration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Apply Phase 1 patch only if not already applied
INSERT IGNORE INTO `sp_migrations` (`migration`) VALUES ('phase_1');

-- Add any Phase 1 specific schema changes here
-- (Currently all schema is in base schema.sql)

-- Add indexes for better performance if they don't exist
CREATE INDEX IF NOT EXISTS `idx_products_total_qty` ON `sp_products` (`total_qty`);
CREATE INDEX IF NOT EXISTS `idx_products_reserved_quotes` ON `sp_products` (`reserved_quotes`);
CREATE INDEX IF NOT EXISTS `idx_products_reserved_orders` ON `sp_products` (`reserved_orders`);

-- Ensure proper collation for text fields
ALTER TABLE `sp_users` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `sp_clients` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `sp_suppliers` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `sp_products` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `sp_warehouses` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
