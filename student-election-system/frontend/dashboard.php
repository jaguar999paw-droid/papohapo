<?php
session_start();
if(!isset($_SESSION['role'])){ header("Location: login.php"); exit; }
$role=$_SESSION['role'];
?>
<h1>Welcome <?=htmlspecialchars($_SESSION['uid'])?></h1>
<p>Role: <?=$role?></p>
<?php if($role==='ADMIN'): ?>
<a href="import.php">Import CSV</a>
<a href="reports.php">View Reports</a>
<?php endif; ?>
<?php if($role==='VOTER'): ?>
<a href="vote.php">Cast Vote</a>
<?php endif; ?>
<?php if($role==='OBSERVER'): ?>
<p>Observer Mode: read-only view</p>
<?php endif; ?>
<a href="logout.php">Logout</a>
