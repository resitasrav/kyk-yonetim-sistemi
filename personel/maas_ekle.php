<?php
session_start();
$_SESSION['modul'] = 'personel';
require_once '../config/db.php';
require_once '../includes/functions.php';
rol_kontrol('yonetici');

$sayfa_basligi = 'Maaş Ödemesi Ekle';
$hatalar = [];

// Sadece aktif veya izinli (pasif olmayan) personelleri getir
$personeller   = $db->query("SELECT id, ad, soyad, sicil_no, maas FROM personel WHERE durum != 'pasif' ORDER BY ad")->fetchAll();
$secili_personel = (int)($_GET['personel_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $personel_id  = (int)($_POST['personel_id'] ?? 0);
    $odeme_ayi    = post('odeme_ayi');
    $net_maas     = (float)($_POST['net_maas'] ?? 0);
    $prim         = (float)($_POST['prim'] ?? 0);
    $kesinti      = (float)($_POST['kesinti'] ?? 0);
    $odeme_tarihi = post('odeme_tarihi') ?: null;
    $aciklama     = post('aciklama');
    $durum        = post('durum') ?: 'bekliyor';
    
    // Toplam ödenecek tutar
    $toplam       = $net_maas + $prim - $kesinti;

    if (!$personel_id) $hatalar[] = 'Personel seçiniz.';
    if (!$odeme_ayi)   $hatalar[] = 'Ödeme ayı zorunludur.';
    if ($net_maas <= 0) $hatalar[] = 'Net maaş 0\'dan büyük olmalıdır.';

    if (empty($hatalar)) {
        // Aynı ay için zaten kayıt var mı? (YYYY-MM-01 formatı ile kontrol)
        $kontrol = $db->prepare("SELECT id FROM maas_odemeleri WHERE personel_id=? AND odeme_ayi=?");
        $kontrol->execute([$personel_id, $odeme_ayi . '-01']);
        if ($kontrol->fetch()) {
            $hatalar[] = 'Bu personel için seçilen aya ait ödeme kaydı zaten mevcut.';
        }
    }

    if (empty($hatalar)) {
        $stmt = $db->prepare("
            INSERT INTO maas_odemeleri
                (personel_id, odeme_ayi, net_maas, prim, kesinti, toplam, odeme_tarihi, aciklama, durum)
            VALUES (?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([$personel_id, $odeme_ayi . '-01', $net_maas, $prim, $kesinti, $toplam, $odeme_tarihi ?: null, $aciklama, $durum]);
        
        mesaj_ayarla('Maaş ödemesi başarıyla eklendi.', 'success');
        header('Location: maas_liste.php?personel_id=' . $personel_id);
        exit;
    }
}

include '../includes/header.php';
?>

<div class="card shadow-sm border-0 mb-5">
    <div class="card-header bg-white py-3 border-bottom">
        <h5 class="mb-0 fw-bold text-success"><i class="bi bi-cash-stack"></i> Yeni Maaş Ödemesi Oluştur</h5>
    </div>
    <div class="card-body p-4">
        
        <?php foreach ($hatalar as $h): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($h) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>

        <form method="POST" id="maasForm">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Personel <span class="text-danger">*</span></label>
                    <select name="personel_id" id="personelSelect" class="form-select" required onchange="maasOtoDoldur(this)">
                        <option value="">— Seçiniz —</option>
                        <?php foreach ($personeller as $p): ?>
                        <option value="<?= $p['id'] ?>" data-maas="<?= $p['maas'] ?>"
                            <?= (($_POST['personel_id'] ?? $secili_personel) == $p['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['ad'] . ' ' . $p['soyad'] . ' (' . $p['sicil_no'] . ')') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Ödeme Ayı <span class="text-danger">*</span></label>
                    <input type="month" name="odeme_ayi" class="form-control" value="<?= $_POST['odeme_ayi'] ?? date('Y-m') ?>" required>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-bold text-secondary">Net Maaş (₺) <span class="text-danger">*</span></label>
                    <input type="number" name="net_maas" id="netMaas" class="form-control fw-bold" step="0.01" min="0" value="<?= $_POST['net_maas'] ?? '0' ?>" oninput="hesapla()">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-success">Prim (₺) ➕</label>
                    <input type="number" name="prim" id="prim" class="form-control" step="0.01" min="0" value="<?= $_POST['prim'] ?? '0' ?>" oninput="hesapla()">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-danger">Kesinti (₺) ➖</label>
                    <input type="number" name="kesinti" id="kesinti" class="form-control" step="0.01" min="0" value="<?= $_POST['kesinti'] ?? '0' ?>" oninput="hesapla()">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-primary">Toplam Ödenecek (₺)</label>
                    <input type="text" id="toplamGoster" class="form-control bg-light text-primary fw-bold" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Ödeme Tarihi</label>
                    <input type="date" name="odeme_tarihi" class="form-control" value="<?= $_POST['odeme_tarihi'] ?? '' ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Durum</label>
                    <select name="durum" class="form-select">
                        <option value="bekliyor" <?= (($_POST['durum'] ?? 'bekliyor') === 'bekliyor') ? 'selected':'' ?>>Bekliyor</option>
                        <option value="odendi"   <?= (($_POST['durum'] ?? '') === 'odendi') ? 'selected':'' ?>>Ödendi</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Açıklama (Örn: Yol yardımı eklendi)</label>
                    <textarea name="aciklama" class="form-control" rows="2"><?= htmlspecialchars($_POST['aciklama'] ?? '') ?></textarea>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="d-flex justify-content-end gap-2">
                <a href="maas_liste.php<?= $secili_personel ? '?personel_id='.$secili_personel : '' ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> İptal</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Kaydet</button>
            </div>
        </form>
    </div>
</div>

<script>
function hesapla() {
    const net     = parseFloat(document.getElementById('netMaas').value)  || 0;
    const prim    = parseFloat(document.getElementById('prim').value)     || 0;
    const kesinti = parseFloat(document.getElementById('kesinti').value)  || 0;
    const toplam  = net + prim - kesinti;
    
    // Toplamı para birimi formatında yazdır
    document.getElementById('toplamGoster').value = toplam.toLocaleString('tr-TR', {minimumFractionDigits:2}) + ' ₺';
}

function maasOtoDoldur(sel) {
    // Seçilen personelin "data-maas" özelliğini al
    const maas = sel.options[sel.selectedIndex].dataset.maas || 0;
    document.getElementById('netMaas').value = parseFloat(maas).toFixed(2);
    hesapla();
}

// Sayfa ilk yüklendiğinde hesaplamayı çalıştır
hesapla();

// Sayfa yüklendiğinde URL'den personel seçilmişse maaşını otomatik doldur
window.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('personelSelect');
    if (sel.value) maasOtoDoldur(sel);
});
</script>

<?php include '../includes/footer.php'; ?>