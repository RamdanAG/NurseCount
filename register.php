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

    if (strlen($username) < 6) {
        $errors[] = 'Username minimal 6 karakter.';
    }

    if (preg_match('/\s/', $username)) {
        $errors[] = 'Username tidak boleh mengandung spasi.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    }

    if (empty($errors)) {
        // Hash password
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Nilai default
        $photo_profile = 'default_photo.jpg';
        $alamat = 'Belum diisi';
        $usia = 0;

        $stmt = $pdo->prepare(
            'INSERT INTO users (nama_lengkap, username, password, role, Photo_profile, alamat, usia)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        try {
            $stmt->execute([
                $nama_lengkap,
                $username,
                $hash,
                'admin',
                $photo_profile,
                $alamat,
                $usia
            ]);
            exit; // Anda bisa redirect ke halaman login di sini jika mau
        } catch (PDOException $e) {
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
    <title>Register - NurseCount</title>
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="stylesheet" href="public/style/login.css">
    <link rel="stylesheet" href="public/style/root.css">
</head>
<body>
    <div class="bg">
        <div class="backgroundLogin">
            <h2 class="login">REGISTER</h2>

            <?php if ($errors): ?>
            <ul class="error">
                <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <form action="register.php" method="POST" class="form-grid">
                <input class="input-field" type="text" name="nama_lengkap" placeholder="Name" required>
                <input class="input-field" type="text" name="username" placeholder="Username" minlength="6" pattern="^\S+$" required>
                <input class="input-field" type="password" name="password" placeholder="Password" minlength="8" required>
                <button type="submit">Register</button>
            </form>
            <a href="login.php">Already have an account? Login here</a>
        </div>
    </div>

    <script>
    // Mencegah input spasi pada username
    document.querySelector('input[name="username"]').addEventListener('input', function () {
        this.value = this.value.replace(/\s/g, '');
    });
    </script>
</body>
</html>
