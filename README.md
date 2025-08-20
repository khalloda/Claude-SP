# Spare Parts Management System

A comprehensive bilingual (Arabic/English) spare parts web application with full sales & purchasing flows, inventory control, payments, PDF/Email capabilities, and a modern, responsive UI.

## System Requirements

- **Platform**: GoDaddy Plesk (Windows or Linux)
- **PHP**: 8.0+ with PDO extension
- **MySQL**: 5.7 or 8.0
- **Web Server**: Apache with mod_rewrite

## Phase 1 Deployment Instructions

### 1. Upload Files

1. Extract the zip file to your local computer
2. Upload all files to your GoDaddy hosting account
3. **Important**: Set the document root to point to the `/public` directory

### 2. Database Setup

1. Access phpMyAdmin from your Plesk control panel
2. Create database `sp_main` if it doesn't exist
3. Import the SQL files in this order:
   ```
   /sql/schema.sql    (Database structure)
   /sql/seed.sql      (Initial data)
   ```

### 3. Configuration

1. Edit `/app/config/Config.php` if needed:
   ```php
   public static array $database = [
       'host' => 'p3nlmysql13plsk.secureserver.net:3306',
       'dbname' => 'sp_main',
       'username' => 'sp',
       'password' => 'Mi@SP@123',
       // ... other settings
   ];
   ```

2. Ensure proper file permissions:
   - PHP files: 644
   - Directories: 755

### 4. Access the Application

1. Visit your domain (e.g., `https://yourdomain.com`)
2. You should be redirected to the login page
3. Use the default admin credentials:
   - **Email**: admin@example.com
   - **Password**: Admin@123

### 5. Verify Installation

1. **Login Test**: Login with admin credentials
2. **Dashboard Access**: Should redirect to `/dashboard` after login
3. **Language Switch**: Test English/Arabic language switching
4. **RTL Support**: Verify Arabic text displays right-to-left

## Phase 1 Features

### ✅ Core Infrastructure
- PSR-4 Autoloader with case-insensitive fallback
- Router with trailing slash tolerance and {id} parameter support
- Authentication system with session management
- Bilingual support (English/Arabic) with RTL
- CSRF protection for all forms
- Responsive layout with modern UI

### ✅ Database Schema
- Complete database schema with all required tables
- Idempotent SQL scripts (safe to re-run)
- Foreign key relationships
- Proper indexes for performance
- RBAC tables (ready for future phases)

### ✅ Security Features
- Password hashing with PHP's password_hash()
- CSRF token validation
- SQL injection protection via PDO prepared statements
- XSS protection via output escaping
- Session security

### ✅ User Interface
- Clean, modern design with gradients
- Responsive layout (mobile-friendly)
- RTL support for Arabic
- Language switcher
- Flash messages and validation errors
- Professional login page

## File Structure

```
/public/                 # Web root (set as document root)
  /.htaccess            # URL rewriting
  /index.php            # Front controller
  /assets/              # CSS, JS, images
/app/                   # Application code
  /config/              # Configuration files
  /core/                # Core framework classes
  /models/              # Data models
  /controllers/         # Request handlers
  /views/               # HTML templates
  /lang/                # Language files
/sql/                   # Database scripts
  schema.sql            # Database structure
  seed.sql              # Initial data
  /patches/             # Migration scripts
/lib/                   # Third-party libraries
```

## Architecture

### MVC Pattern
- **Models**: Handle database operations
- **Views**: HTML templates with PHP
- **Controllers**: Business logic and request handling

### Core Components
- **Router**: URL routing with middleware support
- **Auth**: Authentication and authorization
- **I18n**: Internationalization and RTL support
- **Helpers**: Utility functions (CSRF, input handling, etc.)
- **DB**: Database abstraction layer

### Design Patterns
- Singleton (Database connection)
- Front Controller (Single entry point)
- Registry (Configuration management)

## Next Phases

### Phase 2: Masters CRUD
- Clients, Suppliers, Warehouses, Products management
- Dropdown management system
- Search and pagination

### Phase 3: Sales Flow
- Quotes → Sales Orders → Invoices
- Line and global tax/discount calculations
- Conversion workflows

### Phase 4: Payments & Stock
- Payment processing and balance tracking
- Stock movements and reservations
- Status management

### Phase 5: Email & PDF
- SMTP email integration
- PDF generation with FPDF
- Bilingual templates

### Phase 6: Reports & Advanced Features
- CSV export functionality
- Client/supplier profile tabs
- Per-warehouse stock views

## Development Guidelines

### Coding Standards
- Use `declare(strict_types=1);` in all PHP files
- Follow PSR-4 autoloading standards
- Escape all output with `htmlspecialchars()`
- Validate all input server-side
- Use prepared statements for database queries

### Security Best Practices
- All POST forms include CSRF tokens
- Validate and sanitize user input
- Use parameterized queries
- Implement proper authentication checks
- Log security events

### Database Guidelines
- All tables use `sp_` prefix
- Use appropriate data types and constraints
- Create indexes for frequently queried columns
- Maintain referential integrity with foreign keys
- Write idempotent migration scripts

## Troubleshooting

### Common Issues

1. **"Class not found" errors**: Check autoloader configuration and file permissions
2. **Database connection failed**: Verify database credentials in Config.php
3. **404 errors**: Ensure .htaccess is working and document root is set to /public
4. **Session issues**: Check PHP session configuration
5. **Permission denied**: Set proper file permissions (644 for files, 755 for directories)

### Debug Mode
Set `$app['debug'] = true` in Config.php to enable error reporting during development.

## Support

For technical support or questions about implementation, refer to the project documentation or contact the development team.

---

**Note**: This is Phase 1 of the complete spare parts management system. Additional features will be added in subsequent phases according to the project specification.
```

---

## Deployment Package Structure

Create the following zip file for Phase 1 delivery:

```
spare_parts_phase1.zip
├── public/
│   ├── .htaccess
│   ├── index.php
│   └── assets/
│       ├── css/app.css
│       └── js/app.js
├── app/
│   ├── config/
│   ├── core/
│   ├── controllers/
│   ├── models/
│   ├── views/
│   └── lang/
├── sql/
│   ├── schema.sql
│   ├── seed.sql
│   └── patches/
├── lib/
└── README.md

```
Phase 2: Masters CRUD - Complete Implementation
What's Included in Phase 2:
✅ Full CRUD Operations

Clients Management - Company/Individual types with contact details
Suppliers Management - Complete supplier database
Warehouses Management - Multiple locations with responsible contacts
Products Management - Detailed product catalog with auto-generated codes
Dropdown Management - Classifications, colors, brands, car makes/models

✅ Enhanced Features

Auto Product Codes - Generated based on classification (e.g., ENG0001, BDY0002)
Dependent Dropdowns - Car models filter by car make
Search & Pagination - Find records across all modules
Warehouse Locations - Track products across multiple warehouses
Client Profiles - Tabs for quotes, orders, invoices, payments, and balances
Stock Management - Track quantities, low stock alerts
Responsive Design - Works on mobile and desktop

✅ UI/UX Improvements

Dropdown Navigation - Masters menu with all modules
Enhanced Tables - Sortable, searchable, paginated
Dynamic Forms - Add/remove warehouse locations
Status Indicators - Color-coded stock levels and statuses
Tab System - Organized client/supplier details
