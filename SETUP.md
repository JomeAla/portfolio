# Developer Portfolio - Environment Setup

## Requirements

- PHP 8.1 or higher
- Composer
- MySQL 5.7+
- Node.js & NPM (for Tailwind CSS)

## Quick Start

1. Create new Laravel project:
```bash
composer create-project laravel/laravel joala-portfolio
cd joala-portfolio
```

2. Install dependencies:
```bash
composer require laravel/breeze spatie/laravel-permission
npm install
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init
```

3. Copy .env file and configure:
```bash
cp .env.example .env
```

4. Update .env with your database credentials and settings

5. Run migrations:
```bash
php artisan migrate
```

6. Create admin user:
```bash
php artisan make:seeder AdminUserSeeder
```

7. Build assets:
```bash
npm run build
```

8. Start development server:
```bash
php artisan serve
```

## Deployment to Shared Hosting

1. Upload all files except /vendor to your hosting
2. Upload /vendor folder or run `composer install` on server
3. Configure .env on server
4. Run migrations on server
5. Set proper file permissions