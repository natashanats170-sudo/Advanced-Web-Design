<?php

/**
 * process/vote_process.php - Handle vote submission (UPDATE operation)
 * University Voting System | BIT3208
 */
session_start();
require_once '../config/database.php';

// Auth guard
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$userId = (int) $_SESSION['user_id'];
$candidateId = (int) ($_POST['candidate_id'] ?? 0);

if ($candidateId <= 0) {
    header('Location: ../dashboard.php?error=invalid');
    exit();
}

$conn = getConnection();

// Double-check: has user already voted? (DB-level protection)
$check = $conn->prepare('SELECT id FROM votes WHERE user_id = ?');
$check->bind_param('i', $userId);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    header('Location: ../dashboard.php?error=already_voted');
    exit();
}
$check->close();

// Use a transaction to ensure both updates succeed or both fail
$conn->begin_transaction();

try {
    // 1. INSERT vote record
    $v = $conn->prepare('INSERT INTO votes (user_id, candidate_id) VALUES (?, ?)');
    $v->bind_param('ii', $userId, $candidateId);
    $v->execute();
    $v->close();

    // 2. UPDATE candidate vote count
    $u = $conn->prepare('UPDATE candidates SET vote_count = vote_count + 1 WHERE id = ?');
    $u->bind_param('i', $candidateId);
    $u->execute();
    $u->close();

    // 3. UPDATE user has_voted flag
    $h = $conn->prepare('UPDATE users SET has_voted = 1 WHERE id = ?');
    $h->bind_param('i', $userId);
    $h->execute();
    $h->close();

    $conn->commit();
    $_SESSION['has_voted'] = 1;
    header('Location: ../dashboard.php?success=voted');
    exit();
} catch (Exception $e) {
    $conn->rollback();
    header('Location: ../dashboard.php?error=vote_failed');
    exit();
} finally {
    $conn->close();
}
