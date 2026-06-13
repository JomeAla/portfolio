# ================================================
# Joala Portfolio - Complete Documentation
# ================================================
#
# Last Updated: May 2026
# Project: Joala Ventures Portfolio Website
# Live Site: https://www.joala.com.ng
#
# ================================================

## QUICK START

1. Make changes in local repo: `C:\Users\jomea\portfolio\`
2. Deploy via cPanel File Manager or use deploy.ps1
3. Test with status endpoints
4. Done!

---

## FILE STRUCTURE

```
C:\Users\jomea\portfolio\
│
├── [Core Files]
│   ├── process_emails.php      # Email queue processor
│   ├── process_automation.php  # Automation rules processor
│   ├── LIVE_DEPLOY.php         # Web deployment tool (upload to live)
│   ├── deploy.ps1              # PowerShell deployment script
│   ├── deploy.bat             # Batch deployment script
│   │
│   ├── app/
│   │   ├── Helpers/db_helper.php      # Database helper
│   │   ├── Services/DbConfig.php      # Configuration service
│   │   └── Http/Controllers/          # Laravel controllers
│   │
│   ├── config/
│   │   └── database.php        # Database configuration
│   │
│   └── routes/
│       └── web.php            # Web routes
│
├── [Documentation]
│   ├── README.md              # This file
│   ├── README_QUICK.md        # Quick reference
│   ├── DEPLOYMENT_WORKFLOW.md # Detailed workflow
│   └── DEPLOYMENT_GUIDE.md    # Deployment guide
│
└── [Backup Directory - Created by deploy.ps1]
    └── backups/
        └── [auto-dated backups]
```

---

## DEPLOYMENT METHODS

### Method 1: cPanel File Manager (Recommended)
1. Log into cPanel: https://joala.com.ng/cpanel
2. Go to File Manager > public_html
3. Upload or edit files directly
4. Changes are immediate

### Method 2: LIVE_DEPLOY.php (Web Interface)
1. Upload `LIVE_DEPLOY.php` to public_html via File Manager
2. Access: https://www.joala.com.ng/LIVE_DEPLOY.php?token=deploy2026
3. Use the web interface to deploy files

### Method 3: PowerShell Script
```powershell
cd C:\Users\jomea\portfolio
.\deploy.ps1 -Action deploy -File process_emails.php
```

---

## SYSTEM ENDPOINTS

| Endpoint | Purpose | Auth |
|----------|---------|------|
| https://www.joala.com.ng/ | Homepage | None |
| https://www.joala.com.ng/process_emails.php?status=1 | Email Queue Status | None |
| https://www.joala.com.ng/process_automation.php?status=1 | Automation Status | None |
| https://www.joala.com.ng/LIVE_DEPLOY.php?token=deploy2026 | Deployment Tool | Token Required |

---

## CRON JOBS (Active)

```
*/5 * * * * curl -s 'https://www.joala.com.ng/process_emails.php?auto=1'
*/5 * * * * curl -s 'https://www.joala.com.ng/process_automation.php?auto=1'
```

Run frequency: Every 5 minutes

---

## DATABASE

| Setting | Value |
|---------|-------|
| Host | localhost |
| Database | joalacom_joala |
| Username | joalacom_joala |
| Password | J0ala@2024! |

**Important Tables:**
- `settings` - Contains `process_api_key` and other config
- `email_queue` - Email queue for processing
- `leads` - Lead data

---

## DEPLOYMENT COMMANDS REFERENCE

### PowerShell (Recommended)
```powershell
# Check status
.\deploy.ps1 -Action status

# Deploy file
.\deploy.ps1 -Action deploy -File <filename>

# Initialize credentials
.\deploy.ps1 -Action init

# List live files
.\deploy.ps1 -Action list

# Read live file
.\deploy.ps1 -Action read -File <filename>

# Create backup
.\deploy.ps1 -Action backup

# Sync local to live
.\deploy.ps1 -Action sync
```

### Batch File (Simple)
```batch
deploy.bat status
deploy.bat deploy process_emails.php
deploy.bat init
deploy.bat list
deploy.bat backup
```

---

## TROUBLESHOOTING

### Issue: FTP uploads don't appear on site
**Cause:** FTP and HTTP document roots are different
**Solution:** Use cPanel File Manager or LIVE_DEPLOY.php

### Issue: "API key not configured" error
**Solution:**
1. Access: https://www.joala.com.ng/LIVE_DEPLOY.php?token=deploy2026&action=init
2. Or run: `.\deploy.ps1 -Action init`

### Issue: Cron not processing emails
**Check:**
1. Verify cron is configured in cPanel
2. Check status endpoint for pending emails
3. Verify `process_api_key` exists in settings table

### Issue: Deployment script fails
**Solutions:**
1. Check internet connection
2. Verify LIVE_DEPLOY.php is uploaded to live site
3. Use cPanel File Manager as fallback

---

## HOSTING CONFIGURATION

**Problem Identified:**
- FTP path: `/home/joalacom/public_html/`
- HTTP doc root: Shows same path but different actual directory
- Files uploaded via FTP do NOT appear on live site

**Solution in Place:**
- Use cPanel File Manager for file deployment
- Use LIVE_DEPLOY.php web interface
- Use deploy.ps1 script (which calls LIVE_DEPLOY.php)

---

## SECURITY NOTES

1. **Token Protection:** LIVE_DEPLOY.php requires token `deploy2026`
2. **API Key:** process_emails.php requires API key for processing
3. **No FTP:** File uploads must use cPanel or web interface
4. **Keep Credentials Secure:** Never commit .env or credentials to repo

---

## FILE DEPLOYMENT PRIORITY

### Files to deploy after changes:
1. `process_emails.php` - High priority, affects email processing
2. `process_automation.php` - High priority, affects automation
3. `app/Helpers/db_helper.php` - Medium priority
4. `app/Services/DbConfig.php` - Medium priority
5. `config/database.php` - Medium priority

### Files to upload once:
1. `LIVE_DEPLOY.php` - Upload via cPanel File Manager

---

## CHECKLIST BEFORE DEPLOYMENT

- [ ] Changes tested locally (if applicable)
- [ ] Backup created: `.\deploy.ps1 -Action backup`
- [ ] File encoding verified (UTF-8)
- [ ] Using correct deployment method (cPanel or LIVE_DEPLOY)

---

## CHECKLIST AFTER DEPLOYMENT

- [ ] Tested file via direct URL
- [ ] Verified status endpoint
- [ ] Checked for errors in response
- [ ] Monitored system for 5 minutes

---

## EMERGENCY CONTACTS

- **cPanel:** https://joala.com.ng/cpanel
- **Hosting Provider:** Check your hostinger account
- **Support Email:** support@joala.com.ng

---

## CHANGELOG

### 2026-05-12
- Created complete deployment documentation
- Fixed FTP/HTTP document root mismatch
- Created LIVE_DEPLOY.php web deployment tool
- Created deploy.ps1 and deploy.bat scripts
- Configured cron jobs

### 2026-05-11
- Initial investigation of hosting configuration
- Identified FTP/HTTP path mismatch issue
- Created workaround solution

---

## NOTES

This documentation was created to address the unique hosting configuration
where FTP and HTTP document roots point to different directories despite
showing the same path. All deployment processes have been updated accordingly.

For questions or issues, refer to DEPLOYMENT_WORKFLOW.md for detailed
instructions or README_QUICK.md for quick reference.