<?php
// HasilLajuInfus.php
require_once __DIR__ . '/config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}
// Pastikan user_id tersedia
$user_id = $_SESSION['user']['id'];

// 1) Hapus data jika diminta
if (isset($_GET['hapus'])) {
  $hapusId = (int)$_GET['hapus'];
  $pdo->prepare("DELETE FROM laju_infus WHERE id = ?")
      ->execute([$hapusId]);
  header("Location: HasilLajuInfus.php?hapus_berhasil=1");
  exit;
}

// 2) Detail view jika ada id
if (isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $stmt = $pdo->prepare("SELECT * FROM laju_infus WHERE id = ?");
  $stmt->execute([$id]);
  $data = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$data) die("Data tidak ditemukan.");
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
        <p><strong>Jenis Infus:</strong> <?= htmlspecialchars($data['jenis_infus']) ?></p>
        <p><strong>Nama:</strong> <?= htmlspecialchars($data['Nama_Lengkap']) ?></p>
        <p><strong>Umur:</strong> <?= htmlspecialchars($data['umur']) ?> tahun</p>
        <p><strong>Alamat:</strong> <?= htmlspecialchars($data['alamat']) ?></p>
        <hr>
        <p><strong>Volume Cairan:</strong> <?= htmlspecialchars($data['volume_ml']) ?> mL</p>
        <p><strong>Waktu:</strong> <?= htmlspecialchars($data['waktu_jam']) ?> jam <?= htmlspecialchars($data['menit_opt']) ?> menit</p>
        <hr>
        <p><strong>Dosis Obat:</strong> <?= htmlspecialchars($data['dosis_mg']) ?> mg</p>
        <p><strong>Konsentrasi:</strong> <?= htmlspecialchars($data['konsentrasi']) ?> mg/mL</p>
        <p><strong>Volume Obat:</strong> <?= round($data['volume_obat'], 2) ?> mL</p>
        <hr>
        <p><strong>Laju Infus:</strong> <?= round($data['laju_tetes_menit'], 2) ?> tetes/menit</p>
        <a href="HasilLajuInfus.php"><button class="button-result">Kembali ke Riwayat</button></a>
      </div>
    </div>
  </body>
  </html>
  <?php
  exit;
}

// 3) Simpan data baru jika POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Ambil input
  $jenis       = $_POST['jenis_infus'];              // mikrodrip atau makrodrip
  $nama        = trim($_POST['Nama_Lengkap']);
  $umur        = (int)$_POST['umur'];
  $alamat      = trim($_POST['alamat']);
  $volume_ml   = (float)$_POST['volume_ml'];
  $waktu_jam   = (float)$_POST['waktu_jam'];
  $menit_opt   = (int)($_POST['menit_opt'] ?? 0);
  $dosis_mg    = (float)($_POST['dosis_mg'] ?? 0);
  $konsentrasi = (float)($_POST['konsentrasi'] ?? 0);

  // Hitung volume obat (mL)
  $volume_obat = ($konsentrasi > 0) ? $dosis_mg / $konsentrasi : 0;

  // Hitung total waktu dalam jam
  $total_hours = $waktu_jam + ($menit_opt / 60);

  // Hitung laju cairan mL/jam
  $ml_per_hour = ($total_hours > 0) ? $volume_ml / $total_hours : 0;

  // Tentukan faktor tetes per mL
  $faktor = ($jenis === 'makrodrip') ? 20 : 60;

  // Hitung laju tetes per menit
  $laju_tetes_menit = ($ml_per_hour / 60) * $faktor;

  // Simpan ke DB
  $stmt = $pdo->prepare("
    INSERT INTO laju_infus
      (user_id, jenis_infus, Nama_Lengkap, umur, alamat,
       volume_ml, waktu_jam, menit_opt,
       dosis_mg, konsentrasi, volume_obat, laju_tetes_menit, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
  ");
  $stmt->execute([
    $user_id,
    $jenis,
    $nama,
    $umur,
    $alamat,
    $volume_ml,
    $waktu_jam,
    $menit_opt,
    $dosis_mg,
    $konsentrasi,
    $volume_obat,
    $laju_tetes_menit
  ]);

  header("Location: HasilLajuInfus.php");
  exit;
}

// 4) Tampilkan riwayat semua data
$stmt = $pdo->query("SELECT * FROM laju_infus ORDER BY created_at DESC");
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
    .search-box { width:100%;max-width:300px;padding:8px;margin-bottom:1rem;border:1px solid #ccc;border-radius:8px; }
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
      <h2>Riwayat Laju Infus</h2>
    </div>
    <div class="form-box">
      <?php if (isset($_GET['hapus_berhasil'])): ?>
        <p style="color:green">Data berhasil dihapus.</p>
      <?php endif; ?>
      <input type="text" id="searchInput" class="search-box" placeholder="Cari nama atau jenis..." onkeyup="filterTable()">
      <div class="table-responsive">
        <table id="riwayatTable">
          <thead>
            <tr>
              <th>Jenis</th>
              <th>Nama</th>
              <th>Volume</th>
              <th>Laju (tetes/menit)</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="tableBody">
            <?php foreach ($riwayat as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['jenis_infus']) ?></td>
                <td><?= htmlspecialchars($row['Nama_Lengkap']) ?></td>
                <td><?= htmlspecialchars($row['volume_ml']) ?> mL</td>
                <td><?= round($row['laju_tetes_menit'],2) ?></td>
                <td>
                  <a href="HasilLajuInfus.php?id=<?= $row['id'] ?>">Lihat</a>
                  <a href="HasilLajuInfus.php?hapus=<?= $row['id'] ?>" class="delete" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
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
        let btn=document.createElement('button'); btn.textContent=i;
        if(i===currentPage) btn.classList.add('active');
        btn.onclick=()=>{currentPage=i;displayTable();};
        pagination.appendChild(btn);
      }
    }
    function filterTable(){
      const q=document.getElementById('searchInput').value.toLowerCase();
      rows.forEach(r=>{
        const jenis=r.cells[0].textContent.toLowerCase(),
              nama=r.cells[1].textContent.toLowerCase();
        r.style.display=(jenis.includes(q)||nama.includes(q))?'':'none';
      });
      currentPage=1; displayTable();
    }
    displayTable();
  </script>
</body>
</html>
