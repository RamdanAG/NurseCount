<?php
// HasilSkorMorse.php
require_once __DIR__ . '/config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}
$user_id = $_SESSION['user']['id']; // tetap simpan siapa yang input

// 1) Hapus data (tanpa filter user_id)
if (isset($_GET['hapus'])) {
  $hapusId = (int)$_GET['hapus'];
  $pdo->prepare("DELETE FROM skor_morse WHERE id = ?")
      ->execute([$hapusId]);
  header("Location: HasilSkorMorse.php?hapus_berhasil=1");
  exit;
}

// 2) Detail view (tanpa filter user_id)
if (isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $stmt = $pdo->prepare("SELECT * FROM skor_morse WHERE id = ?");
  $stmt->execute([$id]);
  $data = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$data) die("Data tidak ditemukan.");
  ?>
  <!DOCTYPE html>
  <html lang="id">
  <head>
    <meta charset="UTF-8">
    <title>Detail Skor Morse</title>
    <link rel="stylesheet" href="public/style/root.css">
    <link rel="stylesheet" href="public/style/form.css">
  </head>
  <body>
    <a class="back" href="HasilSkorMorse.php">« BACK</a>
    <div class="main-container">
      <div class="content-text">
        <h6>Kalkulator</h6>
        <h2>Detail Skor Morse</h2>
      </div>
      <div class="form-box">
        <p><strong>Nama:</strong> <?= htmlspecialchars($data['nama_lengkap']) ?></p>
        <p><strong>Usia:</strong> <?= htmlspecialchars($data['usia']) ?> tahun</p>
        <p><strong>Alamat:</strong> <?= htmlspecialchars($data['alamat']) ?></p>
        <hr>
        <p><strong>Riwayat Jatuh:</strong> <?= htmlspecialchars($data['riwayat_jatuh']) ?></p>
        <p><strong>Diagnosis Sekunder:</strong> <?= htmlspecialchars($data['diagnosis_sekunder']) ?></p>
        <p><strong>Bantuan Mobilitas:</strong> <?= htmlspecialchars($data['bantuan_mobilitas']) ?></p>
        <p><strong>Terpasang Infus:</strong> <?= htmlspecialchars($data['terpasang_inpus']) ?></p>
        <p><strong>Gaya Berjalan:</strong> <?= htmlspecialchars($data['gaya_berjalan']) ?></p>
        <p><strong>Status Mental:</strong> <?= htmlspecialchars($data['status_mental']) ?></p>
        <p><strong>Total Skor:</strong> <?= htmlspecialchars($data['total_skor']) ?></p>
        <p><strong>Risiko:</strong> <?= htmlspecialchars($data['resiko']) ?></p>
        <a href="HasilSkorMorse.php"><button class="button-result">Kembali ke Riwayat</button></a>
      </div>
    </div>
  </body>
  </html>
  <?php
  exit;
}

// 3) Simpan data jika POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama     = trim($_POST['nama_lengkap']);
  $usia     = (int)$_POST['usia'];
  $alamat   = trim($_POST['alamat']);
  $riwayat  = (int)$_POST['riwayat_jatuh'];
  $diag     = (int)$_POST['diagnosis_sekunder'];
  $bantuan  = (int)$_POST['bantuan_mobilitas'];
  $inpus    = (int)$_POST['terpasang_inpus'];
  $gaya     = (int)$_POST['gaya_berjalan'];
  $mental   = (int)$_POST['status_mental'];

  $total = $riwayat + $diag + $bantuan + $inpus + $gaya + $mental;
  if ($total < 25) {
    $resiko = "Risiko Rendah";
  } elseif ($total <= 45) {
    $resiko = "Risiko Sedang";
  } else {
    $resiko = "Risiko Tinggi";
  }

  $stmt = $pdo->prepare("
    INSERT INTO skor_morse
      (user_id, nama_lengkap, usia, alamat, riwayat_jatuh,
       diagnosis_sekunder, bantuan_mobilitas, terpasang_inpus,
       gaya_berjalan, status_mental, total_skor, resiko, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
  ");
  $stmt->execute([
    $user_id, $nama, $usia, $alamat,
    $riwayat, $diag, $bantuan, $inpus, $gaya, $mental,
    $total, $resiko
  ]);

  header("Location: HasilSkorMorse.php");
  exit;
}

// 4) Tampilkan semua riwayat (tanpa filter user_id)
$stmt = $pdo->query("SELECT * FROM skor_morse ORDER BY created_at DESC");
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Skor Morse</title>
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
  <a class="back" href="index.php">« BACK</a>
  <div class="main-container">
    <div class="content-text">
      <h6>Kalkulator</h6>
      <h2>Riwayat Skor Morse</h2>
    </div>
    <div class="form-box">
      <?php if (isset($_GET['hapus_berhasil'])): ?>
        <p style="color:green">Data berhasil dihapus.</p>
      <?php endif; ?>
      <input type="text" id="searchInput" class="search-box" placeholder="Cari nama atau usia..." onkeyup="filterTable()">
      <div class="table-responsive">
        <table id="riwayatTable">
          <thead>
            <tr>
              <th>Nama</th>
              <th>Usia</th>
              <th>Total Skor</th>
              <th>Risiko</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="tableBody">
            <?php foreach ($riwayat as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                <td><?= htmlspecialchars($row['usia']) ?></td>
                <td><?= htmlspecialchars($row['total_skor']) ?></td>
                <td><?= htmlspecialchars($row['resiko']) ?></td>
                <td>
                  <a href="HasilSkorMorse.php?id=<?= $row['id'] ?>">Lihat</a>
                  <a href="HasilSkorMorse.php?hapus=<?= $row['id'] ?>" class="delete" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
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
    let currentPage = 1, rowsPerPage = 6;
    const tbody = document.getElementById('tableBody'),
          rows  = Array.from(tbody.querySelectorAll('tr')),
          pagination = document.getElementById('pagination');

    function displayTable() {
      const start = (currentPage - 1) * rowsPerPage,
            end   = start + rowsPerPage;
      rows.forEach((r,i) => r.style.display = (i>=start && i<end) ? '' : 'none');
      renderPagination();
    }
    function renderPagination() {
      const pages = Math.ceil(rows.length / rowsPerPage);
      pagination.innerHTML = '';
      for(let i=1; i<=pages; i++){
        const btn = document.createElement('button');
        btn.textContent = i;
        if(i===currentPage) btn.classList.add('active');
        btn.onclick = () => { currentPage = i; displayTable(); };
        pagination.appendChild(btn);
      }
    }
    function filterTable() {
      const q = document.getElementById('searchInput').value.toLowerCase();
      rows.forEach(r => {
        const nama = r.cells[0].textContent.toLowerCase(),
              usia = r.cells[1].textContent.toLowerCase();
        r.style.display = (nama.includes(q) || usia.includes(q)) ? '' : 'none';
      });
      currentPage = 1;
      displayTable();
    }
    displayTable();
  </script>
</body>
</html>
