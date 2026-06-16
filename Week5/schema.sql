-- ============================================================
-- UniVote - University Online Voting System
-- Database Schema & Sample Data
-- Compatible with MySQL 5.7+ / MariaDB 10.3+
-- ============================================================

CREATE DATABASE IF NOT EXISTS univote_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE univote_db;

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','student') DEFAULT 'student',
    course VARCHAR(100),
    year_of_study TINYINT,
    phone VARCHAR(20),
    profile_photo VARCHAR(255) DEFAULT 'default.png',
    is_active TINYINT(1) DEFAULT 1,
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_expires DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: elections
-- ============================================================
CREATE TABLE IF NOT EXISTS elections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    status ENUM('upcoming','active','closed') DEFAULT 'upcoming',
    created_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: positions
-- ============================================================
CREATE TABLE IF NOT EXISTS positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    election_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    max_votes INT DEFAULT 1,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: candidates
-- ============================================================
CREATE TABLE IF NOT EXISTS candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    election_id INT NOT NULL,
    position_id INT NOT NULL,
    manifesto TEXT,
    photo VARCHAR(255) DEFAULT 'default_candidate.png',
    is_approved TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
    FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_candidate_position (user_id, election_id, position_id)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: votes
-- ============================================================
CREATE TABLE IF NOT EXISTS votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voter_id INT NOT NULL,
    candidate_id INT NOT NULL,
    position_id INT NOT NULL,
    election_id INT NOT NULL,
    voted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    FOREIGN KEY (voter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE CASCADE,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
    UNIQUE KEY unique_vote (voter_id, position_id, election_id)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: results (materialized for fast reporting)
-- ============================================================
CREATE TABLE IF NOT EXISTS results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    election_id INT NOT NULL,
    position_id INT NOT NULL,
    candidate_id INT NOT NULL,
    total_votes INT DEFAULT 0,
    is_winner TINYINT(1) DEFAULT 0,
    declared_at DATETIME DEFAULT NULL,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
    FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    UNIQUE KEY unique_result (election_id, position_id, candidate_id)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: audit_log
-- ============================================================
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: announcements
-- ============================================================
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    election_id INT DEFAULT NULL,
    created_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Admin user (password: Admin@1234)
INSERT INTO users (student_id, fullname, email, password_hash, role, course, year_of_study) VALUES
('ADM001', 'System Administrator', 'admin@university.edu', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administration', 0);

-- Student voters (password: Student@1234 for all)
INSERT INTO users (student_id, fullname, email, password_hash, role, course, year_of_study) VALUES
('STU001', 'Alice Wanjiku Kamau', 'alice@student.edu', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Computer Science', 3),
('STU002', 'Brian Otieno Odhiambo', 'brian@student.edu', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Business Administration', 2),
('STU003', 'Caroline Mwangi Njoroge', 'caroline@student.edu', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Law', 4),
('STU004', 'David Kipchoge Mutai', 'david@student.edu', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Medicine', 1),
('STU005', 'Eva Achieng Omondi', 'eva@student.edu', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Engineering', 2),
('STU006', 'Felix Githinji Maina', 'felix@student.edu', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Computer Science', 4),
('STU007', 'Grace Nduta Wairimu', 'grace@student.edu', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Education', 3),
('STU008', 'Henry Mugo Kariuki', 'henry@student.edu', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Architecture', 2),
('STU009', 'Irene Chebet Koech', 'irene@student.edu', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Nursing', 1),
('STU010', 'James Ochieng Were', 'james@student.edu', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Economics', 3);

-- Active election
INSERT INTO elections (title, description, start_date, end_date, status, created_by) VALUES
('2024/2025 Student Government Elections', 
 'Annual elections for student government leadership positions. All registered students are eligible to vote.', 
 DATE_SUB(NOW(), INTERVAL 1 DAY), 
 DATE_ADD(NOW(), INTERVAL 5 DAY), 
 'active', 1);

-- Positions for election 1
INSERT INTO positions (election_id, name, description, sort_order) VALUES
(1, 'President', 'Student Government President - overall leadership', 1),
(1, 'Vice President', 'Student Government Vice President', 2),
(1, 'Secretary General', 'Manages communications and records', 3),
(1, 'Treasurer', 'Manages student government finances', 4),
(1, 'Class Representative', 'Represents student interests in academic affairs', 5);

-- Candidates (linked to student users)
INSERT INTO candidates (user_id, election_id, position_id, manifesto, photo) VALUES
(2, 1, 1, 'I will champion student welfare, improve campus facilities, and create a bridge between students and administration. Together we can build a better university experience for all.', 'default_candidate.png'),
(3, 1, 1, 'My vision is to digitize student services, establish mentorship programs, and ensure every student voice is heard at the highest levels of governance.', 'default_candidate.png'),
(4, 1, 2, 'I am committed to supporting the President and ensuring smooth day-to-day operations of student government while focusing on academic excellence programs.', 'default_candidate.png'),
(5, 1, 2, 'With a background in engineering, I bring systematic thinking to student governance. I will focus on innovation hubs and entrepreneurship support.', 'default_candidate.png'),
(6, 1, 3, 'As Secretary General, I will maintain transparent records, streamline communications, and ensure all students are informed about government activities.', 'default_candidate.png'),
(7, 1, 3, 'I will modernize our communications through a student portal, regular newsletters, and open feedback channels.', 'default_candidate.png'),
(8, 1, 4, 'Financial transparency is my top priority. I will publish quarterly reports and work to secure more bursaries and scholarships for deserving students.', 'default_candidate.png'),
(9, 1, 4, 'I will audit all student funds, negotiate better deals for campus services, and establish an emergency student welfare fund.', 'default_candidate.png'),
(10, 1, 5, 'I will be the voice of all students in academic matters, pushing for fair assessment policies and better study environments.', 'default_candidate.png'),
(2, 1, 5, 'My focus will be reducing exam fees, improving library resources, and creating peer tutoring networks.', 'default_candidate.png');

-- Sample announcement
INSERT INTO announcements (title, body, election_id, created_by) VALUES
('Voting Now Open!', 
 'The 2024/2025 Student Government Elections are now officially open. All registered students are encouraged to participate and exercise their democratic right. Voting closes in 5 days.', 
 1, 1),
('Candidate Manifesto Forum', 
 'A live candidates forum will be held this Friday at the Main Auditorium from 2PM-5PM. Come meet the candidates and ask your questions.', 
 1, 1);

-- ============================================================
-- VIEWS for reporting
-- ============================================================
CREATE OR REPLACE VIEW vote_tallies AS
SELECT 
    v.election_id,
    v.position_id,
    v.candidate_id,
    c.user_id,
    c.photo,
    u.fullname AS candidate_name,
    u.student_id,
    u.course,
    u.year_of_study,
    p.name AS position_name,
    p.sort_order,
    e.title AS election_title,
    COUNT(v.id) AS vote_count
FROM votes v
JOIN candidates c ON v.candidate_id = c.id
JOIN users u ON c.user_id = u.id
JOIN positions p ON v.position_id = p.id
JOIN elections e ON v.election_id = e.id
GROUP BY v.election_id, v.position_id, v.candidate_id, c.user_id, c.photo,
         u.fullname, u.student_id, u.course, u.year_of_study,
         p.name, p.sort_order, e.title;

CREATE OR REPLACE VIEW voter_participation AS
SELECT 
    e.id AS election_id,
    e.title,
    COUNT(DISTINCT u.id) AS total_eligible,
    COUNT(DISTINCT v.voter_id) AS total_voted,
    ROUND(COUNT(DISTINCT v.voter_id) / NULLIF(COUNT(DISTINCT u.id),0) * 100, 2) AS turnout_percentage
FROM elections e
CROSS JOIN users u
LEFT JOIN votes v ON v.election_id = e.id AND v.voter_id = u.id
WHERE u.role = 'student' AND u.is_active = 1
GROUP BY e.id;
