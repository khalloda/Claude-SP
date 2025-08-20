-- Spare Parts Management System Seed Data
-- This script is idempotent and safe to re-run

USE `sp_main`;

-- Insert admin user if not exists
INSERT IGNORE INTO `sp_users` (`id`, `name`, `email`, `password_hash`, `locale`) VALUES
(1, 'System Administrator', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'en');
-- Password: Admin@123

-- Insert basic roles if not exist
INSERT IGNORE INTO `sp_roles` (`id`, `name`, `description`) VALUES
(1, 'Admin', 'Full system access'),
(2, 'Manager', 'Management access'),
(3, 'User', 'Basic user access');

-- Insert basic permissions if not exist
INSERT IGNORE INTO `sp_permissions` (`id`, `name`, `description`) VALUES
(1, 'manage_users', 'Manage users and permissions'),
(2, 'manage_clients', 'Manage clients'),
(3, 'manage_suppliers', 'Manage suppliers'),
(4, 'manage_products', 'Manage products'),
(5, 'manage_quotes', 'Manage quotes'),
(6, 'manage_orders', 'Manage sales orders'),
(7, 'manage_invoices', 'Manage invoices'),
(8, 'manage_payments', 'Manage payments'),
(9, 'view_reports', 'View reports'),
(10, 'manage_warehouses', 'Manage warehouses');

-- Assign admin role to admin user
INSERT IGNORE INTO `sp_user_roles` (`user_id`, `role_id`) VALUES (1, 1);

-- Assign all permissions to admin role
INSERT IGNORE INTO `sp_role_permissions` (`role_id`, `permission_id`) 
SELECT 1, id FROM `sp_permissions`;

-- Insert sample dropdown values
INSERT IGNORE INTO `sp_dropdowns` (`id`, `category`, `value`, `parent_id`) VALUES
-- Classifications
(1, 'classification', 'Engine Parts', NULL),
(2, 'classification', 'Body Parts', NULL),
(3, 'classification', 'Electrical', NULL),
(4, 'classification', 'Filters', NULL),

-- Colors
(10, 'color', 'Black', NULL),
(11, 'color', 'White', NULL),
(12, 'color', 'Red', NULL),
(13, 'color', 'Blue', NULL),
(14, 'color', 'Silver', NULL),

-- Brands
(20, 'brand', 'OEM', NULL),
(21, 'brand', 'Aftermarket', NULL),
(22, 'brand', 'Bosch', NULL),
(23, 'brand', 'NGK', NULL),
(24, 'brand', 'Denso', NULL),

-- Car Makes
(30, 'car_make', 'Toyota', NULL),
(31, 'car_make', 'Honda', NULL),
(32, 'car_make', 'Ford', NULL),
(33, 'car_make', 'BMW', NULL),
(34, 'car_make', 'Mercedes', NULL),

-- Car Models (Toyota)
(40, 'car_model', 'Camry', 30),
(41, 'car_model', 'Corolla', 30),
(42, 'car_model', 'Prius', 30),
(43, 'car_model', 'RAV4', 30),

-- Car Models (Honda)
(50, 'car_model', 'Civic', 31),
(51, 'car_model', 'Accord', 31),
(52, 'car_model', 'CR-V', 31),

-- Car Models (Ford)
(60, 'car_model', 'Focus', 32),
(61, 'car_model', 'Mustang', 32),
(62, 'car_model', 'F-150', 32),

-- Car Models (BMW)
(70, 'car_model', '3 Series', 33),
(71, 'car_model', '5 Series', 33),
(72, 'car_model', 'X3', 33),

-- Car Models (Mercedes)
(80, 'car_model', 'C-Class', 34),
(81, 'car_model', 'E-Class', 34),
(82, 'car_model', 'GLC', 34);

-- Insert sample clients
INSERT IGNORE INTO `sp_clients` (`id`, `type`, `name`, `phone`, `email`, `address`) VALUES
(1, 'company', 'ABC Auto Parts Ltd', '+1-555-0001', 'contact@abcauto.com', '123 Main Street, City, State 12345'),
(2, 'company', 'XYZ Motors Inc', '+1-555-0002', 'info@xyzmotors.com', '456 Oak Avenue, City, State 67890'),
(3, 'individual', 'John Smith', '+1-555-0003', 'john.smith@email.com', '789 Pine Road, City, State 11111');

-- Insert sample suppliers
INSERT IGNORE INTO `sp_suppliers` (`id`, `type`, `name`, `phone`, `email`, `address`) VALUES
(1, 'company', 'Global Parts Supplier', '+1-555-1001', 'orders@globalparts.com', '100 Industrial Blvd, City, State 22222'),
(2, 'company', 'Auto Components Co', '+1-555-1002', 'sales@autocomponents.com', '200 Commerce Street, City, State 33333'),
(3, 'individual', 'Mike Johnson', '+1-555-1003', 'mike.j@email.com', '300 Business Ave, City, State 44444');

-- Insert sample warehouses
INSERT IGNORE INTO `sp_warehouses` (`id`, `name`, `address`, `capacity`, `responsible_name`, `responsible_email`, `responsible_phone`) VALUES
(1, 'Main Warehouse', '500 Storage Drive, City, State 55555', 10000.00, 'Alice Johnson', 'alice@spareparts.com', '+1-555-2001'),
(2, 'North Branch', '600 North Street, City, State 66666', 5000.00, 'Bob Wilson', 'bob@spareparts.com', '+1-555-2002'),
(3, 'South Branch', '700 South Avenue, City, State 77777', 3000.00, 'Carol Davis', 'carol@spareparts.com', '+1-555-2003');

-- Insert sample products
INSERT IGNORE INTO `sp_products` (`id`, `classification`, `code`, `name`, `cost_price`, `sale_price`, `color`, `brand`, `car_make`, `car_model`, `total_qty`) VALUES
(1, 'Engine Parts', 'ENG0001', 'Oil Filter Standard', 8.50, 12.99, 'Black', 'Bosch', 'Toyota', 'Camry', 50.00),
(2, 'Engine Parts', 'ENG0002', 'Air Filter Element', 15.00, 22.50, 'White', 'OEM', 'Honda', 'Civic', 25.00),
(3, 'Body Parts', 'BDY0001', 'Front Bumper Cover', 125.00, 189.99, 'Black', 'Aftermarket', 'Ford', 'Focus', 5.00),
(4, 'Electrical', 'ELE0001', 'Spark Plug Set', 28.00, 42.99, 'Silver', 'NGK', 'BMW', '3 Series', 15.00),
(5, 'Filters', 'FIL0001', 'Fuel Filter', 12.00, 18.75, 'Black', 'Denso', 'Mercedes', 'C-Class', 30.00);

-- Insert sample product locations
INSERT IGNORE INTO `sp_product_locations` (`product_id`, `warehouse_id`, `location_label`, `qty`) VALUES
(1, 1, 'A1-01', 30.00),
(1, 2, 'B2-05', 20.00),
(2, 1, 'A1-02', 15.00),
(2, 3, 'C3-01', 10.00),
(3, 1, 'A2-10', 3.00),
(3, 2, 'B1-15', 2.00),
(4, 1, 'A1-05', 10.00),
(4, 3, 'C2-08', 5.00),
(5, 1, 'A1-03', 20.00),
(5, 2, 'B2-03', 10.00);
