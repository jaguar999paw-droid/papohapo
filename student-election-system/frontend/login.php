<?php $backend = getenv('BACKEND_ORIGIN') ?: 'http://localhost:8081'; ?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="centered">
<form action="<?= htmlspecialchars($backend) ?>/auth.php" method="POST" class="card">
<h2>Student Election Login</h2>
<input name="email" placeholder="Email" required>
<input name="password" type="password" placeholder="Password" required>
<button>Login</button>
</form>
</body>
</html>
