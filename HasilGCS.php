<?php
require_once __DIR__ . '/config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}
$user_id = $_SESSION['user']['id'];

// Simpan data baru (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama    = trim($_POST['nama_lengkap']);
  $umur    = (int)$_POST['umur'];
  $alamat  = trim($_POST['alamat']);
  $jk      = $_POST['jenis_kelamin'];
  $eye     = (int)$_POST['eye'];
  $verbal  = (int)$_POST['verbal'];
  $motor   = (int)$_POST['motor'];
  $total   = $eye + $verbal + $motor;

  // Interpretasi status
  if ($total >= 14)         $status = "Composmentis (Sadar Baik)";
  elseif ($total >= 12)     $status = "Apatis (Kurang Perhatian)";
  elseif ($total >= 10)     $status = "Delirium (Mudah Tidur)";
  elseif ($total >= 7)      $status = "Somnolen (Meracau/Gelisah)";
  elseif ($total >= 5)      $status = "Sopor (Respon Nyeri)";
  elseif ($total == 4)      $status = "Semi Coma (Sulit Dibangunkan)";
  else                      $status = "Coma (Tidak Ada Respon)";

  // Insert ke DB
  $stmt = $pdo->prepare("
    INSERT INTO gcs_data
      (user_id, nama_lengkap, umur, alamat, jenis_kelamin,
       skor_eye, skor_verbal, skor_motor, skor_total, status_kesadaran)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  ");
  $stmt->execute([
    $user_id, $nama, $umur, $alamat, $jk,
    $eye, $verbal, $motor, $total, $status
  ]);

  header("Location: HasilGCS.php");
  exit;
}

// Hapus data
if (isset($_GET['hapus'])) {
  $hid = (int)$_GET['hapus'];
  $pdo->prepare("DELETE FROM gcs_data WHERE id = ? AND user_id = ?")
      ->execute([$hid, $user_id]);
  header("Location: HasilGCS.php?hapus_berhasil=1");
  exit;
}

// Detail per ID
if (isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $stmt = $pdo->prepare("SELECT * FROM gcs_data WHERE id = ? AND user_id = ?");
  $stmt->execute([$id, $user_id]);
  $d = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$d) die("Data tidak ditemukan.");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail GCS</title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
</head>
<body>
<a class="back" href="HasilGCS.php">« BACK</a>
<div class="main-container">
  <div class="content-text">
    <h6>Kalkulator</h6>
    <h2>Detail Glasgow Coma Scale</h2>
  </div>
  <div class="form-box">
    <p class="result-hjk"><strong>Nama:</strong> <?= htmlspecialchars($d['nama_lengkap']) ?></p>
    <p class="result-hjk"><strong>Umur:</strong> <?= htmlspecialchars($d['umur']) ?> tahun</p>
    <p class="result-hjk"><strong>Alamat:</strong> <?= htmlspecialchars($d['alamat']) ?></p>
    <hr>
    <p class="result-hjk"><strong>Jenis Kelamin:</strong> <?= htmlspecialchars($d['jenis_kelamin']) ?></p>
    <p class="result-hjk"><strong>Skor Eye:</strong> <?= htmlspecialchars($d['skor_eye']) ?></p>
    <p class="result-hjk"><strong>Skor Verbal:</strong> <?= htmlspecialchars($d['skor_verbal']) ?></p>
    <p class="result-hjk"><strong>Skor Motorik:</strong> <?= htmlspecialchars($d['skor_motor']) ?></p>
    <p class="result-hjk"><strong>Total Skor:</strong> <?= htmlspecialchars($d['skor_total']) ?></p>
    <p class="result-hjk"><strong>Status:</strong> <?= htmlspecialchars($d['status_kesadaran']) ?></p>
    <a href="HasilGCS.php"><button class="button-result">Kembali ke Riwayat</button></a>
  </div>
</div>
</body>
</html>
<?php exit; } 

// Riwayat
$stmt = $pdo->prepare("SELECT * FROM gcs_data WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat GCS</title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
  <style>/* sama seperti template sebelumnya */</style>
</head>
<body>
<a class="back" href="index.php">« BACK</a>
<div class="main-container">
  <div class="content-text">
    <h6>Kalkulator</h6>
    <h2>Riwayat GCS</h2>
  </div>
  <div class="form-box">
    <?php if (isset($_GET['hapus_berhasil'])): ?>
      <p class="result-hjk" style="color:green">Data berhasil dihapus.</p>
    <?php endif; ?>
    <input id="searchInput" class="search-box" placeholder="Cari..." onkeyup="filterTable()">
    <div class="table-responsive">
      <table id="riwayatTable"><thead>
        <tr><th>Nama</th><th>Umur</th><th>Total</th><th>Aksi</th></tr>
      </thead><tbody id="tableBody">
        <?php foreach ($riwayat as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
          <td><?= htmlspecialchars($row['umur']) ?></td>
          <td><?= htmlspecialchars($row['skor_total']) ?></td>
          <td>
            <a href="?id=<?= $row['id'] ?>">Lihat</a> |
            <a href="?hapus=<?= $row['id'] ?>" class="delete" onclick="return confirm('Yakin?')">Hapus</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody></table>
    </div>
    <div class="pagination" id="pagination"></div>
  </div>
</div>
<script>/* pagination & filter sama */</script>
</body>
</html>
