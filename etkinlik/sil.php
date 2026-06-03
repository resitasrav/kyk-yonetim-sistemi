<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
rol_kontrol('yonetici');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: liste.php');
    exit;
}

csrf_dogrula();

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $db->prepare("DELETE FROM etkinlikler WHERE id = ?")->execute([$id]);
    mesaj_ayarla('Etkinlik silindi.', 'success');
} else {
    mesaj_ayarla('Geçersiz istek.', 'danger');
}

header('Location: liste.php');
exit;
