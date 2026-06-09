<?php
session_start();
$_SESSION['modul'] = 'ogrenci';
require_once '../config/db.php';
require_once '../includes/functions.php';
rol_kontrol('yonetici');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: izin_liste.php');
    exit;
}

csrf_dogrula();

$id      = (int)($_POST['id'] ?? 0);
$aksiyon = $_POST['aksiyon'] ?? '';

if ($id && in_array($aksiyon, ['onayla', 'reddet'])) {
    $yeni_durum = $aksiyon === 'onayla' ? 'onaylandi' : 'reddedildi';

    $stmt = $db->prepare("UPDATE ogrenci_izinleri SET durum=? WHERE id=?");
    $stmt->execute([$yeni_durum, $id]);

    mesaj_ayarla('Öğrenci izin talebi ' . ($aksiyon === 'onayla' ? 'onaylandı.' : 'reddedildi.'), 'success');
} else {
    mesaj_ayarla('Geçersiz istek.', 'danger');
}

header('Location: izin_liste.php');
exit;
?>
