<?php
// HasilLukaBakar.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lang/lang.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}

$lang = $_SESSION['user']['lang'] ?? 'id';
$teks = ($lang === 'en') ? $bahasa_en : $bahasa_id;

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
  if (!$data) die($teks['data_tidak_ditemukan'] ?? "Data tidak ditemukan.");
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?= htmlspecialchars($teks['detail_luka_bakar']) ?></title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
</head>
<body>
<a class="back" href="HasilLukaBakar.php"><button class="back-button"><</button></a>
  <div class="main-container">
    <div class="content-text">
      <h6><?= htmlspecialchars($teks['kalkulator']) ?></h6>
      <h2><?= htmlspecialchars($teks['detail_luka_bakar']) ?></h2>
    </div>
    <div class="form-box">
      <p class="result-hjk"><strong><?= htmlspecialchars($teks['nama']) ?>:</strong> <?= htmlspecialchars($data['nama']) ?></p>
      <p class="result-hjk"><strong><?= htmlspecialchars($teks['alamat']) ?>:</strong> <?= htmlspecialchars($data['alamat']) ?></p>
      <p class="result-hjk"><strong><?= htmlspecialchars($teks['tbsa']) ?>:</strong> <?= htmlspecialchars($data['tbsa']) ?>%</p>
      <p class="result-hjk"><strong><?= htmlspecialchars($teks['total_cairan']) ?>:</strong> <?= number_format($data['total_cairan']) ?> mL</p>
      <p class="result-hjk"><strong><?= htmlspecialchars($teks['cairan_8jam']) ?>:</strong> <?= number_format($data['cairan_8jam']) ?> mL</p>
      <p class="result-hjk"><strong><?= htmlspecialchars($teks['cairan_16jam']) ?>:</strong> <?= number_format($data['cairan_16jam']) ?> mL</p>
      <hr>
      <p class="result-hjk"><strong><?= htmlspecialchars($teks['berat_kg']) ?>:</strong> <?= htmlspecialchars($data['berat_kg']) ?> kg</p>
      <p class="result-hjk"><strong><?= htmlspecialchars($teks['usia']) ?>:</strong> <?= htmlspecialchars($data['usia']) ?> <?= htmlspecialchars($teks['tahun'] ?? 'tahun') ?></p>
      <a href="HasilLukaBakar.php">
        <button class="button-result"><?= htmlspecialchars($teks['kembali']) ?></button>
      </a>
    </div>
  </div>
</body>
</html>
<?php
  exit;
}

// 3) Simpan data baru jika POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $parts    = $_POST['area_parts'] ?? [];
  $berat_kg = (float)$_POST['berat_kg'];
  $usia     = (int)$_POST['usia'];
  $nama     = $_POST['nama'] ?? '';
  $alamat   = $_POST['alamat'] ?? '';

  // Validasi input
  if (empty($nama) || empty($alamat)) {
    die($teks['error_nama_alamat'] ?? "Nama dan alamat harus diisi.");
  }

  // Hitung TBSA & Parkland
  $tbsa   = array_sum(array_map('floatval', $parts));
  $total  = round(4 * $berat_kg * $tbsa);
  $c8     = round($total / 2);
  $c16    = $total - $c8;

  // Insert ke DB
  $stmt = $pdo->prepare("
    INSERT INTO hasil_lukabakar
      (user_id, nama, alamat, tbsa, total_cairan, cairan_8jam, cairan_16jam,
       berat_kg, usia, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
  ");
  $stmt->execute([
    $_SESSION['user']['id'],
    $nama, $alamat,
    $tbsa, $total, $c8, $c16,
    $berat_kg, $usia
  ]);
  header("Location: HasilLukaBakar.php");
  exit;
}

// 4) Tampilkan riwayat (semua user)
$stmt = $pdo->query("SELECT * FROM hasil_lukabakar ORDER BY created_at DESC");
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?= htmlspecialchars($teks['riwayat_luka_bakar']) ?></title>
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
      <h2><?= htmlspecialchars($teks['riwayat_luka_bakar']) ?></h2>
    </div>
    <div class="form-box">
      <?php if (isset($_GET['hapus_berhasil'])): ?>
        <p class="result-hjk" style="color:green"><?= htmlspecialchars($teks['data_berhasil_dihapus']) ?></p>
      <?php endif; ?>

      <div class="table-responsive">
        <table id="riwayatTable">
          <thead>
            <tr>
              <th><?= htmlspecialchars($teks['nama']) ?></th>
              <th><?= htmlspecialchars($teks['alamat']) ?></th>
              <th><?= htmlspecialchars($teks['tbsa']) ?></th>
              <th><?= htmlspecialchars($teks['usia']) ?></th>
              <th><?= htmlspecialchars($teks['total_cairan']) ?></th>
              <th><?= htmlspecialchars($teks['aksi'] ?? 'Aksi') ?></th>
            </tr>
          </thead>
          <tbody id="tableBody">
            <?php foreach($riwayat as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td><?= htmlspecialchars($row['alamat']) ?></td>
                <td><?= htmlspecialchars($row['tbsa']) ?></td>
                <td><?= htmlspecialchars($row['usia']) ?></td>
                <td><?= number_format($row['total_cairan']) ?></td>
                <td>
                  <a href="?id=<?= $row['id'] ?>"><?= htmlspecialchars($teks['lihat']) ?></a>
                  <a href="?hapus=<?= $row['id'] ?>" class="delete"
                     onclick="return confirm('<?= htmlspecialchars($teks['confirm_hapus'] ?? 'Yakin ingin menghapus?') ?>')">
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
    const rowsPerPage=6, tbody=document.getElementById('tableBody'),
          rows=Array.from(tbody.querySelectorAll('tr')),
          pagination=document.getElementById('pagination');
    let currentPage=1;
    function displayTable(){
      const start=(currentPage-1)*rowsPerPage, end=start+rowsPerPage;
      rows.forEach((r,i)=>r.style.display=(i>=start&&i<end)?'':'none');
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
        const nama=r.cells[0].textContent.toLowerCase(),
              alamat=r.cells[1].textContent.toLowerCase(),
              tbsa=r.cells[2].textContent.toLowerCase();
        r.style.display=(nama.includes(q)||alamat.includes(q)||tbsa.includes(q))?'':'none';
      });
      currentPage=1; displayTable();
    }
    displayTable();
  </script>
</body>
</html>
