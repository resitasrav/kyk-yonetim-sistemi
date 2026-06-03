<?php
session_start();
$_SESSION['modul'] = 'ogrenci';
require_once '../config/db.php';
require_once '../includes/functions.php';
rol_kontrol('yonetici');

$sayfa_basligi = 'Etkinlik Ekle';
$hatalar = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $baslik          = post('baslik');
    $aciklama        = post('aciklama');
    $etkinlik_tarihi = post('etkinlik_tarihi');
    $kapasite        = (int)($_POST['kapasite'] ?? 0);
    $konum           = post('konum');
    $durum           = in_array($_POST['durum'] ?? '', ['aktif','iptal','tamamlandi'])
                       ? $_POST['durum'] : 'aktif';

    if (!$baslik)          $hatalar[] = 'Etkinlik adı zorunludur.';
    if (!$etkinlik_tarihi) $hatalar[] = 'Etkinlik tarihi zorunludur.';
    if ($kapasite < 1)     $hatalar[] = 'Kapasite en az 1 olmalıdır.';

    if (empty($hatalar)) {
        $stmt = $db->prepare("
            INSERT INTO etkinlikler (baslik, aciklama, etkinlik_tarihi, kapasite, konum, durum)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$baslik, $aciklama, $etkinlik_tarihi, $kapasite, $konum, $durum]);
        mesaj_ayarla('Etkinlik başarıyla oluşturuldu.', 'success');
        header('Location: liste.php');
        exit;
    }
}

include '../includes/header.php';
?>

<div class="card shadow-sm border-0 mb-5" style="max-width: 700px;">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-calendar-plus"></i> Yeni Etkinlik Ekle</h5>
    </div>
    <div class="card-body p-4">

        <?php foreach ($hatalar as $h): ?>
            <div class="alert alert-danger p-2 small"><i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($h) ?></div>
        <?php endforeach; ?>

        <form method="POST">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Etkinlik Adı <span class="text-danger">*</span></label>
                    <input type="text" name="baslik" class="form-control"
                           value="<?= htmlspecialchars($_POST['baslik'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tarih <span class="text-danger">*</span></label>
                    <input type="date" name="etkinlik_tarihi" class="form-control"
                           value="<?= htmlspecialchars($_POST['etkinlik_tarihi'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kapasite <span class="text-danger">*</span></label>
                    <input type="number" name="kapasite" class="form-control" min="1"
                           value="<?= (int)($_POST['kapasite'] ?? 50) ?>" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Konum</label>
                    <input type="text" name="konum" class="form-control"
                           placeholder="Örn: Konferans Salonu"
                           value="<?= htmlspecialchars($_POST['konum'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Durum</label>
                    <select name="durum" class="form-select">
                        <option value="aktif"      <?= (($_POST['durum'] ?? 'aktif') === 'aktif')      ? 'selected':'' ?>>Aktif</option>
                        <option value="iptal"      <?= (($_POST['durum'] ?? '') === 'iptal')           ? 'selected':'' ?>>İptal</option>
                        <option value="tamamlandi" <?= (($_POST['durum'] ?? '') === 'tamamlandi')      ? 'selected':'' ?>>Tamamlandı</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Açıklama</label>
                    <textarea name="aciklama" class="form-control" rows="3"
                              placeholder="Etkinlik hakkında bilgi..."><?= htmlspecialchars($_POST['aciklama'] ?? '') ?></textarea>
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
