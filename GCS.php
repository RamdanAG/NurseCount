<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lang/lang.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}

// bahasa
$user = $_SESSION['user'];
$lang = $user['lang'] ?? 'id';
$teks = ($lang === 'en') ? $bahasa_en : $bahasa_id;
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?= htmlspecialchars($teks['judul_gcs']) ?></title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
</head>
<body>
  <a class="back" href="index.php"><?= htmlspecialchars($teks['back']) ?></a>
  <div class="main-container">
    <div class="content-text">
      <h6><?= htmlspecialchars($teks['kalkulator']) ?></h6>
      <h2><?= htmlspecialchars($teks['judul_gcs']) ?></h2>
    </div>
    <div class="form-box">
      <form action="HasilGCS.php" method="POST">
        <label><?= htmlspecialchars($teks['nama_lengkap']) ?>:
          <input type="text" name="nama_lengkap" required>
        </label>
        <label><?= htmlspecialchars($teks['umur']) ?>:
          <input type="number" name="umur" required>
        </label>
        <label><?= htmlspecialchars($teks['alamat']) ?>:
          <input type="text" name="alamat" required>
        </label>
        <label class="text-label"><?= htmlspecialchars($teks['jenis_kelamin']) ?>:
          <div class="radio-group">
            <label><input type="radio" name="jenis_kelamin" value="Laki-laki" required> <?= htmlspecialchars($teks['laki_laki']) ?></label><br>
            <label><input type="radio" name="jenis_kelamin" value="Perempuan" required> <?= htmlspecialchars($teks['perempuan']) ?></label>
          </div>
        </label>
        <label><?= htmlspecialchars($teks['eye_response']) ?>:
          <select name="eye" required>
            <option value="">-- <?= htmlspecialchars($teks['pilih']) ?> --</option>
            <option value="4"><?= htmlspecialchars($teks['eye_4']) ?></option>
            <option value="3"><?= htmlspecialchars($teks['eye_3']) ?></option>
            <option value="2"><?= htmlspecialchars($teks['eye_2']) ?></option>
            <option value="1"><?= htmlspecialchars($teks['eye_1']) ?></option>
          </select>
        </label>
        <label><?= htmlspecialchars($teks['verbal_response']) ?>:
          <select name="verbal" required>
            <option value="">-- <?= htmlspecialchars($teks['pilih']) ?> --</option>
            <option value="5"><?= htmlspecialchars($teks['verbal_5']) ?></option>
            <option value="4"><?= htmlspecialchars($teks['verbal_4']) ?></option>
            <option value="3"><?= htmlspecialchars($teks['verbal_3']) ?></option>
            <option value="2"><?= htmlspecialchars($teks['verbal_2']) ?></option>
            <option value="1"><?= htmlspecialchars($teks['verbal_1']) ?></option>
          </select>
        </label>
        <label><?= htmlspecialchars($teks['motor_response']) ?>:
          <select name="motor" required>
            <option value="">-- <?= htmlspecialchars($teks['pilih']) ?> --</option>
            <option value="6"><?= htmlspecialchars($teks['motor_6']) ?></option>
            <option value="5"><?= htmlspecialchars($teks['motor_5']) ?></option>
            <option value="4"><?= htmlspecialchars($teks['motor_4']) ?></option>
            <option value="3"><?= htmlspecialchars($teks['motor_3']) ?></option>
            <option value="2"><?= htmlspecialchars($teks['motor_2']) ?></option>
            <option value="1"><?= htmlspecialchars($teks['motor_1']) ?></option>
          </select>
        </label>
        <button type="submit"><?= htmlspecialchars($teks['submit']) ?></button>
      </form>
    </div>
  </div>
</body>
</html>
