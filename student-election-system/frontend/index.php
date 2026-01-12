<?php
session_start();
if (!isset($_SESSION['user'])) header("Location: login.php");
$user = $_SESSION['user'];
?>
<h1>Welcome <?= htmlspecialchars($user['name']) ?></h1>
<p>Role: <?= $user['role'] ?></p>

<?php if ($user['role'] === 'ADMIN'): ?>
<h2>Admin Dashboard</h2>
<?php endif; ?>

<?php if ($user['role'] === 'VOTER'): ?>
<h2>Vote</h2>
<form method="POST" action="/backend/vote.php">
  <input type="hidden" name="position_id" value="1">
  <button name="candidate_id" value="">Blank Vote</button>
</form>
<?php endif; ?>

<a href="logout.php">Logout</a>
