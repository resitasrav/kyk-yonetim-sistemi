<?php
session_start();
$_SESSION['modul'] = 'personel';
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
    $stmt = $db->prepare("DELETE FROM personel WHERE id = ?");
    $stmt->execute([$id]);
    mesaj_ayarla($stmt->rowCount() ? 'Personel kaydı silindi.' : 'Kayıt bulunamadı.',
                  $stmt->rowCount() ? 'success' : 'danger');
} else {
    mesaj_ayarla('Geçersiz istek.', 'danger');
}

header('Location: liste.php');
exit;
