# JoAla Portfolio - Setup Instructions

## Developer Information
- **Name:** Jome Alawuru
- **Email:** jomealawuru@hotmail.com
- **Phone:** +2349065257784
- **Twitter:** @jomswoks

---

## Quick Start (Local Development)

### Prerequisites
- PHP 8.1 or higher
- Composer
- Node.js & npm
- MySQL 5.7+

### Step 1: Install Dependencies

```bash
# Navigate to project
cd C:/Users/jomea/JoAla/portfolio

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### Step 2: Configure Environment

```bash
# Copy environment file
copy .env.example .env

# Edit .env with your database credentials
```

Example `.env` configuration:
```env
APP_NAME="JoAla Portfolio"
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=joala_portfolio
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Step 3: Generate Key & Setup Database

```bash
# Generate application key
php artisan key:generate

# Create database (in MySQL)
# CREATE DATABASE joala_portfolio;

# Run migrations
php artisan migrate
```

### Step 4: Create Admin User

```bash
# Run the seeder
php artisan db:seed --class=AdminUserSeeder
```

### Step 5: Start Development Server

```bash
# Start Laravel server
php artisan serve

# Start Vite (for frontend)
npm run dev
```

---

## Admin Login

- **URL:** http://localhost:8000/admin/login
- **Email:** jomealawuru@hotmail.com
- **Password:** password123

---

## Public Pages

| Page | URL |
|------|-----|
| Home | http://localhost:8000/ |
| Portfolio | http://localhost:8000/portfolio |
| Services | http://localhost:8000/services |
| About | http://localhost:8000/about |
| Contact | http://localhost:8000/contact |
| Project Brief | http://localhost:8000/brief |

---

## Default Services (Database Seeds)

The following services will be available:
1. Web Application Development
2. Mobile App Development
3. UI/UX Design
4. API Development & Integration
5. Business Process Automation
6. Technical Consultation

---

## Paystack Setup

1. Create account at https://paystack.com
2. Get your API keys (test keys for development)
3. Go to Admin Panel > Settings > Payment
4. Enter keys and enable test mode

---

## GitHub Integration

1. Create Personal Access Token at https://github.com/settings/tokens
2. Go to Admin Panel > Settings > GitHub
3. Enter username and token

---

## Deployment to Shared Hosting (WhoGoHost)

1. **Upload Files:**
   - Upload all files except `vendor/` to `public_html`
   - Upload `vendor/` folder separately

2. **Setup Database:**
   - Create MySQL database in cPanel
   - Import migration SQL

3. **Configure .env:**
   - Update database credentials
   - Update APP_URL to your domain

4. **Permissions:**
   ```bash
   chmod -R 755 storage/
   chmod -R 755 bootstrap/cache/
   ```

5. **SSL:**
   - Enable Let's Encrypt in cPanel

---

## Support

For issues, check:
- Laravel documentation: https://laravel.com/docs
- Paystack docs: https://paystack.com/docs
- GitHub API docs: https://docs.github.com