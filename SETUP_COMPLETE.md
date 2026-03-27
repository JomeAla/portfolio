# JoAla Portfolio - Setup Complete

## What's Been Done

1. **PostgreSQL Database Created**
   - Database: `joala_portfolio`
   - Host: localhost:5432
   - User: postgres
   - Password: Mylordhelpme12

2. **.env File Configured**
   - Updated for PostgreSQL connection
   - Database credentials set

3. **Git Repository Ready**
   - Remote: https://github.com/JomeAlawuru/joala-portfolio.git

## Next Steps (Manual)

Since PHP/Composer need to be manually added to PATH, here's what to do:

### Option 1: Manual PHP Setup

1. **Open Command Prompt as Administrator** and run:
```cmd
winget install --id PHP.PHP.8.1
```

2. **Restart your computer** (required for PATH updates)

3. **Install Composer:**
   - Download: https://getcomposer.org/download/
   - Run the installer

4. **Then in project folder:**
```bash
cd C:/Users/jomea/JoAla/portfolio

composer install
npm install
php artisan key:generate
php artisan migrate
php artisan db:seed --class=AdminUserSeeder
php artisan serve
```

### Option 2: Use XAMPP (Recommended)

1. Download XAMPP from: https://www.apachefriends.org/
2. Install with PHP 8.1+
3. Start Apache & MySQL
4. Change .env to use MySQL instead of PostgreSQL
5. Run:
```bash
composer install
php artisan migrate
php artisan serve
```

---

## Git Status

The project has been committed and is ready to push to GitHub.

## Access After Setup

- **Local URL:** http://localhost:8000
- **Admin URL:** http://localhost:8000/admin/login
- **Email:** jomealawuru@hotmail.com
- **Password:** password123