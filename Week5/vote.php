<?php
// ============================================================
// api/vote.php — Cast Vote & Get Voting Data
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/mailer.php';
startSecureSession();
header('Content-Type: application/json');

requireLogin();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$db = getDB();

switch ($action) {
    // ── CAST VOTE ─────────────────────────────────────────────
    case 'cast':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid security token.');

        $candidateId = (int)($_POST['candidate_id'] ?? 0);
        $positionId  = (int)($_POST['position_id'] ?? 0);
        $electionId  = (int)($_POST['election_id'] ?? 0);
        $voterId     = (int)$_SESSION['user_id'];

        if (!$candidateId || !$positionId || !$electionId)
            jsonResponse(false, 'Invalid vote data.');

        // Verify election is active
        $stmt = $db->prepare("SELECT id FROM elections WHERE id=? AND status='active' AND NOW() BETWEEN start_date AND end_date");
        $stmt->execute([$electionId]);
        if (!$stmt->fetch()) jsonResponse(false, 'This election is not currently active.');

        // Verify voter hasn't already voted for this position
        if (hasVotedForPosition($voterId, $positionId, $electionId))
            jsonResponse(false, 'You have already voted for this position.');

        // Verify candidate exists and belongs to this position/election
        $stmt = $db->prepare("SELECT id FROM candidates WHERE id=? AND position_id=? AND election_id=? AND is_approved=1");
        $stmt->execute([$candidateId, $positionId, $electionId]);
        if (!$stmt->fetch()) jsonResponse(false, 'Invalid candidate selection.');

        // Verify voter is a student
        $stmt = $db->prepare("SELECT role FROM users WHERE id=? AND is_active=1");
        $stmt->execute([$voterId]);
        $voter = $stmt->fetch();
        if (!$voter || $voter['role'] !== 'student')
            jsonResponse(false, 'Only registered students may vote.');

        // Cast the vote (UNIQUE constraint prevents duplicates at DB level too)
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        try {
            $stmt = $db->prepare("INSERT INTO votes (voter_id, candidate_id, position_id, election_id, ip_address) VALUES (?,?,?,?,?)");
            $stmt->execute([$voterId, $candidateId, $positionId, $electionId, $ip]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) jsonResponse(false, 'Duplicate vote detected.');
            throw $e;
        }

        auditLog('VOTE_CAST', "Vote cast: voter=$voterId, candidate=$candidateId, position=$positionId, election=$electionId");

        // Send confirmation email (non-blocking — failure doesn't affect vote)
        $vStmt = $db->prepare("SELECT u.email, u.fullname, e.title, p.name AS pos_name
            FROM users u, elections e, positions p
            WHERE u.id=? AND e.id=? AND p.id=?");
        $vStmt->execute([$voterId, $electionId, $positionId]);
        if ($vRow = $vStmt->fetch()) {
            notifyVoteCast($vRow['email'], $vRow['fullname'], $vRow['title'], $vRow['pos_name']);
        }
        jsonResponse(true, 'Vote cast successfully!');

    // ── GET CANDIDATES FOR A POSITION ────────────────────────
    case 'candidates':
        $electionId = (int)($_GET['election_id'] ?? 0);
        $positionId = (int)($_GET['position_id'] ?? 0);
        if (!$electionId) jsonResponse(false, 'Election ID required.');

        $sql = "SELECT c.id, c.manifesto, c.photo, u.fullname, u.student_id, u.course, u.year_of_study, p.name AS position_name, p.id AS position_id
                FROM candidates c
                JOIN users u ON c.user_id = u.id
                JOIN positions p ON c.position_id = p.id
                WHERE c.election_id=? AND c.is_approved=1";
        $params = [$electionId];
        if ($positionId) { $sql .= " AND c.position_id=?"; $params[] = $positionId; }
        $sql .= " ORDER BY p.sort_order, u.fullname";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $candidates = $stmt->fetchAll();

        // Add photo URLs
        foreach ($candidates as &$c) {
            $file = UPLOAD_PATH . $c['photo'];
            $c['photo_url'] = file_exists($file) ? UPLOAD_URL . $c['photo'] : APP_URL . '/public/images/default_candidate.png';
        }
        jsonResponse(true, '', ['candidates' => $candidates]);

    // ── GET POSITIONS WITH VOTE STATUS ────────────────────────
    case 'positions':
        $electionId = (int)($_GET['election_id'] ?? 0);
        if (!$electionId) jsonResponse(false, 'Election ID required.');

        $stmt = $db->prepare("SELECT * FROM positions WHERE election_id=? ORDER BY sort_order");
        $stmt->execute([$electionId]);
        $positions = $stmt->fetchAll();

        $voted = getVotedPositions((int)$_SESSION['user_id'], $electionId);
        foreach ($positions as &$p) {
            $p['voted'] = in_array($p['id'], $voted);
        }
        jsonResponse(true, '', ['positions' => $positions]);

    // ── LIVE RESULTS (read-only, public after election) ───────
    case 'results':
        $electionId = (int)($_GET['election_id'] ?? 0);
        if (!$electionId) jsonResponse(false, 'Election ID required.');

        // Only show results if election is closed or user is admin
        $stmt = $db->prepare("SELECT status FROM elections WHERE id=?");
        $stmt->execute([$electionId]);
        $election = $stmt->fetch();
        if (!$election) jsonResponse(false, 'Election not found.');
        if ($election['status'] !== 'closed' && !isAdmin())
            jsonResponse(false, 'Results are not yet available.');

        $stmt = $db->prepare("SELECT * FROM vote_tallies WHERE election_id=? ORDER BY position_id, vote_count DESC");
        $stmt->execute([$electionId]);
        $results = $stmt->fetchAll();

        // Enrich with photo URLs and participation stats
        $totalVoters = $db->query("SELECT COUNT(*) FROM users WHERE role='student' AND is_active=1")->fetchColumn();
        $totalVoted  = $db->prepare("SELECT COUNT(DISTINCT voter_id) FROM votes WHERE election_id=?");
        $totalVoted->execute([$electionId]);
        $totalVoted  = $totalVoted->fetchColumn();

        foreach ($results as &$row) {
            $photoFile = UPLOAD_PATH . ($row['photo'] ?? '');
            $row['photo_url']     = (isset($row['photo']) && file_exists($photoFile))
                ? UPLOAD_URL . $row['photo']
                : APP_URL . '/public/images/default_candidate.png';
            $row['total_voters']  = $totalVoters;
            $row['total_voted']   = $totalVoted;
        }
        jsonResponse(true, '', ['results' => $results]);

    default:
        jsonResponse(false, 'Unknown action.');
}
