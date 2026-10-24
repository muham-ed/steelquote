-- SteelQuote B2B2C Multi-Tenant Database Dump

CREATE DATABASE IF NOT EXISTS `steelquote` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `steelquote`;

DROP TABLE IF EXISTS `project_leads`;
DROP TABLE IF EXISTS `quotation_items`;
DROP TABLE IF EXISTS `quotations`;
DROP TABLE IF EXISTS `materials`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `users`;

-- --------------------------------------------------------

-- Table structure for `users` (الورش والحسابات)
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `workshop_name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `logo` VARCHAR(255) DEFAULT NULL,
  `commercial_reg` VARCHAR(100) DEFAULT NULL,
  `subscription_status` ENUM('trial', 'active', 'expired', 'suspended') DEFAULT 'trial',
  `trial_ends_at` DATE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Super Admin user
INSERT INTO `users` (`id`, `role`, `name`, `email`, `password`, `workshop_name`, `phone`, `address`, `subscription_status`, `trial_ends_at`) VALUES
(1, 'admin', 'المهندس محمد (مالك المنصة)', 'admin@steelquote.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'إدارة منصة SteelQuote', '01060828627', 'القاهرة - المقر الرئيسي', 'active', '2030-12-31');

-- --------------------------------------------------------

-- Table structure for `project_leads` (طلبات تسعير العملاء B2B2C)
CREATE TABLE `project_leads` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `client_name` VARCHAR(255) NOT NULL,
  `client_phone` VARCHAR(50) NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `project_type` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `assigned_workshop_id` INT DEFAULT NULL,
  `status` ENUM('new', 'assigned', 'completed') NOT NULL DEFAULT 'new',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_lead_workshop` FOREIGN KEY (`assigned_workshop_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `project_leads` (`id`, `client_name`, `client_phone`, `city`, `project_type`, `description`, `status`, `created_at`) VALUES
(1, 'م. خالد عبد الرحمن', '01099887766', 'القاهرة - التجمع', 'بوابة حديد فورفورجيه', 'مطلوب تصنيع بوابة لمدخل فيلا مقاس 4*3 متر دهان فرني مع تركيب موتور تلقائي.', 'new', '2025-02-15 10:00:00'),
(2, 'حسام حسن', '01211223344', 'الجيزة - 6 أكتوبر', 'مظلة سيارات ساندوتش بانل', 'مطلوب مظلة تتسع لسيارتين مقاس 6*5 متر هيكل حديد مع صاج معزول.', 'new', '2025-02-16 12:30:00');

-- --------------------------------------------------------

-- Table structure for `customers`
CREATE TABLE `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL DEFAULT 1,
  `name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_customer_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `customers` (`id`, `user_id`, `name`, `phone`, `email`, `address`, `created_at`) VALUES
(1, 1, 'شركة البناء والتشييد الحديثة', '01012345678', 'info@builder-co.com', 'القاهرة - مدينة نصر - الشارع الرئيسي', '2025-01-10 10:00:00'),
(2, 1, 'المهندس أحمد سليمان', '01122334455', 'ahmed.soliman@gmail.com', 'الجيزة - الدقي', '2025-01-15 11:30:00');

-- --------------------------------------------------------

-- Table structure for `materials`
CREATE TABLE `materials` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL DEFAULT 1,
  `name` VARCHAR(255) NOT NULL,
  `unit` VARCHAR(50) NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_material_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `materials` (`id`, `user_id`, `name`, `unit`, `unit_price`, `notes`, `created_at`) VALUES
(1, 1, 'علب حديد 4×4 سم (سُمك 2 مم)', 'متر طولي', 85.00, 'قطاعات مربعة للمظلات والهياكل', '2025-01-01 10:00:00'),
(2, 1, 'علب حديد 8×4 سم (سُمك 2.5 مم)', 'متر طولي', 140.00, 'للأعمدة والإطارات الرئيسية', '2025-01-01 10:00:00');

-- --------------------------------------------------------

-- Table structure for `quotations`
CREATE TABLE `quotations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL DEFAULT 1,
  `quote_number` VARCHAR(50) NOT NULL,
  `customer_id` INT NOT NULL,
  `project_name` VARCHAR(255) NOT NULL,
  `labor_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `overhead_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `materials_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `profit_margin_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `profit_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `selling_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `deposit_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('draft', 'sent', 'accepted', 'rejected') NOT NULL DEFAULT 'draft',
  `valid_until` DATE DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_quotation_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_quotation_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `quotations` (`id`, `user_id`, `quote_number`, `customer_id`, `project_name`, `labor_cost`, `overhead_cost`, `materials_cost`, `total_cost`, `profit_margin_percent`, `profit_amount`, `selling_price`, `deposit_amount`, `status`, `valid_until`, `notes`, `created_at`) VALUES
(1, 1, 'SQ-2025-0001', 1, 'تصنيع وتركيب جمالون معدني للمخزن الرئيسي', 15000.00, 3500.00, 54200.00, 72700.00, 20.00, 18175.00, 90875.00, 45000.00, 'accepted', '2025-03-01', 'يشمل السعر التوريد والتركيب والدهانات والضمان لمدة عامين ضد عيوب التصنيع.', '2025-01-12 11:00:00');

-- --------------------------------------------------------

-- Table structure for `quotation_items`
CREATE TABLE `quotation_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `quotation_id` INT NOT NULL,
  `material_id` INT DEFAULT NULL,
  `item_description` VARCHAR(255) NOT NULL,
  `unit` VARCHAR(50) NOT NULL,
  `quantity` DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  CONSTRAINT `fk_item_quotation` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_item_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `quotation_items` (`id`, `quotation_id`, `material_id`, `item_description`, `unit`, `quantity`, `unit_price`, `total_price`) VALUES
(1, 1, 1, 'علب حديد 4×4 سم لإطار الجمالون', 'متر طولي', 60.00, 85.00, 5100.00);
