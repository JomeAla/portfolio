# Local Environment Setup Plan for JoAla Portfolio

## Current System Status

| Component | Status |
|-----------|--------|
| PHP | Not installed |
| Composer | Not installed |
| MySQL | Not installed |
| Node.js | ✅ Installed (v24) |
| npm | ✅ Installed (v11.3.0) |

## Recommended Setup Options

### Option A: XAMPP (Recommended for Beginners)
- **Pros:** All-in-one bundle (PHP, MySQL, Apache), easy setup
- **Cons:** Uses more resources
- **Download:** https://www.apachefriends.org/

### Option B: Manual Installation
- **Pros:** Full control, lighter
- **Cons:** More configuration needed

### Option C: Laravel Herd (Modern Option)
- **Pros:** Modern, Laravel-optimized
- **Cons:** Newer, less familiar

---

## Detailed Setup Plan (Using XAMPP)

### Phase 1: Install XAMPP (~15 mins)
1. Download XAMPP from https://www.apachefriends.org/
2. Run installer (choose PHP 8.1+ version)
3. Install with default settings
4. Start Apache and MySQL services from XAMPP Control Panel

### Phase 2: Create MySQL Database
1. Open http://localhost/phpmyadmin
2. Create new database: `joala_portfolio`
3. Note credentials (root, no password by default)

### Phase 3: Run Laravel Project
```bash
cd C:/Users/jomea/JoAla/portfolio

# Install Composer dependencies
composer install

# Install NPM dependencies  
npm install

# Setup environment
copy .env.example .env

# Edit .env with:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=joala_portfolio
# DB_USERNAME=root
# DB_PASSWORD=

# Generate key and setup
php artisan key:generate
php artisan migrate
php artisan db:seed --class=AdminUserSeeder

# Start server
php artisan serve
```

### Phase 4: Access Application
- **Local URL:** http://localhost:8000
- **Admin URL:** http://localhost:8000/admin/login
- **Login:** jomealawuru@hotmail.com / password123

---

## Alternative: Use Windows Package Managers

### Using Winget (if available)
```powershell
winget install PHP
winget install Composer
winget install MySQL
```

### Using Chocolatey
```powershell
choco install php
choco install composer
choco install mysql
```

---

## Checklist Before Proceeding

- [ ] Download and install XAMPP
- [ ] Start Apache & MySQL services
- [ ] Create database in phpMyAdmin
- [ ] Verify PHP version (should be 8.1+)

---

## Questions Before I Proceed

1. **XAMPP or Manual?** Which installation method do you prefer?
2. **Already have XAMPP?** Do you have any web server already installed?
3. **Database preference:** Should I set up the MySQL database, or do you prefer PostgreSQL?

Once you confirm, I can guide you through each step interactively.