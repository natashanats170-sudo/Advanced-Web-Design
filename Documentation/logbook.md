# BIT3208: Advanced Web Design and Development — Logbook.

## Student Information.

| Detail | Information |
|--------|-------------|
| **Student** | Natasha Wanjiru Thungu |
| **Registration No** | BSCCS/2024/53895 |
| **Lecturer** | Mr. Nyoro |
| **Department** | Computing and Informatics |
| **Project** | University Voting System |
| **Institution** | Mount Kenya University |
| **Semester** | 2026, May/August |

---

## 📅 Weekly Logbook.

---

### Week 1 – Environment Setup and First Web Pages.
**Date:** 1st May 2026.

**What I did:**
- Downloaded and installed XAMPP 8.2.12 on Windows 11
- Started Apache and MySQL services from XAMPP Control Panel
- Created first PHP page (`hello.php`) displaying student details and server time
- Created `index.html` landing page with university branding and "Get Started" button
- Created `db_test.php` to verify MySQL connectivity
- Tested phpMyAdmin access and explored the interface
- Learned the difference between accessing files via `file://` vs `http://localhost`

**Problems Faced:**
- Port 80 was being used by Skype, preventing Apache from starting
- PHP code was displaying as plain text when opening file directly from folder
- MySQL connection failed with "Access denied" error initially

**Solutions:**
- Changed Apache port from 80 to 8080 in `httpd.conf` file
- Accessed PHP files through `http://localhost` instead of `file://` path
- Verified root password is empty in XAMPP and removed password parameter

**Weekly Reflection:**
This week I learned the fundamentals of setting up a local development environment. Understanding that PHP requires a web server to execute was a crucial concept. The XAMPP stack provides everything needed for web development. I also learned the importance of using `htmlspecialchars()` when outputting data to prevent XSS attacks. The folder structure under `htdocs` is essential for organising web projects.

**Completed Deliverables:**
- ✅ XAMPP installation and configuration
- ✅ `hello.php` with student information
- ✅ `index.html` landing page
- ✅ `db_test.php` database connection test

---

### Week 2 – Wireframes, GUI Design and Project Planning.
**Date:** 6th May 2026.

**What I did:**
- Created hand-drawn wireframes for Login, Registration, Dashboard, and Results pages
- Designed high-fidelity GUI mockups using Canva
- Finalized colour scheme: Deep blue (`#1F4E79`) with gold accents
- Created user flow diagram showing all possible navigation paths
- Developed 8-week project planning timeline with milestones
- Created component library with reusable UI elements
- Digitalized wireframes using draw.io for cleaner presentation
- Established project folder structure

**Problems Faced:**
- Created too many pages initially (6+) and got overwhelmed
- Colour contrast wasn't sufficient for accessibility standards
- Paper wireframes were messy and hard to interpret

**Solutions:**
- Focused on MVP approach: only 4 core pages essential for voting
- Used online contrast checker tool and adjusted colour shades
- Redrew wireframes digitally using draw.io for cleaner output

**Weekly Reflection:**
This week taught me the importance of planning before coding. Wireframes help identify layout issues early, saving development time. The user flow diagram revealed all decision points that need to be coded. Canva proved to be a useful tool for creating professional UI designs without extensive design skills. The MVP approach ensures we deliver essential features first before adding enhancements.

**Completed Deliverables:**
- ✅ Login page wireframe
- ✅ Registration page wireframe
- ✅ Dashboard/Voting page wireframe
- ✅ Results page wireframe
- ✅ Canva high-fidelity designs
- ✅ User flow diagram
- ✅ 8-week project timeline

---

### Week 3 – JavaScript Form Validation and PHP Syntax.
**Date:** 13th May 2026.

**What I did:**
- Created `validation.js` with complete form validation functions
- Implemented real-time validation using `addEventListener('input')`
- Wrote regex patterns for registration number validation
- Implemented password strength validation (8+ chars, uppercase, number)
- Explored PHP variables, arrays, loops, and functions
- Created `config/database.php` with database connection function
- Practiced PHP string concatenation and echo/print statements
- Tested form submission with both client and server validation

**Problems Faced:**
- JavaScript wasn't executing — no error messages appeared
- Validation triggered on every keystroke causing flickering
- PHP array syntax was confusing compared to JavaScript
- Client validation was bypassed when JavaScript was disabled

**Solutions:**
- Used `console.log()` to debug and found syntax errors
- Added debounce function to limit validation calls
- Created comparison table of PHP vs JavaScript syntax
- Implemented full server-side validation as backup

**Weekly Reflection:**
This week I learned the importance of both client-side and server-side validation. JavaScript provides instant feedback to users without page reload, improving user experience. However, relying solely on client-side validation is a security risk since users can disable JavaScript. Server-side validation is mandatory for security. The configuration file approach to database connections ensures credentials can be changed in one place.

**Completed Deliverables:**
- ✅ `validation.js` (complete client-side validation)
- ✅ `config/database.php` (database configuration)
- ✅ PHP syntax demonstration
- ✅ Server-side validation prototype

---

### Week 4 – Forms, Authentication and Sessions.
**Date:** 21st May 2026.

**What I did:**
- Built registration form with server-side validation
- Built login form with `password_hash()` and `password_verify()`
- Implemented PHP sessions — storing `user_id` and `username` after login
- Created dashboard protected by session check
- Organized project into professional folder structure with `includes/`, `css/`, `students/`
- Exported `week4db.sql` for version control
- Pushed Week 4 folder to GitHub

**Problems Faced:**
- `session_start()` needed to be called before any HTML output
- `session_regenerate_id()` was breaking the session when used incorrectly
- Users could access dashboard via URL manipulation
- Session wasn't fully clearing on logout

**Solutions:**
- Placed `session_start()` at the very top of all PHP pages
- Used `session_regenerate_id(true)` after login before any output
- Added auth guard checking if `user_id` exists in session
- Added complete session destruction with cookie removal for logout

**Weekly Reflection:**
This week I learned the critical aspects of user authentication. The POST method is essential for sensitive data like passwords. PHP sessions provide a way to maintain state across HTTP requests. The auth guard pattern must be applied to every protected page. Understanding the difference between GET and POST and when to use each method is fundamental. Security must be considered from the beginning, not added as an afterthought. Session regeneration prevents session fixation attacks.

**Completed Deliverables:**
- ✅ Registration form with validation
- ✅ Login form with `password_verify()`
- ✅ Session management
- ✅ Protected dashboard
- ✅ Professional folder structure
- ✅ GitHub push

---

### Week 5 – Database Design and CRUD Foundations.
**Date:** 27th May 2026.

**What I did:**
- Designed 3-table database schema: `users`, `candidates`, `votes`
- Created `votingdb` database with `utf8mb4_unicode_ci` collation
- Created `users` table with UNIQUE constraints on `email` and `reg_number`
- Created `candidates` table with `vote_count` column defaulting to 0
- Created `votes` table with FOREIGN KEY relationships
- Added UNIQUE constraint on `votes.user_id` to enforce one-vote-per-user
- Inserted sample data for 5 candidates
- Tested database connectivity from PHP with proper charset

**Problems Faced:**
- Initially had 4 tables but overcomplicated the design
- UNIQUE constraint failed on email column initially
- Foreign key constraint failed during votes table creation
- Data insertion failed due to description field length being too short

**Solutions:**
- Merged `user_details` into `users` table, kept only 3 tables
- Used `ALTER TABLE` to ensure column uniqueness
- Created parent tables first before child tables
- Changed `description` from `VARCHAR(255)` to `TEXT`

**Weekly Reflection:**
This week taught me the importance of proper database design. Normalization separates entities into distinct tables to avoid redundancy. Foreign key constraints enforce referential integrity. The UNIQUE constraint on `votes.user_id` ensures one-vote-per-user at the database level, providing a second layer of protection. Understanding primary and foreign key relationships is essential for relational database design. The `ON DELETE CASCADE` option automatically removes related data when a parent record is deleted.

**Completed Deliverables:**
- ✅ `votingdb` database with 3 tables
- ✅ `users` table with constraints
- ✅ `candidates` table with sample data
- ✅ `votes` table with foreign key relationships
- ✅ Database connection configuration

---

### Week 6 – Database Integration and CRUD Operations.
**Date:** 3rd June 2026.

**What I did:**
- Implemented `register.php` with database insertion (CREATE operation)
- Implemented `dashboard.php` with dynamic candidate display (READ operation)
- Built `vote_process.php` with database transaction (UPDATE operation)
- Created `results.php` with percentage bars for real-time results
- Added `has_voted` check to hide vote buttons after voting
- Implemented flash messages for vote success/error status
- Added JavaScript confirm dialog before vote submission
- Optimized database queries with indexing on foreign keys

**Problems Faced:**
- Password hashing was inconsistent when storing in database
- Query wasn't ordering candidates properly
- Transaction wasn't rolling back on error
- Percentage calculation caused division by zero error
- Flash messages disappeared after page refresh

**Solutions:**
- Used `PASSWORD_DEFAULT` constant with `password_hash()`
- Added `ORDER BY position, name ASC` to query
- Added `try-catch` block with rollback in catch
- Added check `if totalVotes > 0` before division
- Used URL parameters for message passing

**Weekly Reflection:**
This week was the most challenging and rewarding. Database transactions ensure that vote operations either fully complete or fully revert, preventing data inconsistency. Prepared statements with `bind_param()` prevent SQL injection attacks. The double-check on `has_voted` (both in PHP and at the database level) ensures the one-vote rule even under concurrent requests. The CRUD pattern maps directly to `INSERT`, `SELECT`, `UPDATE`, and `DELETE` SQL commands. Understanding how to implement transactions is crucial for data integrity.

**Completed Deliverables:**
- ✅ `register.php` with database insertion
- ✅ Dashboard with dynamic candidate display
- ✅ Vote processing with transaction
- ✅ Results page with percentage bars
- ✅ Success/error flash messages
- ✅ Complete voting flow

---

### Week 7 – Authentication and Session Management.
**Date:** 10th June 2026.

**What I did:**
- Implemented `password_hash()` using bcrypt on registration
- Implemented `password_verify()` for secure login verification
- Added `session_regenerate_id(true)` after login to prevent session fixation
- Created complete logout with session destruction and cookie deletion
- Added auth guards to all protected pages (dashboard, results)
- Implemented session timeout checking with timestamp
- Stored minimal session data: `user_id`, `user_name`, `has_voted`
- Documented all security measures

**Problems Faced:**
- Hashed passwords were too long for `VARCHAR(64)` column
- Verification failed even with correct passwords
- Session persisted after logout in some browsers
- Some pages were missing auth guards

**Solutions:**
- Changed password column to `VARCHAR(255)`
- Stopped trimming passwords in sanitization
- Added both `session_destroy()` and cookie deletion
- Created template and applied to all pages systematically

**Weekly Reflection:**
This week focused on security. `password_hash()` uses bcrypt by default and applies a random salt automatically. `password_verify()` compares plaintext input to the stored hash safely. `session_regenerate_id(true)` must be called after login to prevent session fixation attacks. The auth guard pattern must be applied to every protected page before any HTML output. Generic error messages prevent attackers from determining valid email addresses. Security is not a single feature but a collection of practices applied consistently.

**Completed Deliverables:**
- ✅ Secure registration with password hashing
- ✅ Secure login with password verification
- ✅ Session regeneration on login
- ✅ Auth guards on all protected pages
- ✅ Complete secure logout
- ✅ Session timeout implementation

---

### Week 8 – Responsive Design and Final Integration.
**Date:** 17th June 2026.

**What I did:**
- Added viewport meta tag to all pages
- Implemented mobile-first CSS approach
- Created responsive navigation bar with flexbox
- Implemented CSS Grid for candidate cards
- Added media queries for tablet (768px) and desktop (1024px)
- Tested on multiple devices and browsers
- Created `.gitignore` for sensitive files
- Pushed final code to GitHub repository
- Updated `README.md` with complete documentation
- Final testing of all features

**Problems Faced:**
- Some pages were missing the viewport meta tag
- Desktop design suffered from mobile-first approach initially
- CSS Grid didn't work in older browsers
- Database credentials were being pushed to GitHub
- Merge conflicts in `README.md` when pushing to GitHub

**Solutions:**
- Created template and applied meta tag to all pages
- Added media queries for larger screens with `min-width`
- Added flexbox fallback for older browsers
- Added `config/database.php` to `.gitignore`
- Resolved conflicts using `git mergetool`

**Weekly Reflection:**
This week brought all the pieces together. The viewport meta tag is essential for responsive design — without it, mobile browsers render pages at 980px and scale down. Mobile-first CSS writes base styles for small screens, then uses `min-width` media queries to enhance layouts for larger screens. CSS Grid makes multi-column card layouts responsive and trivial to implement. A `.gitignore` file prevents sensitive credentials from being pushed to a public repository. The completed system integrates all 8 weeks of learning into a working, secure, and responsive application.

**Completed Deliverables:**
- ✅ `responsive.css` with media queries
- ✅ Mobile-first CSS approach
- ✅ CSS Grid layout for candidate cards
- ✅ `.gitignore` for sensitive files
- ✅ Complete GitHub repository
- ✅ Comprehensive `README.md`
- ✅ Final testing and bug fixes
- ✅ Complete project submission

---

### Skills Developed.

**Technical Skills:**
- PHP 8.x programming and server-side logic
- MySQL database design and query optimization
- HTML5, CSS3, and responsive web design
- JavaScript validation and DOM manipulation
- Security best practices (hashing, encryption, XSS prevention)

**Tools:**
- XAMPP (Apache, MySQL, PHP)
- phpMyAdmin for database administration
- Git and GitHub for version control
- VS Code development environment
- Chrome DevTools for debugging

**Soft Skills:**
- Project planning and time management
- Problem-solving and debugging
- Documentation and reporting
- Self-directed learning

---

### Key Lessons Learned.

1. **Start Early** — Complex projects take time. Starting early allows for unexpected challenges.
2. **Test Incrementally** — Test each component before moving to the next. This catches bugs early.
3. **Security First** — Implement security from the beginning, not as an afterthought.
4. **Document Continuously** — Documentation should be done as you go, not at the end.
5. **Never Trust User Input** — Always validate, sanitize, and use prepared statements.
6. **Mobile-First Design** — Design for the smallest screen first, then enhance for larger screens.
7. **Use Version Control** — Git saves you from many mistakes and tracks your progress.
8. **Backup Regularly** — Regular backups prevent data loss.
