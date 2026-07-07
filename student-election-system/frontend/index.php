<?php
session_start();
if (!isset($_SESSION['uid'])) { header("Location: login.php"); exit; }
$role = $_SESSION['role'];
$backend = getenv('BACKEND_ORIGIN') ?: 'http://localhost:8081';
?>
<h1>Welcome (user #<?= htmlspecialchars($_SESSION['uid']) ?>)</h1>
<p>Role: <?= htmlspecialchars($role) ?></p>

<?php if ($role === 'ADMIN'): ?>
<h2>Admin Dashboard</h2>
<?php endif; ?>

<?php if ($role === 'VOTER'): ?>
<h2>Vote</h2>
<form method="POST" action="<?= htmlspecialchars($backend) ?>/vote.php">
  <input type="hidden" name="position_id" value="1">
  <button name="candidate_id" value="">Blank Vote</button>
</form>
<?php endif; ?>

<a href="logout.php">Logout</a>
