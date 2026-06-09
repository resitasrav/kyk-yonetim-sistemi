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
    // Personel ile bağlı giriş hesabını tek transaction'da sil (öksüz hesap kalmasın)
    $db->beginTransaction();
    try {
        $db->prepare("DELETE FROM yoneticiler WHERE rol='personel' AND ilgili_id = ?")
           ->execute([$id]);
        $stmt = $db->prepare("DELETE FROM personel WHERE id = ?");
        $stmt->execute([$id]);
        $db->commit();
        mesaj_ayarla($stmt->rowCount() ? 'Personel kaydı ve giriş hesabı silindi.' : 'Kayıt bulunamadı.',
                      $stmt->rowCount() ? 'success' : 'danger');
    } catch (Exception $e) {
        $db->rollBack();
        mesaj_ayarla('Silme işlemi başarısız: ' . $e->getMessage(), 'danger');
    }
} else {
    mesaj_ayarla('Geçersiz istek.', 'danger');
}

header('Location: liste.php');
exit;
