-- SteelQuote Multi-Tenant & Super-Admin Database Dump

CREATE DATABASE IF NOT EXISTS `steelquote` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `steelquote`;

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

-- Insert Super Admin user (Password: 123456)
INSERT INTO `users` (`id`, `role`, `name`, `email`, `password`, `workshop_name`, `phone`, `address`, `subscription_status`, `trial_ends_at`) VALUES
(1, 'admin', 'المهندس محمد (مالك المنصة)', 'admin@steelquote.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'إدارة منصة SteelQuote', '01060828627', 'القاهرة - المقر الرئيسي', 'active', '2030-12-31');

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
(2, 1, 'المهندس أحمد سليمان', '01122334455', 'ahmed.soliman@gmail.com', 'الجيزة - الدقي', '2025-01-15 11:30:00'),
(3, 1, 'مصنع الشروق للأغذية', '01234567890', 'contact@shorouk-food.com', 'العاشر من رمضان - المنطقة الصناعية', '2025-02-01 09:15:00'),
(4, 1, 'فيلا الكابتن محمود', '01500112233', 'm.mahmoud@hotmail.com', 'التجمع الخامس - القاهرة الجديدة', '2025-02-10 14:20:00');

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
(2, 1, 'علب حديد 8×4 سم (سُمك 2.5 مم)', 'متر طولي', 140.00, 'للأعمدة والإطارات الرئيسية', '2025-01-01 10:00:00'),
(3, 1, 'صاج معالج معزول (ساندوتش بانل 5سم)', 'متر مربع', 450.00, 'لتغطية الأسقف والجمالونات', '2025-01-01 10:00:00'),
(4, 1, 'زوايا حديد 5×5 سم (سُمك 4 مم)', 'متر طولي', 95.00, 'للتدعيم والتثبيت', '2025-01-01 10:00:00'),
(5, 1, 'صاج أسود معرج (سُمك 1.5 مم)', 'متر مربع', 220.00, 'لتغطية الأبواب والأسوار', '2025-01-01 10:00:00'),
(6, 1, 'حديد فورفورجيه مشكل (مزخرف)', 'كجم', 65.00, 'للبوابات والشبابيك الديكورية', '2025-01-01 10:00:00'),
(7, 1, 'كمر حديد IPE 160', 'متر طولي', 520.00, 'جمالونات وهياكل ثقيلة', '2025-01-01 10:00:00'),
(8, 1, 'دهان بريمر مانع للصدأ (أحمر/رمادي)', 'جالون', 350.00, 'دهان تأسيس لحماية الحديد', '2025-01-01 10:00:00'),
(9, 1, 'دهان دوكو ناري خارجي', 'جالون', 550.00, 'تشطيب نهائي جودة عالية', '2025-01-01 10:00:00'),
(10, 1, 'سلك لحام 6013 (عبوة 5 كجم)', 'علبة', 280.00, 'مستهلكات لحام', '2025-01-01 10:00:00');

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
(1, 1, 'SQ-2025-0001', 1, 'تصنيع وتركيب جمالون معدني للمخزن الرئيسي', 15000.00, 3500.00, 54200.00, 72700.00, 20.00, 18175.00, 90875.00, 45000.00, 'accepted', '2025-03-01', 'يشمل السعر التوريد والتركيب والدهانات والضمان لمدة عامين ضد عيوب التصنيع.', '2025-01-12 11:00:00'),
(2, 1, 'SQ-2025-0002', 2, 'تصنيع بوابة حديد فورفورجيه لمدخل الفيلا', 4500.00, 800.00, 11850.00, 17150.00, 25.00, 5716.67, 22866.67, 10000.00, 'sent', '2025-03-15', 'الدهان دوكو ناري طلاء حراري فرني + تركيب موتور فتح التلقائي غير شامل الموتور.', '2025-02-05 14:00:00'),
(3, 1, 'SQ-2025-0003', 4, 'مظلة سيارات صاج ساندوتش بانل 6×4 متر', 3000.00, 500.00, 16100.00, 19600.00, 18.00, 4302.44, 23902.44, 5000.00, 'draft', '2025-03-20', 'العرض شامل القواعد الخرسانية والتثبيت بالفيشر الصلب.', '2025-02-12 09:30:00');

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
(1, 1, 7, 'كمر حديد IPE 160 للجسور الرئيسية', 'متر طولي', 60.00, 520.00, 31200.00),
(2, 1, 3, 'صاج ساندوتش بانل 5سم للأسقف', 'متر مربع', 40.00, 450.00, 18000.00),
(3, 1, 4, 'زوايا حديد 5×5 سم للتربيط', 'متر طولي', 40.00, 95.00, 3800.00),
(4, 1, 8, 'دهان بريمر ضد الصدأ', 'جالون', 2.00, 350.00, 700.00),
(5, 1, 10, 'سلك لحام 6013', 'علبة', 1.00, 500.00, 500.00),
(6, 2, 6, 'حديد فورفورجيه مشكل للزخارف', 'كجم', 120.00, 65.00, 7800.00),
(7, 2, 2, 'علب حديد 8×4 سم لإطار البوابة', 'متر طولي', 20.00, 140.00, 2800.00),
(8, 2, 9, 'دهان دوكو ناري أسود مع تطعيم ذهبي', 'جالون', 2.00, 550.00, 1100.00),
(9, 2, NULL, 'اكسسوارات ومفصلات ثقيلة وركائز', 'طقم', 1.00, 1500.00, 1500.00),
(10, 3, 2, 'علب حديد 8×4 سم للأعمدة والمظلة', 'متر طولي', 30.00, 140.00, 4200.00),
(11, 3, 3, 'صاج معالج معزول (ساندوتش بانل)', 'متر مربع', 24.00, 450.00, 10800.00),
(12, 3, 8, 'دهان بريمر تأسيس', 'جالون', 2.00, 350.00, 700.00),
(13, 3, 10, 'مستهلكات تثبيت ولحام وفيشر', 'مجموعة', 1.00, 400.00, 400.00);
