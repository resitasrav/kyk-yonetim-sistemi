<?php
session_start();
$_SESSION['modul'] = 'personel';
require_once '../config/db.php';
require_once '../includes/functions.php';

$rol   = $_SESSION['rol'] ?? 'yonetici';
$kendi = ($rol === 'personel'); // Personel sadece kendi maaşlarını görür

if (!$kendi) {
    rol_kontrol('yonetici');
}

$sayfa_basligi = $kendi ? 'Maaşlarım' : 'Maaş Takibi';

$personel_id  = $kendi ? (int)$_SESSION['ilgili_id'] : (int)($_GET['personel_id'] ?? 0);
$durum_filtre = $_GET['durum'] ?? '';

$sql = "
    SELECT mo.*, p.ad, p.soyad, p.sicil_no
    FROM maas_odemeleri mo
    JOIN personel p ON p.id = mo.personel_id
    WHERE 1=1
";
$params = [];

if ($kendi || $personel_id) {
    $sql .= " AND mo.personel_id = ?";
    $params[] = $personel_id;
}
if ($durum_filtre) {
    $sql .= " AND mo.durum = ?";
    $params[] = $durum_filtre;
}
$sql .= " ORDER BY mo.odeme_ayi DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$odemeler = $stmt->fetchAll();

// Bekleyen ödeme uyarısı
if ($kendi) {
    $bekleme_stmt = $db->prepare("SELECT COUNT(*) FROM maas_odemeleri WHERE personel_id = ? AND durum = 'bekliyor'");
    $bekleme_stmt->execute([$personel_id]);
    $bekleyen = $bekleme_stmt->fetchColumn();
} else {
    $bekleyen = $db->query("SELECT COUNT(*) FROM maas_odemeleri WHERE durum='bekliyor'")->fetchColumn();
}

$filtre_personel = null;
if (!$kendi && $personel_id) {
    $ps = $db->prepare("SELECT ad, soyad FROM personel WHERE id=?");
    $ps->execute([$personel_id]);
    $filtre_personel = $ps->fetch();
}

include '../includes/header.php';
?>

<?php if ($bekleyen > 0): ?>
<div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
    <i class="bi bi-exclamation-circle-fill"></i>
    <?php if ($kendi): ?>
        <strong><?= $bekleyen ?></strong> adet maaş ödemesi henüz işleme alınmadı.
    <?php else: ?>
        <strong><?= $bekleyen ?></strong> adet bekleyen (henüz ödenmemiş) maaş kaydı var.
    <?php endif; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="card shadow-sm border-0 mb-5">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-success">
            <i class="bi bi-cash-stack"></i> <?= $sayfa_basligi ?>
            <?php if ($filtre_personel): ?>
                <span class="text-secondary">— <?= htmlspecialchars($filtre_personel['ad'] . ' ' . $filtre_personel['soyad']) ?></span>
            <?php endif; ?>
        </h5>
    </div>

    <div class="card-body">
        <form method="GET" class="row g-2 mb-4 align-items-center">
            <?php if (!$kendi && $personel_id): ?>
                <input type="hidden" name="personel_id" value="<?= $personel_id ?>">
            <?php endif; ?>

            <div class="col-md-3">
                <select name="durum" class="form-select">
                    <option value="">Tüm Durumlar</option>
                    <option value="bekliyor" <?= $durum_filtre==='bekliyor' ? 'selected':'' ?>>Bekliyor</option>
                    <option value="odendi"   <?= $durum_filtre==='odendi'   ? 'selected':'' ?>>Ödendi</option>
                </select>
            </div>
            <div class="col-md-5 d-flex gap-2">
                <button type="submit" class="btn btn-success text-white"><i class="bi bi-funnel"></i> Filtrele</button>
                <a href="maas_liste.php" class="btn btn-secondary"><i class="bi bi-x-lg"></i> Temizle</a>
            </div>
            <?php if (!$kendi): ?>
            <div class="col-md-4 text-end">
                <a href="maas_ekle.php<?= $personel_id ? '?personel_id='.$personel_id : '' ?>" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Yeni Ödeme Ekle
                </a>
            </div>
            <?php endif; ?>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-success">
                    <tr>
                        <th>#</th>
                        <?php if (!$kendi): ?><th>Personel</th><?php endif; ?>
                        <th>Ödeme Ayı</th>
                        <th>Net Maaş</th>
                        <th>Prim</th>
                        <th>Kesinti</th>
                        <th>Toplam</th>
                        <th>Ödeme Tarihi</th>
                        <th>Durum</th>
                        <?php if (!$kendi): ?><th class="text-center">İşlemler</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($odemeler)): ?>
                    <tr><td colspan="<?= $kendi ? 8 : 10 ?>" class="text-center text-muted py-4">
                        <i class="bi bi-cash-coin" style="font-size:2rem;"></i>
                        <p class="mt-2">Ödeme kaydı bulunamadı.</p>
                    </td></tr>
                    <?php else: ?>
                    <?php foreach ($odemeler as $od): ?>
                    <tr>
                        <td><?= $od['id'] ?></td>
                        <?php if (!$kendi): ?>
                        <td>
                            <a href="maas_liste.php?personel_id=<?= $od['personel_id'] ?>" class="text-decoration-none fw-bold">
                                <?= htmlspecialchars($od['ad'] . ' ' . $od['soyad']) ?>
                            </a>
                            <small class="text-muted d-block"><?= htmlspecialchars($od['sicil_no']) ?></small>
                        </td>
                        <?php endif; ?>
                        <td><span class="badge bg-secondary"><?= date('F Y', strtotime($od['odeme_ayi'])) ?></span></td>
                        <td><?= para_format((float)$od['net_maas']) ?></td>
                        <td><span class="text-success">+ <?= para_format((float)$od['prim']) ?></span></td>
                        <td><span class="text-danger">- <?= para_format((float)$od['kesinti']) ?></span></td>
                        <td class="fw-bold text-dark"><?= para_format((float)$od['toplam']) ?></td>
                        <td><?= tarih_format($od['odeme_tarihi'] ?? '') ?></td>
                        <td><?= durum_badge($od['durum']) ?></td>
                        <?php if (!$kendi): ?>
                        <td class="text-center">
                            <?php if ($od['durum'] === 'bekliyor'): ?>
                            <form method="POST" action="maas_onayla.php" class="d-inline"
                                  onsubmit="return confirm('Ödeme yapıldı olarak işaretlensin mi?')">
                                <?= csrf_input() ?>
                                <input type="hidden" name="id" value="<?= $od['id'] ?>">
                                <button type="submit" class="btn btn-success btn-sm" title="Ödendi İşaretle">
                                    <i class="bi bi-check2-all"></i> Ödendi
                                </button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" action="maas_sil.php" class="d-inline"
                                  onsubmit="return confirm('Bu ödeme kaydını silmek istiyor musunuz?')">
                                <?= csrf_input() ?>
                                <input type="hidden" name="id" value="<?= $od['id'] ?>">
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
