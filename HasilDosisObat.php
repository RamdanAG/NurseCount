<?php
// HasilDosisObat.php
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
    $pdo->prepare("DELETE FROM dosage_data WHERE id = ?")->execute([$hapusId]);
    header("Location: HasilDosisObat.php?hapus_berhasil=1");
    exit;
}

// 2) Detail view
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM dosage_data WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$data) die(htmlspecialchars($teks['data_tidak_ditemukan']));
    ?>
    <!DOCTYPE html>
    <html lang="<?= htmlspecialchars($lang) ?>">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width,initial-scale=1.0">
      <title><?= htmlspecialchars($teks['detail_dosis']) ?></title>
      <link rel="stylesheet" href="public/style/root.css">
      <link rel="stylesheet" href="public/style/form.css">
    </head>
    <body>
      <a class="back" href="HasilDosisObat.php"><button class="back-button"><</button></a>
      <div class="main-container">
        <div class="content-text">
          <h6><?= htmlspecialchars($teks['kalkulator']) ?></h6>
          <h2><?= htmlspecialchars($teks['detail_dosis']) ?></h2>
        </div>
        <div class="form-box">
          <p class="result-hjk"><strong><?= htmlspecialchars($teks['nama_lengkap']) ?>:</strong> <?= htmlspecialchars($data['nama']) ?></p>
          <p class="result-hjk"><strong><?= htmlspecialchars($teks['umur']) ?>:</strong> <?= htmlspecialchars($data['umur']) ?> <?= htmlspecialchars($teks['tahun']) ?></p>
          <p class="result-hjk"><strong><?= htmlspecialchars($teks['alamat']) ?>:</strong> <?= htmlspecialchars($data['alamat']) ?></p>
          <hr>
          <p class="result-hjk"><strong><?= htmlspecialchars($teks['berat_kg']) ?>:</strong> <?= htmlspecialchars($data['berat_kg']) ?> kg</p>
          <p class="result-hjk"><strong><?= htmlspecialchars($teks['tinggi_cm']) ?>:</strong> <?= htmlspecialchars($data['tinggi_cm']) ?> cm</p>
          <p class="result-hjk"><strong><?= htmlspecialchars($teks['aktivitas_factor']) ?>:</strong> <?= htmlspecialchars($data['aktivitas_factor']) ?></p>
          <p class="result-hjk"><strong><?= htmlspecialchars($teks['stress_factor']) ?>:</strong> <?= htmlspecialchars($data['stress_factor']) ?></p>
          <hr>
          <p class="result-hjk"><strong><?= htmlspecialchars($teks['bmr']) ?>:</strong> <?= round($data['bmr'],2) ?> kkal/<?= htmlspecialchars($teks['hari']) ?></p>
          <p class="result-hjk"><strong><?= htmlspecialchars($teks['total_energi']) ?>:</strong> <?= round($data['total_energi'],2) ?> kkal/<?= htmlspecialchars($teks['hari']) ?><p>
          <p class="result-hjk"><strong><?= htmlspecialchars($teks['protein']) ?>:</strong> <?= round($data['protein_g'],2) ?> g</p>
          <p class="result-hjk"><strong><?= htmlspecialchars($teks['karbohidrat']) ?>:</strong> <?= round($data['karbohidrat_g'],2) ?> g</p>
          <a href="HasilDosisObat.php"><button class="button-result"><?= htmlspecialchars($teks['kembali_riwayat']) ?></button></a>
        </div>
      </div>
    </body>
    </html>
    <?php
    exit;
}

// 3) Simpan data baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama   = trim($_POST['nama_lengkap']);
    $umur   = (int)$_POST['umur'];
    $alamat = trim($_POST['alamat']);
    $berat  = (float)$_POST['berat_kg'];
    $tinggi = (float)$_POST['tinggi_cm'];
    $aktiv  = (float)$_POST['aktivitas'];
    $stress = (float)$_POST['stress'];

    $jk = $_SESSION['user']['jenis_kelamin'] ?? 'L';
    if ($jk === $teks['laki_laki']) {
        $bmr = 10*$berat + 6.25*$tinggi - 5*$umur + 5;
    } else {
        $bmr = 10*$berat + 6.25*$tinggi - 5*$umur - 161;
    }

    $te = $bmr * $aktiv * $stress;
    $protein = $te * 0.15 / 4;
    $karbo   = $te * 0.55 / 4;

    $stmt = $pdo->prepare("INSERT INTO dosage_data
      (user_id, nama, umur, alamat, berat_kg, tinggi_cm,
       aktivitas_factor, stress_factor, bmr, total_energi, protein_g, karbohidrat_g)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user']['id'], $nama, $umur, $alamat,
        $berat, $tinggi, $aktiv, $stress,
        $bmr, $te, $protein, $karbo
    ]);

    header("Location: HasilDosisObat.php");
    exit;
}

// 4) Tampilkan riwayat
$stmt = $pdo->query("SELECT * FROM dosage_data ORDER BY created_at DESC");
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?= htmlspecialchars($teks['riwayat_dosis']) ?></title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
  <style>
    .search-box { width:100%; max-width:300px; padding:8px 12px; margin-bottom:1rem; border:1px solid #ccc; border-radius:8px; font-size:14px; }
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
<body>
<a class="back" href="index.php"><button class="back-button"><</button></a>
<div class="main-container">
  <div class="content-text">
    <h6><?= htmlspecialchars($teks['kalkulator']) ?></h6>
    <h2><?= htmlspecialchars($teks['riwayat_dosis']) ?></h2>
  </div>
  <div class="form-box">
    <?php if (isset($_GET['hapus_berhasil'])): ?>
      <p style="color:green"><?= htmlspecialchars($teks['data_berhasil_dihapus']) ?></p>
    <?php endif; ?>
    <div class="table-responsive">
      <table id="riwayatTable">
        <thead>
          <tr>
            <th><?= htmlspecialchars($teks['nama']) ?></th>
            <th><?= htmlspecialchars($teks['total_energi']) ?></th>
            <th><?= htmlspecialchars($teks['aksi']) ?></th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <?php foreach ($riwayat as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= round($row['total_energi'], 2) ?> kkal</td>
            <td>
              <a href="HasilDosisObat.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($teks['lihat']) ?></a>
              <a href="HasilDosisObat.php?hapus=<?= $row['id'] ?>" class="delete" onclick="return confirm('<?= htmlspecialchars($teks['lokasi_hapus']) ?>')"><?= htmlspecialchars($teks['hapus']) ?></a>
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
function displayTable(){ const start=(currentPage-1)*rowsPerPage, end=start+rowsPerPage; rows.forEach((r,i)=> r.style.display=(i>=start&&i<end)?'':'none'); renderPagination(); }
function renderPagination(){ const pages=Math.ceil(rows.length/rowsPerPage); pagination.innerHTML=''; for(let i=1;i<=pages;i++){ let btn=document.createElement('button'); btn.textContent=i; if(i===currentPage)btn.classList.add('active'); btn.onclick=()=>{currentPage=i;displayTable();}; pagination.appendChild(btn);} }
function filterTable(){ const q=document.getElementById('searchInput').value.toLowerCase(); rows.forEach(r=>{ const n=r.cells[0].textContent.toLowerCase(); r.style.display=n.includes(q)?'':'none'; }); currentPage=1; displayTable(); }
displayTable();
</script>
</body>
</html>
