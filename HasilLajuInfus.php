<?php
// templates/riwayat_laju_infus.php
require_once __DIR__ . '/config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}
$user_id = $_SESSION['user']['id'];

// 1) Hapus data jika diminta
if (isset($_GET['hapus'])) {
  $hapusId = (int)$_GET['hapus'];
  $stmt = $pdo->prepare("DELETE FROM laju_infus WHERE id = ? AND user_id = ?");
  $stmt->execute([$hapusId, $user_id]);
  header("Location: HasilLajuInfus.php?hapus_berhasil=1");
  exit;
}

// 2) Detail view jika ada id
if (isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $stmt = $pdo->prepare("SELECT * FROM laju_infus WHERE id = ? AND user_id = ?");
  $stmt->execute([$id, $user_id]);
  $data = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$data) die("Data tidak ditemukan.");
  // Tampilkan detail
  ?>
  <!DOCTYPE html>
  <html lang="id">
  <head>
    <meta charset="UTF-8">
    <title>Detail Laju Infus</title>
    <link rel="stylesheet" href="public/style/root.css">
    <link rel="stylesheet" href="public/style/form.css">
  </head>
  <body>
  <a class="back" href="HasilLajuInfus.php">« BACK</a>
  <div class="main-container">
    <div class="content-text">
      <h6>Kalkulator</h6>
      <h2>Detail Perhitungan Laju Infus</h2>
    </div>
    <div class="form-box">
      <p><strong>Nama:</strong> <?= htmlspecialchars($data['Nama_Lengkap']) ?></p>
      <p><strong>Umur:</strong> <?= htmlspecialchars($data['umur']) ?> tahun</p>
      <p><strong>Alamat:</strong> <?= htmlspecialchars($data['alamat']) ?></p>
      <hr>
      <p><strong>Volume Cairan:</strong> <?= htmlspecialchars($data['volume_ml']) ?> mL</p>
      <p><strong>Dosis Obat:</strong> <?= htmlspecialchars($data['dosis_mg']) ?> mg</p>
      <p><strong>Volume Obat:</strong> <?= round($data['volume_obat'], 2) ?> mL</p>
      <p><strong>Laju Infus:</strong> <?= round($data['laju_tetes_menit'], 2) ?> tetes/menit</p>
      <a href="HasilLajuInfus.php">
        <button class="button-result">Kembali ke Riwayat</button>
      </a>
    </div>
  </div>
  </body>
  </html>
  <?php
  exit;
}

// 3) Simpan data baru jika POST (HasilLajuInfus.php sebenarnya sudah handle POST dan insert)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  header("Location: HasilLajuInfus.php");
  exit;
}

// 4) Tampilkan riwayat
$stmt = $pdo->prepare("SELECT * FROM laju_infus WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Laju Infus</title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
  <style>
    .search-box { width:100%; max-width:300px; padding:8px 12px; margin-bottom:1rem; border:1px solid #ccc; border-radius:8px; font-size:14px; }
    .table-responsive { overflow-x:auto; }
    table { width:100%; border-collapse:collapse; margin-bottom:1rem; }
    th, td { padding:10px; border:1px solid #ccc; text-align:left; }
    th { background-color:#f8f8f8; }
    .pagination { display:flex; justify-content:center; gap:8px; margin-bottom:2rem; }
    .pagination button { padding:6px 12px; border:none; background-color:#e0e0e0; border-radius:4px; cursor:pointer; font-size:14px; }
    .pagination button.active { background-color:#007bff; color:white; }
    .pagination button:hover:not(.active){ background-color:#ccc; }
    a.delete { color:red; margin-left:8px; }
  </style>
</head>
<body>
<a class="back" href="index.php">« BACK</a>
<div class="main-container">
  <div class="content-text">
    <h6>Kalkulator</h6>
    <h2>Riwayat Laju Infus</h2>
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
            <th>Laju (tetes/menit)</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <?php foreach ($riwayat as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['Nama_Lengkap']) ?></td>
              <td><?= htmlspecialchars($row['umur']) ?></td>
              <td><?= round($row['laju_tetes_menit'], 2) ?></td>
              <td>
                <a href="HasilLajuInfus.php?id=<?= $row['id'] ?>">Lihat</a>
                <a href="HasilLajuInfus.php?hapus=<?= $row['id'] ?>" class="delete" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
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
let currentPage = 1;
const rowsPerPage = 6;
const tbody = document.getElementById("tableBody");
const rows  = Array.from(tbody.querySelectorAll("tr"));
const pagination = document.getElementById("pagination");

function displayTable() {
  const start = (currentPage-1)*rowsPerPage;
  const end   = start+rowsPerPage;
  rows.forEach((r,i)=> r.style.display = (i>=start && i<end) ? "" : "none");
  renderPagination();
}
function renderPagination() {
  const totalPages = Math.ceil(rows.length/rowsPerPage);
  pagination.innerHTML = "";
  for(let i=1;i<=totalPages;i++){
    const btn = document.createElement("button");
    btn.textContent = i;
    if(i===currentPage) btn.classList.add("active");
    btn.onclick = ()=> { currentPage = i; displayTable(); };
    pagination.appendChild(btn);
  }
}
function filterTable() {
  const q = document.getElementById("searchInput").value.toLowerCase();
  rows.forEach(r=>{
    const nama = r.cells[0].textContent.toLowerCase();
    const umur = r.cells[1].textContent.toLowerCase();
    r.style.display = (nama.includes(q)||umur.includes(q)) ? "" : "none";
  });
  currentPage = 1;
  displayTable();
}

displayTable();
</script>
</body>
</html>
