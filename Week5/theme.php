<?php
// ============================================================
// api/theme.php — Server-side dark mode preference (no browser storage)
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
startSecureSession();
$theme = in_array($_GET['theme'] ?? '', ['dark','light']) ? $_GET['theme'] : 'light';
$_SESSION['theme'] = $theme;
header('Content-Type: application/json');
echo json_encode(['ok' => true]);
