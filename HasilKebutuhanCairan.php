<?php
require_once __DIR__ . '/config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}
$user_id = $_SESSION['user']['id'];

// 1) Hapus data jika diminta (tanpa filter user_id)
if (isset($_GET['hapus'])) {
  $id = (int)$_GET['hapus'];
  $pdo->prepare("DELETE FROM fluid_data WHERE id = ?")
      ->execute([$id]);
  header("Location: HasilKebutuhanCairan.php?hapus=ok");
  exit;
}

// 2) Detail view jika ada id (tanpa filter user_id)
if (isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $stmt = $pdo->prepare("SELECT * FROM fluid_data WHERE id = ?");
  $stmt->execute([$id]);
  $d = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$d) die("Data tidak ditemukan.");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><title>Detail Cairan</title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
</head>
<body>
<a class="back" href="HasilKebutuhanCairan.php">« BACK</a>
<div class="main-container">
  <div class="content-text">
    <h6>Kalkulator</h6>
    <h2>Detail Kebutuhan Cairan</h2>
  </div>
  <div class="form-box">
    <p><strong>Nama:</strong> <?= htmlspecialchars($d['nama']) ?></p>
    <p><strong>Usia:</strong> <?= htmlspecialchars($d['umur']) ?> tahun</p>
    <p><strong>Alamat:</strong> <?= htmlspecialchars($d['alamat']) ?></p>
    <hr>
    <p><strong>Mode Hitung:</strong> <?= $d['mode_calc']==='per24'?'Per 24 jam':'Per Jam' ?></p>
    <p><strong>Berat Badan:</strong> <?= htmlspecialchars($d['berat_kg']) ?> kg</p>
    <p><strong>Kondisi:</strong> <?= htmlspecialchars($d['kondisi']) ?></p>
    <hr>
    <?php if ($d['mode_calc']==='per24'): ?>
      <p><strong>Total Cairan (24 jam):</strong> <?= round((float)$d['total_ml_per24'],2) ?> mL</p>
      <p><strong>Per Jam:</strong> <?= round((float)$d['total_ml_per24']/24,2) ?> mL/jam</p>
    <?php else: ?>
      <p><strong>Per Jam:</strong> <?= round((float)$d['ml_per_jam'],2) ?> mL/jam</p>
      <p><strong>Per 24 Jam:</strong> <?= round((float)$d['ml_per_jam']*24,2) ?> mL</p>
    <?php endif; ?>

    <a href="HasilKebutuhanCairan.php"><button>Kembali ke Riwayat</button></a>
  </div>
</div>
</body>
</html>
<?php
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama   = trim($_POST['nama']);
  $umur   = (int)$_POST['umur'];
  $alamat = trim($_POST['alamat']);
  $mode   = $_POST['mode_calc'];
  $w      = (float)$_POST['berat_kg'];
  $kond   = $_POST['kondisi'];

  function calc_per24($w){
    $remain = $w; $vol = 0;
    if ($remain>0){
      $use = min($remain,10);
      $vol += $use * 100;
      $remain -= $use;
    }
    if ($remain>0){
      $use = min($remain,10);
      $vol += $use * 50;
      $remain -= $use;
    }
    if ($remain>0){
      $vol += $remain * 20;
    }
    return $vol;
  }
  function calc_perjam($w){
    $remain = $w; $vol = 0;
    if ($remain>0){
      $use = min($remain,10);
      $vol += $use * 4;
      $remain -= $use;
    }
    if ($remain>0){
      $use = min($remain,10);
      $vol += $use * 2;
      $remain -= $use;
    }
    if ($remain>0){
      $vol += $remain * 1;
    }
    return $vol;
  }

  if ($mode==='per24') {
    $total24 = calc_per24($w);
    $perjam  = $total24 / 24;
    $tot24   = $total24;
    $pj      = $perjam;
  } else {
    $pj    = calc_perjam($w);
    $tot24 = $pj * 24;
  }

  $stmt = $pdo->prepare(
    "INSERT INTO fluid_data
     (user_id,nama,umur,alamat,mode_calc,berat_kg,kondisi,total_ml_per24,ml_per_jam)
     VALUES(?,?,?,?,?,?,?,?,?)"
  );
  $stmt->execute([
    $user_id, $nama, $umur, $alamat,
    $mode, $w, $kond,
    $mode==='per24' ? $tot24 : null,
    $mode==='perjam' ? $pj : null
  ]);

  header("Location: HasilKebutuhanCairan.php");
  exit;
}

// 4) Tampilkan semua riwayat (tanpa filter user_id)
$stmt = $pdo->query("SELECT * FROM fluid_data ORDER BY created_at DESC");
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><title>Riwayat Kebutuhan Cairan</title>
  <link rel="stylesheet" href="public/style/root.css">
  <link rel="stylesheet" href="public/style/form.css">
  <style>
    .search-box { width:100%;max-width:300px;padding:8px 12px;margin-bottom:1rem;border:1px solid #ccc;border-radius:8px;font-size:14px; }
    .table-responsive { overflow-x:auto; }
    table { width:100%;border-collapse:collapse;margin-bottom:1rem; }
    th, td { padding:10px;border:1px solid #ccc;text-align:left; }
    th { background-color:#f8f8f8; }
    .pagination { display:flex;justify-content:center;gap:8px;margin-bottom:2rem; }
    .pagination button { padding:6px 12px;border:none;background-color:#e0e0e0;border-radius:4px;cursor:pointer;font-size:14px; }
    .pagination button.active { background-color:#007bff;color:white; }
    .pagination button:hover:not(.active){background-color:#ccc;}
    a.delete { color:red;margin-left:8px; }
  </style>
</head>
<body>
<a class="back" href="index.php">« BACK</a>
<div class="main-container">
  <div class="content-text">
    <h6>Kalkulator</h6>
    <h2>Riwayat Kebutuhan Cairan</h2>
  </div>
  <div class="form-box">
    <?php if(isset($_GET['hapus'])): ?>
      <p style="color:green">Data berhasil dihapus.</p>
    <?php endif; ?>
    <input type="text" id="searchInput" class="search-box" placeholder="Cari nama..." onkeyup="filterTable()">
    <div class="table-responsive">
      <table id="riwayatTable">
        <thead>
          <tr>
            <th>Nama</th>
            <th>Mode</th>
            <th>Cairan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <?php foreach($riwayat as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['nama']) ?></td>
              <td><?= $r['mode_calc']==='per24'?'24 jam':'Per Jam' ?></td>
              <td>
                <?php if ($r['mode_calc']==='per24'): ?>
                  <?= round((float)$r['total_ml_per24'],2) ?> mL
                <?php else: ?>
                  <?= round((float)$r['ml_per_jam'],2) ?> mL/jam
                <?php endif; ?>
              </td>
              <td>
                <a href="?id=<?= $r['id'] ?>">Lihat</a>
                <a href="?hapus=<?= $r['id'] ?>" class="delete" onclick="return confirm('Yakin?')">Hapus</a>
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
const rowsPerPage=6, tbody=document.getElementById("tableBody"),
      rows=Array.from(tbody.querySelectorAll("tr")), pagination=document.getElementById("pagination");
let currentPage=1;
function displayTable(){
  const start=(currentPage-1)*rowsPerPage,end=start+rowsPerPage;
  rows.forEach((r,i)=>r.style.display=(i>=start&&i<end)?"":"none");
  renderPagination();
}
function renderPagination(){
  const pages=Math.ceil(rows.length/rowsPerPage);
  pagination.innerHTML="";
  for(let i=1;i<=pages;i++){
    let btn=document.createElement("button"); btn.textContent=i;
    if(i===currentPage) btn.classList.add("active");
    btn.onclick=()=>{currentPage=i;displayTable();};
    pagination.appendChild(btn);
  }
}
function filterTable(){
  const q=document.getElementById("searchInput").value.toLowerCase();
  rows.forEach(r=>r.style.display=r.cells[0].textContent.toLowerCase().includes(q)?"":"none");
  currentPage=1;displayTable();
}
displayTable();
</script>
</body>
</html>
