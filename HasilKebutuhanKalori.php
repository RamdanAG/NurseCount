<?php
require_once __DIR__ . '/config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}
$user_id = $_SESSION['user']['id'];

// — Hapus data jika diminta
if (isset($_GET['hapus'])) {
  $hapusId = (int)$_GET['hapus'];
  $stmt = $pdo->prepare("DELETE FROM hasil_kalori WHERE id = ? AND user_id = ?");
  $stmt->execute([$hapusId, $user_id]);
  header("Location: HasilKebutuhanKalori.php?hapus_berhasil=1");
  exit;
}

// — Detail view jika ada id
if (isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $stmt = $pdo->prepare("SELECT * FROM hasil_kalori WHERE id = ? AND user_id = ?");
  $stmt->execute([$id, $user_id]);
  $data = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$data) die("Data tidak ditemukan.");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Kebutuhan Kalori</title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
</head>
<body>

<a class="back" href="HasilKebutuhanKalori.php">« BACK</a>
<div class="main-container">
  <div class="content-text">
    <h6>Kalkulator</h6>
    <h2>Detail Kebutuhan Kalori</h2>
  </div>

  <div class="form-box">
    <p class="result-hjk"><strong>Nama:</strong> <?= htmlspecialchars($data['nama_lengkap']) ?></p>
    <p class="result-hjk"><strong>Umur:</strong> <?= htmlspecialchars($data['umur']) ?> tahun</p>
    <p class="result-hjk"><strong>Alamat:</strong> <?= htmlspecialchars($data['alamat']) ?></p>
    <hr>
    <p class="result-hjk"><strong>Jenis Kelamin:</strong> <?= htmlspecialchars($data['jenis_kelamin']) ?></p>
    <p class="result-hjk"><strong>Berat Badan:</strong> <?= htmlspecialchars($data['berat_kg']) ?> kg</p>
    <p class="result-hjk"><strong>Tinggi Badan:</strong> <?= htmlspecialchars($data['tinggi_cm']) ?> cm</p>
    <p class="result-hjk"><strong>Aktivitas:</strong> <?= htmlspecialchars($data['aktivitas']) ?></p>
    <p class="result-hjk"><strong>Stres:</strong> <?= htmlspecialchars($data['stres']) ?></p>
    <hr>
    <p class="result-hjk"><strong>BMR:</strong> <?= round($data['bmr'], 2) ?> kkal</p>
    <p class="result-hjk"><strong>Total Energi:</strong> <?= round($data['total_energi'], 2) ?> kkal/hari</p>
    <p class="result-hjk"><strong>Protein:</strong> <?= round($data['protein'], 2) ?> gram</p>
    <p class="result-hjk"><strong>Karbohidrat:</strong> <?= round($data['karbohidrat'], 2) ?> gram</p>

    <a href="HasilKebutuhanKalori.php">
      <button class="button-result">Kembali ke Riwayat</button>
    </a>
  </div>
</div>

</body>
</html>
<?php
  exit;
}

// — Simpan data baru jika POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama_lengkap  = trim($_POST['nama_lengkap']);
  $umur          = (int)$_POST['usia'];
  $alamat        = trim($_POST['alamat']);
  $jenis_kelamin = $_POST['jenis_kelamin'];
  $berat_kg      = (float)$_POST['berat_kg'];
  $tinggi_cm     = (float)$_POST['tinggi_cm'];
  $aktivitas     = (float)$_POST['aktivitas'];
  $stres         = (float)$_POST['stres'];

  // Hitung BMR (Harris-Benedict)
  if ($jenis_kelamin === "Laki-laki") {
    $bmr = 66 + (13.7 * $berat_kg) + (5 * $tinggi_cm) - (6.8 * $umur);
  } else {
    $bmr = 655 + (9.6 * $berat_kg) + (1.8 * $tinggi_cm) - (4.7 * $umur);
  }

  // Hitung total energi
  $total_energi = $bmr * $aktivitas * $stres;

  // Estimasi makronutrien
  $protein     = round(($berat_kg * 1.2), 2);
  $karbohidrat = round(($total_energi * 0.6) / 4, 2);

  // Simpan ke tabel hasil_kalori
  $stmt = $pdo->prepare("
    INSERT INTO hasil_kalori
      (user_id, nama_lengkap, umur, alamat, jenis_kelamin, berat_kg, tinggi_cm, aktivitas, stres, bmr, total_energi, protein, karbohidrat)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  ");
  $stmt->execute([
    $user_id,
    $nama_lengkap,
    $umur,
    $alamat,
    $jenis_kelamin,
    $berat_kg,
    $tinggi_cm,
    $aktivitas,
    $stres,
    $bmr,
    $total_energi,
    $protein,
    $karbohidrat
  ]);

  header("Location: HasilKebutuhanKalori.php");
  exit;
}

// — Tampilkan riwayat jika bukan POST & bukan detail
$stmt = $pdo->prepare("SELECT * FROM hasil_kalori WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Kebutuhan Kalori</title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
  <style>
    .search-box { width:100%; max-width:300px; padding:8px; margin-bottom:1rem; }
    .table-responsive { overflow-x:auto; }
    table { width:100%; border-collapse:collapse; margin-bottom:1rem; }
    th, td { padding:10px; border:1px solid #ccc; text-align:left; }
    th { background:#f8f8f8; }
    .pagination { display:flex; justify-content:center; gap:8px; margin-bottom:2rem; }
    .pagination button { padding:6px 12px; border:none; background:#e0e0e0; border-radius:4px; cursor:pointer; }
    .pagination button.active { background:#007bff; color:#fff; }
    .delete { color:red; margin-left:8px; }
  </style>
</head>
<body>

<a class="back" href="index.php">« BACK</a>
<div class="main-container">
  <div class="content-text">
    <h6>Kalkulator</h6>
    <h2>Riwayat Kebutuhan Kalori</h2>
  </div>

  <div class="form-box">
    <?php if (isset($_GET['hapus_berhasil'])): ?>
      <p class="result-hjk" style="color:green">Data berhasil dihapus.</p>
    <?php endif; ?>

    <input type="text" id="searchInput" class="search-box" placeholder="Cari nama atau umur..." onkeyup="filterTable()">

    <div class="table-responsive">
      <table id="riwayatTable">
        <thead>
          <tr>
            <th>Nama</th>
            <th>Umur</th>
            <th>Total Kalori</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <?php foreach ($riwayat as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
              <td><?= htmlspecialchars($row['umur']) ?></td>
              <td><?= round($row['total_energi'], 2) ?></td>
              <td>
                <a href="HasilKebutuhanKalori.php?id=<?= $row['id'] ?>">Lihat</a>
                <a href="HasilKebutuhanKalori.php?hapus=<?= $row['id'] ?>" class="delete" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="pagination" id="pagination"></div>
  </div>
</div>

<script>
const rowsPerPage = 6;
let currentPage = 1;
const tbody = document.getElementById("tableBody");
const rows = Array.from(tbody.querySelectorAll("tr"));
const pagination = document.getElementById("pagination");

function displayTable() {
  const start = (currentPage - 1) * rowsPerPage;
  const end = start + rowsPerPage;
  rows.forEach((row, i) => {
    row.style.display = (i >= start && i < end) ? "" : "none";
  });
  renderPagination();
}

function renderPagination() {
  const total = rows.length;
  const pages = Math.ceil(total / rowsPerPage);
  pagination.innerHTML = "";
  for (let i = 1; i <= pages; i++) {
    const btn = document.createElement("button");
    btn.textContent = i;
    if (i === currentPage) btn.classList.add("active");
    btn.onclick = () => { currentPage = i; displayTable(); };
    pagination.appendChild(btn);
  }
}

function filterTable() {
  const q = document.getElementById("searchInput").value.toLowerCase();
  rows.forEach(row => {
    const name = row.cells[0].textContent.toLowerCase();
    const age  = row.cells[1].textContent.toLowerCase();
    row.style.display = (name.includes(q) || age.includes(q)) ? "" : "none";
  });
  currentPage = 1;
  displayTable();
}

displayTable();
</script>

</body>
</html>
