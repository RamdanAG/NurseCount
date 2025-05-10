<?php
// KebutuhanKalori.php: Form Kebutuhan Kalori dengan dukungan multibahasa
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
  <title><?php echo htmlspecialchars($teks['judul_kalori']); ?></title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
</head>
<body>
  <a class="back" href="index.php"><?php echo htmlspecialchars($teks['back']); ?></a>

  <div class="main-container">
    <div class="content-text">
      <h6><?php echo htmlspecialchars($teks['kalkulator']); ?></h6>
      <h2><?php echo htmlspecialchars($teks['judul_kalori']); ?></h2>
    </div>

    <div class="form-box">
      <form action="HasilKebutuhanKalori.php" method="POST">
        <label><?php echo htmlspecialchars($teks['nama_lengkap']); ?>:
          <input type="text" name="nama_lengkap" required>
        </label>

        <label><?php echo htmlspecialchars($teks['alamat']); ?>:
          <input type="text" name="alamat" required>
        </label>

        <label><?php echo htmlspecialchars($teks['jenis_kelamin']); ?>:
          <select name="jenis_kelamin" required>
            <option value=""><?php echo htmlspecialchars($teks['pilih']); ?></option>
            <option value="Laki-laki"><?php echo htmlspecialchars($teks['laki_laki']); ?></option>
            <option value="Perempuan"><?php echo htmlspecialchars($teks['perempuan']); ?></option>
          </select>
        </label>

        <label><?php echo htmlspecialchars($teks['usia']); ?>:
          <input type="number" name="usia" min="1" required>
        </label>

        <label><?php echo htmlspecialchars($teks['berat_kg']); ?>:
          <input type="number" name="berat_kg" step="0.1" min="1" required>
        </label>

        <label><?php echo htmlspecialchars($teks['tinggi_cm']); ?>:
          <input type="number" name="tinggi_cm" step="0.1" min="50" required>
        </label>

        <label><?php echo htmlspecialchars($teks['aktivitas']); ?>:
          <select name="aktivitas" required>
            <option value=""><?php echo htmlspecialchars($teks['pilih']); ?></option>
            <option value="1.2"><?php echo htmlspecialchars($teks['aktivitas_1_2']); ?></option>
            <option value="1.375"><?php echo htmlspecialchars($teks['aktivitas_1_375']); ?></option>
            <option value="1.55"><?php echo htmlspecialchars($teks['aktivitas_1_55']); ?></option>
            <option value="1.725"><?php echo htmlspecialchars($teks['aktivitas_1_725']); ?></option>
            <option value="1.9"><?php echo htmlspecialchars($teks['aktivitas_1_9']); ?></option>
          </select>
        </label>

        <label><?php echo htmlspecialchars($teks['stres']); ?>:
          <select name="stres" required>
            <option value=""><?php echo htmlspecialchars($teks['pilih']); ?></option>
            <option value="1.1"><?php echo htmlspecialchars($teks['stres_1_1']); ?></option>
            <option value="1.3"><?php echo htmlspecialchars($teks['stres_1_3']); ?></option>
            <option value="1.75"><?php echo htmlspecialchars($teks['stres_1_75']); ?></option>
          </select>
        </label>

        <button type="submit"><?php echo htmlspecialchars($teks['hitung']); ?></button>
      </form>
    </div>
  </div>
</body>
</html>
