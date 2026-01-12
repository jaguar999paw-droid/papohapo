<?php
require 'db.php';
require 'rbac.php';
require_role(['ADMIN']);

if($_FILES['csv']['error']!==0){ exit("File error"); }

$file=fopen($_FILES['csv']['tmp_name'],'r');
while(($row=fgetcsv($file))!==false){
    [$name,$email,$role]=$row;
    $stmt=$pdo->prepare("INSERT IGNORE INTO users(name,email,role_id,password_hash)
        VALUES(?, ?, (SELECT id FROM roles WHERE name=?), ?)");
    $stmt->execute([$name,$email,$role,password_hash('changeme',PASSWORD_DEFAULT)]);
}
fclose($file);
echo "CSV imported";
