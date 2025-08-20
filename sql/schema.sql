-- Spare Parts Management System Database Schema
-- Version: 1.0
-- This script is idempotent and safe to re-run

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `sp_main` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sp_main`;

-- --------------------------------------------------------
-- Users and Authentication Tables
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `sp_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `locale` enum('en','ar') NOT NULL DEFAULT 'en',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- RBAC Tables (for future phases)
CREATE TABLE IF NOT EXISTS `sp_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sp_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sp_user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `fk_user_roles_role` (`role_id`),
  CONSTRAINT `fk_user_roles_user` FOREIGN KEY (`user_id`) REFERENCES `sp_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`role_id`) REFERENCES `sp_roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sp_role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `fk_role_permissions_permission` (`permission_id`),
  CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `sp_roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `sp_permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Master Data Tables
-- --------------------------------------------------------

-- Clients and Suppliers
CREATE TABLE IF NOT EXISTS `sp_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('company','individual') NOT NULL DEFAULT 'company',
  `name` varchar(255) NOT NULL,
  `phone` varchar(50),
  `email` varchar(255),
  `address` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sp_suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('company','individual') NOT NULL DEFAULT 'company',
  `name` varchar(255) NOT NULL,
  `phone` varchar(50),
  `email` varchar(255),
  `address` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dropdown Management
CREATE TABLE IF NOT EXISTS `sp_dropdowns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` enum('color','brand','car_make','car_model','classification') NOT NULL,
  `value` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_parent` (`parent_id`),
  KEY `idx_category_parent` (`category`, `parent_id`),
  CONSTRAINT `fk_dropdowns_parent` FOREIGN KEY (`parent_id`) REFERENCES `sp_dropdowns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Warehouses
CREATE TABLE IF NOT EXISTS `sp_warehouses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` text,
  `capacity` decimal(10,2) DEFAULT NULL,
  `responsible_name` varchar(255),
  `responsible_email` varchar(255),
  `responsible_phone` varchar(50),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products
CREATE TABLE IF NOT EXISTS `sp_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `classification` varchar(100) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `cost_price` decimal(12,2) DEFAULT 0.00,
  `sale_price` decimal(12,2) DEFAULT 0.00,
  `color` varchar(100),
  `brand` varchar(100),
  `car_make` varchar(100),
  `car_model` varchar(100),
  `total_qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reserved_quotes` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reserved_orders` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_classification` (`classification`),
  KEY `idx_name` (`name`),
  KEY `idx_brand` (`brand`),
  KEY `idx_car_make_model` (`car_make`, `car_model`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Locations (per warehouse)
CREATE TABLE IF NOT EXISTS `sp_product_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `location_label` varchar(255),
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_warehouse` (`product_id`, `warehouse_id`),
  KEY `fk_product_locations_warehouse` (`warehouse_id`),
  CONSTRAINT `fk_product_locations_product` FOREIGN KEY (`product_id`) REFERENCES `sp_products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_product_locations_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `sp_warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Sales Flow Tables
-- --------------------------------------------------------

-- Quotes
CREATE TABLE IF NOT EXISTS `sp_quotes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `status` enum('sent','approved','rejected') NOT NULL DEFAULT 'sent',
  `items_subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `items_tax_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `items_discount_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `global_tax_type` enum('percent','amount') DEFAULT 'percent',
  `global_tax_value` decimal(10,2) DEFAULT 0.00,
  `global_discount_type` enum('percent','amount') DEFAULT 'percent',
  `global_discount_value` decimal(10,2) DEFAULT 0.00,
  `tax_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_quotes_client` (`client_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_quotes_client` FOREIGN KEY (`client_id`) REFERENCES `sp_clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sp_quote_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quote_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_type` enum('percent','amount') NOT NULL DEFAULT 'percent',
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_type` enum('percent','amount') NOT NULL DEFAULT 'percent',
  PRIMARY KEY (`id`),
  KEY `fk_quote_items_quote` (`quote_id`),
  KEY `fk_quote_items_product` (`product_id`),
  CONSTRAINT `fk_quote_items_quote` FOREIGN KEY (`quote_id`) REFERENCES `sp_quotes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_quote_items_product` FOREIGN KEY (`product_id`) REFERENCES `sp_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sales Orders
CREATE TABLE IF NOT EXISTS `sp_sales_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `quote_id` int(11) DEFAULT NULL,
  `status` enum('open','delivered','rejected') NOT NULL DEFAULT 'open',
  `items_subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `items_tax_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `items_discount_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `global_tax_type` enum('percent','amount') DEFAULT 'percent',
  `global_tax_value` decimal(10,2) DEFAULT 0.00,
  `global_discount_type` enum('percent','amount') DEFAULT 'percent',
  `global_discount_value` decimal(10,2) DEFAULT 0.00,
  `tax_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_sales_orders_client` (`client_id`),
  KEY `fk_sales_orders_quote` (`quote_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_sales_orders_client` FOREIGN KEY (`client_id`) REFERENCES `sp_clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sales_orders_quote` FOREIGN KEY (`quote_id`) REFERENCES `sp_quotes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sp_sales_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sales_order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_type` enum('percent','amount') NOT NULL DEFAULT 'percent',
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_type` enum('percent','amount') NOT NULL DEFAULT 'percent',
  PRIMARY KEY (`id`),
  KEY `fk_sales_order_items_so` (`sales_order_id`),
  KEY `fk_sales_order_items_product` (`product_id`),
  CONSTRAINT `fk_sales_order_items_so` FOREIGN KEY (`sales_order_id`) REFERENCES `sp_sales_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sales_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `sp_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Invoices
CREATE TABLE IF NOT EXISTS `sp_invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `sales_order_id` int(11) DEFAULT NULL,
  `status` enum('open','partial','paid','void') NOT NULL DEFAULT 'open',
  `items_subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `items_tax_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `items_discount_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `global_tax_type` enum('percent','amount') DEFAULT 'percent',
  `global_tax_value` decimal(10,2) DEFAULT 0.00,
  `global_discount_type` enum('percent','amount') DEFAULT 'percent',
  `global_discount_value` decimal(10,2) DEFAULT 0.00,
  `tax_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `paid_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_invoices_client` (`client_id`),
  KEY `fk_invoices_sales_order` (`sales_order_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_invoices_client` FOREIGN KEY (`client_id`) REFERENCES `sp_clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_invoices_sales_order` FOREIGN KEY (`sales_order_id`) REFERENCES `sp_sales_orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sp_invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_type` enum('percent','amount') NOT NULL DEFAULT 'percent',
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_type` enum('percent','amount') NOT NULL DEFAULT 'percent',
  PRIMARY KEY (`id`),
  KEY `fk_invoice_items_invoice` (`invoice_id`),
  KEY `fk_invoice_items_product` (`product_id`),
  CONSTRAINT `fk_invoice_items_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `sp_invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_invoice_items_product` FOREIGN KEY (`product_id`) REFERENCES `sp_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments
CREATE TABLE IF NOT EXISTS `sp_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `method` varchar(100) NOT NULL DEFAULT 'cash',
  `note` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_payments_invoice` (`invoice_id`),
  KEY `fk_payments_client` (`client_id`),
  KEY `idx_method` (`method`),
  CONSTRAINT `fk_payments_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `sp_invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payments_client` FOREIGN KEY (`client_id`) REFERENCES `sp_clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Movements
CREATE TABLE IF NOT EXISTS `sp_stock_movements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `direction` enum('in','out') NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `ref_table` varchar(100),
  `ref_id` int(11),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_stock_movements_product` (`product_id`),
  KEY `idx_direction` (`direction`),
  KEY `idx_ref` (`ref_table`, `ref_id`),
  CONSTRAINT `fk_stock_movements_product` FOREIGN KEY (`product_id`) REFERENCES `sp_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
