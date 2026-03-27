# JoAla Portfolio - Developer Portfolio Website

**Developer:** Jome Alawuru  
**Phone:** +2349065257784  
**Twitter:** @jomswoks  
**Location:** Nigeria  
**Email:** jomealawuru@hotmail.com

## Project Overview

A professional portfolio website for a Nigerian-based custom application developer offering web development, mobile app development, UI/UX design, and automation services.

### Key Features
- Full admin panel for content management
- Customizable design (colors, logo, fonts)
- Paystack payment integration with admin-configurable API keys
- GitHub integration for project showcase
- Client project brief submission system
- Email and WhatsApp notifications

---

## Services Offered

| Service | Description |
|---------|-------------|
| **Web Application Development** | Custom web apps using modern frameworks |
| **Mobile App Development** | iOS and Android native/hybrid apps |
| **UI/UX Design** | User interface and experience design, wireframes, prototypes |
| **API Development & Integration** | RESTful APIs and third-party integrations |
| **Business Process Automation** | Automate workflows and business processes |
| **Technical Consultation** | Technical advice and architecture planning |

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP Laravel 10+ |
| Frontend | Blade + Tailwind CSS |
| Database | MySQL |
| Payment | Paystack |
| Hosting | Shared hosting (WhoGoHost compatible) |

---

## Directory Structure

```
joala-portfolio/
├── app/
│   ├── Models/          # Eloquent models
│   ├── Http/Controllers/# Controllers
│   └── Providers/      # Service providers
├── database/
│   ├── migrations/     # Database migrations
│   └── seeders/        # Data seeders
├── resources/
│   ├── views/          # Blade templates
│   │   ├── admin/      # Admin panel views
│   │   └── front/      # Public-facing views
│   └── css/            # Stylesheets
├── routes/              # Route definitions
├── public/             # Public assets
└── storage/            # Storage files
```

---

## Quick Start

```bash
# 1. Create Laravel project
composer create-project laravel/laravel joala-portfolio

# 2. Install dependencies
composer require laravel/breeze
npm install
npm install -D tailwindcss postcss autoprefixer

# 3. Configure environment
cp .env.example .env
# Edit .env with your database credentials

# 4. Generate application key
php artisan key:generate

# 5. Run migrations
php artisan migrate

# 6. Create admin user
php artisan make:seeder AdminUserSeeder
php artisan db:seed

# 7. Build frontend assets
npm run build

# 8. Start development server
php artisan serve
```

---

## Database Schema

### Tables

| Table | Description |
|-------|-------------|
| `users` | Admin users for authentication |
| `settings` | Site configuration (key-value store) |
| `pages` | Dynamic page content |
| `projects` | Portfolio projects |
| `services` | Services offered |
| `testimonials` | Client testimonials |
| `project_briefs` | Client project submissions |
| `github_repos` | GitHub repository data |
| `payments` | Payment transaction logs |

---

## Admin Features

### Content Management
- Edit all page content dynamically
- Manage portfolio projects (CRUD)
- Manage services (CRUD)
- Manage testimonials (CRUD)
- View and respond to project briefs

### Design Customization
- Upload logo and favicon
- Change primary and accent colors
- Select fonts (heading and body)
- Live preview of changes

### Payment Configuration
- Configure Paystack API keys (test/live)
- Toggle test mode
- View payment history

### GitHub Integration
- Connect GitHub account with token
- Auto-fetch repositories
- Display repo stats (stars, forks)

---

## Public Features

### Pages
- **Home**: Hero, featured projects, testimonials, stats, CTA
- **Portfolio**: Filterable project grid with details
- **Services**: Service cards with pricing
- **About**: Developer profile, skills, experience
- **Contact**: Contact form, info, WhatsApp link
- **Project Brief**: Multi-step form for new projects

### Integrations
- **Paystack**: Accept payments for services
- **GitHub**: Display portfolio from GitHub
- **Email**: Notifications for new briefs
- **WhatsApp**: Direct contact option

---

## Configuration

### Environment Variables (.env)

```env
APP_NAME=JoAla Portfolio
APP_URL=http://localhost
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=joala_portfolio
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025

PAYSTACK_PUBLIC_KEY=
PAYSTACK_SECRET_KEY=
PAYSTACK_TEST_MODE=true
```

### Settings (Managed via Admin)

| Key | Description | Example |
|-----|-------------|---------|
| `site_name` | Website title | "JoAla Development" |
| `logo` | Logo file path | "/uploads/logos/logo.png" |
| `primary_color` | Main brand color | "#0f172a" |
| `accent_color` | Accent color | "#3b82f6" |
| `font_heading` | Heading font | "Cabinet Grotesk" |
| `font_body` | Body font | "Geist" |
| `paystack_public_key` | Paystack public key | "pk_test_..." |
| `paystack_secret_key` | Paystack secret key | "sk_test_..." |
| `github_username` | GitHub username | "joala-dev" |

---

## Deployment to Shared Hosting

### 1. Prepare Files
- Upload all files except `vendor/` to public_html
- Upload `vendor/` folder or run `composer install` on server
- Ensure `.env` is configured with production values

### 2. Database Setup
- Create MySQL database on hosting
- Import database schema
- Update `.env` with database credentials

### 3. Permissions
```bash
# Set proper permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### 4. SSL Configuration
- Enable Let's Encrypt via cPanel
- Update APP_URL to https://

---

## API Keys Management

### Paystack
1. Get keys from Paystack dashboard
2. Go to Admin Panel > Settings > Payment
3. Enter test keys initially
4. Toggle "Test Mode" on
5. When ready, enter production keys and toggle off test mode

### GitHub
1. Create personal access token at github.com/settings/tokens
2. Grant `repo` scope for private repos
3. Add token in Admin Panel > Settings > GitHub

---

## Development Workflow

### Using AI Coding Agents

When building this project with AI agents:
1. Provide the brief.md document
2. Share the TODO.md task list
3. Use the Laravel + Tailwind skill guide
4. Reference this README for structure

### Code Style
- Follow Laravel conventions
- Use Tailwind CSS for styling
- Implement proper validation
- Add comments for complex logic

---

## Support

For issues or questions:
- Check Laravel documentation
- Review Paystack integration docs
- Check GitHub API documentation

---

## License

This project is for personal portfolio use.