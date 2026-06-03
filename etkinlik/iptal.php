<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

// Sadece öğrenciler iptal edebilir
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

$stmt = $db->prepare("DELETE FROM etkinlik_katilim WHERE etkinlik_id = ? AND ogrenci_id = ?");
$stmt->execute([$etkinlik_id, $ogrenci_id]);

if ($stmt->rowCount() > 0) {
    mesaj_ayarla('Etkinlik kaydınız iptal edildi.', 'success');
} else {
    mesaj_ayarla('Kayıt bulunamadı.', 'warning');
}

header('Location: liste.php');
exit;
