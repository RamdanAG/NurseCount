<?php
// LajuInfus.php: Form Laju Infus dengan dukungan multibahasa
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lang/lang.php';

// Mulai session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect ke login jika belum autentikasi
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Ambil preferensi bahasa dan array teks
$user = $_SESSION['user'];
$lang = $user['lang'] ?? 'id';
$teks = ($lang === 'en') ? $bahasa_en : $bahasa_id;
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang); ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?php echo htmlspecialchars($teks['judul']); ?></title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
</head>
<body>
  <a class="back" href="index.php"><?php echo htmlspecialchars($teks['back']); ?></a>

  <div class="main-container">
    <div class="content-text">
      <h6><?php echo htmlspecialchars($teks['kalkulator']); ?></h6>
      <h2><?php echo htmlspecialchars($teks['judul']); ?></h2>
    </div>

    <div class="form-box">
      <form action="HasilLajuInfus.php" method="POST">
        <label class="text-label"><?php echo htmlspecialchars($teks['jenis_infus']); ?>:
          <div class="radio-group">
            <label><input type="radio" name="jenis_infus" value="mikrodrip" required>
              <?php echo htmlspecialchars($teks['mikrodrip']); ?>
            </label><br>
            <label><input type="radio" name="jenis_infus" value="makrodrip" required>
              <?php echo htmlspecialchars($teks['makrodrip']); ?>
            </label>
          </div>
        </label>

        <label><?php echo htmlspecialchars($teks['nama_lengkap']); ?>:
          <input type="text" name="Nama_Lengkap" required>
        </label>

        <label><?php echo htmlspecialchars($teks['umur']); ?>:
          <input type="number" name="umur" required>
        </label>

        <label><?php echo htmlspecialchars($teks['alamat']); ?>:
          <input type="text" name="alamat" required>
        </label>

        <label><?php echo htmlspecialchars($teks['volume']); ?>:
          <input type="number" name="volume_ml" required>
        </label>

        <label><?php echo htmlspecialchars($teks['waktu_jam']); ?>:
          <input type="number" step="0.1" name="waktu_jam" required>
        </label>

        <label><?php echo htmlspecialchars($teks['menit_opt']); ?>:
          <input type="number" name="menit_opt">
        </label>

        <label><?php echo htmlspecialchars($teks['dosis_mg']); ?>:
          <input type="number" name="dosis_mg" step="0.01">
        </label>

        <label><?php echo htmlspecialchars($teks['konsentrasi']); ?>:
          <input type="number" name="konsentrasi" step="0.01">
        </label>

        <button type="submit"><?php echo htmlspecialchars($teks['submit']); ?></button>
      </form>
    </div>
  </div>
</body>
</html>