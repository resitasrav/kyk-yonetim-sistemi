<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

if (isset($_SESSION['yonetici_id'])) {
    header("Location: index.php");
    exit;
}

$hata       = "";
$rol_secili = in_array($_POST['rol_secim'] ?? '', ['yonetici','personel','ogrenci'])
              ? $_POST['rol_secim'] : 'yonetici';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $sifre = $_POST['sifre'] ?? '';

    $stmt = $db->prepare("SELECT * FROM yoneticiler WHERE email = ?");
    $stmt->execute([$email]);
    $kullanici = $stmt->fetch();

    if ($kullanici && password_verify($sifre, $kullanici['sifre'])) {
        // Seçilen sekme ile kayıttaki rol eşleşmeli
        if ($kullanici['rol'] !== $rol_secili) {
            $etiket = ['yonetici' => 'Yönetici', 'personel' => 'Personel', 'ogrenci' => 'Öğrenci'];
            $hata = "Bu hesap " . ($etiket[$kullanici['rol']] ?? $kullanici['rol']) . " rolüne sahip. Lütfen doğru sekmeyi seçin.";
        } else {
            $_SESSION['yonetici_id'] = $kullanici['id'];
            $_SESSION['yonetici_ad'] = $kullanici['ad_soyad'];
            $_SESSION['rol']         = $kullanici['rol'];
            $_SESSION['ilgili_id']   = $kullanici['ilgili_id'];

            if ($kullanici['rol'] === 'personel') {
                header("Location: personel/panel.php");
            } elseif ($kullanici['rol'] === 'ogrenci') {
                header("Location: ogrenci/panel.php");
            } else {
                header("Location: index.php");
            }
            exit;
        }
    } else {
        $hata = "E-posta adresi veya şifre hatalı!";
    }
}

$renkler = [
    'yonetici' => ['bg' => '#212529', 'ikon' => 'shield-fill',       'etiket' => 'Yönetici'],
    'personel' => ['bg' => '#0d6efd', 'ikon' => 'person-badge-fill', 'etiket' => 'Personel'],
    'ogrenci'  => ['bg' => '#198754', 'ikon' => 'mortarboard-fill',  'etiket' => 'Öğrenci' ],
];
$aktif = $renkler[$rol_secili];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sisteme Giriş — KYK Yönetim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); min-height: 100vh; }
        .sekme { border-radius: 0; border: none; transition: .15s; color: #adb5bd; background: #343a40; }
        .sekme:first-child { border-radius: 8px 0 0 8px; }
        .sekme:last-child  { border-radius: 0 8px 8px 0; }
        .sekme:hover { opacity: .85; }
        .sekme.aktif { color: #fff; font-weight: 700; }
        .sekme-yonetici.aktif { background: #212529; }
        .sekme-personel.aktif { background: #0d6efd; }
        .sekme-ogrenci.aktif  { background: #198754; }
        .kart-header { transition: background .3s; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height:100vh;">
<div class="card shadow-lg border-0" style="width:420px;">

    <!-- Üst renk şeridi -->
    <div class="card-header kart-header text-white text-center py-4 border-0"
         id="kartHeader" style="background:<?= $aktif['bg'] ?>; border-radius: 8px 8px 0 0;">
        <i class="bi bi-<?= $aktif['ikon'] ?>" style="font-size:2.5rem;" id="headerIkon"></i>
        <h4 class="fw-bold mt-2 mb-0">KYK Yönetim Sistemi</h4>
        <p class="mb-0 small opacity-75" id="headerAlt"><?= $aktif['etiket'] ?> girişi</p>
    </div>

    <div class="card-body p-4">
        <!-- Rol sekmeleri -->
        <form method="POST" id="loginForm">
            <div class="d-flex mb-4 border rounded overflow-hidden">
                <?php foreach ($renkler as $rol => $r): ?>
                <button type="button"
                        class="btn sekme sekme-<?= $rol ?> w-100 py-2 <?= ($rol === $rol_secili) ? 'aktif' : '' ?>"
                        data-rol="<?= $rol ?>"
                        data-bg="<?= $r['bg'] ?>"
                        data-ikon="<?= $r['ikon'] ?>"
                        data-etiket="<?= $r['etiket'] ?>">
                    <i class="bi bi-<?= $r['ikon'] ?> me-1"></i>
                    <span class="d-none d-sm-inline"><?= $r['etiket'] ?></span>
                </button>
                <?php endforeach; ?>
            </div>
            <input type="hidden" name="rol_secim" id="rolSecim" value="<?= htmlspecialchars($rol_secili) ?>">

            <?php if (isset($_SESSION['mesaj'])): ?>
                <div class="alert alert-<?= $_SESSION['mesaj_tur'] ?> p-2 text-center small">
                    <?= htmlspecialchars($_SESSION['mesaj']) ?>
                </div>
                <?php unset($_SESSION['mesaj'], $_SESSION['mesaj_tur']); ?>
            <?php endif; ?>

            <?php if ($hata): ?>
                <div class="alert alert-danger p-2 small"><i class="bi bi-x-circle"></i> <?= htmlspecialchars($hata) ?></div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label fw-semibold">E-posta Adresi</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control"
                           placeholder="ornek@kyk.gov.tr"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Şifre</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="sifre" class="form-control" required>
                </div>
            </div>

            <button type="submit" class="btn w-100 fw-bold text-white" id="loginBtn"
                    style="background:<?= $aktif['bg'] ?>;">
                <i class="bi bi-box-arrow-in-right"></i>
                <span id="loginBtnYazi"><?= $aktif['etiket'] ?> Olarak Giriş Yap</span>
            </button>
        </form>

        <div class="text-center mt-3">
            <a href="kayit.php" class="text-decoration-none small">
                Hesabın yok mu? <strong>Kayıt Ol</strong>
            </a>
        </div>
    </div>
</div>

<script>
const sekmeler    = document.querySelectorAll('.sekme');
const rolSecim    = document.getElementById('rolSecim');
const kartHeader  = document.getElementById('kartHeader');
const headerIkon  = document.getElementById('headerIkon');
const headerAlt   = document.getElementById('headerAlt');
const loginBtn    = document.getElementById('loginBtn');
const loginBtnYazi = document.getElementById('loginBtnYazi');

sekmeler.forEach(sekme => {
    sekme.addEventListener('click', function () {
        const rol    = this.dataset.rol;
        const bg     = this.dataset.bg;
        const ikon   = this.dataset.ikon;
        const etiket = this.dataset.etiket;

        // Aktif sekmeyi güncelle
        sekmeler.forEach(s => s.classList.remove('aktif'));
        this.classList.add('aktif');

        // Gizli input
        rolSecim.value = rol;

        // Header güncelle
        kartHeader.style.background = bg;
        headerIkon.className = 'bi bi-' + ikon;
        headerAlt.textContent = etiket + ' girişi';

        // Buton güncelle
        loginBtn.style.background = bg;
        loginBtnYazi.textContent = etiket + ' Olarak Giriş Yap';
    });
});
</script>
</body>
</html>
