<?php
// HasilLajuInfus.php

// Aktifkan error reporting untuk development (hapus di production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Ambil preferensi bahasa dan teks
$user = $_SESSION['user'];
$lang = $user['lang'] ?? 'id';
$teks = ($lang === 'en') ? $bahasa_en : $bahasa_id;

// Hapus data jika parameter 'hapus' ada (tanpa filter user_id)
if (isset($_GET['hapus'])) {
    $hapusId = (int)$_GET['hapus'];
    $pdo->prepare("DELETE FROM laju_infus WHERE id = ?")
        ->execute([$hapusId]);
    header("Location: HasilLajuInfus.php?hapus_berhasil=1");
    exit;
}

// Detail view jika parameter 'id' ada (tanpa filter user_id)
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM laju_infus WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$data) {
        die(htmlspecialchars($teks['data_tidak_ditemukan']));
    }
    ?>
    <!DOCTYPE html>
    <html lang="<?= htmlspecialchars($lang) ?>">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width,initial-scale=1.0">
      <title><?= htmlspecialchars($teks['detail_laju_infus']) ?></title>
      <link rel="stylesheet" href="public/style/root.css">
      <link rel="stylesheet" href="public/style/form.css">
    </head>
    <body>
    <a class="back" href="HasilLajuInfus.php"><button class="back-button"><</button></a>
      <div class="main-container">
        <div class="content-text">
          <h6><?= htmlspecialchars($teks['kalkulator']) ?></h6>
          <h2><?= htmlspecialchars($teks['detail_laju_infus']) ?></h2>
        </div>
        <div class="form-box">
          <p class="result-hjk"><strong><?= htmlspecialchars($teks['jenis_infus']) ?>:</strong> <?= htmlspecialchars($data['jenis_infus']) ?></p>
          <p class="result-hjk"><strong><?= htmlspecialchars($teks['nama_lengkap']) ?>:</strong> <?= htmlspecialchars($data['Nama_Lengkap']) ?></p>
          <p class="result-hjk"><strong><?= htmlspecialchars($teks['umur']) ?>:</strong> <?= htmlspecialchars($data['umur']) ?> <?= htmlspecialchars($teks['tahun'] ?? '') ?></p>
          <p class="result-hjk"><strong><?= htmlspecialchars($teks['alamat']) ?>:</strong> <?= htmlspecialchars($data['alamat']) ?></p>
          <hr>
          <p class="result-hjk"><strong><?= htmlspecialchars($teks['volume']) ?>:</strong> <?= htmlspecialchars($data['volume_ml']) ?> mL</p>
          <p class="result-hjk"><strong><?= htmlspecialchars($teks['waktu_jam']) ?>:</strong>
            <?= htmlspecialchars($data['waktu_jam']) ?> <?= htmlspecialchars($teks['jam'] ?? '') ?>
            <?= htmlspecialchars($data['menit_opt']) ?> <?= htmlspecialchars($teks['menit'] ?? '') ?>
          </p>
          <hr>
          <p class="result-hjk"><strong><?= htmlspecialchars($teks['dosis_mg']) ?>:</strong> <?= htmlspecialchars($data['dosis_mg']) ?> mg</p>
          <p class="result-hjk"><strong><?= htmlspecialchars($teks['konsentrasi']) ?>:</strong> <?= htmlspecialchars($data['konsentrasi']) ?> mg/mL</p>
          <p class="result-hjk"><strong><?= htmlspecialchars($teks['volume_obat']) ?>:</strong> <?= round($data['volume_obat'], 2) ?> mL</p>
          <hr>
          <p class="result-hjk"><strong><?= htmlspecialchars($teks['laju_ml_jam']) ?>:</strong> <?= round($data['laju_ml_jam'], 2) ?> mL/jam</p>
          <p class="result-hjk"><strong><?= htmlspecialchars($teks['laju_tetes']) ?>:</strong> <?= round($data['laju_tetes_menit'], 2) ?> <?= htmlspecialchars($teks['tetes_per_menit'] ?? '') ?></p>
          <a href="HasilLajuInfus.php"><button class="button-result"><?= htmlspecialchars($teks['kembali']) ?></button></a>
        </div>
      </div>
    </body>
    </html>
    <?php
    exit;
}

// Simpan data baru jika request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis        = $_POST['jenis_infus'];
    $nama         = trim($_POST['Nama_Lengkap']);
    $umur         = (int) $_POST['umur'];
    $alamat       = trim($_POST['alamat']);
    $volume_ml    = (float) $_POST['volume_ml'];
    $waktu_jam    = (float) $_POST['waktu_jam'];
    $menit_opt    = (int)   ($_POST['menit_opt'] ?? 0);
    $dosis_mg     = (float) ($_POST['dosis_mg'] ?? 0);
    $konsentrasi  = (float) ($_POST['konsentrasi'] ?? 0);

    // Hitung volume obat, laju ml/jam, dan laju tetes
    $volume_obat      = $konsentrasi > 0 ? $dosis_mg / $konsentrasi : 0;
    $total_hours      = $waktu_jam + ($menit_opt / 60);
    $ml_per_hour      = $total_hours > 0 ? $volume_ml / $total_hours : 0;
    $faktor           = ($jenis === 'makrodrip') ? 20 : 60;
    $laju_tetes_menit = ($ml_per_hour / 60) * $faktor;

    // Insert ke DB, lengkap dengan laju_ml_jam
    $stmt = $pdo->prepare("INSERT INTO laju_infus
        (user_id, jenis_infus, Nama_Lengkap, umur, alamat,
         volume_ml, waktu_jam, menit_opt,
         dosis_mg, konsentrasi,
         volume_obat, laju_ml_jam, laju_tetes_menit, created_at)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $stmt->execute([
      $_SESSION['user']['id'],
      $jenis, $nama, $umur, $alamat,
      $volume_ml, $waktu_jam, $menit_opt,
      $dosis_mg, $konsentrasi,
      $volume_obat, $ml_per_hour, $laju_tetes_menit
    ]);

    header("Location: HasilLajuInfus.php");
    exit;
}

// Tampilkan riwayat (semua user)
$stmt   = $pdo->query("SELECT * FROM laju_infus ORDER BY created_at DESC");
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?= htmlspecialchars($teks['riwayat_laju_infus']) ?></title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
  <style>
    .search-box { width:100%; max-width:300px; padding:8px; margin-bottom:1rem; border:1px solid #ccc; border-radius:8px; }
    .table-responsive { overflow-x:auto; }
    table { width:100%; border-collapse:collapse; margin-bottom:1rem; }
    th, td { padding:10px; border:1px solid #ccc; text-align:left; }
    th { background:#f8f8f8; }
    .pagination { display:flex; justify-content:center; gap:8px; margin-bottom:2rem; }
    .pagination button { padding:6px 12px; border:none; background:#e0e0e0; border-radius:4px; cursor:pointer; }
    .pagination button.active { background:#007bff; color:#fff; }
    a.delete { color:red; margin-left:8px; }
  </style>
</head>
<body>
  <a class="back" href="index.php"><button class="back-button"><</button></a>
  <div class="main-container">
    <div class="content-text">
      <h6><?= htmlspecialchars($teks['kalkulator']) ?></h6>
      <h2><?= htmlspecialchars($teks['riwayat_laju_infus']) ?></h2>
    </div>
    <div class="form-box">
      <?php if (isset($_GET['hapus_berhasil'])): ?>
        <p style="color:green"><?= htmlspecialchars($teks['data_berhasil_dihapus']) ?></p>
      <?php endif;?>


      <div class="table-responsive">
        <table id="riwayatTable">
          <thead>
            <tr>
              <th><?= htmlspecialchars($teks['jenis_infus']) ?></th>
              <th><?= htmlspecialchars($teks['nama_lengkap']) ?></th>
              <th><?= htmlspecialchars($teks['volume']) ?></th>
              <th><?= htmlspecialchars($teks['laju_ml_jam']) ?></th>
              <th><?= htmlspecialchars($teks['laju_tetes']) ?></th>
              <th><?= htmlspecialchars($teks['aksi']) ?></th>
            </tr>
          </thead>
          <tbody id="tableBody">
            <?php foreach ($riwayat as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['jenis_infus']) ?></td>
                <td><?= htmlspecialchars($row['Nama_Lengkap']) ?></td>
                <td><?= htmlspecialchars($row['volume_ml']) ?> mL</td>
                <td><?= round($row['laju_ml_jam'], 2) ?> mL/jam</td>
                <td><?= round($row['laju_tetes_menit'], 2) ?></td>
                <td>
                  <a href="HasilLajuInfus.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($teks['lihat']) ?></a>
                  <a href="HasilLajuInfus.php?hapus=<?= $row['id'] ?>" class="delete" onclick="return confirm('<?= htmlspecialchars($teks['confirm_hapus']) ?>')"><?= htmlspecialchars($teks['hapus']) ?></a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>

