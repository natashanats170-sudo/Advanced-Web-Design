<?php

/**
 * register.php - Student voter registration
 * University Voting System | BIT3208
 * Author: Natasha Wanjiru Thungu | BSCCS/2024/53895
 */
session_start();
require_once 'config/database.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$errors = [];
$success = '';
$fullname = $regNumber = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── 1. Retrieve and sanitise inputs ───────────────────────────
    $fullname = trim($_POST['fullname'] ?? '');
    $regNumber = strtoupper(trim($_POST['regNumber'] ?? ''));
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirmPassword'] ?? '';

    // ── 2. Server-side validation ──────────────────────────────────
    if (strlen($fullname) < 3) {
        $errors[] = 'Full name must be at least 3 characters.';
    }
    if (strlen($regNumber) < 5) {
        $errors[] = 'Invalid registration number format (e.g. BSCCS/2024/53895).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number.';
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must contain at least one special character (!@#$%^&*).';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // ── 3. Check for duplicate email or registration number ────────
    if (empty($errors)) {
        $conn = getConnection();
        $check = $conn->prepare(
            'SELECT id FROM users WHERE email = ? OR reg_number = ? LIMIT 1'
        );
        $check->bind_param('ss', $email, $regNumber);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $errors[] = 'An account with this email or registration number already exists.';
        }
        $check->close();

        // ── 4. Hash and store the password ─────────────────────────
        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                'INSERT INTO users (fullname, reg_number, email, password) VALUES (?, ?, ?, ?)'
            );
            $stmt->bind_param('ssss', $fullname, $regNumber, $email, $hashedPassword);

            if ($stmt->execute()) {
                $success = 'Registration successful! Please log in.';
                $fullname = $regNumber = $email = '';
            } else {
                $errors[] = 'Registration failed. Please try again later.';
            }
            $stmt->close();
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - University Voting System</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="auth-container">
        <div class="auth-card">
            <div style="text-align:center;margin-bottom:20px;">
                <img src="images/mku-logo.png" alt="MKU Logo" style="height:60px;width:auto;">
                <h1 style="color:#1F4E79;margin-top:10px;">Create Your Account</h1>
                <p class="subtitle" style="color:#64748B;">University Voting System</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $e): ?>
                        <p><?= htmlspecialchars($e) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php" id="registerForm" novalidate>
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname"
                        value="<?= htmlspecialchars($fullname) ?>" required>
                    <span class="error-msg" id="fullname-error"></span>
                </div>

                <div class="form-group">
                    <label for="regNumber">Registration Number</label>
                    <input type="text" id="regNumber" name="regNumber"
                        placeholder="e.g. BSCCS/2024/53895"
                        value="<?= htmlspecialchars($regNumber) ?>" required>
                    <span class="error-msg" id="regNumber-error"></span>
                </div>

                <div class="form-group">
                    <label for="email">University Email</label>
                    <input type="email" id="email" name="email"
                        value="<?= htmlspecialchars($email) ?>" required>
                    <span class="error-msg" id="email-error"></span>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                        placeholder="Min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special" required>
                    <span class="error-msg" id="password-error"></span>
                    <small style="color:#64748B;font-size:0.75rem;display:block;margin-top:4px;">
                        Requirements: 8+ characters, uppercase, lowercase, number, special character
                    </small>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                    <span class="error-msg" id="confirmPassword-error"></span>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>

            <p class="auth-link">Already registered? <a href="index.php">Login here</a></p>
        </div>
    </div>
    <script src="js/validation.js"></script>
</body>

</html>
