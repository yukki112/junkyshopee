-- Database Backup
-- Generated: 2025-10-06 12:58:29
-- Database: junkshop

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Table structure for table `attendance_logs`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `attendance_logs`;
CREATE TABLE `attendance_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) DEFAULT NULL,
  `login_time` datetime DEFAULT NULL,
  `logout_time` datetime DEFAULT NULL,
  `method` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_attendance_emp` (`employee_id`),
  CONSTRAINT `fk_attendance_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `attendance_logs`

INSERT INTO `attendance_logs` (`id`, `employee_id`, `login_time`, `logout_time`, `method`) VALUES ('1', '8', '2025-09-26 17:31:15', '2025-09-26 17:39:33', 'Manual');
INSERT INTO `attendance_logs` (`id`, `employee_id`, `login_time`, `logout_time`, `method`) VALUES ('2', '8', '2025-09-26 20:53:50', '2025-09-26 20:54:47', 'Manual');
INSERT INTO `attendance_logs` (`id`, `employee_id`, `login_time`, `logout_time`, `method`) VALUES ('3', '8', '2025-09-29 20:07:14', '2025-09-29 22:49:18', 'Manual');

-- --------------------------------------------------------
-- Table structure for table `custom_report_templates`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `custom_report_templates`;
CREATE TABLE `custom_report_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(100) NOT NULL,
  `report_type` varchar(50) NOT NULL,
  `filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`filters`)),
  `columns` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`columns`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `custom_report_templates_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `customer_activity`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `customer_activity`;
CREATE TABLE `customer_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `activity_date` date NOT NULL,
  `activity_type` enum('purchase','inquiry','return','complaint') NOT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `items_involved` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `recorded_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `customer_activity_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `customer_activity_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `customer_activity`

INSERT INTO `customer_activity` (`id`, `customer_id`, `activity_date`, `activity_type`, `amount`, `items_involved`, `notes`, `recorded_at`, `recorded_by`) VALUES ('6', '1', '2025-07-19', 'purchase', '1358.00', '1', 'Transaction ID: TXN-20250719-00007\nType: Pickup\nStatus: Pending\nItems: 500.00kg Copper Wire', '2025-07-28 02:29:03', '10');
INSERT INTO `customer_activity` (`id`, `customer_id`, `activity_date`, `activity_type`, `amount`, `items_involved`, `notes`, `recorded_at`, `recorded_by`) VALUES ('7', '1', '2025-07-19', 'purchase', '14990.00', '1', 'Transaction ID: TXN-20250719-00008\nType: Pickup\nStatus: Pending\nItems: 12.00kg Aluminum Cans', '2025-07-28 02:29:04', '10');
INSERT INTO `customer_activity` (`id`, `customer_id`, `activity_date`, `activity_type`, `amount`, `items_involved`, `notes`, `recorded_at`, `recorded_by`) VALUES ('8', '1', '2025-07-19', 'purchase', '175.00', '1', 'Transaction ID: TXN-20250719-00015\nType: Pickup\nStatus: Pending\nItems: 50.00kg PET Bottles', '2025-07-28 02:29:04', '10');
INSERT INTO `customer_activity` (`id`, `customer_id`, `activity_date`, `activity_type`, `amount`, `items_involved`, `notes`, `recorded_at`, `recorded_by`) VALUES ('9', '3', '2025-07-19', 'purchase', '600.00', '1', 'Transaction ID: TXN-20250719-00009\nType: Pickup\nStatus: Pending\nItems: 1.00kg Copper Wire', '2025-07-28 02:29:04', '10');
INSERT INTO `customer_activity` (`id`, `customer_id`, `activity_date`, `activity_type`, `amount`, `items_involved`, `notes`, `recorded_at`, `recorded_by`) VALUES ('10', '3', '2025-07-19', 'purchase', '150000.00', '1', 'Transaction ID: TXN-20250719-00010\nType: Pickup\nStatus: Pending\nItems: 4.00kg Aluminum Cans', '2025-07-28 02:29:04', '10');
INSERT INTO `customer_activity` (`id`, `customer_id`, `activity_date`, `activity_type`, `amount`, `items_involved`, `notes`, `recorded_at`, `recorded_by`) VALUES ('11', '3', '2025-07-19', 'purchase', '150000.00', '1', 'Transaction ID: TXN-20250719-00011\nType: Pickup\nStatus: Pending\nItems: 21.00kg Cardboard', '2025-07-28 02:29:04', '10');
INSERT INTO `customer_activity` (`id`, `customer_id`, `activity_date`, `activity_type`, `amount`, `items_involved`, `notes`, `recorded_at`, `recorded_by`) VALUES ('12', '54', '2025-09-27', 'purchase', '12.00', NULL, 'asd', '2025-09-27 00:08:52', '8');

-- --------------------------------------------------------
-- Table structure for table `employee_2fa_recovery`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `employee_2fa_recovery`;
CREATE TABLE `employee_2fa_recovery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `recovery_code` varchar(10) NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_recovery_employee` (`employee_id`),
  CONSTRAINT `fk_recovery_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `employee_performance`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `employee_performance`;
CREATE TABLE `employee_performance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `sales_amount` decimal(12,2) NOT NULL,
  `items_processed` int(11) NOT NULL,
  `customer_interactions` int(11) NOT NULL,
  `efficiency_rating` decimal(3,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `evaluated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `evaluated_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `evaluated_by` (`evaluated_by`),
  CONSTRAINT `employee_performance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`),
  CONSTRAINT `employee_performance_ibfk_2` FOREIGN KEY (`evaluated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `employee_roles`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `employee_roles`;
CREATE TABLE `employee_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `employee_roles`

INSERT INTO `employee_roles` (`id`, `role_name`, `description`, `is_active`) VALUES ('1', 'Cashier', 'Handles customer transactions', '1');
INSERT INTO `employee_roles` (`id`, `role_name`, `description`, `is_active`) VALUES ('2', 'Loader', 'Responsible for item loading and unloading', '1');
INSERT INTO `employee_roles` (`id`, `role_name`, `description`, `is_active`) VALUES ('3', 'Sorter', 'Sorts materials by category or condition', '1');
INSERT INTO `employee_roles` (`id`, `role_name`, `description`, `is_active`) VALUES ('4', 'Encoder', 'Inputs data into the system', '1');
INSERT INTO `employee_roles` (`id`, `role_name`, `description`, `is_active`) VALUES ('5', 'Administrator', 'System Administrator', '1');

-- --------------------------------------------------------
-- Table structure for table `employees`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `role_id` int(11) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `resume` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `address` text DEFAULT NULL,
  `two_factor_auth` tinyint(1) DEFAULT 0,
  `two_factor_secret` varchar(32) DEFAULT NULL,
  `backup_codes` text DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `fk_employee_role` (`role_id`),
  CONSTRAINT `fk_employee_role` FOREIGN KEY (`role_id`) REFERENCES `employee_roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `employees`

INSERT INTO `employees` (`id`, `first_name`, `last_name`, `username`, `password_hash`, `is_verified`, `role_id`, `contact_number`, `email`, `profile_photo`, `resume`, `is_active`, `created_at`, `updated_at`, `address`, `two_factor_auth`, `two_factor_secret`, `backup_codes`, `last_login`) VALUES ('2', 'Andyy', 'Doza', 'adoza', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', '0', '2', '09170000001', 'andy.doza@junkshop.com', NULL, 'uploads/resumes/68847ccf41b31_Questions-forr-Website.docx', '0', '2025-07-26 14:53:16', '2025-10-06 02:07:56', '', '0', NULL, NULL, NULL);
INSERT INTO `employees` (`id`, `first_name`, `last_name`, `username`, `password_hash`, `is_verified`, `role_id`, `contact_number`, `email`, `profile_photo`, `resume`, `is_active`, `created_at`, `updated_at`, `address`, `two_factor_auth`, `two_factor_secret`, `backup_codes`, `last_login`) VALUES ('3', 'Jossell', 'Viray', 'jviray', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', '0', '3', '09170000002', 'jossell.viray@junkshop.com', NULL, NULL, '0', '2025-07-26 14:53:16', '2025-09-29 00:41:30', '', '0', NULL, NULL, NULL);
INSERT INTO `employees` (`id`, `first_name`, `last_name`, `username`, `password_hash`, `is_verified`, `role_id`, `contact_number`, `email`, `profile_photo`, `resume`, `is_active`, `created_at`, `updated_at`, `address`, `two_factor_auth`, `two_factor_secret`, `backup_codes`, `last_login`) VALUES ('5', 'Zaldy', 'Solis', 'solis', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', '0', '1', '09170000004', 'zaldy.solis@junkshop.com', NULL, NULL, '0', '2025-07-26 14:53:16', '2025-09-30 18:40:01', '', '0', NULL, NULL, NULL);
INSERT INTO `employees` (`id`, `first_name`, `last_name`, `username`, `password_hash`, `is_verified`, `role_id`, `contact_number`, `email`, `profile_photo`, `resume`, `is_active`, `created_at`, `updated_at`, `address`, `two_factor_auth`, `two_factor_secret`, `backup_codes`, `last_login`) VALUES ('6', 'stephen', 'viray', 'teamsqu4d', '$2y$10$zs/BZzfUowM73DJSK.yuZu6azRoTdOn8VISL4FGZJVQZNNSJ3vpS2', '1', '2', '09070204324', 'stessssphenviray12@gmail.comasd', 'uploads/employee_avatars/employee_avatar_6_1759178933.png', 'uploads/resumes/68847cf2e3e1c_ITE4-Proposal (1) (1).docx', '0', '2025-07-26 15:00:02', '2025-09-30 18:39:46', '', '0', NULL, NULL, NULL);
INSERT INTO `employees` (`id`, `first_name`, `last_name`, `username`, `password_hash`, `is_verified`, `role_id`, `contact_number`, `email`, `profile_photo`, `resume`, `is_active`, `created_at`, `updated_at`, `address`, `two_factor_auth`, `two_factor_secret`, `backup_codes`, `last_login`) VALUES ('8', 'Yukki', 'Yokai', 'Yupi', '$2y$10$d137D/khucPIDC5L2mvwkObFrSmVJv3I3ELzTlNsEzR.zoSdlEqKu', '1', '1', '09070204324', 'staycsobad12223@gmail.com', 'uploads/employee_avatars/employee_avatar_8_1759088114.png', 'uploads/resumes/688f422702a1d_ORDER-FORM-1 (1).pdf', '1', '2025-08-03 19:04:07', '2025-09-29 03:35:14', '054 gold extention\r\n', '0', 'DVSHQI37ESW3MF6I', NULL, NULL);
INSERT INTO `employees` (`id`, `first_name`, `last_name`, `username`, `password_hash`, `is_verified`, `role_id`, `contact_number`, `email`, `profile_photo`, `resume`, `is_active`, `created_at`, `updated_at`, `address`, `two_factor_auth`, `two_factor_secret`, `backup_codes`, `last_login`) VALUES ('9', 'aye', 'rodrigez', 'aye', '$2y$10$YIno5hDWT2yN2bo12WqOI.DsIuvq6zf9y3a61DybCVExv7E8snHXG', '1', '1', '', 'asdasd@asdasd.asd', NULL, NULL, '0', '2025-09-27 03:11:13', '2025-09-30 18:39:35', '', '0', NULL, NULL, NULL);
INSERT INTO `employees` (`id`, `first_name`, `last_name`, `username`, `password_hash`, `is_verified`, `role_id`, `contact_number`, `email`, `profile_photo`, `resume`, `is_active`, `created_at`, `updated_at`, `address`, `two_factor_auth`, `two_factor_secret`, `backup_codes`, `last_login`) VALUES ('10', 'Mariefe', 'Baturi', 's2xwoooos', '$2y$10$HwmyEBOR12Q2yn0YAMz0i.bS4ImfP5lFUGzrs/jWJFhWBGEQ9T6jm', '1', '1', '09984319585', 'asdasdasd@jklahsd.com', 'uploads/employee_avatars/employee_avatar_10_1759083346.png', 'uploads/resumes/68d97a7dcb1a1_LP-20250929-6237.pdf', '1', '2025-09-29 02:12:13', '2025-09-29 02:15:46', 'asdasdasdasd', '0', NULL, NULL, NULL);
INSERT INTO `employees` (`id`, `first_name`, `last_name`, `username`, `password_hash`, `is_verified`, `role_id`, `contact_number`, `email`, `profile_photo`, `resume`, `is_active`, `created_at`, `updated_at`, `address`, `two_factor_auth`, `two_factor_secret`, `backup_codes`, `last_login`) VALUES ('11', 'Mics', 'Trinidad', 'mics', '$2y$10$0OagRP3DV15Z60ry4uNDOu2l2Kj8FOFX18MUWrsg.1Y0ElmqN6gWe', '1', '1', '097456498753', 'bachaomicaela@gmail.com', NULL, 'uploads/resumes/68d9871b40d7b_Viray, Excuse letter day 1.docx', '0', '2025-09-29 03:06:03', '2025-09-30 18:39:41', '', '0', NULL, NULL, NULL);
INSERT INTO `employees` (`id`, `first_name`, `last_name`, `username`, `password_hash`, `is_verified`, `role_id`, `contact_number`, `email`, `profile_photo`, `resume`, `is_active`, `created_at`, `updated_at`, `address`, `two_factor_auth`, `two_factor_secret`, `backup_codes`, `last_login`) VALUES ('13', 'Yukki', '', 'admin_43', '$2y$10$dYKgBRxrQ.Oc5PulHIelJOXN.adatMMWFGsxhmWEkD4b8cq4WLuw6', '0', '5', NULL, 'kahitbatapaako123@gmail.com', NULL, NULL, '1', '2025-09-30 17:30:10', '2025-09-30 17:30:10', NULL, '0', NULL, NULL, NULL);

-- --------------------------------------------------------
-- Table structure for table `inventory_audit`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `inventory_audit`;
CREATE TABLE `inventory_audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `action_type` enum('create','update','delete','restock','deduction') NOT NULL,
  `user_id` int(11) NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_audit_item` (`item_id`),
  KEY `fk_audit_user` (`user_id`),
  CONSTRAINT `fk_audit_item` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `inventory_audit`

INSERT INTO `inventory_audit` (`id`, `item_id`, `action_type`, `user_id`, `old_values`, `new_values`, `timestamp`, `notes`) VALUES ('1', '1', 'update', '1', '{\"current_stock\": 100.00}', '{\"current_stock\": 85.50}', '2025-07-25 15:14:18', 'Stock updated after sale');

-- --------------------------------------------------------
-- Table structure for table `inventory_category_relations`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `inventory_category_relations`;
CREATE TABLE `inventory_category_relations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_relation` (`parent_id`,`child_id`),
  KEY `fk_child_category` (`child_id`),
  CONSTRAINT `fk_child_category` FOREIGN KEY (`child_id`) REFERENCES `item_categories` (`id`),
  CONSTRAINT `fk_parent_category` FOREIGN KEY (`parent_id`) REFERENCES `item_categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `inventory_category_relations`

INSERT INTO `inventory_category_relations` (`id`, `parent_id`, `child_id`) VALUES ('1', '1', '2');

-- --------------------------------------------------------
-- Table structure for table `inventory_items`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `inventory_items`;
CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `material_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `condition_id` int(11) DEFAULT NULL,
  `item_name` varchar(100) NOT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `initial_stock` decimal(10,2) DEFAULT 0.00,
  `unit` varchar(20) DEFAULT 'kg',
  `image_url` varchar(255) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `user_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_restock_date` date DEFAULT NULL,
  `current_stock` decimal(10,2) DEFAULT 0.00,
  `min_stock_level` decimal(10,2) DEFAULT 10.00,
  `low_stock_threshold` decimal(10,2) DEFAULT 10.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `barcode_unique` (`barcode`),
  KEY `fk_inventory_material` (`material_id`),
  KEY `fk_inventory_category` (`category_id`),
  KEY `fk_item_condition` (`condition_id`),
  KEY `fk_inventory_user` (`user_id`),
  KEY `fk_item_supplier` (`supplier_id`),
  CONSTRAINT `fk_inventory_category` FOREIGN KEY (`category_id`) REFERENCES `item_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inventory_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inventory_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_item_condition` FOREIGN KEY (`condition_id`) REFERENCES `item_conditions` (`id`),
  CONSTRAINT `fk_item_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `inventory_suppliers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `inventory_items`

INSERT INTO `inventory_items` (`id`, `material_id`, `category_id`, `condition_id`, `item_name`, `barcode`, `description`, `initial_stock`, `unit`, `image_url`, `location`, `notes`, `is_active`, `user_id`, `supplier_id`, `created_at`, `updated_at`, `last_restock_date`, `current_stock`, `min_stock_level`, `low_stock_threshold`) VALUES ('1', '6', '1', '2', 'Copper Wire Scrap', 'CW001', 'Used copper wires from electronics', '100.00', 'kg', NULL, 'Warehouse A', 'High quality copper', '1', '1', '1', '2025-07-25 15:14:18', '2025-08-09 14:53:20', '2025-07-20', '150.50', '10.00', '10.00');
INSERT INTO `inventory_items` (`id`, `material_id`, `category_id`, `condition_id`, `item_name`, `barcode`, `description`, `initial_stock`, `unit`, `image_url`, `location`, `notes`, `is_active`, `user_id`, `supplier_id`, `created_at`, `updated_at`, `last_restock_date`, `current_stock`, `min_stock_level`, `low_stock_threshold`) VALUES ('2', '28', '1', '2', 'Bakal ', 'asdasd', 'asdasd', '1.00', 'kg', NULL, '', '', '1', '10', '1', '2025-07-25 22:43:51', '2025-09-27 02:14:12', NULL, '52.00', '2.00', '2.00');
INSERT INTO `inventory_items` (`id`, `material_id`, `category_id`, `condition_id`, `item_name`, `barcode`, `description`, `initial_stock`, `unit`, `image_url`, `location`, `notes`, `is_active`, `user_id`, `supplier_id`, `created_at`, `updated_at`, `last_restock_date`, `current_stock`, `min_stock_level`, `low_stock_threshold`) VALUES ('4', '30', '1', NULL, 'Computer Parts Scrap', NULL, NULL, '0.00', 'kg', NULL, NULL, NULL, '1', NULL, NULL, '2025-08-09 13:38:16', '2025-10-02 13:10:14', NULL, '54.00', '10.00', '10.00');
INSERT INTO `inventory_items` (`id`, `material_id`, `category_id`, `condition_id`, `item_name`, `barcode`, `description`, `initial_stock`, `unit`, `image_url`, `location`, `notes`, `is_active`, `user_id`, `supplier_id`, `created_at`, `updated_at`, `last_restock_date`, `current_stock`, `min_stock_level`, `low_stock_threshold`) VALUES ('5', '43', '1', NULL, 'Batteries Scrap', NULL, NULL, '0.00', 'kg', NULL, NULL, NULL, '1', NULL, NULL, '2025-08-09 13:47:38', '2025-09-29 03:39:58', NULL, '44.00', '10.00', '10.00');
INSERT INTO `inventory_items` (`id`, `material_id`, `category_id`, `condition_id`, `item_name`, `barcode`, `description`, `initial_stock`, `unit`, `image_url`, `location`, `notes`, `is_active`, `user_id`, `supplier_id`, `created_at`, `updated_at`, `last_restock_date`, `current_stock`, `min_stock_level`, `low_stock_threshold`) VALUES ('6', '9', '1', NULL, 'Stainless Steel Scrap', NULL, NULL, '0.00', 'kg', NULL, NULL, NULL, '1', NULL, NULL, '2025-08-09 13:49:20', '2025-10-06 00:01:47', NULL, '46.00', '10.00', '10.00');
INSERT INTO `inventory_items` (`id`, `material_id`, `category_id`, `condition_id`, `item_name`, `barcode`, `description`, `initial_stock`, `unit`, `image_url`, `location`, `notes`, `is_active`, `user_id`, `supplier_id`, `created_at`, `updated_at`, `last_restock_date`, `current_stock`, `min_stock_level`, `low_stock_threshold`) VALUES ('7', '10', '1', NULL, 'E-Waste Scrap', NULL, NULL, '0.00', 'kg', NULL, NULL, NULL, '1', NULL, NULL, '2025-08-09 13:49:20', '2025-10-02 12:40:16', NULL, '23.00', '10.00', '10.00');
INSERT INTO `inventory_items` (`id`, `material_id`, `category_id`, `condition_id`, `item_name`, `barcode`, `description`, `initial_stock`, `unit`, `image_url`, `location`, `notes`, `is_active`, `user_id`, `supplier_id`, `created_at`, `updated_at`, `last_restock_date`, `current_stock`, `min_stock_level`, `low_stock_threshold`) VALUES ('8', '42', '1', NULL, 'Yero (Corrugated Sheets) Scrap', NULL, NULL, '0.00', 'kg', NULL, NULL, NULL, '1', NULL, NULL, '2025-08-09 13:49:20', '2025-10-02 13:10:06', NULL, '314.00', '10.00', '10.00');
INSERT INTO `inventory_items` (`id`, `material_id`, `category_id`, `condition_id`, `item_name`, `barcode`, `description`, `initial_stock`, `unit`, `image_url`, `location`, `notes`, `is_active`, `user_id`, `supplier_id`, `created_at`, `updated_at`, `last_restock_date`, `current_stock`, `min_stock_level`, `low_stock_threshold`) VALUES ('9', '8', '1', NULL, 'Iron Scrap Scrap', NULL, NULL, '0.00', 'kg', NULL, NULL, NULL, '1', NULL, NULL, '2025-08-09 13:49:20', '2025-10-06 00:02:07', NULL, '290.00', '10.00', '10.00');
INSERT INTO `inventory_items` (`id`, `material_id`, `category_id`, `condition_id`, `item_name`, `barcode`, `description`, `initial_stock`, `unit`, `image_url`, `location`, `notes`, `is_active`, `user_id`, `supplier_id`, `created_at`, `updated_at`, `last_restock_date`, `current_stock`, `min_stock_level`, `low_stock_threshold`) VALUES ('10', '29', '1', NULL, 'Glass Bottles Scrap', NULL, NULL, '0.00', 'kg', NULL, NULL, NULL, '1', NULL, NULL, '2025-08-09 14:51:28', '2025-09-27 02:14:35', NULL, '66.00', '10.00', '10.00');
INSERT INTO `inventory_items` (`id`, `material_id`, `category_id`, `condition_id`, `item_name`, `barcode`, `description`, `initial_stock`, `unit`, `image_url`, `location`, `notes`, `is_active`, `user_id`, `supplier_id`, `created_at`, `updated_at`, `last_restock_date`, `current_stock`, `min_stock_level`, `low_stock_threshold`) VALUES ('11', '26', '1', NULL, 'PET Bottles Scrap', NULL, NULL, '0.00', 'kg', NULL, NULL, NULL, '1', NULL, NULL, '2025-08-09 14:53:20', '2025-08-09 14:53:20', NULL, '21.00', '10.00', '10.00');
INSERT INTO `inventory_items` (`id`, `material_id`, `category_id`, `condition_id`, `item_name`, `barcode`, `description`, `initial_stock`, `unit`, `image_url`, `location`, `notes`, `is_active`, `user_id`, `supplier_id`, `created_at`, `updated_at`, `last_restock_date`, `current_stock`, `min_stock_level`, `low_stock_threshold`) VALUES ('12', '27', '1', NULL, 'Cardboard Scrap', NULL, NULL, '0.00', 'kg', NULL, NULL, NULL, '1', NULL, NULL, '2025-08-09 15:16:57', '2025-09-27 02:14:27', NULL, '96.00', '10.00', '10.00');

-- --------------------------------------------------------
-- Table structure for table `inventory_logs`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `inventory_logs`;
CREATE TABLE `inventory_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inventory_item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_type` enum('addition','deduction','edit') NOT NULL,
  `quantity_change` decimal(10,2) NOT NULL,
  `previous_stock` decimal(10,2) DEFAULT NULL,
  `new_stock` decimal(10,2) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_log_inventory_item` (`inventory_item_id`),
  KEY `fk_log_user` (`user_id`),
  CONSTRAINT `fk_log_inventory_item` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `inventory_logs`

INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('1', '1', '1', 'deduction', '14.50', '100.00', '85.50', 'Sold to customer', '2025-07-25 15:14:18');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('2', '1', '3', 'addition', '25.00', '85.50', '110.50', 'New shipment from supplier', '2025-07-21 09:15:00');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('3', '1', '1', 'addition', '10.25', '110.50', '100.25', 'Sold to walk-in customer', '2025-07-21 14:30:00');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('4', '1', '1', 'deduction', '5.75', '100.25', '94.50', 'Used for in-house recycling project', '2025-07-22 11:20:00');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('5', '1', '1', 'addition', '15.50', '94.50', '110.00', 'Restock from warehouse B', '2025-07-23 10:00:00');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('6', '1', '1', 'deduction', '20.00', '110.00', '90.00', 'Bulk order for manufacturing client', '2025-07-24 16:45:00');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('7', '1', '10', 'deduction', '1.00', '85.50', '84.50', 'asd', '2025-07-25 22:42:31');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('8', '1', '10', 'deduction', '1.00', '84.50', '83.50', 'ad', '2025-07-25 22:42:38');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('9', '2', '10', 'addition', '1.00', '0.00', '1.00', 'Initial stock addition', '2025-07-25 22:43:51');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('10', '4', '8', 'addition', '12.00', '0.00', '12.00', 'Purchase from asd', '2025-08-09 13:38:16');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('11', '1', '8', 'addition', '21.00', '83.50', '104.50', 'Purchase from solis', '2025-08-09 13:47:38');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('12', '5', '8', 'addition', '31.00', '0.00', '31.00', 'Purchase from solis', '2025-08-09 13:47:38');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('13', '4', '8', 'addition', '1.00', '12.00', '13.00', 'Purchase from solis', '2025-08-09 13:47:38');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('14', '1', '8', 'addition', '1.00', '104.50', '105.50', 'Purchase from solis', '2025-08-09 13:49:20');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('15', '6', '8', 'addition', '2.00', '0.00', '2.00', 'Purchase from solis', '2025-08-09 13:49:20');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('16', '7', '8', 'addition', '4.00', '0.00', '4.00', 'Purchase from solis', '2025-08-09 13:49:20');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('17', '8', '8', 'addition', '5.00', '0.00', '5.00', 'Purchase from solis', '2025-08-09 13:49:20');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('18', '5', '8', 'addition', '6.00', '31.00', '37.00', 'Purchase from solis', '2025-08-09 13:49:20');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('19', '9', '8', 'addition', '1.00', '0.00', '1.00', 'Purchase from solis', '2025-08-09 13:49:20');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('20', '1', '8', 'addition', '2.00', '105.50', '107.50', 'Purchase from Aye', '2025-08-09 14:01:47');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('21', '5', '8', 'addition', '1.00', '37.00', '38.00', 'Purchase from Aye', '2025-08-09 14:01:47');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('22', '8', '8', 'addition', '1.00', '5.00', '6.00', 'Purchase from solis', '2025-08-09 14:02:38');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('23', '1', '8', 'addition', '21.00', '107.50', '128.50', 'Purchase from aye rodrigez', '2025-08-09 14:36:06');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('24', '4', '8', 'addition', '2.00', '13.00', '15.00', 'Purchase from aye rodrigez', '2025-08-09 14:36:06');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('25', '5', '8', 'addition', '1.00', '38.00', '39.00', 'Purchase from aye rodrigez', '2025-08-09 14:36:06');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('26', '7', '8', 'addition', '1.00', '4.00', '5.00', 'Purchase from asdasd', '2025-08-09 14:36:34');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('27', '7', '8', 'addition', '1.00', '5.00', '6.00', 'Purchase from asd', '2025-08-09 14:37:53');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('28', '7', '8', 'addition', '1.00', '6.00', '7.00', 'Purchase from Aye', '2025-08-09 14:39:23');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('29', '6', '8', 'addition', '1.00', '2.00', '3.00', 'Purchase from Aye', '2025-08-09 14:40:48');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('30', '8', '8', 'addition', '1.00', '6.00', '7.00', 'Purchase from solis', '2025-08-09 14:41:17');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('31', '6', '8', 'addition', '1.00', '3.00', '4.00', 'Purchase from asd', '2025-08-09 14:48:37');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('32', '1', '8', 'addition', '1.00', '128.50', '129.50', 'Purchase from aye rodrigez', '2025-08-09 14:51:28');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('33', '5', '8', 'addition', '2.00', '39.00', '41.00', 'Purchase from aye rodrigez', '2025-08-09 14:51:28');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('34', '9', '8', 'addition', '1.00', '1.00', '2.00', 'Purchase from aye rodrigez', '2025-08-09 14:51:28');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('35', '10', '8', 'addition', '4.00', '0.00', '4.00', 'Purchase from aye rodrigez', '2025-08-09 14:51:28');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('36', '1', '8', 'addition', '21.00', '129.50', '150.50', 'Purchase from bing solis', '2025-08-09 14:53:20');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('37', '5', '8', 'addition', '1.00', '41.00', '42.00', 'Purchase from bing solis', '2025-08-09 14:53:20');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('38', '11', '8', 'addition', '21.00', '0.00', '21.00', 'Purchase from bing solis', '2025-08-09 14:53:20');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('39', '4', '8', 'addition', '1.00', '15.00', '16.00', 'Purchase from bing solis', '2025-08-09 14:53:20');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('40', '9', '8', 'addition', '21.00', '2.00', '23.00', 'Purchase from bing solis', '2025-08-09 14:53:20');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('41', '9', '8', 'addition', '1.00', '23.00', '24.00', 'Purchase from asd', '2025-08-09 14:54:24');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('45', '12', '8', 'addition', '1.00', '0.00', '1.00', 'Purchase from mics trinidad', '2025-08-09 15:16:57');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('46', '9', '8', 'addition', '1.00', '24.00', '25.00', 'Purchase from solos', '2025-08-09 15:18:16');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('49', '9', '8', 'addition', '1.00', '25.00', '26.00', 'Purchase from mics trinidad', '2025-08-09 15:32:53');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('50', '9', '8', 'addition', '121.00', '26.00', '147.00', 'Purchase from asd', '2025-08-09 15:33:28');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('51', '6', '8', 'addition', '12.00', '4.00', '16.00', 'Purchase from yukki', '2025-08-09 15:35:02');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('52', '7', '8', 'addition', '3.00', '7.00', '10.00', 'Purchase from asd', '2025-08-09 16:19:04');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('53', '9', '8', 'addition', '2.00', '147.00', '149.00', 'Purchase from asd', '2025-08-09 16:19:44');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('54', '7', '8', 'deduction', '1.00', '10.00', '9.00', 'Sale to Solis', '2025-08-09 16:20:36');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('55', '9', '8', 'addition', '1.00', '149.00', '150.00', 'Purchase from asd', '2025-08-09 16:24:16');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('56', '9', '8', 'addition', '121.00', '150.00', '271.00', 'Purchase from solis', '2025-08-09 16:26:55');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('57', '6', '8', 'addition', '21.00', '16.00', '37.00', 'Purchase from asd', '2025-08-15 11:33:10');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('59', '5', '8', 'addition', '1.00', '42.00', '43.00', 'Purchase from Hanni Pham', '2025-08-15 11:36:31');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('60', '4', '8', 'addition', '2.00', '16.00', '18.00', 'Purchase from Hanni Pham', '2025-08-15 11:36:31');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('61', '2', '8', 'addition', '1.00', '1.00', '2.00', 'Purchase from Hanni Pham', '2025-08-15 11:36:31');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('62', '10', '8', 'addition', '2.00', '4.00', '6.00', 'Purchase from Hanni Pham', '2025-08-15 11:36:31');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('63', '6', '8', 'addition', '1.00', '37.00', '38.00', 'Purchase from Hanni Pham', '2025-08-15 11:36:31');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('64', '12', '8', 'addition', '2.00', '1.00', '3.00', 'Purchase from Hanni Pham', '2025-08-15 11:36:31');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('65', '9', '8', 'addition', '1.00', '271.00', '272.00', 'Purchase from Hanni Pham', '2025-08-15 11:36:31');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('66', '4', '8', 'addition', '1.00', '18.00', '19.00', 'Purchase from Hanni Pham', '2025-08-15 11:36:31');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('67', '4', '8', 'addition', '1.00', '19.00', '20.00', 'Purchase from mics trinidad', '2025-08-28 14:42:43');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('68', '7', '8', 'addition', '2.00', '9.00', '11.00', 'Purchase from mics trinidad', '2025-08-28 14:42:43');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('69', '8', '8', 'addition', '3.00', '7.00', '10.00', 'Purchase from mics trinidad', '2025-08-28 14:42:43');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('75', '7', '8', 'addition', '1.00', '11.00', '12.00', 'Purchase from solis', '2025-08-28 15:09:05');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('76', '9', '8', 'addition', '2.00', '272.00', '274.00', 'Purchase from solis', '2025-08-28 15:09:05');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('77', '7', '8', 'addition', '3.00', '12.00', '15.00', 'Purchase from solis', '2025-08-28 15:09:05');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('80', '6', '8', 'addition', '1.00', '38.00', '39.00', 'Purchase from solis', '2025-08-28 15:12:19');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('85', '4', '8', 'addition', '1.00', '20.00', '21.00', 'Purchase from solis', '2025-08-28 15:39:13');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('86', '9', '8', 'addition', '2.00', '274.00', '276.00', 'Purchase from mics trinidad', '2025-08-28 15:39:25');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('87', '6', '8', 'addition', '2.00', '39.00', '41.00', 'Purchase from Aye', '2025-08-28 15:39:40');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('88', '8', '8', 'deduction', '1.00', '10.00', '9.00', 'Sale to Solis', '2025-08-28 15:39:48');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('89', '6', '8', 'deduction', '1.00', '41.00', '40.00', 'Sale to Solis', '2025-08-28 15:39:56');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('90', '7', '8', 'addition', '3.00', '15.00', '18.00', 'Purchase from solisss', '2025-08-28 15:41:05');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('91', '7', '8', 'addition', '1.00', '18.00', '19.00', 'Purchase from mics trinidad', '2025-08-28 15:41:24');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('92', '4', '8', 'addition', '2.00', '21.00', '23.00', 'Purchase from mics trinidad', '2025-08-28 15:41:24');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('93', '8', '8', 'addition', '2.00', '9.00', '11.00', 'Purchase from mics trinidad', '2025-08-28 15:41:24');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('94', '6', '8', 'addition', '1.00', '40.00', '41.00', 'Purchase from mics trinidad', '2025-08-28 15:41:56');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('95', '9', '8', 'addition', '1.00', '276.00', '277.00', 'Purchase from aye rodrigez', '2025-08-28 15:42:42');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('96', '2', '43', 'addition', '50.00', '2.00', '52.00', 'hjk', '2025-09-27 02:14:12');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('97', '12', '43', 'addition', '6.00', '3.00', '9.00', 'jkl', '2025-09-27 02:14:18');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('98', '12', '43', 'addition', '87.00', '9.00', '96.00', 'jkl', '2025-09-27 02:14:27');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('99', '10', '43', 'addition', '60.00', '6.00', '66.00', 'ghj', '2025-09-27 02:14:35');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('100', '4', '8', 'addition', '1.00', '23.00', '24.00', 'Purchase from stephen viray', '2025-09-29 02:56:41');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('101', '7', '8', 'addition', '1.00', '19.00', '20.00', 'Purchase from mics trinidad', '2025-09-29 02:57:12');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('102', '8', '8', 'addition', '2.00', '11.00', '13.00', 'Purchase from mics trinidad', '2025-09-29 02:57:12');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('103', '9', '8', 'addition', '1.00', '277.00', '278.00', 'Purchase from stephen viray', '2025-09-29 03:12:50');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('104', '6', '8', 'addition', '1.00', '41.00', '42.00', 'Purchase from stephen viray', '2025-09-29 03:20:06');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('105', '7', '8', 'addition', '2.00', '20.00', '22.00', 'Purchase from zdc', '2025-09-29 03:21:23');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('106', '6', '8', 'addition', '1.00', '42.00', '43.00', 'Purchase from Hanni Pham', '2025-09-29 03:38:30');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('107', '5', '8', 'addition', '1.00', '43.00', '44.00', 'Purchase from Hanni Pham', '2025-09-29 03:39:58');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('108', '7', '8', 'addition', '1.00', '22.00', '23.00', 'Purchase from Hanni Pham', '2025-09-29 03:45:12');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('109', '6', '8', 'addition', '1.00', '43.00', '44.00', 'Purchase from Hanni Pham', '2025-09-29 03:50:16');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('110', '6', '8', 'addition', '1.00', '44.00', '45.00', 'Purchase from Hanni Pham', '2025-09-29 04:16:40');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('111', '9', '8', 'addition', '12.00', '278.00', '290.00', 'Purchase from stephen viray', '2025-10-02 12:27:54');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('112', '9', '8', 'deduction', '1.00', '290.00', '289.00', 'Sale to stephen viray', '2025-10-02 12:39:46');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('113', '7', '8', 'deduction', '1.00', '23.00', '22.00', 'Sale to stephen viray', '2025-10-02 12:39:55');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('114', '7', '8', 'addition', '1.00', '22.00', '23.00', 'Purchase from stephen viray', '2025-10-02 12:40:16');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('115', '4', '8', 'addition', '21.00', '24.00', '45.00', 'Purchase from stephen viray', '2025-10-02 12:40:27');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('116', '8', '8', 'addition', '1.00', '13.00', '14.00', 'Purchase from stephen viray', '2025-10-02 13:02:36');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('117', '6', '8', 'deduction', '1.00', '45.00', '44.00', 'Sale to stephen viray', '2025-10-02 13:09:17');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('118', '6', '8', 'addition', '1.00', '44.00', '45.00', 'Purchase from stephen viray', '2025-10-02 13:09:32');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('119', '4', '8', 'addition', '21.00', '45.00', '66.00', 'Purchase from stephen viray', '2025-10-02 13:09:46');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('120', '8', '8', 'addition', '300.00', '14.00', '314.00', 'Purchase from stephen viray', '2025-10-02 13:10:06');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('121', '4', '8', 'deduction', '12.00', '66.00', '54.00', 'Sale to stephen viray', '2025-10-02 13:10:14');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('122', '6', '8', 'addition', '1.00', '45.00', '46.00', 'Purchase from asdasd', '2025-10-06 00:01:47');
INSERT INTO `inventory_logs` (`id`, `inventory_item_id`, `user_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `reason`, `created_at`) VALUES ('123', '9', '8', 'addition', '1.00', '289.00', '290.00', 'Purchase from stephen viray', '2025-10-06 00:02:07');

-- --------------------------------------------------------
-- Table structure for table `inventory_movement`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `inventory_movement`;
CREATE TABLE `inventory_movement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `movement_type` enum('in','out') NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_value` decimal(12,2) NOT NULL,
  `movement_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `inventory_movement_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`),
  CONSTRAINT `inventory_movement_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `inventory_suppliers`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `inventory_suppliers`;
CREATE TABLE `inventory_suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `inventory_suppliers`

INSERT INTO `inventory_suppliers` (`id`, `name`, `contact_person`, `email`, `phone`, `address`, `notes`, `is_active`, `created_at`, `updated_at`) VALUES ('1', 'N/A', 'Juan Dela Cruz', 'juan@greenrecycling.com', '09171234567', '123 Eco Street, Quezon City', 'Primary supplier for metals', '1', '2025-07-25 15:14:18', '2025-09-27 02:13:55');

-- --------------------------------------------------------
-- Table structure for table `inventory_transfers`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `inventory_transfers`;
CREATE TABLE `inventory_transfers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `from_location` varchar(100) NOT NULL,
  `to_location` varchar(100) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `transfer_date` date NOT NULL,
  `transferred_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_transfer_item` (`item_id`),
  KEY `fk_transfer_user` (`transferred_by`),
  CONSTRAINT `fk_transfer_item` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`),
  CONSTRAINT `fk_transfer_user` FOREIGN KEY (`transferred_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `inventory_transfers`

INSERT INTO `inventory_transfers` (`id`, `item_id`, `from_location`, `to_location`, `quantity`, `transfer_date`, `transferred_by`, `notes`, `created_at`) VALUES ('1', '1', 'Warehouse A', 'Processing Area', '20.00', '2025-07-22', '1', 'Transfer for processing', '2025-07-25 15:14:18');

-- --------------------------------------------------------
-- Table structure for table `item_categories`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `item_categories`;
CREATE TABLE `item_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `color_tag` varchar(20) DEFAULT '#708B4C',
  `icon` varchar(50) DEFAULT 'fa-box',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `item_categories`

INSERT INTO `item_categories` (`id`, `category_name`, `description`, `is_active`, `created_at`, `updated_at`, `color_tag`, `icon`) VALUES ('1', 'Metals', 'Various metal materials for recycling', '1', '2025-07-25 15:14:18', '2025-07-25 15:14:18', '#708B4C', 'fa-box');
INSERT INTO `item_categories` (`id`, `category_name`, `description`, `is_active`, `created_at`, `updated_at`, `color_tag`, `icon`) VALUES ('2', 'Plastics', 'Various plastic materials', '1', '2025-07-25 15:14:18', '2025-07-25 15:14:18', '#4C8BB7', 'fa-bottle');
INSERT INTO `item_categories` (`id`, `category_name`, `description`, `is_active`, `created_at`, `updated_at`, `color_tag`, `icon`) VALUES ('4', 'Iloveyousomuch', 'NO', '1', '2025-07-25 23:10:25', '2025-07-25 23:10:25', '#6b7785', 'fa-bottle');
INSERT INTO `item_categories` (`id`, `category_name`, `description`, `is_active`, `created_at`, `updated_at`, `color_tag`, `icon`) VALUES ('5', 'asdas', 'dasdasd', '1', '2025-07-26 12:39:18', '2025-07-26 12:39:18', '#91ff00', 'fa-cube');

-- --------------------------------------------------------
-- Table structure for table `item_conditions`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `item_conditions`;
CREATE TABLE `item_conditions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `condition_label` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `item_conditions`

INSERT INTO `item_conditions` (`id`, `condition_label`, `description`, `is_active`) VALUES ('1', 'New', 'Brand new, unused items', '1');
INSERT INTO `item_conditions` (`id`, `condition_label`, `description`, `is_active`) VALUES ('2', 'Recycled', 'Items made from recycled materials', '1');
INSERT INTO `item_conditions` (`id`, `condition_label`, `description`, `is_active`) VALUES ('3', 'Damaged', 'Items with visible damage', '1');
INSERT INTO `item_conditions` (`id`, `condition_label`, `description`, `is_active`) VALUES ('4', 'Refurbished', 'Used items that have been restored', '1');

-- --------------------------------------------------------
-- Table structure for table `loyalty_point_logs`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `loyalty_point_logs`;
CREATE TABLE `loyalty_point_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `points_change` int(11) NOT NULL COMMENT 'Positive for additions, negative for deductions',
  `reason` text NOT NULL,
  `previous_points` int(11) NOT NULL,
  `new_points` int(11) NOT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_loyalty_customer` (`customer_id`),
  KEY `fk_loyalty_employee` (`employee_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_loyalty_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_loyalty_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `loyalty_point_logs`

INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('1', '54', '8', '2', 'sick leave', '178', '180', NULL, '2025-09-27 01:02:48');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('2', '46', '8', '1', 'asd', '0', '1', 'receipts/loyalty/LP-20250927-2250.pdf', '2025-09-27 01:47:37');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('3', '54', '8', '20', 'Aniversarry reward', '180', '200', 'receipts/loyalty/LP-20250927-3318.pdf', '2025-09-27 02:01:49');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('4', '54', '8', '2', 'sick leave', '10200', '10202', 'receipts/loyalty/LP-20250929-6237.pdf', '2025-09-29 01:41:42');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('5', '54', '8', '1', 'sick leave', '10202', '10203', 'receipts/loyalty/LP-20250929-8707.pdf', '2025-09-29 01:47:17');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('6', '54', '8', '20000', 'Aniversarry reward', '10203', '30203', 'receipts/loyalty/LP-20250929-8467.pdf', '2025-09-29 01:48:12');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('7', '54', '8', '-500', 'sick leave', '30203', '29703', 'receipts/loyalty/LP-20250929-1818.pdf', '2025-09-29 01:49:30');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('8', '16', '8', '3500', 'asd', '1215', '4715', NULL, '2025-09-29 04:06:05');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('9', '16', '8', '5000', 'asd', '4715', '9715', NULL, '2025-09-29 04:06:25');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('10', '16', '8', '6000', 'sdf', '9715', '15715', NULL, '2025-09-29 04:06:35');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('15', '16', '2', '-800', '0', '13416', '12616', 'receipts/vouchers/GOLD202509285046.pdf', '2025-09-29 04:47:57');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('16', '16', '2', '-800', '0', '12616', '11816', 'receipts/vouchers/GOLD202509282538.pdf', '2025-09-29 04:48:26');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('17', '16', '2', '-800', '0', '11816', '11016', 'receipts/vouchers/GOLD202509282326.pdf', '2025-09-29 04:49:04');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('18', '16', '2', '-800', '0', '11016', '10216', 'receipts/vouchers/GOLD202509288239.pdf', '2025-09-29 04:49:29');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('19', '16', '2', '-800', '0', '10216', '9416', 'receipts/vouchers/GOLD202509284852.pdf', '2025-09-29 04:49:31');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('20', '16', '2', '-800', '0', '9416', '8616', 'receipts/vouchers/GOLD202509287984.pdf', '2025-09-29 04:49:33');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('21', '16', '2', '-800', '0', '8616', '7816', 'receipts/vouchers/GOLD202509282142.pdf', '2025-09-29 04:49:34');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('22', '16', '2', '-800', '0', '7816', '7016', 'receipts/vouchers/GOLD202509282942.pdf', '2025-09-29 04:49:34');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('23', '16', '2', '-800', '0', '7016', '6216', 'receipts/vouchers/GOLD202509281852.pdf', '2025-09-29 04:49:34');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('24', '16', '2', '-800', '0', '6216', '5416', 'receipts/vouchers/GOLD202509288300.pdf', '2025-09-29 04:49:34');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('25', '16', '2', '-800', '0', '5416', '4616', 'receipts/vouchers/GOLD202509285063.pdf', '2025-09-29 04:49:34');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('26', '16', '2', '-800', '0', '4616', '3816', 'receipts/vouchers/GOLD202509281598.pdf', '2025-09-29 04:49:35');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('27', '16', '2', '-800', '0', '3816', '3016', 'receipts/vouchers/GOLD202509282756.pdf', '2025-09-29 04:49:35');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('28', '16', '2', '-800', '0', '3016', '2216', 'receipts/vouchers/GOLD202509288690.pdf', '2025-09-29 04:49:35');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('29', '16', '2', '-800', '0', '2216', '1416', 'receipts/vouchers/GOLD202509283397.pdf', '2025-09-29 04:49:35');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('30', '16', '2', '-800', '0', '1416', '616', 'receipts/vouchers/GOLD202509289982.pdf', '2025-09-29 04:49:36');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('31', '16', '2', '-500', '0', '616', '116', 'receipts/vouchers/SILVER202509284214.pdf', '2025-09-29 05:07:04');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('32', '16', '8', '1000', 'test', '116', '1116', NULL, '2025-09-29 05:10:04');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('33', '16', '2', '-500', '0', '1116', '616', 'receipts/vouchers/SILVER202509284305.pdf', '2025-09-29 05:10:08');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('34', '16', '2', '-500', '0', '616', '116', 'receipts/vouchers/SILVER202509284389.pdf', '2025-09-29 05:10:28');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('35', '16', '8', '1000', 'test', '116', '1116', NULL, '2025-09-29 05:16:05');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('36', '16', '2', '-500', '0', '1116', '616', 'receipts/vouchers/SILVER202509289808.pdf', '2025-09-29 05:16:10');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('37', '16', '2', '-500', '0', '616', '116', 'receipts/vouchers/SILVER202509281866.pdf', '2025-09-29 05:16:13');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('38', '16', '8', '1000', 'cxv', '116', '1116', NULL, '2025-09-29 05:31:28');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('39', '16', '2', '-500', '0', '1116', '616', 'receipts/vouchers/SILVER202509288802.pdf', '2025-09-29 05:31:52');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('40', '16', '2', '-500', '0', '616', '116', 'receipts/vouchers/SILVER202509287265.pdf', '2025-09-29 05:32:18');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('41', '16', '8', '1000', 'sdf', '116', '1116', NULL, '2025-09-29 05:33:07');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('42', '16', '2', '-500', '0', '1116', '616', 'receipts/vouchers/SILVER202509283461.pdf', '2025-09-29 05:33:17');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('43', '16', '2', '-500', '0', '616', '116', 'receipts/vouchers/SILVER202509293568.pdf', '2025-09-29 06:18:07');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('44', '16', '8', '2100', 'Aniversarry reward', '116', '2216', 'receipts/loyalty/LP-20250929-7424.pdf', '2025-09-29 06:43:24');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('45', '16', '2', '-500', '0', '2216', '1716', 'receipts/vouchers/SILVER202509297205.pdf', '2025-09-29 06:45:22');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('46', '16', '2', '-500', '0', '1716', '1216', 'receipts/vouchers/SILVER202509297110.pdf', '2025-09-30 01:48:26');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('47', '16', '8', '400000', 'test', '1216', '401216', NULL, '2025-09-30 01:50:13');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('48', '16', '2', '-3500', '0', '401216', '397716', 'receipts/vouchers/ETHEREAL202509295672.pdf', '2025-09-30 01:50:32');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('49', '16', '8', '-1200', '0', '397716', '396516', 'receipts/vouchers/PLATINUM202509306186.pdf', '2025-09-30 18:43:35');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('50', '16', '8', '10000', 'test', '396516', '406516', NULL, '2025-10-06 05:05:41');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('51', '16', '8', '-500', '0', '506516', '506016', 'receipts/vouchers/SILVER202510052125.pdf', '2025-10-06 05:11:53');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('52', '16', '8', '-800', '0', '506016', '505216', 'receipts/vouchers/GOLD202510051241.pdf', '2025-10-06 05:12:20');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('53', '16', '8', '-1200', '0', '505216', '504016', 'receipts/vouchers/PLATINUM202510057436.pdf', '2025-10-06 05:12:44');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('54', '16', '8', '-2000', '0', '504016', '502016', 'receipts/vouchers/DIAMOND202510054095.pdf', '2025-10-06 05:12:56');
INSERT INTO `loyalty_point_logs` (`id`, `customer_id`, `employee_id`, `points_change`, `reason`, `previous_points`, `new_points`, `receipt_path`, `created_at`) VALUES ('55', '16', '8', '-3500', '0', '502016', '498516', 'receipts/vouchers/ETHEREAL202510059365.pdf', '2025-10-06 05:13:09');

-- --------------------------------------------------------
-- Table structure for table `market_prices`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `market_prices`;
CREATE TABLE `market_prices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `material_id` int(11) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `buying_price` decimal(10,2) DEFAULT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `collected_on` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_market_material` (`material_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `market_prices`

INSERT INTO `market_prices` (`id`, `material_id`, `source`, `buying_price`, `selling_price`, `collected_on`, `notes`) VALUES ('1', '1', 'Market A', '13.10', '15.30', '2025-07-20', 'Based on July market survey');
INSERT INTO `market_prices` (`id`, `material_id`, `source`, `buying_price`, `selling_price`, `collected_on`, `notes`) VALUES ('2', '2', 'Market B', '7.60', '9.10', '2025-07-21', 'Promo price period');
INSERT INTO `market_prices` (`id`, `material_id`, `source`, `buying_price`, `selling_price`, `collected_on`, `notes`) VALUES ('3', '3', 'Market C', '15.90', '18.00', '2025-07-22', 'Updated by field agent');
INSERT INTO `market_prices` (`id`, `material_id`, `source`, `buying_price`, `selling_price`, `collected_on`, `notes`) VALUES ('4', '4', 'Market A', '5.40', '6.90', '2025-07-23', 'Average of last 3 months');
INSERT INTO `market_prices` (`id`, `material_id`, `source`, `buying_price`, `selling_price`, `collected_on`, `notes`) VALUES ('5', '5', 'Market D', '19.30', '21.10', '2025-07-24', 'Adjusted after competitor research');

-- --------------------------------------------------------
-- Table structure for table `material_prices`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `material_prices`;
CREATE TABLE `material_prices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `material_type` varchar(50) DEFAULT NULL,
  `unit` enum('per_kg','per_item') DEFAULT NULL,
  `buying_price` decimal(10,2) DEFAULT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `effective_date` date DEFAULT curdate(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `materials`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `materials`;
CREATE TABLE `materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `material_option` enum('Copper Wire','PET Bottles','Aluminum Cans','Cardboard','Steel','Glass Bottles','Computer Parts','Iron Scrap','Stainless Steel','E-Waste','Yero (Corrugated Sheets)','Batteries') DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `trend_change` decimal(10,2) DEFAULT 0.00,
  `trend_direction` enum('up','down','equal') DEFAULT 'equal',
  `category_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `is_featured` tinyint(1) DEFAULT 0,
  `is_recyclable` tinyint(1) DEFAULT 1,
  `weight_unit` enum('kg','g','lbs') DEFAULT 'kg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`material_option`),
  UNIQUE KEY `material_option` (`material_option`),
  UNIQUE KEY `material_option_2` (`material_option`),
  UNIQUE KEY `material_option_3` (`material_option`),
  UNIQUE KEY `material_option_4` (`material_option`),
  KEY `fk_materials_category` (`category_id`),
  CONSTRAINT `fk_materials_category` FOREIGN KEY (`category_id`) REFERENCES `item_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `materials`

INSERT INTO `materials` (`id`, `material_option`, `unit_price`, `trend_change`, `trend_direction`, `category_id`, `status`, `is_featured`, `is_recyclable`, `weight_unit`, `created_at`, `updated_at`) VALUES ('6', 'Copper Wire', '450.00', '3.39', 'up', NULL, 'active', '0', '1', 'kg', '2025-07-25 13:26:30', '2025-09-30 18:43:48');
INSERT INTO `materials` (`id`, `material_option`, `unit_price`, `trend_change`, `trend_direction`, `category_id`, `status`, `is_featured`, `is_recyclable`, `weight_unit`, `created_at`, `updated_at`) VALUES ('7', 'Aluminum Cans', '75.00', '0.98', 'down', NULL, 'active', '0', '1', 'kg', '2025-07-25 13:26:30', '2025-09-30 18:43:48');
INSERT INTO `materials` (`id`, `material_option`, `unit_price`, `trend_change`, `trend_direction`, `category_id`, `status`, `is_featured`, `is_recyclable`, `weight_unit`, `created_at`, `updated_at`) VALUES ('8', 'Iron Scrap', '18.55', '0.35', 'up', NULL, 'active', '0', '1', 'kg', '2025-07-25 13:26:30', '2025-09-30 18:43:04');
INSERT INTO `materials` (`id`, `material_option`, `unit_price`, `trend_change`, `trend_direction`, `category_id`, `status`, `is_featured`, `is_recyclable`, `weight_unit`, `created_at`, `updated_at`) VALUES ('9', 'Stainless Steel', '65.75', '1.16', 'down', NULL, 'active', '0', '1', 'kg', '2025-07-25 13:26:30', '2025-09-30 18:43:04');
INSERT INTO `materials` (`id`, `material_option`, `unit_price`, `trend_change`, `trend_direction`, `category_id`, `status`, `is_featured`, `is_recyclable`, `weight_unit`, `created_at`, `updated_at`) VALUES ('10', 'E-Waste', '113.47', '0.35', 'up', NULL, 'active', '0', '1', 'kg', '2025-07-25 13:26:30', '2025-09-30 18:43:04');
INSERT INTO `materials` (`id`, `material_option`, `unit_price`, `trend_change`, `trend_direction`, `category_id`, `status`, `is_featured`, `is_recyclable`, `weight_unit`, `created_at`, `updated_at`) VALUES ('26', 'PET Bottles', '9.00', '0.17', 'up', NULL, 'active', '0', '1', 'kg', '2025-07-25 13:26:30', '2025-09-30 18:43:48');
INSERT INTO `materials` (`id`, `material_option`, `unit_price`, `trend_change`, `trend_direction`, `category_id`, `status`, `is_featured`, `is_recyclable`, `weight_unit`, `created_at`, `updated_at`) VALUES ('27', 'Cardboard', '2.00', '0.02', 'up', NULL, 'active', '0', '1', 'kg', '2025-07-25 13:26:30', '2025-09-30 18:43:48');
INSERT INTO `materials` (`id`, `material_option`, `unit_price`, `trend_change`, `trend_direction`, `category_id`, `status`, `is_featured`, `is_recyclable`, `weight_unit`, `created_at`, `updated_at`) VALUES ('28', 'Steel', '8.00', '0.00', 'up', NULL, 'active', '0', '1', 'kg', '2025-07-25 13:26:30', '2025-09-30 18:43:48');
INSERT INTO `materials` (`id`, `material_option`, `unit_price`, `trend_change`, `trend_direction`, `category_id`, `status`, `is_featured`, `is_recyclable`, `weight_unit`, `created_at`, `updated_at`) VALUES ('29', 'Glass Bottles', '2.00', '0.01', 'up', NULL, 'active', '0', '1', 'kg', '2025-07-25 13:26:30', '2025-09-30 18:43:48');
INSERT INTO `materials` (`id`, `material_option`, `unit_price`, `trend_change`, `trend_direction`, `category_id`, `status`, `is_featured`, `is_recyclable`, `weight_unit`, `created_at`, `updated_at`) VALUES ('30', 'Computer Parts', '250.00', '2.88', 'down', NULL, 'active', '0', '1', 'kg', '2025-07-25 13:26:30', '2025-09-30 18:43:48');
INSERT INTO `materials` (`id`, `material_option`, `unit_price`, `trend_change`, `trend_direction`, `category_id`, `status`, `is_featured`, `is_recyclable`, `weight_unit`, `created_at`, `updated_at`) VALUES ('42', 'Yero (Corrugated Sheets)', '7.00', '0.00', 'up', NULL, 'active', '0', '1', 'kg', '2025-08-08 17:32:35', '2025-09-30 18:43:48');
INSERT INTO `materials` (`id`, `material_option`, `unit_price`, `trend_change`, `trend_direction`, `category_id`, `status`, `is_featured`, `is_recyclable`, `weight_unit`, `created_at`, `updated_at`) VALUES ('43', 'Batteries', '25.00', '0.21', 'down', NULL, 'active', '0', '1', 'kg', '2025-08-08 17:32:35', '2025-09-30 18:43:48');

-- --------------------------------------------------------
-- Table structure for table `measurement_units`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `measurement_units`;
CREATE TABLE `measurement_units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_name` varchar(50) NOT NULL,
  `unit_symbol` varchar(10) NOT NULL,
  `conversion_to_base` decimal(10,4) DEFAULT NULL,
  `base_unit` varchar(10) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `messages`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `is_deleted` tinyint(1) DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_edited` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_msg_sender` (`sender_id`),
  KEY `fk_msg_receiver` (`receiver_id`),
  CONSTRAINT `fk_msg_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `fk_msg_sender` FOREIGN KEY (`sender_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `messages`

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('6', '8', '6', 'asdasd', '1', '0', NULL, '0', '2025-09-27 02:37:21');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('7', '8', '5', 'asd12', '0', '0', NULL, '0', '2025-09-27 02:37:38');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('13', '8', '2', 'andy  stop na sa pag aaral mangalakal nalnag tayo', '0', '0', NULL, '0', '2025-09-27 03:03:15');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('14', '8', '3', 'cousin, paki dala si coby', '0', '0', NULL, '0', '2025-09-27 03:03:41');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('15', '8', '5', 'asd', '0', '0', NULL, '0', '2025-09-27 03:04:02');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('17', '8', '5', 'sabi mo wala si foreman, nahuli ako nas, nag tatago ng bakal', '0', '0', NULL, '0', '2025-09-27 03:06:00');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('18', '9', '8', 'asdasd', '1', '0', NULL, '0', '2025-09-27 03:11:13');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('19', '8', '9', 'ayoko po', '1', '0', NULL, '0', '2025-09-27 03:11:43');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('20', '9', '8', 'pa collect ako ng report nung aug 2 hanggang aug 18', '1', '0', NULL, '0', '2025-09-27 03:12:15');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('21', '8', '9', 'okay poo', '1', '0', NULL, '0', '2025-09-27 03:12:37');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('22', '9', '8', 'asdasd', '1', '0', NULL, '0', '2025-09-29 00:40:34');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('23', '8', '9', 'foreman', '1', '0', NULL, '0', '2025-09-29 02:07:15');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('24', '8', '9', 'sad', '1', '0', NULL, '0', '2025-09-29 02:07:22');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('25', '8', '9', 'ss', '1', '0', NULL, '0', '2025-09-29 02:07:41');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('26', '8', '6', 'biss sabi mo', '1', '0', NULL, '0', '2025-09-29 02:10:12');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('27', '8', '10', 'hi love', '1', '0', NULL, '0', '2025-09-29 02:12:27');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('28', '8', '10', 'asd', '1', '0', NULL, '0', '2025-09-29 02:13:33');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('29', '9', '8', 'asd', '1', '0', NULL, '0', '2025-09-29 02:24:27');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('30', '8', '9', 'ano nanaman kaylangan mo boss', '1', '0', NULL, '0', '2025-09-29 02:24:36');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('31', '9', '8', 'abay tang ina mo ah', '1', '0', NULL, '0', '2025-09-29 02:24:44');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('32', '8', '9', 'jk lang boss', '1', '0', NULL, '0', '2025-09-29 02:24:49');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('33', '9', '8', 'good', '1', '0', NULL, '0', '2025-09-29 02:25:02');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('34', '9', '10', 'hi my babyyy, iloveyou so much', '1', '0', NULL, '0', '2025-09-29 02:27:20');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('35', '8', '9', 'ss', '0', '0', NULL, '0', '2025-09-29 02:36:47');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('36', '9', '8', 'asd', '1', '0', NULL, '0', '2025-09-29 02:41:58');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('37', '9', '8', 'tang ina naman', '1', '0', NULL, '0', '2025-09-29 02:43:40');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('38', '8', '9', 'ano ba yun', '0', '0', NULL, '0', '2025-09-29 02:43:52');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('39', '8', '9', 'kasalanan ko nanaman', '0', '0', NULL, '0', '2025-09-29 02:43:57');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('40', '9', '2', 'dy', '0', '0', NULL, '0', '2025-09-29 02:44:36');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('41', '9', '5', 'soliss', '0', '0', NULL, '0', '2025-09-29 02:44:43');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('42', '9', '6', 'asd', '1', '0', NULL, '0', '2025-09-29 02:44:50');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('43', '9', '9', 'sad', '0', '0', NULL, '0', '2025-09-29 02:45:16');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('44', '9', '8', 'oo kasalanan mo palagi', '1', '0', NULL, '0', '2025-09-29 02:45:26');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('45', '9', '8', 'no', '1', '0', NULL, '0', '2025-09-29 02:49:55');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('46', '9', '8', 'yes', '1', '0', NULL, '0', '2025-09-29 02:50:08');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('47', '8', '9', 'ano nanaman pinag sasasabi mo boss', '0', '0', NULL, '0', '2025-09-29 02:50:21');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('48', '9', '8', 'namo a', '1', '0', NULL, '0', '2025-09-29 02:50:37');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('49', '8', '9', 'ioloveyou boss', '0', '0', NULL, '0', '2025-09-29 02:59:54');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('50', '9', '8', 'pwede ba manahimik kana', '1', '0', NULL, '0', '2025-09-29 03:00:19');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('51', '9', '8', 'ha', '1', '0', NULL, '0', '2025-09-29 03:00:37');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('52', '8', '9', 'sge boss', '0', '1', '2025-09-29 03:01:02', '0', '2025-09-29 03:00:46');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('53', '8', '9', 'boss miss na kita', '0', '0', NULL, '0', '2025-09-29 03:07:49');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('54', '9', '8', 'wag', '1', '0', NULL, '0', '2025-09-29 03:09:48');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('55', '11', '8', 'nigger', '1', '0', NULL, '0', '2025-09-29 03:10:53');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('56', '11', '8', 'jk', '1', '0', NULL, '0', '2025-09-29 03:10:57');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('57', '10', '8', 'hi my baby', '1', '0', NULL, '0', '2025-09-29 06:47:02');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('58', '8', '9', 'asd', '0', '0', NULL, '0', '2025-09-29 06:50:05');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('59', '9', '8', 'asd', '1', '0', NULL, '0', '2025-09-29 06:50:46');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('60', '8', '10', 'ayoko na dito', '0', '0', NULL, '0', '2025-09-29 06:52:07');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('61', '6', '8', 'baliw kaba', '1', '0', NULL, '0', '2025-09-30 04:48:46');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('62', '6', '8', '?', '1', '0', NULL, '0', '2025-09-30 04:48:46');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('63', '8', '6', 'sorry', '0', '0', NULL, '0', '2025-09-30 04:49:11');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('64', '13', '8', 'let me there for you, imma make you see that', '1', '0', NULL, '0', '2025-09-30 17:30:10');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('65', '13', '8', 'i want youu', '1', '0', NULL, '0', '2025-09-30 18:19:42');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('66', '8', '13', 'dont please', '0', '0', NULL, '0', '2025-09-30 18:40:25');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('67', '13', '8', 'bitch?', '1', '0', NULL, '0', '2025-09-30 18:40:32');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('68', '8', '13', 'yes?', '0', '0', NULL, '0', '2025-09-30 18:40:37');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('69', '13', '8', 'asd', '0', '0', NULL, '0', '2025-10-06 01:44:13');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('70', '13', '8', 'asd', '0', '0', NULL, '0', '2025-10-06 02:08:05');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('71', '13', '10', 'asdasd', '0', '0', NULL, '0', '2025-10-06 04:11:11');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `is_deleted`, `deleted_at`, `is_edited`, `created_at`) VALUES ('72', '13', '8', 'asdasd', '0', '0', NULL, '0', '2025-10-06 04:11:50');

-- --------------------------------------------------------
-- Table structure for table `migrations`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `pickup_materials`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `pickup_materials`;
CREATE TABLE `pickup_materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pickup_id` int(11) DEFAULT NULL,
  `material_id` int(11) DEFAULT NULL,
  `quantity_kg` decimal(10,2) NOT NULL,
  `estimated_price` decimal(10,2) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pm_pickup` (`pickup_id`),
  KEY `fk_pm_material` (`material_id`),
  KEY `fk_pickup_materials_transaction` (`transaction_id`),
  CONSTRAINT `fk_pickup_materials_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `pickup_materials`

INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('1', '1', '6', '5.00', '1250.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('2', '2', '10', '5.00', '600.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('3', '2', '7', '85.00', '3825.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('4', '2', '8', '6.00', '108.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('5', '2', '10', '6.00', '720.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('6', '3', '6', '5.00', '1250.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('7', '4', '6', '2.00', '500.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('8', '5', '6', '6.00', '1500.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('9', '6', '10', '5.00', '600.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('10', '6', '10', '6.00', '720.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('11', '7', '6', '5.00', '1250.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('12', '7', '8', '6.00', '108.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('13', '8', '7', '87.00', '3915.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('14', '8', '9', '55.00', '3575.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('15', '8', '6', '30.00', '7500.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('16', '9', '10', '5.00', '600.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('17', '10', '6', '600.00', '150000.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('18', '11', '6', '600.00', '150000.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('19', '12', '7', '63.00', '2835.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('20', '13', '9', '34.00', '2210.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('21', '14', '10', '22.00', '2640.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('22', '15', '6', '0.70', '175.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('23', '16', '27', '1.00', '17.14', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('24', '17', '26', '5.00', '142.85', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('25', '17', '30', '1.00', '85.71', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('26', '18', '26', '2.00', '57.14', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('27', '19', '26', '1.00', '28.57', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('28', '20', '28', '7.00', '319.97', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('29', '21', '26', '1.00', '28.57', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('30', '22', '27', '2.00', '34.28', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('31', '23', '7', '1212.00', '54540.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('32', '24', '27', '1.00', '17.14', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('33', '25', '27', '9.00', '154.26', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('34', '26', '27', '9.00', '154.26', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('35', '27', '6', '1.00', '250.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('36', '27', '7', '2.00', '90.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('37', '28', '26', '12.00', '342.84', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('38', '29', '27', '232.00', '3976.48', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('39', '30', '6', '300.00', '75000.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('40', '31', '6', '500.00', '125000.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('41', '32', '7', '12.00', '540.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('42', '33', '6', '1.00', '250.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('43', '34', '7', '4.00', '180.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('44', '35', '27', '21.00', '359.94', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('45', '36', '26', '5.00', '142.85', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('46', '37', '6', '50.00', '12500.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('47', '38', '6', '3.00', '750.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('48', '38', '29', '5.00', '71.45', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('49', '38', '27', '10.00', '171.40', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('50', '38', '26', '15.00', '428.55', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('51', '38', '30', '20.00', '1714.20', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('52', '38', '10', '10.00', '1200.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('53', '39', '26', '50.00', '1428.50', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('54', '40', '26', '1.00', '28.57', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('55', '41', '27', '5.00', '85.70', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('56', '41', '26', '2.00', '57.14', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('57', '42', '27', '1212.00', '20773.68', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('58', '43', '28', '1.00', '45.71', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('59', '44', '27', '123.00', '2108.22', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('60', '45', '6', '111.00', '33300.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('61', '46', '6', '123.00', '36900.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('62', '47', '6', '1.00', '300.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('63', '48', '26', '1.00', '29.57', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('64', '49', '7', '1.00', '45.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('65', '50', '6', '1.00', '300.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('66', '51', '27', '1.00', '17.14', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('67', '54', '28', '1.00', '45.71', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('68', '55', '26', '1.00', '29.57', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('69', '56', '10', '1.00', '120.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('70', '57', '26', '1.00', '29.57', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('71', '58', '6', '12.00', '3600.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('72', '59', '6', '31.00', '9300.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('73', '60', '26', '6.00', '177.42', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('74', '61', '28', '1.00', '8.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('75', '62', '7', '21.00', '1575.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('76', '63', '28', '12.00', '96.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('77', '64', '26', '1.00', '9.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('78', '65', '26', '1.00', '9.00', NULL);
INSERT INTO `pickup_materials` (`id`, `pickup_id`, `material_id`, `quantity_kg`, `estimated_price`, `transaction_id`) VALUES ('79', '65', '7', '2.00', '150.00', NULL);

-- --------------------------------------------------------
-- Table structure for table `pickups`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `pickups`;
CREATE TABLE `pickups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `pickup_date` date NOT NULL,
  `time_slot` varchar(50) NOT NULL,
  `address` text DEFAULT NULL,
  `special_instructions` text DEFAULT NULL,
  `estimated_value` decimal(10,2) DEFAULT NULL,
  `status` enum('Scheduled','Completed','Cancelled') DEFAULT 'Scheduled',
  `shipping_method` varchar(255) DEFAULT NULL,
  `shipping_fee` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_free_pickup` tinyint(1) DEFAULT 0,
  `reward_redemption_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pickups_user` (`user_id`),
  KEY `reward_redemption_id` (`reward_redemption_id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `pickups`

INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('1', '1', '2025-07-23', '10:00 - 12:00 PM', '123 Main Street, Barangay San Jose, Quezon City', 'ilolveyou', '1250.00', 'Scheduled', NULL, NULL, '2025-07-19 14:03:20', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('2', '1', '2025-07-31', '10:00 - 12:00 PM', '123 Main Street, Barangay San Jose, Quezon City', 'black gate', '5253.00', 'Scheduled', NULL, NULL, '2025-07-19 14:04:34', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('3', '1', '2025-07-23', '10:00 - 12:00 PM', 'Gold Extention', 'black gate', '1250.00', 'Scheduled', NULL, NULL, '2025-07-19 14:08:41', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('4', '3', '2025-07-25', '10:00 - 12:00 PM', '123 Main Street, Barangay San Jose, Quezon City', 'asd', '500.00', 'Scheduled', NULL, NULL, '2025-07-19 14:13:37', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('5', '1', '2025-07-24', '3:00 - 5:00 PM', 'iloveyou', 'black na gate sa tabi ng dahon', '1500.00', 'Scheduled', NULL, NULL, '2025-07-19 14:14:51', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('6', '1', '2025-07-30', '8:00 - 10:00 AM', 'jan lnag', 'wtf', '1320.00', 'Scheduled', NULL, NULL, '2025-07-19 14:17:29', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('7', '1', '2025-07-23', '1:00 - 3:00 PM', 'sasd', 'asdaa', '1358.00', 'Scheduled', NULL, NULL, '2025-07-19 14:24:59', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('8', '1', '2025-07-27', '8:00 - 10:00 AM', '54 Gold Extention', 'Black gate', '14990.00', 'Scheduled', NULL, NULL, '2025-07-19 14:26:58', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('9', '3', '2025-07-29', '8:00 - 10:00 AM', 'jan lang', 'po boss', '600.00', 'Scheduled', NULL, NULL, '2025-07-19 14:32:51', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('10', '3', '2025-07-22', '1:00 - 3:00 PM', 'same parin', 'Same parin', '150000.00', 'Scheduled', NULL, NULL, '2025-07-19 15:17:17', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('11', '3', '2025-07-22', '1:00 - 3:00 PM', 'same parin', 'Same parin', '150000.00', 'Scheduled', NULL, NULL, '2025-07-19 15:17:17', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('12', '3', '2025-07-31', '1:00 - 3:00 PM', 'asd', 'asd', '2835.00', 'Scheduled', NULL, NULL, '2025-07-19 15:17:39', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('13', '3', '2025-07-31', '1:00 - 3:00 PM', 'asdas', 'asdasd', '2210.00', 'Scheduled', NULL, NULL, '2025-07-19 15:17:54', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('14', '3', '2025-07-30', '1:00 - 3:00 PM', 'asdasd', 'adasd', '2640.00', 'Scheduled', NULL, NULL, '2025-07-19 15:18:14', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('15', '1', '2025-07-23', '8:00 - 10:00 AM', 'Iloveyou so much', 'balck gate', '175.00', 'Scheduled', NULL, NULL, '2025-07-19 16:53:02', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('16', '1', '2025-07-21', '10:00 - 12:00 PM', '054 gold extention', 'asdasd', '17.14', 'Scheduled', NULL, NULL, '2025-07-19 17:24:00', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('17', '1', '2025-07-28', '10:00 - 12:00 PM', '054 gold extention', 'Black green gate', '228.56', 'Scheduled', NULL, NULL, '2025-07-19 17:47:28', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('18', '1', '2025-08-09', '8:00 - 10:00 AM', '054 gold extention', 'red gate', '57.14', 'Scheduled', NULL, NULL, '2025-07-19 17:48:15', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('19', '1', '2025-07-28', '10:00 - 12:00 PM', '054 gold extention', 'pink gate', '28.57', 'Scheduled', NULL, NULL, '2025-07-19 17:49:07', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('20', '1', '2025-09-24', '8:00 - 10:00 AM', '054 gold extention', 'You', '319.97', 'Scheduled', NULL, NULL, '2025-07-19 18:09:42', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('21', '1', '2025-08-06', '3:00 - 5:00 PM', '054 gold extention', 'asdasd', '28.57', 'Scheduled', NULL, NULL, '2025-07-19 18:19:05', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('22', '1', '2025-08-06', '8:00 - 10:00 AM', '054 gold extention', 'asdasd', '34.28', 'Scheduled', NULL, NULL, '2025-07-19 18:20:48', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('23', '1', '2025-07-20', '8:00 - 10:00 AM', '054 gold extention', 'asd22', '54540.00', 'Scheduled', NULL, NULL, '2025-07-19 18:21:25', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('24', '1', '2025-07-31', '1:00 - 3:00 PM', '054 gold extention', 'asd', '17.14', 'Scheduled', NULL, NULL, '2025-07-19 18:33:18', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('25', '1', '2025-07-30', '1:00 - 3:00 PM', '054 gold extention', 'asdasd', '154.26', 'Scheduled', NULL, NULL, '2025-07-19 18:36:14', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('26', '1', '2025-07-30', '1:00 - 3:00 PM', '054 gold extention', 'asdasd', '154.26', 'Scheduled', NULL, NULL, '2025-07-19 18:36:14', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('27', '8', '2025-07-30', '3:00 - 5:00 PM', 'Gold st', 'WTF', '340.00', 'Scheduled', NULL, NULL, '2025-07-19 19:33:33', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('28', '1', '2025-07-23', '1:00 - 3:00 PM', '054 gold extention', 'asd', '342.84', 'Scheduled', NULL, NULL, '2025-07-19 23:23:24', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('29', '10', '2025-07-29', '10:00 - 12:00 PM', '054 gold extention', 'asdasd', '3976.48', 'Scheduled', NULL, NULL, '2025-07-19 23:24:06', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('30', '10', '2025-07-28', '1:00 - 3:00 PM', '054 gold extention', '123123', '75000.00', 'Scheduled', NULL, NULL, '2025-07-19 23:24:40', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('31', '12', '2025-07-31', '1:00 - 3:00 PM', '054 gold extention', 'asdasd', '125000.00', 'Scheduled', NULL, NULL, '2025-07-19 23:27:32', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('32', '10', '2025-07-22', '8:00 - 10:00 AM', '054 gold extention', 'asd', '540.00', 'Scheduled', NULL, NULL, '2025-07-20 12:39:51', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('33', '10', '2025-07-21', '10:00 - 12:00 PM', '054 gold extention', '123', '250.00', 'Scheduled', NULL, NULL, '2025-07-20 12:42:08', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('34', '1', '2025-07-23', '10:00 - 12:00 PM', '054 gold extention', 'asd', '180.00', 'Scheduled', NULL, NULL, '2025-07-20 12:43:26', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('35', '12', '2025-07-23', '10:00 - 12:00 PM', '054 gold extention', '1212', '359.94', 'Scheduled', NULL, NULL, '2025-07-20 12:51:48', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('36', '1', '2025-07-30', '8:00 - 10:00 AM', '054 gold extention baranggay commonwelth qc', 'Black gate', '142.85', 'Scheduled', NULL, NULL, '2025-07-21 13:24:12', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('37', '12', '2025-07-23', '1:00 - 3:00 PM', 'caloocan st bagong silang', 'Black gate', '12500.00', 'Scheduled', NULL, NULL, '2025-07-22 22:11:39', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('38', '15', '2025-07-25', '1:00 - 3:00 PM', '565 hajsmnah', ' kunin sa aso ko', '4335.60', 'Scheduled', NULL, NULL, '2025-07-23 17:28:42', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('39', '12', '2025-07-25', '3:00 - 5:00 PM', 'caloocan st bagong silang', 'sa kanto nalang', '1428.50', 'Scheduled', NULL, NULL, '2025-07-23 17:32:02', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('40', '1', '2025-07-26', '10:00 - 12:00 PM', '054 gold extention baranggay commonwelt12h qc', 'blue gate', '28.57', 'Scheduled', NULL, NULL, '2025-07-24 12:25:55', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('41', '1', '2025-07-30', '1:00 - 3:00 PM', '054 gold extention baranggay commonwelth qc', '', '142.84', 'Scheduled', NULL, NULL, '2025-07-25 20:55:33', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('42', '4', '2025-07-30', '10:00 - 12:00 PM', '054 GOLD EXTENTION, BARANGGAY COMMONWELTH QC', 'asdad', '20773.68', 'Scheduled', NULL, NULL, '2025-07-26 13:13:36', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('43', '16', '2025-07-30', '10:00 - 12:00 PM', '054 gold extention', 'asdasdasd', '45.71', 'Scheduled', NULL, NULL, '2025-07-27 15:34:28', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('44', '16', '2025-08-01', '1:00 - 3:00 PM', '054 gold extention', 'asdasd', '2108.22', 'Scheduled', NULL, NULL, '2025-07-27 23:18:39', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('45', '1', '2025-07-31', '1:00 - 3:00 PM', '054 gold extention baranggay commonwelth qc', 'asdasd', '33300.00', 'Scheduled', NULL, NULL, '2025-07-28 00:26:17', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('46', '12', '2025-07-30', '10:00 - 12:00 PM', 'caloocan st bagong silang', 'asdasd', '36900.00', 'Scheduled', NULL, NULL, '2025-07-28 01:05:50', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('47', '12', '2025-07-30', '10:00 - 12:00 PM', 'caloocan st bagong silang', 'asdasd', '300.00', 'Scheduled', NULL, NULL, '2025-07-28 11:27:19', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('48', '12', '2025-07-31', '1:00 - 3:00 PM', 'caloocan st bagong silang', 'asdasd', '29.57', 'Scheduled', NULL, NULL, '2025-07-28 11:28:56', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('49', '16', '2025-07-31', '3:00 - 5:00 PM', '054 gold extention', 'asdasdsa', '45.00', 'Scheduled', NULL, NULL, '2025-07-28 12:01:15', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('50', '16', '2025-08-01', '8:00 - 10:00 AM', '054 gold extention', 'asdasd', '300.00', 'Scheduled', NULL, NULL, '2025-07-28 12:22:34', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('51', '16', '2025-07-31', '8:00 - 10:00 AM', '054 gold extention', 'asdasd', '17.14', 'Scheduled', NULL, NULL, '2025-07-28 12:23:10', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('52', '1', '2025-08-07', '8:00 - 10:00 AM', '054 gold extention baranggay commonwelth qc', 'asdasdad', '29.57', 'Scheduled', NULL, NULL, '2025-07-28 14:00:33', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('53', '1', '2025-08-09', '10:00 - 12:00 PM', '054 gold extention baranggay commonwelth qc', 'asdasd', '45.71', 'Scheduled', NULL, NULL, '2025-07-28 14:01:56', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('54', '1', '2025-08-09', '10:00 - 12:00 PM', '054 gold extention baranggay commonwelth qc', 'asdasd', '45.71', 'Scheduled', NULL, NULL, '2025-07-28 14:02:18', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('55', '1', '2025-08-09', '1:00 - 3:00 PM', '054 gold extention baranggay commonwelth qc', 'asd', '29.57', 'Scheduled', NULL, NULL, '2025-07-28 14:03:39', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('56', '1', '2025-07-28', '8:00 - 10:00 AM', '054 gold extention baranggay commonwelth qc', 'asdasd', '120.00', 'Scheduled', NULL, NULL, '2025-07-28 14:06:36', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('57', '12', '2025-07-29', '3:00 - 5:00 PM', 'caloocan st bagong silang', 'adasd', '29.57', 'Scheduled', NULL, NULL, '2025-07-28 14:10:14', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('58', '16', '2025-08-01', '10:00 - 12:00 PM', '054 gold extention', 'asd', '3600.00', 'Scheduled', NULL, NULL, '2025-07-28 17:24:55', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('59', '16', '2025-08-26', '1:00 - 3:00 PM', '054 gold extention', 'asdasd', '9300.00', 'Scheduled', NULL, NULL, '2025-07-28 17:25:45', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('60', '52', '2025-08-06', '1:00 - 3:00 PM', '054 gold extention', 'asdasdasd', '177.42', 'Scheduled', NULL, NULL, '2025-08-04 03:14:16', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('61', '54', '2025-08-15', '8:00 - 10:00 AM', '054 gold extention', '', '8.00', 'Scheduled', '4', '100.00', '2025-08-08 21:02:08', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('62', '54', '2025-08-15', '10:00 - 12:00 PM', '054 gold extention', 'black gate', '1575.00', 'Scheduled', '3', '50.00', '2025-08-08 21:25:37', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('63', '54', '2025-08-30', '8:00 - 10:00 AM', '054 gold extention', 'Black Gate', '96.00', 'Scheduled', '3', '50.00', '2025-08-08 21:26:38', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('64', '54', '2025-08-14', '8:00 - 10:00 AM', '10 Sto. Niño St. Barangay Commonwealth Quezon city \\r\\n', 'black gate', '9.00', 'Scheduled', '2', '800.00', '2025-08-09 15:46:21', '0', NULL);
INSERT INTO `pickups` (`id`, `user_id`, `pickup_date`, `time_slot`, `address`, `special_instructions`, `estimated_value`, `status`, `shipping_method`, `shipping_fee`, `created_at`, `is_free_pickup`, `reward_redemption_id`) VALUES ('65', '16', '2025-09-29', '3:00 - 5:00 PM', '054 gold extention', 'asdasd', '159.00', 'Scheduled', '3', '50.00', '2025-09-29 05:47:39', '0', NULL);

-- --------------------------------------------------------
-- Table structure for table `price_display_settings`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `price_display_settings`;
CREATE TABLE `price_display_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `material_id` int(11) DEFAULT NULL,
  `show_buying_price` tinyint(1) DEFAULT 1,
  `show_selling_price` tinyint(1) DEFAULT 0,
  `visible_on_kiosk` tinyint(1) DEFAULT 1,
  `visible_on_customer_portal` tinyint(1) DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_material` (`material_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `price_display_settings`

INSERT INTO `price_display_settings` (`id`, `material_id`, `show_buying_price`, `show_selling_price`, `visible_on_kiosk`, `visible_on_customer_portal`, `updated_at`) VALUES ('1', '1', '1', '1', '1', '1', '2025-07-27 21:08:40');
INSERT INTO `price_display_settings` (`id`, `material_id`, `show_buying_price`, `show_selling_price`, `visible_on_kiosk`, `visible_on_customer_portal`, `updated_at`) VALUES ('2', '2', '1', '0', '1', '0', '2025-07-27 21:08:40');
INSERT INTO `price_display_settings` (`id`, `material_id`, `show_buying_price`, `show_selling_price`, `visible_on_kiosk`, `visible_on_customer_portal`, `updated_at`) VALUES ('3', '3', '1', '1', '0', '1', '2025-07-27 21:08:40');
INSERT INTO `price_display_settings` (`id`, `material_id`, `show_buying_price`, `show_selling_price`, `visible_on_kiosk`, `visible_on_customer_portal`, `updated_at`) VALUES ('4', '4', '1', '0', '0', '0', '2025-07-27 21:08:40');
INSERT INTO `price_display_settings` (`id`, `material_id`, `show_buying_price`, `show_selling_price`, `visible_on_kiosk`, `visible_on_customer_portal`, `updated_at`) VALUES ('5', '5', '1', '1', '1', '0', '2025-07-27 21:08:40');
INSERT INTO `price_display_settings` (`id`, `material_id`, `show_buying_price`, `show_selling_price`, `visible_on_kiosk`, `visible_on_customer_portal`, `updated_at`) VALUES ('6', '6', '0', '0', '0', '0', '2025-07-27 21:09:48');

-- --------------------------------------------------------
-- Table structure for table `price_history`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `price_history`;
CREATE TABLE `price_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `material_id` int(11) NOT NULL,
  `old_price` decimal(10,2) NOT NULL,
  `new_price` decimal(10,2) NOT NULL,
  `change_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `changed_by` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_price_material` (`material_id`),
  KEY `fk_price_changed_by` (`changed_by`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `price_history`

INSERT INTO `price_history` (`id`, `material_id`, `old_price`, `new_price`, `change_date`, `changed_by`, `reason`) VALUES ('1', '1', '12.50', '13.00', '2025-07-20 10:15:00', '101', 'Adjusted due to market increase');
INSERT INTO `price_history` (`id`, `material_id`, `old_price`, `new_price`, `change_date`, `changed_by`, `reason`) VALUES ('2', '2', '8.00', '7.50', '2025-07-21 14:00:00', '102', 'Seasonal discount');
INSERT INTO `price_history` (`id`, `material_id`, `old_price`, `new_price`, `change_date`, `changed_by`, `reason`) VALUES ('3', '3', '15.00', '15.75', '2025-07-22 09:30:00', '103', 'Supplier price hike');
INSERT INTO `price_history` (`id`, `material_id`, `old_price`, `new_price`, `change_date`, `changed_by`, `reason`) VALUES ('4', '4', '5.25', '5.50', '2025-07-23 12:45:00', '104', 'Rounded off pricing');
INSERT INTO `price_history` (`id`, `material_id`, `old_price`, `new_price`, `change_date`, `changed_by`, `reason`) VALUES ('5', '5', '20.00', '19.00', '2025-07-24 16:10:00', '101', 'Competitive pricing adjustment');
INSERT INTO `price_history` (`id`, `material_id`, `old_price`, `new_price`, `change_date`, `changed_by`, `reason`) VALUES ('6', '6', '250.00', '300.00', '2025-07-27 21:09:29', '10', 'demand');
INSERT INTO `price_history` (`id`, `material_id`, `old_price`, `new_price`, `change_date`, `changed_by`, `reason`) VALUES ('7', '26', '28.57', '29.57', '2025-07-27 21:15:49', '10', 'Demand');
INSERT INTO `price_history` (`id`, `material_id`, `old_price`, `new_price`, `change_date`, `changed_by`, `reason`) VALUES ('8', '6', '450.00', '451.00', '2025-09-27 02:10:42', '43', 'demand');
INSERT INTO `price_history` (`id`, `material_id`, `old_price`, `new_price`, `change_date`, `changed_by`, `reason`) VALUES ('9', '6', '451.00', '450.00', '2025-09-27 02:10:54', '43', 'no');
INSERT INTO `price_history` (`id`, `material_id`, `old_price`, `new_price`, `change_date`, `changed_by`, `reason`) VALUES ('10', '6', '450.00', '451.00', '2025-09-29 05:44:57', '43', 'no');
INSERT INTO `price_history` (`id`, `material_id`, `old_price`, `new_price`, `change_date`, `changed_by`, `reason`) VALUES ('11', '6', '447.61', '487.61', '2025-09-29 06:01:14', '43', 'asd');
INSERT INTO `price_history` (`id`, `material_id`, `old_price`, `new_price`, `change_date`, `changed_by`, `reason`) VALUES ('12', '6', '450.00', '458.00', '2025-09-30 18:41:10', '43', 'test');
INSERT INTO `price_history` (`id`, `material_id`, `old_price`, `new_price`, `change_date`, `changed_by`, `reason`) VALUES ('13', '6', '450.00', '450.00', '2025-10-06 04:12:23', '43', 'indemand');

-- --------------------------------------------------------
-- Table structure for table `price_logs`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `price_logs`;
CREATE TABLE `price_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `material_type` varchar(50) DEFAULT NULL,
  `old_buying_price` decimal(10,2) DEFAULT NULL,
  `new_buying_price` decimal(10,2) DEFAULT NULL,
  `old_selling_price` decimal(10,2) DEFAULT NULL,
  `new_selling_price` decimal(10,2) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_price_admin` (`admin_id`),
  CONSTRAINT `fk_price_admin` FOREIGN KEY (`admin_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `profit_loss`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `profit_loss`;
CREATE TABLE `profit_loss` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `total_revenue` decimal(12,2) NOT NULL,
  `total_cost` decimal(12,2) NOT NULL,
  `gross_profit` decimal(12,2) NOT NULL,
  `operating_expenses` decimal(12,2) NOT NULL,
  `net_profit` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `profit_loss_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `referral_logs`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `referral_logs`;
CREATE TABLE `referral_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `referrer_id` int(11) NOT NULL,
  `referred_id` int(11) NOT NULL,
  `points_awarded` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_referrer` (`referrer_id`),
  KEY `fk_referred` (`referred_id`),
  CONSTRAINT `fk_referred` FOREIGN KEY (`referred_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_referrer` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `referral_logs`

INSERT INTO `referral_logs` (`id`, `referrer_id`, `referred_id`, `points_awarded`, `created_at`) VALUES ('1', '19', '20', '100', '2025-07-28 17:37:51');
INSERT INTO `referral_logs` (`id`, `referrer_id`, `referred_id`, `points_awarded`, `created_at`) VALUES ('2', '19', '21', '100', '2025-07-28 18:36:55');
INSERT INTO `referral_logs` (`id`, `referrer_id`, `referred_id`, `points_awarded`, `created_at`) VALUES ('3', '19', '22', '100', '2025-07-28 18:51:26');
INSERT INTO `referral_logs` (`id`, `referrer_id`, `referred_id`, `points_awarded`, `created_at`) VALUES ('4', '19', '23', '100', '2025-07-28 19:04:36');
INSERT INTO `referral_logs` (`id`, `referrer_id`, `referred_id`, `points_awarded`, `created_at`) VALUES ('5', '19', '24', '100', '2025-07-28 19:04:56');
INSERT INTO `referral_logs` (`id`, `referrer_id`, `referred_id`, `points_awarded`, `created_at`) VALUES ('6', '45', '46', '100', '2025-07-29 23:12:21');

-- --------------------------------------------------------
-- Table structure for table `sales_reports`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `sales_reports`;
CREATE TABLE `sales_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_sales` decimal(12,2) NOT NULL,
  `total_profit` decimal(12,2) NOT NULL,
  `items_sold` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `sales_reports_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `sales_reports`

INSERT INTO `sales_reports` (`id`, `report_name`, `start_date`, `end_date`, `total_sales`, `total_profit`, `items_sold`, `created_at`, `created_by`) VALUES ('2', 'solis', '2025-08-01', '2025-09-27', '47582.00', '-24371.20', '39', '2025-09-27 00:25:50', '8');
INSERT INTO `sales_reports` (`id`, `report_name`, `start_date`, `end_date`, `total_sales`, `total_profit`, `items_sold`, `created_at`, `created_by`) VALUES ('3', 'asdasd', '2025-08-06', '2025-10-02', '47545.00', '-24408.20', '45', '2025-10-02 12:27:18', '8');
INSERT INTO `sales_reports` (`id`, `report_name`, `start_date`, `end_date`, `total_sales`, `total_profit`, `items_sold`, `created_at`, `created_by`) VALUES ('4', 'asdasdss', '2025-08-02', '2025-10-02', '53263.09', '-18690.11', '50', '2025-10-02 12:46:41', '8');
INSERT INTO `sales_reports` (`id`, `report_name`, `start_date`, `end_date`, `total_sales`, `total_profit`, `items_sold`, `created_at`, `created_by`) VALUES ('5', 'ffssd', '2025-08-05', '2025-10-02', '53263.09', '-18690.11', '50', '2025-10-02 12:52:45', '8');
INSERT INTO `sales_reports` (`id`, `report_name`, `start_date`, `end_date`, `total_sales`, `total_profit`, `items_sold`, `created_at`, `created_by`) VALUES ('6', 'ffssd', '2025-08-05', '2025-10-02', '53263.09', '-18690.11', '50', '2025-10-02 12:54:18', '8');
INSERT INTO `sales_reports` (`id`, `report_name`, `start_date`, `end_date`, `total_sales`, `total_profit`, `items_sold`, `created_at`, `created_by`) VALUES ('7', 'ffssd', '2025-08-05', '2025-10-02', '53263.09', '-18690.11', '50', '2025-10-02 12:54:24', '8');
INSERT INTO `sales_reports` (`id`, `report_name`, `start_date`, `end_date`, `total_sales`, `total_profit`, `items_sold`, `created_at`, `created_by`) VALUES ('8', 'sddasd', '2025-10-01', '2025-10-02', '5718.09', '5718.09', '5', '2025-10-02 12:54:43', '8');

-- --------------------------------------------------------
-- Table structure for table `scheduled_price_updates`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `scheduled_price_updates`;
CREATE TABLE `scheduled_price_updates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `material_id` int(11) DEFAULT NULL,
  `new_buying_price` decimal(10,2) DEFAULT NULL,
  `new_selling_price` decimal(10,2) DEFAULT NULL,
  `scheduled_date` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `fk_sched_creator` (`created_by`),
  KEY `fk_sched_material` (`material_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `scheduled_price_updates`

INSERT INTO `scheduled_price_updates` (`id`, `material_id`, `new_buying_price`, `new_selling_price`, `scheduled_date`, `created_by`, `created_at`, `status`) VALUES ('1', '1', '13.20', '15.00', '2025-07-30', '101', '2025-07-27 21:08:33', 'pending');
INSERT INTO `scheduled_price_updates` (`id`, `material_id`, `new_buying_price`, `new_selling_price`, `scheduled_date`, `created_by`, `created_at`, `status`) VALUES ('2', '2', '7.75', '9.50', '2025-07-31', '102', '2025-07-27 21:08:33', 'pending');
INSERT INTO `scheduled_price_updates` (`id`, `material_id`, `new_buying_price`, `new_selling_price`, `scheduled_date`, `created_by`, `created_at`, `status`) VALUES ('3', '3', '16.00', '18.00', '2025-08-01', '103', '2025-07-27 21:08:33', 'completed');
INSERT INTO `scheduled_price_updates` (`id`, `material_id`, `new_buying_price`, `new_selling_price`, `scheduled_date`, `created_by`, `created_at`, `status`) VALUES ('4', '4', '5.60', '7.00', '2025-08-02', '104', '2025-07-27 21:08:33', 'cancelled');
INSERT INTO `scheduled_price_updates` (`id`, `material_id`, `new_buying_price`, `new_selling_price`, `scheduled_date`, `created_by`, `created_at`, `status`) VALUES ('5', '5', '19.50', '21.00', '2025-08-03', '101', '2025-07-27 21:08:33', 'pending');
INSERT INTO `scheduled_price_updates` (`id`, `material_id`, `new_buying_price`, `new_selling_price`, `scheduled_date`, `created_by`, `created_at`, `status`) VALUES ('6', '6', '455.00', '500.00', '2025-09-29', '43', '2025-09-27 02:11:28', 'pending');

-- --------------------------------------------------------
-- Table structure for table `security_logs`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `security_logs`;
CREATE TABLE `security_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `event_type` varchar(50) NOT NULL,
  `event_description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `shipping_methods`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `shipping_methods`;
CREATE TABLE `shipping_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `max_weight` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `shipping_methods`

INSERT INTO `shipping_methods` (`id`, `name`, `description`, `rate`, `max_weight`, `is_active`, `created_at`, `updated_at`) VALUES ('1', 'Big Truck', 'Up to 500kg load', '1500.00', '500.00', '1', '2025-08-08 20:41:39', '2025-08-08 20:41:39');
INSERT INTO `shipping_methods` (`id`, `name`, `description`, `rate`, `max_weight`, `is_active`, `created_at`, `updated_at`) VALUES ('2', 'Mini Truck', 'Up to 200kg load', '800.00', '200.00', '1', '2025-08-08 20:41:39', '2025-08-08 20:41:39');
INSERT INTO `shipping_methods` (`id`, `name`, `description`, `rate`, `max_weight`, `is_active`, `created_at`, `updated_at`) VALUES ('3', 'Side Cart', 'Up to 50kg load (Barangay only)', '50.00', '50.00', '1', '2025-08-08 20:41:39', '2025-08-08 20:41:39');
INSERT INTO `shipping_methods` (`id`, `name`, `description`, `rate`, `max_weight`, `is_active`, `created_at`, `updated_at`) VALUES ('4', 'Push Cart', 'Up to 100kg load (Barangay only)', '100.00', '100.00', '1', '2025-08-08 20:41:39', '2025-08-08 20:41:39');

-- --------------------------------------------------------
-- Table structure for table `system_settings`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('boolean','string','integer','json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `system_settings`

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`, `updated_by`) VALUES ('1', 'maintenance_mode', '0', 'boolean', 'Enable or disable maintenance mode for the entire system', '2025-10-06 18:58:24', '43');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`, `updated_by`) VALUES ('2', 'maintenance_message', 'We are currently performing scheduled maintenance. Please check back soon.', 'string', 'Message displayed during maintenance mode', '2025-09-30 02:45:10', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`, `updated_by`) VALUES ('3', 'allow_admin_access', '1', 'boolean', 'Allow administrators to access the system during maintenance', '2025-09-30 02:45:10', NULL);
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`, `updated_by`) VALUES ('4', 'debug_mode', '0', 'boolean', 'Enable debug mode for development', '2025-10-06 18:58:24', '43');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`, `updated_by`) VALUES ('5', 'app_name', 'JunkValue', 'string', 'Application name', '2025-10-06 18:58:24', '43');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`, `updated_by`) VALUES ('6', 'timezone', 'Asia/Manila', 'string', 'Default timezone for the application', '2025-10-06 18:58:24', '43');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`, `updated_by`) VALUES ('67', 'email_system_alerts', '1', 'boolean', NULL, '2025-10-05 21:30:39', '43');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`, `updated_by`) VALUES ('68', 'email_inventory_warnings', '1', 'boolean', NULL, '2025-10-05 21:30:39', '43');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`, `updated_by`) VALUES ('69', 'email_user_activities', '1', 'boolean', NULL, '2025-10-05 21:30:39', '43');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`, `updated_by`) VALUES ('70', 'email_backup_reminders', '1', 'boolean', NULL, '2025-10-05 21:30:39', '43');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`, `updated_by`) VALUES ('71', 'app_new_transactions', '1', 'boolean', NULL, '2025-10-05 21:30:39', '43');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`, `updated_by`) VALUES ('72', 'app_system_updates', '1', 'boolean', NULL, '2025-10-05 21:30:39', '43');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`, `updated_by`) VALUES ('73', 'app_scheduled_tasks', '1', 'boolean', NULL, '2025-10-05 21:30:39', '43');

-- --------------------------------------------------------
-- Table structure for table `transactions`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `transaction_type` enum('Pickup','Walk-in','Loyalty') NOT NULL,
  `type` enum('Purchase','Sale') NOT NULL DEFAULT 'Sale',
  `transaction_date` date NOT NULL,
  `transaction_time` time NOT NULL,
  `item_details` text NOT NULL,
  `additional_info` text DEFAULT NULL,
  `status` enum('Completed','Pending','Cancelled') DEFAULT 'Pending',
  `cancel_reason` text DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `points_earned` int(11) DEFAULT 0,
  `points_redeemed` int(11) DEFAULT 0,
  `pickup_date` date DEFAULT NULL,
  `time_slot` varchar(50) DEFAULT NULL,
  `shipping_method` varchar(255) DEFAULT NULL,
  `shipping_fee` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_id` (`transaction_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=169 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `transactions`

INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('31', 'TXN-20250719-00007', '1', NULL, '', 'Pickup', 'Sale', '2025-07-19', '08:24:59', 'Scheduled Pickup: Copper Wire (5kg), Iron Scrap (6kg)', 'Address: sasd\nSpecial Instructions: asdaa', 'Pending', NULL, NULL, '1358.00', '2025-07-19 14:24:59', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('32', 'TXN-20250719-00008', '1', NULL, '', 'Pickup', 'Sale', '2025-07-19', '08:26:58', 'Scheduled Pickup: Aluminum Cans (87kg), Stainless Steel (55kg), Copper Wire (30kg)', 'Address: 54 Gold Extention\nSpecial Instructions: Black gate', 'Pending', NULL, NULL, '14990.00', '2025-07-19 14:26:58', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('33', 'TXN-20250719-00009', '3', NULL, '', 'Pickup', 'Sale', '2025-07-19', '08:32:51', 'Scheduled Pickup: E-Waste (5kg)', 'Address: jan lang\nSpecial Instructions: po boss', 'Pending', NULL, NULL, '600.00', '2025-07-19 14:32:51', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('34', 'TXN-20250719-00010', '3', NULL, '', 'Pickup', 'Sale', '2025-07-19', '09:17:17', 'Scheduled Pickup: Copper Wire (600kg)', 'Address: same parin\nSpecial Instructions: Same parin', 'Pending', NULL, NULL, '150000.00', '2025-07-19 15:17:17', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('35', 'TXN-20250719-00011', '3', NULL, '', 'Pickup', 'Sale', '2025-07-19', '09:17:17', 'Scheduled Pickup: Copper Wire (600kg)', 'Address: same parin\nSpecial Instructions: Same parin', 'Pending', NULL, NULL, '150000.00', '2025-07-19 15:17:17', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('36', 'TXN-20250719-00012', '3', NULL, '', 'Pickup', 'Sale', '2025-07-19', '09:17:39', 'Scheduled Pickup: Aluminum Cans (63kg)', 'Address: asd\nSpecial Instructions: asd', 'Pending', NULL, NULL, '2835.00', '2025-07-19 15:17:39', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('37', 'TXN-20250719-00013', '3', NULL, '', 'Pickup', 'Sale', '2025-07-19', '09:17:54', 'Scheduled Pickup: Stainless Steel (34kg)', 'Address: asdas\nSpecial Instructions: asdasd', 'Pending', NULL, NULL, '2210.00', '2025-07-19 15:17:54', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('38', 'TXN-20250719-00014', '3', NULL, '', 'Pickup', 'Sale', '2025-07-19', '09:18:14', 'Scheduled Pickup: E-Waste (22kg)', 'Address: asdasd\nSpecial Instructions: adasd', 'Pending', NULL, NULL, '2640.00', '2025-07-19 15:18:14', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('39', 'TXN-20250719-00015', '1', NULL, '', 'Pickup', 'Sale', '2025-07-19', '10:53:02', 'Scheduled Pickup: Copper Wire (0.7kg)', 'Address: Iloveyou so much\nSpecial Instructions: balck gate', 'Pending', NULL, NULL, '175.00', '2025-07-19 16:53:02', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('40', 'TXN-20250719-00016', '1', NULL, '', 'Pickup', 'Sale', '2025-07-19', '11:24:00', 'Scheduled Pickup: Cardboard (1kg)', 'Address: 054 gold extention\nSpecial Instructions: asdasd', 'Pending', NULL, NULL, '17.14', '2025-07-19 17:24:00', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('41', 'TXN-20250719-00017', '1', NULL, '', 'Pickup', 'Sale', '2025-07-19', '11:47:28', 'Scheduled Pickup: PET Bottles (5kg), Computer Parts (1kg)', 'Address: 054 gold extention\nSpecial Instructions: Black green gate', 'Pending', NULL, NULL, '228.56', '2025-07-19 17:47:28', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('42', 'TXN-20250719-00018', '1', NULL, '', 'Pickup', 'Sale', '2025-07-19', '11:48:15', 'Scheduled Pickup: PET Bottles (2kg)', 'Address: 054 gold extention\nSpecial Instructions: red gate', 'Pending', NULL, NULL, '57.14', '2025-07-19 17:48:15', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('43', 'TXN-20250719-00019', '1', NULL, '', 'Pickup', 'Sale', '2025-07-19', '11:49:07', 'Scheduled Pickup: PET Bottles (1kg)', 'Address: 054 gold extention\nSpecial Instructions: pink gate', 'Pending', NULL, NULL, '28.57', '2025-07-19 17:49:07', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('44', 'TXN-20250719-00020', '1', NULL, '', 'Pickup', 'Sale', '2025-07-19', '12:09:42', 'Scheduled Pickup: Steel (7kg)', 'Address: 054 gold extention\nSpecial Instructions: You', 'Pending', NULL, NULL, '319.97', '2025-07-19 18:09:42', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('45', 'TXN-20250719-00021', '1', NULL, '', 'Pickup', 'Sale', '2025-07-19', '12:19:05', 'Scheduled Pickup: PET Bottles (1kg)', 'Address: 054 gold extention\nSpecial Instructions: asdasd', 'Pending', NULL, NULL, '28.57', '2025-07-19 18:19:05', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('46', 'TXN-20250719-00022', '1', NULL, '', 'Pickup', 'Sale', '2025-07-19', '12:20:48', 'Scheduled Pickup: Cardboard (2kg)', 'Address: 054 gold extention\nSpecial Instructions: asdasd', 'Pending', NULL, NULL, '34.28', '2025-07-19 18:20:48', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('47', 'TXN-20250719-00023', '1', NULL, '', 'Pickup', 'Sale', '2025-07-19', '12:21:25', 'Scheduled Pickup: Aluminum Cans (1212kg)', 'Address: 054 gold extention\nSpecial Instructions: asd22', 'Pending', NULL, NULL, '54540.00', '2025-07-19 18:21:25', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('48', 'TXN-20250719-00024', '1', NULL, '', 'Pickup', 'Sale', '2025-07-19', '12:33:18', 'Scheduled Pickup: Cardboard (1kg)', 'Address: 054 gold extention\nSpecial Instructions: asd', 'Pending', NULL, NULL, '17.14', '2025-07-19 18:33:18', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('49', 'TXN-20250719-00025', '1', NULL, '', 'Pickup', 'Sale', '2025-07-19', '12:36:14', 'Scheduled Pickup: Cardboard (9kg)', 'Address: 054 gold extention\nSpecial Instructions: asdasd', 'Pending', NULL, NULL, '154.26', '2025-07-19 18:36:14', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('50', 'TXN-20250719-00026', '1', NULL, '', 'Pickup', 'Sale', '2025-07-19', '12:36:14', 'Scheduled Pickup: Cardboard (9kg)', 'Address: 054 gold extention\nSpecial Instructions: asdasd', 'Pending', NULL, NULL, '154.26', '2025-07-19 18:36:14', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('51', 'TXN-20250719-00027', '8', NULL, '', 'Pickup', 'Sale', '2025-07-19', '13:33:33', 'Scheduled Pickup: Copper Wire (1kg), Aluminum Cans (2kg)', 'Address: Gold st\nSpecial Instructions: WTF', 'Pending', NULL, NULL, '340.00', '2025-07-19 19:33:33', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('52', 'TXN-20250719-00028', '1', NULL, '', 'Pickup', 'Sale', '2025-07-19', '17:23:24', 'Scheduled Pickup: PET Bottles (12kg)', 'Address: 054 gold extention\nSpecial Instructions: asd', 'Pending', NULL, NULL, '342.84', '2025-07-19 23:23:24', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('53', 'TXN-20250719-00029', '10', NULL, '', 'Pickup', 'Sale', '2025-07-19', '17:24:06', 'Scheduled Pickup: Cardboard (232kg)', 'Address: 054 gold extention\nSpecial Instructions: asdasd', 'Pending', NULL, NULL, '3976.48', '2025-07-19 23:24:06', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('54', 'TXN-20250719-00030', '10', NULL, '', 'Pickup', 'Sale', '2025-07-19', '17:24:40', 'Scheduled Pickup: Copper Wire (300kg)', 'Address: 054 gold extention\nSpecial Instructions: 123123', 'Pending', NULL, NULL, '75000.00', '2025-07-19 23:24:40', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('55', 'TXN-20250719-00031', '12', NULL, '', 'Pickup', 'Sale', '2025-07-19', '17:27:32', 'Scheduled Pickup: Copper Wire (500kg)', 'Address: 054 gold extention\nSpecial Instructions: asdasd', 'Pending', NULL, NULL, '125000.00', '2025-07-19 23:27:32', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('56', 'TXN-20250720-00032', '10', NULL, '', 'Pickup', 'Sale', '2025-07-20', '06:39:51', 'Scheduled Pickup: Aluminum Cans (12kg)', 'Address: 054 gold extention\nSpecial Instructions: asd', 'Pending', NULL, NULL, '540.00', '2025-07-20 12:39:51', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('57', 'TXN-20250720-00033', '10', NULL, '', 'Pickup', 'Sale', '2025-07-20', '06:42:08', 'Scheduled Pickup: Copper Wire (1kg)', 'Address: 054 gold extention\nSpecial Instructions: 123', 'Pending', NULL, NULL, '250.00', '2025-07-20 12:42:08', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('58', 'TXN-20250720-00034', '1', NULL, '', 'Pickup', 'Sale', '2025-07-20', '06:43:26', 'Scheduled Pickup: Aluminum Cans (4kg)', 'Address: 054 gold extention\nSpecial Instructions: asd', 'Pending', NULL, NULL, '180.00', '2025-07-20 12:43:26', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('59', 'TXN-20250720-00035', '12', NULL, '', 'Pickup', 'Sale', '2025-07-20', '06:51:48', 'Scheduled Pickup: Cardboard (21kg)', 'Address: 054 gold extention\nSpecial Instructions: 1212', 'Pending', NULL, NULL, '359.94', '2025-07-20 12:51:48', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('60', 'TXN-20250721-00036', '1', NULL, '', 'Pickup', 'Sale', '2025-07-21', '07:24:12', 'Scheduled Pickup: PET Bottles (5kg)', 'Address: 054 gold extention baranggay commonwelth qc\nSpecial Instructions: Black gate', 'Pending', NULL, NULL, '142.85', '2025-07-21 13:24:12', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('61', 'TXN-20250722-00037', '12', NULL, '', 'Pickup', 'Sale', '2025-07-22', '16:11:39', 'Scheduled Pickup: Copper Wire (50kg)', 'Address: caloocan st bagong silang\nSpecial Instructions: Black gate', 'Pending', NULL, NULL, '12500.00', '2025-07-22 22:11:39', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('62', 'TXN-20250723-00038', '15', NULL, '', 'Pickup', 'Sale', '2025-07-23', '11:28:42', 'Scheduled Pickup: Copper Wire (3kg), Glass Bottles (5kg), Cardboard (10kg), PET Bottles (15kg), Computer Parts (20kg), E-Waste (10kg)', 'Address: 565 hajsmnah\nSpecial Instructions:  kunin sa aso ko', 'Pending', NULL, NULL, '4335.60', '2025-07-23 17:28:42', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('63', 'TXN-20250723-00039', '12', NULL, '', 'Pickup', 'Sale', '2025-07-23', '11:32:02', 'Scheduled Pickup: PET Bottles (50kg)', 'Address: caloocan st bagong silang\nSpecial Instructions: sa kanto nalang', 'Pending', NULL, NULL, '1428.50', '2025-07-23 17:32:02', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('64', 'TXN-20250724-00040', '1', NULL, '', 'Pickup', 'Sale', '2025-07-24', '06:25:55', 'Scheduled Pickup: PET Bottles (1kg)', 'Address: 054 gold extention baranggay commonwelt12h qc\nSpecial Instructions: blue gate', 'Pending', NULL, NULL, '28.57', '2025-07-24 12:25:55', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('65', 'TXN-20250725-00041', '1', NULL, '', 'Pickup', 'Sale', '2025-07-25', '14:55:33', 'Scheduled Pickup: Cardboard (5kg), PET Bottles (2kg)', 'Address: 054 gold extention baranggay commonwelth qc', 'Pending', NULL, NULL, '142.84', '2025-07-25 20:55:33', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('66', 'TXN-20250726-00042', '4', NULL, '', 'Pickup', 'Sale', '2025-07-26', '07:13:36', 'Scheduled Pickup: Cardboard (1212kg)', 'Address: 054 GOLD EXTENTION, BARANGGAY COMMONWELTH QC\nSpecial Instructions: asdad', '', NULL, NULL, '20773.68', '2025-07-26 13:13:36', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('67', 'TXN-20250727-00043', '16', NULL, '', 'Pickup', 'Sale', '2025-07-27', '09:34:28', 'Scheduled Pickup: Steel (1kg)', 'Address: 054 gold extention\nSpecial Instructions: asdasdasd', 'Pending', NULL, NULL, '45.71', '2025-07-27 15:34:28', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('68', 'TXN-20250727-00044', '16', NULL, '', 'Pickup', 'Sale', '2025-07-27', '17:18:39', 'Scheduled Pickup: Cardboard (123kg)', 'Address: 054 gold extention\nSpecial Instructions: asdasd', 'Cancelled', 'df', '2025-09-29 03:37:42', '2108.22', '2025-07-27 23:18:39', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('69', 'TXN-20250727-00045', '1', NULL, '', 'Pickup', 'Sale', '2025-07-27', '18:26:17', 'Scheduled Pickup: Copper Wire (111kg)', 'Address: 054 gold extention baranggay commonwelth qc\nSpecial Instructions: asdasd', 'Completed', NULL, NULL, '33300.00', '2025-07-28 00:26:17', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('70', 'TXN-20250727-00046', '12', NULL, '', 'Pickup', 'Sale', '2025-07-27', '19:05:50', 'Scheduled Pickup: Copper Wire (123kg)', 'Address: caloocan st bagong silang\nSpecial Instructions: asdasd', 'Cancelled', NULL, NULL, '36900.00', '2025-07-28 01:05:50', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('71', 'TXN-20250728-00047', '12', NULL, '', 'Pickup', 'Sale', '2025-07-28', '05:27:19', 'Scheduled Pickup: Copper Wire (1kg)', 'Address: caloocan st bagong silang\nSpecial Instructions: asdasd', 'Completed', NULL, NULL, '300.00', '2025-07-28 11:27:19', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('72', 'TXN-20250728-00048', '12', NULL, '', 'Pickup', 'Sale', '2025-07-28', '05:28:56', 'Scheduled Pickup: PET Bottles (1kg)', 'Address: caloocan st bagong silang\nSpecial Instructions: asdasd', 'Completed', NULL, NULL, '29.57', '2025-07-28 11:28:56', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('73', 'TXN-20250728-00049', '16', NULL, '', 'Pickup', 'Sale', '2025-07-28', '06:01:15', 'Scheduled Pickup: Aluminum Cans (1kg)', 'Address: 054 gold extention\nSpecial Instructions: asdasdsa', 'Pending', NULL, NULL, '45.00', '2025-07-28 12:01:15', '0', '0', '2025-07-31', '3:00 - 5:00 PM', NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('74', 'TXN-20250728-00050', '16', NULL, '', 'Pickup', 'Sale', '2025-07-28', '06:22:34', 'Scheduled Pickup: Copper Wire (1kg)', 'Address: 054 gold extention\nSpecial Instructions: asdasd', 'Completed', NULL, NULL, '300.00', '2025-07-28 12:22:34', '0', '0', '2025-08-01', '8:00 - 10:00 AM', NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('75', 'TXN-20250728-00051', '16', NULL, '', 'Pickup', 'Sale', '2025-07-28', '06:23:10', 'Scheduled Pickup: Cardboard (1kg)', 'Address: 054 gold extention\nSpecial Instructions: asdasd', 'Cancelled', NULL, NULL, '17.14', '2025-07-28 12:23:10', '0', '0', '2025-07-31', '8:00 - 10:00 AM', NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('76', 'TXN-20250728-00054', '1', NULL, '', 'Pickup', 'Sale', '2025-07-28', '08:02:18', 'Scheduled Pickup: Steel (1kg)', 'Address: 054 gold extention baranggay commonwelth qc\nSpecial Instructions: asdasd', 'Pending', NULL, NULL, '45.71', '2025-07-28 14:02:18', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('77', 'TXN-20250728-00055', '1', NULL, '', 'Pickup', 'Sale', '2025-07-28', '08:03:39', 'Scheduled Pickup: PET Bottles (1kg)', 'Address: 054 gold extention baranggay commonwelth qc\nSpecial Instructions: asd', 'Pending', NULL, NULL, '29.57', '2025-07-28 14:03:39', '0', '0', '2025-08-09', '1:00 - 3:00 PM', NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('78', 'TXN-20250728-00056', '1', NULL, 'Naoi', 'Pickup', 'Sale', '2025-07-28', '08:06:36', 'Scheduled Pickup: E-Waste (1kg)', 'Address: 054 gold extention baranggay commonwelth qc\nSpecial Instructions: asdasd', 'Completed', NULL, NULL, '120.00', '2025-07-28 14:06:36', '0', '0', '2025-07-28', '8:00 - 10:00 AM', NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('79', 'TXN-20250728-00057', '12', NULL, 'Jordan', 'Pickup', 'Sale', '2025-07-28', '08:10:14', 'Scheduled Pickup: PET Bottles (1kg)', 'Address: caloocan st bagong silang\nSpecial Instructions: adasd', 'Completed', NULL, NULL, '29.57', '2025-07-28 14:10:14', '0', '0', '2025-07-29', '3:00 - 5:00 PM', NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('80', 'TXN-20250728-00058', '16', NULL, 'Pham', 'Pickup', 'Sale', '2025-07-28', '11:24:55', 'Scheduled Pickup: Copper Wire (12kg)', 'Address: 054 gold extention\nSpecial Instructions: asd', 'Completed', NULL, NULL, '3600.00', '2025-07-28 17:24:55', '0', '0', '2025-08-01', '10:00 - 12:00 PM', NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('81', 'TXN-20250728-00059', '16', NULL, 'Pham', 'Pickup', 'Sale', '2025-07-28', '11:25:45', 'Scheduled Pickup: Copper Wire (31kg)', 'Address: 054 gold extention\nSpecial Instructions: asdasd', 'Completed', NULL, NULL, '9300.00', '2025-07-28 17:25:45', '0', '0', '2025-08-26', '1:00 - 3:00 PM', NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('82', 'TXN-20250803-6409', '19', NULL, '', '', 'Sale', '2025-08-04', '03:07:14', '', 'bug', 'Cancelled', NULL, NULL, '0.00', '2025-08-04 03:07:14', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('83', 'TXN-20250803-3956', '19', NULL, '', '', 'Sale', '2025-08-04', '03:10:46', '', 'random rewards', 'Cancelled', NULL, NULL, '0.00', '2025-08-04 03:10:46', '3000', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('84', 'TXN-20250803-4096', '19', NULL, '', '', 'Sale', '2025-08-04', '03:11:17', '', 'bug', 'Cancelled', NULL, NULL, '0.00', '2025-08-04 03:11:17', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('85', 'TXN-20250803-00060', '52', NULL, '', 'Pickup', 'Sale', '2025-08-03', '21:14:16', 'Scheduled Pickup: PET Bottles (6kg)', 'Address: 054 gold extention\nSpecial Instructions: asdasdasd', 'Completed', NULL, NULL, '177.42', '2025-08-04 03:14:16', '0', '0', '2025-08-06', '1:00 - 3:00 PM', NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('86', 'TXN-20250803-1175', '19', NULL, '', '', 'Sale', '2025-08-04', '03:33:47', '', 'asdasd', 'Pending', NULL, NULL, '0.00', '2025-08-04 03:33:47', '100', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('87', 'TXN-20250803-0147', '19', NULL, '', '', 'Sale', '2025-08-04', '03:34:10', '', 'asdasd', 'Completed', NULL, NULL, '0.00', '2025-08-04 03:34:10', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('88', 'TXN-20250808-00061', '54', NULL, 'viray', 'Pickup', 'Sale', '2025-08-08', '15:02:08', 'Scheduled Pickup: Steel (1kg)', 'Address: 054 gold extention\nShipping Method: Push Cart (₱100.00)', 'Cancelled', 'awes', '2025-08-08 21:07:38', '108.00', '2025-08-08 21:02:08', '0', '0', '2025-08-15', '8:00 - 10:00 AM', NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('89', 'TXN-20250808-00062', '54', NULL, 'viray', 'Pickup', 'Sale', '2025-08-08', '15:25:37', 'Scheduled Pickup: Aluminum Cans (21kg)', 'Address: 054 gold extention\nSpecial Instructions: black gate\nShipping Method: Side Cart (₱50.00)', 'Cancelled', 'ayoko naaa', '2025-08-08 21:25:57', '1625.00', '2025-08-08 21:25:37', '0', '0', '2025-08-15', '10:00 - 12:00 PM', NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('90', 'TXN-20250808-00063', '54', NULL, 'viray', 'Pickup', 'Sale', '2025-08-08', '15:26:38', 'Scheduled Pickup: Steel (12kg)', 'Address: 054 gold extention\nSpecial Instructions: Black Gate\nShipping Method: Side Cart (₱50.00)', 'Cancelled', 'iloveyou', '2025-08-08 21:26:43', '146.00', '2025-08-08 21:26:38', '0', '0', '2025-08-30', '8:00 - 10:00 AM', NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('91', 'TXN-20250809-43360', '0', '8', 'asd', '', 'Purchase', '2025-08-09', '07:38:16', '[{\"material\":\"Computer Parts\",\"quantity\":12,\"unit\":\"kg\",\"price_per_kg\":\"250.00\",\"total\":3000}]', NULL, 'Completed', NULL, NULL, '3000.00', '2025-08-09 13:38:16', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('92', 'TXN-20250809-98327', '0', '8', 'solis', '', 'Purchase', '2025-08-09', '07:47:38', '[{\"material\":\"Copper Wire\",\"quantity\":21,\"unit\":\"kg\",\"price_per_kg\":\"450.00\",\"total\":9450},{\"material\":\"Batteries\",\"quantity\":31,\"unit\":\"kg\",\"price_per_kg\":\"25.00\",\"total\":775},{\"material\":\"Computer Parts\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"250.00\",\"total\":250}]', NULL, 'Completed', NULL, NULL, '10475.00', '2025-08-09 13:47:38', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('93', 'TXN-20250809-88150', '0', '8', 'solis', '', 'Purchase', '2025-08-09', '07:49:20', '[{\"material\":\"Copper Wire\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"450.00\",\"total\":450},{\"material\":\"Stainless Steel\",\"quantity\":2,\"unit\":\"kg\",\"price_per_kg\":\"65.00\",\"total\":130},{\"material\":\"E-Waste\",\"quantity\":4,\"unit\":\"kg\",\"price_per_kg\":\"120.00\",\"total\":480},{\"material\":\"Yero (Corrugated Sheets)\",\"quantity\":5,\"unit\":\"kg\",\"price_per_kg\":\"7.00\",\"total\":35},{\"material\":\"Batteries\",\"quantity\":6,\"unit\":\"kg\",\"price_per_kg\":\"25.00\",\"total\":150},{\"material\":\"Iron Scrap\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"18.00\",\"total\":18}]', NULL, 'Completed', NULL, NULL, '1263.00', '2025-08-09 13:49:20', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('94', 'TXN-20250809-12384', '0', '8', 'Aye', '', 'Purchase', '2025-08-09', '08:01:47', '[{\"material\":\"Copper Wire\",\"quantity\":2,\"unit\":\"kg\",\"price_per_kg\":\"450.00\",\"total\":900},{\"material\":\"Batteries\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"25.00\",\"total\":25}]', NULL, 'Completed', NULL, NULL, '925.00', '2025-08-09 14:01:47', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('95', 'TXN-20250809-61706', '0', '8', 'solis', '', 'Purchase', '2025-08-09', '08:02:38', '[{\"material\":\"Yero (Corrugated Sheets)\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"7.00\",\"total\":7}]', NULL, 'Completed', NULL, NULL, '7.00', '2025-08-09 14:02:38', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('96', 'TXN-20250809-31172', '0', '8', 'aye rodrigez', '', 'Purchase', '2025-08-09', '08:36:06', '[{\"material\":\"Copper Wire\",\"quantity\":21,\"unit\":\"kg\",\"price_per_kg\":\"450.00\",\"total\":9450},{\"material\":\"Computer Parts\",\"quantity\":2,\"unit\":\"kg\",\"price_per_kg\":\"250.00\",\"total\":500},{\"material\":\"Batteries\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"25.00\",\"total\":25}]', NULL, 'Completed', NULL, NULL, '9975.00', '2025-08-09 14:36:06', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('97', 'TXN-20250809-87807', '0', '8', 'asdasd', '', 'Purchase', '2025-08-09', '08:36:34', '[{\"material\":\"E-Waste\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"120.00\",\"total\":120}]', NULL, 'Completed', NULL, NULL, '120.00', '2025-08-09 14:36:34', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('98', 'TXN-20250809-85187', '0', '8', 'asd', '', 'Purchase', '2025-08-09', '08:37:53', '[{\"material\":\"E-Waste\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"120.00\",\"total\":120}]', NULL, 'Completed', NULL, NULL, '120.00', '2025-08-09 14:37:53', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('99', 'TXN-20250809-67344', '0', '8', 'Aye', '', 'Purchase', '2025-08-09', '08:39:23', '[{\"material\":\"E-Waste\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"120.00\",\"total\":120}]', NULL, 'Completed', NULL, NULL, '120.00', '2025-08-09 14:39:23', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('100', 'TXN-20250809-76676', '0', '8', 'Aye', '', 'Purchase', '2025-08-09', '08:40:48', '[{\"material\":\"Stainless Steel\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"65.00\",\"total\":65}]', NULL, 'Completed', NULL, NULL, '65.00', '2025-08-09 14:40:48', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('101', 'TXN-20250809-24983', '0', '8', 'solis', '', 'Purchase', '2025-08-09', '08:41:17', '[{\"material\":\"Yero (Corrugated Sheets)\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"7.00\",\"total\":7}]', NULL, 'Completed', NULL, NULL, '7.00', '2025-08-09 14:41:17', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('102', 'TXN-20250809-39329', '0', '8', 'asd', '', 'Purchase', '2025-08-09', '08:48:37', '[{\"material\":\"Stainless Steel\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"65.00\",\"total\":65}]', NULL, 'Completed', NULL, NULL, '65.00', '2025-08-09 14:48:37', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('103', 'TXN-20250809-19267', '0', '8', 'aye rodrigez', '', 'Purchase', '2025-08-09', '08:51:28', '[{\"material\":\"Copper Wire\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"450.00\",\"total\":450},{\"material\":\"Batteries\",\"quantity\":2,\"unit\":\"kg\",\"price_per_kg\":\"25.00\",\"total\":50},{\"material\":\"Iron Scrap\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"18.00\",\"total\":18},{\"material\":\"Glass Bottles\",\"quantity\":4,\"unit\":\"kg\",\"price_per_kg\":\"2.00\",\"total\":8}]', NULL, 'Completed', NULL, NULL, '526.00', '2025-08-09 14:51:28', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('104', 'TXN-20250809-86903', '0', '8', 'bing solis', '', 'Purchase', '2025-08-09', '08:53:20', '[{\"material\":\"Copper Wire\",\"quantity\":21,\"unit\":\"kg\",\"price_per_kg\":\"450.00\",\"total\":9450},{\"material\":\"Batteries\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"25.00\",\"total\":25},{\"material\":\"PET Bottles\",\"quantity\":21,\"unit\":\"kg\",\"price_per_kg\":\"9.00\",\"total\":189},{\"material\":\"Computer Parts\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"250.00\",\"total\":250},{\"material\":\"Iron Scrap\",\"quantity\":21,\"unit\":\"kg\",\"price_per_kg\":\"18.00\",\"total\":378}]', NULL, 'Completed', NULL, NULL, '10292.00', '2025-08-09 14:53:20', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('105', 'TXN-20250809-74109', '0', '8', 'asd', '', 'Purchase', '2025-08-09', '08:54:24', '[{\"material\":\"Iron Scrap\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"18.00\",\"total\":18}]', NULL, 'Completed', NULL, NULL, '18.00', '2025-08-09 14:54:24', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('106', 'TXN-20250809-27904', '0', '8', 'mics trinidad', '', 'Purchase', '2025-08-09', '09:16:57', '[{\"material\":\"Cardboard\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"2.00\",\"total\":2}]', NULL, 'Completed', NULL, NULL, '2.00', '2025-08-09 15:16:57', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('107', 'TXN-20250809-40749', '0', '8', 'solos', '', 'Purchase', '2025-08-09', '09:18:16', '[{\"material\":\"Iron Scrap\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"18.00\",\"total\":18}]', NULL, 'Completed', NULL, NULL, '18.00', '2025-08-09 15:18:16', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('109', 'TXN-20250809-01460', '34', '8', 'mics trinidad', '', 'Purchase', '2025-08-09', '09:32:53', '[{\"material\":\"Iron Scrap\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"18.00\",\"total\":18}]', NULL, 'Completed', NULL, NULL, '18.00', '2025-08-09 15:32:53', '1', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('110', 'TXN-20250809-38656', '34', '8', 'asd', '', 'Purchase', '2025-08-09', '09:33:28', '[{\"material\":\"Iron Scrap\",\"quantity\":121,\"unit\":\"kg\",\"price_per_kg\":\"18.00\",\"total\":2178}]', NULL, 'Completed', NULL, NULL, '2178.00', '2025-08-09 15:33:28', '121', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('111', 'TXN-20250809-50752', '54', '8', 'yukki', '', 'Purchase', '2025-08-09', '09:35:02', '[{\"material\":\"Stainless Steel\",\"quantity\":12,\"unit\":\"kg\",\"price_per_kg\":\"65.00\",\"total\":780}]', NULL, 'Completed', NULL, NULL, '780.00', '2025-08-09 15:35:02', '12', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('112', 'TXN-20250809-00064', '54', NULL, 'viray', 'Pickup', 'Sale', '2025-08-09', '09:46:21', 'Scheduled Pickup: PET Bottles (1kg)', 'Address: 10 Sto. Niño St. Barangay Commonwealth Quezon city \\r\\n\nSpecial Instructions: black gate\nShipping Method: Mini Truck (₱800.00)', 'Completed', NULL, NULL, '809.00', '2025-08-09 15:46:21', '0', '0', '2025-08-14', '8:00 - 10:00 AM', NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('113', 'TXN-20250809-63336', '34', '8', 'asd', '', 'Sale', '2025-08-09', '10:19:04', '[{\"material\":\"E-Waste\",\"quantity\":3,\"unit\":\"kg\",\"price_per_kg\":\"120.00\",\"total\":360}]', NULL, 'Completed', NULL, NULL, '360.00', '2025-08-09 16:19:04', '3', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('114', 'TXN-20250809-77860', '54', '8', 'asd', '', 'Sale', '2025-08-09', '10:19:44', '[{\"material\":\"Iron Scrap\",\"quantity\":2,\"unit\":\"kg\",\"price_per_kg\":\"18.00\",\"total\":36}]', NULL, 'Completed', NULL, NULL, '36.00', '2025-08-09 16:19:44', '2', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('115', 'TXN-20250809-68039', '54', '8', 'Solis', '', 'Sale', '2025-08-09', '10:20:36', '[{\"material\":\"E-Waste\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"120.00\",\"total\":120}]', NULL, 'Completed', NULL, NULL, '120.00', '2025-08-09 16:20:36', '1', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('116', 'TXN-20250809-35098', '54', '8', 'asd', '', 'Sale', '2025-08-09', '10:24:16', '[{\"material\":\"Iron Scrap\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"18.00\",\"total\":18}]', NULL, 'Completed', NULL, NULL, '18.00', '2025-08-09 16:24:16', '1', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('117', 'TXN-20250809-07043', '54', '8', 'solis', '', 'Sale', '2025-08-09', '10:26:55', '[{\"material\":\"Iron Scrap\",\"quantity\":121,\"unit\":\"kg\",\"price_per_kg\":\"18.00\",\"total\":2178}]', NULL, 'Completed', NULL, NULL, '2178.00', '2025-08-09 16:26:55', '121', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('118', 'TXN-20250815-50566', '54', '8', 'asd', '', 'Sale', '2025-08-15', '05:33:10', '[{\"material\":\"Stainless Steel\",\"quantity\":21,\"unit\":\"kg\",\"price_per_kg\":\"65.00\",\"total\":1365}]', NULL, 'Completed', NULL, NULL, '1365.00', '2025-08-15 11:33:10', '21', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('120', 'TXN-20250828-44581', '54', '8', 'mics trinidad', '', 'Sale', '2025-08-28', '08:42:43', '[{\"material\":\"Computer Parts\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"250.00\",\"total\":250},{\"material\":\"E-Waste\",\"quantity\":2,\"unit\":\"kg\",\"price_per_kg\":\"120.00\",\"total\":240},{\"material\":\"Yero (Corrugated Sheets)\",\"quantity\":3,\"unit\":\"kg\",\"price_per_kg\":\"7.00\",\"total\":21}]', NULL, 'Completed', NULL, NULL, '511.00', '2025-08-28 14:42:43', '6', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('121', 'TXN-20250828-98110', '54', '8', 'solis', '', 'Sale', '2025-08-28', '09:09:05', '[{\"material\":\"E-Waste\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"120.00\",\"total\":120},{\"material\":\"Iron Scrap\",\"quantity\":2,\"unit\":\"kg\",\"price_per_kg\":\"18.00\",\"total\":36},{\"material\":\"E-Waste\",\"quantity\":3,\"unit\":\"kg\",\"price_per_kg\":\"120.00\",\"total\":360}]', NULL, 'Completed', NULL, NULL, '516.00', '2025-08-28 15:09:05', '6', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('122', 'TXN-20250828-86599', '54', '8', 'solis', '', 'Sale', '2025-08-28', '09:12:19', '[{\"material\":\"Stainless Steel\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"65.00\",\"total\":65}]', NULL, 'Completed', NULL, NULL, '65.00', '2025-08-28 15:12:19', '1', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('123', 'TXN-20250828-06323', NULL, '8', 'solis', '', 'Sale', '2025-08-28', '09:39:13', '[{\"material\":\"Computer Parts\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"250.00\",\"total\":250}]', NULL, 'Completed', NULL, NULL, '250.00', '2025-08-28 15:39:13', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('124', 'TXN-20250828-16970', '54', '8', 'mics trinidad', '', 'Sale', '2025-08-28', '09:39:25', '[{\"material\":\"Iron Scrap\",\"quantity\":2,\"unit\":\"kg\",\"price_per_kg\":\"18.00\",\"total\":36}]', NULL, 'Completed', NULL, NULL, '36.00', '2025-08-28 15:39:25', '2', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('125', 'TXN-20250828-92912', NULL, '8', 'Aye', '', 'Sale', '2025-08-28', '09:39:40', '[{\"material\":\"Stainless Steel\",\"quantity\":2,\"unit\":\"kg\",\"price_per_kg\":\"65.00\",\"total\":130}]', NULL, 'Completed', NULL, NULL, '130.00', '2025-08-28 15:39:40', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('126', 'TXN-20250828-67996', '54', '8', 'Solis', '', 'Sale', '2025-08-28', '09:39:48', '[{\"material\":\"Yero (Corrugated Sheets)\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"7.00\",\"total\":7}]', NULL, 'Completed', NULL, NULL, '7.00', '2025-08-28 15:39:48', '1', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('127', 'TXN-20250828-47289', NULL, '8', 'Solis', '', 'Sale', '2025-08-28', '09:39:56', '[{\"material\":\"Stainless Steel\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"65.00\",\"total\":65}]', NULL, 'Completed', NULL, NULL, '65.00', '2025-08-28 15:39:56', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('128', 'TXN-20250828-90218', '54', '8', 'solisss', '', 'Sale', '2025-08-28', '09:41:05', '[{\"material\":\"E-Waste\",\"quantity\":3,\"unit\":\"kg\",\"price_per_kg\":\"120.00\",\"total\":360}]', NULL, 'Completed', NULL, NULL, '360.00', '2025-08-28 15:41:05', '3', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('129', 'TXN-20250828-15756', NULL, '8', 'mics trinidad', '', 'Sale', '2025-08-28', '09:41:24', '[{\"material\":\"E-Waste\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"120.00\",\"total\":120},{\"material\":\"Computer Parts\",\"quantity\":2,\"unit\":\"kg\",\"price_per_kg\":\"250.00\",\"total\":500},{\"material\":\"Yero (Corrugated Sheets)\",\"quantity\":2,\"unit\":\"kg\",\"price_per_kg\":\"7.00\",\"total\":14}]', NULL, 'Completed', NULL, NULL, '634.00', '2025-08-28 15:41:24', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('130', 'TXN-20250828-18975', '54', '8', 'mics trinidad', '', 'Sale', '2025-08-28', '09:41:56', '[{\"material\":\"Stainless Steel\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"65.00\",\"total\":65}]', NULL, 'Completed', NULL, NULL, '65.00', '2025-08-28 15:41:56', '1', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('131', 'TXN-20250828-62228', NULL, '8', 'aye rodrigez', '', 'Sale', '2025-08-28', '09:42:42', '[{\"material\":\"Iron Scrap\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"18.00\",\"total\":18}]', NULL, 'Completed', NULL, NULL, '18.00', '2025-08-28 15:42:42', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('132', 'TXN-20250928-2225', '19', NULL, '', '', 'Sale', '2025-09-29', '00:42:19', '', 'aasd', 'Cancelled', NULL, NULL, '0.00', '2025-09-29 00:42:19', '2500', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('133', 'TXN-20250928-3991', '19', NULL, '', '', 'Sale', '2025-09-29', '00:57:10', '', 'test', 'Cancelled', NULL, NULL, '0.00', '2025-09-29 00:57:10', '30000', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('134', 'TXN-20250928-2272', '19', NULL, '', '', 'Sale', '2025-09-29', '01:03:31', '', 's', 'Cancelled', NULL, NULL, '0.00', '2025-09-29 01:03:31', '2000', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('135', 'TXN-20250928-1536', '54', NULL, '', '', 'Sale', '2025-09-29', '01:03:40', '', 'sda', 'Cancelled', NULL, NULL, '0.00', '2025-09-29 01:03:40', '4000', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('136', 'TXN-20250928-6045', '54', NULL, '', '', 'Sale', '2025-09-29', '01:03:48', '', 'fgd', 'Cancelled', NULL, NULL, '0.00', '2025-09-29 01:03:48', '6000', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('137', 'TXN-20250928-5663', '19', NULL, '', '', 'Sale', '2025-09-29', '01:11:21', '', 'fdg', 'Cancelled', NULL, NULL, '0.00', '2025-09-29 01:11:21', '0', '10000', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('138', 'TXN-20250928-8379', '19', NULL, '', '', 'Sale', '2025-09-29', '01:11:29', '', 'd', 'Cancelled', NULL, NULL, '0.00', '2025-09-29 01:11:29', '100111', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('139', 'TXN-20250928-8385', '34', NULL, '', '', 'Sale', '2025-09-29', '01:11:55', '', 'zxc', 'Pending', NULL, NULL, '0.00', '2025-09-29 01:11:55', '9400', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('140', 'TXN-20250928-1676', '45', NULL, '', '', 'Sale', '2025-09-29', '01:12:08', '', 'jk', 'Cancelled', NULL, NULL, '0.00', '2025-09-29 01:12:08', '4555', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('142', 'TXN-20250928-2635', '46', NULL, '', '', 'Sale', '2025-09-29', '01:14:52', '', 'a', 'Cancelled', NULL, NULL, '0.00', '2025-09-29 01:14:52', '31000', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('143', 'TXN-20250928-47923', '54', '8', 'stephen viray', '', 'Sale', '2025-09-28', '20:56:41', '[{\"material\":\"Computer Parts\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"250.00\",\"total\":250}]', NULL, 'Completed', NULL, NULL, '250.00', '2025-09-29 02:56:41', '1', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('144', 'TXN-20250928-78540', '34', '8', 'mics trinidad', '', 'Sale', '2025-09-28', '20:57:12', '[{\"material\":\"E-Waste\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"120.00\",\"total\":120},{\"material\":\"Yero (Corrugated Sheets)\",\"quantity\":2,\"unit\":\"kg\",\"price_per_kg\":\"7.00\",\"total\":14}]', NULL, 'Completed', NULL, NULL, '134.00', '2025-09-29 02:57:12', '3', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('145', 'TXN-20250928-82611', '54', '8', 'stephen viray', '', 'Sale', '2025-09-28', '21:12:50', '[{\"material\":\"Iron Scrap\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"18.00\",\"total\":18}]', NULL, 'Completed', NULL, NULL, '18.00', '2025-09-29 03:12:50', '1', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('146', 'TXN-20250928-87982', '54', '8', 'stephen viray', '', 'Sale', '2025-09-28', '21:20:06', '[{\"material\":\"Stainless Steel\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"65.00\",\"total\":65}]', NULL, 'Completed', NULL, NULL, '65.00', '2025-09-29 03:20:06', '1', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('147', 'TXN-20250928-47616', NULL, '8', 'zdc', '', 'Sale', '2025-09-28', '21:21:23', '[{\"material\":\"E-Waste\",\"quantity\":2,\"unit\":\"kg\",\"price_per_kg\":\"120.00\",\"total\":240}]', NULL, 'Completed', NULL, NULL, '240.00', '2025-09-29 03:21:23', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('151', 'TXN-20250928-62369', '16', '8', 'Hanni Pham', 'Walk-in', 'Sale', '2025-09-28', '21:50:16', '[{\"material\":\"Stainless Steel\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"65.00\",\"total\":65}]', NULL, 'Completed', NULL, NULL, '65.00', '2025-09-29 03:50:16', '1', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('152', 'TXN-20250928-21363', '16', '8', 'Hanni Pham', 'Walk-in', 'Sale', '2025-09-28', '22:16:40', '[{\"material\":\"Stainless Steel\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"65.00\",\"total\":65}]', NULL, 'Completed', NULL, NULL, '65.00', '2025-09-29 04:16:40', '1', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('153', 'TXN-20250928-00065', '16', NULL, 'Pham', 'Pickup', 'Sale', '2025-09-28', '23:47:39', 'Scheduled Pickup: PET Bottles (1kg), Aluminum Cans (2kg)', 'Address: 054 gold extention\nSpecial Instructions: asdasd\nShipping Method: Side Cart (₱50.00)', 'Cancelled', 'asd', '2025-09-29 05:47:48', '209.00', '2025-09-29 05:47:39', '0', '0', '2025-09-29', '3:00 - 5:00 PM', NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('154', 'TXN-20251002-23429', '54', '8', 'stephen viray', 'Walk-in', 'Sale', '2025-10-02', '06:27:54', '[{\"material\":\"Iron Scrap\",\"quantity\":12,\"unit\":\"kg\",\"price_per_kg\":\"18.55\",\"total\":222.60000000000002}]', NULL, 'Completed', NULL, NULL, '222.60', '2025-10-02 12:27:54', '12', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('155', 'TXN-20251002-02499', '54', '8', 'stephen viray', 'Walk-in', 'Sale', '2025-10-02', '06:39:46', '[{\"material\":\"Iron Scrap\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"18.55\",\"total\":18.55}]', NULL, 'Completed', NULL, NULL, '18.55', '2025-10-02 12:39:46', '1', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('156', 'TXN-20251002-25075', '54', '8', 'stephen viray', 'Walk-in', 'Sale', '2025-10-02', '06:39:55', '[{\"material\":\"E-Waste\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"113.47\",\"total\":113.47}]', NULL, 'Completed', NULL, NULL, '113.47', '2025-10-02 12:39:55', '1', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('157', 'TXN-20251002-14718', '54', '8', 'stephen viray', 'Walk-in', 'Sale', '2025-10-02', '06:40:16', '[{\"material\":\"E-Waste\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"113.47\",\"total\":113.47}]', NULL, 'Completed', NULL, NULL, '113.47', '2025-10-02 12:40:16', '1', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('158', 'TXN-20251002-92553', '54', '8', 'stephen viray', 'Walk-in', 'Sale', '2025-10-02', '06:40:27', '[{\"material\":\"Computer Parts\",\"quantity\":21,\"unit\":\"kg\",\"price_per_kg\":\"250.00\",\"total\":5250}]', NULL, 'Completed', NULL, NULL, '5250.00', '2025-10-02 12:40:27', '21', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('159', 'TXN-20251002-94732', '54', '8', 'stephen viray', 'Walk-in', 'Purchase', '2025-10-02', '07:02:36', '[{\"material\":\"Yero (Corrugated Sheets)\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"7.00\",\"total\":7}]', NULL, 'Completed', NULL, NULL, '7.00', '2025-10-02 13:02:36', '1', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('160', 'TXN-20251002-63644', '54', '8', 'stephen viray', 'Walk-in', 'Sale', '2025-10-02', '07:09:17', '[{\"material\":\"Stainless Steel\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"65.75\",\"total\":65.75}]', NULL, 'Completed', NULL, NULL, '65.75', '2025-10-02 13:09:17', '1', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('161', 'TXN-20251002-26627', '54', '8', 'stephen viray', 'Walk-in', 'Purchase', '2025-10-02', '07:09:32', '[{\"material\":\"Stainless Steel\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"65.75\",\"total\":65.75}]', NULL, 'Completed', NULL, NULL, '65.75', '2025-10-02 13:09:32', '1', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('162', 'TXN-20251002-81631', '54', '8', 'stephen viray', 'Walk-in', 'Purchase', '2025-10-02', '07:09:46', '[{\"material\":\"Computer Parts\",\"quantity\":21,\"unit\":\"kg\",\"price_per_kg\":\"250.00\",\"total\":5250}]', NULL, 'Completed', NULL, NULL, '5250.00', '2025-10-02 13:09:46', '21', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('163', 'TXN-20251002-43088', '54', '8', 'stephen viray', 'Walk-in', 'Purchase', '2025-10-02', '07:10:06', '[{\"material\":\"Yero (Corrugated Sheets)\",\"quantity\":300,\"unit\":\"kg\",\"price_per_kg\":\"7.00\",\"total\":2100}]', NULL, 'Completed', NULL, NULL, '2100.00', '2025-10-02 13:10:06', '300', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('164', 'TXN-20251002-28402', '54', '8', 'stephen viray', 'Walk-in', 'Sale', '2025-10-02', '07:10:14', '[{\"material\":\"Computer Parts\",\"quantity\":12,\"unit\":\"kg\",\"price_per_kg\":\"250.00\",\"total\":3000}]', NULL, 'Completed', NULL, NULL, '3000.00', '2025-10-02 13:10:14', '12', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('165', 'TXN-20251005-93347', NULL, '8', 'asdasd', 'Walk-in', 'Purchase', '2025-10-05', '18:01:47', '[{\"material\":\"Stainless Steel\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"65.75\",\"total\":65.75}]', NULL, 'Completed', NULL, NULL, '65.75', '2025-10-06 00:01:47', '0', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('166', 'TXN-20251005-07358', '54', '8', 'stephen viray', 'Walk-in', 'Purchase', '2025-10-05', '18:02:07', '[{\"material\":\"Iron Scrap\",\"quantity\":1,\"unit\":\"kg\",\"price_per_kg\":\"18.55\",\"total\":18.55}]', NULL, 'Completed', NULL, NULL, '18.55', '2025-10-06 00:02:07', '1', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('167', 'TXN-20251005-0990', '16', NULL, '', '', 'Sale', '2025-10-06', '05:06:01', '', 'test', 'Pending', NULL, NULL, '0.00', '2025-10-06 05:06:01', '100000', '0', NULL, NULL, NULL, NULL);
INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `created_by`, `name`, `transaction_type`, `type`, `transaction_date`, `transaction_time`, `item_details`, `additional_info`, `status`, `cancel_reason`, `cancelled_at`, `amount`, `created_at`, `points_earned`, `points_redeemed`, `pickup_date`, `time_slot`, `shipping_method`, `shipping_fee`) VALUES ('168', 'TXN-20251006-9599', '16', NULL, '', '', 'Sale', '2025-10-06', '18:43:52', '', 'test', 'Pending', NULL, NULL, '0.00', '2025-10-06 18:43:52', '6000000', '0', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------
-- Table structure for table `two_factor_codes`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `two_factor_codes`;
CREATE TABLE `two_factor_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `code` varchar(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `used` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_employee_id` (`employee_id`),
  KEY `idx_code` (`code`),
  KEY `idx_expires_used` (`expires_at`,`used`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `user_type` enum('individual','business','collector') NOT NULL,
  `agreed_terms` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `loyalty_points` int(11) DEFAULT 0,
  `loyalty_tier` enum('bronze','silver','gold','platinum','diamond','ethereal') DEFAULT 'bronze',
  `referral_code` varchar(20) DEFAULT NULL,
  `referred_by` int(11) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `facebook_id` varchar(255) DEFAULT NULL,
  `api_key` varchar(64) DEFAULT NULL,
  `two_factor_auth` tinyint(1) DEFAULT 0,
  `two_factor_secret` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username_UNIQUE` (`username`),
  UNIQUE KEY `referral_code_UNIQUE` (`referral_code`),
  UNIQUE KEY `uniq_api_key` (`api_key`),
  KEY `idx_api_key` (`api_key`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `users`

INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('1', 'Rei', 'Naoi', NULL, 'stephenvissssray12@gmail.com', '0907 0204 324', '054 gold extention baranggay commonwelth qc', 'uploads/avatar_1_1753506615.png', '$2y$10$16X36OYWq3YeKVn8rnGXd.SMs/muSzGi5m0um55.awSuvggi9caEq', 'individual', '1', '2025-07-19 01:06:44', '0', 'bronze', NULL, NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('2', 'Rei', 'shibal', NULL, 'Reinaoi@gmail.com', '0998 4319 585', '054 GOLD EXTENTION, BARANGGAY COMMONWELTH QC', NULL, '$2y$10$fmeNy7U9xZ27WXXUW4vhHu4elNt9GiRxZSGrHlGoHcgipVFxjWb8W', 'collector', '1', '2025-07-19 01:08:29', '0', 'bronze', NULL, NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('3', 'Kim', 'Minji', NULL, 'minji12@gmail.com', '094946705513', 'wtf', 'uploads/avatar_3_1752913962.png', '$2y$10$iGGif.3X2yvilIqIRSt3NOBz85dKuJVhvmFh9SMU0TFSgrx0/kr7a', 'individual', '1', '2025-07-19 02:00:57', '0', 'bronze', NULL, NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('4', 'alliana', 'barrete', NULL, 'alliana@gmail.com', '0998 4319 585', '054 GOLD EXTENTION, BARANGGAY COMMONWELTH QC', 'uploads/avatar_4_1753506784.png', '$2y$10$B8JrRS.60EEa/kq3B//MpuuUR/H.Y5o/QZLRIfoKgie8U5HJI6eJq', 'individual', '1', '2025-07-19 02:10:51', '0', 'bronze', NULL, NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('5', 'zaldy', 'Solis jr', NULL, 'Solis@gmail.com', '0907 0204 324', '054 gold extention', NULL, '$2y$10$wE890acO1gi03dz1VkgcJ.57u7QdSSN60geLbPhXNYQUweiQksTAW', 'individual', '1', '2025-07-19 11:04:09', '0', 'bronze', NULL, NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('8', 'spykekyle', 'viray', NULL, 'Spyke@gmail.com', '09494670513', 'Gold st', 'uploads/avatar_8_1752924904.png', '$2y$10$z65rWP9xtOD4OdCZN0yIhOGLzt6jxqmDnW8j3PJkkrpgEoUEuu6ru', 'individual', '1', '2025-07-19 19:32:29', '0', 'bronze', NULL, NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('10', 'Yukki', '', 'Yukki2', 'Yukki@gmail.com', '0907 0204 324', '054 gold extention', 'uploads/avatar_10_1753601241.png', '$2y$10$jqjIqFsPHVw11JYIlgQpr.8jmoQnmkPfBmHwUgDJ4/wmeean.9I9m', 'business', '1', '2025-07-19 22:50:46', '0', 'bronze', NULL, NULL, '1', '2025-07-28 17:30:37', '0', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('11', 'zaki', 'viray', NULL, 'zaki@gmail.com', '0998 4319 585', '054 GOLD EXTENTION, BARANGGAY COMMONWELTH QC', NULL, '$2y$10$7g53Z9aUwDEmFF18lI8D5.ND0kvFKUCYPIjtlcebMNpp1gb7NTrh2', 'collector', '1', '2025-07-19 23:22:58', '0', 'bronze', NULL, NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('12', 'joseph', 'Jordan', NULL, 'Joseph@gmail.com', '0998 4319 585', 'caloocan st bagong silang', 'uploads/avatar_12_1753079568.png', '$2y$10$00Eh/mmZAgD8xIUDkA6doel3W21Qg3G0B7zDs.TJtI9M3zzMd4J/K', 'collector', '1', '2025-07-19 23:27:01', '0', 'bronze', NULL, NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('13', 'aye', 'rodrigez', NULL, 'ayee@gmail.com', '0907 0204 324', '054 gold extention', 'uploads/avatar_13_1753194864.png', '$2y$10$lsWx9bvxnzjo4qv1p8a2Yue/HFf5jsrfkhw9YfOST2RkBxTFsPqSy', 'individual', '1', '2025-07-22 22:24:02', '0', 'bronze', NULL, NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('14', 'norlika', 'linog', NULL, 'norlikalinog@gmail.com', '1234 5678 910', 'rerfr', NULL, '$2y$10$kcnk2nfuPtjIM7wDnSbGQ.J3k2uvY.kIsxBXYxeRc6AtTM.VphkqW', 'individual', '1', '2025-07-23 16:46:44', '0', 'bronze', NULL, NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('16', 'Hanni', 'Pham', 's2xwoo', 'hanni@gmail.com', '0907 0204 324', '054 gold extention', 'uploads/avatar_16_1753601293.png', '$2y$10$THKnHjcg./CuAtaYcJaSw.7OukCTyyVO5P5D2W7dnouod/x9Z2dOW', 'individual', '1', '2025-07-27 13:27:33', '6498516', 'ethereal', 'S2X8246', NULL, '0', NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('17', 'Haerin', 'Kang', 'KangHaerin1', 'haerin@gmail.com', '0998 4319 585', '054 GOLD EXTENTION, BARANGGAY COMMONWELTH QC', 'uploads/avatar_17_1753594250.png', '$2y$10$JWSf5PscFb2OcWmLLUZbOO7N5ZVCt7Nl1c0dRbXy70s6pOi.hTMPC', 'individual', '1', '2025-07-27 13:30:12', '0', 'bronze', 'KAN2778', NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('18', 'Suzy', 'Bae', 'suzy12', 'stephenvirayasdasd12@gmail.com', '0907 0204 324', '054 gold extention', 'uploads/avatar_18_1753600421.png', '$2y$10$DdAaGt4S1OPcxcB2my8Y5eaYnacks5WRi9lzsuKkerTkRv6nAP5Ra', 'individual', '1', '2025-07-27 15:13:13', '0', 'bronze', 'SUZ8424', NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('19', 'kim', 'jiwoon', 'jiwoon12', 'jiwwon@gmail.com', '0949 4670 513', 'jan lang sa tabi tabi', 'uploads/avatar_19_1753695841.png', '$2y$10$6OLADaFRSrkxqrUSGzta7ue7ijhd5hDoGr74z7qJxwlIgD213AigW', 'individual', '1', '2025-07-28 17:36:57', '112111', 'ethereal', 'ZEWVZC9', NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('20', 'ethyl', 'alcohol', 'etyhil', 'yupotin@gmail.com', '0907 0204 324', '054 gold extention', NULL, '$2y$10$1m/a9dRBLD4TRIU1YE0t1OeJgHr0M7E7Dz8/QTgv7PAwDjJObkJtu', 'individual', '1', '2025-07-28 17:37:51', '0', 'bronze', 'ROLSHC9', '19', '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('21', 'lambanog', 'delacruz', 'lambanog', 'asdasdasd@asdasd.com', '0907 0204 324', '054 gold extention', NULL, '$2y$10$NIcK.DwBAExl68u1oqQjC.xG/O2JbKnmJQrvAfto5cY6BhA2g898y', 'business', '1', '2025-07-28 18:36:55', '0', 'bronze', 'RVDLR9I', '19', '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('22', 'yupol', 'asdh', '132asdj', 'aslhdk@asldhjk.kjla', '0992 6445 117', '054 gold extention', NULL, '$2y$10$5JsSQGZWnapa.OgYEcfoNuThSa/v6L723czpaMs9tmzExmPkgZyES', 'collector', '1', '2025-07-28 18:51:26', '0', 'bronze', 'NB4566X', '19', '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('23', 'stephen', 'viray', 'EeyoreMPA', 'Yukkiasdasd@gmail.com', '0907 0204 324', '054 gold extention', NULL, '$2y$10$3ki8ZvVaYstyo61iwPTYv.3bWHmLQuC8.S1C1Zh2E.QeXaeb0xn2e', 'individual', '1', '2025-07-28 19:04:36', '0', 'bronze', 'DYJBSNT', '19', '0', NULL, '0', '4ee4b9e57bfc3e72c4a294d6d70029488982025e682d7abe11ed10dae50acaa0', '2025-07-29 13:04:36', NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('24', 'stephen', 'viray', 'EeyoreMPA12', 'Yukkiasdadaadsd@gmail.com', '0907 0204 324', '054 gold extention', NULL, '$2y$10$B1cnVrODo4rytTeUveh3B.r4r6N1UDaUi9j8NB0i81hBJdNU8Lnn2', 'individual', '1', '2025-07-28 19:04:56', '0', 'bronze', 'M2SEHAQ', '19', '0', NULL, '0', 'ddfcc1ae39e71371bb497b4be67e3bf004b80434d90d21bf2a649f505e2b39a0', '2025-07-29 13:04:56', NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('25', 'stephen', 'viray', 'EeyoreMPA1212', 'Yukkiasdadssaadsd@gmail.com', '0907 0204 324', '054 gold extention', NULL, '$2y$10$twMIJgh4uWtJEBjowvffr.O0SROEuUtaZ.NOev9cJ/sCSiVBCwqbK', 'individual', '1', '2025-07-28 19:07:36', '0', 'bronze', 'XFAZAUD', NULL, '0', NULL, '0', '1ff30724b5a33912cc9d71b84b3f22bf8f24ed8c5ba3256f1dbf74e5d11698e1', '2025-07-29 13:07:36', NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('31', 'solis', 'zaldy', 'solis12', 'solisb1738@gmail.com', '0907 0204 324', '054 gold extention', NULL, '$2y$10$RKfnWm2ELmprJxKTbJVWYOFPWSrw..k4skO7PQ5keSCnjy8XoBWCq', 'business', '1', '2025-07-28 19:48:26', '0', 'bronze', '0EQXS0C', NULL, '0', NULL, '0', '9d052142809f6b0bb4a8d5ccc19ac6e20bf59ffd8c8dd153dc2fbf45c341dca8', '2025-07-29 13:48:26', NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('32', 'tj', 'viray', 'tjviray12', 'johntrixx.delosreyes20@gmail.com', '0907 0204 324', '054 gold extention', NULL, '$2y$10$CkknAoNw..s8la7AdKjuiOcXDFmWdp1omgBi0phyaD5w8Bu8ZgE7u', 'collector', '1', '2025-07-28 19:50:58', '0', 'bronze', 'LZBPQSX', NULL, '0', NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('33', 'rich', 'viray', 'richvuray', 'richeldviray@gmail.com', '0907 0204 324', 'ajshdjkhasd', NULL, '$2y$10$uBSvhXG.AiXrPVMI6OeUtuNRE4PTICLPhJj.qwlEmlcxw9MeyEA6m', 'individual', '1', '2025-07-28 19:55:31', '0', 'bronze', '6YC71L7', NULL, '0', NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('35', 'andy', 'doza', 'andy12', 'dozaandy21@gmail.com', '0907 0204 324', '054 gold extention', NULL, '$2y$10$wkk061VQAGA3vhLlOJ7aM.sxtofbLPZS8Ae4cLz96PmNPnlfPye/a', 'business', '1', '2025-07-28 20:10:45', '0', 'bronze', 'O4KRUBD', NULL, '0', NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('36', 'marco', 'polo', 'marconigger', 'raiku.akiras@gmail.com', '0907 0204 324', '054 gold extention', NULL, '$2y$10$Ov0jN6.XkgY..CGfYnA9q.bMf3jrYusjjRHLag69kQcEzPWD3TRo2', 'business', '1', '2025-07-28 20:24:39', '0', 'bronze', 'AQ2N63O', NULL, '0', NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('37', 'marcus', 'pelaez', 'marcus', 'geremiedreyes@gmail.com', '0907 0204 324', '054 gold extention', NULL, '$2y$10$3/qhX9ZTQ7pu2H439UOgn.J5UQE4pFvh7wRtd4JIECV.ltX850.U2', 'individual', '1', '2025-07-28 20:27:48', '0', 'bronze', 'TIKIOVK', NULL, '0', NULL, '0', '6dee170737a29abc5b4db24d6fea155a3fc6570eb117bc1a0fb800f1a39a1aa5', '2025-07-29 14:27:48', NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('43', 'Yukki', '', 'Yukki', 'kahitbatapaako123@gmail.com', '0907 0204 324', '054 gold extention', 'uploads/avatar_43_1759671650.png', '$2y$10$6QZYrUZOilbxUZI.C6JS3OEbkdUebTiTNWlsB.gWTgr7CtJTDfeAW', 'business', '1', '2025-07-28 23:01:07', '0', 'bronze', 'YHTNMUJ', NULL, '1', '2025-10-06 18:58:25', '1', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('45', 'alliana', 'barrete', 'lunaaaa', 'yannabarrete@gmail.com', '0945 2452 147', 'grg', NULL, '$2y$10$Yyb/h/lQ4Y3eeMs/UaDH1OvYZxz86VyPM6XZzrfm6fqOIxK4jOR36', 'individual', '1', '2025-07-29 23:08:47', '4655', 'gold', 'TLJ01I4', NULL, '0', NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('46', 'joseph', 'jordan', 'goner', 'jj5587262@gmail.com', '0998 4319 585', '054 GOLD EXTENTION, BARANGGAY COMMONWELTH QC', NULL, '$2y$10$7DKaXvOydJmRvEHryOLW0.xyN16dDug2IR6MDw9lMM1BEOaHH8NAm', 'individual', '1', '2025-07-29 23:12:21', '31001', 'ethereal', 'FLW5QL9', '45', '0', NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('48', 'ghdjklsah', 'hjk', 'fgsdhjklh', 'erfevgfsdgrd@yahoo.com', '0154 6523 167', 'gdrfg', NULL, '$2y$10$PkNAEgrVlwmlTHd65qu5MOox49G8gZAwyAehA7nFkWZkjpiQGlBrG', 'business', '1', '2025-07-29 23:25:50', '0', 'bronze', 'LUOYNDD', NULL, '0', NULL, '0', 'f57f5b5acb11979801dd49aff50472f0fca8202d801ee2fbdb717b72944c0a04', '2025-07-30 17:25:50', NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('54', 'stephen', 'viray', 'rockstar', 'yenajigumina12@gmail.com', '09070204324', '054 gold extention', NULL, '$2y$10$w/Re03M1gC1x8nuH5F2P7eJjk1.8Sx2KECVBXm.4fjlN50Xf06hfG', 'individual', '1', '2025-08-08 17:06:33', '30079', 'ethereal', '5SZ5VTX', NULL, '0', NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('56', 'stephen', 'viray', 'versacexaeia111', 'ASHasjkhd@asjkdh.com', '09984319585', '054 gold extention\r\nbaranggay commonwelth qc', NULL, '$2y$10$xzS59qTfcjOmqbX1FP.Zg.LcFrGOpB629SspKS7GfXr9CitF7RTLu', 'business', '0', '2025-09-27 00:46:46', '0', 'bronze', 'STVI1080', NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('57', 'stephen', 'viray', 'ilovelhatinagirlssssss', 'stephenviray12@gmail.com', '09984319585', '054 gold extention', NULL, '$2y$10$Qgfx0.B8Cm3c7rR9bk090OGzYc0.ZG4zaFlgl7/CCacqdjfrEBcPO', 'business', '1', '2025-09-30 04:48:15', '0', 'bronze', 'AE579MT', NULL, '0', NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `address`, `profile_image`, `password_hash`, `user_type`, `agreed_terms`, `created_at`, `loyalty_points`, `loyalty_tier`, `referral_code`, `referred_by`, `is_admin`, `last_login`, `is_verified`, `verification_token`, `token_expires_at`, `reset_token`, `reset_token_expires`, `facebook_id`, `api_key`, `two_factor_auth`, `two_factor_secret`) VALUES ('58', 'Mariefe', 'Baturi', 'riri', 'mariefe022904@gmail.com', '09984319585', '054 gold extention', NULL, '$2y$10$piScSFcvk1VueStlsVwkZOT63ZuBnoGzitsapL3Z5XVYzXNxii4wy', 'collector', '1', '2025-10-02 12:12:16', '0', 'bronze', 'SKA10W2', NULL, '0', NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL);

-- --------------------------------------------------------
-- Table structure for table `vouchers`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `vouchers`;
CREATE TABLE `vouchers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `voucher_code` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tier` enum('silver','gold','platinum','diamond','ethereal') NOT NULL,
  `voucher_type` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `points_cost` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `redeemed_at` timestamp NULL DEFAULT NULL,
  `is_redeemed` tinyint(1) DEFAULT 0,
  `receipt_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `voucher_code` (`voucher_code`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `vouchers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `vouchers`

INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('1', 'SILVER001', '1', 'silver', 'Free Pickup', 'One free scrap pickup service', '50.00', '500', '2025-09-29 04:36:11', '2025-10-06 04:36:11', NULL, '0', NULL);
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('2', 'GOLD001', '1', 'gold', 'Cash Voucher', '₱100 cash voucher for scrap sales', '100.00', '800', '2025-09-29 04:36:11', '2025-10-06 04:36:11', NULL, '0', NULL);
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('3', 'PLATINUM001', '1', 'platinum', 'Premium Service', 'Priority processing and 15% bonus', '150.00', '1200', '2025-09-29 04:36:11', '2025-10-06 04:36:11', NULL, '0', NULL);
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('4', 'DIAMOND001', '1', 'diamond', 'VIP Package', 'VIP treatment with 20% bonus and free pickup', '250.00', '2000', '2025-09-29 04:36:11', '2025-10-06 04:36:11', NULL, '0', NULL);
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('5', 'ETHEREAL001', '1', 'ethereal', 'Ultimate Reward', 'Ultimate package with 25% bonus and premium perks', '500.00', '3500', '2025-09-29 04:36:11', '2025-10-06 04:36:11', NULL, '0', NULL);
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('6', 'SILVER202509287380', '16', 'silver', 'Free Pickup', 'One free scrap pickup service', '50.00', '500', '2025-09-29 04:40:26', '2025-10-05 22:40:26', NULL, '0', 'receipts/vouchers/SILVER202509287380.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('7', 'GOLD202509289792', '16', 'gold', 'Cash Voucher', '₱100 cash voucher for scrap sales', '100.00', '800', '2025-09-29 04:40:40', '2025-10-05 22:40:40', NULL, '0', 'receipts/vouchers/GOLD202509289792.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('8', 'SILVER202509287277', '16', 'silver', 'Free Pickup', 'One free scrap pickup service', '50.00', '500', '2025-09-29 04:40:57', '2025-10-05 22:40:57', '2025-09-29 04:43:15', '1', 'receipts/vouchers/SILVER202509287277.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('9', 'SILVER202509283758', '16', 'silver', 'Free Pickup', 'One free scrap pickup service', '50.00', '500', '2025-09-29 04:41:06', '2025-10-05 22:41:06', '2025-09-29 04:42:59', '1', 'receipts/vouchers/SILVER202509283758.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('10', 'GOLD202509285046', '16', 'gold', 'Cash Voucher', '₱100 cash voucher for scrap sales', '100.00', '800', '2025-09-29 04:47:57', '2025-10-05 22:47:57', NULL, '0', 'receipts/vouchers/GOLD202509285046.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('11', 'GOLD202509282538', '16', 'gold', 'Cash Voucher', '₱100 cash voucher for scrap sales', '100.00', '800', '2025-09-29 04:48:26', '2025-10-05 22:48:26', NULL, '0', 'receipts/vouchers/GOLD202509282538.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('12', 'GOLD202509282326', '16', 'gold', 'Cash Voucher', '₱100 cash voucher for scrap sales', '100.00', '800', '2025-09-29 04:49:04', '2025-10-05 22:49:04', NULL, '0', 'receipts/vouchers/GOLD202509282326.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('13', 'GOLD202509288239', '16', 'gold', 'Cash Voucher', '₱100 cash voucher for scrap sales', '100.00', '800', '2025-09-29 04:49:29', '2025-10-05 22:49:29', NULL, '0', 'receipts/vouchers/GOLD202509288239.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('14', 'GOLD202509284852', '16', 'gold', 'Cash Voucher', '₱100 cash voucher for scrap sales', '100.00', '800', '2025-09-29 04:49:31', '2025-10-05 22:49:31', NULL, '0', 'receipts/vouchers/GOLD202509284852.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('15', 'GOLD202509287984', '16', 'gold', 'Cash Voucher', '₱100 cash voucher for scrap sales', '100.00', '800', '2025-09-29 04:49:33', '2025-10-05 22:49:33', NULL, '0', 'receipts/vouchers/GOLD202509287984.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('16', 'GOLD202509282142', '16', 'gold', 'Cash Voucher', '₱100 cash voucher for scrap sales', '100.00', '800', '2025-09-29 04:49:34', '2025-10-05 22:49:34', NULL, '0', 'receipts/vouchers/GOLD202509282142.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('17', 'GOLD202509282942', '16', 'gold', 'Cash Voucher', '₱100 cash voucher for scrap sales', '100.00', '800', '2025-09-29 04:49:34', '2025-10-05 22:49:34', NULL, '0', 'receipts/vouchers/GOLD202509282942.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('18', 'GOLD202509281852', '16', 'gold', 'Cash Voucher', '₱100 cash voucher for scrap sales', '100.00', '800', '2025-09-29 04:49:34', '2025-10-05 22:49:34', NULL, '0', 'receipts/vouchers/GOLD202509281852.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('19', 'GOLD202509288300', '16', 'gold', 'Cash Voucher', '₱100 cash voucher for scrap sales', '100.00', '800', '2025-09-29 04:49:34', '2025-10-05 22:49:34', NULL, '0', 'receipts/vouchers/GOLD202509288300.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('20', 'GOLD202509285063', '16', 'gold', 'Cash Voucher', '₱100 cash voucher for scrap sales', '100.00', '800', '2025-09-29 04:49:34', '2025-10-05 22:49:34', NULL, '0', 'receipts/vouchers/GOLD202509285063.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('21', 'GOLD202509281598', '16', 'gold', 'Cash Voucher', '₱100 cash voucher for scrap sales', '100.00', '800', '2025-09-29 04:49:35', '2025-10-05 22:49:35', NULL, '0', 'receipts/vouchers/GOLD202509281598.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('22', 'GOLD202509282756', '16', 'gold', 'Cash Voucher', '₱100 cash voucher for scrap sales', '100.00', '800', '2025-09-29 04:49:35', '2025-10-05 22:49:35', NULL, '0', 'receipts/vouchers/GOLD202509282756.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('23', 'GOLD202509288690', '16', 'gold', 'Cash Voucher', '₱100 cash voucher for scrap sales', '100.00', '800', '2025-09-29 04:49:35', '2025-10-05 22:49:35', NULL, '0', 'receipts/vouchers/GOLD202509288690.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('24', 'GOLD202509283397', '16', 'gold', 'Cash Voucher', '₱100 cash voucher for scrap sales', '100.00', '800', '2025-09-29 04:49:35', '2025-10-05 22:49:35', NULL, '0', 'receipts/vouchers/GOLD202509283397.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('25', 'GOLD202509289982', '16', 'gold', 'Cash Voucher', '₱100 cash voucher for scrap sales', '100.00', '800', '2025-09-29 04:49:35', '2025-10-05 22:49:35', NULL, '0', 'receipts/vouchers/GOLD202509289982.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('26', 'SILVER202509284214', '16', 'silver', 'Free Pickup', 'One free scrap pickup service', '50.00', '500', '2025-09-29 05:07:04', '2025-10-05 23:07:04', NULL, '0', 'receipts/vouchers/SILVER202509284214.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('27', 'SILVER202509284305', '16', 'silver', 'Free Pickup', 'One free scrap pickup service', '50.00', '500', '2025-09-29 05:10:08', '2025-10-05 23:10:08', NULL, '0', 'receipts/vouchers/SILVER202509284305.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('28', 'SILVER202509284389', '16', 'silver', 'Free Pickup', 'One free scrap pickup service', '50.00', '500', '2025-09-29 05:10:28', '2025-10-05 23:10:28', NULL, '0', 'receipts/vouchers/SILVER202509284389.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('29', 'SILVER202509289808', '16', 'silver', 'Free Pickup', 'One free scrap pickup service', '50.00', '500', '2025-09-29 05:16:10', '2025-10-05 23:16:10', NULL, '0', 'receipts/vouchers/SILVER202509289808.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('30', 'SILVER202509281866', '16', 'silver', 'Free Pickup', 'One free scrap pickup service', '50.00', '500', '2025-09-29 05:16:13', '2025-10-05 23:16:13', NULL, '0', 'receipts/vouchers/SILVER202509281866.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('31', 'SILVER202509288802', '16', 'silver', 'Free Pickup', 'One free scrap pickup service', '50.00', '500', '2025-09-29 05:31:52', '2025-10-05 23:31:52', NULL, '0', 'receipts/vouchers/SILVER202509288802.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('32', 'SILVER202509287265', '16', 'silver', 'Free Pickup', 'One free scrap pickup service', '50.00', '500', '2025-09-29 05:32:18', '2025-10-05 23:32:18', NULL, '0', 'receipts/vouchers/SILVER202509287265.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('33', 'SILVER202509283461', '16', 'silver', 'Free Pickup', 'One free scrap pickup service', '50.00', '500', '2025-09-29 05:33:17', '2025-10-05 23:33:17', NULL, '0', 'receipts/vouchers/SILVER202509283461.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('34', 'SILVER202509293568', '16', 'silver', 'Free Pickup', 'One free scrap pickup service', '50.00', '500', '2025-09-29 06:18:07', '2025-10-06 00:18:07', NULL, '0', 'receipts/vouchers/SILVER202509293568.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('35', 'SILVER202509297205', '16', 'silver', 'Free Pickup', 'One free scrap pickup service', '50.00', '500', '2025-09-29 06:45:22', '2025-10-06 00:45:22', NULL, '0', 'receipts/vouchers/SILVER202509297205.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('36', 'SILVER202509297110', '16', 'silver', 'Free Pickup', 'One free scrap pickup service', '50.00', '500', '2025-09-30 01:48:26', '2025-10-06 19:48:26', NULL, '0', 'receipts/vouchers/SILVER202509297110.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('37', 'ETHEREAL202509295672', '16', 'ethereal', 'Ultimate Reward', 'Ultimate package with 25% bonus and premium perks', '500.00', '3500', '2025-09-30 01:50:32', '2025-10-06 19:50:32', NULL, '0', 'receipts/vouchers/ETHEREAL202509295672.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('38', 'PLATINUM202509306186', '16', 'platinum', 'Premium Service', 'Priority processing and 15% bonus', '150.00', '1200', '2025-09-30 18:43:35', '2025-10-07 12:43:35', NULL, '0', 'receipts/vouchers/PLATINUM202509306186.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('39', 'SILVER202510052125', '16', 'silver', 'Free Pickup', 'One free scrap pickup service', '50.00', '500', '2025-10-06 05:11:53', '2025-10-12 23:11:53', NULL, '0', 'receipts/vouchers/SILVER202510052125.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('40', 'GOLD202510051241', '16', 'gold', 'Cash Voucher', '₱100 cash voucher for scrap sales', '100.00', '800', '2025-10-06 05:12:20', '2025-10-12 23:12:20', NULL, '0', 'receipts/vouchers/GOLD202510051241.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('41', 'PLATINUM202510057436', '16', 'platinum', 'Premium Service', 'Priority processing and 15% bonus', '150.00', '1200', '2025-10-06 05:12:44', '2025-10-12 23:12:44', NULL, '0', 'receipts/vouchers/PLATINUM202510057436.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('42', 'DIAMOND202510054095', '16', 'diamond', 'VIP Package', 'VIP treatment with 20% bonus and free pickup', '250.00', '2000', '2025-10-06 05:12:56', '2025-10-12 23:12:56', NULL, '0', 'receipts/vouchers/DIAMOND202510054095.pdf');
INSERT INTO `vouchers` (`id`, `voucher_code`, `user_id`, `tier`, `voucher_type`, `description`, `value`, `points_cost`, `created_at`, `expires_at`, `redeemed_at`, `is_redeemed`, `receipt_path`) VALUES ('43', 'ETHEREAL202510059365', '16', 'ethereal', 'Ultimate Reward', 'Ultimate package with 25% bonus and premium perks', '500.00', '3500', '2025-10-06 05:13:09', '2025-10-12 23:13:09', NULL, '0', 'receipts/vouchers/ETHEREAL202510059365.pdf');

-- --------------------------------------------------------
-- Table structure for table `work_logs`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `work_logs`;
CREATE TABLE `work_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) DEFAULT NULL,
  `action_description` text DEFAULT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `related_item_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_worklog_emp` (`employee_id`),
  CONSTRAINT `fk_worklog_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
