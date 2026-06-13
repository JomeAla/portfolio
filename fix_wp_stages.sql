-- Fix WordPress Starter Kit Funnel Stages
-- Run in phpMyAdmin for joalacom_joala

-- 1. DELETE duplicate/old stages (keep only correct ones)
DELETE FROM funnel_stages WHERE funnel_id = 2;
-- Starting fresh - will insert correct stages below

-- 2. Insert correct stages for WP Starter Kit funnel
INSERT INTO funnel_stages (funnel_id, name, type, `order`, content, created_at, updated_at) VALUES
(2, 'Landing Page', 'landing_page', 1, '{"page_slug":"free-wordpress-starter-kit"}', NOW(), NOW()),
(2, 'Download Page', 'download', 2, '{"file":"wordpress-starter-kit.zip"}', NOW(), NOW()),
(2, 'Thank You', 'thankyou', 3, '{"message":"Check your email for the download link!","button_text":"Download Again"}', NOW(), NOW());

-- 3. Update funnel to use correct sequence (21) instead of 22
UPDATE funnels SET welcome_sequence_id = 21 WHERE id = 2;

-- 4. Verify the stages
SELECT fs.id, fs.name, fs.type, fs.order, fs.content, f.name as funnel_name, f.welcome_sequence_id
FROM funnel_stages fs
JOIN funnels f ON fs.funnel_id = f.id
WHERE f.id = 2
ORDER BY fs.order;