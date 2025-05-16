<?php
// HasilKebutuhanKalori.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lang/lang.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}

$lang = $_SESSION['user']['lang'] ?? 'id';
$teks = ($lang === 'en') ? $bahasa_en : $bahasa_id;

// 1) Hapus data
if (isset($_GET['hapus'])) {
  $hapusId = (int)$_GET['hapus'];
  $pdo->prepare("DELETE FROM kebutuhan_kalori WHERE id = ?")->execute([$hapusId]);
  header("Location: HasilKebutuhanKalori.php?hapus_berhasil=1");
  exit;
}

// 2) Detail view
if (isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $stmt = $pdo->prepare("SELECT * FROM kebutuhan_kalori WHERE id = ?");
  $stmt->execute([$id]);
  $data = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$data) die(htmlspecialchars($teks['data_tidak_ditemukan']));
  ?>

  <!DOCTYPE html>
  <html lang="<?=htmlspecialchars($lang)?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?=htmlspecialchars($teks['detail_kalori'])?></title>
    <link rel="stylesheet" href="public/style/root.css">
    <link rel="stylesheet" href="public/style/form.css">
  </head>
  <body>
    <a class="back" href="HasilKebutuhanKalori.php"><button class="back-button"><</button></a>
    <div class="main-container">
      <div class="content-text">
        <h6><?=htmlspecialchars($teks['kalkulator'])?></h6>
        <h2><?=htmlspecialchars($teks['detail_kalori'])?></h2>
      </div>
      <div class="form-box">
        <p class="result-hjk"><strong><?=htmlspecialchars($teks['nama_lengkap'])?>:</strong> <?=htmlspecialchars($data['nama_lengkap'])?></p>
        <p class="result-hjk"><strong><?=htmlspecialchars($teks['usia'])?>:</strong> <?=htmlspecialchars($data['usia'])?> <?=htmlspecialchars($teks['tahun'])?></p>
        <p class="result-hjk"><strong><?=htmlspecialchars($teks['alamat'])?>:</strong> <?=htmlspecialchars($data['alamat'])?></p>
        <hr>
        <p class="result-hjk"><strong><?=htmlspecialchars($teks['gender'])?>:</strong> <?=htmlspecialchars(ucfirst($data['gender']))?></p>
        <p class="result-hjk"><strong><?=htmlspecialchars($teks['berat_kg'])?>:</strong> <?=htmlspecialchars($data['berat_badan'])?> kg</p>
        <p class="result-hjk"><strong><?=htmlspecialchars($teks['tinggi_cm'])?>:</strong> <?=htmlspecialchars($data['tinggi_badan'])?> cm</p>
        <p class="result-hjk"><strong><?=htmlspecialchars($teks['bmr'])?>:</strong> <?=round($data['bmr'],2)?> <?=htmlspecialchars($teks['kalori'])?></p>
        <p class="result-hjk"><strong><?=htmlspecialchars($teks['total_kalori'])?>:</strong> <?=round($data['total_kalori'],2)?> <?=htmlspecialchars($teks['kalori'])?></p>
        <a href="HasilKebutuhanKalori.php"><button class="button-result"><?=htmlspecialchars($teks['kembali'])?></button></a>
      </div>
    </div>
  </body>
  </html>
  <?php
  exit;
}

// 3) Simpan data baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama_lengkap = trim($_POST['nama_lengkap']);
  $usia = (int)$_POST['usia'];
  $alamat = trim($_POST['alamat']);
  $gender = $_POST['gender'];
  $berat = (float)$_POST['berat_badan'];
  $tinggi = (float)$_POST['tinggi_badan'];
  $aktivitas = (float)$_POST['aktivitas'];
  $stress = (float)$_POST['stress'];

  // Hitung BMR
  if ($gender === 'pria') {
    $bmr = 10 * $berat + 6.25 * $tinggi - 5 * $usia + 5;
  } else {
    $bmr = 10 * $berat + 6.25 * $tinggi - 5 * $usia - 161;
  }
  $total_kalori = $bmr * $aktivitas * $stress;

  // Simpan data tanpa 'user_id' karena data bersifat umum
  $stmt = $pdo->prepare("INSERT INTO kebutuhan_kalori
  (nama_lengkap, usia, alamat, gender,
  berat_badan, tinggi_badan, aktivitas, stress,
  bmr, total_kalori)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->execute([
    $nama_lengkap, $usia, $alamat, $gender,
    $berat, $tinggi, $aktivitas, $stress,
    $bmr, $total_kalori
  ]);

  header("Location: HasilKebutuhanKalori.php");
  exit;
}

// 4) Riwayat semua data
$stmt = $pdo->query("SELECT * FROM kebutuhan_kalori ORDER BY tanggal_input DESC");
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="<?=htmlspecialchars($lang)?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?=htmlspecialchars($teks['riwayat_kalori'])?></title>
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
    a.delete { color:red; margin-left:8px; }
  </style>
</head>
<body>
<a class="back" href="index.php"><button class="back-button"><</button></a>
  <div class="main-container">
    <div class="content-text">
      <h6><?=htmlspecialchars($teks['kalkulator'])?></h6>
      <h2><?=htmlspecialchars($teks['riwayat_kalori'])?></h2>
    </div>
    <div class="form-box">
      <?php if (isset($_GET['hapus_berhasil'])): ?><p style="color:green"><?=htmlspecialchars($teks['data_berhasil_dihapus'])?></p><?php endif; ?>
      <div class="table-responsive">
        <table id="riwayatTable">
          <thead>
            <tr>
              <th><?=htmlspecialchars($teks['nama_lengkap'])?></th>
              <th><?=htmlspecialchars($teks['usia'])?></th>
              <th><?=htmlspecialchars($teks['total_kalori'])?></th>
              <th><?=htmlspecialchars($teks['aksi'])?></th>
            </tr>
          </thead>
          <tbody id="tableBody">
            <?php foreach ($riwayat as $row): ?>
              <tr>
                <td><?=htmlspecialchars($row['nama_lengkap'])?></td>
                <td><?=htmlspecialchars($row['usia'])?></td>
                <td><?=round($row['total_kalori'],2)?> <?=htmlspecialchars($teks['kalori'])?></td>
                <td>
                  <a href="?id=<?= $row['id'] ?>"><?=htmlspecialchars($teks['lihat'])?></a>
                  <a href="?hapus=<?= $row['id'] ?>" class="delete" onclick="return confirm('<?=htmlspecialchars($teks['lokasi_hapus'])?>')"><?=htmlspecialchars($teks['hapus'])?></a>
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
const rowsPerPage=6, tbody=document.getElementById('tableBody'), rows=Array.from(tbody.querySelectorAll('tr')), pagination=document.getElementById('pagination');
let currentPage=1;
function displayTable(){ const start=(currentPage-1)*rowsPerPage,end=start+rowsPerPage; rows.forEach((r,i)=>r.style.display=(i>=start&&i<end)?'':'none'); renderPagination(); }
function renderPagination(){ const pages=Math.ceil(rows.length/rowsPerPage); pagination.innerHTML=''; for(let i=1;i<=pages;i++){ let btn=document.createElement('button'); btn.textContent=i; if(i===currentPage)btn.classList.add('active'); btn.onclick=()=>{currentPage=i;displayTable();}; pagination.appendChild(btn);} }
function filterTable(){ const q=document.getElementById('searchInput').value.toLowerCase(); rows.forEach(r=>{ const name=r.cells[0].textContent.toLowerCase(), age=r.cells[1].textContent.toLowerCase(); r.style.display=(name.includes(q)||age.includes(q))?'':'none'; }); currentPage=1; displayTable(); }
displayTable();
</script>
</body>
</html>
