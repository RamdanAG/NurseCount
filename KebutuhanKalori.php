<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lang/lang.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$lang = $user['lang'] ?? 'id';
$teks = ($lang === 'en') ? $bahasa_en : $bahasa_id;
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?= htmlspecialchars($teks['judul_kebutuhan_kalori']) ?></title>
    <link rel="stylesheet" href="public/style/root.css">
    <link rel="stylesheet" href="public/style/form.css">
</head>
<body>
    <a class="back" href="index.php"><button class="back-button"><</button></a>

    <div class="main-container">
        <div class="content-text">
            <h6><?= htmlspecialchars($teks['kalkulator']) ?></h6>
            <h2><?= htmlspecialchars($teks['judul_kebutuhan_kalori']) ?></h2>
        </div>

        <div class="form-box">
            <form action="HasilKebutuhanKalori.php" method="POST">
                <label><?= $teks['nama_lengkap'] ?>:
                    <input type="text" name="nama_lengkap" required>
                </label>

                <label><?= $teks['usia'] ?>:
                    <input type="number" name="usia" required>
                </label>

                <label><?= $teks['alamat'] ?>:
                    <input type="text" name="alamat" required>
                </label>

                <label><?= $teks['gender'] ?>:
                    <select name="gender" required>
                        <option value="Laki-Laki/Men"><?= $teks['pria'] ?></option>
                        <option value="wanita/Female"><?= $teks['wanita'] ?></option>
                    </select>
                </label>

                <label><?= $teks['berat_badan'] ?>:
                    <input type="number" step="0.1" name="berat_badan" required>
                </label>

                <label><?= $teks['tinggi_badan'] ?>:
                    <input type="number" step="0.1" name="tinggi_badan" required>
                </label>

                <label><?= $teks['aktivitas'] ?>:
                    <select name="aktivitas" required>
                        <option value="1.2"><?= $teks['aktivitas1'] ?></option>
                        <option value="1.375"><?= $teks['aktivitas2'] ?></option>
                        <option value="1.55"><?= $teks['aktivitas3'] ?></option>
                        <option value="1.725"><?= $teks['aktivitas4'] ?></option>
                        <option value="1.9"><?= $teks['aktivitas5'] ?></option>
                    </select>
                </label>

                <label><?= $teks['stress'] ?>:
                    <select name="stress" required>
                        <option value="1.1"><?= $teks['stress1'] ?></option>
                        <option value="1.3"><?= $teks['stress2'] ?></option>
                        <option value="1.75"><?= $teks['stress3'] ?></option>
                    </select>
                </label>

                <button type="submit"><?= $teks['submit'] ?></button>
            </form>
        </div>
    </div>
</body>
</html>
