CREATE DATABASE IF NOT EXISTS votingdb
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE votingdb;

-- ── Users table (registered voters) ─────────────────────────────
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    reg_number VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    has_voted TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── Candidates table ─────────────────────────────────────────────
CREATE TABLE candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    description TEXT,
    photo VARCHAR(255) DEFAULT 'default.jpg',
    vote_count INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── Votes table (audit trail) ────────────────────────────────────
CREATE TABLE votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    candidate_id INT NOT NULL,
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    UNIQUE KEY unique_vote (user_id)
);

-- ── Insert sample candidates ─────────────────────────────────────
INSERT INTO candidates (name, position, description) VALUES
('Alice Mwangi', 'Guild President', 'Championing student welfare and academic excellence.'),
('Brian Otieno', 'Guild President', 'Promoting inclusivity and transparent governance.'),
('Carol Njeri', 'Vice President', 'Focused on mental health and student support services.'),
('David Kamau', 'Vice President', 'Driving innovation in campus activities.'),
('Esther Wahu', 'Secretary General', 'Committed to efficient student communication.');
