<?php
require 'db.php';
require 'rbac.php';
require_role(['VOTER']);
session_start();

$position_id = $_POST['position_id'];

// Only allow voting while the parent election is ACTIVE and inside its window.
$check = $pdo->prepare("
SELECT e.status, e.start_at, e.end_at
FROM positions p
JOIN elections e ON p.election_id = e.id
WHERE p.id = ?
");
$check->execute([$position_id]);
$election = $check->fetch(PDO::FETCH_ASSOC);

if (!$election || $election['status'] !== 'ACTIVE') {
    http_response_code(403);
    exit("Voting is not open for this election.");
}

$now = date('Y-m-d H:i:s');
if (($election['start_at'] && $now < $election['start_at']) ||
    ($election['end_at'] && $now > $election['end_at'])) {
    http_response_code(403);
    exit("Voting window is closed.");
}

$stmt=$pdo->prepare("
INSERT INTO votes(voter_id,position_id,candidate_id)
VALUES(?,?,?)
ON DUPLICATE KEY UPDATE candidate_id=VALUES(candidate_id)
");
$stmt->execute([
  $_SESSION['uid'],
  $position_id,
  $_POST['candidate_id'] ?: null
]);
echo "Vote recorded";
