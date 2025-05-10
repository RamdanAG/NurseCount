<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lang/lang.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}
$lang = $_SESSION['user']['lang'] ?? 'id';
$teks = ($lang === 'en') ? $bahasa_en : $bahasa_id;
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Kalkulator Kebutuhan Cairan</title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
</head>
<body>
  <a class="back" href="index.php">« Kembali</a>
  <div class="main-container">
    <div class="content-text">
      <h6>Kalkulator</h6>
      <h2>Kebutuhan Cairan</h2>
    </div>
    <div class="form-box">
      <form action="HasilKebutuhanCairan.php" method="POST">
      <label><?php echo htmlspecialchars($teks['mode_hitung']); ?>:
  <select name="mode_calc" required>
    <option value=""><?php echo htmlspecialchars($teks['pilih']); ?></option>
    <option value="per24"><?php echo htmlspecialchars($teks['per24']); ?></option>
    <option value="perjam"><?php echo htmlspecialchars($teks['perjam']); ?></option>
  </select>
</label>

<label><?php echo htmlspecialchars($teks['nama_lengkap']); ?>:
  <input type="text" name="nama" required>
</label>

<label><?php echo htmlspecialchars($teks['usia']); ?>:
  <input type="number" name="umur" min="0" required>
</label>

<label><?php echo htmlspecialchars($teks['alamat']); ?>:
  <input type="text" name="alamat" required>
</label>

<label><?php echo htmlspecialchars($teks['berat_kg']); ?>:
  <input type="number" name="berat_kg" step="0.1" min="0.1" required>
</label>

<label><?php echo htmlspecialchars($teks['kondisi_klinis']); ?>:
  <select name="kondisi" required>
    <option value=""><?php echo htmlspecialchars($teks['pilih']); ?></option>
    <option value="Normal"><?php echo htmlspecialchars($teks['normal']); ?></option>
    <option value="Demam"><?php echo htmlspecialchars($teks['demam']); ?></option>
    <option value="Dehidrasi Ringan"><?php echo htmlspecialchars($teks['dehidrasi_ringan']); ?></option>
    <option value="Dehidrasi Berat"><?php echo htmlspecialchars($teks['dehidrasi_berat']); ?></option>
  </select>
</label>

<button type="submit"><?php echo htmlspecialchars($teks['hitung']); ?></button>

      </form>
    </div>
  </div>
</body>
</html>
