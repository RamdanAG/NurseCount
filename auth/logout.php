<?php
require_once __DIR__ . '/../config/config.php';

// Mulai session terlebih dahulu
session_start();

// Hapus semua session
session_destroy();

// Redirect ke halaman login
header('Location: ../login.php');
exit;
