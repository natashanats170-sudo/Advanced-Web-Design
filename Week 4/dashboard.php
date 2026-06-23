<?php

/**
 * dashboard.php - Protected voting dashboard
 * Shows candidates and voting interface for authenticated users
 */
session_start();
require_once 'config/database.php';

// ── Auth Guard ────────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?error=auth');
    exit();
}

$userId = (int) $_SESSION['user_id'];
$userName = htmlspecialchars($_SESSION['user_name']);
$hasVoted = (int) ($_SESSION['has_voted'] ?? 0);

// ── Fetch Candidates ──────────────────────────────────────────────
$conn = getConnection();
$stmt = $conn->prepare('SELECT * FROM candidates ORDER BY position, name ASC');
$stmt->execute();
$candidates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── Total Votes for Percentage Bars ──────────────────────────────
$totalResult = $conn->query('SELECT SUM(vote_count) AS total FROM candidates');
$totalVotes = (int) ($totalResult->fetch_assoc()['total'] ?? 0);

// ── Handle flash messages ─────────────────────────────────────────
$flashMsg = '';
$flashType = '';

if (isset($_GET['success']) && $_GET['success'] === 'voted') {
    $flashMsg = 'Your vote has been recorded successfully. Thank you!';
    $flashType = 'success';
}

if (isset($_GET['error'])) {
    $msg = match ($_GET['error']) {
        'already_voted' => 'You have already cast your vote.',
        'invalid' => 'Invalid candidate selected.',
        'vote_failed' => 'Vote submission failed. Please try again.',
        'auth' => 'Please log in to access the voting system.',
        default => 'An unexpected error occurred.',
    };
    $flashMsg = $msg;
    $flashType = 'error';
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote - University Voting System</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="navbar-inner">
            <div class="navbar-brand">
                <img src="images/mku-logo.png" alt="MKU" style="height:40px;width:auto;margin-right:12px;">
                <div class="navbar-title"><strong>MKU Voting System</strong></div>
            </div>
            <div class="navbar-user">
                <span>Welcome, <?= $userName ?>!</span>
                <?php if ($hasVoted): ?>
                    <a href="results.php" class="btn btn-sm" style="background:#FDB813;color:#1F4E79;">View Results</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-sm" style="background:#dc3545;color:#fff;">Logout</a>
            </div>
        </div>
    </nav>
    <div class="navbar-gold-bar"></div>

    <main class="main-content">
        <?php if ($flashMsg): ?>
            <div class="alert alert-<?= $flashType ?>">
                <?= htmlspecialchars($flashMsg) ?>
            </div>
        <?php endif; ?>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <h2 style="color:#1F4E79;">
                <?= $hasVoted ? 'Election Results' : 'Cast Your Vote' ?>
            </h2>
            <?php if ($hasVoted): ?>
                <span class="alert" style="background:#DCFCE7;color:#166534;border-left:4px solid #16a34a;padding:8px 16px;">
                    ✅ You have voted
                </span>
            <?php endif; ?>
        </div>

        <div class="candidate-grid">
            <?php foreach ($candidates as $c):
                $pct = $totalVotes > 0 ? round(($c['vote_count'] / $totalVotes) * 100, 1) : 0;
            ?>
                <div class="candidate-card">
                    <div class="candidate-photo">
                        <div class="candidate-initials">
                            <?= strtoupper(substr($c['name'], 0, 2)) ?>
                        </div>
                        <div class="candidate-position-badge"><?= htmlspecialchars($c['position']) ?></div>
                    </div>
                    <div class="candidate-body">
                        <div class="candidate-name"><?= htmlspecialchars($c['name']) ?></div>
                        <p class="candidate-bio"><?= htmlspecialchars($c['description']) ?></p>

                        <?php if ($hasVoted): ?>
                            <!-- Show results bar if user has voted -->
                            <div class="result-bar-track">
                                <div class="result-bar-fill" style="width: <?= $pct ?>%"></div>
                            </div>
                            <small><?= $c['vote_count'] ?> votes (<?= $pct ?>%)</small>
                        <?php else: ?>
                            <!-- Show vote button if user has not voted -->
                            <form method="POST" action="process/vote_process.php">
                                <input type="hidden" name="candidate_id" value="<?= (int) $c['id'] ?>">
                                <button type="submit" class="btn btn-primary btn-block"
                                    onclick="return confirm('Confirm vote for <?= htmlspecialchars($c['name']) ?>? This cannot be undone.')">
                                    Vote
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($hasVoted): ?>
            <div style="text-align:center;margin-top:32px;padding:20px;background:#fff;border-radius:12px;border:1px solid #CBD5E1;">
                <p style="color:#64748B;">Thank you for participating in the MKU elections!</p>
            </div>
        <?php endif; ?>
    </main>
    <script src="js/app.js"></script>
</body>

</html>
