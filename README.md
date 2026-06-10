# 🏛️ KYK Yönetim Sistemi

Modern, güvenli ve modüler bir mimariyle geliştirilmiş web tabanlı **Yurt (KYK) Yönetim ve Takip Sistemi**. Bu proje, Bursa Teknik Üniversitesi Web Tabanlı Programlama dersi kapsamında PHP & MySQL projesi olarak geliştirilmiştir.

---

## 🖼️ Ekran Görüntüleri

> Görüntüler bağlama göre gruplanmıştır. Açmak için başlıklara tıklayın.

<details open>
<summary><b>🔐 Giriş Ekranları</b> (Yönetici / Personel / Öğrenci)</summary>
<br>
<p align="center">
  <img src="https://raw.githubusercontent.com/resitasrav/kyk-yonetim-sistemi/main/screenshots/1_giris/yonetici_giris.jpg" width="250" alt="Yönetici Girişi">
  <img src="https://raw.githubusercontent.com/resitasrav/kyk-yonetim-sistemi/main/screenshots/1_giris/personel_giris.jpg" width="250" alt="Personel Girişi">
  <img src="https://raw.githubusercontent.com/resitasrav/kyk-yonetim-sistemi/main/screenshots/1_giris/ogrenci_giris.jpg" width="250" alt="Öğrenci Girişi">
</p>
</details>

<details>
<summary><b>🛡️ Yönetici Paneli</b> (modül seçimi, öğrenci / personel / etkinlik takibi)</summary>
<br>
<p align="center">
  <img src="https://raw.githubusercontent.com/resitasrav/kyk-yonetim-sistemi/main/screenshots/2_yonetici/modul_secimi.jpg" width="700" alt="Modül Seçimi"><br><br>
  <img src="https://raw.githubusercontent.com/resitasrav/kyk-yonetim-sistemi/main/screenshots/2_yonetici/ogrenci_takibi.jpg" width="700" alt="Öğrenci Takibi"><br><br>
  <img src="https://raw.githubusercontent.com/resitasrav/kyk-yonetim-sistemi/main/screenshots/2_yonetici/personel_maas_takibi.jpg" width="700" alt="Personel Maaş Takibi"><br><br>
  <img src="https://raw.githubusercontent.com/resitasrav/kyk-yonetim-sistemi/main/screenshots/2_yonetici/etkinlik_takibi.jpg" width="700" alt="Etkinlik Takibi">
</p>
</details>

<details>
<summary><b>🎓 Öğrenci Paneli</b> (ana panel, izin alma, etkinlik kaydı)</summary>
<br>
<p align="center">
  <img src="https://raw.githubusercontent.com/resitasrav/kyk-yonetim-sistemi/main/screenshots/3_ogrenci/ana_panel.jpg" width="700" alt="Öğrenci Ana Panel"><br><br>
  <img src="https://raw.githubusercontent.com/resitasrav/kyk-yonetim-sistemi/main/screenshots/3_ogrenci/izin_alma.jpg" width="700" alt="Öğrenci İzin Alma"><br><br>
  <img src="https://raw.githubusercontent.com/resitasrav/kyk-yonetim-sistemi/main/screenshots/3_ogrenci/etkinlik_kayit.jpg" width="700" alt="Etkinlik Kaydı">
</p>
</details>

<details>
<summary><b>👔 Personel Paneli</b> (ana panel, izin talep / takip, maaş)</summary>
<br>
<p align="center">
  <img src="https://raw.githubusercontent.com/resitasrav/kyk-yonetim-sistemi/main/screenshots/4_personel/ana_panel.jpg" width="700" alt="Personel Ana Panel"><br><br>
  <img src="https://raw.githubusercontent.com/resitasrav/kyk-yonetim-sistemi/main/screenshots/4_personel/izin_talep.jpg" width="700" alt="Personel İzin Talep"><br><br>
  <img src="https://raw.githubusercontent.com/resitasrav/kyk-yonetim-sistemi/main/screenshots/4_personel/izin_takip.jpg" width="700" alt="Personel İzin Takip"><br><br>
  <img src="https://raw.githubusercontent.com/resitasrav/kyk-yonetim-sistemi/main/screenshots/4_personel/maas_takip.jpg" width="700" alt="Personel Maaş Takip">
</p>
</details>

<details>
<summary><b>🗄️ Veritabanı</b> (genel görünüm, tablo bağlantıları)</summary>
<br>
<p align="center">
  <img src="https://raw.githubusercontent.com/resitasrav/kyk-yonetim-sistemi/main/screenshots/5_veritabani/database_ekrani.jpg" width="700" alt="Veritabanı Ekranı"><br><br>
  <img src="https://raw.githubusercontent.com/resitasrav/kyk-yonetim-sistemi/main/screenshots/5_veritabani/tablo_baglantilari.jpg" width="700" alt="Tablo Bağlantıları">
</p>
</details>

> ℹ️ Görseller `screenshots/` klasöründe (GitHub deposunda) tutulur ve yukarıda **GitHub raw bağlantılarıyla** gösterilir. Uygulamanın çalışması için gerekli değildir; sunucuya yüklenmezler (bkz. [Dağıtım notu](#-dağıtımda-sunucuya-gitmeyecek-dosyalar)).

---

## 🎬 Tanıtım Videosu

> 📺 Uygulamanın genel kullanımını gösteren tanıtım videosu:

[![YouTube Video](https://img.shields.io/badge/YouTube-%F0%9F%93%BA%20Videoyu%20İzle-red?style=for-the-badge)](https://youtu.be/BbRNGKbZ5pA)

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
- **Öğrenci self-servis paneli:** kendi profil bilgileri, etkinlik kayıtları, izin talepleri
- **Öğrenci profil sayfası:** telefon, e-posta, adres ve şifre kendi değiştirebilir; TC, okul no, bölüm yönetici yetkisindedir
- **Öğrenci izin (evci/dışarı izni) sistemi:** öğrenci kendi izin talebini oluşturur, yönetici onaylar/reddeder

### 👔 Personel Modülü (CRUD + Self-Servis)
- Personel kayıt, listeleme, düzenleme ve silme — yönetici TC, görevi, departman, maaş gibi bilgileri yönetir
- Sicil numarası ve departman bazlı yönetim
- **Personel self-servis paneli:** kendi izin geçmişi, maaş geçmişi
- **Personel profil sayfası:** telefon, e-posta ve şifre kendi değiştirebilir; TC, sicil no, görevi, maaş yönetici yetkisindedir

### 📅 İzin Takibi
- **Personel izni:** Yıllık, mazeret, hastalık, ücretsiz izin türleri; gün sayısı **hafta sonları hariç** otomatik hesaplanır
- **Öğrenci izni:** Evci, günübirlik, sağlık, diğer izin türleri; gün sayısı **hafta sonları dahil** (evci izni hafta sonunu kapsar) hesaplanır
- Hem personel hem öğrenci kendi izin talebini oluşturabilir, yönetici onaylar/reddeder
- Personel/öğrenci rolü sadece kendi izinlerini görebilir; yönetici tüm talepleri yönetir
- Onaylanan personel izninde personel durumu otomatik `izinli` olur

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
│   ├── ogrenci_izin_migration.sql  # Mevcut DB'ye öğrenci izin tablosu ekleme
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
│   ├── panel.php            # Öğrenci self-servis paneli (etkinlik + izin özeti)
│   ├── profil.php           # Öğrenci profil düzenleme (telefon, e-posta, adres, şifre)
│   ├── izin_liste.php       # Öğrenci izin listesi (öğrenci: kendi, yönetici: tümü)
│   ├── izin_ekle.php        # Yeni öğrenci izin talebi (POST+CSRF)
│   ├── izin_onayla.php      # Öğrenci izin onay/red işlemi (yönetici)
│   └── izin_sil.php         # Öğrenci izin silme (POST+CSRF)
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
├── screenshots/             # Ekran görüntüleri (sadece dokümantasyon — sunucuya gitmez)
│   ├── 1_giris/             # Giriş ekranları
│   ├── 2_yonetici/          # Yönetici paneli
│   ├── 3_ogrenci/           # Öğrenci paneli
│   ├── 4_personel/          # Personel paneli
│   └── 5_veritabani/        # Veritabanı görselleri
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

ogrenci_izinleri
────────────────
id (PK)
ogrenci_id (FK → ogrenciler)
izin_turu  (evci/gunubirlik/saglik/diger)
baslangic_tarihi
bitis_tarihi
gun_sayisi
aciklama
durum      (bekliyor/onaylandi/reddedildi)
olusturuldu
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

## 📦 Dağıtımda Sunucuya Gitmeyecek Dosyalar

Ekran görüntüleri ve dokümanlar **yalnızca GitHub deposu için**dir; çalışan uygulamanın bunlara ihtiyacı yoktur. Okul sunucusuna gereksiz yer kaplamasınlar diye iki katmanlı koruma vardır:

1. **`.gitattributes` → `export-ignore`:** GitHub'dan **"Download ZIP"** ile veya `git archive` ile aldığınız pakette `screenshots/`, `README.md`, `AI.md` **otomatik hariç tutulur.** Yani ZIP indirip sunucuya yüklerseniz görseller pakette zaten olmaz.

2. **README'deki görseller mutlak GitHub raw bağlantısıyla** gösterilir. Böylece görsel dosyaları sadece GitHub'da durur; sunucuya kopyalanmasalar bile depo sayfasında sorunsuz görünür.

**FTP / dosya yöneticisiyle elle yüklüyorsanız**, sunucuya sadece şunları atın:

```
config/  includes/  ogrenci/  personel/  etkinlik/  yonetici/
index.php  login.php  kayit.php  logout.php
```

`screenshots/`, `screenshoots/`, `README.md`, `AI.md` ve `.git*` dosyalarını **yüklemeyin** — uygulama bunlar olmadan tam çalışır.

> 💡 `git archive` ile temiz paket oluşturmak isterseniz:
> ```bash
> git archive --format=zip --output=deploy.zip main
> ```
> Bu komutun ürettiği `deploy.zip`, `export-ignore` sayesinde görselleri ve dokümanları içermez.

---

## 🔒 Güvenlik Önlemleri

- **SQL Injection:** Tüm sorgular PDO Prepared Statements ile korunmaktadır.
- **XSS:** Tüm kullanıcı girdileri `htmlspecialchars()` ile temizlenmektedir.
- **Şifre Güvenliği:** Şifreler `password_hash()` (bcrypt) ile hash'lenerek saklanmaktadır.
- **Oturum Yönetimi:** PHP Session tabanlı kimlik doğrulama kullanılmaktadır.
- **Rol Bazlı Erişim:** Her sayfa `rol_kontrol()` ile yetkisiz erişime kapalıdır.
- **CSRF Koruması:** Tüm durum değiştiren işlemler (ekleme, silme, izin onay/red, etkinlik kayıt/iptal) POST + token doğrulamasıyla korunmaktadır.
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
