<?php
session_start();
$_SESSION['modul'] = 'personel';
require_once '../config/db.php';
require_once '../includes/functions.php';
rol_kontrol('yonetici');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: maas_liste.php');
    exit;
}

csrf_dogrula();

$id = (int)($_POST['id'] ?? 0);

if ($id) {
    // Durumu 'odendi' yap ve ödeme tarihini bugünün tarihi yap.
    $stmt = $db->prepare("UPDATE maas_odemeleri SET durum='odendi', odeme_tarihi=CURDATE() WHERE id=?");
    $stmt->execute([$id]);
    
    mesaj_ayarla('Ödeme "Ödendi" olarak başarıyla işaretlendi.', 'success');
} else {
    mesaj_ayarla('Geçersiz istek.', 'danger');
}

header('Location: maas_liste.php');
exit;
?>