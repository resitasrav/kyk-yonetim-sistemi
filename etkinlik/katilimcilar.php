<?php
session_start();
$_SESSION['modul'] = 'ogrenci';
require_once '../config/db.php';
require_once '../includes/functions.php';
rol_kontrol('yonetici');

$etkinlik_id = (int)($_GET['id'] ?? 0);
if (!$etkinlik_id) {
    header('Location: liste.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM etkinlikler WHERE id = ?");
$stmt->execute([$etkinlik_id]);
$etkinlik = $stmt->fetch();

if (!$etkinlik) {
    mesaj_ayarla('Etkinlik bulunamadı.', 'danger');
    header('Location: liste.php');
    exit;
}

// Katılımcı listesi
$stmt2 = $db->prepare("
    SELECT ek.*, o.ad, o.soyad, o.okul_no, o.bolum, o.telefon
    FROM etkinlik_katilim ek
    JOIN ogrenciler o ON o.id = ek.ogrenci_id
    WHERE ek.etkinlik_id = ?
    ORDER BY ek.katilim_tarihi ASC
");
$stmt2->execute([$etkinlik_id]);
$katilimcilar = $stmt2->fetchAll();

$sayfa_basligi = 'Katılımcı Listesi';
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-people text-primary"></i> Katılımcı Listesi</h4>
        <p class="text-muted mb-0">
            <strong><?= htmlspecialchars($etkinlik['baslik']) ?></strong> —
            <?= tarih_format($etkinlik['etkinlik_tarihi']) ?>
            <?php if ($etkinlik['konum']): ?>· <?= htmlspecialchars($etkinlik['konum']) ?><?php endif; ?>
        </p>
    </div>
    <a href="liste.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Geri Dön</a>
</div>

<!-- Özet bilgi -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 bg-primary text-white">
            <div class="fs-2 fw-bold"><?= $etkinlik['kapasite'] ?></div>
            <div class="small">Toplam Kapasite</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 bg-success text-white">
            <div class="fs-2 fw-bold"><?= count($katilimcilar) ?></div>
            <div class="small">Kayıtlı Kişi</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 bg-warning text-dark">
            <div class="fs-2 fw-bold"><?= max(0, $etkinlik['kapasite'] - count($katilimcilar)) ?></div>
            <div class="small">Kalan Kontenjan</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 <?= $etkinlik['durum']==='aktif' ? 'bg-success' : 'bg-secondary' ?> text-white">
            <div class="fs-5 fw-bold mt-2"><?= durum_badge($etkinlik['durum']) ?></div>
            <div class="small mt-1">Etkinlik Durumu</div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-list-check"></i> Katılımcılar (<?= count($katilimcilar) ?>)</h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($katilimcilar)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-person-x" style="font-size:2.5rem;"></i>
            <p class="mt-2">Bu etkinliğe henüz kimse kayıt olmadı.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Ad Soyad</th>
                        <th>Okul No</th>
                        <th>Bölüm</th>
                        <th>Telefon</th>
                        <th>Kayıt Tarihi</th>
                        <th>Durum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($katilimcilar as $i => $k): ?>
                    <tr>
                        <td class="text-muted"><?= $i + 1 ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($k['ad'] . ' ' . $k['soyad']) ?></td>
                        <td><?= htmlspecialchars($k['okul_no']) ?></td>
                        <td><?= htmlspecialchars($k['bolum']) ?></td>
                        <td><?= htmlspecialchars($k['telefon'] ?? '-') ?></td>
                        <td><?= date('d.m.Y H:i', strtotime($k['katilim_tarihi'])) ?></td>
                        <td><?= durum_badge($k['durum']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
