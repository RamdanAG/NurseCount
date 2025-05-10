<?php
require_once __DIR__ . '/config/config.php';
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}
$user_id = $_SESSION['user']['id'];

// Hapus data jika diminta
if (isset($_GET['hapus'])) {
  $hapusId = (int)$_GET['hapus'];
  $stmt = $pdo->prepare("DELETE FROM hasil_imt WHERE id = ? AND user_id = ?");
  $stmt->execute([$hapusId, $user_id]);
  header("Location: HasilIMT.php?hapus_berhasil=1");
  exit;
}

// Detail view jika ada id
if (isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $stmt = $pdo->prepare("SELECT * FROM hasil_imt WHERE id = ? AND user_id = ?");
  $stmt->execute([$id, $user_id]);
  $data = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$data) die("Data tidak ditemukan.");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Perhitungan IMT</title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
</head>
<body>

<a class="back" href="HasilIMT.php">« BACK</a>
<div class="main-container">
  <div class="content-text">
    <h6>Kalkulator</h6>
    <h2>Detail Perhitungan IMT</h2>
  </div>

  <div class="form-box">
    <p class="result-hjk"><strong>Nama:</strong> <?= htmlspecialchars($data['nama_lengkap']) ?></p>
    <p class="result-hjk"><strong>Umur:</strong> <?= htmlspecialchars($data['umur']) ?> tahun</p>
    <p class="result-hjk"><strong>Alamat:</strong> <?= htmlspecialchars($data['alamat']) ?></p>
    <hr>
    <p class="result-hjk"><strong>Jenis Kelamin:</strong> <?= htmlspecialchars($data['jenis_kelamin']) ?></p>
    <p class="result-hjk"><strong>Usia:</strong> <?= htmlspecialchars($data['usia']) ?> tahun</p>
    <p class="result-hjk"><strong>Berat Badan:</strong> <?= htmlspecialchars($data['berat_kg']) ?> kg</p>
    <p class="result-hjk"><strong>Tinggi Badan:</strong> <?= htmlspecialchars($data['tinggi_cm']) ?> cm</p>
    <p class="result-hjk"><strong>IMT:</strong> <?= htmlspecialchars($data['imt']) ?></p>
    <p class="result-hjk"><strong>Kategori:</strong> <?= htmlspecialchars($data['kategori']) ?></p>

    <a href="HasilIMT.php">
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
  $nama_lengkap  = trim($_POST['nama_lengkap']);
  $umur          = (int)$_POST['umur'];
  $alamat        = trim($_POST['alamat']);
  $jenis_kelamin = $_POST['jenis_kelamin'];
  $usia          = (int)$_POST['usia'];
  $berat_kg      = (float)$_POST['berat_kg'];
  $tinggi_cm     = (float)$_POST['tinggi_cm'];

  // Hitung IMT
  $tinggi_m = $tinggi_cm / 100;
  $imt = round($berat_kg / ($tinggi_m * $tinggi_m), 2);
  // Tentukan kategori
  if ($imt < 16)             $kategori = "Sangat Kurus";
  elseif ($imt <= 18.5)      $kategori = "Kurus";
  elseif ($imt <= 25)        $kategori = "Berat Badan Normal";
  elseif ($imt <= 30)        $kategori = "Kelebihan Berat Badan";
  elseif ($imt <= 35)        $kategori = "Obesitas Kelas 1";
  elseif ($imt <= 40)        $kategori = "Obesitas Kelas 2";
  else                       $kategori = "Obesitas Morbid";

  $stmt = $pdo->prepare("
    INSERT INTO hasil_imt 
      (user_id, nama_lengkap, umur, alamat, jenis_kelamin, usia, berat_kg, tinggi_cm, imt, kategori)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  ");
  $stmt->execute([
    $user_id,
    $nama_lengkap,
    $umur,
    $alamat,
    $jenis_kelamin,
    $usia,
    $berat_kg,
    $tinggi_cm,
    $imt,
    $kategori
  ]);

  header("Location: HasilIMT.php");
  exit;
}

// Jika bukan POST & bukan detail, tampilkan history
$stmt = $pdo->prepare("SELECT * FROM hasil_imt WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat IMT</title>
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
    a.delete {
      color: red;
      margin-left: 8px;
    }
  </style>
</head>
<body>

<a class="back" href="index.php">« BACK</a>
<div class="main-container">
  <div class="content-text">
    <h6>Kalkulator</h6>
    <h2>Riwayat IMT</h2>
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
            <th>IMT</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <?php foreach ($riwayat as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
              <td><?= htmlspecialchars($row['umur']) ?></td>
              <td><?= htmlspecialchars($row['imt']) ?></td>
              <td>
                <a href="HasilIMT.php?id=<?= $row['id'] ?>">Lihat</a>
                <a href="HasilIMT.php?hapus=<?= $row['id'] ?>" class="delete" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
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
