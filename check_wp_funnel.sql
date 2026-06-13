-- Check WordPress Starter Kit Funnel Status
-- Run in phpMyAdmin for joalacom_joala

-- 1. Find the funnel
SELECT id, name, is_active, description FROM funnels WHERE name LIKE '%WordPress%' OR name LIKE '%wordpress%';

-- 2. Get funnel stages
SELECT fs.id, fs.funnel_id, fs.name, fs.type, fs.order, fs.content, f.name as funnel_name
FROM funnel_stages fs
LEFT JOIN funnels f ON fs.funnel_id = f.id
WHERE f.name LIKE '%WordPress%'
ORDER BY fs.order;

-- 3. Check landing page
SELECT id, title, slug, is_active, sequence_id, funnel_id 
FROM landing_pages 
WHERE slug = 'free-wordpress-starter-kit';

-- 4. Check email sequences
SELECT id, name, is_active FROM email_sequences WHERE name LIKE '%WordPress%';

-- 5. Get sequence steps
SELECT ss.id, ss.sequence_id, ss.subject, ss.step_order, ss.delay_days, es.name as sequence_name
FROM sequence_steps ss
LEFT JOIN email_sequences es ON ss.sequence_id = es.id
WHERE es.name LIKE '%WordPress%'
ORDER BY ss.step_order;