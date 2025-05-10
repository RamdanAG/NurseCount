<?php
require_once __DIR__ . '/../config/config.php';
// Hapus session dan redirect ke login
session_destroy();
header('Location: ../login.php');
exit;