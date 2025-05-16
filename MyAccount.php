<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lang/lang.php';

// Mulai session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']['id'] ?? null;

// Ambil bahasa
$lang = $_SESSION['user']['lang'] ?? 'id';
$teks = ($lang === 'en') ? $bahasa_en : $bahasa_id;

// Ambil data user dari database
$stmt = $pdo->prepare("SELECT nama_lengkap, username, alamat, Photo_profile, usia FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "<script>alert('User tidak ditemukan.'); window.location.href = 'index.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - NurseCount</title>
    <link rel="stylesheet" href="public/style/root.css">
    <link rel="stylesheet" href="public/style/form.css">
    <style>
            .static-field {
    font-size: 16px;
    color: #333;
    margin-bottom: 12px;
    border: 1px solid #ccc;
    padding: 8px 12px;
    border-radius: 4px;
    background-color: #f9f9f9;
    }
    </style>
</head>
<body>
<a class="back" href="index.php"><button class="back-button"><</button></a>

<div class="main-container">
    <div class="content-text">
        <h6><?= htmlspecialchars($teks['kalkulator']) ?></h6>
        <h2>My Account</h2>
    </div>

    <div class="form-box">
        <form action="EditMyAccount.php" method="post">
        <div style="text-align: center; margin-bottom: 20px;">
        <?php if ($user['Photo_profile'] && file_exists(__DIR__ . "/public/image/profile/" . $user['Photo_profile'])): ?>
            <img src="public/image/profile/<?= htmlspecialchars($user['Photo_profile']) ?>" alt="Profile" style="width: 125px; height:125px; border-radius: 50%; object-fit: cover;">
        <?php else: ?>
            <img src="public/image/profile/default.png" alt="Default Profile" style="max-width: 150px; border-radius: 50%;">
        <?php endif; ?>
    </div>

    <p class="static-field result-hjk"><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
    <p class="static-field result-hjk"><strong><?php echo htmlspecialchars($teks['nama']); ?>:</strong> <?= htmlspecialchars($user['nama_lengkap']) ?></p>
    <p class="static-field result-hjk"><strong><?php echo htmlspecialchars($teks['alamat']); ?>:</strong> <?= htmlspecialchars($user['alamat']) ?></p>
    <p class="static-field result-hjk"><strong><?php echo htmlspecialchars($teks['usia']); ?>:</strong> <?= htmlspecialchars($user['usia']) ?></p>
    <button type="submit">Edit Profile</button>
        </form>
</div>

</div>
</body>
</html>
