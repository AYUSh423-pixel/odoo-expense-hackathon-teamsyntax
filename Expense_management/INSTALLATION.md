# Installation Guide - Expense Management System

## Quick Start (5 minutes)

### 1. Prerequisites
- XAMPP installed and running
- Modern web browser
- Internet connection (for APIs)

### 2. Download and Setup
```bash
# Clone the repository
git clone https://github.com/AYUSh423-pixel/odoo-expense-hackathon-teamsyntax.git
cd odoo-expense-hackathon-teamsyntax

# Copy to XAMPP htdocs
# Windows: Copy folder to C:\xampp\htdocs\expense_manager\
# Mac: Copy folder to /Applications/XAMPP/htdocs/expense_manager/
# Linux: Copy folder to /opt/lampp/htdocs/expense_manager/
```

### 3. Database Setup
1. Open XAMPP Control Panel
2. Start Apache and MySQL services
3. Open phpMyAdmin (http://localhost/phpmyadmin)
4. Click "Import" tab
5. Choose `database_schema.sql` file
6. Click "Go" to import
7. (Optional) Import `sample_data.sql` for test data

### 4. Verify Installation
1. Open browser and go to: `http://localhost/expense_manager/setup.php`
2. Check all items show green checkmarks
3. If any errors, follow the suggested fixes

### 5. Access Application
- Go to: `http://localhost/expense_manager/public/`
- Create a new company account or use test accounts:
  - Admin: admin@acme.com / password123
  - Manager: sarah@acme.com / password123
  - Employee: tom@acme.com / password123

## Detailed Installation

### System Requirements
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Web Server**: Apache (included in XAMPP)
- **Browser**: Chrome, Firefox, Safari, or Edge (latest versions)

### Step-by-Step Installation

#### 1. Install XAMPP
1. Download XAMPP from https://www.apachefriends.org/
2. Install following the installer instructions
3. Start XAMPP Control Panel
4. Start Apache and MySQL services

#### 2. Configure PHP (if needed)
- Ensure PHP extensions are enabled:
  - PDO
  - PDO_MySQL
  - JSON
  - cURL
  - mbstring

#### 3. Database Configuration
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create database `expense_manager` (or import schema)
3. Import `database_schema.sql`
4. (Optional) Import `sample_data.sql` for test data

#### 4. Application Configuration
1. Edit `includes/config.php` if needed:
   ```php
   return [
       'db' => [
           'host' => '127.0.0.1',
           'name' => 'expense_manager',
           'user' => 'root',
           'pass' => ''  // Your MySQL password
       ],
       'base_url' => 'http://localhost/expense_manager'
   ];
   ```

#### 5. File Permissions
Ensure the following directories are writable:
- `public/` (for file uploads)
- `api/` (for API responses)
- `includes/` (for configuration)

#### 6. Test Installation
1. Run setup script: `http://localhost/expense_manager/setup.php`
2. Verify all checks pass
3. Test login with sample accounts

## Troubleshooting

### Common Issues

#### 1. Database Connection Failed
**Error**: "Database connection failed"
**Solution**: 
- Check MySQL is running in XAMPP
- Verify database name and credentials in `config.php`
- Ensure database `expense_manager` exists

#### 2. Tables Missing
**Error**: "Table 'expenses' doesn't exist"
**Solution**:
- Import `database_schema.sql` in phpMyAdmin
- Check for SQL errors during import

#### 3. Permission Denied
**Error**: "Permission denied" or "Access forbidden"
**Solution**:
- Check file permissions on directories
- Ensure web server has read access to files
- Check .htaccess files (if any)

#### 4. API Endpoints Not Working
**Error**: "API endpoint not accessible"
**Solution**:
- Check Apache is running
- Verify URL rewriting is enabled
- Check for PHP errors in logs

#### 5. OCR Not Working
**Error**: "Tesseract.js not loading"
**Solution**:
- Check internet connection
- Verify CDN links are accessible
- Check browser console for JavaScript errors

### Log Files
Check these locations for error logs:
- **Apache**: `xampp/apache/logs/error.log`
- **PHP**: `xampp/php/logs/php_error_log`
- **MySQL**: `xampp/mysql/data/mysql_error.log`

### Performance Issues
1. **Slow Loading**: Check database indexes
2. **Memory Issues**: Increase PHP memory limit
3. **Timeout Issues**: Increase PHP execution time

## Development Setup

### For Developers
1. Clone repository
2. Set up local development environment
3. Install dependencies (if any)
4. Configure development database
5. Run tests and setup script

### Environment Variables
For production deployment, consider using environment variables:
```bash
export DB_HOST=localhost
export DB_NAME=expense_manager
export DB_USER=your_user
export DB_PASS=your_password
```

## Security Considerations

### Production Deployment
1. **Change default passwords**
2. **Use HTTPS**
3. **Restrict database user permissions**
4. **Enable error logging**
5. **Regular security updates**

### File Upload Security
1. Validate file types
2. Limit file sizes
3. Store uploads outside web root
4. Scan for malware

## Support

### Getting Help
1. Check this installation guide
2. Review README.md for features
3. Check GitHub issues
4. Contact support team

### Reporting Issues
When reporting issues, include:
- Operating system
- PHP version
- MySQL version
- Error messages
- Steps to reproduce

## Updates

### Updating the Application
1. Backup database
2. Download latest version
3. Replace files (except config)
4. Run database migrations (if any)
5. Test functionality

### Backup
Before any updates:
1. Export database
2. Backup configuration files
3. Backup uploaded files
4. Test restore process

---

**Need help?** Contact us at support@expensemanager.com or create an issue on GitHub.
