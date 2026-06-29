-- Database schema for SIPERPUS AI (initial)
-- Create database manually: CREATE DATABASE perpus_ai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE perpus_ai;

-- Table: roles
CREATE TABLE IF NOT EXISTS roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table: users (administrators and staff)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(191) NOT NULL,
  email VARCHAR(191) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role_id INT NOT NULL,
  phone VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Table: members (siswa)
CREATE TABLE IF NOT EXISTS members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nisn VARCHAR(50) UNIQUE,
  full_name VARCHAR(191) NOT NULL,
  email VARCHAR(191),
  phone VARCHAR(50),
  address TEXT,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table: categories
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(191) NOT NULL,
  description TEXT
) ENGINE=InnoDB;

-- Table: racks
CREATE TABLE IF NOT EXISTS racks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) NOT NULL,
  name VARCHAR(191) NOT NULL
) ENGINE=InnoDB;

-- Table: publishers
CREATE TABLE IF NOT EXISTS publishers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(191) NOT NULL
) ENGINE=InnoDB;

-- Table: authors
CREATE TABLE IF NOT EXISTS authors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(191) NOT NULL
) ENGINE=InnoDB;

-- Table: languages
CREATE TABLE IF NOT EXISTS languages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(10) NOT NULL,
  name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- Book master
CREATE TABLE IF NOT EXISTS books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  isbn VARCHAR(50) UNIQUE,
  title VARCHAR(255) NOT NULL,
  category_id INT,
  rack_id INT,
  publisher_id INT,
  language_id INT,
  published_year YEAR,
  pages INT DEFAULT 0,
  stock INT DEFAULT 0,
  cover VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  FOREIGN KEY (rack_id) REFERENCES racks(id) ON DELETE SET NULL,
  FOREIGN KEY (publisher_id) REFERENCES publishers(id) ON DELETE SET NULL,
  FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Book authors (many-to-many)
CREATE TABLE IF NOT EXISTS book_authors (
  book_id INT,
  author_id INT,
  PRIMARY KEY (book_id, author_id),
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
  FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Copies / exemplars
CREATE TABLE IF NOT EXISTS copies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  book_id INT NOT NULL,
  barcode VARCHAR(100) UNIQUE,
  status ENUM('available','loaned','reserved','lost') DEFAULT 'available',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Loans
CREATE TABLE IF NOT EXISTS loans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  copy_id INT NOT NULL,
  member_id INT NOT NULL,
  loaned_by INT NOT NULL,
  loan_date DATE NOT NULL,
  due_date DATE NOT NULL,
  returned_date DATE,
  fine_amount DECIMAL(10,2) DEFAULT 0,
  status ENUM('ongoing','returned','overdue') DEFAULT 'ongoing',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (copy_id) REFERENCES copies(id) ON DELETE RESTRICT,
  FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE RESTRICT,
  FOREIGN KEY (loaned_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Reservations
CREATE TABLE IF NOT EXISTS reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  book_id INT NOT NULL,
  member_id INT NOT NULL,
  reserved_by INT NOT NULL,
  reserved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('waiting','collected','cancelled') DEFAULT 'waiting',
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
  FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
  FOREIGN KEY (reserved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Fines table
CREATE TABLE IF NOT EXISTS fines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  loan_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  paid BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Notifications
CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  member_id INT,
  title VARCHAR(255),
  body TEXT,
  is_read BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Activity log
CREATE TABLE IF NOT EXISTS activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  action VARCHAR(255),
  context TEXT,
  ip VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- AI related tables: nlp_dataset, chatbot_dataset, stopwords, synonyms, ai_index
CREATE TABLE IF NOT EXISTS nlp_dataset (
  id INT AUTO_INCREMENT PRIMARY KEY,
  text TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS chatbot_dataset (
  id INT AUTO_INCREMENT PRIMARY KEY,
  intent VARCHAR(150),
  sample TEXT,
  response TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS stopwords (
  id INT AUTO_INCREMENT PRIMARY KEY,
  word VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS synonyms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  word VARCHAR(100) NOT NULL,
  synonym VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ai_index (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dataset_id INT,
  vector TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Views for dashboard
CREATE OR REPLACE VIEW view_dashboard_stats AS
SELECT
  (SELECT COUNT(*) FROM books) AS total_books,
  (SELECT COUNT(*) FROM members) AS total_members,
  (SELECT COUNT(*) FROM loans WHERE status='ongoing') AS ongoing_loans
;

-- Popular books view (simple by total loans)
CREATE OR REPLACE VIEW view_popular_books AS
SELECT b.id, b.title, COUNT(l.id) AS borrow_count
FROM books b
LEFT JOIN copies c ON c.book_id = b.id
LEFT JOIN loans l ON l.copy_id = c.id
GROUP BY b.id, b.title
ORDER BY borrow_count DESC
LIMIT 10;

-- Sample trigger: calculate fine when a loan is updated with returned_date
DELIMITER $$
CREATE TRIGGER trg_loans_after_update
AFTER UPDATE ON loans
FOR EACH ROW
BEGIN
  IF NEW.returned_date IS NOT NULL AND OLD.returned_date IS NULL THEN
    DECLARE days_overdue INT DEFAULT 0;
    SET days_overdue = DATEDIFF(NEW.returned_date, NEW.due_date);
    IF days_overdue > 0 THEN
      INSERT INTO fines (loan_id, amount, created_at) VALUES (NEW.id, days_overdue * 1000, NOW());
      UPDATE loans SET fine_amount = days_overdue * 1000 WHERE id = NEW.id;
    END IF;
  END IF;
END$$
DELIMITER ;

-- Stored procedure: sp_mark_return (marks loan as returned and sets returned_date)
DELIMITER $$
CREATE PROCEDURE sp_mark_return(IN p_loan_id INT)
BEGIN
  UPDATE loans SET returned_date = CURDATE(), status = 'returned' WHERE id = p_loan_id;
  -- The trigger will compute fines
END$$
DELIMITER ;

-- Basic seed placeholders (minimal) - heavy seeding is done via PHP seeder script
INSERT INTO languages (code, name) VALUES ('id', 'Indonesia'), ('en','English');

-- Create minimal admin role and user placeholder (password must be hashed by seeder)
INSERT IGNORE INTO roles (id, name, description) VALUES (1, 'Administrator', 'Admin'), (2, 'Petugas', 'Staff'), (3, 'Siswa', 'Student');

-- Create an admin user row with placeholder password (replace via seeder)
INSERT IGNORE INTO users (id, full_name, email, password, role_id, created_at) VALUES (1, 'Administrator', 'admin@sekolah.local', '$2y$10$exampleplaceholderhash................', 1, NOW());

-- End of schema
