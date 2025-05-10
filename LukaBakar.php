<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lang/lang.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$lang = $_SESSION['user']['lang'] ?? 'id';
$teks = ($lang === 'en') ? $bahasa_en : $bahasa_id;
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang); ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?php echo htmlspecialchars($teks['kalkulator_lukabakar']); ?></title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
</head>
<body>
  <a class="back" href="index.php"><?php echo htmlspecialchars($teks['back']); ?></a>

  <div class="main-container">
    <div class="content-text">
      <h6><?php echo htmlspecialchars($teks['kalkulator']); ?></h6>
      <h2><?php echo htmlspecialchars($teks['lukabakar']); ?></h2>
    </div>

    <div class="form-box">
      <form action="HasilLukaBakar.php" method="POST" enctype="multipart/form-data">
        <fieldset>
          <legend><?php echo htmlspecialchars($teks['pilih_area_lukabakar']); ?>:</legend>
          <label><input type="checkbox" name="area_parts[]" value="4.5"> Kepala Anterior (4.5%)</label>
          <label><input type="checkbox" name="area_parts[]" value="4.5"> Kepala Posterior (4.5%)</label>
          <label><input type="checkbox" name="area_parts[]" value="9"> Dada (9%)</label>
          <label><input type="checkbox" name="area_parts[]" value="9"> Perut (9%)</label>
          <label><input type="checkbox" name="area_parts[]" value="9"> Punggung Anterior (9%)</label>
          <label><input type="checkbox" name="area_parts[]" value="9"> Punggung Posterior (9%)</label>
          <label><input type="checkbox" name="area_parts[]" value="4.5"> Lengan Kanan Anterior (4.5%)</label>
          <label><input type="checkbox" name="area_parts[]" value="4.5"> Lengan Kanan Posterior (4.5%)</label>
          <label><input type="checkbox" name="area_parts[]" value="4.5"> Lengan Kiri Anterior (4.5%)</label>
          <label><input type="checkbox" name="area_parts[]" value="4.5"> Lengan Kiri Posterior (4.5%)</label>
          <label><input type="checkbox" name="area_parts[]" value="9"> Kaki Kanan Anterior (9%)</label>
          <label><input type="checkbox" name="area_parts[]" value="9"> Kaki Kanan Posterior (9%)</label>
          <label><input type="checkbox" name="area_parts[]" value="9"> Kaki Kiri Anterior (9%)</label>
          <label><input type="checkbox" name="area_parts[]" value="9"> Kaki Kiri Posterior (9%)</label>
          <label><input type="checkbox" name="area_parts[]" value="1"> Perineum (1%)</label>
        </fieldset>

        <label>Berat Badan (kg):
          <input type="number" name="berat_kg" step="0.1" min="1" required>
        </label>

        <label>Usia (tahun):
          <input type="number" name="usia" min="0" required>
        </label>

        <label>Unggah Gambar Luka:
          <input type="file" name="gambar" accept="image/*" required>
        </label>

        <button type="submit"><?php echo htmlspecialchars($teks['hitung_lukabakar']); ?></button>
      </form>
    </div>
  </div>
</body>
</html>
