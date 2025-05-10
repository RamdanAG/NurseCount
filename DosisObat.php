<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lang/lang.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php'); exit;
}
$lang = $_SESSION['user']['lang'] ?? 'id';
$teks = ($lang === 'en') ? $bahasa_en : $bahasa_id;
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang); ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?php echo htmlspecialchars($teks['dosis_kalkulator']); ?></title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
</head>
<body>
  <a class="back" href="index.php"><?php echo htmlspecialchars($teks['back']); ?></a>
  <div class="main-container">
    <div class="content-text">
      <h6><?php echo htmlspecialchars($teks['kalkulator']); ?></h6>
      <h2><?php echo htmlspecialchars($teks['dosis_obat']); ?></h2>
    </div>
    <div class="form-box">
      <form action="HasilDosisObat.php" method="POST">
        <label><?php echo htmlspecialchars($teks['nama_lengkap']); ?>:
          <input type="text" name="nama_lengkap" required>
        </label>
        <label><?php echo htmlspecialchars($teks['umur']); ?>:
          <input type="number" name="umur" required>
        </label>
        <label><?php echo htmlspecialchars($teks['alamat']); ?>:
          <input type="text" name="alamat" required>
        </label>
        <label><?php echo htmlspecialchars($teks['berat_kg']); ?>:
          <input type="number" name="berat_kg" step="0.1" min="1" required>
        </label>
        <label><?php echo htmlspecialchars($teks['tinggi_cm']); ?>:
          <input type="number" name="tinggi_cm" step="0.1" min="50" required>
        </label>
        <label><?php echo htmlspecialchars($teks['tingkat_aktivitas']); ?>:
          <select name="aktivitas" required>
            <option value=""><?php echo htmlspecialchars($teks['pilih']); ?></option>
            <option value="1.2"><?php echo htmlspecialchars('Berbaring di tempat tidur (1.2)'); ?></option>
            <option value="1.375"><?php echo htmlspecialchars('Aktivitas ringan (1.375)'); ?></option>
            <option value="1.55"><?php echo htmlspecialchars('Aktivitas sedang (1.55)'); ?></option>
            <option value="1.725"><?php echo htmlspecialchars('Aktivitas berat (1.725)'); ?></option>
            <option value="1.9"><?php echo htmlspecialchars('Aktivitas sangat berat (1.9)'); ?></option>
          </select>
        </label>
        <label><?php echo htmlspecialchars($teks['faktor_stress']); ?>:
          <select name="stress" required>
            <option value=""><?php echo htmlspecialchars($teks['pilih']); ?></option>
            <option value="1.1"><?php echo htmlspecialchars('Pembedahan ringan (1.1)'); ?></option>
            <option value="1.3"><?php echo htmlspecialchars('Infeksi sedang (1.2-1.4)'); ?></option>
            <option value="1.75"><?php echo htmlspecialchars('Luka bakar (1.5-2.0)'); ?></option>
          </select>
        </label>
        <button type="submit"><?php echo htmlspecialchars($teks['submit']); ?></button>
      </form>
    </div>
  </div>
</body>
</html>
