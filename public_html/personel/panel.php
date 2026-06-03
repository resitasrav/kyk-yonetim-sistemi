<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

rol_kontrol('personel');

$personel_id = $_SESSION['ilgili_id'];

// Personel bilgilerini çek
$stmt = $db->prepare("SELECT * FROM personel WHERE id = ?");
$stmt->execute([$personel_id]);
$personel = $stmt->fetch();

if (!$personel) {
    mesaj_ayarla("Personel kaydınız bulunamadı. Lütfen yöneticiyle iletişime geçin.", "danger");
    header("Location: ../logout.php");
    exit;
}

// Son 5 izin
$stmt2 = $db->prepare("
    SELECT * FROM izinler
    WHERE personel_id = ?
    ORDER BY olusturuldu DESC
    LIMIT 5
");
$stmt2->execute([$personel_id]);
$son_izinler = $stmt2->fetchAll();

// İzin özet sayıları
$stmt3 = $db->prepare("SELECT durum, COUNT(*) as adet FROM izinler WHERE personel_id = ? GROUP BY durum");
$stmt3->execute([$personel_id]);
$izin_ozet = [];
foreach ($stmt3->fetchAll() as $row) {
    $izin_ozet[$row['durum']] = $row['adet'];
}

// Son 3 maaş
$stmt4 = $db->prepare("
    SELECT * FROM maas_odemeleri
    WHERE personel_id = ?
    ORDER BY odeme_ayi DESC
    LIMIT 3
");
$stmt4->execute([$personel_id]);
$son_maaslar = $stmt4->fetchAll();

$sayfa_basligi = "Personel Paneli";
require_once '../includes/header.php';
?>

<div class="row g-4 mb-4">
    <!-- Profil kartı -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <i class="bi bi-person-badge-fill text-primary" style="font-size: 4rem;"></i>
                </div>
                <h5 class="fw-bold"><?= htmlspecialchars($personel['ad'] . ' ' . $personel['soyad']) ?></h5>
                <p class="text-muted small mb-1"><?= htmlspecialchars($personel['gorevi']) ?></p>
                <span class="badge bg-primary mb-3"><?= htmlspecialchars($personel['departman']) ?></span>

                <a href="profil.php" class="btn btn-outline-primary btn-sm w-100 mb-3">
                    <i class="bi bi-pencil-square"></i> Profilimi Düzenle
                </a>
                <hr>
                <ul class="list-unstyled text-start small">
                    <li class="mb-2"><i class="bi bi-hash text-muted me-2"></i><strong>Sicil No:</strong> <?= htmlspecialchars($personel['sicil_no']) ?></li>
                    <li class="mb-2"><i class="bi bi-telephone text-muted me-2"></i><strong>Telefon:</strong> <?= htmlspecialchars($personel['telefon'] ?? '-') ?></li>
                    <li class="mb-2"><i class="bi bi-envelope text-muted me-2"></i><strong>E-posta:</strong> <?= htmlspecialchars($personel['email'] ?? '-') ?></li>
                    <li class="mb-2"><i class="bi bi-calendar text-muted me-2"></i><strong>İşe Giriş:</strong> <?= tarih_format($personel['ise_giris']) ?></li>
                    <li><i class="bi bi-circle-fill text-muted me-2"></i><strong>Durum:</strong> <?= durum_badge($personel['durum']) ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Özet kartlar -->
        <div class="row g-3 mb-4">
            <div class="col-4">
                <div class="card border-0 shadow-sm text-center p-3 bg-warning text-dark">
                    <div style="font-size:2rem;"><i class="bi bi-hourglass-split"></i></div>
                    <div class="fs-3 fw-bold"><?= $izin_ozet['bekliyor'] ?? 0 ?></div>
                    <div class="small">Bekleyen İzin</div>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 shadow-sm text-center p-3 bg-success text-white">
                    <div style="font-size:2rem;"><i class="bi bi-check-circle"></i></div>
                    <div class="fs-3 fw-bold"><?= $izin_ozet['onaylandi'] ?? 0 ?></div>
                    <div class="small">Onaylı İzin</div>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 shadow-sm text-center p-3 bg-primary text-white">
                    <div style="font-size:2rem;"><i class="bi bi-cash-coin"></i></div>
                    <div class="fs-3 fw-bold"><?= para_format($personel['maas']) ?></div>
                    <div class="small">Baz Maaş</div>
                </div>
            </div>
        </div>

        <!-- Son izinler -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-event text-primary"></i> Son İzin Taleplerim</span>
                <a href="izin_ekle.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Yeni Talep
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($son_izinler)): ?>
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-calendar-x" style="font-size:2rem;"></i>
                        <p class="mt-2 mb-1">Henüz izin talebiniz yok.</p>
                        <a href="izin_ekle.php" class="btn btn-outline-primary btn-sm">İzin Talebi Oluştur</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle small">
                            <thead class="table-light">
                                <tr>
                                    <th>Tür</th>
                                    <th>Başlangıç</th>
                                    <th>Bitiş</th>
                                    <th>Gün</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($son_izinler as $izin): ?>
                                <tr>
                                    <td><?= htmlspecialchars(ucfirst($izin['izin_turu'])) ?></td>
                                    <td><?= tarih_format($izin['baslangic_tarihi']) ?></td>
                                    <td><?= tarih_format($izin['bitis_tarihi']) ?></td>
                                    <td><?= $izin['gun_sayisi'] ?></td>
                                    <td><?= durum_badge($izin['durum']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Son maaşlar -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-cash-stack text-success"></i> Son Maaş Ödemelerim
            </div>
            <div class="card-body p-0">
                <?php if (empty($son_maaslar)): ?>
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-cash-coin" style="font-size:2rem;"></i>
                        <p class="mt-2">Henüz maaş kaydı yok.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle small">
                            <thead class="table-light">
                                <tr>
                                    <th>Dönem</th>
                                    <th>Net Maaş</th>
                                    <th>Prim</th>
                                    <th>Toplam</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($son_maaslar as $maas): ?>
                                <tr>
                                    <td><?= date('F Y', strtotime($maas['odeme_ayi'])) ?></td>
                                    <td><?= para_format($maas['net_maas']) ?></td>
                                    <td><?= para_format($maas['prim']) ?></td>
                                    <td class="fw-bold"><?= para_format($maas['toplam']) ?></td>
                                    <td><?= durum_badge($maas['durum']) ?></td>
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
