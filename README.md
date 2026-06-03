# 🏛️ KYK Yönetim Sistemi

Modern, güvenli ve modüler bir mimariyle geliştirilmiş web tabanlı **Yurt (KYK) Yönetim ve Takip Sistemi**. Bu proje, Bursa Teknik Üniversitesi Web Tabanlı Programlama dersi kapsamında PHP & MySQL projesi olarak geliştirilmiştir.

---

## 🖼️ Ekran Görüntüleri

### Giriş Ekranı
![Giriş Ekranı](ekran1.png)

### Ana Sayfa — Modül Seçimi
![Ana Sayfa](ekran2.png)

### Öğrenci Listesi
![Öğrenci Listesi](ekran3.png)

---

## 🎬 Tanıtım Videosu

> 📺 Uygulamanın genel kullanımını gösteren tanıtım videosu:

[![Tanıtım Videosu](video_thumbnail.png)](video.mp4)

🔗 **Video Bağlantısı:** [YouTube veya Google Drive linki buraya eklenecek]

---

## 🚀 Proje Hakkında

KYK Yönetim Sistemi, bir yurdun temel operasyonlarını dijitalleştirmek için tasarlanmıştır. Sistem, yönetici yetkilendirmesiyle korunmakta olup kullanıcı dostu arayüzü sayesinde yurt müdürleri ve idari personelin iş yükünü hafifletmektedir.

### 🛠️ Kullanılan Teknolojiler

| Katman | Teknoloji |
|--------|-----------|
| **Backend** | PHP 8.x — Saf PHP, PDO |
| **Veritabanı** | MySQL / MariaDB |
| **Frontend** | HTML5, Bootstrap 5.3, Bootstrap Icons |
| **JavaScript** | Vanilla JS (form doğrulama, dinamik hesaplama) |
| **Güvenlik** | Bcrypt (password_hash), PDO Prepared Statements, Session |

---

## 🌟 Özellikler

### 🔐 Kimlik Doğrulama & Rol Sistemi
- **Rol sekmeli giriş sayfası:** Yönetici (siyah) / Personel (mavi) / Öğrenci (yeşil) sekmeleri; seçilen sekme ile kayıttaki rol eşleşmezse hata verilir
- **Doğrudan kayıt akışı:** Kayıt formunda rol seçimine göre ilgili tabloya (`personel` veya `ogrenciler`) kayıt oluşturulur, ardından `yoneticiler`'e bağlanır; önceden yönetici kaydı gerekli değildir
- Şifreler **bcrypt** algoritması ile hash'lenerek saklanır
- PHP **Session** tabanlı oturum yönetimi
- Rol bazlı sayfa erişim kontrolü (`rol_kontrol()`)
- Profil sayfalarında mevcut şifre doğrulamasıyla şifre değiştirme

### 🎓 Öğrenci Modülü (CRUD + Self-Servis)
- Yeni öğrenci kaydı oluşturma — yönetici tam bilgi girer, öğrenci kayıt sırasında okul no + oda no girer
- Okul Numarası benzersizlik kontrolü; TC Kimlik No yönetici tarafından sonradan eklenir
- Arama ve durum filtreleme (Aktif / Pasif / Mezun)
- Anlık istatistik kartları (toplam, aktif, pasif, mezun)
- **Öğrenci self-servis paneli:** kendi profil bilgileri, etkinlik kayıtları
- **Öğrenci profil sayfası:** telefon, e-posta, adres ve şifre kendi değiştirebilir; TC, okul no, bölüm yönetici yetkisindedir

### 👔 Personel Modülü (CRUD + Self-Servis)
- Personel kayıt, listeleme, düzenleme ve silme — yönetici TC, görevi, departman, maaş gibi bilgileri yönetir
- Sicil numarası ve departman bazlı yönetim
- **Personel self-servis paneli:** kendi izin geçmişi, maaş geçmişi
- **Personel profil sayfası:** telefon, e-posta ve şifre kendi değiştirebilir; TC, sicil no, görevi, maaş yönetici yetkisindedir

### 📅 İzin Takibi
- Yıllık, mazeret, hastalık, ücretsiz izin türleri
- İzin gün sayısının **hafta sonları hariç** otomatik hesaplanması
- Personel kendi izin talebini oluşturabilir, yönetici onaylar/reddeder
- Personel rolü sadece kendi izinlerini görebilir

### 💰 Maaş Yönetimi
- Personelin net maaşını otomatik getirme
- Prim ve kesinti uygulamasıyla dinamik toplam hesaplama
- Ödeme onayı ve geçmiş ödeme kayıtları
- Personel rolü sadece kendi maaşlarını görüntüleyebilir (okuma)

### 🎉 Etkinlik Modülü
- Yönetici etkinlik oluşturabilir (tarih, kapasite, konum)
- Öğrenciler etkinliklere kayıt olabilir / kaydını iptal edebilir
- Kapasite kontrolü: dolan etkinliğe kayıt engellenir
- Yönetici katılımcı listesini ve doluluk oranını görebilir

---

## 📂 Proje Dizin Yapısı

```
public_html/
├── config/
│   ├── db.php               # PDO veritabanı bağlantı ayarları
│   ├── init.sql             # Tablo yapıları ve test verileri
│   └── sql_kodlari.sql      # Yardımcı SQL sorguları
├── includes/
│   ├── header.php           # Dinamik navbar + güvenlik duvarı
│   ├── footer.php           # Alt bilgi
│   └── functions.php        # Güvenlik, tarih ve doğrulama fonksiyonları
├── ogrenci/
│   ├── liste.php            # Öğrenci listesi (arama + filtre)
│   ├── ekle.php             # Yeni öğrenci kaydı (yönetici — tam bilgi)
│   ├── duzenle.php          # Öğrenci düzenleme (yönetici)
│   ├── sil.php              # Öğrenci silme (POST+CSRF)
│   ├── panel.php            # Öğrenci self-servis paneli
│   └── profil.php           # Öğrenci profil düzenleme (telefon, e-posta, adres, şifre)
├── personel/
│   ├── liste.php            # Personel listesi
│   ├── ekle.php             # Yeni personel kaydı (yönetici — tam bilgi)
│   ├── duzenle.php          # Personel düzenleme (yönetici)
│   ├── sil.php              # Personel silme (POST+CSRF)
│   ├── panel.php            # Personel self-servis paneli
│   ├── profil.php           # Personel profil düzenleme (telefon, e-posta, şifre)
│   ├── izin_liste.php       # İzin listesi
│   ├── izin_ekle.php        # Yeni izin talebi
│   ├── izin_onayla.php      # İzin onay/red işlemi
│   ├── izin_sil.php         # İzin silme
│   ├── maas_liste.php       # Maaş ödeme listesi
│   ├── maas_ekle.php        # Yeni maaş kaydı
│   ├── maas_onayla.php      # Maaş ödeme onayı
│   └── maas_sil.php         # Maaş kaydı silme
├── etkinlik/
│   ├── liste.php            # Etkinlik listesi (yönetici: tablo, öğrenci: kart)
│   ├── ekle.php             # Yeni etkinlik (yönetici)
│   ├── duzenle.php          # Etkinlik düzenleme (yönetici)
│   ├── sil.php              # Etkinlik silme (POST+CSRF)
│   ├── katilimcilar.php     # Katılımcı listesi (yönetici)
│   ├── katil.php            # Etkinliğe kayıt (öğrenci, POST)
│   └── iptal.php            # Kayıt iptali (öğrenci, POST)
├── yonetici/
│   └── profil.php           # Hesap ayarları (mevcut şifre doğrulamalı)
├── index.php                # Yönetici dashboard (istatistik kartları)
├── login.php                # Giriş (rol bazlı yönlendirme)
├── kayit.php                # Kayıt (rol + sicil/okul no seçimi)
└── logout.php               # Güvenli çıkış
```

---

## 🗄️ Veritabanı Şeması

```
yoneticiler       ogrenciler        personel
───────────       ──────────        ────────
id (PK)           id (PK)           id (PK)
ad_soyad          ad                ad
email (UNIQUE)    soyad             soyad
sifre (hash)      tc_no (UNIQUE)    tc_no (UNIQUE)
olusturuldu       okul_no (UNIQUE)  sicil_no (UNIQUE)
                  bolum             gorevi
                  sinif             departman
                  telefon           telefon
                  email             email
                  adres             ise_giris
                  oda_no            maas
                  kayit_tarihi      durum
                  durum             olusturuldu
                  olusturuldu       guncellendi
                  guncellendi
                        
izinler                       maas_odemeleri
───────                       ──────────────
id (PK)                       id (PK)
personel_id (FK → personel)   personel_id (FK → personel)
izin_turu                     odeme_ayi
baslangic_tarihi              net_maas
bitis_tarihi                  prim
gun_sayisi                    kesinti
aciklama                      toplam
durum                         odeme_tarihi
olusturuldu                   durum
                              aciklama
                              olusturuldu

etkinlikler                   etkinlik_katilim
───────────                   ────────────────
id (PK)                       id (PK)
baslik                        etkinlik_id (FK → etkinlikler)
aciklama                      ogrenci_id  (FK → ogrenciler)
etkinlik_tarihi               katilim_tarihi
kapasite                      durum
konum                         UNIQUE (etkinlik_id, ogrenci_id)
durum
olusturuldu
guncellendi
```

---

## ⚙️ Kurulum

### Gereksinimler
- PHP 8.0+
- MySQL 5.7+ veya MariaDB 10.4+
- Apache / Nginx web sunucusu (XAMPP, WAMP, vs.)

### Adımlar

**1. Projeyi klonlayın:**
```bash
git clone https://github.com/resitasrav/kyk-yonetim-sistemi.git
```

**2. Veritabanını oluşturun:**

XAMPP/WAMP üzerinde `phpMyAdmin`'i açın ve `config/init.sql` dosyasını içeri aktarın. Bu işlem tüm tabloları ve örnek verileri otomatik kurar.

**3. Veritabanı bağlantısını ayarlayın:**

`config/db.php` dosyasını açıp kendi bilgilerinizi girin:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'kullanici_adiniz');
define('DB_PASS', 'sifreniz');
define('DB_NAME', 'kyk_yonetim');
```

**4. Sisteme giriş yapın:**

Tarayıcınızdan `kayit.php` adresine gidip ilk yönetici hesabınızı oluşturun.

> ⚠️ **Canlı sunucuya alırken** `config/db.php` içindeki veritabanı bilgilerini sunucu bilgileriyle güncellemeyi unutmayın.

---

## 🔒 Güvenlik Önlemleri

- **SQL Injection:** Tüm sorgular PDO Prepared Statements ile korunmaktadır.
- **XSS:** Tüm kullanıcı girdileri `htmlspecialchars()` ile temizlenmektedir.
- **Şifre Güvenliği:** Şifreler `password_hash()` (bcrypt) ile hash'lenerek saklanmaktadır.
- **Oturum Yönetimi:** PHP Session tabanlı kimlik doğrulama kullanılmaktadır.
- **Rol Bazlı Erişim:** Her sayfa `rol_kontrol()` ile yetkisiz erişime kapalıdır.
- **CSRF Koruması:** Tüm silme işlemleri POST + token doğrulamasıyla korunmaktadır.
- **Şifre Değişikliği:** Profil sayfasında yeni şifre girilmeden önce mevcut şifre doğrulanır.

---

## 👥 Geliştirici Ekip

Bu proje **Bursa Teknik Üniversitesi — Bilgisayar Mühendisliği** Web Tabanlı Programlama dersi kapsamında geliştirilmiştir.

| İsim | Sorumluluk |
|------|-----------|
| **Reşit Asrav** | Arayüz Tasarımı (Frontend), Veritabanı Mimarisi & Schema Güncellemeleri, Personel Modülü (liste/ekle/düzenle/sil/izin/maaş), Personel Profil Sayfası (`personel/profil.php`), Rol Bazlı Navbar & Dashboard İstatistikleri, CSRF Güvenliği, Etkinlik Modülü (yönetici tarafı) |
| **Almira** | Öğrenci Modülü (liste/ekle/düzenle/sil), Öğrenci Profil Sayfası (`ogrenci/profil.php`), Kimlik Doğrulama (Rol Sekmeli Login, Doğrudan Kayıt Akışı, Logout), Self-Servis Paneller (öğrenci & personel), Etkinlik Kayıt Sistemi (öğrenci tarafı), Test Süreçleri |

---

## 📄 Lisans

Bu proje eğitim amaçlıdır. Açık kaynaklı olarak paylaşılmaktadır.
