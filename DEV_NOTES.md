# Development Notes

## Files to Remove After Development

These files were created for setup and testing. Remove before going to production:

### Setup Scripts
- `setup_email_sequence.php` - Sets up lead magnet sequence (run once)
- `setup_post_purchase_sequence.php` - Sets up post-purchase sequence (run once)
- `simulate_success.php` - Simulates payment success for testing
- `check_queue.php` - Checks email queue status

### Other Files
- `CREDENTIALS.md` - Contains sensitive credentials
- `*.sql` - Database exports
- `*.log` - Log files
- `server.log` - Server logs

### Command to Remove
```bash
rm setup_email_sequence.php setup_post_purchase_sequence.php simulate_success.php check_queue.php CREDENTIALS.md
rm -f *.sql *.log server.log
```

---

## Current Setup (as of April 21, 2026)

### Email Sequences
1. **Lead Magnet Nurture** (ID: 1) - For email checklist subscribers
2. **Post-Purchase** (ID: 2) - For product buyers

### Database Stats
- Leads created
- Email queues queued
- Sequences configured

### To Re-run Setup
1. Run `setup_email_sequence.php` on live
2. Run `setup_post_purchase_sequence.php` on live