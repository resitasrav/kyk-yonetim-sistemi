<?php
session_start();
$_SESSION['modul'] = 'ogrenci';
require_once '../config/db.php';
require_once '../includes/functions.php';

// Hem yönetici hem öğrenci erişebilir
if (!isset($_SESSION['yonetici_id'])) {
    header('Location: ../login.php');
    exit;
}
$rol   = $_SESSION['rol'] ?? 'yonetici';
$kendi = ($rol === 'ogrenci');

if ($kendi && !$_SESSION['ilgili_id']) {
    mesaj_ayarla("Öğrenci kaydınız bulunamadı.", "danger");
    header('Location: panel.php');
    exit;
}

$sayfa_basligi = 'İzin Talebi Oluştur';
$hatalar = [];

// Öğrenci rolü: sadece kendi id'si; yönetici: dropdown listesi
if ($kendi) {
    $kendi_ogrenci_id = (int)$_SESSION['ilgili_id'];
} else {
    $ogrenciler    = $db->query("SELECT id, ad, soyad, okul_no FROM ogrenciler WHERE durum='aktif' ORDER BY ad")->fetchAll();
    $secili_ogrenci = (int)($_GET['ogrenci_id'] ?? 0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_dogrula();

    $ogrenci_id       = $kendi ? $kendi_ogrenci_id : (int)($_POST['ogrenci_id'] ?? 0);
    $izin_turu        = post('izin_turu');
    $baslangic_tarihi = post('baslangic_tarihi');
    $bitis_tarihi     = post('bitis_tarihi');
    $aciklama         = post('aciklama');

    $gecerli_turler = ['evci', 'gunubirlik', 'saglik', 'diger'];

    if (!$kendi && !$ogrenci_id)                $hatalar[] = 'Öğrenci seçiniz.';
    if (!in_array($izin_turu, $gecerli_turler)) $hatalar[] = 'Geçerli bir izin türü seçiniz.';
    if (!$baslangic_tarihi)                     $hatalar[] = 'Başlangıç tarihi zorunludur.';
    if (!$bitis_tarihi)                         $hatalar[] = 'Bitiş tarihi zorunludur.';
    if ($baslangic_tarihi && $bitis_tarihi && $bitis_tarihi < $baslangic_tarihi)
        $hatalar[] = 'Bitiş tarihi başlangıç tarihinden önce olamaz.';

    if (empty($hatalar)) {
        $gun_sayisi = takvim_gun_hesapla($baslangic_tarihi, $bitis_tarihi);

        $stmt = $db->prepare("
            INSERT INTO ogrenci_izinleri (ogrenci_id, izin_turu, baslangic_tarihi, bitis_tarihi, gun_sayisi, aciklama)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$ogrenci_id, $izin_turu, $baslangic_tarihi, $bitis_tarihi, $gun_sayisi, $aciklama]);

        mesaj_ayarla("İzin talebiniz oluşturuldu. ($gun_sayisi gün) Yönetici onayı bekleniyor.", 'success');

        if ($kendi) {
            header('Location: panel.php');
        } else {
            header('Location: izin_liste.php?ogrenci_id=' . $ogrenci_id);
        }
        exit;
    }
}

include '../includes/header.php';
?>

<div class="card shadow-sm border-0 mb-5">
    <div class="card-header bg-white py-3 border-bottom">
        <h5 class="mb-0 fw-bold text-success">
            <i class="bi bi-calendar-plus"></i> Yeni İzin Talebi Oluştur
        </h5>
    </div>
    <div class="card-body p-4">

        <?php foreach ($hatalar as $h): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($h) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>

        <?php if ($kendi): ?>
        <div class="alert alert-info small">
            <i class="bi bi-info-circle"></i>
            Talebiniz oluşturulduktan sonra yönetici onayına gönderilecektir. Onay durumunu "İzinlerim" sayfasından takip edebilirsiniz.
        </div>
        <?php endif; ?>

        <form method="POST">
            <?= csrf_input() ?>
            <div class="row g-3">

                <?php if ($kendi): ?>
                <input type="hidden" name="ogrenci_id" value="<?= $kendi_ogrenci_id ?>">
                <?php else: ?>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Öğrenci <span class="text-danger">*</span></label>
                    <select name="ogrenci_id" class="form-select" required>
                        <option value="">— Seçiniz —</option>
                        <?php foreach ($ogrenciler as $o): ?>
                        <option value="<?= $o['id'] ?>"
                            <?= (($_POST['ogrenci_id'] ?? $secili_ogrenci) == $o['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($o['ad'] . ' ' . $o['soyad'] . ' (' . $o['okul_no'] . ')') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="<?= $kendi ? 'col-md-12' : 'col-md-6' ?>">
                    <label class="form-label fw-bold">İzin Türü <span class="text-danger">*</span></label>
                    <select name="izin_turu" class="form-select" required>
                        <option value="">— Seçiniz —</option>
                        <option value="evci"       <?= (($_POST['izin_turu'] ?? '') === 'evci')       ? 'selected':'' ?>>Evci İzni (hafta sonu / ailenin yanı)</option>
                        <option value="gunubirlik" <?= (($_POST['izin_turu'] ?? '') === 'gunubirlik') ? 'selected':'' ?>>Günübirlik İzin</option>
                        <option value="saglik"     <?= (($_POST['izin_turu'] ?? '') === 'saglik')     ? 'selected':'' ?>>Sağlık İzni</option>
                        <option value="diger"      <?= (($_POST['izin_turu'] ?? '') === 'diger')      ? 'selected':'' ?>>Diğer</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Başlangıç Tarihi <span class="text-danger">*</span></label>
                    <input type="date" name="baslangic_tarihi" class="form-control"
                           value="<?= htmlspecialchars($_POST['baslangic_tarihi'] ?? '') ?>"
                           min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Bitiş Tarihi <span class="text-danger">*</span></label>
                    <input type="date" name="bitis_tarihi" class="form-control"
                           value="<?= htmlspecialchars($_POST['bitis_tarihi'] ?? '') ?>"
                           min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Açıklama</label>
                    <textarea name="aciklama" class="form-control" rows="3"
                              placeholder="İzin talebinizle ilgili açıklama ekleyebilirsiniz..."><?= htmlspecialchars($_POST['aciklama'] ?? '') ?></textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <?php if ($kendi): ?>
                    <a href="panel.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> İptal</a>
                <?php else: ?>
                    <a href="izin_liste.php<?= isset($secili_ogrenci) && $secili_ogrenci ? '?ogrenci_id='.$secili_ogrenci : '' ?>"
                       class="btn btn-secondary"><i class="bi bi-arrow-left"></i> İptal</a>
                <?php endif; ?>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-send"></i> <?= $kendi ? 'Talep Gönder' : 'Kaydet' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
