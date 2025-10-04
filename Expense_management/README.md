# Expense Management System

A comprehensive role-based expense management application built with PHP, MySQL, and modern web technologies.

## 🚀 Features

### Core Functionality
- **Multi-role Authentication**: Admin, Manager, Employee, Finance, Director roles
- **Company Management**: Auto-create company and admin on signup
- **Expense Submission**: Submit expenses with multi-currency support
- **Approval Workflows**: Sequential and parallel approval processes
- **OCR Receipt Processing**: Auto-fill expense details from receipt images
- **Currency Conversion**: Real-time currency conversion with caching
- **Admin Override**: Admin can override any approval decision

### Technical Features
- **Secure Authentication**: Password hashing and session management
- **Database Security**: PDO prepared statements prevent SQL injection
- **Input Validation**: Client and server-side validation
- **Responsive Design**: Modern UI with Tailwind CSS
- **Real-time Updates**: AJAX-powered interactions
- **File Upload Security**: Secure receipt image processing

## 🛠️ Tech Stack

- **Backend**: PHP 7.4+ with PDO
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Styling**: Tailwind CSS
- **OCR**: Tesseract.js
- **APIs**: 
  - ExchangeRate API for currency conversion
  - REST Countries API for country/currency data

## 📋 Prerequisites

- XAMPP (Apache + MySQL)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/AYUSh423-pixel/odoo-expense-hackathon-teamsyntax.git
cd odoo-expense-hackathon-teamsyntax
```

### 2. Set up XAMPP
1. Install XAMPP from https://www.apachefriends.org/
2. Start Apache and MySQL services
3. Copy the project folder to `htdocs/expense_manager/`

### 3. Database Setup
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Import the `database_schema.sql` file
3. This will create the `expense_manager` database with all required tables

### 4. Configuration
1. Update `includes/config.php` with your database credentials if needed
2. The default configuration should work with XAMPP:
   - Host: 127.0.0.1
   - Database: expense_manager
   - Username: root
   - Password: (empty)

### 5. Access the Application
1. Open your browser and navigate to `http://localhost/expense_manager/public/`
2. Create a new company account or login with existing credentials

## 📱 Usage

### For Employees
1. **Submit Expenses**: Upload receipt images for OCR processing
2. **Track Status**: View approval status and comments
3. **Multi-currency**: Submit expenses in any currency

### For Managers
1. **Review Approvals**: See pending expenses requiring approval
2. **Approve/Reject**: Make decisions with comments
3. **View All Expenses**: Monitor company-wide expense activity

### For Admins
1. **User Management**: Create and manage user accounts
2. **Expense Override**: Override any approval decision
3. **Workflow Settings**: Configure approval rules and sequences
4. **Company Settings**: Manage company information

## 🔧 API Endpoints

### Authentication
- `POST /api/auth.php?action=login` - User login
- `POST /api/auth.php?action=signup` - Company signup
- `POST /api/auth.php?action=logout` - User logout

### Expenses
- `POST /api/expenses.php?action=submit` - Submit new expense
- `GET /api/expenses.php?action=list` - List expenses
- `GET /api/expenses.php?action=get&id={id}` - Get expense details

### Approvals
- `POST /api/approvals.php?action=approve` - Approve expense
- `POST /api/approvals.php?action=reject` - Reject expense
- `GET /api/approvals.php?action=pending` - Get pending approvals
- `POST /api/approvals.php?action=override` - Admin override

### Users
- `GET /api/users.php?action=list` - List users
- `POST /api/users.php?action=create` - Create user
- `POST /api/users.php?action=update` - Update user
- `POST /api/users.php?action=delete` - Delete user

## 🗄️ Database Schema

### Core Tables
- **companies**: Company information and settings
- **users**: User accounts with roles and relationships
- **expenses**: Expense records with currency conversion
- **approvals**: Approval workflow tracking
- **approval_rules**: Company-specific approval rules
- **approval_sequences**: Workflow step definitions
- **exchange_rates**: Currency conversion rate cache

## 🔒 Security Features

- **Password Security**: bcrypt hashing with salt
- **SQL Injection Prevention**: PDO prepared statements
- **XSS Protection**: Input sanitization and output escaping
- **Session Management**: Secure session handling
- **File Upload Security**: Type and size validation
- **CSRF Protection**: Token-based request validation

## 🌍 Internationalization

- **Multi-currency Support**: Submit expenses in any currency
- **Automatic Conversion**: Real-time currency conversion
- **Country Selection**: REST Countries API integration
- **Exchange Rate Caching**: Reduces API calls and improves performance

## 📊 OCR Integration

- **Receipt Processing**: Tesseract.js for text extraction
- **Auto-fill Forms**: Automatically populate expense fields
- **Image Support**: PNG, JPG, GIF formats
- **Client-side Processing**: No server-side dependencies

## 🚀 Deployment

### Production Considerations
1. **Environment Variables**: Move sensitive config to environment variables
2. **HTTPS**: Enable SSL/TLS for secure communication
3. **Database Security**: Use dedicated database user with limited privileges
4. **File Uploads**: Store uploaded files outside web root
5. **Error Logging**: Implement proper error logging and monitoring

### Performance Optimization
1. **Database Indexing**: Optimize queries with proper indexes
2. **Caching**: Implement Redis or Memcached for session storage
3. **CDN**: Use CDN for static assets
4. **Compression**: Enable gzip compression

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Team

- **Ayush** - Project Lead & Developer
- **Team Syntax** - Development Team

## 🆘 Support

For support, email support@expensemanager.com or create an issue in the repository.

## 🔮 Future Enhancements

- [ ] Mobile app (React Native/Flutter)
- [ ] Advanced reporting and analytics
- [ ] Integration with accounting software
- [ ] Multi-language support
- [ ] Advanced OCR with machine learning
- [ ] Real-time notifications
- [ ] API rate limiting and throttling
- [ ] Advanced workflow customization
- [ ] Expense categorization with AI
- [ ] Budget tracking and alerts

---

**Built with ❤️ for the Odoo Hackathon Expense Management Challenge**
