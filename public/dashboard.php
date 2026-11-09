<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

if (!isLoggedIn()) {
    redirect('public/index.php');
}

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | RABZ</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="navbar">
            <div class="brand"><span>RABZ</span> Dashboard</div>
            <div>
                <a href="../api/logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </header>

    <main class="container">
        <section class="card login-panel">
            <h2>Selamat datang, <?= htmlspecialchars($user['username']) ?></h2>
            <p>Role: <strong><?= htmlspecialchars($user['role']) ?></strong></p>
            <p>Akun ini sedang memiliki akses ke area dashboard manajemen RABZ.</p>
        </section>
    </main>
</body>
</html>
