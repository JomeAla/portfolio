-- Delete duplicate WordPress funnels (keep ID 2)
DELETE FROM funnel_stages WHERE funnel_id IN (3, 4, 21);
DELETE FROM funnels WHERE id IN (3, 4, 21);

-- Add stages for funnel 2 if they don't exist
INSERT INTO funnel_stages (funnel_id, name, type, `order`, content, created_at, updated_at)
SELECT 2, 'Landing Page', 'landing_page', 1, '{"page_slug":"free-wordpress-starter-kit"}', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM funnel_stages WHERE funnel_id = 2 AND `order` = 1);

INSERT INTO funnel_stages (funnel_id, name, type, `order`, content, created_at, updated_at)
SELECT 2, 'Download Page', 'content', 2, '{"url":"/downloads/wordpress-starter-kit.pdf"}', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM funnel_stages WHERE funnel_id = 2 AND `order` = 2);

INSERT INTO funnel_stages (funnel_id, name, type, `order`, content, created_at, updated_at)
SELECT 2, 'Thank You', 'thankyou', 3, '{"message":"Thanks for downloading!"}', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM funnel_stages WHERE funnel_id = 2 AND `order` = 3);