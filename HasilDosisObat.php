<?php
require_once __DIR__ . '/config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// 1) Hapus data jika diminta
if (isset($_GET['hapus'])) {
  $hapusId = (int)$_GET['hapus'];
  $stmt = $pdo->prepare("DELETE FROM dosage_data WHERE id = ?");
  $stmt->execute([$hapusId]);
  header("Location: HasilDosisObat.php?hapus_berhasil=1");
  exit;
}

// 2) Detail view jika ada id
if (isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $stmt = $pdo->query("SELECT * FROM dosage_data ORDER BY created_at DESC");
  $riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $data = null;
  foreach ($riwayat as $row) {
    if ((int)$row['id'] === $id) {
      $data = $row;
      break;
    }
  }
  if (!$data) die("Data tidak ditemukan.");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Dosis Obat</title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
</head>
<body>
<a class="back" href="HasilDosisObat.php">« BACK</a>
<div class="main-container">
  <div class="content-text">
    <h6>Kalkulator</h6>
    <h2>Detail Dosis Obat</h2>
  </div>
  <div class="form-box">
    <p class="result-hjk"><strong>Nama:</strong> <?= htmlspecialchars($data['nama']) ?></p>
    <p class="result-hjk"><strong>Umur:</strong> <?= htmlspecialchars($data['umur']) ?> tahun</p>
    <p class="result-hjk"><strong>Alamat:</strong> <?= htmlspecialchars($data['alamat']) ?></p>
    <hr>
    <p class="result-hjk"><strong>Berat Badan:</strong> <?= htmlspecialchars($data['berat_kg']) ?> kg</p>
    <p class="result-hjk"><strong>Tinggi Badan:</strong> <?= htmlspecialchars($data['tinggi_cm']) ?> cm</p>
    <p class="result-hjk"><strong>Aktivitas Factor:</strong> <?= htmlspecialchars($data['aktivitas_factor']) ?></p>
    <p class="result-hjk"><strong>Stress Factor:</strong> <?= htmlspecialchars($data['stress_factor']) ?></p>
    <hr>
    <p class="result-hjk"><strong>BMR:</strong> <?= round($data['bmr'], 2) ?> kkal/hari</p>
    <p class="result-hjk"><strong>Total Energi:</strong> <?= round($data['total_energi'], 2) ?> kkal/hari</p>
    <p class="result-hjk"><strong>Protein:</strong> <?= round($data['protein_g'], 2) ?> g</p>
    <p class="result-hjk"><strong>Karbohidrat:</strong> <?= round($data['karbohidrat_g'], 2) ?> g</p>
    <a href="HasilDosisObat.php">
      <button class="button-result">Kembali ke Riwayat</button>
    </a>
  </div>
</div>
</body>
</html>
<?php
  exit;
}

// 3) Simpan data baru jika POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama   = trim($_POST['nama_lengkap']);
  $umur   = (int)$_POST['umur'];
  $alamat = trim($_POST['alamat']);
  $berat  = (float)$_POST['berat_kg'];
  $tinggi = (float)$_POST['tinggi_cm'];
  $aktiv  = (float)$_POST['aktivitas'];
  $stress = (float)$_POST['stress'];

  $jk = $_SESSION['user']['gender'] ?? 'L';
  if ($jk === 'L') {
    $bmr = (10 * $berat) + (6.25 * $tinggi) - (5 * $umur) + 5;
  } else {
    $bmr = (10 * $berat) + (6.25 * $tinggi) - (5 * $umur) - 161;
  }

  $te = $bmr * $aktiv * $stress;
  $protein = ($te * 0.15) / 4;
  $karbo   = ($te * 0.55) / 4;

  $userId = $_SESSION['user']['id'];

  $stmt = $pdo->prepare("
    INSERT INTO dosage_data
      (user_id, nama, umur, alamat, berat_kg, tinggi_cm,
       aktivitas_factor, stress_factor,
       bmr, total_energi, protein_g, karbohidrat_g)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  ");
  $stmt->execute([
    $userId, $nama, $umur, $alamat,
    $berat, $tinggi, $aktiv, $stress,
    $bmr, $te, $protein, $karbo
  ]);
  

  header("Location: HasilDosisObat.php");
  exit;
}

// 4) Ambil semua data
$stmt = $pdo->query("SELECT * FROM dosage_data ORDER BY created_at DESC");
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Dosis Obat</title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
  <style>
    .search-box { width:100%;max-width:300px;padding:8px 12px;margin-bottom:1rem;border:1px solid #ccc;border-radius:8px;font-size:14px; }
    .table-responsive { overflow-x:auto; }
    table { width:100%;border-collapse:collapse;margin-bottom:1rem; }
    th, td { padding:10px;border:1px solid #ccc;text-align:left; }
    th { background-color:#f8f8f8; }
    .pagination { display:flex;justify-content:center;gap:8px;margin-bottom:2rem; }
    .pagination button { padding:6px 12px;border:none;background-color:#e0e0e0;border-radius:4px;cursor:pointer;font-size:14px; }
    .pagination button.active { background-color:#007bff;color:white; }
    .pagination button:hover:not(.active){background-color:#ccc;}
    a.delete { color:red;margin-left:8px; }
  </style>
</head>
<body>
<a class="back" href="index.php">« BACK</a>
<div class="main-container">
  <div class="content-text">
    <h6>Kalkulator</h6>
    <h2>Riwayat Dosis Obat</h2>
  </div>
  <div class="form-box">
    <?php if (isset($_GET['hapus_berhasil'])): ?>
      <p class="result-hjk" style="color:green">Data berhasil dihapus.</p>
    <?php endif; ?>

    <input type="text" id="searchInput" class="search-box" placeholder="Cari nama..." onkeyup="filterTable()">

    <div class="table-responsive">
      <table id="riwayatTable">
        <thead>
          <tr>
            <th>Nama</th>
            <th>Total Energi</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <?php foreach ($riwayat as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['nama']) ?></td>
              <td><?= round($row['total_energi'],2) ?> kkal</td>
              <td>
                <a href="HasilDosisObat.php?id=<?= $row['id'] ?>">Lihat</a>
                <a href="HasilDosisObat.php?hapus=<?= $row['id'] ?>" class="delete" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
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
const rowsPerPage = 6, tbody = document.getElementById("tableBody"),
      rows = Array.from(tbody.querySelectorAll("tr")), pagination = document.getElementById("pagination");

function displayTable() {
  const start=(currentPage-1)*rowsPerPage, end=start+rowsPerPage;
  rows.forEach((r,i)=>r.style.display=(i>=start&&i<end)?"":"none");
  renderPagination();
}
function renderPagination(){
  const total=rows.length,pages=Math.ceil(total/rowsPerPage);
  pagination.innerHTML="";
  for(let i=1;i<=pages;i++){
    const btn=document.createElement("button");btn.textContent=i;
    if(i===currentPage)btn.classList.add("active");
    btn.onclick=()=>{currentPage=i;displayTable();};
    pagination.appendChild(btn);
  }
}
function filterTable(){
  const q=document.getElementById("searchInput").value.toLowerCase();
  rows.forEach(r=>{
    const n=r.cells[0].textContent.toLowerCase();
    r.style.display=n.includes(q)?"":"none";
  });
  currentPage=1;displayTable();
}
let currentPage=1;displayTable();
</script>
</body>
</html>
