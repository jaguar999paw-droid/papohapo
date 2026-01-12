<?php
require 'db.php';
require 'rbac.php';
require_role(['VOTER']);
session_start();
$stmt=$pdo->prepare("
INSERT INTO votes(voter_id,position_id,candidate_id)
VALUES(?,?,?)
ON DUPLICATE KEY UPDATE candidate_id=VALUES(candidate_id)
");
$stmt->execute([
  $_SESSION['uid'],
  $_POST['position_id'],
  $_POST['candidate_id'] ?: null
]);
echo "Vote recorded";
