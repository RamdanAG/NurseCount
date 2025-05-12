<?php
// HasilLukaBakar.php
require_once __DIR__ . '/config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}

// 1) Hapus data jika diminta (tanpa filter user_id)
if (isset($_GET['hapus'])) {
  $hapusId = (int)$_GET['hapus'];
  $pdo->prepare("DELETE FROM hasil_lukabakar WHERE id = ?")
      ->execute([$hapusId]);
  header("Location: HasilLukaBakar.php?hapus_berhasil=1");
  exit;
}

// 2) Detail view jika ada id (tanpa filter user_id)
if (isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $stmt = $pdo->prepare("SELECT * FROM hasil_lukabakar WHERE id = ?");
  $stmt->execute([$id]);
  $data = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$data) die("Data tidak ditemukan.");
  ?>
  <!DOCTYPE html>
  <html lang="id">
  <head>
    <meta charset="UTF-8">
    <title>Detail Luka Bakar</title>
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
        <p><strong>TBSA:</strong> <?= htmlspecialchars($data['tbsa']) ?>%</p>
        <p><strong>Total Cairan:</strong> <?= number_format($data['total_cairan']) ?> mL</p>
        <p><strong>8 jam pertama:</strong> <?= number_format($data['cairan_8jam']) ?> mL</p>
        <p><strong>16 jam berikutnya:</strong> <?= number_format($data['cairan_16jam']) ?> mL</p>
        <hr>
        <p><strong>Berat Badan:</strong> <?= htmlspecialchars($data['berat_kg']) ?> kg</p>
        <p><strong>Usia:</strong> <?= htmlspecialchars($data['usia']) ?> tahun</p>
        <?php if ($data['foto']): ?>
          <p><strong>Foto:</strong></p>
          <img src="public/image/LukaBakar/<?= htmlspecialchars($data['foto']) ?>" alt="Foto Luka" style="max-width:100%;height:auto;">
        <?php endif; ?>
        <a href="HasilLukaBakar.php"><button>Kembali ke Riwayat</button></a>
      </div>
    </div>
  </body>
  </html>
  <?php
  exit;
}

// 3) Simpan data baru jika POST (tetap sertakan user_id untuk FK jika diperlukan)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $parts    = $_POST['area_parts'] ?? [];
  $berat_kg = (float)$_POST['berat_kg'];
  $usia     = (int)$_POST['usia'];

  // Hitung TBSA & Parkland
  $tbsa = array_sum(array_map('floatval', $parts));
  $total = round(4 * $berat_kg * $tbsa);
  $c8 = round($total / 2);
  $c16 = $total - $c8;

  // Upload gambar jika ada
  $fotoNama = null;
  if (!empty($_FILES['gambar']['tmp_name'])) {
    $uploadDir = __DIR__ . '/public/image/LukaBakar/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $fotoNama = time() . '_' . basename($_FILES['gambar']['name']);
    move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadDir . $fotoNama);
  }

  // Insert ke DB
  $stmt = $pdo->prepare("
    INSERT INTO hasil_lukabakar 
      (user_id, tbsa, total_cairan, cairan_8jam, cairan_16jam, berat_kg, usia, foto, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
  ");
  $stmt->execute([
    $_SESSION['user']['id'], // tetap simpan user_id
    $tbsa, $total, $c8, $c16,
    $berat_kg, $usia, $fotoNama
  ]);

  header("Location: HasilLukaBakar.php");
  exit;
}

// 4) Tampilkan semua riwayat (tanpa filter user_id)
$stmt = $pdo->query("SELECT * FROM hasil_lukabakar ORDER BY created_at DESC");
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
      <h2>Riwayat Luka Bakar</h2>
    </div>
    <div class="form-box">
      <?php if (isset($_GET['hapus_berhasil'])): ?>
        <p style="color:green">Data berhasil dihapus.</p>
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
                <td><?= number_format($row['total_cairan']) ?></td>
                <td>
                  <a href="HasilLukaBakar.php?id=<?= $row['id'] ?>">Lihat</a>
                  <a href="HasilLukaBakar.php?hapus=<?= $row['id'] ?>" class="delete" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
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
    let currentPage=1, rowsPerPage=6;
    const tbody=document.getElementById('tableBody'),
          rows=Array.from(tbody.querySelectorAll('tr')),
          pagination=document.getElementById('pagination');

    function displayTable(){
      const start=(currentPage-1)*rowsPerPage, end=start+rowsPerPage;
      rows.forEach((r,i)=> r.style.display=(i>=start&&i<end)?'':'none');
      renderPagination();
    }
    function renderPagination(){
      const pages=Math.ceil(rows.length/rowsPerPage);
      pagination.innerHTML='';
      for(let i=1;i<=pages;i++){
        let btn=document.createElement('button');
        btn.textContent=i;
        if(i===currentPage) btn.classList.add('active');
        btn.onclick=()=>{currentPage=i;displayTable();};
        pagination.appendChild(btn);
      }
    }
    function filterTable(){
      const q=document.getElementById('searchInput').value.toLowerCase();
      rows.forEach(r=>{
        const tbsa=r.cells[0].textContent.toLowerCase(),
              usia=r.cells[1].textContent.toLowerCase();
        r.style.display=(tbsa.includes(q)||usia.includes(q))?'':'none';
      });
      currentPage=1; displayTable();
    }
    displayTable();
  </script>
</body>
</html>
