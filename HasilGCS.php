<?php
require_once __DIR__ . '/config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}

// 1) Hapus data jika diminta (tanpa filter user_id)
if (isset($_GET['hapus'])) {
  $hapusId = (int)$_GET['hapus'];
  $stmt = $pdo->prepare("DELETE FROM gcs_data WHERE id = ?");
  $stmt->execute([$hapusId]);
  header("Location: HasilGCS.php?hapus_berhasil=1");
  exit;
}

// 2) Detail view jika ada id (tanpa filter user_id)
if (isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $stmt = $pdo->prepare("SELECT * FROM gcs_data WHERE id = ?");
  $stmt->execute([$id]);
  $data = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$data) {
    die("Data tidak ditemukan.");
  }
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
      <p class="result-hjk"><strong>Nama:</strong> <?= htmlspecialchars($data['nama_lengkap']) ?></p>
      <p class="result-hjk"><strong>Umur:</strong> <?= htmlspecialchars($data['umur']) ?> tahun</p>
      <p class="result-hjk"><strong>Alamat:</strong> <?= htmlspecialchars($data['alamat']) ?></p>
      <hr>
      <p class="result-hjk"><strong>Jenis Kelamin:</strong> <?= htmlspecialchars($data['jenis_kelamin']) ?></p>
      <p class="result-hjk"><strong>Skor E:</strong> <?= htmlspecialchars($data['skor_eye']) ?></p>
      <p class="result-hjk"><strong>Skor V:</strong> <?= htmlspecialchars($data['skor_verbal']) ?></p>
      <p class="result-hjk"><strong>Skor M:</strong> <?= htmlspecialchars($data['skor_motor']) ?></p>
      <p class="result-hjk"><strong>Total Skor GCS:</strong> <?= htmlspecialchars($data['skor_total']) ?></p>
      <p class="result-hjk"><strong>Status Kesadaran:</strong> <?= htmlspecialchars($data['status_kesadaran']) ?></p>
      <a href="HasilGCS.php">
        <button class="button-result">Kembali ke Riwayat</button>
      </a>
    </div>
  </div>
</body>
</html>
<?php
  exit;
}

// 3) Simpan data baru jika POST (tetap sertakan user_id jika constraint FK masih ada)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_SESSION['user']['id'];
  $nama   = trim($_POST['nama_lengkap']);
  $umur   = (int)$_POST['umur'];
  $alamat = trim($_POST['alamat']);
  $jk     = $_POST['jenis_kelamin'];
  $eye    = (int)$_POST['eye'];
  $verbal = (int)$_POST['verbal'];
  $motor  = (int)$_POST['motor'];
  $total  = $eye + $verbal + $motor;

  // Interpretasi status
  if ($total >= 14)      $status = "Composmentis (Sadar Baik)";
  elseif ($total >= 12)  $status = "Apatis (Kurang Perhatian)";
  elseif ($total >= 10)  $status = "Delirium (Mudah Tidur)";
  elseif ($total >= 7)   $status = "Somnolen (Meracau/Gelisah)";
  elseif ($total >= 5)   $status = "Sopor (Respon Nyeri)";
  elseif ($total == 4)   $status = "Semi Coma (Sulit Dibangunkan)";
  else                   $status = "Coma (Tidak Ada Respon)";

  $stmt = $pdo->prepare("
    INSERT INTO gcs_data 
      (user_id, nama_lengkap, umur, alamat, jenis_kelamin,
       skor_eye, skor_verbal, skor_motor, skor_total, status_kesadaran)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  ");
  $stmt->execute([$user_id, $nama, $umur, $alamat, $jk, $eye, $verbal, $motor, $total, $status]);

  header("Location: HasilGCS.php");
  exit;
}

// 4) Tampilkan riwayat tanpa filter user_id
$stmt = $pdo->query("SELECT * FROM gcs_data ORDER BY created_at DESC");
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat GCS</title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
  <style>
    .search-box { width:100%; max-width:300px; padding:8px 12px; margin-bottom:1rem;
                  border:1px solid #ccc; border-radius:8px; font-size:14px; }
    .table-responsive { overflow-x:auto; }
    table { width:100%; border-collapse:collapse; margin-bottom:1rem; }
    th, td { padding:10px; border:1px solid #ccc; text-align:left; }
    th { background-color:#f8f8f8; }
    .pagination { display:flex; justify-content:center; gap:8px; margin-bottom:2rem; }
    .pagination button { padding:6px 12px; border:none; background-color:#e0e0e0;
                         border-radius:4px; cursor:pointer; font-size:14px; }
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
      <h2>Riwayat Glasgow Coma Scale</h2>
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
              <th>Total Skor</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="tableBody">
            <?php foreach ($riwayat as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                <td><?= htmlspecialchars($row['umur']) ?></td>
                <td><?= htmlspecialchars($row['skor_total']) ?></td>
                <td>
                  <a href="HasilGCS.php?id=<?= $row['id'] ?>">Lihat</a>
                  <a href="HasilGCS.php?hapus=<?= $row['id'] ?>" class="delete" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
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
    const rowsPerPage = 6,
          tbody = document.getElementById("tableBody"),
          rows = Array.from(tbody.querySelectorAll("tr")),
          pagination = document.getElementById("pagination");
    let currentPage = 1;

    function displayTable() {
      const start = (currentPage-1) * rowsPerPage,
            end   = start + rowsPerPage;
      rows.forEach((r,i) => r.style.display = (i>=start && i<end) ? "" : "none");
      renderPagination();
    }

    function renderPagination(){
      const total = rows.length,
            pages = Math.ceil(total / rowsPerPage);
      pagination.innerHTML = "";
      for (let i=1; i<=pages; i++) {
        const btn = document.createElement("button");
        btn.textContent = i;
        if (i === currentPage) btn.classList.add("active");
        btn.onclick = () => { currentPage = i; displayTable(); };
        pagination.appendChild(btn);
      }
    }

    function filterTable(){
      const q = document.getElementById("searchInput").value.toLowerCase();
      rows.forEach(r => {
        const nama = r.cells[0].textContent.toLowerCase(),
              umur = r.cells[1].textContent.toLowerCase();
        r.style.display = (nama.includes(q) || umur.includes(q)) ? "" : "none";
      });
      currentPage = 1;
      displayTable();
    }

    displayTable();
  </script>
</body>
</html>
