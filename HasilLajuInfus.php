<?php
require_once 'config/config.php';
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}

if (isset($_GET['id'])) {
  $id = $_GET['id'];
  $stmt = $pdo->prepare("SELECT * FROM laju_infus WHERE id = ?");
  $stmt->execute([$id]);
  $data = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$data) die("Data tidak ditemukan.");
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama_lengkap = $_POST['Nama_Lengkap'];
  $umur = $_POST['umur'];
  $alamat = $_POST['alamat'];
  $jenis = $_POST['jenis_infus'];
  $volume = $_POST['volume_ml'];
  $jam = $_POST['waktu_jam'];
  $mnt = $_POST['menit_opt'];
  $dosis_mg = $_POST['dosis_mg'];
  $konsentrasi = $_POST['konsentrasi'];

  $volume_obat = $dosis_mg / $konsentrasi;
  $total_menit = ($jam * 60) + $mnt;
  $laju_ml_jam = $volume / ($total_menit / 60);
  $laju_tetes_menit = ($volume / $total_menit) * 20;

  $stmt = $pdo->prepare("INSERT INTO laju_infus 
    (user_id, Nama_Lengkap, umur, alamat, jenis_infus, volume_ml, waktu_jam, menit_opt, dosis_mg, konsentrasi, volume_obat, laju_ml_jam, laju_tetes_menit)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->execute([
    $_SESSION['user']['id'], $nama_lengkap, $umur, $alamat,
    $jenis, $volume, $jam, $mnt, $dosis_mg, $konsentrasi,
    $volume_obat, $laju_ml_jam, $laju_tetes_menit
  ]);
  $id = $pdo->lastInsertId();
  $data = compact('Nama_Lengkap', 'umur', 'alamat', 'jenis', 'volume', 'jam', 'mnt', 'dosis_mg', 'konsentrasi', 'volume_obat', 'laju_ml_jam', 'laju_tetes_menit');
} else {
  $stmt = $pdo->query("SELECT * FROM laju_infus ORDER BY created_at DESC");
  $riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);

  if (isset($_GET['hapus'])) {
    $hapusId = $_GET['hapus'];
    $stmt = $pdo->prepare("DELETE FROM laju_infus WHERE id = ?");
    $stmt->execute([$hapusId]);
    header("Location: ?hapus_berhasil=1");
    exit;
  }

  ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Laju Infus</title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
  <style>
    .search-box {
      width: 100%;
      max-width: 300px;
      padding: 8px 12px;
      margin-bottom: 1rem;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
    }

    .table-responsive {
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 1rem;
    }

    th, td {
      padding: 10px;
      border: 1px solid #ccc;
      text-align: left;
    }

    th {
      background-color: #f8f8f8;
    }

    .pagination {
      display: flex;
      justify-content: center;
      gap: 8px;
      margin-bottom: 2rem;
    }

    .pagination button {
      padding: 6px 12px;
      border: none;
      background-color: #e0e0e0;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
    }

    .pagination button.active {
      background-color: #007bff;
      color: white;
    }

    .pagination button:hover:not(.active) {
      background-color: #ccc;
    }
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
            <td><?= $row['umur'] ?></td>
            <td><?= round($row['laju_tetes_menit'], 2) ?></td>
            <td>
  <a href="?id=<?= $row['id'] ?>">Lihat</a> |
  <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
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
const table = document.getElementById("riwayatTable");
const tbody = document.getElementById("tableBody");
const rows = Array.from(tbody.querySelectorAll("tr"));
const pagination = document.getElementById("pagination");

function displayTable() {
  const start = (currentPage - 1) * rowsPerPage;
  const end = start + rowsPerPage;
  rows.forEach((row, index) => {
    row.style.display = (index >= start && index < end) ? "" : "none";
  });
  renderPagination();
}

function renderPagination() {
  const totalPages = Math.ceil(rows.length / rowsPerPage);
  pagination.innerHTML = "";
  for (let i = 1; i <= totalPages; i++) {
    const btn = document.createElement("button");
    btn.textContent = i;
    if (i === currentPage) btn.classList.add("active");
    btn.onclick = () => {
      currentPage = i;
      displayTable();
    };
    pagination.appendChild(btn);
  }
}

function filterTable() {
  const input = document.getElementById("searchInput").value.toLowerCase();
  rows.forEach(row => {
    const nama = row.cells[0].textContent.toLowerCase();
    const umur = row.cells[1].textContent.toLowerCase();
    row.style.display = (nama.includes(input) || umur.includes(input)) ? "" : "none";
  });

  // Reset current page and visible rows for search
  currentPage = 1;
  displayTable();
}

displayTable();
</script>

</body>
</html>


  <?php exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Hasil Laju Infus</title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
</head>
<body>

<a class="back" href="?">« BACK</a>
<div class="main-container">
  <div class="content-text">
    <h6>Kalkulator</h6>
    <h2>Detail Perhitungan Laju Infus</h2>
  </div>

  <div class="form-box">
    <p class="result-hjk"><strong>Nama:</strong> <?= htmlspecialchars($data['Nama_Lengkap']) ?></p>
    <p class="result-hjk"><strong>Umur:</strong> <?= $data['umur'] ?> tahun</p>
    <p class="result-hjk"><strong>Alamat:</strong> <?= htmlspecialchars($data['alamat']) ?></p>
    <hr>
    <p class="result-hjk"><strong>Volume Cairan:</strong> <?= $data['volume_ml'] ?> mL</p>
    <p class="result-hjk"><strong>Dosis Obat:</strong> <?= $data['dosis_mg'] ?> mg</p>
    <p class="result-hjk"><strong>Volume Obat:</strong> <?= round($data['volume_obat'], 2) ?> mL</p>
    <p class="result-hjk"><strong>Laju Infus:</strong> <?= round($data['laju_tetes_menit'], 2) ?> tetes/menit</p>

    <a href="?">
      <button class="button-result">Kembali ke Riwayat</button>
    </a>
  </div>
</div>

</body>
</html>

