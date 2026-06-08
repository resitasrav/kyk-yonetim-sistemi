<?php
session_start();
$_SESSION['modul'] = 'ogrenci';
require_once '../config/db.php';
require_once '../includes/functions.php';
rol_kontrol('yonetici');

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: liste.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM etkinlikler WHERE id = ?");
$stmt->execute([$id]);
$etkinlik = $stmt->fetch();

if (!$etkinlik) {
    mesaj_ayarla('Etkinlik bulunamadı.', 'danger');
    header('Location: liste.php');
    exit;
}

$sayfa_basligi = 'Etkinlik Düzenle';
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
            UPDATE etkinlikler
            SET baslik=?, aciklama=?, etkinlik_tarihi=?, kapasite=?, konum=?, durum=?
            WHERE id=?
        ");
        $stmt->execute([$baslik, $aciklama, $etkinlik_tarihi, $kapasite, $konum, $durum, $id]);
        mesaj_ayarla('Etkinlik güncellendi.', 'success');
        header('Location: liste.php');
        exit;
    }

    // POST hatasından dönüşte form değerlerini kullan
    $etkinlik = array_merge($etkinlik, $_POST);
}

include '../includes/header.php';
?>

<div class="card shadow-sm border-0 mb-5" style="max-width: 700px;">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-warning"><i class="bi bi-pencil-square"></i> Etkinlik Düzenle</h5>
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
                           value="<?= htmlspecialchars($etkinlik['baslik']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tarih <span class="text-danger">*</span></label>
                    <input type="date" name="etkinlik_tarihi" class="form-control"
                           value="<?= htmlspecialchars($etkinlik['etkinlik_tarihi']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kapasite <span class="text-danger">*</span></label>
                    <input type="number" name="kapasite" class="form-control" min="1"
                           value="<?= (int)$etkinlik['kapasite'] ?>" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Konum</label>
                    <input type="text" name="konum" class="form-control"
                           value="<?= htmlspecialchars($etkinlik['konum'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Durum</label>
                    <select name="durum" class="form-select">
                        <option value="aktif"      <?= ($etkinlik['durum'] === 'aktif')      ? 'selected':'' ?>>Aktif</option>
                        <option value="iptal"      <?= ($etkinlik['durum'] === 'iptal')      ? 'selected':'' ?>>İptal</option>
                        <option value="tamamlandi" <?= ($etkinlik['durum'] === 'tamamlandi') ? 'selected':'' ?>>Tamamlandı</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Açıklama</label>
                    <textarea name="aciklama" class="form-control" rows="3"><?= htmlspecialchars($etkinlik['aciklama'] ?? '') ?></textarea>
                </div>
            </div>

            <hr class="my-4">
            <div class="d-flex justify-content-end gap-2">
                <a href="liste.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> İptal</a>
                <button type="submit" class="btn btn-warning text-white"><i class="bi bi-save"></i> Güncelle</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
