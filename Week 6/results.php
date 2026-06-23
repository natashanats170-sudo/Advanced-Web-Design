<?php

/**
 * results.php - Public results page
 * Shows election results with percentage bars
 */
session_start();
require_once 'config/database.php';

$conn = getConnection();

// Fetch candidates ordered by votes (highest first)
$stmt = $conn->prepare('SELECT * FROM candidates ORDER BY vote_count DESC');
$stmt->execute();
$candidates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Total votes
$totalResult = $conn->query('SELECT SUM(vote_count) AS total FROM candidates');
$totalVotes = (int) ($totalResult->fetch_assoc()['total'] ?? 0);
$conn->close();

// Get user status if logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? htmlspecialchars($_SESSION['user_name']) : '';
$hasVoted = $isLoggedIn ? (int) ($_SESSION['has_voted'] ?? 0) : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results - University Voting System</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <nav class="navbar">
        <div class="navbar-inner">
            <div class="navbar-brand">
                <img src="images/mku-logo.png" alt="MKU" style="height:40px;width:auto;margin-right:12px;">
                <div class="navbar-title"><strong>MKU Voting System</strong></div>
            </div>
            <div class="navbar-user">
                <?php if ($isLoggedIn): ?>
                    <span>Welcome, <?= $userName ?></span>
                    <a href="dashboard.php" class="btn btn-sm" style="background:#FDB813;color:#1F4E79;">Back to Vote</a>
                    <a href="logout.php" class="btn btn-sm" style="background:#dc3545;color:#fff;">Logout</a>
                <?php else: ?>
                    <a href="index.php" class="btn btn-sm" style="background:#FDB813;color:#1F4E79;">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="navbar-gold-bar"></div>

    <div class="main-content">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <h2 style="color:#1F4E79;">Election Results</h2>
            <p style="color:#64748B;">Total votes cast: <strong><?= $totalVotes ?></strong></p>
        </div>

        <?php if ($isLoggedIn && $hasVoted): ?>
            <div class="alert alert-success">✅ Thank you for participating in the elections!</div>
        <?php endif; ?>

        <?php foreach ($candidates as $c):
            $pct = $totalVotes > 0 ? round(($c['vote_count'] / $totalVotes) * 100, 1) : 0;
        ?>
            <div style="background:#fff;padding:16px 20px;border-radius:8px;border:1px solid #CBD5E1;margin-bottom:12px;">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;">
                    <div>
                        <strong style="color:#1F4E79;"><?= htmlspecialchars($c['name']) ?></strong>
                        <span style="color:#64748B;font-size:.85rem;margin-left:8px;"><?= htmlspecialchars($c['position']) ?></span>
                    </div>
                    <div style="font-weight:600;"><?= $c['vote_count'] ?> votes (<?= $pct ?>%)</div>
                </div>
                <div class="result-bar-track" style="margin-top:8px;">
                    <div class="result-bar-fill" style="width: <?= $pct ?>%"></div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($candidates)): ?>
            <div style="text-align:center;padding:40px;background:#fff;border-radius:12px;border:1px solid #CBD5E1;">
                <p style="color:#64748B;">No candidates have been added yet.</p>
            </div>
        <?php endif; ?>
    </div>
    <script src="js/app.js"></script>
</body>

</html>
