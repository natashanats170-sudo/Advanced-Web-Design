<?php
// ============================================================
// api/export.php — PDF & Excel Export
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
startSecureSession();
requireAdmin();

$type       = sanitizeInput($_GET['type'] ?? 'pdf');
$electionId = (int)($_GET['election_id'] ?? 0);
if (!$electionId) die('Election ID required.');

$db = getDB();

// Fetch election
$stmt = $db->prepare("SELECT * FROM elections WHERE id=?");
$stmt->execute([$electionId]);
$election = $stmt->fetch();
if (!$election) die('Election not found.');

// Fetch results
$stmt = $db->prepare("
    SELECT r.*, u.fullname, u.student_id, u.course, u.year_of_study,
           p.name AS position_name, p.sort_order,
           (SELECT COUNT(DISTINCT voter_id) FROM votes WHERE election_id=r.election_id) AS total_voted,
           (SELECT COUNT(*) FROM users WHERE role='student' AND is_active=1) AS total_voters
    FROM results r
    JOIN candidates c ON r.candidate_id=c.id
    JOIN users u ON c.user_id=u.id
    JOIN positions p ON r.position_id=p.id
    WHERE r.election_id=?
    ORDER BY p.sort_order, r.total_votes DESC
");
$stmt->execute([$electionId]);
$results = $stmt->fetchAll();

// Group by position
$byPosition = [];
foreach ($results as $row) {
    $byPosition[$row['position_id']] = $byPosition[$row['position_id']] ?? ['name'=>$row['position_name'], 'rows'=>[]];
    $byPosition[$row['position_id']]['rows'][] = $row;
}

$totalVoters = $results[0]['total_voters'] ?? 0;
$totalVoted  = $results[0]['total_voted']  ?? 0;
$turnout     = $totalVoters > 0 ? round($totalVoted/$totalVoters*100,1) : 0;

// ── PDF Export ────────────────────────────────────────────────
if ($type === 'pdf') {
    header('Content-Type: text/html; charset=utf-8');
    $generatedAt = date('F j, Y \a\t g:i A');
    echo <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Election Results — {$election['title']}</title>
<style>
  * { box-sizing:border-box; margin:0; padding:0; }
  body { font-family: Arial, sans-serif; color: #1a1a2e; font-size: 11pt; }
  .header { background: linear-gradient(135deg, #1d4ed8, #1e40af); color: white; padding: 24px 32px; }
  .header h1 { font-size: 22pt; margin-bottom: 4px; }
  .header p  { opacity: .85; font-size: 10pt; }
  .meta-bar { display: flex; gap: 32px; background: #f8fafc; padding: 12px 32px; border-bottom: 2px solid #e2e8f0; font-size: 9.5pt; }
  .meta-bar strong { color: #1d4ed8; }
  .section { padding: 20px 32px; page-break-inside: avoid; }
  .pos-title { font-size: 13pt; font-weight: bold; color: #1d4ed8; margin-bottom: 10px; padding-bottom: 6px; border-bottom: 2px solid #1d4ed8; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 9.5pt; }
  th { background: #1e40af; color: white; padding: 8px 10px; text-align: left; }
  td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; }
  tr:nth-child(even) td { background: #f8fafc; }
  .winner { background: #fef9c3 !important; font-weight: bold; }
  .winner td { color: #92400e; }
  .badge { display: inline-block; background: #fef08a; color: #92400e; padding: 2px 8px; border-radius: 99px; font-size: 8pt; font-weight: bold; }
  .bar-cell { min-width: 120px; }
  .bar-bg { background: #e2e8f0; border-radius: 99px; height: 8px; }
  .bar-fill { background: #1d4ed8; height: 8px; border-radius: 99px; }
  .bar-fill.winner-fill { background: #f59e0b; }
  .footer { margin-top: 20px; padding: 12px 32px; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 8.5pt; }
  @media print { .no-print { display:none; } body { -webkit-print-color-adjust: exact; } }
</style>
</head>
<body>
<div class="no-print" style="padding:12px;background:#1d4ed8;color:white;text-align:center;font-family:Arial">
  <button onclick="window.print()" style="background:white;color:#1d4ed8;border:none;padding:8px 20px;border-radius:6px;font-weight:bold;cursor:pointer;font-size:14px">🖨️ Print / Save as PDF</button>
  &nbsp; <button onclick="window.close()" style="background:rgba(255,255,255,.2);color:white;border:none;padding:8px 16px;border-radius:6px;cursor:pointer">Close</button>
</div>

<div class="header">
  <h1>🗳️ {$election['title']}</h1>
  <p>Official Election Results Report &nbsp;|&nbsp; Generated: {$generatedAt}</p>
</div>

<div class="meta-bar">
  <span>Total Voters: <strong>{$totalVoters}</strong></span>
  <span>Participated: <strong>{$totalVoted}</strong></span>
  <span>Turnout: <strong>{$turnout}%</strong></span>
  <span>Voting Period: <strong>{$election['start_date']}</strong> to <strong>{$election['end_date']}</strong></span>
</div>
HTML;

    foreach ($byPosition as $pos) {
        $totalPosVotes = array_sum(array_column($pos['rows'], 'total_votes'));
        echo "<div class='section'><div class='pos-title'>📋 {$pos['name']}</div>";
        echo "<table><thead><tr><th>#</th><th>Candidate</th><th>Student ID</th><th>Course</th><th>Votes</th><th>%</th><th>Bar</th><th>Status</th></tr></thead><tbody>";
        foreach ($pos['rows'] as $i => $r) {
            $pct = $totalPosVotes > 0 ? round($r['total_votes']/$totalPosVotes*100,1) : 0;
            $cls = $r['is_winner'] ? 'winner' : '';
            $badge = $r['is_winner'] ? '<span class="badge">🏆 WINNER</span>' : '';
            $fillClass = $r['is_winner'] ? 'winner-fill' : '';
            echo "<tr class='{$cls}'><td>".($i+1)."</td><td><strong>{$r['fullname']}</strong></td><td>{$r['student_id']}</td><td>{$r['course']}</td><td><strong>{$r['total_votes']}</strong></td><td>{$pct}%</td>";
            echo "<td class='bar-cell'><div class='bar-bg'><div class='bar-fill {$fillClass}' style='width:{$pct}%'></div></div></td>";
            echo "<td>{$badge}</td></tr>";
        }
        echo "</tbody></table></div>";
    }

    echo "<div class='footer'>UniVote — University Online Voting System &nbsp;|&nbsp; This is an official document. &nbsp;|&nbsp; Generated: {$generatedAt}</div></body></html>";
    exit;
}

// ── Excel Export ──────────────────────────────────────────────
if ($type === 'excel') {
    $filename = 'election_results_' . $electionId . '_' . date('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');

    $out = fopen('php://output', 'w');

    // BOM for Excel UTF-8
    fwrite($out, "\xEF\xBB\xBF");

    // Header section
    fputcsv($out, ['UniVote — Election Results Report']);
    fputcsv($out, ['Election:', $election['title']]);
    fputcsv($out, ['Period:', $election['start_date'] . ' to ' . $election['end_date']]);
    fputcsv($out, ['Total Voters:', $totalVoters]);
    fputcsv($out, ['Total Voted:', $totalVoted]);
    fputcsv($out, ['Turnout:', $turnout . '%']);
    fputcsv($out, ['Generated:', date('Y-m-d H:i:s')]);
    fputcsv($out, []);

    foreach ($byPosition as $pos) {
        $totalPosVotes = array_sum(array_column($pos['rows'], 'total_votes'));
        fputcsv($out, ['--- ' . strtoupper($pos['name']) . ' ---']);
        fputcsv($out, ['Rank', 'Candidate Name', 'Student ID', 'Course', 'Year', 'Votes', 'Percentage', 'Result']);
        foreach ($pos['rows'] as $i => $r) {
            $pct = $totalPosVotes > 0 ? round($r['total_votes']/$totalPosVotes*100,1) : 0;
            fputcsv($out, [
                $i + 1,
                $r['fullname'],
                $r['student_id'],
                $r['course'],
                'Year ' . $r['year_of_study'],
                $r['total_votes'],
                $pct . '%',
                $r['is_winner'] ? 'WINNER' : ''
            ]);
        }
        fputcsv($out, []);
    }

    fclose($out);
    auditLog('EXPORT', "Results exported as CSV for election #$electionId");
    exit;
}

die('Invalid export type.');
