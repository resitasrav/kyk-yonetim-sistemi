<?php
session_start();
$_SESSION['modul'] = 'ogrenci';
require_once '../config/db.php';
require_once '../includes/functions.php';
rol_kontrol('yonetici');

$sayfa_basligi = 'Öğrenci Listesi';

// Arama / filtre
$arama = trim($_GET['arama'] ?? '');
$durum_filtre = $_GET['durum'] ?? '';

$sql = "SELECT * FROM ogrenciler WHERE 1=1";
$params = [];

if ($arama) {
    $sql .= " AND (ad LIKE ? OR soyad LIKE ? OR okul_no LIKE ? OR bolum LIKE ?)";
    $params = array_merge($params, ["%$arama%", "%$arama%", "%$arama%", "%$arama%"]);
}
if ($durum_filtre) {
    $sql .= " AND durum = ?";
    $params[] = $durum_filtre;
}
$sql .= " ORDER BY olusturuldu DESC";

// PDO sorgusu çalıştırılıyor # $db.php 
$stmt = $db->prepare($sql);
$stmt->execute($params);
$ogrenciler = $stmt->fetchAll();

// İstatistik
$istatistik = $db->query("
    SELECT
        COUNT(*) as toplam,
        SUM(durum='aktif') as aktif,
        SUM(durum='pasif') as pasif,
        SUM(durum='mezun') as mezun
    FROM ogrenciler
")->fetch();

include '../includes/header.php';
?>

<div class="row g-3 mb-4 text-center">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-primary border-4 h-100">
            <div class="card-body">
                <h2 class="display-6 fw-bold text-primary mb-0"><?= $istatistik['toplam'] ?></h2>
                <span class="text-muted">Toplam Öğrenci</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
            <div class="card-body">
                <h2 class="display-6 fw-bold text-success mb-0"><?= $istatistik['aktif'] ?? 0 ?></h2>
                <span class="text-muted">Aktif</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-secondary border-4 h-100">
            <div class="card-body">
                <h2 class="display-6 fw-bold text-secondary mb-0"><?= $istatistik['pasif'] ?? 0 ?></h2>
                <span class="text-muted">Pasif</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-info border-4 h-100">
            <div class="card-body">
                <h2 class="display-6 fw-bold text-info mb-0"><?= $istatistik['mezun'] ?? 0 ?></h2>
                <span class="text-muted">Mezun</span>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-5">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-list-ul"></i> Öğrenci Listesi</h5>
    </div>
    
    <div class="card-body">
        <form method="GET" class="row g-2 mb-4">
            <div class="col-md-5">
                <input type="text" name="arama" class="form-control" placeholder="Ad, soyad, okul no, bölüm..." value="<?= htmlspecialchars($arama) ?>">
            </div>
            <div class="col-md-3">
                <select name="durum" class="form-select">
                    <option value="">Tüm Durumlar</option>
                    <option value="aktif"  <?= $durum_filtre==='aktif'  ? 'selected':'' ?>>Aktif</option>
                    <option value="pasif"  <?= $durum_filtre==='pasif'  ? 'selected':'' ?>>Pasif</option>
                    <option value="mezun"  <?= $durum_filtre==='mezun'  ? 'selected':'' ?>>Mezun</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search"></i> Ara</button>
                <a href="liste.php" class="btn btn-secondary flex-fill"><i class="bi bi-x-lg"></i> Temizle</a>
                <a href="ekle.php" class="btn btn-success flex-fill"><i class="bi bi-plus-lg"></i> Yeni</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Ad Soyad</th>
                        <th>TC No</th>
                        <th>Okul No</th>
                        <th>Bölüm</th>
                        <th>Sınıf</th>
                        <th>Oda</th>
                        <th>Durum</th>
                        <th class="text-center">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ogrenciler)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">Kayıt bulunamadı.</td></tr>
                    <?php else: ?>
                    <?php foreach ($ogrenciler as $o): ?>
                    <tr>
                        <td><?= $o['id'] ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($o['ad'] . ' ' . $o['soyad']) ?></td>
                        <td><?= htmlspecialchars($o['tc_no']) ?></td>
                        <td><?= htmlspecialchars($o['okul_no']) ?></td>
                        <td><?= htmlspecialchars($o['bolum']) ?></td>
                        <td><?= $o['sinif'] ?>. Sınıf</td>
                        <td><?= htmlspecialchars($o['oda_no'] ?? '-') ?></td>
                        <td><?= durum_badge($o['durum']) ?></td>
                        <td class="text-center">
                            <a href="duzenle.php?id=<?= $o['id'] ?>" class="btn btn-warning btn-sm" title="Düzenle">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form method="POST" action="sil.php" class="d-inline"
                                  onsubmit="return confirm('Bu öğrenciyi silmek istediğinizden emin misiniz?')">
                                <?= csrf_input() ?>
                                <input type="hidden" name="id" value="<?= $o['id'] ?>">
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