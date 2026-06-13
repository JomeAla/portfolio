-- Fix submitLead enrollment bug and enroll existing leads
-- Run in phpMyAdmin for joalacom_joala

-- 1. Fix column name in sequence code (if not done)
-- The code uses 'scheduled_at' but table has 'scheduled_send_time'

-- 2. Enroll ALL existing active leads in their sequences
UPDATE leads l
JOIN landing_pages lp ON l.landing_page_id = lp.id
SET l.enrolled_at = NOW()
WHERE l.sequence_id IS NOT NULL 
AND l.enrolled_at IS NULL;

-- 3. Add any missing email_queue entries for enrolled leads
INSERT INTO email_queue (lead_id, sequence_step_id, scheduled_send_time, status, created_at, updated_at)
SELECT l.id, ss.id, DATE_ADD(NOW(), INTERVAL ss.delay_days DAY), 'pending', NOW(), NOW()
FROM leads l
JOIN sequence_steps ss ON ss.sequence_id = l.sequence_id
WHERE l.enrolled_at IS NOT NULL
AND l.id NOT IN (SELECT lead_id FROM email_queue WHERE sequence_step_id = ss.id);

-- Verify leads now enrolled
SELECT l.id, l.email, l.sequence_id, l.enrolled_at, 
(SELECT COUNT(*) FROM email_queue eq WHERE eq.lead_id = l.id) as emails_queued
FROM leads l
WHERE l.id >= 10
ORDER BY l.id DESC LIMIT 10;