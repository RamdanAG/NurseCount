<?php
require_once __DIR__ . '/config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}
$user_id = $_SESSION['user']['id'];

// Hapus data jika diminta
if (isset($_GET['hapus'])) {
  $hapusId = (int)$_GET['hapus'];
  $stmt = $pdo->prepare("DELETE FROM hasil_lukabakar WHERE id = ? AND user_id = ?");
  $stmt->execute([$hapusId, $user_id]);
  header("Location: HasilLukaBakar.php?hapus_berhasil=1");
  exit;
}

// Detail view jika ada id
if (isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $stmt = $pdo->prepare("SELECT * FROM hasil_lukabakar WHERE id = ? AND user_id = ?");
  $stmt->execute([$id, $user_id]);
  $data = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$data) die("Data tidak ditemukan.");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Perhitungan Luka Bakar</title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
</head>
<body>

<a class="back" href="HasilLukaBakar.php">« BACK</a>
<div class="main-container">
  <div class="content-text">
    <h6>Kalkulator</h6>
    <h2>Detail Luka Bakar</h2>
  </div>

  <div class="form-box">
    <p class="result-hjk"><strong>TBSA:</strong> <?= htmlspecialchars($data['tbsa']) ?>%</p>
    <p class="result-hjk"><strong>Total Cairan:</strong> <?= htmlspecialchars(number_format($data['total_cairan'])) ?> mL</p>
    <p class="result-hjk"><strong>8 jam pertama:</strong> <?= htmlspecialchars(number_format($data['cairan_8jam'])) ?> mL</p>
    <p class="result-hjk"><strong>16 jam berikutnya:</strong> <?= htmlspecialchars(number_format($data['cairan_16jam'])) ?> mL</p>
    <hr>
    <p class="result-hjk"><strong>Berat Badan:</strong> <?= htmlspecialchars($data['berat_kg']) ?> kg</p>
    <p class="result-hjk"><strong>Usia:</strong> <?= htmlspecialchars($data['usia']) ?> tahun</p>
    <?php if ($data['foto']): ?>
      <p class="result-hjk"><strong>Foto:</strong></p>
      <img src="public/image/LukaBakar/<?= htmlspecialchars($data['foto']) ?>" alt="Foto Luka" style="max-width:100%;height:auto;">
    <?php endif; ?>

    <a href="HasilLukaBakar.php">
      <button class="button-result">Kembali ke Riwayat</button>
    </a>
  </div>
</div>

</body>
</html>
<?php
  exit;
}

// Simpan data baru jika POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $parts      = $_POST['area_parts'] ?? [];
  $berat_kg   = (float)$_POST['berat_kg'];
  $usia       = (int)$_POST['usia'];

  // Hitung TBSA
  $tbsa       = array_sum(array_map('floatval', $parts));
  // Rumus Parkland
  $total      = round(4 * $berat_kg * $tbsa, 0);
  $c8         = round($total/2, 0);
  $c16        = $total - $c8;

  // Upload gambar
  $uploadDir  = __DIR__.'/public/image/LukaBakar/';
  if (!is_dir($uploadDir)) mkdir($uploadDir,0755,true);
  $fotoNama   = time().'_'.basename($_FILES['gambar']['name']);
  move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadDir.$fotoNama);

  // Simpan ke DB
  $stmt = $pdo->prepare("
    INSERT INTO hasil_lukabakar 
      (user_id, tbsa, total_cairan, cairan_8jam, cairan_16jam, berat_kg, usia, foto)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
  ");
  $stmt->execute([
    $user_id, $tbsa, $total, $c8, $c16, $berat_kg, $usia, $fotoNama
  ]);

  header("Location: HasilLukaBakar.php");
  exit;
}

// Jika bukan POST & bukan detail, tampilkan history
$stmt = $pdo->prepare("SELECT * FROM hasil_lukabakar WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Luka Bakar</title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
  <style>
    .search-box {
      width: 100%; max-width: 300px; padding: 8px 12px;
      margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 8px;
      font-size: 14px;
    }
    .table-responsive { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
    th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
    th { background-color: #f8f8f8; }
    .pagination { display: flex; justify-content: center; gap: 8px; margin-bottom: 2rem; }
    .pagination button {
      padding: 6px 12px; border: none; background-color: #e0e0e0;
      border-radius: 4px; cursor: pointer; font-size: 14px;
    }
    .pagination button.active { background-color: #007bff; color: white; }
    .pagination button:hover:not(.active) { background-color: #ccc; }
    a.delete { color: red; margin-left: 8px; }
  </style>
</head>
<body>

<a class="back" href="index.php">« BACK</a>
<div class="main-container">
  <div class="content-text">
    <h6>Kalkulator</h6>
    <h2>Riwayat Luka Bakar</h2>
  </div>

  <div class="form-box">
    <?php if (isset($_GET['hapus_berhasil'])): ?>
      <p class="result-hjk" style="color:green">Data berhasil dihapus.</p>
    <?php endif; ?>

    <input type="text" id="searchInput" class="search-box" placeholder="Cari TBSA atau usia..." onkeyup="filterTable()">

    <div class="table-responsive">
      <table id="riwayatTable">
        <thead>
          <tr>
            <th>TBSA (%)</th>
            <th>Usia</th>
            <th>Total Cairan (mL)</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <?php foreach ($riwayat as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['tbsa']) ?></td>
              <td><?= htmlspecialchars($row['usia']) ?></td>
              <td><?= htmlspecialchars(number_format($row['total_cairan'])) ?></td>
              <td>
                <a href="HasilLukaBakar.php?id=<?= $row['id'] ?>">Lihat</a>
                <a href="HasilLukaBakar.php?hapus=<?= $row['id'] ?>" class="delete" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
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
const tbody      = document.getElementById("tableBody");
const rows       = Array.from(tbody.querySelectorAll("tr"));
const pagination = document.getElementById("pagination");

function displayTable() {
  const start = (currentPage - 1) * rowsPerPage;
  const end   = start + rowsPerPage;
  rows.forEach((row,i) => row.style.display = (i>=start && i<end) ? "" : "none");
  renderPagination();
}

function renderPagination() {
  const pages = Math.ceil(rows.length / rowsPerPage);
  pagination.innerHTML = "";
  for (let i=1; i<=pages; i++) {
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
    const tbsa = row.cells[0].textContent.toLowerCase();
    const usia = row.cells[1].textContent.toLowerCase();
    row.style.display = (tbsa.includes(q) || usia.includes(q)) ? "" : "none";
  });
  currentPage = 1;
  displayTable();
}

displayTable();
</script>

</body>
</html>
