<?php

/**
 * logout.php - Secure session destruction and logout
 * University Voting System | BIT3208
 */
session_start();

// ── Step 1: Clear all session variables from memory ──────────────
$_SESSION = [];

// ── Step 2: Delete the session cookie from the browser ───────────
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), // "PHPSESSID"
        '', // Empty value
        time() - 42000, // Expiry in the past - forces deletion
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// ── Step 3: Destroy the session data on the server ───────────────
session_destroy();

// ── Step 4: Redirect to login page with confirmation message ─────
header('Location: index.php?logout=1');
exit();
