<?php
session_start();
$_SESSION['modul'] = 'personel';
require_once '../config/db.php';
require_once '../includes/functions.php';
rol_kontrol('yonetici');

$sayfa_basligi = 'Personel Listesi';

$arama = trim($_GET['arama'] ?? '');
$durum_filtre = $_GET['durum'] ?? '';

$sql = "SELECT * FROM personel WHERE 1=1";
$params = [];

if ($arama) {
    $sql .= " AND (ad LIKE ? OR soyad LIKE ? OR sicil_no LIKE ? OR gorevi LIKE ? OR departman LIKE ?)";
    $params = array_merge($params, array_fill(0, 5, "%$arama%"));
}
if ($durum_filtre) {
    $sql .= " AND durum = ?";
    $params[] = $durum_filtre;
}
$sql .= " ORDER BY olusturuldu DESC";

// $pdo yerine $db
$stmt = $db->prepare($sql);
$stmt->execute($params);
$personeller = $stmt->fetchAll();

$istatistik = $db->query("
    SELECT
        COUNT(*) as toplam,
        SUM(durum='aktif') as aktif,
        SUM(durum='izinli') as izinli,
        SUM(durum='pasif') as pasif,
        COALESCE(SUM(maas),0) as toplam_maas
    FROM personel
")->fetch();

include '../includes/header.php';
?>

<div class="row g-3 mb-4 text-center">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
            <div class="card-body">
                <h2 class="display-6 fw-bold text-success mb-0"><?= $istatistik['toplam'] ?></h2>
                <span class="text-muted">Toplam Personel</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-primary border-4 h-100">
            <div class="card-body">
                <h2 class="display-6 fw-bold text-primary mb-0"><?= $istatistik['aktif'] ?? 0 ?></h2>
                <span class="text-muted">Aktif</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-warning border-4 h-100">
            <div class="card-body">
                <h2 class="display-6 fw-bold text-warning mb-0"><?= $istatistik['izinli'] ?? 0 ?></h2>
                <span class="text-muted">İzinli</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-secondary border-4 h-100">
            <div class="card-body">
                <h2 class="display-6 fw-bold text-secondary mb-0"><?= para_format((float)$istatistik['toplam_maas']) ?></h2>
                <span class="text-muted">Toplam Maaş Yükü</span>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-5">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-success"><i class="bi bi-people"></i> Personel Listesi</h5>
    </div>
    
    <div class="card-body">
        <form method="GET" class="row g-2 mb-4">
            <div class="col-md-5">
                <input type="text" name="arama" class="form-control" placeholder="Ad, soyad, sicil no, görev..." value="<?= htmlspecialchars($arama) ?>">
            </div>
            <div class="col-md-3">
                <select name="durum" class="form-select">
                    <option value="">Tüm Durumlar</option>
                    <option value="aktif"   <?= $durum_filtre==='aktif'   ? 'selected':'' ?>>Aktif</option>
                    <option value="izinli"  <?= $durum_filtre==='izinli'  ? 'selected':'' ?>>İzinli</option>
                    <option value="pasif"   <?= $durum_filtre==='pasif'   ? 'selected':'' ?>>Pasif</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-success flex-fill"><i class="bi bi-search"></i> Ara</button>
                <a href="liste.php" class="btn btn-secondary flex-fill"><i class="bi bi-x-lg"></i> Temizle</a>
                <a href="ekle.php" class="btn btn-primary flex-fill"><i class="bi bi-plus-lg"></i> Yeni</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-success">
                    <tr>
                        <th>#</th>
                        <th>Ad Soyad</th>
                        <th>Sicil No</th>
                        <th>Görev</th>
                        <th>Departman</th>
                        <th>İşe Giriş</th>
                        <th>Maaş</th>
                        <th>Durum</th>
                        <th class="text-center">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($personeller)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">Kayıt bulunamadı.</td></tr>
                    <?php else: ?>
                    <?php foreach ($personeller as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($p['ad'] . ' ' . $p['soyad']) ?></td>
                        <td><?= htmlspecialchars($p['sicil_no']) ?></td>
                        <td><?= htmlspecialchars($p['gorevi']) ?></td>
                        <td><?= htmlspecialchars($p['departman']) ?></td>
                        <td><?= tarih_format($p['ise_giris']) ?></td>
                        <td class="fw-bold text-success"><?= para_format((float)$p['maas']) ?></td>
                        <td><?= durum_badge($p['durum']) ?></td>
                        <td class="text-center">
                            <a href="izin_liste.php?personel_id=<?= $p['id'] ?>" class="btn btn-info btn-sm text-white" title="İzinler">
                                <i class="bi bi-calendar2-week"></i>
                            </a>
                            <a href="maas_liste.php?personel_id=<?= $p['id'] ?>" class="btn btn-success btn-sm" title="Maaşlar">
                                <i class="bi bi-cash-coin"></i>
                            </a>
                            <a href="duzenle.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm" title="Düzenle">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form method="POST" action="sil.php" class="d-inline"
                                  onsubmit="return confirm('Bu personeli silmek istediğinizden emin misiniz?')">
                                <?= csrf_input() ?>
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" title="Sil">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>