<?php
// HasilIMT.php
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

// 1) Hapus data jika diminta (semua data user)
if (isset($_GET['hapus'])) {
    $hapusId = (int)$_GET['hapus'];
    $pdo->prepare("DELETE FROM hasil_imt WHERE id = ?")
        ->execute([$hapusId]);
    header("Location: HasilIMT.php?hapus_berhasil=1");
    exit;
}

// 2) Detail view jika ada id
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM hasil_imt WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$data) die(htmlspecialchars($teks['data_tidak_ditemukan']));
    ?>
    <!DOCTYPE html>
    <html lang="<?= htmlspecialchars($lang) ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <title><?= htmlspecialchars($teks['detail_perhitungan_imt']) ?></title>
        <link rel="stylesheet" href="public/style/root.css">
        <link rel="stylesheet" href="public/style/form.css">
    </head>
    <body>
    <a class="back" href="HasilIMT.php"><button class="back-button"><</button></a>
        <div class="main-container">
            <div class="content-text">
                <h6><?= htmlspecialchars($teks['kalkulator']) ?></h6>
                <h2><?= htmlspecialchars($teks['detail_perhitungan_imt']) ?></h2>
            </div>
            <div class="form-box">
                <p class="result-hjk"><strong><?= htmlspecialchars($teks['nama']) ?>:</strong> <?= htmlspecialchars($data['nama_lengkap']) ?></p>
                <p class="result-hjk"><strong><?= htmlspecialchars($teks['umur']) ?>:</strong> <?= htmlspecialchars($data['umur']) ?> <?= htmlspecialchars($teks['tahun']) ?></p>
                <p class="result-hjk"><strong><?= htmlspecialchars($teks['alamat']) ?>:</strong> <?= htmlspecialchars($data['alamat']) ?></p>
                <hr>
                <p class="result-hjk"><strong><?= htmlspecialchars($teks['jenis_kelamin']) ?>:</strong> <?= htmlspecialchars($data['jenis_kelamin']) ?></p>
                <p class="result-hjk"><strong><?= htmlspecialchars($teks['usia']) ?>:</strong> <?= htmlspecialchars($data['usia']) ?> <?= htmlspecialchars($teks['tahun']) ?></p>
                <p class="result-hjk"><strong><?= htmlspecialchars($teks['berat_kg']) ?>:</strong> <?= htmlspecialchars($data['berat_kg']) ?> kg</p>
                <p class="result-hjk"><strong><?= htmlspecialchars($teks['tinggi_cm']) ?>:</strong> <?= htmlspecialchars($data['tinggi_cm']) ?> cm</p>
                <p class="result-hjk"><strong><?= htmlspecialchars($teks['imt']) ?>:</strong> <?= htmlspecialchars($data['imt']) ?></p>
                <p class="result-hjk"><strong><?= htmlspecialchars($teks['kategori']) ?>:</strong> <?= htmlspecialchars($data['kategori']) ?></p>
                <a href="HasilIMT.php"><button class="button-result"><?= htmlspecialchars($teks['kembali_riwayat']) ?></button></a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// 3) Simpan data baru jika POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap  = trim($_POST['nama_lengkap']);
    $umur          = (int)$_POST['umur'];
    $alamat        = trim($_POST['alamat']);
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $usia          = (int)$_POST['usia'];
    $berat_kg      = (float)$_POST['berat_kg'];
    $tinggi_cm     = (float)$_POST['tinggi_cm'];

    // Validasi
    if ($tinggi_cm <= 0) die(htmlspecialchars($teks['validasi_tinggi']));

    // Hitung IMT
    $tinggi_m = $tinggi_cm / 100;
    $imt = round($berat_kg / ($tinggi_m * $tinggi_m), 2);

    // Kategori
    if ($imt < 16)         $kategori = $teks['kategori_sangat_kurus'];
    elseif ($imt < 18.5)   $kategori = $teks['kategori_kurus'];
    elseif ($imt < 25)     $kategori = $teks['kategori_normal'];
    elseif ($imt < 30)     $kategori = $teks['kategori_kelebihan'];
    elseif ($imt < 35)     $kategori = $teks['kategori_obesitas1'];
    elseif ($imt < 40)     $kategori = $teks['kategori_obesitas2'];
    else                   $kategori = $teks['kategori_obesitas_morbid'];

    $stmt = $pdo->prepare("INSERT INTO hasil_imt
      (user_id, nama_lengkap, umur, alamat, jenis_kelamin, usia,
       berat_kg, tinggi_cm, imt, kategori)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $user_id, $nama_lengkap, $umur, $alamat, $jenis_kelamin,
        $usia, $berat_kg, $tinggi_cm, $imt, $kategori
    ]);

    header("Location: HasilIMT.php");
    exit;
}

// 4) Tampilkan riwayat
$stmt = $pdo->query("SELECT * FROM hasil_imt ORDER BY created_at DESC");
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?= htmlspecialchars($teks['riwayat_imt']) ?></title>
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
<a class="back" href="index.php"><button class="back-button"><</button></a>
<div class="main-container">
  <div class="content-text">
    <h6><?= htmlspecialchars($teks['kalkulator']) ?></h6>
    <h2><?= htmlspecialchars($teks['riwayat_imt']) ?></h2>
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
            <th><?= htmlspecialchars($teks['umur']) ?></th>
            <th><?= htmlspecialchars($teks['imt']) ?></th>
            <th><?= htmlspecialchars($teks['aksi']) ?></th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <?php foreach ($riwayat as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
            <td><?= htmlspecialchars($row['umur']) ?></td>
            <td><?= htmlspecialchars($row['imt']) ?></td>
            <td>
              <a href="HasilIMT.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($teks['lihat']) ?></a>
              <a href="HasilIMT.php?hapus=<?= $row['id'] ?>" class="delete" onclick="return confirm('<?= htmlspecialchars($teks['lokasi_hapus']) ?>')">
                <?= htmlspecialchars($teks['hapus']) ?>
              </a>
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

function displayTable(){
  const start=(currentPage-1)*rowsPerPage, end=start+rowsPerPage;
  rows.forEach((r,i)=> r.style.display=(i>=start&&i<end)?'':'none'); renderPagination();
}
function renderPagination(){
  const pages=Math.ceil(rows.length/rowsPerPage); pagination.innerHTML='';
  for(let i=1;i<=pages;i++){ let btn=document.createElement('button'); btn.textContent=i;
    if(i===currentPage) btn.classList.add('active'); btn.onclick=()=>{currentPage=i;displayTable();}; pagination.appendChild(btn);
  }
}
function filterTable(){
  const q=document.getElementById('searchInput').value.toLowerCase();
  rows.forEach(r=>{ const n=r.cells[0].textContent.toLowerCase(), a=r.cells[1].textContent.toLowerCase(); r.style.display=(n.includes(q)||a.includes(q))?'':'none'; });
  currentPage=1; displayTable();
}
displayTable();
</script>
</body>
</html>
