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
  <a class="back" href="index.php"><button class="back-button"><</button></a>>
  <div class="main-container">
    <div class="content-text">
      <h6><?php echo htmlspecialchars($teks['kalkulator']); ?></h6>
      <h2><?php echo htmlspecialchars($teks['dosis_obat']); ?></h2>
    </div>
    <div class="form-box">
      <form action="HasilDosisObat.php" method="POST">
        <div class="form-group">
          <label for="nama_lengkap"><?php echo htmlspecialchars($teks['nama_lengkap']); ?></label>
          <input type="text" id="nama_lengkap" name="nama_lengkap" required>
        </div>

        <div class="form-group">
          <label for="umur"><?php echo htmlspecialchars($teks['umur']); ?></label>
          <input type="number" id="umur" name="umur" required>
        </div>

        <div class="form-group">
          <label for="alamat"><?php echo htmlspecialchars($teks['alamat']); ?></label>
          <input type="text" id="alamat" name="alamat" required>
        </div>

        <div class="form-group">
          <label for="berat_kg"><?php echo htmlspecialchars($teks['berat_kg']); ?></label>
          <input type="number" id="berat_kg" name="berat_kg" step="0.1" min="1" required>
        </div>

        <div class="form-group">
          <label for="tinggi_cm"><?php echo htmlspecialchars($teks['tinggi_cm']); ?></label>
          <input type="number" id="tinggi_cm" name="tinggi_cm" step="0.1" min="50" required>
        </div>

        <div class="form-group">
          <label for="aktivitas"><?php echo htmlspecialchars($teks['tingkat_aktivitas']); ?></label>
          <select id="aktivitas" name="aktivitas" required>
            <option value=""><?php echo htmlspecialchars($teks['pilih']); ?></option>
            <option value="1.2"><?php echo htmlspecialchars($teks['akt_berbaring']); ?></option>
            <option value="1.375"><?php echo htmlspecialchars($teks['akt_ringan']); ?></option>
            <option value="1.55"><?php echo htmlspecialchars($teks['akt_sedang']); ?></option>
            <option value="1.725"><?php echo htmlspecialchars($teks['akt_berat']); ?></option>
            <option value="1.9"><?php echo htmlspecialchars($teks['akt_sangat_berat']); ?></option>
          </select>
        </div>

        <div class="form-group">
          <label for="stress"><?php echo htmlspecialchars($teks['faktor_stress']); ?></label>
          <select id="stress" name="stress" required>
            <option value=""><?php echo htmlspecialchars($teks['pilih']); ?></option>
            <option value="1.1"><?php echo htmlspecialchars($teks['stress_pembedahan']); ?></option>
            <option value="1.3"><?php echo htmlspecialchars($teks['stress_infeksi']); ?></option>
            <option value="1.75"><?php echo htmlspecialchars($teks['stress_lukabakar']); ?></option>
          </select>
        </div>

        <button type="submit"><?php echo htmlspecialchars($teks['hitung']); ?></button>
      </form>
    </div>
  </div>
</body>
</html>
