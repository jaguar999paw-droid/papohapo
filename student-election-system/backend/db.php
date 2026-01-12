<?php
$pdo = new PDO(
  "mysql:host=db;dbname=election",
  "election_user",
  "election_pass",
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
