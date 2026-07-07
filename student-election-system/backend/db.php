<?php
$pdo = new PDO(
  "mysql:host=db;dbname=" . (getenv('DB_NAME') ?: 'election'),
  getenv('DB_USER') ?: 'election_user',
  getenv('DB_PASSWORD') ?: '',
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
