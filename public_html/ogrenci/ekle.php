<?php
session_start();
$_SESSION['modul'] = 'ogrenci';
require_once '../config/db.php';
require_once '../includes/functions.php';
rol_kontrol('yonetici');

$sayfa_basligi = 'Yeni Öğrenci Ekle';
$hatalar = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad      = post('ad');
    $soyad   = post('soyad');
    $tc_no   = post('tc_no');
    $okul_no = post('okul_no');
    $bolum   = post('bolum');
    $sinif   = (int)($_POST['sinif'] ?? 1);
    $telefon = post('telefon');
    $email   = post('email');
    $adres   = post('adres');
    $oda_no  = post('oda_no');
    $durum   = post('durum') ?: 'aktif';
    $kayit_tarihi = post('kayit_tarihi') ?: date('Y-m-d');

    // Doğrulama 
    if (!$ad)   $hatalar[] = 'Ad alanı zorunludur.';
    if (!$soyad) $hatalar[] = 'Soyad alanı zorunludur.';
    if (!tc_gecerli($tc_no)) $hatalar[] = 'Geçerli bir TC Kimlik No giriniz (11 rakam).';
    if (!$okul_no) $hatalar[] = 'Okul numarası zorunludur.';
    if (!$bolum)   $hatalar[] = 'Bölüm zorunludur.';

    if (empty($hatalar)) {
        // TC / okul no benzersizlik kontrolü $db den sorgulayarak referans alıyoruz
        $kontrol = $db->prepare("SELECT id FROM ogrenciler WHERE tc_no=? OR okul_no=?");
        $kontrol->execute([$tc_no, $okul_no]);
        if ($kontrol->fetch()) {
            $hatalar[] = 'Bu TC No veya Okul Numarası zaten kayıtlı.';
        }
    }

    if (empty($hatalar)) {
        $stmt = $db->prepare("
            INSERT INTO ogrenciler
                (ad, soyad, tc_no, okul_no, bolum, sinif, telefon, email, adres, oda_no, kayit_tarihi, durum)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([$ad, $soyad, $tc_no, $okul_no, $bolum, $sinif, $telefon, $email, $adres, $oda_no, $kayit_tarihi, $durum]);
        
        // Mesaj türünü Bootstrap uyumlu 'success' yaptık
        mesaj_ayarla('Öğrenci başarıyla eklendi.', 'success');
        header('Location: liste.php');
        exit;
    }
}

include '../includes/header.php';
?>

<div class="card shadow-sm border-0 mb-5">
    <div class="card-header bg-white py-3 border-bottom">
        <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-person-plus"></i> Yeni Öğrenci Ekle</h5>
    </div>
    <div class="card-body p-4">
        
        <?php foreach ($hatalar as $h): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($h) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>

        <form method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Ad <span class="text-danger">*</span></label>
                    <input type="text" name="ad" class="form-control" value="<?= htmlspecialchars($_POST['ad'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Soyad <span class="text-danger">*</span></label>
                    <input type="text" name="soyad" class="form-control" value="<?= htmlspecialchars($_POST['soyad'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">TC Kimlik No <span class="text-danger">*</span></label>
                    <input type="text" name="tc_no" class="form-control" maxlength="11" placeholder="11 Haneli TC No" value="<?= htmlspecialchars($_POST['tc_no'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Okul Numarası <span class="text-danger">*</span></label>
                    <input type="text" name="okul_no" class="form-control" value="<?= htmlspecialchars($_POST['okul_no'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Bölüm <span class="text-danger">*</span></label>
                    <input type="text" name="bolum" class="form-control" value="<?= htmlspecialchars($_POST['bolum'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Sınıf</label>
                    <select name="sinif" class="form-select">
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                        <option value="<?= $i ?>" <?= (($_POST['sinif'] ?? 1) == $i) ? 'selected' : '' ?>><?= $i ?>. Sınıf</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Telefon</label>
                    <input type="text" name="telefon" class="form-control" placeholder="05XX XXX XX XX" value="<?= htmlspecialchars($_POST['telefon'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">E-posta</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Oda No</label>
                    <input type="text" name="oda_no" class="form-control" value="<?= htmlspecialchars($_POST['oda_no'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Kayıt Tarihi</label>
                    <input type="date" name="kayit_tarihi" class="form-control" value="<?= $_POST['kayit_tarihi'] ?? date('Y-m-d') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Durum</label>
                    <select name="durum" class="form-select">
                        <option value="aktif"  <?= (($_POST['durum'] ?? 'aktif') === 'aktif')  ? 'selected' : '' ?>>Aktif</option>
                        <option value="pasif"  <?= (($_POST['durum'] ?? '') === 'pasif')  ? 'selected' : '' ?>>Pasif</option>
                        <option value="mezun"  <?= (($_POST['durum'] ?? '') === 'mezun')  ? 'selected' : '' ?>>Mezun</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Adres</label>
                    <textarea name="adres" class="form-control" rows="3"><?= htmlspecialchars($_POST['adres'] ?? '') ?></textarea>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="d-flex justify-content-end gap-2">
                <a href="liste.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> İptal</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Kaydet</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>