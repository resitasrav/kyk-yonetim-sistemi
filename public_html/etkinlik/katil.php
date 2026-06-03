<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

// Sadece öğrenciler kayıt olabilir
if (!isset($_SESSION['yonetici_id']) || ($_SESSION['rol'] ?? '') !== 'ogrenci') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: liste.php');
    exit;
}

$etkinlik_id = (int)($_POST['etkinlik_id'] ?? 0);
$ogrenci_id  = (int)$_SESSION['ilgili_id'];

if (!$etkinlik_id || !$ogrenci_id) {
    mesaj_ayarla('Geçersiz istek.', 'danger');
    header('Location: liste.php');
    exit;
}

// Etkinlik var ve aktif mi?
$stmt = $db->prepare("SELECT * FROM etkinlikler WHERE id = ? AND durum = 'aktif' AND etkinlik_tarihi >= CURDATE()");
$stmt->execute([$etkinlik_id]);
$etkinlik = $stmt->fetch();

if (!$etkinlik) {
    mesaj_ayarla('Etkinlik bulunamadı veya kayıt dönemi sona erdi.', 'danger');
    header('Location: liste.php');
    exit;
}

// Zaten kayıtlı mı?
$kontrol = $db->prepare("SELECT id FROM etkinlik_katilim WHERE etkinlik_id = ? AND ogrenci_id = ?");
$kontrol->execute([$etkinlik_id, $ogrenci_id]);
if ($kontrol->fetch()) {
    mesaj_ayarla('Bu etkinliğe zaten kayıt oldunuz.', 'warning');
    header('Location: liste.php');
    exit;
}

// Kapasite dolu mu?
$kapasite_stmt = $db->prepare("SELECT COUNT(*) FROM etkinlik_katilim WHERE etkinlik_id = ?");
$kapasite_stmt->execute([$etkinlik_id]);
if ($kapasite_stmt->fetchColumn() >= $etkinlik['kapasite']) {
    mesaj_ayarla('Üzgünüz, bu etkinliğin kontenjanı doldu.', 'warning');
    header('Location: liste.php');
    exit;
}

// Kayıt oluştur
$stmt = $db->prepare("INSERT INTO etkinlik_katilim (etkinlik_id, ogrenci_id) VALUES (?, ?)");
$stmt->execute([$etkinlik_id, $ogrenci_id]);

mesaj_ayarla(htmlspecialchars($etkinlik['baslik']) . ' etkinliğine kaydınız alındı!', 'success');
header('Location: liste.php');
exit;
