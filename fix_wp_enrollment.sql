-- Fix WordPress Starter Kit Funnel Enrollment
-- Run in phpMyAdmin for database joalacom_joala

-- 1. Mark all active leads with sequences as enrolled (if not already)
UPDATE leads l
SET l.enrolled_at = NOW()
WHERE l.sequence_id IS NOT NULL 
AND l.enrolled_at IS NULL;

-- 2. Add email queue entries for enrolled leads who don't have them
INSERT INTO email_queue (lead_id, sequence_step_id, scheduled_send_time, status, created_at, updated_at)
SELECT l.id, ss.id, DATE_ADD(NOW(), INTERVAL ss.delay_days DAY), 'pending', NOW(), NOW()
FROM leads l
JOIN sequence_steps ss ON ss.sequence_id = l.sequence_id
LEFT JOIN email_queue eq ON eq.lead_id = l.id AND eq.sequence_step_id = ss.id
WHERE l.enrolled_at IS NOT NULL AND eq.id IS NULL;

-- 3. Verify the fix worked for lead 12
SELECT l.id, l.email, l.sequence_id, l.enrolled_at, 
(SELECT COUNT(*) FROM email_queue eq WHERE eq.lead_id = l.id) as emails_in_queue
FROM leads l
WHERE l.id = 12;

-- 4. Show all leads with their queue status
SELECT l.id, l.email, l.sequence_id, l.enrolled_at, 
COUNT(eq.id) as queue_count,
SUM(CASE WHEN eq.status = 'sent' THEN 1 ELSE 0 END) as sent_count
FROM leads l
LEFT JOIN email_queue eq ON eq.lead_id = l.id
WHERE l.sequence_id IS NOT NULL
GROUP BY l.id
ORDER BY l.id DESC LIMIT 10;