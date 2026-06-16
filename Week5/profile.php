<?php
// ============================================================
// api/profile.php — Student Profile Management
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
startSecureSession();
header('Content-Type: application/json');
requireLogin();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$db = getDB();
$userId = (int)$_SESSION['user_id'];

switch ($action) {
    case 'update':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
        $fullname = sanitizeInput($_POST['fullname'] ?? '');
        $email    = strtolower(sanitizeInput($_POST['email'] ?? ''));
        $phone    = sanitizeInput($_POST['phone'] ?? '');

        if (strlen($fullname) < 3) jsonResponse(false, 'Full name too short.');
        if (!validateEmail($email)) jsonResponse(false, 'Invalid email.');

        // Check email uniqueness
        $stmt = $db->prepare("SELECT id FROM users WHERE email=? AND id!=?");
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) jsonResponse(false, 'Email already in use.');

        // Handle photo upload
        $photo = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photo = uploadPhoto($_FILES['photo'], 'profile');
            if (!$photo) jsonResponse(false, 'Photo upload failed.');
        }

        if ($photo) {
            $db->prepare("UPDATE users SET fullname=?,email=?,phone=?,profile_photo=? WHERE id=?")->execute([$fullname,$email,$phone,$photo,$userId]);
        } else {
            $db->prepare("UPDATE users SET fullname=?,email=?,phone=? WHERE id=?")->execute([$fullname,$email,$phone,$userId]);
        }
        $_SESSION['fullname'] = $fullname;
        auditLog('PROFILE_UPDATE', "Profile updated for user #$userId");
        jsonResponse(true, 'Profile updated successfully.');

    case 'change_password':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($new !== $confirm) jsonResponse(false, 'New passwords do not match.');
        if (strlen($new) < 8)  jsonResponse(false, 'Password must be at least 8 characters.');

        $stmt = $db->prepare("SELECT password_hash FROM users WHERE id=?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if (!password_verify($current, $user['password_hash']))
            jsonResponse(false, 'Current password is incorrect.');

        $hash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
        $db->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$hash, $userId]);
        auditLog('PASSWORD_CHANGE', "Password changed for user #$userId");
        jsonResponse(true, 'Password changed successfully.');

    case 'get':
        $stmt = $db->prepare("SELECT id, student_id, fullname, email, phone, course, year_of_study, profile_photo, role, created_at FROM users WHERE id=?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        $user['photo_url'] = APP_URL . '/public/images/default_avatar.png'; // fallback
        jsonResponse(true, '', ['user' => $user]);

    default:
        jsonResponse(false, 'Unknown action.');
}
