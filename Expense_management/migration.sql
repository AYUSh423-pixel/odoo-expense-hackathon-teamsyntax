-- Migration script to add receipt_path column and update roles
-- Run this after the main database_schema.sql

USE expense_manager;

-- Add receipt_path column to expenses table
ALTER TABLE expenses ADD COLUMN receipt_path VARCHAR(255) NULL AFTER expense_date;

-- Update user roles to include new roles
ALTER TABLE users MODIFY COLUMN role ENUM('Admin','Manager','Employee','Finance','Director','HR','CFO') DEFAULT 'Employee';

-- Add sample HR and CFO users if they don't exist
INSERT IGNORE INTO users (company_id, name, email, password, role, manager_id, is_manager_approver) VALUES 
(1, 'Emma HR', 'emma@acme.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'HR', 1, 1),
(1, 'David CFO', 'david@acme.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CFO', 1, 1);

-- Update approval sequences to include HR and CFO steps
INSERT IGNORE INTO approval_sequences (company_id, step_order, approver_type, approver_role) VALUES 
(1, 4, 'ROLE', 'HR'),
(1, 5, 'ROLE', 'CFO');

-- Create uploads directory structure (this would be done via PHP, but documenting here)
-- The PHP code will create: ../uploads/receipts/ directory automatically
