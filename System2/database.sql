-- Database schema for Debt Management System

-- Create database
CREATE DATABASE IF NOT EXISTS debt_management_system;
USE debt_management_system;

-- Users table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Debtors table
CREATE TABLE debtors (
    debtor_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    postal_code VARCHAR(20),
    country VARCHAR(50),
    total_debt DECIMAL(15,2) DEFAULT 0.00,
    status ENUM('active', 'inactive', 'paid', 'in_dispute', 'under_review') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    notes TEXT,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES users(user_id)
);

-- Payment plans table
CREATE TABLE payment_plans (
    plan_id INT PRIMARY KEY AUTO_INCREMENT,
    debtor_id INT NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    monthly_payment DECIMAL(15,2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (debtor_id) REFERENCES debtors(debtor_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id)
);

-- Payments table
CREATE TABLE payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    debtor_id INT NOT NULL,
    plan_id INT,
    amount DECIMAL(15,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'credit_card', 'other') NOT NULL,
    status ENUM('pending', 'completed', 'failed', 'refunded', 'in_dispute', 'under_review') DEFAULT 'pending',
    reference_number VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (debtor_id) REFERENCES debtors(debtor_id),
    FOREIGN KEY (plan_id) REFERENCES payment_plans(plan_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id)
);

-- Notifications table
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('payment', 'overdue', 'system', 'other') NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Settings table
CREATE TABLE settings (
    setting_id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('company_name', 'Debt Management System', 'Company name'),
('currency', 'USD', 'Default currency'),
('payment_reminder_days', '7', 'Days before payment due date to send reminder'),
('overdue_threshold_days', '30', 'Number of days after which a payment is considered overdue'),
('max_payment_plans', '12', 'Maximum number of payment plans per debtor'),
('interest_rate', '0.00', 'Default interest rate for payment plans');

-- Create indexes for better performance
CREATE INDEX idx_debtors_status ON debtors(status);
CREATE INDEX idx_payments_date ON payments(payment_date);
CREATE INDEX idx_payments_status ON payments(status);
CREATE INDEX idx_notifications_user ON notifications(user_id, is_read);
CREATE INDEX idx_payment_plans_status ON payment_plans(status);

-- Create view for overdue payments
CREATE VIEW overdue_payments AS
SELECT 
    d.debtor_id,
    d.first_name,
    d.last_name,
    d.email,
    d.phone,
    p.payment_id,
    p.amount,
    p.payment_date,
    DATEDIFF(CURRENT_DATE, p.payment_date) as days_overdue
FROM debtors d
JOIN payments p ON d.debtor_id = p.debtor_id
WHERE p.status = 'pending'
AND p.payment_date < CURRENT_DATE;

-- Create view for financial summary
CREATE VIEW financial_summary AS
SELECT 
    DATE_FORMAT(payment_date, '%Y-%m') as month,
    COUNT(*) as total_payments,
    SUM(amount) as total_amount,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as successful_payments,
    SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as successful_amount
FROM payments
GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
ORDER BY month DESC;

-- Insert sample users
INSERT INTO users (username, password, email, full_name, role, created_at, last_login) VALUES
('jdoe', 'hashed_password1', 'jdoe@example.com', 'John Doe', 'admin', '2023-12-01 09:15:00', '2024-05-01 08:00:00'),
('asmith', 'hashed_password2', 'asmith@example.com', 'Alice Smith', 'user', '2024-01-10 10:30:00', '2024-05-02 09:00:00'),
('bwilson', 'hashed_password3', 'bwilson@example.com', 'Bob Wilson', 'user', '2024-02-20 11:45:00', '2024-05-03 10:00:00');

-- Insert sample debtors with more statuses and realistic data
INSERT INTO debtors (first_name, last_name, email, phone, address, city, state, postal_code, country, total_debt, status, created_at, updated_at, notes, created_by) VALUES
('Michael', 'Johnson', 'mjohnson@email.com', '+1-555-123-4567', '123 Maple St', 'New York', 'NY', '10001', 'USA', 12000.00, 'active', '2024-01-15 14:22:00', '2024-05-01 09:00:00', 'Business loan for equipment', 1),
('Sophia', 'Martinez', 'smartinez@email.com', '+1-555-234-5678', '456 Oak Ave', 'Los Angeles', 'CA', '90001', 'USA', 8500.00, 'in_dispute', '2024-02-10 10:10:00', '2024-05-02 10:00:00', 'Dispute over invoice #A123', 2),
('Liam', 'Nguyen', 'lnguyen@email.com', '+1-555-345-6789', '789 Pine Rd', 'Houston', 'TX', '77001', 'USA', 5000.00, 'under_review', '2024-03-05 16:45:00', '2024-05-03 11:00:00', 'Reviewing payment plan eligibility', 1),
('Emma', 'Brown', 'ebrown@email.com', '+1-555-456-7890', '321 Cedar Blvd', 'Chicago', 'IL', '60601', 'USA', 0.00, 'paid', '2024-01-20 12:00:00', '2024-04-15 15:00:00', 'Debt fully paid', 3);

-- Insert sample payments with more statuses, descriptions, and realistic timestamps
INSERT INTO payments (debtor_id, amount, payment_date, payment_method, status, reference_number, notes, created_at, updated_at, created_by) VALUES
(1, 2000.00, '2024-04-01', 'bank_transfer', 'completed', 'TXN1001', 'April installment for equipment loan', '2024-04-01 09:30:00', '2024-04-01 09:30:00', 1),
(1, 1000.00, '2024-05-01', 'credit_card', 'pending', 'TXN1002', 'May installment for equipment loan', '2024-05-01 09:30:00', '2024-05-01 09:30:00', 1),
(2, 8500.00, '2024-03-15', 'cash', 'in_dispute', 'TXN2001', 'Full payment for invoice #A123 (disputed)', '2024-03-15 10:00:00', '2024-05-02 10:00:00', 2),
(3, 2500.00, '2024-02-10', 'bank_transfer', 'under_review', 'TXN3001', 'First payment for review', '2024-02-10 11:00:00', '2024-05-03 11:00:00', 1),
(3, 2500.00, '2024-03-10', 'bank_transfer', 'pending', 'TXN3002', 'Second payment for review', '2024-03-10 11:00:00', '2024-05-03 11:00:00', 1),
(4, 5000.00, '2024-01-20', 'credit_card', 'completed', 'TXN4001', 'Final payment for personal loan', '2024-01-20 12:30:00', '2024-04-15 15:00:00', 3); 