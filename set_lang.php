<?php
session_start();
require_once __DIR__ . '/config/config.php'; // Ini harus mendefinisikan $pdo (bukan $conn)

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$lang = $_GET['lang'] ?? 'id';
$lang = ($lang === 'en') ? 'en' : 'id';

// Update session
$_SESSION['user']['lang'] = $lang;

// Update database
$id = $_SESSION['user']['id'];
$stmt = $pdo->prepare("UPDATE users SET lang = :lang WHERE id = :id");
$stmt->execute([
    ':lang' => $lang,
    ':id' => $id
]);

// Redirect kembali
$redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header("Location: $redirect");
exit;
