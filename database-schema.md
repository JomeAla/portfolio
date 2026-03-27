# Database Schema Design

## Tables

### 1. users (Admin)
```sql
- id (bigint, PK)
- name (string)
- email (string, unique)
- password (string)
- created_at (timestamp)
- updated_at (timestamp)
```

### 2. settings (Site Configuration)
```sql
- id (bigint, PK)
- key (string, unique) - e.g., 'site_name', 'primary_color', 'logo'
- value (text) - JSON or string
- created_at (timestamp)
- updated_at (timestamp)
```

### 3. pages (Dynamic Pages)
```sql
- id (bigint, PK)
- slug (string, unique) - e.g., 'home', 'about', 'contact'
- title (string)
- content (text) - JSON structure for sections
- meta_title (string)
- meta_description (text)
- created_at (timestamp)
- updated_at (timestamp)
```

### 4. projects (Portfolio)
```sql
- id (bigint, PK)
- title (string)
- slug (string, unique)
- description (text)
- problem_solved (text)
- solution (text)
- technologies (json) - array of tech names
- category (string) - web, mobile, api, automation
- industry (string)
- client_name (string, nullable)
- duration (string)
- thumbnail (string) - file path
- images (json) - array of image paths
- github_url (string, nullable)
- live_url (string, nullable)
- order (integer)
- is_featured (boolean)
- created_at (timestamp)
- updated_at (timestamp)
```

### 5. services
```sql
- id (bigint, PK)
- title (string)
- slug (string, unique)
- description (text)
- features (json) - array of feature strings
- pricing (string, nullable)
- icon (string) - icon name/class
- order (integer)
- is_active (boolean)
- created_at (timestamp)
- updated_at (timestamp)
```

### 6. testimonials
```sql
- id (bigint, PK)
- name (string)
- company (string, nullable)
- role (string, nullable)
- content (text)
- avatar (string, nullable)
- rating (integer) - 1-5
- order (integer)
- is_active (boolean)
- created_at (timestamp)
- updated_at (timestamp)
```

### 7. project_briefs (Client Submissions)
```sql
- id (bigint, PK)
- name (string)
- email (string)
- phone (string)
- company (string, nullable)
- project_type (string) - web, mobile, api, etc.
- description (text)
- budget_range (string)
- timeline (string)
- files (json) - array of file paths
- status (string) - new, contacted, in_progress, completed
- notes (text, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

### 8. github_repos
```sql
- id (bigint, PK)
- repo_name (string)
- description (text)
- language (string)
- stars (integer)
- forks (integer)
- url (string)
- last_updated (timestamp)
- is_displayed (boolean)
- created_at (timestamp)
- updated_at (timestamp)
```

### 9. payments (Payment Logs)
```sql
- id (bigint, PK)
- reference (string, unique)
- amount (decimal)
- currency (string)
- status (string) - pending, success, failed
- customer_email (string)
- metadata (json)
- created_at (timestamp)
- updated_at (timestamp)
```

## Settings Keys Reference

| Key | Type | Description |
|-----|------|-------------|
| site_name | string | Website title |
| site_description | string | SEO description |
| logo | string | Logo file path |
| favicon | string | Favicon file path |
| primary_color | string | Hex color code |
| accent_color | string | Hex color code |
| font_heading | string | Font family name |
| font_body | string | Font family name |
| paystack_public_key | string | Paystack public key |
| paystack_secret_key | string | Paystack secret key |
| paystack_test_mode | boolean | Enable test mode |
| github_username | string | GitHub username |
| github_token | string | Personal access token |
| email_notifications | boolean | Enable email alerts |
| whatsapp_notifications | boolean | Enable WhatsApp alerts |
| contact_email | string | Contact form destination |
| address | string | Business address |
| phone | string | Phone number |
| whatsapp | string | WhatsApp number |
| hero_title | string | Homepage hero title |
| hero_subtitle | string | Homepage hero subtitle |
| cta_text | string | Call to action text |
| cta_link | string | Call to action link |