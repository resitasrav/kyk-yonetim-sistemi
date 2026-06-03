<?php
session_start();
$_SESSION['modul'] = 'personel';
require_once '../config/db.php';
require_once '../includes/functions.php';
rol_kontrol('yonetici');

$sayfa_basligi = 'Yeni Personel Ekle';
$hatalar = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad        = post('ad');
    $soyad     = post('soyad');
    $tc_no     = post('tc_no');
    $sicil_no  = post('sicil_no');
    $gorevi    = post('gorevi');
    $departman = post('departman');
    $telefon   = post('telefon');
    $email     = post('email');
    $ise_giris = post('ise_giris');
    $maas      = (float)($_POST['maas'] ?? 0);
    $durum     = post('durum') ?: 'aktif';

    if (!$ad)       $hatalar[] = 'Ad zorunludur.';
    if (!$soyad)    $hatalar[] = 'Soyad zorunludur.';
    if (!tc_gecerli($tc_no)) $hatalar[] = 'Geçerli bir TC Kimlik No giriniz (11 rakam).';
    if (!$sicil_no) $hatalar[] = 'Sicil numarası zorunludur.';
    if (!$gorevi)   $hatalar[] = 'Görev zorunludur.';
    if (!$departman) $hatalar[] = 'Departman zorunludur.';
    if (!$ise_giris) $hatalar[] = 'İşe giriş tarihi zorunludur.';

    if (empty($hatalar)) {
        // $pdo yerine $db kullanıldı
        $kontrol = $db->prepare("SELECT id FROM personel WHERE tc_no=? OR sicil_no=?");
        $kontrol->execute([$tc_no, $sicil_no]);
        if ($kontrol->fetch()) {
            $hatalar[] = 'Bu TC No veya Sicil Numarası zaten kayıtlı.';
        }
    }

    if (empty($hatalar)) {
        $stmt = $db->prepare("
            INSERT INTO personel (ad, soyad, tc_no, sicil_no, gorevi, departman, telefon, email, ise_giris, maas, durum)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([$ad, $soyad, $tc_no, $sicil_no, $gorevi, $departman, $telefon, $email, $ise_giris, $maas, $durum]);
        
        mesaj_ayarla('Personel başarıyla eklendi.', 'success');
        header('Location: liste.php');
        exit;
    }
}

include '../includes/header.php';
?>

<div class="card shadow-sm border-0 mb-5">
    <div class="card-header bg-white py-3 border-bottom">
        <h5 class="mb-0 fw-bold text-success"><i class="bi bi-person-plus"></i> Yeni Personel Ekle</h5>
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
                    <input type="text" name="tc_no" class="form-control" maxlength="11" value="<?= htmlspecialchars($_POST['tc_no'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Sicil Numarası <span class="text-danger">*</span></label>
                    <input type="text" name="sicil_no" class="form-control" value="<?= htmlspecialchars($_POST['sicil_no'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Görev <span class="text-danger">*</span></label>
                    <input type="text" name="gorevi" class="form-control" value="<?= htmlspecialchars($_POST['gorevi'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Departman <span class="text-danger">*</span></label>
                    <input type="text" name="departman" class="form-control" value="<?= htmlspecialchars($_POST['departman'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Telefon</label>
                    <input type="text" name="telefon" class="form-control" value="<?= htmlspecialchars($_POST['telefon'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">E-posta</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">İşe Giriş Tarihi <span class="text-danger">*</span></label>
                    <input type="date" name="ise_giris" class="form-control" value="<?= $_POST['ise_giris'] ?? '' ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Maaş (₺)</label>
                    <input type="number" name="maas" class="form-control" step="0.01" min="0" value="<?= $_POST['maas'] ?? '0' ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Durum</label>
                    <select name="durum" class="form-select">
                        <option value="aktif"   <?= (($_POST['durum'] ?? 'aktif') === 'aktif')  ? 'selected':'' ?>>Aktif</option>
                        <option value="izinli"  <?= (($_POST['durum'] ?? '') === 'izinli') ? 'selected':'' ?>>İzinli</option>
                        <option value="pasif"   <?= (($_POST['durum'] ?? '') === 'pasif')  ? 'selected':'' ?>>Pasif</option>
                    </select>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="d-flex justify-content-end gap-2">
                <a href="liste.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> İptal</a>
                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Kaydet</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>