<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'success' => false,
        'message' => 'Method not allowed.'
    ], 405);
}

$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$role = trim($_POST['role'] ?? 'customer');

if ($fullName === '' || $email === '' || $username === '' || $password === '') {
    jsonResponse([
        'success' => false,
        'message' => 'Semua field wajib diisi.'
    ], 400);
}

$allowedRoles = ['customer', 'driver', 'admin', 'owner'];
if (!in_array($role, $allowedRoles, true)) {
    $role = 'customer';
}

$pdo = getDbConnection();

$stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
$stmt->execute([
    ':username' => $username,
    ':email' => $email,
]);

if ($stmt->fetch()) {
    jsonResponse([
        'success' => false,
        'message' => 'Username atau email sudah terdaftar.'
    ], 409);
}

$passwordHash = hashPassword($password);

$stmt = $pdo->prepare('INSERT INTO users (full_name, email, username, password_hash, role, created_at) VALUES (:full_name, :email, :username, :password_hash, :role, NOW())');
$stmt->execute([
    ':full_name' => $fullName,
    ':email' => $email,
    ':username' => $username,
    ':password_hash' => $passwordHash,
    ':role' => $role,
]);

jsonResponse([
    'success' => true,
    'message' => 'Registrasi berhasil.'
]);
