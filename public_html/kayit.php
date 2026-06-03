<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

if (isset($_SESSION['yonetici_id'])) {
    header("Location: index.php");
    exit;
}

$hatalar    = [];
$rol_secili = in_array($_POST['rol'] ?? '', ['yonetici','personel','ogrenci'])
              ? $_POST['rol'] : 'yonetici';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad_soyad     = trim($_POST['ad_soyad'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $sifre        = $_POST['sifre'] ?? '';
    $sifre_tekrar = $_POST['sifre_tekrar'] ?? '';

    // Temel doğrulamalar
    if (!$ad_soyad)  $hatalar[] = "Ad Soyad giriniz.";
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL))
        $hatalar[] = "Geçerli bir e-posta adresi giriniz.";
    if (strlen($sifre) < 6)       $hatalar[] = "Şifre en az 6 karakter olmalıdır.";
    if ($sifre !== $sifre_tekrar) $hatalar[] = "Şifreler uyuşmuyor.";

    // Rol bazlı zorunlu alanlar
    if ($rol_secili === 'personel') {
        if (!trim($_POST['sicil_no']  ?? '')) $hatalar[] = "Sicil No zorunludur.";
        if (!trim($_POST['ise_giris'] ?? '')) $hatalar[] = "İşe giriş tarihi zorunludur.";
    } elseif ($rol_secili === 'ogrenci') {
        if (!trim($_POST['okul_no'] ?? '')) $hatalar[] = "Okul No zorunludur.";
    }

    // E-posta tekrar kontrolü
    if (empty($hatalar)) {
        $kontrol = $db->prepare("SELECT id FROM yoneticiler WHERE email = ?");
        $kontrol->execute([$email]);
        if ($kontrol->fetch()) $hatalar[] = "Bu e-posta adresiyle zaten bir hesap mevcut.";
    }

    if (empty($hatalar)) {
        $kriptolu  = password_hash($sifre, PASSWORD_DEFAULT);
        $ilgili_id = null;

        // Ad / soyad ayrıştır
        $parcalar = explode(' ', trim($ad_soyad), 2);
        $ad       = $parcalar[0];
        $soyad    = $parcalar[1] ?? '';

        try {
            $db->beginTransaction();

            if ($rol_secili === 'personel') {
                $sicil_no = trim($_POST['sicil_no'] ?? '');

                $ck = $db->prepare("SELECT id FROM personel WHERE sicil_no = ?");
                $ck->execute([$sicil_no]);
                if ($ck->fetch()) throw new Exception("Bu sicil numarası zaten kayıtlı.");

                $ins = $db->prepare(
                    "INSERT INTO personel (ad, soyad, sicil_no, email, ise_giris, durum)
                     VALUES (?, ?, ?, ?, ?, 'aktif')"
                );
                $ins->execute([
                    $ad, $soyad, $sicil_no, $email,
                    trim($_POST['ise_giris'] ?? '') ?: date('Y-m-d'),
                ]);
                $ilgili_id = $db->lastInsertId();

            } elseif ($rol_secili === 'ogrenci') {
                $okul_no = trim($_POST['okul_no'] ?? '');

                $ck = $db->prepare("SELECT id FROM ogrenciler WHERE okul_no = ?");
                $ck->execute([$okul_no]);
                if ($ck->fetch()) throw new Exception("Bu okul numarası zaten kayıtlı.");

                $ins = $db->prepare(
                    "INSERT INTO ogrenciler (ad, soyad, okul_no, oda_no, email, durum)
                     VALUES (?, ?, ?, ?, ?, 'aktif')"
                );
                $ins->execute([
                    $ad, $soyad, $okul_no,
                    trim($_POST['oda_no'] ?? '') ?: null,
                    $email,
                ]);
                $ilgili_id = $db->lastInsertId();
            }

            $db->prepare(
                "INSERT INTO yoneticiler (ad_soyad, email, sifre, rol, ilgili_id) VALUES (?, ?, ?, ?, ?)"
            )->execute([$ad_soyad, $email, $kriptolu, $rol_secili, $ilgili_id]);

            $db->commit();
            mesaj_ayarla("Kayıt başarılı! Şimdi giriş yapabilirsiniz.", "success");
            header("Location: login.php");
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            $hatalar[] = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol — KYK Yönetim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); min-height: 100vh; }
        .sekme { border-radius: 0; border: none; transition: .15s; color: #adb5bd; background: #343a40; }
        .sekme:first-child { border-radius: 8px 0 0 8px; }
        .sekme:last-child  { border-radius: 0 8px 8px 0; }
        .sekme.aktif { color: #fff; font-weight: 700; }
        .sekme-yonetici.aktif { background: #212529; }
        .sekme-personel.aktif { background: #0d6efd; }
        .sekme-ogrenci.aktif  { background: #198754; }
    </style>
</head>
<body class="d-flex align-items-start justify-content-center py-5" style="min-height:100vh;">
<div class="card shadow-lg border-0" style="width:460px;">
    <div class="card-body p-5">
        <h3 class="text-center mb-4 fw-bold">
            <i class="bi bi-person-plus text-primary"></i> Kayıt Ol
        </h3>

        <?php foreach ($hatalar as $h): ?>
            <div class="alert alert-danger p-2 small mb-2">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($h) ?>
            </div>
        <?php endforeach; ?>

        <form method="POST" id="kayitForm">
            <!-- Hesap türü seçimi -->
            <div class="mb-4">
                <label class="form-label fw-semibold">Hesap Türü</label>
                <div class="d-flex border rounded overflow-hidden">
                    <button type="button" class="btn w-100 sekme sekme-yonetici py-2" data-rol="yonetici">
                        <i class="bi bi-shield-fill"></i> Yönetici
                    </button>
                    <button type="button" class="btn w-100 sekme sekme-personel py-2 border-start border-end" data-rol="personel">
                        <i class="bi bi-person-badge-fill"></i> Personel
                    </button>
                    <button type="button" class="btn w-100 sekme sekme-ogrenci py-2" data-rol="ogrenci">
                        <i class="bi bi-mortarboard-fill"></i> Öğrenci
                    </button>
                </div>
                <input type="hidden" name="rol" id="rolInput" value="<?= htmlspecialchars($rol_secili) ?>">
            </div>

            <!-- Ortak alanlar -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Ad Soyad</label>
                <input type="text" name="ad_soyad" class="form-control"
                       value="<?= htmlspecialchars($_POST['ad_soyad'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">E-posta Adresi</label>
                <input type="email" name="email" class="form-control"
                       placeholder="ornek@kyk.gov.tr"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>

            <!-- Personel ek alanları -->
            <div id="personelAlanlar"
                 style="display:<?= ($rol_secili === 'personel') ? 'block' : 'none' ?>;">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Sicil No <span class="text-danger">*</span></label>
                    <input type="text" name="sicil_no" class="form-control"
                           placeholder="Örn: P003"
                           value="<?= htmlspecialchars($_POST['sicil_no'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">İşe Giriş Tarihi <span class="text-danger">*</span></label>
                    <input type="date" name="ise_giris" class="form-control"
                           value="<?= htmlspecialchars($_POST['ise_giris'] ?? '') ?>">
                </div>
            </div>

            <!-- Öğrenci ek alanları -->
            <div id="ogrenciAlanlar"
                 style="display:<?= ($rol_secili === 'ogrenci') ? 'block' : 'none' ?>;">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Okul No <span class="text-danger">*</span></label>
                    <input type="text" name="okul_no" class="form-control"
                           placeholder="Örn: 2021001"
                           value="<?= htmlspecialchars($_POST['okul_no'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Oda No</label>
                    <input type="text" name="oda_no" class="form-control"
                           placeholder="Örn: 101"
                           value="<?= htmlspecialchars($_POST['oda_no'] ?? '') ?>">
                    <div class="form-text">Oda ataması daha sonra da yapılabilir.</div>
                </div>
            </div>

            <!-- Şifre -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Şifre</label>
                <input type="password" name="sifre" class="form-control" minlength="6" required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Şifre (Tekrar)</label>
                <input type="password" name="sifre_tekrar" class="form-control" required>
            </div>

            <button type="submit" class="btn w-100 fw-bold text-white" id="submitBtn">
                <i class="bi bi-check-circle"></i> Kayıt Ol
            </button>
        </form>

        <div class="text-center mt-3">
            <a href="login.php" class="text-decoration-none small">
                Zaten hesabın var mı? <strong>Giriş Yap</strong>
            </a>
        </div>
    </div>
</div>

<script>
const rolInput        = document.getElementById('rolInput');
const personelAlanlar = document.getElementById('personelAlanlar');
const ogrenciAlanlar  = document.getElementById('ogrenciAlanlar');
const submitBtn       = document.getElementById('submitBtn');
const tabBtnler       = document.querySelectorAll('[data-rol]');

const ayarlar = {
    yonetici: { renk: '#212529', cls: 'btn-dark',    yazi: 'Yönetici Olarak Kayıt Ol'  },
    personel:  { renk: '#0d6efd', cls: 'btn-primary', yazi: 'Personel Olarak Kayıt Ol'  },
    ogrenci:   { renk: '#198754', cls: 'btn-success', yazi: 'Öğrenci Olarak Kayıt Ol'   },
};

function rolUygula(rol) {
    rolInput.value = rol;

    tabBtnler.forEach(btn => {
        btn.classList.remove('aktif');
        if (btn.dataset.rol === rol) btn.classList.add('aktif');
    });

    personelAlanlar.style.display = (rol === 'personel') ? 'block' : 'none';
    ogrenciAlanlar.style.display  = (rol === 'ogrenci')  ? 'block' : 'none';

    submitBtn.style.background = ayarlar[rol].renk;
    submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> ' + ayarlar[rol].yazi;
}

tabBtnler.forEach(btn => btn.addEventListener('click', () => rolUygula(btn.dataset.rol)));
rolUygula(rolInput.value || 'yonetici');
</script>
</body>
</html>
