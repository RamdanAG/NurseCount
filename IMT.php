<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lang/lang.php';

// Mulai session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Ambil bahasa
$lang = $_SESSION['user']['lang'] ?? 'id';
$teks = ($lang === 'en') ? $bahasa_en : $bahasa_id;
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang); ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?php echo htmlspecialchars($teks['imt']); ?></title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
</head>
<body>
<a class="back" href="index.php"><button class="back-button"><</button></a>
  <div class="main-container">
    <div class="content-text">
      <h6><?php echo htmlspecialchars($teks['kalkulator']); ?></h6>
      <h2><?php echo htmlspecialchars($teks['imt']); ?></h2>
    </div>

    <div class="form-box">
      <form action="HasilIMT.php" method="POST">
        <label><?php echo htmlspecialchars($teks['jenis_kelamin']); ?>:
          <select name="jenis_kelamin" required>
            <option value=""><?php echo htmlspecialchars($teks['pilih']); ?></option>
            <option value="Laki-laki/Men">Laki-laki/Men</option>
            <option value="Perempuan/Female ">Perempuan/Female</option>
          </select>
        </label>

        <label><?php echo htmlspecialchars($teks['nama_lengkap']); ?>:
          <input type="text" name="nama_lengkap" required>
        </label>

        <label><?php echo htmlspecialchars($teks['umur']); ?>:
          <input type="number" name="umur" required>
        </label>

        <label><?php echo htmlspecialchars($teks['alamat']); ?>:
          <input type="text" name="alamat" required>
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

        <button type="submit"><?php echo htmlspecialchars($teks['submit_imt']); ?></button>
      </form>
    </div>
  </div>
</body>
</html>
