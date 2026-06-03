<?php
session_start();
$_SESSION['modul'] = 'personel';
require_once '../config/db.php';
require_once '../includes/functions.php';
rol_kontrol('yonetici');

$id      = (int)($_GET['id'] ?? 0);
$aksiyon = $_GET['aksiyon'] ?? '';

if ($id && in_array($aksiyon, ['onayla', 'reddet'])) {
    $yeni_durum = $aksiyon === 'onayla' ? 'onaylandi' : 'reddedildi';

    // $pdo yerine $db
    $stmt = $db->prepare("UPDATE izinler SET durum=? WHERE id=?");
    $stmt->execute([$yeni_durum, $id]);

    // Eğer onaylandıysa personel durumunu otomatik 'izinli' yap
    if ($aksiyon === 'onayla') {
        $iz = $db->prepare("SELECT personel_id FROM izinler WHERE id=?");
        $iz->execute([$id]);
        $row = $iz->fetch();
        if ($row) {
            $db->prepare("UPDATE personel SET durum='izinli' WHERE id=?")->execute([$row['personel_id']]);
        }
    }

    mesaj_ayarla('İzin talebi ' . ($aksiyon === 'onayla' ? 'onaylandı.' : 'reddedildi.'), 'success');
} else {
    mesaj_ayarla('Geçersiz istek.', 'danger');
}

header('Location: izin_liste.php');
exit;
?>