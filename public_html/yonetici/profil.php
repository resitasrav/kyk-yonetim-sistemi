<?php
session_start();
// Modülümüzü yonetici 
$_SESSION['modul'] = 'yonetici';
require_once '../config/db.php';
require_once '../includes/functions.php';

$sayfa_basligi = 'Profil Ayarları';
$yonetici_id = $_SESSION['yonetici_id'];
$hatalar = [];

// Yöneticinin mevcut bilgilerini çek
$stmt = $db->prepare("SELECT * FROM yoneticiler WHERE id = ?");
$stmt->execute([$yonetici_id]);
$yonetici = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad_soyad      = post('ad_soyad');
    $email         = post('email');
    $mevcut_sifre  = $_POST['mevcut_sifre'] ?? '';
    $yeni_sifre    = $_POST['yeni_sifre'] ?? '';

    if (!$ad_soyad) $hatalar[] = "Ad Soyad boş bırakılamaz.";
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $hatalar[] = "Geçerli bir e-posta giriniz.";

    // Şifre değiştirilmek isteniyorsa mevcut şifre kontrolü
    if (!empty($yeni_sifre)) {
        if (!password_verify($mevcut_sifre, $yonetici['sifre'])) {
            $hatalar[] = "Mevcut şifreniz hatalı.";
        } elseif (strlen($yeni_sifre) < 6) {
            $hatalar[] = "Yeni şifre en az 6 karakter olmalıdır.";
        }
    }

    if (empty($hatalar)) {
        $kontrol = $db->prepare("SELECT id FROM yoneticiler WHERE email = ? AND id != ?");
        $kontrol->execute([$email, $yonetici_id]);
        if ($kontrol->fetch()) {
            $hatalar[] = "Bu e-posta adresi başka bir hesap tarafından kullanılıyor.";
        }
    }

    if (empty($hatalar)) {
        if (!empty($yeni_sifre)) {
            $kriptolu = password_hash($yeni_sifre, PASSWORD_DEFAULT);
            $db->prepare("UPDATE yoneticiler SET ad_soyad=?, email=?, sifre=? WHERE id=?")
               ->execute([$ad_soyad, $email, $kriptolu, $yonetici_id]);
        } else {
            $db->prepare("UPDATE yoneticiler SET ad_soyad=?, email=? WHERE id=?")
               ->execute([$ad_soyad, $email, $yonetici_id]);
        }
        $_SESSION['yonetici_ad'] = $ad_soyad;
        mesaj_ayarla('Profil bilgileriniz güncellendi.', 'success');
        header('Location: profil.php');
        exit;
    }
}

include '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 mb-5">
            <div class="card-header bg-dark text-white py-3 border-bottom">
                <h5 class="mb-0 fw-bold"><i class="bi bi-person-gear"></i> Profil Ayarları</h5>
            </div>
            <div class="card-body p-4">
                
                <?php foreach ($hatalar as $h): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($h) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endforeach; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Ad Soyad <span class="text-danger">*</span></label>
                        <input type="text" name="ad_soyad" class="form-control" value="<?= htmlspecialchars($yonetici['ad_soyad']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">E-posta Adresi <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($yonetici['email']) ?>" required>
                    </div>
                    <hr class="my-3">
                    <p class="text-muted small mb-3"><i class="bi bi-lock"></i> Şifre değiştirmek istemiyorsanız aşağıdaki alanları boş bırakın.</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mevcut Şifre</label>
                        <input type="password" name="mevcut_sifre" class="form-control" placeholder="Mevcut şifreniz">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Yeni Şifre</label>
                        <input type="password" name="yeni_sifre" class="form-control" placeholder="En az 6 karakter">
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="../index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Ana Sayfaya Dön</a>
                        <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Bilgileri Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>