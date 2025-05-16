<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lang/lang.php';

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$lang = $user['lang'] ?? 'id';
$namaLengkap = htmlspecialchars($user['nama_lengkap']);

// Ambil bahasa sesuai session
$teks = $lang === 'en' ? $bahasa_en : $bahasa_id;
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>NurseCount</title>
  <link rel="stylesheet" href="public/style/index.css">
  <link rel="stylesheet" href="public/style/root.css">
</head>
<body>
  <header>
    <h1 class="screen-title"></h1>
    <div id="menuToggle" class="menu-toggle">☰</div>
  </header>

  <div id="overlayMenu" class="overlay">
  <div class="menu-content">
    <div class="menu-header">
      <span><?= $namaLengkap ?></span>
      <div id="closeMenu" class="close-btn">×</div>
    </div>
    <ul class="menu-list">
      <li><a href="MyAccount.php"><?= $teks['akun_saya'] ?></a></li>

      <li class="has-dropdown">
        <button id="riwayatToggle" class="dropdown-btn">
          <?= $teks['riwayat_data'] ?> <span class="arrow">▾</span>
        </button>
        <ul id="riwayatMenu" class="dropdown-menu">
          <li><a href="HasilLukaBakar.php"><?= $teks['lukabakar'] ?></a></li>
          <li><a href="HasilLajuInfus.php"><?= $teks['laju_infus'] ?></a></li>
          <li><a href="HasilIMT.php"><?= $teks['imt'] ?></a></li>
          <li><a href="HasilGCS.php"><?= $teks['judul_index'] ?></a></li>
          <li><a href="HasilDosisObat.php"><?= $teks['dosis_obat'] ?></a></li>
          <li><a href="HasilKebutuhanCairan.php"><?= $teks['kebutuhan_cairan'] ?></a></li>
          <li><a href="HasilSkorMorse.php"><?= $teks['skorMorse'] ?></a></li>
          <li><a href="HasilKebutuhanKalori.php"><?= $teks['kebutuhanKalori'] ?></a></li>
        </ul>
      </li>

      <!-- Bahasa Dropdown -->
      <li class="has-dropdown">
        <button id="bahasaToggle" class="dropdown-btn">
          <?= $teks['bahasa'] ?? 'Bahasa' ?> <span class="arrow">▾</span>
        </button>
        <ul id="bahasaMenu" class="dropdown-menu">
          <li><a href="set_lang.php?lang=id">🇮🇩 Indonesia</a></li>
          <li><a href="set_lang.php?lang=en">🇺🇸 English</a></li>
        </ul>
      </li>
    </ul>
    <a href="auth/logout.php" class="logout-btn"><?= $teks['logout'] ?></a>
  </div>
</div>


  <main class="container">
    <div class="hero">
      <img src="image/1.png" style="width:25%">
      <p><?= $teks['halo'] ?></p>
      <h1>NURSECOUNT</h1>
      <p><?= $teks['bantuan'] ?></p>
    </div>
    <div class="grid-buttons">
      <a href="LukaBakar.php" class="btn">
        <img src="image/5.png" alt="LukaBakar" class="icon-img">
        <span class="btn-text"><?= $teks['lukabakar'] ?></span>
      </a>

      <a href="LajuInfus.php" class="btn">
        <img src="image/4.png" alt="Infus" class="icon-img">
        <span class="btn-text"><?= $teks['laju_infus'] ?></span>
      </a>

      <a href="IMT.php" class="btn">
        <img src="image/9.png" alt="IMT" class="icon-img">
        <span class="btn-text"><?= $teks['imt'] ?></span>
      </a>

      <a href="GCS.php" class="btn">
        <img src="image/8.png" alt="GCS" class="icon-img">
        <span class="btn-text"><?= $teks['judul_index'] ?></span>
      </a>

      <a href="DosisObat.php" class="btn">
        <img src="image/7.png" alt="GCS" class="icon-img">
        <span class="btn-text"><?= $teks['dosis_obat'] ?></span>
      </a>

      <a href="KebutuhanCairan.php" class="btn">
        <img src="image/3.png" alt="GCS" class="icon-img" style="left:1rem; position:relative;">
        <span class="btn-text" style="padding-left:1rem;"><?= $teks['kebutuhan_cairan'] ?></span>
      </a>

      <a href="SkorMorse.php" class="btn">
        <img src="image/10.png" alt="GCS" class="icon-img">
        <span class="btn-text"><?= $teks['skorMorse'] ?></span>
      </a>
      
      <a href="KebutuhanKalori.php" class="btn">
        <img src="image/6.png" alt="GCS" class="icon-img">
        <span class="btn-text"><?= $teks['kebutuhanKalori'] ?></span>
      </a>
    </div>
  </main>

  <script>
    const menuToggle = document.getElementById('menuToggle');
    const overlayMenu = document.getElementById('overlayMenu');
    const closeMenu = document.getElementById('closeMenu');
    const riwayatToggle = document.getElementById('riwayatToggle');
    const riwayatMenu = document.getElementById('riwayatMenu');

    menuToggle.addEventListener('click', () => {
      overlayMenu.classList.add('open');
    });
    closeMenu.addEventListener('click', () => {
      overlayMenu.classList.remove('open');
    });
    overlayMenu.addEventListener('click', e => {
      if (e.target === overlayMenu) overlayMenu.classList.remove('open');
    });
    riwayatToggle.addEventListener('click', () => {
      riwayatMenu.classList.toggle('open');
      riwayatToggle.querySelector('.arrow').textContent =
        riwayatMenu.classList.contains('open') ? '▴' : '▾';
    });

    const bahasaToggle = document.getElementById('bahasaToggle');
  const bahasaMenu = document.getElementById('bahasaMenu');

  bahasaToggle.addEventListener('click', () => {
    bahasaMenu.classList.toggle('open');
    bahasaToggle.querySelector('.arrow').textContent =
      bahasaMenu.classList.contains('open') ? '▴' : '▾';
  });
  </script>
</body>
</html>
