-- Fix WordPress Starter Kit Landing Page
-- Run this in phpMyAdmin for database joalacom_joala

UPDATE landing_pages 
SET 
    title = 'WordPress Starter Kit - Free Download',
    custom_html = '{"headline":"WordPress Starter Kit","subheadline":"Everything you need to build a professional WordPress site - themes, plugins, templates & setup guide. No coding required.","items":["Premium WordPress Theme (worth ₦15,000)","Essential Plugins Bundle","5 Ready-to-Use Page Templates","Step-by-Step Setup Guide","SEO Optimization Checklist","Free Updates for Life"],"cta":"Get My Free Kit"}',
    is_active = 1,
    show_popup = 0,
    updated_at = NOW()
WHERE slug = 'free-wordpress-starter-kit';

-- Verify the update
SELECT id, title, slug, is_active, show_popup 
FROM landing_pages 
WHERE slug = 'free-wordpress-starter-kit';