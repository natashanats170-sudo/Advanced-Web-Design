# University Voting System - BIT3208.

## Advanced Web Design and Development.

### Student Information.

* **Student Name:** Natasha Wanjiru Thungu.
* **Registration Number:** BSCCS/2024/53895.
* **Unit Code:** BIT3208
* **Course:** Advanced Web Design and Development.
* **Lecturer:** Mr. Nyoro.
* **Date:** June 2026

---

# University Voting System.

A secure, web-based election platform developed using PHP, MySQL, HTML, CSS, and JavaScript. The system enables university students to register, authenticate, cast votes securely, and view election results in real time.

## 1) Project Overview.

The University Voting System was developed as part of the BIT3208 Advanced Web Design and Development coursework. The application demonstrates full-stack web development concepts including:

* User authentication and authorization
* Secure password hashing with bcrypt
* Database integration using MySQL
* Responsive web design
* Session management
* Vote integrity enforcement
* Client-side and server-side validation

## 2) Features.

### 2.1) Authentication.

* Student registration
* Secure login system
* Password hashing using bcrypt
* Session management
* Logout functionality

### 2.2) Voting System.

* Candidate listing and profiles
* One-vote-per-user enforcement
* Vote processing with database transactions
* Real-time vote counting
* Results visualization

### 2.3) Security Features.

* Password hashing (`password_hash`)
* Password verification (`password_verify`)
* SQL injection prevention using prepared statements
* XSS prevention using `htmlspecialchars`
* Session fixation protection
* Input validation and sanitization

### 2.4) User Experience.

* Responsive design
* Mobile-friendly interface
* Real-time form validation
* Flash messages and feedback
* Intuitive navigation

## 3) Technology Stack.

| Technology   | Purpose                 |
| ------------ | ----------------------- |
| PHP 8.x      | Backend Development     |
| MySQL        | Database Management     |
| HTML5        | Page Structure          |
| CSS3         | Styling and Layout      |
| JavaScript   | Client-Side Validation  |
| XAMPP        | Development Environment |
| phpMyAdmin   | Database Administration |
| Git & GitHub | Version Control         |
| Canva        | GUI Design              |

## 4) Project Structure.

```text
votingsystem/
│
├── admin/
├── config/
│   └── database.php
├── css/
│   ├── style.css
│   └── responsive.css
├── images/
├── js/
│   └── app.js
├── process/
│   └── vote_process.php
├── uploads/
├── dashboard.php
├── db_test.php
├── hello.php
├── index.html
├── index.php
├── logout.php
├── register.php
├── results.php
├── test_hash.php
```

## 5) Database Schema.

### Users Table.

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    reg_number VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    has_voted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Candidates Table.

```sql
CREATE TABLE candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    description TEXT,
    photo VARCHAR(255) DEFAULT 'default.jpg',
    vote_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Votes Table.

```sql
CREATE TABLE votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    candidate_id INT NOT NULL,
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    UNIQUE KEY unique_vote (user_id)
);
```

## 6) Security Implementation.

### 6.1) Password Hashing.

```php
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
```

### 6.2) Password Verification.

```php
password_verify($password, $user['password']);
```

### 6.3) Prepared Statements.

```php
$stmt = $conn->prepare(
    "SELECT * FROM users WHERE email = ?"
);
```

### 6.4) XSS Prevention.

```php
echo htmlspecialchars($userName);
```

## 7) Weekly Progress Timeline.

| Week       | Title                           | Key Deliverables                                                                                                                            |
| ---------- | ------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------- |
| **Week 1** | Environment Setup               | XAMPP installation, Hello World (`hello.php`), Database connection test (`db_test.php`), Landing page (`index.html`)                        |
| **Week 2** | Wireframes & GUI Design         | Page wireframes, Canva mockups, User flow diagram, Project planning timeline                                                                |
| **Week 3** | JavaScript & PHP Basics         | Form validation (`validation.js`), PHP syntax examples, Database configuration (`config/database.php`)                                      |
| **Week 4** | Server-Side Components          | Login form (`index.php`), Prototype authentication, Session management, Dashboard template                                                  |
| **Week 5** | Database Design                 | `votingdb` schema, `users`, `candidates`, and `votes` tables, Sample data, Foreign key relationships                                        |
| **Week 6** | Database Integration & CRUD     | User registration (`register.php`), Candidate display (`dashboard.php`), Vote processing (`vote_process.php`), Results page (`results.php`) |
| **Week 7** | Authentication & Security       | Password hashing (`test_hash.php`), Secure login, Authentication guards, Logout (`logout.php`), Session management                          |
| **Week 8** | Responsive Design & Integration | `responsive.css`, Media queries, Final testing, GitHub repository setup, Complete system integration                                        |

---

## 8) Challenges Faced and Solutions.

### Challenge 1: Database Connection Issues.

**Problem:**
Initial connection attempts to MySQL failed due to incorrect credentials or inactive services.

**Solution:**

* Verified that Apache and MySQL services were running in XAMPP.
* Confirmed that the default XAMPP root user has no password.
* Created `db_test.php` to isolate and troubleshoot connection issues.
* Implemented proper error handling using `error_log()`.

---

### Challenge 2: Password Hashing and Verification.

**Problem:**
Understanding bcrypt hashing and the correct password verification workflow.

**Solution:**

* Created `test_hash.php` to experiment with `password_hash()` and `password_verify()`.
* Learned that bcrypt hashes are salted automatically and produce unique hashes for the same password.
* Stored password hashes in a `VARCHAR(255)` column to accommodate future algorithm upgrades.
* Documented the authentication workflow for future maintenance.

---

### Challenge 3: One-Vote-Per-User Enforcement.

**Problem:**
Preventing duplicate votes and maintaining vote integrity.

**Solution:**

* Implemented application-level validation using the `has_voted` field.
* Added a database-level `UNIQUE` constraint on `votes.user_id`.
* Used database transactions to ensure atomic vote processing.
* Combined both checks to provide reliable vote protection.

---

### Challenge 4: Session Management.

**Problem:**
Users could access protected pages such as the dashboard without logging in.

**Solution:**
Implemented an authentication guard at the top of every protected page:

```php
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}
```

Additional measures included:

* Using `session_start()` on all protected pages.
* Regenerating session IDs after successful login with `session_regenerate_id(true)`.
* Destroying all session data during logout.
* Restricting access to voting and results pages to authenticated users only.

## 9) Learning Outcomes.

This project demonstrates:

* PHP programming
* Database integration
* Authentication systems
* Web security principles
* Responsive design
* CRUD operations
* Version control with Git
* Full-stack application development

## 10) References.

1. [PHP Documentation](https://www.php.net/manual/en/)
2. [Mozilla Developer Network (MDN)](https://www.apachefriends.org/)
3. [XAMPP Documentation](https://www.w3schools.com/)
4. [OWASP Top 10](https://dev.mysql.com/doc/)
5. GitHub Documentation
