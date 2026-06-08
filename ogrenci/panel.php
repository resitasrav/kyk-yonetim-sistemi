<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

rol_kontrol('ogrenci');

$ogrenci_id = $_SESSION['ilgili_id'];

// Öğrenci bilgilerini çek
$stmt = $db->prepare("SELECT * FROM ogrenciler WHERE id = ?");
$stmt->execute([$ogrenci_id]);
$ogrenci = $stmt->fetch();

if (!$ogrenci) {
    mesaj_ayarla("Öğrenci kaydınız bulunamadı. Lütfen yöneticiyle iletişime geçin.", "danger");
    header("Location: ../logout.php");
    exit;
}

// Kayıtlı etkinlikler (bekleyen + onaylanan)
$stmt2 = $db->prepare("
    SELECT ek.durum, ek.katilim_tarihi, e.baslik, e.etkinlik_tarihi, e.konum
    FROM etkinlik_katilim ek
    JOIN etkinlikler e ON e.id = ek.etkinlik_id
    WHERE ek.ogrenci_id = ?
    ORDER BY e.etkinlik_tarihi DESC
");
$stmt2->execute([$ogrenci_id]);
$katilimlar = $stmt2->fetchAll();

// Yaklaşan aktif etkinlik sayısı (katılmadıkları)
$stmt3 = $db->prepare("
    SELECT COUNT(*) FROM etkinlikler
    WHERE durum = 'aktif'
      AND etkinlik_tarihi >= CURDATE()
      AND id NOT IN (
          SELECT etkinlik_id FROM etkinlik_katilim WHERE ogrenci_id = ?
      )
");
$stmt3->execute([$ogrenci_id]);
$yaklasan_etkinlik = $stmt3->fetchColumn();

$sayfa_basligi = "Öğrenci Paneli";
require_once '../includes/header.php';
?>

<div class="row g-4 mb-4">
    <!-- Profil kartı -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <i class="bi bi-person-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h5 class="fw-bold"><?= htmlspecialchars($ogrenci['ad'] . ' ' . $ogrenci['soyad']) ?></h5>
                <p class="text-muted small mb-1"><?= htmlspecialchars($ogrenci['bolum']) ?></p>
                <span class="badge bg-success mb-3"><?= htmlspecialchars($ogrenci['sinif']) ?>. Sınıf</span>

                <a href="profil.php" class="btn btn-outline-success btn-sm w-100 mb-3">
                    <i class="bi bi-pencil-square"></i> Profilimi Düzenle
                </a>
                <hr>
                <ul class="list-unstyled text-start small">
                    <li class="mb-2"><i class="bi bi-hash text-muted me-2"></i><strong>Okul No:</strong> <?= htmlspecialchars($ogrenci['okul_no']) ?></li>
                    <li class="mb-2"><i class="bi bi-door-open text-muted me-2"></i><strong>Oda No:</strong> <?= htmlspecialchars($ogrenci['oda_no'] ?? '-') ?></li>
                    <li class="mb-2"><i class="bi bi-telephone text-muted me-2"></i><strong>Telefon:</strong> <?= htmlspecialchars($ogrenci['telefon'] ?? '-') ?></li>
                    <li class="mb-2"><i class="bi bi-envelope text-muted me-2"></i><strong>E-posta:</strong> <?= htmlspecialchars($ogrenci['email'] ?? '-') ?></li>
                    <li><i class="bi bi-calendar-check text-muted me-2"></i><strong>Kayıt:</strong> <?= tarih_format($ogrenci['kayit_tarihi']) ?></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Özet kartlar + katılımlar -->
    <div class="col-md-8">
        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="card border-0 shadow-sm text-center p-3 bg-success text-white">
                    <div style="font-size:2rem;"><i class="bi bi-calendar-event"></i></div>
                    <div class="fs-3 fw-bold"><?= count($katilimlar) ?></div>
                    <div class="small">Etkinlik Kaydım</div>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 shadow-sm text-center p-3 bg-warning text-dark">
                    <div style="font-size:2rem;"><i class="bi bi-bell"></i></div>
                    <div class="fs-3 fw-bold"><?= $yaklasan_etkinlik ?></div>
                    <div class="small">Yeni Etkinlik</div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-check text-success"></i> Etkinlik Kayıtlarım</span>
                <a href="../etkinlik/liste.php" class="btn btn-success btn-sm">
                    <i class="bi bi-plus-circle"></i> Etkinliklere Göz At
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($katilimlar)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-calendar-x" style="font-size:2rem;"></i>
                        <p class="mt-2">Henüz hiçbir etkinliğe kayıt olmadınız.</p>
                        <a href="../etkinlik/liste.php" class="btn btn-outline-success btn-sm">Etkinlikleri Gör</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Etkinlik</th>
                                    <th>Tarih</th>
                                    <th>Konum</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($katilimlar as $k): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($k['baslik']) ?></td>
                                    <td><?= tarih_format($k['etkinlik_tarihi']) ?></td>
                                    <td><?= htmlspecialchars($k['konum'] ?? '-') ?></td>
                                    <td><?= durum_badge($k['durum']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
