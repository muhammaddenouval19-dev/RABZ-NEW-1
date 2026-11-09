<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

$pdo = getDbConnection();
$action = $_REQUEST['action'] ?? ($_GET['action'] ?? 'list');

function normalizeAccountRole(string $role): string
{
    $value = strtolower(trim($role));

    if ($value === 'owner') {
        return 'owner';
    }

    if ($value === 'admin') {
        return 'admin';
    }

    if ($value === 'driver' || $value === 'karyawan') {
        return 'driver';
    }

    return 'customer';
}

function buildFallbackEmail(string $username): string
{
    $base = preg_replace('/[^a-zA-Z0-9._-]+/', '', strtolower(trim($username)));
    if ($base === '') {
        $base = 'user';
    }

    return $base . '@rabz.local';
}

function createUserRecord(PDO $pdo, array $data): array
{
    $fullName = trim((string) ($data['full_name'] ?? '')) ?: (string) ($data['username'] ?? 'User');
    $email = trim((string) ($data['email'] ?? '')) ?: buildFallbackEmail((string) ($data['username'] ?? 'user'));
    $username = trim((string) ($data['username'] ?? '')) ?: preg_replace('/[^a-zA-Z0-9._-]+/', '', strtolower(str_replace(' ', '', $fullName))) ?: 'user';
    $role = normalizeAccountRole((string) ($data['role'] ?? 'customer'));
    $password = (string) ($data['password'] ?? '');
    $passwordHash = $password !== '' ? hashPassword($password) : hashPassword('google_' . bin2hex(random_bytes(6)));

    $existing = $pdo->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
    $existing->execute([
        ':username' => $username,
        ':email' => $email,
    ]);

    if ($existing->fetch()) {
        jsonResponse([
            'success' => false,
            'message' => 'Username atau email sudah terdaftar.',
        ], 409);
    }

    $stmt = $pdo->prepare('INSERT INTO users (full_name, email, username, password_hash, role, status, created_at) VALUES (:full_name, :email, :username, :password_hash, :role, :status, NOW())');
    $stmt->execute([
        ':full_name' => $fullName,
        ':email' => $email,
        ':username' => $username,
        ':password_hash' => $passwordHash,
        ':role' => $role,
        ':status' => 'active',
    ]);

    $userId = (int) $pdo->lastInsertId();

    if ($role === 'customer') {
        $customerStmt = $pdo->prepare('INSERT INTO customers (user_id, phone, address, city) VALUES (:user_id, NULL, NULL, NULL)');
        $customerStmt->execute([':user_id' => $userId]);
    }

    if ($role === 'driver') {
        $driverStmt = $pdo->prepare('INSERT INTO drivers (user_id, vehicle_name, license_number, status) VALUES (:user_id, NULL, NULL, :status)');
        $driverStmt->execute([
            ':user_id' => $userId,
            ':status' => 'offline',
        ]);
    }

    return [
        'id' => $userId,
        'full_name' => $fullName,
        'email' => $email,
        'username' => $username,
        'role' => $role,
    ];
}

if ($action === 'list') {
    $stmt = $pdo->query('SELECT * FROM users ORDER BY created_at DESC');
    $users = $stmt->fetchAll();
    jsonResponse([
        'success' => true,
        'users' => $users,
    ]);
}

if ($action === 'create_account') {
    $payload = [
        'full_name' => $_POST['full_name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'username' => $_POST['username'] ?? '',
        'password' => $_POST['password'] ?? '',
        'role' => $_POST['role'] ?? 'customer',
    ];

    if (($payload['username'] ?? '') === '' || ($payload['password'] ?? '') === '') {
        jsonResponse([
            'success' => false,
            'message' => 'Username dan password wajib diisi.',
        ], 400);
    }

    $user = createUserRecord($pdo, $payload);
    jsonResponse([
        'success' => true,
        'message' => 'Akun berhasil disimpan ke database.',
        'user' => $user,
    ]);
}

if ($action === 'update_account') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $role = normalizeAccountRole((string) ($_POST['role'] ?? 'customer'));
    $password = $_POST['password'] ?? '';

    if ($username === '' && $email === '') {
        jsonResponse([
            'success' => false,
            'message' => 'Username atau email wajib diisi untuk update.',
        ], 400);
    }

    $where = [];
    $params = [];

    if ($username !== '') {
        $where[] = 'username = :username';
        $params[':username'] = $username;
    }

    if ($email !== '') {
        $where[] = 'email = :email';
        $params[':email'] = $email;
    }

    $query = 'SELECT id FROM users WHERE ' . implode(' OR ', $where) . ' LIMIT 1';
    $check = $pdo->prepare($query);
    $check->execute($params);
    $userRow = $check->fetch();

    if (!$userRow) {
        jsonResponse([
            'success' => false,
            'message' => 'Akun tidak ditemukan untuk diupdate.',
        ], 404);
    }

    $setParts = ['full_name = :full_name', 'role = :role', 'updated_at = NOW()'];
    $bind = [
        ':full_name' => $fullName !== '' ? $fullName : ($username !== '' ? $username : $email),
        ':role' => $role,
    ];

    if ($password !== '') {
        $setParts[] = 'password_hash = :password_hash';
        $bind[':password_hash'] = hashPassword($password);
    }

    $sql = 'UPDATE users SET ' . implode(', ', $setParts) . ' WHERE id = :id';
    $bind[':id'] = (int) $userRow['id'];
    $stmt = $pdo->prepare($sql);
    $stmt->execute($bind);

    jsonResponse([
        'success' => true,
        'message' => 'Perubahan akun berhasil disimpan ke database.',
        'user' => [
            'username' => $username !== '' ? $username : $email,
            'role' => $role,
        ],
    ]);
}

if ($action === 'google_signup') {
    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $username = trim((string) ($_POST['username'] ?? ''));

    if ($fullName === '' || $email === '') {
        jsonResponse([
            'success' => false,
            'message' => 'Nama lengkap dan email Google wajib diisi.',
        ], 400);
    }

    $baseUsername = $username !== '' ? $username : strtolower(str_replace(' ', '', $fullName));
    $baseUsername = preg_replace('/[^a-zA-Z0-9._-]+/', '', $baseUsername) ?: 'googleuser';
    $candidateUsername = $baseUsername;
    $counter = 1;

    while (true) {
        $check = $pdo->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
        $check->execute([
            ':username' => $candidateUsername,
            ':email' => $email,
        ]);

        if (!$check->fetch()) {
            break;
        }

        if ($email !== '') {
            $checkUser = $pdo->prepare('SELECT username FROM users WHERE email = :email LIMIT 1');
            $checkUser->execute([':email' => $email]);
            $existing = $checkUser->fetch();
            if ($existing) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Akun Google sudah terdaftar.',
                    'user' => [
                        'full_name' => $fullName,
                        'email' => $email,
                        'username' => $existing['username'],
                        'role' => 'customer',
                    ],
                ]);
            }
        }

        $candidateUsername = $baseUsername . $counter;
        $counter++;
    }

    $user = createUserRecord($pdo, [
        'full_name' => $fullName,
        'email' => $email,
        'username' => $candidateUsername,
        'password' => 'google_' . bin2hex(random_bytes(8)),
        'role' => 'customer',
    ]);

    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role'],
        'full_name' => $user['full_name'],
    ];

    jsonResponse([
        'success' => true,
        'message' => 'Akun Google berhasil didaftarkan dan tersimpan ke database.',
        'user' => $_SESSION['user'],
    ]);
}

jsonResponse([
    'success' => false,
    'message' => 'Aksi tidak dikenal.',
], 400);
