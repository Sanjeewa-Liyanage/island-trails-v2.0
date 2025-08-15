# Island Trails v2.0 - Travel Booking System

A full-stack web application for booking travel packages in Sri Lanka, featuring a PHP backend API and HTML/CSS/JavaScript frontend.

## 🏗️ Project Structure

```
island-trails-v2.0/
├── backend/          # PHP API Backend
│   ├── src/
│   │   ├── api/      # API endpoints (auth, bookings, packages)
│   │   ├── classes/  # PHP classes (User, Booking, Packages)
│   │   ├── database/ # Database connection and SSL certificates
│   │   └── utils/    # Utilities (JWT handler, router, API base)
│   ├── vendor/       # Composer dependencies
│   └── composer.json # PHP dependencies
└── frontend/         # Frontend HTML pages
    ├── home.html
    ├── admin.html
    ├── customer.html
    ├── login.html
    └── signup.html
```

## 📋 Prerequisites

### System Requirements
- **PHP**: Version 7.4 or higher
- **Composer**: For PHP dependency management
- **Web Server**: Apache/Nginx or PHP built-in server
- **Database**: MySQL (hosted on Aiven Cloud)

### Required Software Installation

**Windows (PowerShell):**
```powershell
# Install PHP (using Chocolatey)
choco install php

# Install Composer
choco install composer

# Verify installations
php --version
composer --version
```

**Alternative PHP Installation:**
- Download PHP from [php.net](https://www.php.net/downloads)
- Download Composer from [getcomposer.org](https://getcomposer.org/download/)

## 🚀 Installation & Setup

### 1. Clone the Repository
```powershell
git clone https://github.com/Sanjeewa-Liyanage/island-trails-v2.0.git
cd island-trails-v2.0
```

### 2. Backend Setup
```powershell
# Navigate to backend directory
cd backend

# Install PHP dependencies
composer install

# Verify installation
composer show
```

### 3. Database Configuration
The application uses a MySQL database hosted on Aiven Cloud. The connection details are pre-configured in:
- `backend/src/database/connection.php`
- SSL certificate: `backend/src/database/ca.pem`

**Database Schema:**
- `users` - User accounts (customers/admins)
- `packages` - Travel packages
- `bookings` - Booking records

## 🏃‍♂️ Running the Application

### Start the PHP Development Server
```powershell
# From the backend directory
php -S localhost:8000

# Alternative: Start from project root
cd backend
php -S localhost:8000 index.php
```

### Access the Application
- **Frontend**: Open any HTML file in `frontend/` directory in a web browser
- **API Base URL**: `http://localhost:8000`
- **API Documentation**: Available through the router endpoints

## 📦 Dependencies

### PHP Dependencies (Composer)
```json
{
    "require": {
        "firebase/php-jwt": "^6.11"
    }
}
```

**Core Dependencies:**
- **firebase/php-jwt**: JWT token handling for authentication

### Frontend Dependencies
- Pure HTML/CSS/JavaScript (no package manager required)
- Google Fonts (Playfair Display, Lato)
- External CDN resources loaded via HTML

## 🔧 Development Commands

### Composer Commands
```powershell
# Install dependencies
composer install

# Update dependencies
composer update

# Show installed packages
composer show

# Validate composer.json
composer validate

# Dump autoloader (if needed)
composer dump-autoload
```

### Git Commands
```powershell
# Check status
git status

# Pull latest changes
git pull origin main

# Add and commit changes
git add .
git commit -m "Your commit message"
git push origin main
```

### Testing Commands
```powershell
# Test API endpoints (using curl or test files)
php test_api.php
php test_booking_update.php

# Test database connection
php -r "require 'src/database/connection.php'; echo 'Connection successful!';"
```

## 🔌 API Endpoints

### Authentication
- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration
- `POST /api/auth/logout` - User logout

### Packages
- `GET /api/packages` - Get all packages
- `POST /api/packages` - Create package (admin)
- `PUT /api/packages/{id}` - Update package (admin)
- `DELETE /api/packages/{id}` - Delete package (admin)

### Bookings
- `GET /api/bookings` - Get user bookings
- `POST /api/bookings` - Create booking
- `PUT /api/bookings/{id}` - Update booking
- `DELETE /api/bookings/{id}` - Cancel booking (admin)

## 🔐 Authentication

The application uses JWT (JSON Web Tokens) for authentication:
- Tokens are generated on login
- Protected routes require valid JWT tokens
- Role-based access control (customer/admin)

## 🌐 Frontend Pages

- **home.html** - Landing page with package showcase
- **login.html** - User authentication
- **signup.html** - User registration
- **customer.html** - Customer dashboard and bookings
- **admin.html** - Admin panel for managing packages and bookings

## 🗃️ Database

**Connection Details:**
- Host: Aiven Cloud MySQL
- SSL enabled with certificate validation
- Connection pooling via PDO

**Tables:**
- `users` - User management
- `packages` - Travel package catalog
- `bookings` - Booking records with relationships

## 🛠️ Troubleshooting

### Common Issues

**PHP Extensions:**
```powershell
# Enable required extensions in php.ini
extension=pdo_mysql
extension=openssl
extension=curl
```

**CORS Issues:**
- CORS headers are configured in `index.php`
- Supports all common HTTP methods

**Database Connection:**
- SSL certificate must be present in `src/database/`
- Check firewall settings for external database access

### Development Tips
- Use PHP built-in server for development
- Check PHP error logs for debugging
- Validate JSON in API requests/responses
- Test API endpoints individually before frontend integration

## 📝 License

This project is developed for educational purposes.

---

## Quick Start Summary
1. Install PHP and Composer
2. Run `composer install` in backend directory
3. Start server with `php -S localhost:8000`
4. Open frontend HTML files in browser
5. API available at `http://localhost:8000`

## Author
**Sanjeewa Liyanage**
- GitHub: [@Sanjeewa-Liyanage]()

## Version
v2.0 - August 2025
