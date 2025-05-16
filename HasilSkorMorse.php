<?php
// HasilSkorMorse.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lang/lang.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}

$lang = $_SESSION['user']['lang'] ?? 'id';
$teks = ($lang === 'en') ? $bahasa_en : $bahasa_id;
$user_id = $_SESSION['user']['id'];

// 1) Hapus data
if (isset($_GET['hapus'])) {
  $hapusId = (int)$_GET['hapus'];
  $pdo->prepare("DELETE FROM skor_morse WHERE id = ?")->execute([$hapusId]);
  header("Location: HasilSkorMorse.php?hapus_berhasil=1");
  exit;
}

// 2) Detail view
if (isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $stmt = $pdo->prepare("SELECT * FROM skor_morse WHERE id = ?");
  $stmt->execute([$id]);
  $data = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$data) die(htmlspecialchars($teks['data_tidak_ditemukan']));
  ?>
  <!DOCTYPE html>
  <html lang="<?= htmlspecialchars($lang) ?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?= htmlspecialchars($teks['detail_morse']) ?></title>
    <link rel="stylesheet" href="public/style/root.css">
    <link rel="stylesheet" href="public/style/form.css">
  </head>
  <body>
  <a class="back" href="HasilSkorMorse.php"><button class="back-button"><</button></a>
    <div class="main-container">
      <div class="content-text">
        <h6><?= htmlspecialchars($teks['kalkulator']) ?></h6>
        <h2><?= htmlspecialchars($teks['detail_morse']) ?></h2>
      </div>
      <div class="form-box">
        <p class="result-hjk"><strong><?= htmlspecialchars($teks['nama_lengkap']) ?>:</strong> <?= htmlspecialchars($data['nama_lengkap']) ?></p>
        <p class="result-hjk"><strong><?= htmlspecialchars($teks['usia']) ?>:</strong> <?= htmlspecialchars($data['usia']) ?> <?= htmlspecialchars($teks['tahun']) ?></p>
        <p class="result-hjk"><strong><?= htmlspecialchars($teks['alamat']) ?>:</strong> <?= htmlspecialchars($data['alamat']) ?></p>
        <hr>
        <p class="result-hjk"><strong><?= htmlspecialchars($teks['riwayat_jatuh']) ?>:</strong> <?= htmlspecialchars($data['riwayat_jatuh']) ?></p>
        <p class="result-hjk"><strong><?= htmlspecialchars($teks['diagnosis_sekunder']) ?>:</strong> <?= htmlspecialchars($data['diagnosis_sekunder']) ?></p>
        <p class="result-hjk"><strong><?= htmlspecialchars($teks['bantuan_mobilitas']) ?>:</strong> <?= htmlspecialchars($data['bantuan_mobilitas']) ?></p>
        <p class="result-hjk"><strong><?= htmlspecialchars($teks['terpasang_inpus']) ?>:</strong> <?= htmlspecialchars($data['terpasang_inpus']) ?></p>
        <p class="result-hjk"><strong><?= htmlspecialchars($teks['gaya_berjalan']) ?>:</strong> <?= htmlspecialchars($data['gaya_berjalan']) ?></p>
        <p class="result-hjk"><strong><?= htmlspecialchars($teks['status_mental']) ?>:</strong> <?= htmlspecialchars($data['status_mental']) ?></p>
        <hr>
        <p class="result-hjk"><strong><?= htmlspecialchars($teks['total_skor']) ?>:</strong> <?= htmlspecialchars($data['total_skor']) ?></p>
        <p class="result-hjk"><strong><?= htmlspecialchars($teks['resiko']) ?>:</strong> <?= htmlspecialchars($data['resiko']) ?></p>
        <a href="HasilSkorMorse.php"><button class="button-result"><?= htmlspecialchars($teks['kembali']) ?></button></a>
      </div>
    </div>
  </body>
  </html>
  <?php
  exit;
}

// 3) Simpan data baru
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
  if ($total < 25)      $resiko = $teks['risiko_rendah'];
  elseif ($total <= 45) $resiko = $teks['risiko_sedang'];
  else                  $resiko = $teks['risiko_tinggi'];

  $stmt = $pdo->prepare("INSERT INTO skor_morse
    (user_id, nama_lengkap, usia, alamat, riwayat_jatuh,
     diagnosis_sekunder, bantuan_mobilitas, terpasang_inpus,
     gaya_berjalan, status_mental, total_skor, resiko, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
  $stmt->execute([
    $user_id, $nama, $usia, $alamat,
    $riwayat, $diag, $bantuan, $inpus, $gaya, $mental,
    $total, $resiko
  ]);

  header("Location: HasilSkorMorse.php");
  exit;
}

// 4) Riwayat
$stmt = $pdo->query("SELECT * FROM skor_morse ORDER BY created_at DESC");
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?= htmlspecialchars($teks['riwayat_morse']) ?></title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
  <style>
    .search-box { width:100%; max-width:300px; padding:8px; margin-bottom:1rem; border:1px solid #ccc; border-radius:8px; }
    .table-responsive { overflow-x:auto; }
    table { width:100%; border-collapse:collapse; margin-bottom:1rem; }
    th, td { padding:10px; border:1px solid #ccc; text-align:left; }
    th { background-color:#f8f8f8; }
    .pagination { display:flex; justify-content:center; gap:8px; margin-bottom:2rem; }
    .pagination button { padding:6px 12px; border:none; background-color:#e0e0e0; border-radius:4px; cursor:pointer; }
    .pagination button.active { background-color:#007bff; color:white; }
    a.delete { color:red; margin-left:8px; }
  </style>
</head>
<body style="height: 100svh;">
<a class="back" href="index.php"><button class="back-button"><</button></a>
  <div class="main-container">
    <div class="content-text">
      <h6><?= htmlspecialchars($teks['kalkulator']) ?></h6>
      <h2><?= htmlspecialchars($teks['riwayat_morse']) ?></h2>
    </div>
    <div class="form-box">
      <?php if (isset($_GET['hapus_berhasil'])): ?><p style="color:green"><?= htmlspecialchars($teks['data_berhasil_dihapus']) ?></p><?php endif; ?>
      <div class="table-responsive">
        <table id="riwayatTable">
          <thead>
            <tr>
              <th><?= htmlspecialchars($teks['nama_lengkap']) ?></th>
              <th><?= htmlspecialchars($teks['usia']) ?></th>
              <th><?= htmlspecialchars($teks['total_skor']) ?></th>
              <th><?= htmlspecialchars($teks['resiko']) ?></th>
              <th><?= htmlspecialchars($teks['aksi']) ?></th>
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
                  <a href="HasilSkorMorse.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($teks['lihat']) ?></a>
                  <a href="HasilSkorMorse.php?hapus=<?= $row['id'] ?>" class="delete" onclick="return confirm('<?= htmlspecialchars($teks['lokasi_hapus']) ?>')"><?= htmlspecialchars($teks['hapus']) ?></a>
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
const tbody=document.getElementById('tableBody'), rows=Array.from(tbody.querySelectorAll('tr')), pagination=document.getElementById('pagination');
function displayTable(){ const start=(currentPage-1)*rowsPerPage,end=start+rowsPerPage; rows.forEach((r,i)=>r.style.display=(i>=start&&i<end)?'':'none'); renderPagination(); }
function renderPagination(){ const pages=Math.ceil(rows.length/rowsPerPage); pagination.innerHTML=''; for(let i=1;i<=pages;i++){ let btn=document.createElement('button'); btn.textContent=i; if(i===currentPage)btn.classList.add('active'); btn.onclick=()=>{currentPage=i;displayTable();}; pagination.appendChild(btn);} }
function filterTable(){ const q=document.getElementById('searchInput').value.toLowerCase(); rows.forEach(r=>{ const n=r.cells[0].textContent.toLowerCase(), u=r.cells[1].textContent.toLowerCase(); r.style.display=(n.includes(q)||u.includes(q))?'':'none'; }); currentPage=1; displayTable(); }
displayTable();
</script>
</body>
</html>
