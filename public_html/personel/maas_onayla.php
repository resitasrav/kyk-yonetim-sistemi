<?php
session_start();
$_SESSION['modul'] = 'personel';
require_once '../config/db.php';
require_once '../includes/functions.php';
rol_kontrol('yonetici');

$id = (int)($_GET['id'] ?? 0);

if ($id) {
    // $pdo yerine $db kullanıldı. Durumu 'odendi' yap ve ödeme tarihini bugünün tarihi yap.
    $stmt = $db->prepare("UPDATE maas_odemeleri SET durum='odendi', odeme_tarihi=CURDATE() WHERE id=?");
    $stmt->execute([$id]);
    
    mesaj_ayarla('Ödeme "Ödendi" olarak başarıyla işaretlendi.', 'success');
} else {
    mesaj_ayarla('Geçersiz istek.', 'danger');
}

header('Location: maas_liste.php');
exit;
?>