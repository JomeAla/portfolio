-- Fix: Enroll lead 12 in WordPress sequence manually
-- Run in phpMyAdmin

-- 1. Update lead enrolled_at
UPDATE leads SET enrolled_at = NOW() WHERE id = 12;

-- 2. Add emails to queue for sequence steps
INSERT INTO email_queue (lead_id, sequence_step_id, scheduled_send_time, status, created_at, updated_at)
SELECT 12, ss.id, NOW(), 'pending', NOW(), NOW()
FROM sequence_steps ss
WHERE ss.sequence_id = 21 AND ss.step_order = 1;

-- Verify
SELECT l.id, l.email, l.sequence_id, l.enrolled_at, eq.id as queue_id, eq.status, eq.scheduled_send_time
FROM leads l
LEFT JOIN email_queue eq ON eq.lead_id = l.id
WHERE l.id = 12;