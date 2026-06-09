-- KYK Yönetim Sistemi - Veritabanı Kurulum Scripti

CREATE DATABASE IF NOT EXISTS dbstorage23360859080
CHARACTER SET utf8mb4
COLLATE utf8mb4_turkish_ci;

USE dbstorage23360859080;

-- =============================================
-- 0. TEMİZLİK
-- =============================================

DROP TABLE IF EXISTS etkinlik_katilim;
DROP TABLE IF EXISTS etkinlikler;
DROP TABLE IF EXISTS maas_odemeleri;
DROP TABLE IF EXISTS izinler;
DROP TABLE IF EXISTS ogrenci_izinleri;
DROP TABLE IF EXISTS personel;
DROP TABLE IF EXISTS ogrenciler;
DROP TABLE IF EXISTS yoneticiler;

-- =============================================
-- 1. ÖĞRENCİ TABLOSU
-- =============================================

CREATE TABLE IF NOT EXISTS ogrenciler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(50) NOT NULL,
    soyad VARCHAR(50) NOT NULL,
    tc_no CHAR(11) NULL UNIQUE,
    okul_no VARCHAR(20) NOT NULL UNIQUE,
    bolum VARCHAR(100) NULL,
    sinif TINYINT NOT NULL DEFAULT 1,
    telefon VARCHAR(15),
    email VARCHAR(100),
    adres TEXT,
    oda_no VARCHAR(10),
    kayit_tarihi DATE NOT NULL DEFAULT (CURRENT_DATE),
    durum ENUM('aktif','pasif','mezun') NOT NULL DEFAULT 'aktif',
    olusturuldu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    guncellendi TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- =============================================
-- 2. PERSONEL TABLOSU
-- =============================================

CREATE TABLE IF NOT EXISTS personel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(50) NOT NULL,
    soyad VARCHAR(50) NOT NULL,
    tc_no CHAR(11) NULL UNIQUE,
    sicil_no VARCHAR(20) NOT NULL UNIQUE,
    gorevi VARCHAR(100) NULL,
    departman VARCHAR(100) NULL,
    telefon VARCHAR(15),
    email VARCHAR(100),
    ise_giris DATE NOT NULL,
    maas DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    durum ENUM('aktif','pasif','izinli') NOT NULL DEFAULT 'aktif',
    olusturuldu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    guncellendi TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- =============================================
-- 3. İZİNLER TABLOSU
-- =============================================

CREATE TABLE IF NOT EXISTS izinler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    personel_id INT NOT NULL,
    izin_turu ENUM('yillik','mazeret','ucretsiz','hastalik')
        NOT NULL DEFAULT 'yillik',
    baslangic_tarihi DATE NOT NULL,
    bitis_tarihi DATE NOT NULL,
    gun_sayisi INT NOT NULL,
    aciklama TEXT,
    durum ENUM('bekliyor','onaylandi','reddedildi')
        NOT NULL DEFAULT 'bekliyor',
    olusturuldu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_izin_personel
        FOREIGN KEY (personel_id)
        REFERENCES personel(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- =============================================
-- 3.b ÖĞRENCİ İZİNLERİ TABLOSU (Evci / dışarı izni)
-- =============================================

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

-- =============================================
-- 4. MAAŞ ÖDEMELERİ TABLOSU
-- =============================================

CREATE TABLE IF NOT EXISTS maas_odemeleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    personel_id INT NOT NULL,
    odeme_ayi DATE NOT NULL,
    net_maas DECIMAL(10,2) NOT NULL,
    prim DECIMAL(10,2) DEFAULT 0.00,
    kesinti DECIMAL(10,2) DEFAULT 0.00,
    toplam DECIMAL(10,2) NOT NULL,
    odeme_tarihi DATE,
    durum ENUM('bekliyor','odendi')
        NOT NULL DEFAULT 'bekliyor',
    aciklama TEXT,
    olusturuldu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_maas_personel
        FOREIGN KEY (personel_id)
        REFERENCES personel(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- =============================================
-- 5. YÖNETİCİLER TABLOSU
-- =============================================

CREATE TABLE IF NOT EXISTS yoneticiler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_soyad VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    sifre VARCHAR(255) NOT NULL,
    rol ENUM('yonetici','personel','ogrenci') NOT NULL DEFAULT 'yonetici',
    ilgili_id INT NULL,  -- personel.id veya ogrenciler.id (role göre)
    olusturuldu TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- =============================================
-- 6. ETKİNLİKLER TABLOSU
-- =============================================

CREATE TABLE IF NOT EXISTS etkinlikler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    baslik VARCHAR(200) NOT NULL,
    aciklama TEXT,
    etkinlik_tarihi DATE NOT NULL,
    kapasite INT NOT NULL DEFAULT 50,
    konum VARCHAR(200),
    durum ENUM('aktif','iptal','tamamlandi') NOT NULL DEFAULT 'aktif',
    olusturuldu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    guncellendi TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- =============================================
-- 7. ETKİNLİK KATILIM TABLOSU
-- =============================================

CREATE TABLE IF NOT EXISTS etkinlik_katilim (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etkinlik_id INT NOT NULL,
    ogrenci_id INT NOT NULL,
    katilim_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    durum ENUM('bekliyor','onaylandi','reddedildi') NOT NULL DEFAULT 'bekliyor',
    CONSTRAINT fk_katilim_etkinlik
        FOREIGN KEY (etkinlik_id)
        REFERENCES etkinlikler(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_katilim_ogrenci
        FOREIGN KEY (ogrenci_id)
        REFERENCES ogrenciler(id)
        ON DELETE CASCADE,
    UNIQUE KEY uk_etkinlik_ogrenci (etkinlik_id, ogrenci_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- =============================================
-- 8. TEST VERİLERİ
-- =============================================

INSERT INTO ogrenciler
(ad, soyad, tc_no, okul_no, bolum, sinif, telefon, email, oda_no)
VALUES
('Ahmet', 'Yılmaz', '12345678901', '2021001',
 'Bilgisayar Mühendisliği', 3,
 '05321234567', 'ahmet@example.com', '101'),

('Fatma', 'Kaya', '98765432109', '2022002',
 'Elektrik Elektronik', 2,
 '05329876543', 'fatma@example.com', '205');

INSERT INTO personel
(ad, soyad, tc_no, sicil_no, gorevi, departman,
 telefon, email, ise_giris, maas)
VALUES
('Mehmet', 'Demir', '11122233344', 'P001',
 'Yurt Müdürü', 'Yönetim',
 '05331112233', 'mehmet@kyk.gov.tr',
 '2020-01-15', 35000.00),

('Ayşe', 'Çelik', '55566677788', 'P002',
 'Memur', 'Öğrenci İşleri',
 '05335556677', 'ayse@kyk.gov.tr',
 '2021-03-01', 25000.00);

INSERT INTO izinler
(personel_id, izin_turu, baslangic_tarihi,
 bitis_tarihi, gun_sayisi, aciklama, durum)
VALUES
(1, 'yillik', '2026-06-10', '2026-06-15',
 4, 'Yıllık iznimin bir kısmını kullanmak istiyorum.',
 'bekliyor');

INSERT INTO ogrenci_izinleri
(ogrenci_id, izin_turu, baslangic_tarihi,
 bitis_tarihi, gun_sayisi, aciklama, durum)
VALUES
(1, 'evci', '2026-06-13', '2026-06-15',
 3, 'Hafta sonu ailemin yanına gitmek istiyorum.',
 'bekliyor');

INSERT INTO maas_odemeleri
(personel_id, odeme_ayi, net_maas,
 prim, kesinti, toplam, durum)
VALUES
(2, '2026-05-01',
 25000.00, 1500.00, 0.00,
 26500.00, 'bekliyor');

-- Test yöneticisi (şifre: admin123)
INSERT INTO yoneticiler (ad_soyad, email, sifre, rol, ilgili_id)
VALUES ('KYK Yöneticisi', 'admin@kyk.gov.tr',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'yonetici', NULL);

-- Test personel kullanıcısı (personel.id=1 olan Mehmet Demir, şifre: personel123)
INSERT INTO yoneticiler (ad_soyad, email, sifre, rol, ilgili_id)
VALUES ('Mehmet Demir', 'mehmet@kyk.gov.tr',
        '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p360n.cFcDy4DlJmhBl22e',
        'personel', 1);

-- Test öğrenci kullanıcısı (ogrenciler.id=1 olan Ahmet Yılmaz, şifre: ogrenci123)
INSERT INTO yoneticiler (ad_soyad, email, sifre, rol, ilgili_id)
VALUES ('Ahmet Yılmaz', 'ahmet@example.com',
        '$2y$10$TKh8H1.PmFh97b4oDEY7xuCDFZHqHq6qFmyvvS4iqQBtfCk3p.TG6',
        'ogrenci', 1);

-- Test etkinlikleri
INSERT INTO etkinlikler (baslik, aciklama, etkinlik_tarihi, kapasite, konum, durum)
VALUES
('Bahar Şenliği', 'Yıllık bahar şenliğimize tüm öğrenciler davetlidir.',
 '2026-06-15', 200, 'Yurt Bahçesi', 'aktif'),
('Kariyer Günleri', 'Şirket temsilcileriyle networking etkinliği.',
 '2026-06-20', 100, 'Konferans Salonu', 'aktif'),
('Spor Turnuvası', 'Futbol ve voleybol turnuvası.',
 '2026-07-01', 60, 'Spor Sahası', 'aktif');