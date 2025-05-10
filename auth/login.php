<?php
session_start();
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password'])) {
    // Gagal: kembalikan ke form dengan pesan error
    header('Location: ../login.php?error=Username atau password salah');
    exit;
}

// Berhasil: simpan session
$_SESSION['user'] = [
    'id'           => $user['id'],
    'nama_lengkap' => $user['nama_lengkap'],
    'username'     => $user['username'],
    'role'         => $user['role'],
    'lang'         => $user['lang'],  // pastikan kolom lang ada di DB
];

// Redirect ke index
header('Location: ../index.php');
exit;
