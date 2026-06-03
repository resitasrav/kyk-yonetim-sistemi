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

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $db->prepare("DELETE FROM izinler WHERE id = ?")->execute([$id]);
    mesaj_ayarla('İzin kaydı silindi.', 'success');
} else {
    mesaj_ayarla('Geçersiz istek.', 'danger');
}

header('Location: izin_liste.php');
exit;
