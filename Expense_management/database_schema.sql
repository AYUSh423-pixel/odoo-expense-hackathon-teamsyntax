-- Expense Management System Database Schema
-- Run this in phpMyAdmin to create the database and tables

CREATE DATABASE IF NOT EXISTS expense_manager;
USE expense_manager;

-- Companies table
CREATE TABLE companies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  country VARCHAR(100) NOT NULL,
  currency VARCHAR(10) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Users table
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  company_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('Admin','Manager','Employee','Finance','Director') DEFAULT 'Employee',
  manager_id INT NULL,
  is_manager_approver TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Expenses table
CREATE TABLE expenses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  company_id INT NOT NULL,
  user_id INT NOT NULL,
  original_amount DECIMAL(12,2) NOT NULL,
  original_currency VARCHAR(10) NOT NULL,
  company_amount DECIMAL(12,2) NULL,
  company_currency VARCHAR(10) NULL,
  exchange_rate DECIMAL(18,8) NULL,
  category VARCHAR(80) NOT NULL,
  description TEXT,
  expense_date DATE NOT NULL,
  status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Approvals table - one row per approver per expense
CREATE TABLE approvals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  expense_id INT NOT NULL,
  approver_id INT NOT NULL,
  step_order INT DEFAULT 1,
  status ENUM('Pending','Approved','Rejected','Skipped') DEFAULT 'Pending',
  comments TEXT,
  action_time DATETIME NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (expense_id) REFERENCES expenses(id) ON DELETE CASCADE,
  FOREIGN KEY (approver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Approval rules per company
CREATE TABLE approval_rules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  company_id INT NOT NULL,
  mode ENUM('SEQUENTIAL','PARALLEL') DEFAULT 'SEQUENTIAL',
  rule_type ENUM('None','Percentage','Specific','Hybrid') DEFAULT 'None',
  threshold INT NULL, -- e.g., 60 for 60%
  specific_approver_id INT NULL, -- special approver e.g., CFO user id
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  FOREIGN KEY (specific_approver_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Approval sequences - defines the workflow steps
CREATE TABLE approval_sequences (
  id INT AUTO_INCREMENT PRIMARY KEY,
  company_id INT NOT NULL,
  step_order INT NOT NULL,
  approver_type ENUM('USER','ROLE','MANAGER') DEFAULT 'USER',
  approver_user_id INT NULL,
  approver_role VARCHAR(40) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  FOREIGN KEY (approver_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Exchange rates cache
CREATE TABLE exchange_rates (
  base_currency VARCHAR(10) PRIMARY KEY,
  rates JSON NOT NULL,
  fetched_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Audit log for tracking changes
CREATE TABLE audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  table_name VARCHAR(50) NOT NULL,
  record_id INT NOT NULL,
  action VARCHAR(20) NOT NULL,
  old_values JSON,
  new_values JSON,
  user_id INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Indexes for better performance
CREATE INDEX idx_users_company ON users(company_id);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_expenses_company ON expenses(company_id);
CREATE INDEX idx_expenses_user ON expenses(user_id);
CREATE INDEX idx_expenses_status ON expenses(status);
CREATE INDEX idx_approvals_expense ON approvals(expense_id);
CREATE INDEX idx_approvals_approver ON approvals(approver_id);
CREATE INDEX idx_approvals_status ON approvals(status);
CREATE INDEX idx_approval_sequences_company ON approval_sequences(company_id);
CREATE INDEX idx_audit_logs_record ON audit_logs(table_name, record_id);
