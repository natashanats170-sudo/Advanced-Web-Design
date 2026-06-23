<?php

/**
 * index.php - Secure login with password_verify()
 * University Voting System | BIT3208
 */
session_start();
require_once 'config/database.php';

// Redirect authenticated users straight to the dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    // Basic input validation
    if (empty($email) || empty($password)) {
        $error = 'Both email and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $conn = getConnection();

        // Fetch user by email using a prepared statement
        $stmt = $conn->prepare(
            'SELECT id, fullname, password, has_voted FROM users WHERE email = ? LIMIT 1'
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();

        // password_verify() compares entered password with bcrypt hash
        if ($user && password_verify($password, $user['password'])) {

            // Regenerate session ID to prevent session fixation attacks
            session_regenerate_id(true);

            // Store minimal data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['fullname'];
            $_SESSION['has_voted'] = (int) $user['has_voted'];

            header('Location: dashboard.php');
            exit();
        } else {
            // Generic error - do not reveal which field was wrong
            $error = 'Invalid email or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - University Voting System</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="auth-container">
        <div class="auth-card">
            <div style="text-align:center;margin-bottom:20px;">
                <img src="images/mku-logo.png" alt="MKU Logo" style="height:70px;width:auto;">
                <h1 style="color:#1F4E79;margin-top:10px;">MKU Voting System</h1>
                <p style="color:#64748B;">BIT3208 | Natasha Wanjiru Thungu</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (isset($_GET['logout'])): ?>
                <div class="alert alert-success">You have been logged out successfully.</div>
            <?php endif; ?>

            <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success">✅ Account created successfully! Please login.</div>
            <?php endif; ?>

            <form method="POST" action="index.php">
                <div class="form-group">
                    <label for="email">University Email</label>
                    <input type="email" id="email" name="email"
                        value="<?= htmlspecialchars($email) ?>"
                        placeholder="your.email@mku.ac.ke" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                        placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>

            <p class="auth-link">New student? <a href="register.php">Register here</a></p>
        </div>
    </div>
</body>

</html>
