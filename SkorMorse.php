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
    <title><?= htmlspecialchars($teks['judul_morse']) ?></title>
    <link rel="stylesheet" href="public/style/root.css">
    <link rel="stylesheet" href="public/style/form.css">
</head>
<body>
<a class="back" href="index.php"><?= htmlspecialchars($teks['back']) ?></a>
<div class="main-container">
    <div class="content-text">
        <h6><?= htmlspecialchars($teks['kalkulator']) ?></h6>
        <h2><?= htmlspecialchars($teks['judul_morse']) ?></h2>
    </div>

    <div class="form-box">
        <form method="POST" action="HasilSkorMorse.php">
            <label><?= $teks['nama_lengkap'] ?>:
                <input type="text" name="nama_lengkap" required>
            </label>

            <label><?= $teks['usia'] ?>:
                <input type="number" name="usia" required>
            </label>

            <label><?= $teks['alamat'] ?>:
                <input type="text" name="alamat" required>
            </label>

            <label><?= $teks['riwayat_jatuh'] ?>:
                <select name="riwayat_jatuh">
                    <option value="0"><?= $teks['tidak'] ?></option>
                    <option value="25"><?= $teks['ya'] ?></option>
                </select>
            </label>

            <label><?= $teks['diagnosis_sekunder'] ?>:
                <select name="diagnosis_sekunder">
                    <option value="0"><?= $teks['tidak'] ?></option>
                    <option value="15"><?= $teks['ya'] ?></option>
                </select>
            </label>

            <label><?= $teks['bantuan_mobilitas'] ?>:
                <select name="bantuan_mobilitas">
                    <option value="0"><?= $teks['tidak_ada_kursi'] ?></option>
                    <option value="15"><?= $teks['tongkat'] ?></option>
                    <option value="30"><?= $teks['furniture'] ?></option>
                </select>
            </label>

            <label><?= $teks['terpasang_inpus'] ?>:
                <select name="terpasang_inpus">
                    <option value="0"><?= $teks['tidak'] ?></option>
                    <option value="20"><?= $teks['ya'] ?></option>
                </select>
            </label>

            <label><?= $teks['gaya_berjalan'] ?>:
                <select name="gaya_berjalan">
                    <option value="0"><?= $teks['tidak_ada_tirah'] ?></option>
                    <option value="10"><?= $teks['lemah'] ?></option>
                    <option value="20"><?= $teks['terganggu'] ?></option>
                </select>
            </label>

            <label><?= $teks['status_mental'] ?>:
                <select name="status_mental">
                    <option value="0"><?= $teks['mengetahui_diri'] ?></option>
                    <option value="15"><?= $teks['lupa_batas'] ?></option>
                </select>
            </label>

            <button type="submit"><?= $teks['submit'] ?></button>
        </form>
    </div>
</div>
</body>
</html>
