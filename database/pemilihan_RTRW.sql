-- Database schema for Pemilihan RT/RW
-- Import this file into MySQL/MariaDB, e.g.:
-- mysql -u root -p < pemilihan_RTRW.sql

CREATE DATABASE IF NOT EXISTS pemilihan_rtrw CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE pemilihan_rtrw;

-- Table: candidates
CREATE TABLE IF NOT EXISTS candidates (
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(255) NOT NULL,
	description TEXT,
	password VARCHAR(255) NOT NULL,
	photo_url VARCHAR(255),
	is_active TINYINT(1) DEFAULT 1,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: voters (identified by NIK)
CREATE TABLE IF NOT EXISTS voters (
	id INT AUTO_INCREMENT PRIMARY KEY,
	nik VARCHAR(64) NOT NULL UNIQUE,
	name VARCHAR(255),
	rt VARCHAR(10),
	rw VARCHAR(10),
	password VARCHAR(255) NOT NULL,
	has_voted TINYINT(1) DEFAULT 0,
	voted_at DATETIME NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: votes
CREATE TABLE IF NOT EXISTS votes (
	id INT AUTO_INCREMENT PRIMARY KEY,
	voter_id INT NOT NULL,
	candidate_id INT NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (voter_id) REFERENCES voters(id) ON DELETE CASCADE,
	FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE
);

-- Admin table
CREATE TABLE IF NOT EXISTS admins (
	id INT AUTO_INCREMENT PRIMARY KEY,
	username VARCHAR(100) NOT NULL UNIQUE,
	password VARCHAR(255) NOT NULL,
	email VARCHAR(100),
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample data: admin (password: admin123)
INSERT INTO admins (username, password, email) VALUES
('admin', MD5('admin123'), 'admin@rt-rw.local');

-- Sample data: candidates (password: candidate123)
INSERT INTO candidates (name, description, password, is_active) VALUES
('Calon Ketua RT 001', 'Calon dari RW 01 - A', MD5('candidate123'), 1),
('Calon Ketua RT 002', 'Calon dari RW 01 - B', MD5('candidate123'), 1),
('Calon Ketua RT 003', 'Calon dari RW 02 - A', MD5('candidate123'), 1);

