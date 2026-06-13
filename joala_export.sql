-- Database Export
-- Date: 2026-04-21 14:25:01

SET FOREIGN_KEY_CHECKS=0;

-- Table: products (1 rows)
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `images` json DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'template',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`id`, `title`, `slug`, `description`, `short_description`, `price`, `sale_price`, `file_path`, `image`, `images`, `type`, `is_active`, `is_featured`, `order`, `created_at`, `updated_at`) VALUES ('1', 'Email Sequence Templates Pack', 'email-sequence-templates-pack', '# Email Sequence Templates Pack

Stop writing emails from scratch. This comprehensive pack gives you 6 complete email sequences with 24 tested, high-converting templates.

## What\'s Inside:

**6 Email Sequences:**
1. Welcome Series (5 emails) - Build relationships from day one
2. Abandoned Cart (3 emails) - Recover lost sales
3. Re-engagement (4 emails) - Win back inactive subscribers
4. Webinar Follow-up (5 emails) - Convert webinar attendees to customers
5. Product Launch (4 emails) - Launch new products with maximum impact
6. Thank You & Upsell (3 emails) - Maximize customer lifetime value

## Features:
- Copy & paste ready templates
- Easy customization with [placeholders]
- Industry best practices embedded
- Pro tips for maximum results
- Tested subject lines included', '6 ready-to-use email sequences with 24 tested templates for maximum conversions', '15000.00', '12000.00', 'uploads/products/files/email-sequence-templates-pack.html', 'uploads/products/email-templates-cover.svg', NULL, 'ebook', '1', '1', '1', '2026-04-21 15:10:42', '2026-04-21 15:10:42');

SET FOREIGN_KEY_CHECKS=1;
