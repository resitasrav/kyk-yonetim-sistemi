<?php
session_start();
$_SESSION['modul'] = 'ogrenci';
require_once '../config/db.php';
require_once '../includes/functions.php';

$rol   = $_SESSION['rol'] ?? 'yonetici';
$kendi = ($rol === 'ogrenci'); // Öğrenci rolü → sadece kendi kayıtları

if (!$kendi) {
    rol_kontrol('yonetici');
}

$sayfa_basligi = $kendi ? 'İzinlerim' : 'Öğrenci İzin Yönetimi';

// Öğrenci rolündeyse kendi id'si; yöneticiyse URL'den
$ogrenci_id   = $kendi ? (int)$_SESSION['ilgili_id'] : (int)($_GET['ogrenci_id'] ?? 0);
$durum_filtre = $_GET['durum'] ?? '';

$sql = "
    SELECT i.*, o.ad, o.soyad, o.okul_no, o.oda_no
    FROM ogrenci_izinleri i
    JOIN ogrenciler o ON o.id = i.ogrenci_id
    WHERE 1=1
";
$params = [];

if ($kendi || $ogrenci_id) {
    $sql .= " AND i.ogrenci_id = ?";
    $params[] = $ogrenci_id;
}
if ($durum_filtre) {
    $sql .= " AND i.durum = ?";
    $params[] = $durum_filtre;
}
$sql .= " ORDER BY i.olusturuldu DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$izinler = $stmt->fetchAll();

// Bekleyen izin sayısı
if ($kendi) {
    $bekleme_stmt = $db->prepare("SELECT COUNT(*) FROM ogrenci_izinleri WHERE ogrenci_id = ? AND durum = 'bekliyor'");
    $bekleme_stmt->execute([$ogrenci_id]);
    $bekleyen = $bekleme_stmt->fetchColumn();
} else {
    $bekleyen = $db->query("SELECT COUNT(*) FROM ogrenci_izinleri WHERE durum='bekliyor'")->fetchColumn();
}

$filtre_ogrenci = null;
if (!$kendi && $ogrenci_id) {
    $os = $db->prepare("SELECT ad, soyad FROM ogrenciler WHERE id=?");
    $os->execute([$ogrenci_id]);
    $filtre_ogrenci = $os->fetch();
}

include '../includes/header.php';
?>

<?php if ($bekleyen > 0): ?>
<div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
    <i class="bi bi-hourglass-split"></i>
    <?php if ($kendi): ?>
        <strong><?= $bekleyen ?></strong> adet izin talebiniz onay bekliyor.
    <?php else: ?>
        <strong><?= $bekleyen ?></strong> adet onay bekleyen öğrenci izin talebi var.
    <?php endif; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="card shadow-sm border-0 mb-5">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-success">
            <i class="bi bi-calendar-event"></i> <?= $sayfa_basligi ?>
            <?php if ($filtre_ogrenci): ?>
                <span class="text-secondary">— <?= htmlspecialchars($filtre_ogrenci['ad'] . ' ' . $filtre_ogrenci['soyad']) ?></span>
            <?php endif; ?>
        </h5>
    </div>

    <div class="card-body">
        <form method="GET" class="row g-2 mb-4 align-items-center">
            <?php if (!$kendi && $ogrenci_id): ?>
                <input type="hidden" name="ogrenci_id" value="<?= $ogrenci_id ?>">
            <?php endif; ?>

            <div class="col-md-3">
                <select name="durum" class="form-select">
                    <option value="">Tüm Durumlar</option>
                    <option value="bekliyor"   <?= $durum_filtre==='bekliyor'   ? 'selected':'' ?>>Bekliyor</option>
                    <option value="onaylandi"  <?= $durum_filtre==='onaylandi'  ? 'selected':'' ?>>Onaylandı</option>
                    <option value="reddedildi" <?= $durum_filtre==='reddedildi' ? 'selected':'' ?>>Reddedildi</option>
                </select>
            </div>
            <div class="col-md-5 d-flex gap-2">
                <button type="submit" class="btn btn-success"><i class="bi bi-funnel"></i> Filtrele</button>
                <a href="izin_liste.php" class="btn btn-secondary"><i class="bi bi-x-lg"></i> Temizle</a>
            </div>
            <div class="col-md-4 text-end">
                <a href="izin_ekle.php<?= ($kendi || $ogrenci_id) ? '?ogrenci_id='.$ogrenci_id : '' ?>"
                   class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Yeni İzin Talebi
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-success">
                    <tr>
                        <th>#</th>
                        <?php if (!$kendi): ?><th>Öğrenci</th><?php endif; ?>
                        <th>İzin Türü</th>
                        <th>Başlangıç</th>
                        <th>Bitiş</th>
                        <th>Gün</th>
                        <th>Açıklama</th>
                        <th>Durum</th>
                        <?php if (!$kendi): ?><th class="text-center">İşlemler</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($izinler)): ?>
                    <tr><td colspan="<?= $kendi ? 7 : 9 ?>" class="text-center text-muted py-4">
                        <i class="bi bi-calendar-x" style="font-size:2rem;"></i>
                        <p class="mt-2">İzin kaydı bulunamadı.</p>
                        <?php if ($kendi): ?>
                            <a href="izin_ekle.php" class="btn btn-outline-primary btn-sm">İzin Talebi Oluştur</a>
                        <?php endif; ?>
                    </td></tr>
                    <?php else: ?>
                    <?php foreach ($izinler as $iz): ?>
                    <tr>
                        <td><?= $iz['id'] ?></td>
                        <?php if (!$kendi): ?>
                        <td>
                            <a href="izin_liste.php?ogrenci_id=<?= $iz['ogrenci_id'] ?>" class="text-decoration-none fw-bold">
                                <?= htmlspecialchars($iz['ad'] . ' ' . $iz['soyad']) ?>
                            </a>
                            <small class="text-muted d-block">
                                No: <?= htmlspecialchars($iz['okul_no']) ?> · Oda: <?= htmlspecialchars($iz['oda_no'] ?? '-') ?>
                            </small>
                        </td>
                        <?php endif; ?>
                        <td><?= htmlspecialchars(ogrenci_izin_turu_adi($iz['izin_turu'])) ?></td>
                        <td><?= tarih_format($iz['baslangic_tarihi']) ?></td>
                        <td><?= tarih_format($iz['bitis_tarihi']) ?></td>
                        <td><span class="badge bg-secondary"><?= $iz['gun_sayisi'] ?> Gün</span></td>
                        <td><span title="<?= htmlspecialchars($iz['aciklama'] ?? '') ?>">
                            <?= htmlspecialchars(mb_substr($iz['aciklama'] ?? '-', 0, 30)) ?>
                            <?= mb_strlen($iz['aciklama'] ?? '') > 30 ? '…' : '' ?>
                        </span></td>
                        <td><?= durum_badge($iz['durum']) ?></td>
                        <?php if (!$kendi): ?>
                        <td class="text-center">
                            <?php if ($iz['durum'] === 'bekliyor'): ?>
                            <form method="POST" action="izin_onayla.php" class="d-inline"
                                  onsubmit="return confirm('İzni onaylamak istiyor musunuz?')">
                                <?= csrf_input() ?>
                                <input type="hidden" name="id" value="<?= $iz['id'] ?>">
                                <input type="hidden" name="aksiyon" value="onayla">
                                <button type="submit" class="btn btn-success btn-sm" title="Onayla">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                            </form>
                            <form method="POST" action="izin_onayla.php" class="d-inline"
                                  onsubmit="return confirm('İzni reddetmek istiyor musunuz?')">
                                <?= csrf_input() ?>
                                <input type="hidden" name="id" value="<?= $iz['id'] ?>">
                                <input type="hidden" name="aksiyon" value="reddet">
                                <button type="submit" class="btn btn-danger btn-sm" title="Reddet">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" action="izin_sil.php" class="d-inline"
                                  onsubmit="return confirm('İzin kaydını silmek istiyor musunuz?')">
                                <?= csrf_input() ?>
                                <input type="hidden" name="id" value="<?= $iz['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm ms-1" title="Sil">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
