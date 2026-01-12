CREATE DATABASE IF NOT EXISTS election;
USE election;

-- Faculties
CREATE TABLE faculties (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) UNIQUE NOT NULL
);

-- Roles
CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name ENUM('ADMIN','CANDIDATE','VOTER','OBSERVER') UNIQUE NOT NULL
);

-- Users
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role_id INT NOT NULL,
  faculty_id INT NULL,
  active BOOLEAN DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(role_id) REFERENCES roles(id) ON DELETE RESTRICT,
  FOREIGN KEY(faculty_id) REFERENCES faculties(id) ON DELETE SET NULL
);

-- Elections
CREATE TABLE elections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  scope ENUM('GENERAL','FACULTY') DEFAULT 'GENERAL',
  faculty_id INT NULL,
  status ENUM('DRAFT','ACTIVE','CLOSED') DEFAULT 'DRAFT',
  start_at DATETIME NULL,
  end_at DATETIME NULL,
  FOREIGN KEY(faculty_id) REFERENCES faculties(id)
);

-- Positions
CREATE TABLE positions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  election_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  max_votes INT DEFAULT 1,
  allows_blank BOOLEAN DEFAULT TRUE,
  FOREIGN KEY(election_id) REFERENCES elections(id)
);

-- Candidates
CREATE TABLE candidates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  position_id INT NOT NULL,
  manifesto TEXT,
  photo_url VARCHAR(255),
  FOREIGN KEY(user_id) REFERENCES users(id),
  FOREIGN KEY(position_id) REFERENCES positions(id),
  UNIQUE(user_id, position_id)
);

-- Votes
CREATE TABLE votes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  voter_id INT NOT NULL,
  position_id INT NOT NULL,
  candidate_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(voter_id) REFERENCES users(id),
  FOREIGN KEY(position_id) REFERENCES positions(id),
  FOREIGN KEY(candidate_id) REFERENCES candidates(id),
  UNIQUE(voter_id, position_id)
);

-- Audit Logs
CREATE TABLE audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  action VARCHAR(255),
  metadata JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed roles
INSERT INTO roles (name) VALUES ('ADMIN'),('CANDIDATE'),('VOTER'),('OBSERVER');
