<?php
// ============================================================
// api/admin.php — Admin CRUD Operations
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/mailer.php';
startSecureSession();
header('Content-Type: application/json');

requireAdmin();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$db = getDB();

switch ($action) {
    // ══ ELECTIONS ═══════════════════════════════════════════

    case 'get_elections':
        $stmt = $db->query("SELECT e.*, u.fullname AS created_by_name, 
            (SELECT COUNT(*) FROM candidates WHERE election_id=e.id) AS candidate_count,
            (SELECT COUNT(*) FROM positions WHERE election_id=e.id) AS position_count,
            (SELECT COUNT(DISTINCT voter_id) FROM votes WHERE election_id=e.id) AS vote_count
            FROM elections e LEFT JOIN users u ON e.created_by=u.id ORDER BY e.created_at DESC");
        jsonResponse(true, '', ['elections' => $stmt->fetchAll()]);

    case 'save_election':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
        $id    = (int)($_POST['id'] ?? 0);
        $title = sanitizeInput($_POST['title'] ?? '');
        $desc  = sanitizeInput($_POST['description'] ?? '');
        $start = sanitizeInput($_POST['start_date'] ?? '');
        $end   = sanitizeInput($_POST['end_date'] ?? '');
        $status= sanitizeInput($_POST['status'] ?? 'upcoming');
        if (strlen($title) < 5) jsonResponse(false, 'Title must be at least 5 chars.');
        if (strtotime($start) >= strtotime($end)) jsonResponse(false, 'End date must be after start date.');

        if ($id) {
            $db->prepare("UPDATE elections SET title=?,description=?,start_date=?,end_date=?,status=? WHERE id=?")->execute([$title,$desc,$start,$end,$status,$id]);
            auditLog('ELECTION_UPDATE', "Election updated: $title");
        } else {
            $db->prepare("INSERT INTO elections (title,description,start_date,end_date,status,created_by) VALUES (?,?,?,?,?,?)")->execute([$title,$desc,$start,$end,$status,$_SESSION['user_id']]);
            $id = $db->lastInsertId();
            // Auto-create standard positions
            $positions = ['President','Vice President','Secretary General','Treasurer','Class Representative'];
            $sort = 1;
            foreach ($positions as $pos) {
                $db->prepare("INSERT INTO positions (election_id,name,sort_order) VALUES (?,?,?)")->execute([$id,$pos,$sort++]);
            }
            auditLog('ELECTION_CREATE', "Election created: $title");
        }
        jsonResponse(true, 'Election saved successfully.');

    case 'delete_election':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM elections WHERE id=?")->execute([$id]);
        auditLog('ELECTION_DELETE', "Election deleted: ID=$id");
        jsonResponse(true, 'Election deleted.');

    case 'toggle_election_status':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
        $id     = (int)($_POST['id'] ?? 0);
        $status = sanitizeInput($_POST['status'] ?? '');
        if (!in_array($status, ['upcoming','active','closed'])) jsonResponse(false, 'Invalid status.');
        $db->prepare("UPDATE elections SET status=? WHERE id=?")->execute([$status, $id]);
        auditLog('ELECTION_STATUS', "Election #$id status changed to: $status");
        jsonResponse(true, "Election status updated to $status.");

    // ══ POSITIONS ════════════════════════════════════════════

    case 'get_positions':
        $electionId = (int)($_GET['election_id'] ?? 0);
        $stmt = $db->prepare("SELECT p.*, (SELECT COUNT(*) FROM candidates WHERE position_id=p.id) AS candidate_count FROM positions p WHERE p.election_id=? ORDER BY sort_order");
        $stmt->execute([$electionId]);
        jsonResponse(true, '', ['positions' => $stmt->fetchAll()]);

    case 'save_position':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
        $id         = (int)($_POST['id'] ?? 0);
        $electionId = (int)($_POST['election_id'] ?? 0);
        $name       = sanitizeInput($_POST['name'] ?? '');
        $desc       = sanitizeInput($_POST['description'] ?? '');
        $sort       = (int)($_POST['sort_order'] ?? 0);
        if (strlen($name) < 2) jsonResponse(false, 'Position name required.');
        if ($id) {
            $db->prepare("UPDATE positions SET name=?,description=?,sort_order=? WHERE id=?")->execute([$name,$desc,$sort,$id]);
        } else {
            $db->prepare("INSERT INTO positions (election_id,name,description,sort_order) VALUES (?,?,?,?)")->execute([$electionId,$name,$desc,$sort]);
        }
        jsonResponse(true, 'Position saved.');

    case 'delete_position':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM positions WHERE id=?")->execute([$id]);
        jsonResponse(true, 'Position deleted.');

    // ══ CANDIDATES ═══════════════════════════════════════════

    case 'get_candidates':
        $electionId = (int)($_GET['election_id'] ?? 0);
        $sql = "SELECT c.id, c.manifesto, c.photo, c.is_approved, c.created_at,
                    u.fullname, u.student_id, u.course, u.year_of_study, u.email,
                    p.name AS position_name, p.id AS position_id, e.title AS election_title
                FROM candidates c
                JOIN users u ON c.user_id=u.id
                JOIN positions p ON c.position_id=p.id
                JOIN elections e ON c.election_id=e.id";
        if ($electionId) { $sql .= " WHERE c.election_id=?"; }
        $sql .= " ORDER BY p.sort_order, u.fullname";
        $stmt = $db->prepare($sql);
        if ($electionId) $stmt->execute([$electionId]); else $stmt->execute();
        $candidates = $stmt->fetchAll();
        foreach ($candidates as &$c) {
            $file = UPLOAD_PATH . $c['photo'];
            $c['photo_url'] = file_exists($file) ? UPLOAD_URL . $c['photo'] : APP_URL . '/public/images/default_candidate.png';
        }
        jsonResponse(true, '', ['candidates' => $candidates]);

    case 'save_candidate':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
        $id         = (int)($_POST['id'] ?? 0);
        $studentId  = strtoupper(sanitizeInput($_POST['student_id'] ?? ''));
        $positionId = (int)($_POST['position_id'] ?? 0);
        $electionId = (int)($_POST['election_id'] ?? 0);
        $manifesto  = sanitizeInput($_POST['manifesto'] ?? '');
        $approved   = (int)($_POST['is_approved'] ?? 1);

        // Find the user by student ID
        $stmt = $db->prepare("SELECT id FROM users WHERE student_id=?");
        $stmt->execute([$studentId]);
        $user = $stmt->fetch();
        if (!$user) jsonResponse(false, 'Student ID not found in the system.');

        // Handle photo upload
        $photo = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photo = uploadPhoto($_FILES['photo'], 'cand');
            if (!$photo) jsonResponse(false, 'Photo upload failed. Max 2MB, JPEG/PNG/WEBP only.');
        }

        if ($id) {
            $sql = "UPDATE candidates SET user_id=?,position_id=?,election_id=?,manifesto=?,is_approved=?";
            $params = [$user['id'],$positionId,$electionId,$manifesto,$approved];
            if ($photo) { $sql .= ",photo=?"; $params[] = $photo; }
            $sql .= " WHERE id=?"; $params[] = $id;
            $db->prepare($sql)->execute($params);
        } else {
            // Check not already a candidate for this position
            $stmt = $db->prepare("SELECT id FROM candidates WHERE user_id=? AND position_id=? AND election_id=?");
            $stmt->execute([$user['id'],$positionId,$electionId]);
            if ($stmt->fetch()) jsonResponse(false, 'This student is already a candidate for this position.');
            $photo = $photo ?: 'default_candidate.png';
            $db->prepare("INSERT INTO candidates (user_id,position_id,election_id,manifesto,photo,is_approved) VALUES (?,?,?,?,?,?)")->execute([$user['id'],$positionId,$electionId,$manifesto,$photo,$approved]);
        }
        auditLog('CANDIDATE_SAVE', "Candidate saved: $studentId for position $positionId");
        jsonResponse(true, 'Candidate saved successfully.');

    case 'delete_candidate':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM candidates WHERE id=?")->execute([$id]);
        auditLog('CANDIDATE_DELETE', "Candidate deleted: ID=$id");
        jsonResponse(true, 'Candidate deleted.');

    // ══ VOTERS ═══════════════════════════════════════════════

    case 'get_voters':
        $stmt = $db->query("SELECT id, student_id, fullname, email, course, year_of_study, is_active, created_at FROM users WHERE role='student' ORDER BY fullname");
        jsonResponse(true, '', ['voters' => $stmt->fetchAll()]);

    case 'save_voter':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
        $id        = (int)($_POST['id'] ?? 0);
        $studentId = strtoupper(sanitizeInput($_POST['student_id'] ?? ''));
        $fullname  = sanitizeInput($_POST['fullname'] ?? '');
        $email     = strtolower(sanitizeInput($_POST['email'] ?? ''));
        $course    = sanitizeInput($_POST['course'] ?? '');
        $year      = (int)($_POST['year_of_study'] ?? 1);
        $active    = (int)($_POST['is_active'] ?? 1);

        if (!validateStudentId($studentId)) jsonResponse(false, 'Invalid Student ID.');
        if (!validateEmail($email))         jsonResponse(false, 'Invalid email.');

        if ($id) {
            $db->prepare("UPDATE users SET student_id=?,fullname=?,email=?,course=?,year_of_study=?,is_active=? WHERE id=? AND role='student'")->execute([$studentId,$fullname,$email,$course,$year,$active,$id]);
        } else {
            $password = 'Student@1234'; // Default; student must reset
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $db->prepare("INSERT INTO users (student_id,fullname,email,password_hash,role,course,year_of_study) VALUES (?,?,?,?,'student',?,?)")->execute([$studentId,$fullname,$email,$hash,$course,$year]);
        }
        auditLog('VOTER_SAVE', "Voter saved: $studentId");
        jsonResponse(true, 'Voter saved. Default password is Student@1234 (if new).');

    case 'delete_voter':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM users WHERE id=? AND role='student'")->execute([$id]);
        auditLog('VOTER_DELETE', "Voter deleted: ID=$id");
        jsonResponse(true, 'Voter deleted.');

    case 'toggle_voter':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("UPDATE users SET is_active = !is_active WHERE id=?")->execute([$id]);
        jsonResponse(true, 'Voter status toggled.');

    // ══ RESULTS / DECLARE ════════════════════════════════════

    case 'declare_winners':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
        $electionId = (int)($_POST['election_id'] ?? 0);
        // Close election first
        $db->prepare("UPDATE elections SET status='closed' WHERE id=?")->execute([$electionId]);
        // Delete old results
        $db->prepare("DELETE FROM results WHERE election_id=?")->execute([$electionId]);
        // Insert results from votes
        $db->prepare("INSERT INTO results (election_id,position_id,candidate_id,total_votes)
            SELECT election_id, position_id, candidate_id, COUNT(*) FROM votes WHERE election_id=? GROUP BY position_id, candidate_id")->execute([$electionId]);
        // Mark winners (highest vote per position)
        $db->prepare("UPDATE results r1
            JOIN (SELECT position_id, MAX(total_votes) AS max_v FROM results WHERE election_id=? GROUP BY position_id) r2
            ON r1.position_id=r2.position_id AND r1.total_votes=r2.max_v
            SET r1.is_winner=1, r1.declared_at=NOW()
            WHERE r1.election_id=?")->execute([$electionId,$electionId]);
        auditLog('DECLARE_WINNERS', "Winners declared for election #$electionId");
        // Notify all active students by email
        $elTitle = $db->prepare("SELECT title FROM elections WHERE id=?");
        $elTitle->execute([$electionId]);
        $eName = $elTitle->fetchColumn();
        $allStudents = $db->query("SELECT email, fullname FROM users WHERE role='student' AND is_active=1");
        foreach ($allStudents->fetchAll() as $s) {
            notifyWinnersDeclared($s['email'], $s['fullname'], $eName);
        }
        jsonResponse(true, 'Winners declared, election closed, and notifications sent.');

    case 'get_results':
        $electionId = (int)($_GET['election_id'] ?? 0);
        $stmt = $db->prepare("SELECT r.*, u.fullname, u.student_id, u.course, p.name AS position_name, c.photo,
            (SELECT COUNT(DISTINCT voter_id) FROM votes WHERE election_id=r.election_id) AS total_voted,
            (SELECT COUNT(*) FROM users WHERE role='student' AND is_active=1) AS total_voters
            FROM results r JOIN candidates c ON r.candidate_id=c.id JOIN users u ON c.user_id=u.id JOIN positions p ON r.position_id=p.id
            WHERE r.election_id=? ORDER BY p.sort_order, r.total_votes DESC");
        $stmt->execute([$electionId]);
        $results = $stmt->fetchAll();
        foreach ($results as &$r) {
            $file = UPLOAD_PATH . $r['photo'];
            $r['photo_url'] = file_exists($file) ? UPLOAD_URL . $r['photo'] : APP_URL . '/public/images/default_candidate.png';
        }
        jsonResponse(true, '', ['results' => $results]);

    case 'get_live_votes':
        $electionId = (int)($_GET['election_id'] ?? 0);
        if (!$electionId) jsonResponse(false, 'Election ID required.');
        $stmt = $db->prepare("SELECT * FROM vote_tallies WHERE election_id=? ORDER BY position_id, vote_count DESC");
        $stmt->execute([$electionId]);
        $tallies = $stmt->fetchAll();
        $stats = getElectionStats($electionId);
        jsonResponse(true, '', ['tallies' => $tallies, 'stats' => $stats]);

    case 'get_stats':
        $electionId = (int)($_GET['election_id'] ?? 0);
        if ($electionId) {
            $stats = getElectionStats($electionId);
        } else {
            $stmt = $db->query("SELECT 
                (SELECT COUNT(*) FROM users WHERE role='student') AS total_students,
                (SELECT COUNT(*) FROM elections) AS total_elections,
                (SELECT COUNT(*) FROM votes) AS total_votes,
                (SELECT COUNT(*) FROM candidates) AS total_candidates");
            $stats = $stmt->fetch();
        }
        jsonResponse(true, '', ['stats' => $stats]);

    case 'get_audit_log':
        $stmt = $db->query("SELECT l.*, u.fullname, u.student_id FROM audit_log l LEFT JOIN users u ON l.user_id=u.id ORDER BY l.created_at DESC LIMIT 200");
        jsonResponse(true, '', ['logs' => $stmt->fetchAll()]);

    case 'get_announcements':
        $stmt = $db->query("SELECT a.*, u.fullname AS author, e.title AS election_title FROM announcements a LEFT JOIN users u ON a.created_by=u.id LEFT JOIN elections e ON a.election_id=e.id ORDER BY a.created_at DESC");
        jsonResponse(true, '', ['announcements' => $stmt->fetchAll()]);

    case 'save_announcement':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
        $title = sanitizeInput($_POST['title'] ?? '');
        $body  = sanitizeInput($_POST['body'] ?? '');
        $elId  = ($_POST['election_id'] ?? '') ? (int)$_POST['election_id'] : null;
        if (strlen($title) < 3) jsonResponse(false, 'Title required.');
        $db->prepare("INSERT INTO announcements (title,body,election_id,created_by) VALUES (?,?,?,?)")->execute([$title,$body,$elId,$_SESSION['user_id']]);
        jsonResponse(true, 'Announcement published.');

    case 'delete_announcement':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM announcements WHERE id=?")->execute([$id]);
        jsonResponse(true, 'Announcement deleted.');

    case 'reset_voter_password':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
        $id = (int)($_POST['id'] ?? 0);
        $hash = password_hash('Student@1234', PASSWORD_BCRYPT, ['cost' => 12]);
        $db->prepare("UPDATE users SET password_hash=? WHERE id=? AND role='student'")->execute([$hash,$id]);
        jsonResponse(true, 'Password reset to Student@1234.');

    default:
        jsonResponse(false, 'Unknown action.');
}
