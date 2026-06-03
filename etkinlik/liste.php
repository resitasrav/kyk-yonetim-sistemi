<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['yonetici_id'])) {
    header('Location: ../login.php');
    exit;
}

$rol = $_SESSION['rol'] ?? 'yonetici';

// Sadece yönetici ve öğrenci erişebilir
if ($rol === 'personel') {
    header('Location: ../personel/panel.php');
    exit;
}

$_SESSION['modul'] = 'ogrenci'; // Yönetici navbar bağlamı

$sayfa_basligi = 'Etkinlikler';

// Öğrenci rolü için: kendi kayıt durumlarını çek
$ogrenci_id     = ($rol === 'ogrenci') ? (int)$_SESSION['ilgili_id'] : 0;
$durum_filtre   = $_GET['durum'] ?? '';

// Yönetici: tüm etkinlikler; Öğrenci: sadece aktif ve gelecek etkinlikler
$sql = "SELECT e.*,
    (SELECT COUNT(*) FROM etkinlik_katilim WHERE etkinlik_id = e.id) AS katilimci_sayisi
    FROM etkinlikler e WHERE 1=1";
$params = [];

if ($rol === 'ogrenci') {
    $sql .= " AND e.durum = 'aktif' AND e.etkinlik_tarihi >= CURDATE()";
} elseif ($durum_filtre) {
    $sql .= " AND e.durum = ?";
    $params[] = $durum_filtre;
}
$sql .= " ORDER BY e.etkinlik_tarihi ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$etkinlikler = $stmt->fetchAll();

// Öğrencinin kayıtlı olduğu etkinlik id'leri
$kayitli_ids = [];
if ($rol === 'ogrenci') {
    $ks = $db->prepare("SELECT etkinlik_id FROM etkinlik_katilim WHERE ogrenci_id = ?");
    $ks->execute([$ogrenci_id]);
    $kayitli_ids = array_column($ks->fetchAll(), 'etkinlik_id');
}

include '../includes/header.php';
?>

<?php if ($rol === 'yonetici'): ?>
<!-- YÖNETİCİ GÖRÜNÜMÜ: Tablo -->
<div class="card shadow-sm border-0 mb-5">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-primary">
            <i class="bi bi-calendar-event"></i> Etkinlik Yönetimi
        </h5>
        <a href="ekle.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Yeni Etkinlik
        </a>
    </div>
    <div class="card-body">
        <!-- Durum filtresi -->
        <form method="GET" class="row g-2 mb-4 align-items-center">
            <div class="col-md-3">
                <select name="durum" class="form-select">
                    <option value="">Tüm Durumlar</option>
                    <option value="aktif"       <?= $durum_filtre==='aktif'       ? 'selected':'' ?>>Aktif</option>
                    <option value="iptal"       <?= $durum_filtre==='iptal'       ? 'selected':'' ?>>İptal</option>
                    <option value="tamamlandi"  <?= $durum_filtre==='tamamlandi'  ? 'selected':'' ?>>Tamamlandı</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Filtrele</button>
                <a href="liste.php" class="btn btn-secondary"><i class="bi bi-x-lg"></i> Temizle</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Etkinlik Adı</th>
                        <th>Tarih</th>
                        <th>Konum</th>
                        <th class="text-center">Kapasite</th>
                        <th class="text-center">Katılımcı</th>
                        <th>Durum</th>
                        <th class="text-center">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($etkinlikler)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Etkinlik bulunamadı.</td></tr>
                    <?php else: ?>
                    <?php foreach ($etkinlikler as $e): ?>
                    <tr>
                        <td><?= $e['id'] ?></td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($e['baslik']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars(mb_substr($e['aciklama'] ?? '', 0, 50)) ?><?= mb_strlen($e['aciklama'] ?? '') > 50 ? '…' : '' ?></small>
                        </td>
                        <td><?= tarih_format($e['etkinlik_tarihi']) ?></td>
                        <td><?= htmlspecialchars($e['konum'] ?? '-') ?></td>
                        <td class="text-center"><?= $e['kapasite'] ?></td>
                        <td class="text-center">
                            <a href="katilimcilar.php?id=<?= $e['id'] ?>" class="text-decoration-none">
                                <span class="badge <?= $e['katilimci_sayisi'] >= $e['kapasite'] ? 'bg-danger' : 'bg-success' ?>">
                                    <?= $e['katilimci_sayisi'] ?> / <?= $e['kapasite'] ?>
                                </span>
                            </a>
                        </td>
                        <td><?= durum_badge($e['durum']) ?></td>
                        <td class="text-center">
                            <a href="duzenle.php?id=<?= $e['id'] ?>" class="btn btn-warning btn-sm" title="Düzenle">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="katilimcilar.php?id=<?= $e['id'] ?>" class="btn btn-info btn-sm" title="Katılımcılar">
                                <i class="bi bi-people"></i>
                            </a>
                            <form method="POST" action="sil.php" class="d-inline"
                                  onsubmit="return confirm('Bu etkinliği silmek istiyor musunuz?')">
                                <?= csrf_input() ?>
                                <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Sil">
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

<?php else: ?>
<!-- ÖĞRENCİ GÖRÜNÜMÜ: Kart grid -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-calendar-event text-success"></i> Yaklaşan Etkinlikler</h4>
    <?php if (!empty($kayitli_ids)): ?>
        <span class="badge bg-success fs-6"><?= count($kayitli_ids) ?> etkinliğe kayıtlısınız</span>
    <?php endif; ?>
</div>

<?php if (empty($etkinlikler)): ?>
<div class="text-center text-muted py-5">
    <i class="bi bi-calendar-x" style="font-size:3rem;"></i>
    <h5 class="mt-3">Şu an aktif etkinlik bulunmuyor.</h5>
</div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($etkinlikler as $e):
        $dolu       = $e['katilimci_sayisi'] >= $e['kapasite'];
        $kayitli    = in_array($e['id'], $kayitli_ids);
        $kalan      = $e['kapasite'] - $e['katilimci_sayisi'];
    ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0 <?= $kayitli ? 'border-success border-2' : '' ?>">
            <?php if ($kayitli): ?>
            <div class="card-header bg-success text-white py-1 text-center small fw-bold">
                <i class="bi bi-check-circle"></i> Kayıtlısınız
            </div>
            <?php endif; ?>
            <div class="card-body p-4">
                <h5 class="card-title fw-bold"><?= htmlspecialchars($e['baslik']) ?></h5>
                <p class="text-muted small"><?= htmlspecialchars($e['aciklama'] ?? '') ?></p>

                <ul class="list-unstyled small mt-3">
                    <li class="mb-1"><i class="bi bi-calendar3 text-primary me-2"></i><?= tarih_format($e['etkinlik_tarihi']) ?></li>
                    <li class="mb-1"><i class="bi bi-geo-alt text-danger me-2"></i><?= htmlspecialchars($e['konum'] ?? 'Belirtilmemiş') ?></li>
                    <li><i class="bi bi-people text-success me-2"></i>
                        <?php if ($dolu): ?>
                            <span class="text-danger fw-bold">Kontenjan Dolu</span>
                        <?php else: ?>
                            <span class="text-success"><?= $kalan ?> yer kaldı</span>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
            <div class="card-footer bg-white border-0 pb-3 px-4">
                <?php if ($kayitli): ?>
                    <form method="POST" action="iptal.php"
                          onsubmit="return confirm('Etkinlik kaydınızı iptal etmek istiyor musunuz?')">
                        <input type="hidden" name="etkinlik_id" value="<?= $e['id'] ?>">
                        <button type="submit" class="btn btn-outline-danger w-100 btn-sm">
                            <i class="bi bi-x-circle"></i> Kaydımı İptal Et
                        </button>
                    </form>
                <?php elseif ($dolu): ?>
                    <button class="btn btn-secondary w-100 btn-sm" disabled>
                        <i class="bi bi-slash-circle"></i> Kontenjan Dolu
                    </button>
                <?php else: ?>
                    <form method="POST" action="katil.php">
                        <input type="hidden" name="etkinlik_id" value="<?= $e['id'] ?>">
                        <button type="submit" class="btn btn-success w-100 btn-sm">
                            <i class="bi bi-plus-circle"></i> Kayıt Ol
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
