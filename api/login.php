<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'success' => false,
        'message' => 'Method not allowed.'
    ], 405);
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    jsonResponse([
        'success' => false,
        'message' => 'Username dan password wajib diisi.'
    ], 400);
}

$pdo = getDbConnection();
$stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1');
$stmt->execute([
    ':username' => $username,
    ':email' => $username,
]);

$user = $stmt->fetch();

if (!$user || !verifyPassword($password, $user['password_hash'])) {
    jsonResponse([
        'success' => false,
        'message' => 'Username atau password salah.'
    ], 401);
}

$_SESSION['user'] = [
    'id' => (int) $user['id'],
    'username' => $user['username'],
    'email' => $user['email'],
    'role' => $user['role'],
    'full_name' => $user['full_name'],
];

jsonResponse([
    'success' => true,
    'message' => 'Login berhasil.',
    'user' => $_SESSION['user'],
]);
