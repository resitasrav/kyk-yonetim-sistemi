<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['yonetici_id'])) {
    header('Location: ../login.php');
    exit;
}

$rol    = $_SESSION['rol']    ?? 'yonetici';
$modul  = $_SESSION['modul']  ?? '';

// Rol + modüle göre navbar rengi ve başlık
if ($rol === 'ogrenci') {
    $nav_renk  = 'bg-success';
    $nav_ikon  = 'bi-mortarboard-fill';
    $modul_adi = 'Öğrenci Paneli';
} elseif ($rol === 'personel') {
    $nav_renk  = 'bg-primary';
    $nav_ikon  = 'bi-person-badge-fill';
    $modul_adi = 'Personel Paneli';
} else {
    // Yönetici — modüle göre renk
    if ($modul === 'personel') {
        $nav_renk  = 'bg-success';
        $nav_ikon  = 'bi-person-badge';
        $modul_adi = 'Personel Yönetimi';
    } elseif ($modul === 'yonetici') {
        $nav_renk  = 'bg-dark';
        $nav_ikon  = 'bi-person-gear';
        $modul_adi = 'Profil Ayarları';
    } else {
        $nav_renk  = 'bg-dark';
        $nav_ikon  = 'bi-mortarboard';
        $modul_adi = 'Öğrenci Yönetimi';
    }
}

// Ana sayfaya (dashboard) dönüş linki role göre
$ana_sayfa = ($rol === 'ogrenci') ? '../ogrenci/panel.php'
           : (($rol === 'personel') ? '../personel/panel.php' : '../index.php');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($sayfa_basligi ?? 'KYK Yönetim Sistemi') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { display: flex; flex-direction: column; min-height: 100vh; background-color: #f4f6f9; }
        main { flex: 1; }
        .navbar-brand { font-weight: 700; letter-spacing: 0.5px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark <?= $nav_renk ?> mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="<?= $ana_sayfa ?>">
            <i class="bi <?= $nav_ikon ?>"></i>
            KYK — <?= $modul_adi ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">

                <?php if ($rol === 'yonetici'): ?>
                    <?php if ($modul === 'ogrenci'): ?>
                        <li class="nav-item"><a class="nav-link" href="../ogrenci/liste.php"><i class="bi bi-list-ul"></i> Öğrenciler</a></li>
                        <li class="nav-item"><a class="nav-link" href="../ogrenci/ekle.php"><i class="bi bi-person-plus"></i> Yeni Kayıt</a></li>
                        <li class="nav-item"><a class="nav-link" href="../ogrenci/izin_liste.php"><i class="bi bi-calendar-event"></i> İzinler</a></li>
                    <?php elseif ($modul === 'personel'): ?>
                        <li class="nav-item"><a class="nav-link" href="../personel/liste.php"><i class="bi bi-people"></i> Personel</a></li>
                        <li class="nav-item"><a class="nav-link" href="../personel/ekle.php"><i class="bi bi-person-plus"></i> Yeni Kayıt</a></li>
                        <li class="nav-item"><a class="nav-link" href="../personel/izin_liste.php"><i class="bi bi-calendar-event"></i> İzinler</a></li>
                        <li class="nav-item"><a class="nav-link" href="../personel/maas_liste.php"><i class="bi bi-cash-coin"></i> Maaşlar</a></li>
                    <?php endif; ?>

                <?php elseif ($rol === 'personel'): ?>
                    <li class="nav-item"><a class="nav-link" href="../personel/panel.php"><i class="bi bi-house"></i> Panelim</a></li>
                    <li class="nav-item"><a class="nav-link" href="../personel/izin_ekle.php"><i class="bi bi-plus-circle"></i> İzin Talebi</a></li>
                    <li class="nav-item"><a class="nav-link" href="../personel/izin_liste.php?kendi=1"><i class="bi bi-calendar-check"></i> İzinlerim</a></li>
                    <li class="nav-item"><a class="nav-link" href="../personel/maas_liste.php?kendi=1"><i class="bi bi-cash-coin"></i> Maaşlarım</a></li>

                <?php elseif ($rol === 'ogrenci'): ?>
                    <li class="nav-item"><a class="nav-link" href="../ogrenci/panel.php"><i class="bi bi-house"></i> Panelim</a></li>
                    <li class="nav-item"><a class="nav-link" href="../etkinlik/liste.php"><i class="bi bi-calendar-event"></i> Etkinlikler</a></li>
                    <li class="nav-item"><a class="nav-link" href="../ogrenci/izin_ekle.php"><i class="bi bi-plus-circle"></i> İzin Talebi</a></li>
                    <li class="nav-item"><a class="nav-link" href="../ogrenci/izin_liste.php"><i class="bi bi-calendar-check"></i> İzinlerim</a></li>
                <?php endif; ?>

            </ul>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <?php if ($rol === 'yonetici'): ?>
                    <a href="../yonetici/profil.php" class="btn btn-outline-light btn-sm border-0">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['yonetici_ad']) ?>
                    </a>
                    <a href="../index.php" class="btn btn-light btn-sm fw-bold">
                        <i class="bi bi-grid"></i> Modül Değiştir
                    </a>
                <?php else: ?>
                    <span class="text-white-50 small me-1">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['yonetici_ad']) ?>
                    </span>
                <?php endif; ?>
                <a href="../logout.php" class="btn btn-danger btn-sm fw-bold">
                    <i class="bi bi-box-arrow-right"></i> Çıkış
                </a>
            </div>
        </div>
    </div>
</nav>

<main class="container">

    <?php if (isset($_SESSION['mesaj'])): ?>
        <div class="alert alert-<?= $_SESSION['mesaj_tur'] ?? 'info' ?> alert-dismissible fade show shadow-sm" role="alert">
            <?= htmlspecialchars($_SESSION['mesaj']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
        </div>
        <?php unset($_SESSION['mesaj'], $_SESSION['mesaj_tur']); ?>
    <?php endif; ?>
