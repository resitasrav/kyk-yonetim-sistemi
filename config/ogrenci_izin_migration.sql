-- =====================================================================
-- ÖĞRENCİ İZİN SİSTEMİ - MIGRATION
-- ---------------------------------------------------------------------
-- Mevcut veritabanını SIFIRLAMADAN öğrenci izin özelliğini eklemek için
-- bu dosyayı çalıştırın. (init.sql tüm tabloları yeniden kurar; bu dosya
-- yalnızca yeni tabloyu ekler ve örnek bir kayıt oluşturur.)
--
-- Kullanım (phpMyAdmin > SQL sekmesi veya CLI):
--   USE <veritabani_adiniz>;
--   SOURCE config/ogrenci_izin_migration.sql;
-- =====================================================================

CREATE TABLE IF NOT EXISTS ogrenci_izinleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ogrenci_id INT NOT NULL,
    izin_turu ENUM('evci','gunubirlik','saglik','diger')
        NOT NULL DEFAULT 'evci',
    baslangic_tarihi DATE NOT NULL,
    bitis_tarihi DATE NOT NULL,
    gun_sayisi INT NOT NULL,
    aciklama TEXT,
    durum ENUM('bekliyor','onaylandi','reddedildi')
        NOT NULL DEFAULT 'bekliyor',
    olusturuldu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_ogrenci_izin
        FOREIGN KEY (ogrenci_id)
        REFERENCES ogrenciler(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Örnek kayıt (id=1 olan öğrenci için bekleyen evci izni)
INSERT INTO ogrenci_izinleri
(ogrenci_id, izin_turu, baslangic_tarihi, bitis_tarihi, gun_sayisi, aciklama, durum)
SELECT 1, 'evci', '2026-06-13', '2026-06-15', 3,
       'Hafta sonu ailemin yanına gitmek istiyorum.', 'bekliyor'
WHERE EXISTS (SELECT 1 FROM ogrenciler WHERE id = 1);
