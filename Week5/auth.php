<?php
// ============================================================
// api/auth.php — Login / Register / Logout / Reset
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/mailer.php';
startSecureSession();
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    // ── REGISTER ──────────────────────────────────────────────
    case 'register':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid security token.');

        $studentId = strtoupper(sanitizeInput($_POST['student_id'] ?? ''));
        $fullname  = sanitizeInput($_POST['fullname'] ?? '');
        $email     = strtolower(sanitizeInput($_POST['email'] ?? ''));
        $password  = $_POST['password'] ?? '';
        $course    = sanitizeInput($_POST['course'] ?? '');
        $year      = (int)($_POST['year_of_study'] ?? 0);

        // Validate
        if (!validateStudentId($studentId)) jsonResponse(false, 'Invalid Student ID format (letters/numbers, 3-20 chars).');
        if (strlen($fullname) < 3)          jsonResponse(false, 'Full name must be at least 3 characters.');
        if (!validateEmail($email))          jsonResponse(false, 'Invalid email address.');
        if (strlen($password) < 8)           jsonResponse(false, 'Password must be at least 8 characters.');
        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password))
            jsonResponse(false, 'Password must contain an uppercase letter and a number.');
        if ($year < 1 || $year > 8)          jsonResponse(false, 'Invalid year of study.');

        $db = getDB();
        // Check duplicates
        $stmt = $db->prepare("SELECT id FROM users WHERE student_id=? OR email=?");
        $stmt->execute([$studentId, $email]);
        if ($stmt->fetch()) jsonResponse(false, 'Student ID or email already registered.');

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $db->prepare("INSERT INTO users (student_id, fullname, email, password_hash, role, course, year_of_study) VALUES (?,?,?,?,'student',?,?)");
        $stmt->execute([$studentId, $fullname, $email, $hash, $course, $year]);

        $newId = $db->lastInsertId();
        auditLog('REGISTER', "New student registered: $studentId", $newId);
        notifyRegistration($email, $fullname, $studentId);
        jsonResponse(true, 'Registration successful! You can now log in.');

    // ── LOGIN ─────────────────────────────────────────────────
    case 'login':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid security token.');

        $studentId = strtoupper(sanitizeInput($_POST['student_id'] ?? ''));
        $password  = $_POST['password'] ?? '';

        if (empty($studentId) || empty($password)) jsonResponse(false, 'Student ID and password are required.');

        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE student_id=? AND is_active=1");
        $stmt->execute([$studentId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash']))
            jsonResponse(false, 'Invalid Student ID or password.');

        // Set session
        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['student_id'] = $user['student_id'];
        $_SESSION['fullname']   = $user['fullname'];
        $_SESSION['role']       = $user['role'];
        $_SESSION['created']    = time();

        auditLog('LOGIN', "User logged in: {$user['student_id']}", $user['id']);
        jsonResponse(true, 'Login successful.', ['role' => $user['role']]);

    // ── LOGOUT ────────────────────────────────────────────────
    case 'logout':
        if (isLoggedIn()) auditLog('LOGOUT', "User logged out: {$_SESSION['student_id']}");
        session_unset();
        session_destroy();
        jsonResponse(true, 'Logged out successfully.');

    // ── PASSWORD RESET REQUEST ────────────────────────────────
    case 'reset_request':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid security token.');
        $email = strtolower(sanitizeInput($_POST['email'] ?? ''));
        if (!validateEmail($email)) jsonResponse(false, 'Invalid email address.');

        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email=?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Always say success (prevent email enumeration)
        if ($user) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $db->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE id=?")->execute([$token, $expires, $user['id']]);
            // Fetch user name for email
            $uRow = $db->prepare("SELECT fullname FROM users WHERE id=?");
            $uRow->execute([$user['id']]);
            $uName = $uRow->fetchColumn() ?: 'Student';
            notifyPasswordReset($email, $uName, $token);
            auditLog('RESET_REQUEST', "Password reset requested for: $email | token: $token", $user['id']);
        }
        jsonResponse(true, 'If that email exists, a reset link has been sent.');

    // ── PASSWORD RESET CONFIRM ────────────────────────────────
    case 'reset_confirm':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid security token.');
        $token    = sanitizeInput($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (strlen($token) !== 64) jsonResponse(false, 'Invalid reset token.');
        if ($password !== $confirm) jsonResponse(false, 'Passwords do not match.');
        if (strlen($password) < 8) jsonResponse(false, 'Password must be at least 8 characters.');

        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE reset_token=? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        if (!$user) jsonResponse(false, 'Reset token is invalid or expired.');

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $db->prepare("UPDATE users SET password_hash=?, reset_token=NULL, reset_expires=NULL WHERE id=?")->execute([$hash, $user['id']]);
        auditLog('RESET_DONE', "Password reset completed", $user['id']);
        jsonResponse(true, 'Password updated successfully.');

    default:
        jsonResponse(false, 'Unknown action.');
}
