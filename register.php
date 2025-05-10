<?php
require_once __DIR__ . '/config/config.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $username     = trim($_POST['username'] ?? '');
    $password     = $_POST['password'] ?? '';

    // Validasi sederhana
    if (!$nama_lengkap || !$username || !$password) {
        $errors[] = 'Semua field wajib diisi.';
    }

    if (empty($errors)) {
        // Hash password
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert ke database
        $stmt = $pdo->prepare(
            'INSERT INTO users (nama_lengkap, username, password, role) VALUES (?, ?, ?, ?)'  
        );
        try {
            $stmt->execute([
                $nama_lengkap,
                $username,
                $hash,
                'user'
            ]);
            echo "<script>alert('Registrasi berhasil!'); window.location.href = 'login.php';</script>";
            exit;
        } catch (PDOException $e) {
            // Jika username duplikat
            if ($e->getCode() === '23000') {
                $errors[] = 'Username sudah terdaftar.';
            } else {
                $errors[] = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public/css/styles.css">
    <title>Register - NurseCount</title>
    <link rel="stylesheet" href="public/style/login.css">
</head>
<body>
    <div class="login-container">
        <h2>REGISTER</h2>
        <?php if ($errors): ?>
            <ul class="error">
                <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form action="register.php" method="POST" class="form-grid">
            <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Daftar</button>
        </form>
        <p>
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </p>
    </div>
</body>
</html>