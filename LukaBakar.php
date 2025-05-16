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
  <a class="back" href="index.php"><button class="back-button"><</button></a>

  <div class="main-container">
    <div class="content-text">
      <h6><?php echo htmlspecialchars($teks['kalkulator']); ?></h6>
      <h2><?php echo htmlspecialchars($teks['lukabakar']); ?></h2>
    </div>

    

    <div class="form-box">
      <form action="HasilLukaBakar.php" method="POST" enctype="multipart/form-data">
      <fieldset>
  <legend><?php echo htmlspecialchars($teks['pilih_area_lukabakar']); ?>:</legend>
  <label>
    <input type="checkbox" name="area_parts[]" value="4.5">
    <?php echo htmlspecialchars($teks['area_kepala_anterior']); ?>
  </label>
  <label>
    <input type="checkbox" name="area_parts[]" value="4.5">
    <?php echo htmlspecialchars($teks['area_kepala_posterior']); ?>
  </label>
  <label>
    <input type="checkbox" name="area_parts[]" value="9">
    <?php echo htmlspecialchars($teks['area_dada']); ?>
  </label>
  <label>
    <input type="checkbox" name="area_parts[]" value="9">
    <?php echo htmlspecialchars($teks['area_perut']); ?>
  </label>
  <label>
    <input type="checkbox" name="area_parts[]" value="9">
    <?php echo htmlspecialchars($teks['area_punggung_anterior']); ?>
  </label>
  <label>
    <input type="checkbox" name="area_parts[]" value="9">
    <?php echo htmlspecialchars($teks['area_punggung_posterior']); ?>
  </label>
  <label>
    <input type="checkbox" name="area_parts[]" value="4.5">
    <?php echo htmlspecialchars($teks['area_lengan_kanan_anterior']); ?>
  </label>
  <label>
    <input type="checkbox" name="area_parts[]" value="4.5">
    <?php echo htmlspecialchars($teks['area_lengan_kanan_posterior']); ?>
  </label>
  <label>
    <input type="checkbox" name="area_parts[]" value="4.5">
    <?php echo htmlspecialchars($teks['area_lengan_kiri_anterior']); ?>
  </label>
  <label>
    <input type="checkbox" name="area_parts[]" value="4.5">
    <?php echo htmlspecialchars($teks['area_lengan_kiri_posterior']); ?>
  </label>
  <label>
    <input type="checkbox" name="area_parts[]" value="9">
    <?php echo htmlspecialchars($teks['area_kaki_kanan_anterior']); ?>
  </label>
  <label>
    <input type="checkbox" name="area_parts[]" value="9">
    <?php echo htmlspecialchars($teks['area_kaki_kanan_posterior']); ?>
  </label>
  <label>
    <input type="checkbox" name="area_parts[]" value="9">
    <?php echo htmlspecialchars($teks['area_kaki_kiri_anterior']); ?>
  </label>
  <label>
    <input type="checkbox" name="area_parts[]" value="9">
    <?php echo htmlspecialchars($teks['area_kaki_kiri_posterior']); ?>
  </label>
  <label>
    <input type="checkbox" name="area_parts[]" value="1">
    <?php echo htmlspecialchars($teks['area_perineum']); ?>
  </label>
</fieldset>

<label>
  <?php echo htmlspecialchars($teks['nama_lengkap']); ?>:
  <input type="text" name="nama" required>
</label>

<label>
  <?php echo htmlspecialchars($teks['alamat']); ?>:
  <input type="text" name="alamat" required>
</label>

<label>
  <?php echo htmlspecialchars($teks['usia']); ?>:
  <input type="number" name="usia" min="0" required>
</label>

<label>
  <?php echo htmlspecialchars($teks['berat_kg']); ?>:
  <input type="number" name="berat_kg" step="0.1" min="1" required>
</label>

<button type="submit"><?php echo htmlspecialchars($teks['hitung_lukabakar']); ?></button>

      </form>
    </div>
  </div>
</body>
</html>
