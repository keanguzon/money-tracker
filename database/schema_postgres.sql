-- BukoJuice Database Schema (PostgreSQL / Supabase)
-- LemoneyD-style with Accounts/E-wallets

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    avatar VARCHAR(255) DEFAULT NULL,
    currency VARCHAR(10) DEFAULT 'PHP',
    dark_mode BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Accounts table (E-wallets, Banks, Cash, Credit Cards)
CREATE TABLE IF NOT EXISTS accounts (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(20) CHECK (type IN ('ewallet', 'bank', 'cash', 'credit_card', 'investment')) NOT NULL,
    balance DECIMAL(15, 2) DEFAULT 0.00,
    icon VARCHAR(50) DEFAULT 'wallet',
    color VARCHAR(20) DEFAULT '#6366f1',
    interest_rate DECIMAL(5, 2) DEFAULT 0.00,
    is_savings BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Categories table (simplified)
CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    type VARCHAR(20) CHECK (type IN ('income', 'expense')) NOT NULL,
    icon VARCHAR(50) DEFAULT 'wallet',
    color VARCHAR(20) DEFAULT '#6366f1',
    user_id INT DEFAULT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Transactions table (with account tracking)
CREATE TABLE IF NOT EXISTS transactions (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    account_id INT NOT NULL,
    type VARCHAR(20) CHECK (type IN ('income', 'expense', 'transfer')) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    description VARCHAR(255),
    transaction_date DATE NOT NULL,
    to_account_id INT DEFAULT NULL,
    installment_id INT DEFAULT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (to_account_id) REFERENCES accounts(id) ON DELETE SET NULL
);

-- Installment plans table
CREATE TABLE IF NOT EXISTS installments (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    total_amount DECIMAL(15, 2) NOT NULL,
    paid_amount DECIMAL(15, 2) DEFAULT 0.00,
    interest_rate DECIMAL(5, 2) DEFAULT 0.00,
    monthly_payment DECIMAL(15, 2) NOT NULL,
    remaining_months INT NOT NULL,
    account_id INT NOT NULL,
    category_id INT NOT NULL,
    next_due_date DATE NOT NULL,
    is_completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Budgets table
CREATE TABLE IF NOT EXISTS budgets (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT DEFAULT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    period VARCHAR(20) CHECK (period IN ('weekly', 'monthly', 'yearly')) DEFAULT 'monthly',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Goals table
CREATE TABLE IF NOT EXISTS goals (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    target_amount DECIMAL(15, 2) NOT NULL,
    current_amount DECIMAL(15, 2) DEFAULT 0.00,
    deadline DATE,
    icon VARCHAR(50) DEFAULT 'target',
    color VARCHAR(20) DEFAULT '#6366f1',
    description TEXT,
    is_achieved BOOLEAN DEFAULT FALSE,
    achieved_at TIMESTAMP WITH TIME ZONE DEFAULT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Recurring transactions table
CREATE TABLE IF NOT EXISTS recurring_transactions (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    type VARCHAR(20) CHECK (type IN ('income', 'expense')) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    description VARCHAR(255),
    frequency VARCHAR(20) CHECK (frequency IN ('daily', 'weekly', 'monthly', 'yearly')) NOT NULL,
    next_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_processed TIMESTAMP WITH TIME ZONE DEFAULT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Budget alerts table
CREATE TABLE IF NOT EXISTS budget_alerts (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    budget_id INT NOT NULL,
    alert_type VARCHAR(20) CHECK (alert_type IN ('warning', 'exceeded')) NOT NULL,
    threshold_percentage INT DEFAULT 80,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (budget_id) REFERENCES budgets(id) ON DELETE CASCADE
);

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(20) CHECK (type IN ('info', 'warning', 'success', 'error')) DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    link VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default categories (simplified)
INSERT INTO categories (name, type, icon, color, is_default) VALUES
-- Income categories
('Salary', 'income', 'briefcase', '#10b981', TRUE),
('Business', 'income', 'trending-up', '#06b6d4', TRUE),
('Freelance', 'income', 'code', '#8b5cf6', TRUE),
('Other Income', 'income', 'plus-circle', '#6366f1', TRUE),
-- Expense categories
('Food & Drinks', 'expense', 'utensils', '#ef4444', TRUE),
('Transportation', 'expense', 'car', '#f97316', TRUE),
('Shopping', 'expense', 'shopping-bag', '#eab308', TRUE),
('Bills & Utilities', 'expense', 'file-text', '#14b8a6', TRUE),
('Entertainment', 'expense', 'film', '#a855f7', TRUE),
('Healthcare', 'expense', 'heart', '#ec4899', TRUE),
('Education', 'expense', 'book-open', '#3b82f6', TRUE),
('Installment', 'expense', 'credit-card', '#f59e0b', TRUE),
('Other', 'expense', 'minus-circle', '#64748b', TRUE);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_transactions_user ON transactions(user_id);
CREATE INDEX IF NOT EXISTS idx_transactions_date ON transactions(transaction_date);
CREATE INDEX IF NOT EXISTS idx_transactions_type ON transactions(type);
CREATE INDEX IF NOT EXISTS idx_transactions_account ON transactions(account_id);
CREATE INDEX IF NOT EXISTS idx_categories_user ON categories(user_id);
CREATE INDEX IF NOT EXISTS idx_accounts_user ON accounts(user_id);
CREATE INDEX IF NOT EXISTS idx_installments_user ON installments(user_id);
CREATE INDEX IF NOT EXISTS idx_budgets_user ON budgets(user_id);
CREATE INDEX IF NOT EXISTS idx_budgets_dates ON budgets(start_date,A end_date);
CREATE INDEX IF NOT EXISTS idx_goals_user ON goals(user_id);
CREATE INDEX IF NOT EXISTS idx_goals_deadline ON goals(deadline);
CREATE INDEX IF NOT EXISTS idx_recurring_user ON recurring_transactions(user_id);
CREATE INDEX IF NOT EXISTS idx_recurring_next_date ON recurring_transactions(next_date, is_active);
CREATE INDEX IF NOT EXISTS idx_budget_alerts_user ON budget_alerts(user_id, is_read);
CREATE INDEX IF NOT EXISTS idx_notifications_user ON notifications(user_id, is_read);
CREATE INDEX IF NOT EXISTS idx_notifications_created ON notifications(created_at);
