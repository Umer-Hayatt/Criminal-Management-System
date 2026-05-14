-- ============================================================
--   CRMS FULL UPGRADE — Database Migration
-- ============================================================

USE criminal_record_db;

-- Add photo columns to Criminal and Officer
ALTER TABLE Criminal ADD COLUMN photo VARCHAR(255) NULL;
ALTER TABLE Officer ADD COLUMN photo VARCHAR(255) NULL;

-- Add extra columns to Imprisonment
-- sentence_years already exists, add months and release_date
ALTER TABLE Imprisonment ADD COLUMN sentence_months INT NULL;
ALTER TABLE Imprisonment ADD COLUMN release_date DATE NULL;

-- Users table for authentication
CREATE TABLE IF NOT EXISTS users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(60) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','officer','viewer') DEFAULT 'viewer',
  officer_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (officer_id) REFERENCES Officer(officer_id) ON DELETE SET NULL
);

-- Suspect table
CREATE TABLE IF NOT EXISTS Suspect (
  suspect_id   INT AUTO_INCREMENT PRIMARY KEY,
  first_name   VARCHAR(60),
  last_name    VARCHAR(60),
  phone        VARCHAR(20),
  address      TEXT,
  note         TEXT,
  case_id      INT,
  FOREIGN KEY (case_id) REFERENCES Case_Record(case_id) ON DELETE CASCADE
);

-- Warrant table
CREATE TABLE IF NOT EXISTS Warrant (
  warrant_id   INT AUTO_INCREMENT PRIMARY KEY,
  case_id      INT,
  criminal_id  INT,
  type         ENUM('Arrest','Search','Other') DEFAULT 'Arrest',
  issued_date  DATE,
  expiry_date  DATE,
  status       ENUM('Active','Expired','Executed') DEFAULT 'Active',
  notes        TEXT,
  FOREIGN KEY (case_id)     REFERENCES Case_Record(case_id),
  FOREIGN KEY (criminal_id) REFERENCES Criminal(criminal_id)
);

-- Evidence table
CREATE TABLE IF NOT EXISTS Evidence (
  evidence_id  INT AUTO_INCREMENT PRIMARY KEY,
  case_id      INT,
  label        VARCHAR(120),
  file_path    VARCHAR(255),
  file_type    VARCHAR(30),
  uploaded_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  uploaded_by  INT,
  FOREIGN KEY (case_id)      REFERENCES Case_Record(case_id) ON DELETE CASCADE,
  FOREIGN KEY (uploaded_by)  REFERENCES users(user_id)
);

-- Activity Log table
CREATE TABLE IF NOT EXISTS Activity_Log (
  log_id      INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT,
  action      VARCHAR(120),
  entity_type VARCHAR(40),
  entity_id   INT NULL,
  detail      TEXT NULL,
  ip_address  VARCHAR(45),
  logged_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Seed default admin user (password: admin123)
INSERT INTO users (username, password_hash, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Seed officer user linked to officer 1
INSERT INTO users (username, password_hash, role, officer_id) VALUES
('kamran', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'officer', 1);

-- Seed viewer user
INSERT INTO users (username, password_hash, role) VALUES
('viewer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'viewer');
