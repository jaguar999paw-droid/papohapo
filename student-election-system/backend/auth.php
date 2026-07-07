<?php
require 'db.php';
session_start();
$email = $_POST['email'];
$password = $_POST['password'];

$stmt = $pdo->prepare("
SELECT u.id,u.name,u.password_hash,r.name AS role,u.faculty_id
FROM users u
JOIN roles r ON u.role_id=r.id
WHERE u.email=? AND u.active=1
");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if($user && password_verify($password,$user['password_hash'])){
    $_SESSION['uid']=$user['id'];
    $_SESSION['role']=$user['role'];
    $_SESSION['faculty_id']=$user['faculty_id'];
    $frontend = getenv('FRONTEND_ORIGIN') ?: 'http://localhost:8080';
    header("Location: $frontend/dashboard.php");
    exit;
}
http_response_code(401);
echo "Invalid login";
