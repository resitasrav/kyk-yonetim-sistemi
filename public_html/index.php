<?php
session_start();
require_once 'config/db.php';

// GÜVENLİK DUVARI
if (!isset($_SESSION['yonetici_id'])) {
    header("Location: login.php");
    exit;
}

// Yönetici değilse kendi panosuna yönlendir
$rol = $_SESSION['rol'] ?? 'yonetici';
if ($rol === 'ogrenci') {
    header("Location: ogrenci/panel.php");
    exit;
} elseif ($rol === 'personel') {
    header("Location: personel/panel.php");
    exit;
}

// Modül seçimi
if (isset($_GET['modul'])) {
    $modul = $_GET['modul'];
    if (in_array($modul, ['ogrenci', 'personel', 'etkinlik'])) {
        $_SESSION['modul'] = $modul;
        if ($modul === 'etkinlik') {
            header('Location: etkinlik/liste.php');
        } else {
            header('Location: ' . $modul . '/liste.php');
        }
        exit;
    }
}

unset($_SESSION['modul']);

// Dashboard istatistikleri
$toplam_ogrenci  = $db->query("SELECT COUNT(*) FROM ogrenciler WHERE durum='aktif'")->fetchColumn();
$toplam_personel = $db->query("SELECT COUNT(*) FROM personel WHERE durum='aktif'")->fetchColumn();
$bekleyen_izin   = $db->query("SELECT COUNT(*) FROM izinler WHERE durum='bekliyor'")->fetchColumn();
$yaklasan_etkinlik = $db->query(
    "SELECT COUNT(*) FROM etkinlikler WHERE durum='aktif' AND etkinlik_tarihi >= CURDATE()"
)->fetchColumn();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KYK Yönetim Sistemi | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { background-color: #f4f6f9; display: flex; flex-direction: column; min-height: 100vh; }
        .module-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-top: 5px solid transparent;
            text-decoration: none;
            color: inherit;
        }
        .module-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 1rem 3rem rgba(0,0,0,.15) !important;
        }
        .card-ogrenci  { border-top-color: #0d6efd; }
        .card-personel { border-top-color: #198754; }
        .card-etkinlik { border-top-color: #fd7e14; }
        .icon-wrapper  { font-size: 4rem; margin-bottom: 0.75rem; }
        .stat-card     { border: none; border-radius: 12px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <span class="navbar-brand mb-0 h1">
            <i class="bi bi-building"></i> KYK Yönetim Sistemi
        </span>
        <div class="d-flex align-items-center gap-2">
            <span class="text-white-50 small d-none d-md-inline">
                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['yonetici_ad']) ?>
            </span>
            <a href="yonetici/profil.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-gear"></i> Profil
            </a>
            <a href="logout.php" class="btn btn-danger btn-sm">
                <i class="bi bi-box-arrow-right"></i> Çıkış
            </a>
        </div>
    </div>
</nav>

<div class="container py-5 flex-grow-1">

    <div class="text-center mb-5">
        <h2 class="fw-bold text-dark">Hoş Geldiniz, <?= htmlspecialchars(explode(' ', $_SESSION['yonetici_ad'])[0]) ?>!</h2>
        <p class="text-muted">Yönetmek istediğiniz modülü seçin</p>
    </div>

    <!-- İstatistik kartları -->
    <div class="row g-3 mb-5">
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm text-center p-3 bg-primary text-white">
                <div style="font-size:2rem;"><i class="bi bi-mortarboard-fill"></i></div>
                <div class="fs-2 fw-bold"><?= $toplam_ogrenci ?></div>
                <div class="small opacity-75">Aktif Öğrenci</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm text-center p-3 bg-success text-white">
                <div style="font-size:2rem;"><i class="bi bi-person-badge-fill"></i></div>
                <div class="fs-2 fw-bold"><?= $toplam_personel ?></div>
                <div class="small opacity-75">Aktif Personel</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <a href="personel/izin_liste.php" class="card stat-card shadow-sm text-center p-3 text-decoration-none
               <?= $bekleyen_izin > 0 ? 'bg-warning text-dark' : 'bg-light text-dark' ?>">
                <div style="font-size:2rem;"><i class="bi bi-hourglass-split"></i></div>
                <div class="fs-2 fw-bold"><?= $bekleyen_izin ?></div>
                <div class="small opacity-75">Bekleyen İzin</div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="etkinlik/liste.php" class="card stat-card shadow-sm text-center p-3 bg-warning text-dark text-decoration-none">
                <div style="font-size:2rem;"><i class="bi bi-calendar-event-fill"></i></div>
                <div class="fs-2 fw-bold"><?= $yaklasan_etkinlik ?></div>
                <div class="small opacity-75">Yaklaşan Etkinlik</div>
            </a>
        </div>
    </div>

    <!-- Modül kartları -->
    <div class="row g-4 justify-content-center">
        <div class="col-md-4">
            <a href="?modul=ogrenci" class="card module-card card-ogrenci h-100 shadow-sm text-center p-4">
                <div class="card-body">
                    <div class="icon-wrapper text-primary"><i class="bi bi-mortarboard-fill"></i></div>
                    <h4 class="card-title fw-bold text-primary mb-2">Öğrenci Modülü</h4>
                    <p class="card-text text-secondary small">Öğrenci kayıt, listeleme, düzenleme ve silme işlemleri</p>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="?modul=personel" class="card module-card card-personel h-100 shadow-sm text-center p-4">
                <div class="card-body">
                    <div class="icon-wrapper text-success"><i class="bi bi-person-badge-fill"></i></div>
                    <h4 class="card-title fw-bold text-success mb-2">Personel Modülü</h4>
                    <p class="card-text text-secondary small">Personel yönetimi, izin takibi ve maaş işlemleri</p>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="?modul=etkinlik" class="card module-card card-etkinlik h-100 shadow-sm text-center p-4">
                <div class="card-body">
                    <div class="icon-wrapper text-warning"><i class="bi bi-calendar-event-fill"></i></div>
                    <h4 class="card-title fw-bold text-warning mb-2">Etkinlik Modülü</h4>
                    <p class="card-text text-secondary small">Etkinlik oluşturma, yönetme ve katılımcı takibi</p>
                </div>
            </a>
        </div>
    </div>

</div>

<footer class="bg-dark text-white text-center py-3 mt-auto shadow-lg">
    <div class="container">
        <small>&copy; <?= date("Y") ?> KYK Yönetim Sistemi | Tüm Hakları Saklıdır.</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
