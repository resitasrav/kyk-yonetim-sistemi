<?php
session_start();
$_SESSION['modul'] = 'personel';
require_once '../config/db.php';
require_once '../includes/functions.php';
rol_kontrol('yonetici');

$sayfa_basligi = 'Personel Düzenle';
$id = (int)($_GET['id'] ?? 0);

// $pdo yerine $db kullanıyoruz
$stmt = $db->prepare("SELECT * FROM personel WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    mesaj_ayarla('Personel bulunamadı.', 'danger');
    header('Location: liste.php');
    exit;
}

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

    if (!$ad)    $hatalar[] = 'Ad zorunludur.';
    if (!$soyad) $hatalar[] = 'Soyad zorunludur.';
    if (!tc_gecerli($tc_no)) $hatalar[] = 'Geçerli bir TC Kimlik No giriniz.';

    if (empty($hatalar)) {
        // Başka kayıtta aynı TC/Sicil no var mı? (Kendi ID'sini hariç tutarak arar)
        $kontrol = $db->prepare("SELECT id FROM personel WHERE (tc_no=? OR sicil_no=?) AND id != ?");
        $kontrol->execute([$tc_no, $sicil_no, $id]);
        if ($kontrol->fetch()) {
            $hatalar[] = 'Bu TC No veya Sicil No başka personelde kayıtlı.';
        }
    }

    if (empty($hatalar)) {
        $stmt = $db->prepare("
            UPDATE personel SET
                ad=?, soyad=?, tc_no=?, sicil_no=?, gorevi=?, departman=?,
                telefon=?, email=?, ise_giris=?, maas=?, durum=?
            WHERE id=?
        ");
        $stmt->execute([$ad, $soyad, $tc_no, $sicil_no, $gorevi, $departman,
                        $telefon, $email, $ise_giris, $maas, $durum, $id]);
                        
        mesaj_ayarla('Personel bilgileri güncellendi.', 'success');
        header('Location: liste.php');
        exit;
    }

    $p = array_merge($p, $_POST);
}

include '../includes/header.php';
?>

<div class="card shadow-sm border-0 mb-5">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-success">
            <i class="bi bi-pencil-square"></i> Personel Düzenle — <?= htmlspecialchars($p['ad'] . ' ' . $p['soyad']) ?>
        </h5>
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
                    <input type="text" name="ad" class="form-control" value="<?= htmlspecialchars($p['ad']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Soyad <span class="text-danger">*</span></label>
                    <input type="text" name="soyad" class="form-control" value="<?= htmlspecialchars($p['soyad']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">TC Kimlik No <span class="text-danger">*</span></label>
                    <input type="text" name="tc_no" class="form-control" maxlength="11" value="<?= htmlspecialchars($p['tc_no']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Sicil Numarası <span class="text-danger">*</span></label>
                    <input type="text" name="sicil_no" class="form-control" value="<?= htmlspecialchars($p['sicil_no']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Görev <span class="text-danger">*</span></label>
                    <input type="text" name="gorevi" class="form-control" value="<?= htmlspecialchars($p['gorevi']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Departman <span class="text-danger">*</span></label>
                    <input type="text" name="departman" class="form-control" value="<?= htmlspecialchars($p['departman']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Telefon</label>
                    <input type="text" name="telefon" class="form-control" value="<?= htmlspecialchars($p['telefon'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">E-posta</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($p['email'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">İşe Giriş Tarihi <span class="text-danger">*</span></label>
                    <input type="date" name="ise_giris" class="form-control" value="<?= $p['ise_giris'] ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Maaş (₺)</label>
                    <input type="number" name="maas" class="form-control" step="0.01" min="0" value="<?= $p['maas'] ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Durum</label>
                    <select name="durum" class="form-select">
                        <option value="aktif"  <?= $p['durum']==='aktif'  ? 'selected':'' ?>>Aktif</option>
                        <option value="izinli" <?= $p['durum']==='izinli' ? 'selected':'' ?>>İzinli</option>
                        <option value="pasif"  <?= $p['durum']==='pasif'  ? 'selected':'' ?>>Pasif</option>
                    </select>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="d-flex justify-content-between flex-wrap gap-3">
                <div class="d-flex gap-2">
                    <a href="izin_liste.php?personel_id=<?= $id ?>" class="btn btn-info text-white"><i class="bi bi-calendar2-week"></i> İzinleri Görüntüle</a>
                    <a href="maas_liste.php?personel_id=<?= $id ?>" class="btn btn-success"><i class="bi bi-cash-coin"></i> Maaş Geçmişi</a>
                </div>
                <div class="d-flex gap-2">
                    <a href="liste.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> İptal</a>
                    <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Güncelle</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>