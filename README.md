# Laravel API Starter

**Laravel API Starter** is a production-ready backend boilerplate for building RESTful APIs, featuring OTP-based authentication, role-based access control, standardized architecture patterns with service layers and form requests, and comprehensive test coverage to accelerate development.

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

## 🚀 Features

### Core Features
- **🔐 Authentication System**
  - Email-based registration with OTP verification
  - Secure login/logout with Laravel Sanctum
  - Rate limiting on authentication endpoints
  - Password hashing with Bcrypt

- **👥 Role-Based Access Control (RBAC)**
  - User and Admin roles
  - Middleware-based authorization
  - Flexible permission system

- **📦 Pre-built Modules**
  - Company management (CRUD operations)
  - User management
  - Easily extensible for new modules

### Architecture & Patterns
- **Service Layer Pattern**: Business logic separated from controllers
- **Form Request Validation**: Centralized validation logic
- **API Resources**: Consistent JSON response formatting
- **Repository Pattern Ready**: Easy to implement data access layer
- **Custom Response Handler**: Standardized API responses

### Developer Experience
- **Comprehensive Test Coverage**: Unit and Feature tests with PHPUnit
- **Code Quality**: Laravel Pint for code formatting
- **Error Monitoring**: Sentry integration for production error tracking
- **Development Tools**: Laravel Pail for log monitoring, Tinker for REPL
- **Automated Scripts**: Composer scripts for setup, testing, and development

## 📋 Requirements

- PHP >= 8.2
- Composer
- PostgreSQL (or your preferred database)
- Node.js & npm (for asset compilation)

## 🛠️ Installation

### Quick Setup

Clone the repository and run the automated setup script:

```bash
git clone https://github.com/SubheSadek/laravel-starter.git
cd laravel-starter
composer setup
```

This will automatically:
- Install PHP dependencies
- Copy `.env.example` to `.env`
- Generate application key
- Run database migrations
- Install and build frontend assets

### Manual Setup

If you prefer manual setup:

```bash
# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database in .env file
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Run migrations
php artisan migrate

# Install frontend dependencies
npm install
npm run build
```

## 🔧 Configuration

### Environment Variables

Key environment variables to configure:

```env
# Application
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Mail (for OTP emails)
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@example.com

# Queue (for background jobs)
QUEUE_CONNECTION=database

# Sentry (optional, for error tracking)
SENTRY_LARAVEL_DSN=your_sentry_dsn
```

## 🚦 Running the Application

### Development Mode

Use the composer dev script to run all development services:

```bash
composer dev
```

This will start:
- Laravel development server (http://localhost:8000)
- Queue worker for background jobs
- Log monitoring with Laravel Pail
- Vite dev server for assets

### Individual Services

Or run services individually:

```bash
# Start development server
php artisan serve

# Run queue worker
php artisan queue:work

# Monitor logs
php artisan pail

# Build assets
npm run dev
```

## 📚 API Documentation

### Authentication Endpoints

#### Register User
```http
POST /api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePassword123",
  "password_confirmation": "SecurePassword123"
}
```

#### Verify User (OTP)
```http
POST /api/auth/verify-user
Content-Type: application/json

{
  "email": "john@example.com",
  "otp": "123456"
}
```

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "SecurePassword123"
}
```

#### Get Authenticated User
```http
GET /api/auth/auth-user
Authorization: Bearer {token}
```

#### Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

### Company Endpoints

All company endpoints require authentication.

#### List Companies
```http
GET /api/company/company-list
Authorization: Bearer {token}
```

#### Create Company
```http
POST /api/company/create-company
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Company Name",
  "email": "company@example.com"
}
```

#### Get Company Details
```http
GET /api/company/company-details/{id}
Authorization: Bearer {token}
```

#### Update Company
```http
PUT /api/company/update-company/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Updated Company Name"
}
```

#### Delete Company
```http
DELETE /api/company/delete-company/{id}
Authorization: Bearer {token}
```

## 🧪 Testing

### Run All Tests
```bash
composer test
# or
php artisan test
```

### Run Specific Test Suite
```bash
# Unit tests only
php artisan test --testsuite=Unit

# Feature tests only
php artisan test --testsuite=Feature

# With coverage
php artisan test --coverage
```

### Test Structure
```
tests/
├── Feature/          # Integration tests
│   └── CompanyTest.php
└── Unit/             # Unit tests
    └── Services/
        └── AuthServiceTest.php
```

## 📁 Project Structure

```
laravel-starter/
├── app/
│   ├── Helper/              # Helper functions
│   │   └── ResponseHandler.php
│   ├── Http/
│   │   ├── Controllers/      # API Controllers
│   │   ├── Middleware/       # Custom middleware
│   │   ├── Requests/         # Form request validation
│   │   └── Resources/        # API resources
│   ├── Mail/                 # Email templates
│   ├── Models/               # Eloquent models
│   └── Services/             # Business logic layer
├── config/                   # Configuration files
├── database/
│   ├── factories/            # Model factories
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── routes/
│   └── api.php               # API routes
└── tests/
    ├── Feature/              # Feature tests
    └── Unit/                 # Unit tests
```

## 🏗️ Architecture Patterns

### Service Layer
Business logic is encapsulated in service classes:

```php
// app/Services/AuthService.php
class AuthService
{
    public function register(array $data): array
    {
        // Registration logic
    }
}
```

### Form Requests
Request validation is handled by dedicated form request classes:

```php
// app/Http/Requests/CompanyRequest.php
class CompanyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}
```

### API Resources
Consistent JSON responses using API resource transformers:

```php
// app/Http/Resources/AuthResource.php
class AuthResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            // ...
        ];
    }
}
```

### Response Handler
Standardized API responses:

```php
return success('User registered successfully', $data);
return error('Validation failed', 422, $errors);
```

## 🔒 Security Features

- **Rate Limiting**: Authentication endpoints have throttling (8 requests per minute)
- **Password Hashing**: Bcrypt with configurable rounds
- **OTP Verification**: Email-based OTP for user verification
- **Token-Based Authentication**: Laravel Sanctum for API authentication
- **CSRF Protection**: Built-in Laravel CSRF protection
- **SQL Injection Prevention**: Eloquent ORM with parameter binding

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Coding Standards

This project follows Laravel coding standards:
- Run `composer pint` to format code
- Write tests for new features
- Update documentation as needed

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🙏 Acknowledgments

- [Laravel Framework](https://laravel.com) - The PHP framework for web artisans
- [Laravel Sanctum](https://laravel.com/docs/sanctum) - API authentication
- [Sentry](https://sentry.io) - Error tracking and monitoring

## 📧 Contact

For questions or support, please open an issue on GitHub.

---

**Built with ❤️ using Laravel**
