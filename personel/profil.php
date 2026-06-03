<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

rol_kontrol('personel');

$personel_id = (int)$_SESSION['ilgili_id'];
$yonetici_id = (int)$_SESSION['yonetici_id'];
$hatalar     = [];

// Mevcut kayıtları çek
$stmt = $db->prepare("SELECT * FROM personel WHERE id = ?");
$stmt->execute([$personel_id]);
$personel = $stmt->fetch();

$yStmt = $db->prepare("SELECT * FROM yoneticiler WHERE id = ?");
$yStmt->execute([$yonetici_id]);
$hesap = $yStmt->fetch();

if (!$personel || !$hesap) {
    mesaj_ayarla("Kayıt bulunamadı.", "danger");
    header("Location: panel.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telefon      = trim($_POST['telefon']      ?? '');
    $email        = trim($_POST['email']        ?? '');
    $mevcut_sifre = $_POST['mevcut_sifre']      ?? '';
    $yeni_sifre   = $_POST['yeni_sifre']        ?? '';

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL))
        $hatalar[] = "Geçerli bir e-posta giriniz.";

    if (!empty($yeni_sifre)) {
        if (!password_verify($mevcut_sifre, $hesap['sifre']))
            $hatalar[] = "Mevcut şifreniz hatalı.";
        elseif (strlen($yeni_sifre) < 6)
            $hatalar[] = "Yeni şifre en az 6 karakter olmalıdır.";
    }

    if (empty($hatalar)) {
        $ck = $db->prepare("SELECT id FROM yoneticiler WHERE email = ? AND id != ?");
        $ck->execute([$email, $yonetici_id]);
        if ($ck->fetch()) $hatalar[] = "Bu e-posta başka bir hesaba ait.";
    }

    if (empty($hatalar)) {
        // personel tablosunu güncelle
        $db->prepare("UPDATE personel SET telefon=?, email=? WHERE id=?")
           ->execute([$telefon ?: null, $email, $personel_id]);

        // yoneticiler tablosunu güncelle
        if (!empty($yeni_sifre)) {
            $db->prepare("UPDATE yoneticiler SET email=?, sifre=? WHERE id=?")
               ->execute([$email, password_hash($yeni_sifre, PASSWORD_DEFAULT), $yonetici_id]);
        } else {
            $db->prepare("UPDATE yoneticiler SET email=? WHERE id=?")
               ->execute([$email, $yonetici_id]);
        }

        mesaj_ayarla("Bilgileriniz güncellendi.", "success");
        header("Location: profil.php");
        exit;
    }
}

$sayfa_basligi = "Profilim";
require_once '../includes/header.php';
?>

<div class="row justify-content-center">
<div class="col-lg-8">

    <!-- Salt okunur bilgiler -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-person-badge-fill"></i> Personel Bilgilerim</h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted small">Ad Soyad</label>
                    <p class="fw-bold mb-0"><?= htmlspecialchars($personel['ad'] . ' ' . $personel['soyad']) ?></p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Sicil No</label>
                    <p class="fw-bold mb-0"><?= htmlspecialchars($personel['sicil_no']) ?></p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">TC Kimlik No</label>
                    <p class="fw-bold mb-0"><?= $personel['tc_no'] ? htmlspecialchars($personel['tc_no']) : '<span class="text-muted fst-italic">Henüz eklenmedi</span>' ?></p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Görevi</label>
                    <p class="fw-bold mb-0"><?= $personel['gorevi'] ? htmlspecialchars($personel['gorevi']) : '<span class="text-muted fst-italic">-</span>' ?></p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Departman</label>
                    <p class="fw-bold mb-0"><?= $personel['departman'] ? htmlspecialchars($personel['departman']) : '<span class="text-muted fst-italic">-</span>' ?></p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">İşe Giriş</label>
                    <p class="fw-bold mb-0"><?= tarih_format($personel['ise_giris']) ?></p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Baz Maaş</label>
                    <p class="fw-bold mb-0"><?= para_format($personel['maas']) ?></p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Durum</label>
                    <p class="mb-0"><?= durum_badge($personel['durum']) ?></p>
                </div>
            </div>
            <div class="alert alert-info mt-3 mb-0 py-2 small">
                <i class="bi bi-info-circle"></i> TC kimlik no, görevi, departman ve maaş bilgileri yönetici tarafından düzenlenir.
            </div>
        </div>
    </div>

    <!-- Düzenlenebilir bilgiler -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-pencil-square"></i> İletişim Bilgilerimi Düzenle</h5>
        </div>
        <div class="card-body p-4">

            <?php foreach ($hatalar as $h): ?>
                <div class="alert alert-danger p-2 small">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($h) ?>
                </div>
            <?php endforeach; ?>

            <form method="POST">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">E-posta <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($_POST['email'] ?? $personel['email'] ?? '') ?>" required>
                        <div class="form-text">Giriş yaparken kullandığınız e-posta.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Telefon</label>
                        <input type="text" name="telefon" class="form-control"
                               placeholder="05XX XXX XX XX"
                               value="<?= htmlspecialchars($_POST['telefon'] ?? $personel['telefon'] ?? '') ?>">
                    </div>
                </div>

                <hr class="my-4">
                <p class="text-muted small mb-3"><i class="bi bi-lock"></i> Şifre değiştirmek istemiyorsanız aşağıdaki alanları boş bırakın.</p>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mevcut Şifre</label>
                        <input type="password" name="mevcut_sifre" class="form-control" placeholder="Mevcut şifreniz">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Yeni Şifre</label>
                        <input type="password" name="yeni_sifre" class="form-control" placeholder="En az 6 karakter">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="panel.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Panele Dön</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Kaydet</button>
                </div>
            </form>
        </div>
    </div>

</div>
</div>

<?php require_once '../includes/footer.php'; ?>
