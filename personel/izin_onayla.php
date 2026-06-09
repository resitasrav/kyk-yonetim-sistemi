<?php
session_start();
$_SESSION['modul'] = 'personel';
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

    $stmt = $db->prepare("UPDATE izinler SET durum=? WHERE id=?");
    $stmt->execute([$yeni_durum, $id]);

    // İlgili personeli bul
    $iz = $db->prepare("SELECT personel_id FROM izinler WHERE id=?");
    $iz->execute([$id]);
    $row = $iz->fetch();

    if ($row) {
        if ($aksiyon === 'onayla') {
            // Onaylandıysa personel durumunu otomatik 'izinli' yap
            $db->prepare("UPDATE personel SET durum='izinli' WHERE id=?")->execute([$row['personel_id']]);
        } else {
            // Reddedildiyse, başka onaylı/aktif izni yoksa durumu 'aktif'e döndür
            $aktif_izin = $db->prepare("
                SELECT COUNT(*) FROM izinler
                WHERE personel_id = ? AND durum = 'onaylandi' AND bitis_tarihi >= CURDATE()
            ");
            $aktif_izin->execute([$row['personel_id']]);
            if ($aktif_izin->fetchColumn() == 0) {
                $db->prepare("UPDATE personel SET durum='aktif' WHERE id=? AND durum='izinli'")
                   ->execute([$row['personel_id']]);
            }
        }
    }

    mesaj_ayarla('İzin talebi ' . ($aksiyon === 'onayla' ? 'onaylandı.' : 'reddedildi.'), 'success');
} else {
    mesaj_ayarla('Geçersiz istek.', 'danger');
}

header('Location: izin_liste.php');
exit;
?>