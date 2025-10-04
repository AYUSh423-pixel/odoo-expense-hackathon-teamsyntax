-- Sample data for testing the Expense Management System
-- Run this after creating the database schema

USE expense_manager;

-- Insert sample company
INSERT INTO companies (name, country, currency) VALUES 
('ACME Corporation', 'United States', 'USD'),
('TechStart India', 'India', 'INR'),
('Global Solutions Ltd', 'United Kingdom', 'GBP');

-- Insert sample users (passwords are 'password123' hashed)
-- Note: In production, use proper password hashing
INSERT INTO users (company_id, name, email, password, role, manager_id, is_manager_approver) VALUES 
-- ACME Corporation users
(1, 'John Admin', 'admin@acme.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', NULL, 1),
(1, 'Sarah Manager', 'sarah@acme.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Manager', 1, 1),
(1, 'Mike Finance', 'mike@acme.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Finance', 1, 1),
(1, 'Lisa Director', 'lisa@acme.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Director', 1, 1),
(1, 'Emma HR', 'emma@acme.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'HR', 1, 1),
(1, 'David CFO', 'david@acme.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CFO', 1, 1),
(1, 'Tom Employee', 'tom@acme.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Employee', 2, 0),
(1, 'Jane Employee', 'jane@acme.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Employee', 2, 0),

-- TechStart India users
(2, 'Rajesh Admin', 'admin@techstart.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', NULL, 1),
(2, 'Priya Manager', 'priya@techstart.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Manager', 7, 1),
(2, 'Amit Employee', 'amit@techstart.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Employee', 8, 0);

-- Insert sample expenses
INSERT INTO expenses (company_id, user_id, original_amount, original_currency, company_amount, company_currency, exchange_rate, category, description, expense_date, status) VALUES 
-- ACME Corporation expenses
(1, 5, 150.00, 'USD', 150.00, 'USD', 1.0000, 'Travel', 'Flight to New York for client meeting', '2024-01-15', 'Approved'),
(1, 5, 75.50, 'USD', 75.50, 'USD', 1.0000, 'Meals', 'Business dinner with client', '2024-01-16', 'Pending'),
(1, 6, 200.00, 'EUR', 220.00, 'USD', 1.1000, 'Accommodation', 'Hotel stay in Paris', '2024-01-20', 'Rejected'),
(1, 6, 45.00, 'USD', 45.00, 'USD', 1.0000, 'Office Supplies', 'Stationery and office materials', '2024-01-22', 'Approved'),

-- TechStart India expenses
(2, 9, 5000.00, 'INR', 5000.00, 'INR', 1.0000, 'Travel', 'Train tickets for business trip', '2024-01-18', 'Pending'),
(2, 9, 1200.00, 'INR', 1200.00, 'INR', 1.0000, 'Meals', 'Team lunch meeting', '2024-01-19', 'Approved');

-- Insert approval sequences for companies
INSERT INTO approval_sequences (company_id, step_order, approver_type, approver_role) VALUES 
-- ACME Corporation workflow: Manager -> Finance -> Director -> HR -> CFO
(1, 1, 'MANAGER', NULL),
(1, 2, 'ROLE', 'Finance'),
(1, 3, 'ROLE', 'Director'),
(1, 4, 'ROLE', 'HR'),
(1, 5, 'ROLE', 'CFO'),

-- TechStart India workflow: Manager -> Finance
(2, 1, 'MANAGER', NULL),
(2, 2, 'ROLE', 'Finance');

-- Insert approval rules
INSERT INTO approval_rules (company_id, mode, rule_type, threshold, specific_approver_id) VALUES 
(1, 'SEQUENTIAL', 'None', NULL, NULL),
(2, 'SEQUENTIAL', 'None', NULL, NULL);

-- Insert sample approvals
INSERT INTO approvals (expense_id, approver_id, step_order, status, comments, action_time) VALUES 
-- ACME Corporation approvals
(1, 2, 1, 'Approved', 'Approved for client meeting', '2024-01-15 10:30:00'),
(1, 3, 2, 'Approved', 'Budget approved', '2024-01-15 14:20:00'),
(1, 4, 3, 'Approved', 'Final approval', '2024-01-15 16:45:00'),

(2, 2, 1, 'Pending', NULL, NULL),

(3, 2, 1, 'Rejected', 'Expense not justified', '2024-01-20 09:15:00'),

(4, 2, 1, 'Approved', 'Office supplies approved', '2024-01-22 11:30:00'),
(4, 3, 2, 'Approved', 'Budget within limits', '2024-01-22 15:20:00'),
(4, 4, 3, 'Approved', 'Final approval', '2024-01-22 17:10:00'),

-- TechStart India approvals
(5, 8, 1, 'Pending', NULL, NULL),

(6, 8, 1, 'Approved', 'Team lunch approved', '2024-01-19 12:00:00'),
(6, 7, 2, 'Approved', 'Budget approved', '2024-01-19 14:30:00');

-- Insert sample exchange rates
INSERT INTO exchange_rates (base_currency, rates, fetched_at) VALUES 
('USD', '{"rates":{"EUR":0.85,"GBP":0.73,"INR":83.0,"JPY":110.0,"CAD":1.25,"AUD":1.35}}', NOW()),
('EUR', '{"rates":{"USD":1.18,"GBP":0.86,"INR":97.5,"JPY":129.0,"CAD":1.47,"AUD":1.59}}', NOW()),
('INR', '{"rates":{"USD":0.012,"EUR":0.010,"GBP":0.009,"JPY":1.33,"CAD":0.015,"AUD":0.016}}', NOW());

-- Update expenses status based on approvals
UPDATE expenses SET status = 'Approved' WHERE id IN (1, 4, 6);
UPDATE expenses SET status = 'Rejected' WHERE id = 3;
