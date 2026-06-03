<?php
// Veritabanı bağlantı ayarları
// Bu dosya .gitignore'a eklidir - GitHub'a yükleme!

define('DB_HOST', 'localhost'); // localde de çalışması için .
define('DB_USER', 'databse_kullanici_adi'); //  Database kullanıcı adı 
define('DB_PASS', 'database_sifre'); // databse şifresi
define('DB_NAME', 'create_adilen_databse');// kullanılan database adı 

try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("Veritabanı bağlantısı başarısız: " . $e->getMessage());
}
?>