# Lapor Ketua (LAPORAN)

A modern, AI-powered public reporting system for Malaysian local governance, community reports, and infrastructure issues. Built with Laravel 12 and Vue 3, featuring automated AI analysis using OpenAI GPT for intelligent report assessment and risk evaluation.

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat&logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=flat&logo=vue.js)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat&logo=php)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-4-38B2AC?style=flat&logo=tailwind-css)
![License](https://img.shields.io/badge/License-MIT-green.svg)

## 📋 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Prerequisites](#-prerequisites)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [Project Structure](#-project-structure)
- [API Endpoints](#-api-endpoints)
- [User Roles & Permissions](#-user-roles--permissions)
- [AI Analysis](#-ai-analysis)
- [Screenshots](#-screenshots)
- [Contributing](#-contributing)
- [License](#-license)

## ✨ Features

### Core Features
- 📝 **Report Management** - Create, edit, view, and delete public issue reports
- 🏷️ **Category System** - Organize reports by customizable categories
- 📎 **File Attachments** - Upload images and documents with reports (view & download)
- 🔍 **Advanced Search & Filters** - Search by keyword, status, category, risk level, and date range
- 📊 **Rich Dashboard** - Role-based dashboards for Super Admin, Admin, and Users

### AI-Powered Analysis
- 🤖 **Automatic AI Analysis** - Every report submission triggers automatic AI analysis
- ⚠️ **Risk Level Assessment** - AI determines risk level (Low/Medium/High/Critical)
- 📈 **Urgency Score** - 1-10 scale urgency rating
- 💡 **Recommended Actions** - AI-generated action recommendations
- 🔗 **Related Issues** - Links to current related public issues
- 📉 **AI Usage Monitoring** - Track OpenAI API usage, tokens, and costs

### User Management
- 👥 **Role-Based Access Control** - Super Admin, Admin, and User roles
- ✅ **Registration Approval System** - Admins approve new user registrations
- 🔐 **Authentication** - Secure login with Laravel Sanctum
- 🔑 **Password Recovery** - Email-based password reset
- 👤 **User Profiles** - Manage personal information

### Administration
- 🎭 **Role & Permission Management** - Granular permission system
- 📊 **Monitoring Dashboard** - System health, storage, and statistics
- 🗂️ **Category Management** - Create and manage report categories
- 📧 **SMTP Configuration** - Configurable email settings
- 📜 **Activity Logs** - Track user actions
- 🔒 **Security Logs** - Monitor login attempts and security events
- 🤖 **AI Logs** - View OpenAI API request history and costs

### Reporting & Analytics
- 📊 **Top 5 Active Reporters** - Dashboard widget showing most active users
- 📈 **Reports by Risk Level** - Breakdown of reports by AI-assessed risk
- 📅 **Recent Reports** - Quick access to latest submissions
- 💰 **Cost Tracking** - Monitor daily AI analysis costs

## 🛠️ Tech Stack

### Backend
- **Framework:** Laravel 12
- **PHP Version:** 8.3+
- **Authentication:** Laravel Sanctum (Session-based)
- **Database:** MySQL 8.0+
- **Architecture:** Repository Pattern with Service Layer
- **AI Integration:** OpenAI API (GPT-3.5-turbo, GPT-4)

### Frontend
- **Framework:** Vue 3 (Composition API with `<script setup>`)
- **Build Tool:** Vite 7
- **Router:** Vue Router 4
- **Styling:** Tailwind CSS 4
- **HTTP Client:** Axios
- **Rich Text Editor:** Tiptap (for report descriptions)

### Development Tools
- **Package Manager:** npm, Composer
- **Code Quality:** Laravel Pint
- **Testing:** PHPUnit, Pest

## 📦 Prerequisites

Before installation, ensure you have:

- PHP 8.3 or higher
- Composer 2.x
- Node.js 18.x or higher
- npm 9.x or higher
- MySQL 8.0 or higher
- Git

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/laporan.git
cd laporan
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install JavaScript Dependencies

```bash
npm install
```

### 4. Environment Configuration

Copy the example environment file and generate application key:

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configure Database

Edit `.env` and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laporan
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 6. Run Database Migrations & Seeders

```bash
php artisan migrate --seed
```

This will create:
- Database tables
- Default roles (Super Admin, Admin, User)
- Permissions
- Default Super Admin account

**Default Super Admin Credentials:**
- Email: `superadmin@example.com`
- Password: `password`

### 7. Create Storage Link

```bash
php artisan storage:link
```

### 8. Build Frontend Assets

For development:
```bash
npm run dev
```

For production:
```bash
npm run build
```

### 9. Start Development Server

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## ⚙️ Configuration

### OpenAI API Setup

1. Navigate to **Settings** in the admin panel
2. Enable AI Analysis
3. Enter your OpenAI API Key
4. Configure AI settings:
   - Model (gpt-3.5-turbo, gpt-4o-mini, gpt-4o, gpt-4-turbo)
   - Temperature (0-1, default: 0.3)
   - Max Tokens (default: 1000)
   - Daily Limit (default: 100)

### SMTP Configuration

Configure email settings in **Settings > SMTP**:
- Host, Port, Encryption
- Username & Password
- From Address & Name

### File Upload Settings

Configure in `.env`:

```env
# Maximum upload size (in KB)
MAX_FILE_SIZE=10240

# Allowed file types
ALLOWED_FILE_TYPES=jpg,jpeg,png,gif,pdf,doc,docx
```

## 🎯 Usage

### For Regular Users

1. **Register Account** - Sign up and wait for admin approval
2. **Create Report** - Submit public issue reports with attachments
3. **Track Reports** - View your submitted reports and their status
4. **View Dashboard** - See your report statistics

### For Admins

1. **Approve Users** - Review and approve pending registrations
2. **Manage Reports** - View, update status of all reports
3. **Manage Categories** - Create and organize report categories
4. **View Analytics** - Access detailed reporting dashboards

### For Super Admins

1. **User Management** - Create, edit, deactivate users
2. **Role & Permission Management** - Configure access control
3. **System Configuration** - Manage SMTP and AI settings
4. **Monitoring** - View system health, logs, and AI usage
5. **Full Access** - All admin features plus system-level controls

## 📁 Project Structure

```
LAPORAN/
├── app/
│   ├── Enums/              # Enumerations (RiskLevel, Status, etc.)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/        # API Controllers
│   │   ├── Middleware/     # Custom Middleware
│   │   ├── Requests/       # Form Requests
│   │   └── Resources/      # API Resources
│   ├── Models/             # Eloquent Models
│   ├── Policies/           # Authorization Policies
│   ├── Repositories/       # Repository Pattern Implementation
│   │   ├── Contracts/      # Repository Interfaces
│   │   └── Eloquent/       # Eloquent Implementations
│   └── Services/           # Business Logic Services
├── database/
│   ├── migrations/         # Database Migrations
│   └── seeders/            # Database Seeders
├── resources/
│   ├── js/
│   │   ├── api/            # Axios API Clients
│   │   ├── components/     # Vue Components
│   │   ├── composables/    # Vue Composables
│   │   ├── pages/          # Page Components
│   │   ├── router/         # Vue Router Configuration
│   │   └── utils/          # Utility Functions
│   └── views/
│       └── app.blade.php   # Main SPA Entry Point
├── routes/
│   ├── api.php             # API Routes
│   └── web.php             # Web Routes
├── storage/
│   ├── app/
│   │   ├── private/        # Private File Storage (attachments)
│   │   └── public/         # Public File Storage
│   └── logs/               # Application Logs
└── public/                 # Public Assets
```

## 🔌 API Endpoints

### Authentication
```
POST   /api/auth/login              # Login
POST   /api/auth/logout             # Logout
POST   /api/auth/register           # Register
POST   /api/auth/forgot-password    # Request password reset
POST   /api/auth/reset-password     # Reset password
GET    /api/auth/user               # Get authenticated user
```

### Reports
```
GET    /api/reports                 # List reports (filtered)
POST   /api/reports                 # Create report (auto AI analysis)
GET    /api/reports/{id}            # Show report
PUT    /api/reports/{id}            # Update report
DELETE /api/reports/{id}            # Delete report
PATCH  /api/reports/{id}/status     # Update status
POST   /api/reports/{id}/analyze    # Re-analyze with AI
```

### Dashboard
```
GET    /api/dashboard               # Get dashboard stats (role-based)
```

### Categories
```
GET    /api/categories              # List categories
POST   /api/categories              # Create category
PUT    /api/categories/{id}         # Update category
DELETE /api/categories/{id}         # Delete category
```

### Users (Admin/Super Admin)
```
GET    /api/users                   # List users
POST   /api/users                   # Create user
GET    /api/users/{id}              # Show user
PUT    /api/users/{id}              # Update user
DELETE /api/users/{id}              # Delete user
```

### Logs (Admin/Super Admin)
```
GET    /api/logs/activity           # Activity logs
GET    /api/logs/security           # Security logs
GET    /api/logs/ai                 # AI usage logs
GET    /api/logs/ai/today-usage     # Today's AI usage stats
```

## 👥 User Roles & Permissions

### Super Admin
- Full system access
- User management (create, edit, delete, assign roles)
- Role & permission management
- System configuration (SMTP, AI settings)
- View all logs and monitoring data

### Admin
- Approve pending user registrations
- View and manage all reports
- Update report status
- Manage categories
- Create users
- Trigger AI analysis
- View activity and security logs

### User (Regular)
- Create and submit reports
- View own reports
- Edit own reports
- Delete own reports
- View dashboard with personal statistics

## 🤖 AI Analysis

### How It Works

1. **Automatic Triggering** - When a user submits a report, AI analysis starts automatically
2. **OpenAI Processing** - Report data is sent to OpenAI API
3. **AI Assessment** - GPT model analyzes the report and returns:
   - Risk Level (Low/Medium/High/Critical)
   - Urgency Score (1-10)
   - Summary
   - Recommended Action
   - Related Current Issue
4. **Data Storage** - Analysis results are stored with the report
5. **Cost Tracking** - Token usage and estimated cost are logged

### Pricing (per 1M tokens)

| Model | Input | Output |
|-------|-------|--------|
| gpt-4o-mini | $0.15 | $0.60 |
| gpt-3.5-turbo | $0.50 | $1.50 |
| gpt-4o | $2.50 | $10.00 |
| gpt-4-turbo | $10.00 | $30.00 |

### AI Analysis Fields

- **Risk Level**: Categorizes report severity
- **Urgency Score**: 1-10 scale (10 = highest urgency)
- **Summary**: Concise overview of the report
- **Recommended Action**: Suggested next steps for authorities
- **Related Issue**: Connection to current Malaysian public issues

## 📸 Screenshots

*(Add screenshots of your application here)*

### Dashboard
- Super Admin Dashboard
- Admin Dashboard
- User Dashboard

### Report Management
- Report List
- Report Detail with AI Analysis
- Create Report Form

### Administration
- User Management
- Category Management
- AI Logs
- System Settings

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Coding Standards

- Follow PSR-12 for PHP code
- Use Laravel Pint for code formatting: `./vendor/bin/pint`
- Write clear commit messages
- Add tests for new features

## 📝 License

This project is open-source software licensed under the [MIT License](LICENSE).

## 👨‍💻 Development Team

- **Developer**: [Your Name]
- **Project**: Public Reporting System with AI Analysis
- **Year**: 2026

## 🙏 Acknowledgments

- Laravel Framework - https://laravel.com
- Vue.js - https://vuejs.org
- Tailwind CSS - https://tailwindcss.com
- OpenAI - https://openai.com
- Icons - Heroicons

## 📧 Support

For support, email support@example.com or open an issue in the GitHub repository.

## 🔄 Changelog

### Version 1.0.0 (2026-02-16)

#### Features
- ✅ Complete report management system
- ✅ Automatic AI analysis on report submission
- ✅ Role-based access control
- ✅ User registration approval workflow
- ✅ File attachment support with view/download
- ✅ Rich text editor for report descriptions
- ✅ Advanced filtering and search
- ✅ Top 5 Active Reporters dashboard widget
- ✅ AI usage and cost tracking
- ✅ Activity, Security, and AI logs
- ✅ SMTP configuration
- ✅ Password recovery
- ✅ System health monitoring

#### AI Analysis
- ✅ Risk Level assessment
- ✅ Urgency scoring
- ✅ Summary generation
- ✅ Recommended actions
- ✅ Related issue identification
- ✅ Cost estimation per request

---

**Made with ❤️ using Laravel and Vue.js**
