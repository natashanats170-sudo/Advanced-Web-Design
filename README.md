# BIT3208: Advanced Web Design and Development.

## Student Information.

| Detail | Information |
|--------|-------------|
| **Name** | Natasha Wanjiru Thungu |
| **Registration** | BSCCS/2024/53895 |
| **Course** | Advanced Web Design and Development |
| **Unit Code** | BIT3208 |
| **Lecturer** | Mr. Nyoro |
| **Department** | Computing and Informatics |
| **Institution** | Mount Kenya University |
| **Semester** | 2026, May/August |

---

## Project Overview.

This repository contains the complete **University Voting System** project developed for **BIT3208: Advanced Web Design and Development**. The system is a secure, web-based platform enabling registered students to vote in university elections.

### Project Objectives.

- Design and implement a secure web-based voting system
- Apply full-stack web development principles (PHP, MySQL, HTML, CSS, JavaScript)
- Implement robust authentication and authorization mechanisms
- Ensure one-vote-per-user enforcement
- Create a responsive interface for all devices
- Follow security best practices (OWASP Top 10)

---

## 🗳️ System Features.

| Feature | Description |
|---------|-------------|
| **Secure Authentication** | bcrypt password hashing with `password_hash()` |
| **One-Vote-Per-User** | Enforced at database level with UNIQUE constraint |
| **Real-Time Results** | Live percentage bars with animated transitions |
| **Responsive Design** | Mobile-first with 4 breakpoint levels |
| **SQL Injection Protection** | Prepared statements with `bind_param()` |
| **XSS Prevention** | `htmlspecialchars()` on all user output |
| **Session Security** | `session_regenerate_id(true)` on login |
| **Professional UI** | MKU branding with consistent color scheme |

---

## Technologies Used.

| Category | Technology | Purpose |
|----------|------------|---------|
| **Backend** | PHP 8.x | Server-side logic & processing |
| **Database** | MySQL | Data storage & retrieval |
| **Frontend** | HTML5 | Page structure |
| **Styling** | CSS3 | Visual design & responsiveness |
| **Interactivity** | JavaScript | Client-side validation & animations |
| **Server** | Apache (XAMPP) | Local development environment |
| **Version Control** | Git & GitHub | Code management & collaboration |


## Key Learnings.

### Technical Skills
- ✅ Full-stack web development (PHP + MySQL)
- ✅ Secure authentication (bcrypt hashing)
- ✅ Session management (regeneration, guards)
- ✅ Responsive design (mobile-first, media queries)
- ✅ CRUD operations (Create, Read, Update, Delete)
- ✅ Database transactions (ACID compliance)

### Security Skills
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS prevention (htmlspecialchars)
- ✅ Session fixation prevention (regeneration)
- ✅ Password security (bcrypt hashing)
- ✅ One-vote enforcement (database constraints)

### Soft Skills
- ✅ Project planning and execution
- ✅ Documentation best practices
- ✅ Version control (Git/GitHub)
- ✅ Testing and debugging
- ✅ Report writing

---

## References.

- **PHP Manual**: https://www.php.net/manual/en/
- **MySQL Documentation**: https://dev.mysql.com/doc/
- **MDN Web Docs**: https://developer.mozilla.org/
- **XAMPP Documentation**: https://www.apachefriends.org/
- **OWASP Top 10**: https://owasp.org/Top10/
- **W3Schools**: https://www.w3schools.com/
- **Duckett, J. (2011)**. HTML & CSS: Design and Build Web Sites. John Wiley & Sons.
- **Nixon, R. (2021)**. Learning PHP, MySQL & JavaScript (6th ed.). O'Reilly Media.

## Academic Integrity.
All practical work was conducted in a controlled lab environment with proper authorization.
