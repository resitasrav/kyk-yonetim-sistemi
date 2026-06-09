# AI Sohbet Günlüğü — KYK Yönetim Sistemi

Bu dosya, KYK Yönetim Sistemi projesinin geliştirilmesi sırasında yapay zeka (Claude) ile yapılan gerçek sohbet kayıtlarını içermektedir.

---

## Sohbet 1 — Veritabanı Tasarımı

**Ben:** Bir KYK yurdu için yönetim sistemi yapıyorum. Öğrenci, personel, izin ve maaş tablolarına ihtiyacım var. MySQL için nasıl bir şema önerirsin?

**Claude:** KYK sistemi için şu tablo yapısını öneririm:

```sql
CREATE TABLE ogrenciler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(50) NOT NULL,
    soyad VARCHAR(50) NOT NULL,
    tc_no CHAR(11) NOT NULL UNIQUE,
    okul_no VARCHAR(20) NOT NULL UNIQUE,
    bolum VARCHAR(100) NOT NULL,
    sinif TINYINT NOT NULL DEFAULT 1,
    oda_no VARCHAR(10),
    durum ENUM('aktif','pasif','mezun') NOT NULL DEFAULT 'aktif',
    olusturuldu TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Personel ve izin tabloları için `CASCADE` silme kuralı eklemenizi öneririm — personel silinince izinleri de otomatik silinsin:

```sql
CONSTRAINT fk_izin_personel
    FOREIGN KEY (personel_id)
    REFERENCES personel(id)
    ON DELETE CASCADE
```

**Ben:** Peki `ON DELETE CASCADE` neden önemli?

**Claude:** Cascade olmadan bir personeli sildiğinizde, o personele ait izin ve maaş kayıtları veritabanında "sahipsiz" kalır (orphan records). Bu hem veri tutarsızlığı yaratır hem de JOIN sorgularında hata çıkabilir. Cascade ile üst kayıt silinince alt kayıtlar da otomatik temizlenir.

**Ben:** Anladım. utf8mb4 ile utf8 farkı ne?

**Claude:** `utf8mb4` Türkçe karakterler ve emoji dahil tüm Unicode karakterleri destekler. MySQL'in eski `utf8` tipi aslında UTF-8'in sadece 3 byte'lık alt kümesidir — 4 byte gerektiren karakterleri saklayamaz. Türkçe projeler için `utf8mb4_turkish_ci` collation kullanın: hem Türkçe karakter desteği hem de büyük/küçük harf duyarsız sıralama sağlar.

---

## Sohbet 2 — PDO ve Güvenlik

**Ben:** SQL injection nedir, nasıl önlerim?

**Claude:** SQL injection, kullanıcı girdisinin sorguya doğrudan eklenmesiyle oluşur. Örneğin:

```php
// YANLIŞ - injection'a açık:
$sql = "SELECT * FROM users WHERE email = '" . $_POST['email'] . "'";

// DOĞRU - prepared statement ile:
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$_POST['email']]);
```

Kullanıcı `' OR '1'='1` girerse birinci yöntemde tüm tabloyu döndürür. İkinci yöntemde girdi parametre olarak muamele görür, SQL kodu olarak yorumlanamaz.

**Ben:** Her yerde `$db->prepare()` kullanmak zorunda mıyım?

**Claude:** Evet, kullanıcıdan veya dış kaynaktan gelen her veri için. Kendi kodunuzda ürettiğiniz sabit değerler (örn. `ORDER BY id DESC`) için prepared statement gerekmez. Kural şu: güvensiz kaynaktan gelen her şey `?` placeholder ile bağlanmalı.

---

## Sohbet 3 — Kullanıcı Kayıt ve Giriş

**Ben:** Kayıt formunda şifreyi nasıl güvenli saklarım?

**Claude:** `password_hash()` kullanın:

```php
$kriptolu = password_hash($_POST['sifre'], PASSWORD_DEFAULT);
// Veritabanına $kriptolu kaydedin
```

Giriş kontrolünde:
```php
if (password_verify($_POST['sifre'], $kullanici['sifre'])) {
    // Giriş başarılı
}
```

`PASSWORD_DEFAULT` şu an bcrypt kullanır ve PHP güncellenince otomatik daha güçlü algoritmaya geçer. Şifreyi asla düz metin saklamayın, md5/sha1 de kullanmayın — bunlar hızlı ve kırılabilir.

**Ben:** Session'da ne saklamalıyım?

**Claude:** Minimum tutun: kullanıcı ID'si, adı ve rolü yeterli. Şifre veya hassas bilgileri session'a koymayın:

```php
$_SESSION['yonetici_id'] = $kullanici['id'];
$_SESSION['yonetici_ad'] = $kullanici['ad_soyad'];
$_SESSION['rol']         = $kullanici['rol'];
```

Her korumalı sayfanın başında `$_SESSION['yonetici_id']` kontrolü yapın:
```php
if (!isset($_SESSION['yonetici_id'])) {
    header('Location: login.php');
    exit;
}
```

---

## Sohbet 4 — Kullanıcı Rol Sistemi

**Ben:** Sistemde üç farklı kullanıcı tipi olacak: yönetici, personel ve öğrenci. Her biri farklı sayfalar görecek. Nasıl tasarlarım?

**Claude:** `yoneticiler` tablosuna `rol` sütunu ekleyerek tek tabloda yönetebilirsiniz:

```sql
ALTER TABLE yoneticiler
ADD COLUMN rol ENUM('yonetici','personel','ogrenci') NOT NULL DEFAULT 'yonetici',
ADD COLUMN ilgili_id INT NULL;
```

`ilgili_id` personel için `personel.id`, öğrenci için `ogrenciler.id`'yi gösterir.

Giriş sonrası yönlendirme:
```php
if ($kullanici['rol'] === 'personel') {
    header('Location: personel/panel.php');
} elseif ($kullanici['rol'] === 'ogrenci') {
    header('Location: ogrenci/panel.php');
} else {
    header('Location: index.php');
}
```

**Ben:** Personel rolündeki biri admin sayfasına girmeye çalışırsa?

**Claude:** Bunun için merkezi bir kontrol fonksiyonu yazın:

```php
function rol_kontrol(string ...$izinli_roller): void {
    if (!isset($_SESSION['yonetici_id'])) {
        header('Location: ../login.php');
        exit;
    }
    $rol = $_SESSION['rol'] ?? 'yonetici';
    if (!in_array($rol, $izinli_roller)) {
        // Yetkisiz — kendi panosuna geri gönder
        header('Location: ../index.php');
        exit;
    }
}
```

Admin sayfalarının başına `rol_kontrol('yonetici');` ekleyin. Personel/öğrenci bu sayfaya URL yazarak giremez.

---

## Sohbet 5 — Personel Self-Servis Paneli

**Ben:** Personel giriş yapınca sadece kendi izinlerini görsün istiyorum. Mevcut izin listesi tüm izinleri gösteriyor, bunu nasıl ayırt ederim?

**Claude:** `$_SESSION['rol']` kontrolüyle aynı sayfayı iki modda çalıştırabilirsiniz:

```php
$rol   = $_SESSION['rol'] ?? 'yonetici';
$kendi = ($rol === 'personel');

// Personel kendi id'sini kullanır, yönetici URL'den alır
$personel_id = $kendi
    ? (int)$_SESSION['ilgili_id']
    : (int)($_GET['personel_id'] ?? 0);
```

Sorguda:
```php
if ($kendi || $personel_id) {
    $sql .= " AND i.personel_id = ?";
    $params[] = $personel_id;
}
```

Tabloda da işlem butonlarını rolüne göre göster/gizle:
```php
<?php if (!$kendi): ?>
    <!-- Onay/Red/Sil butonları sadece yöneticiye -->
<?php endif; ?>
```

**Ben:** Personel yeni izin talebi oluştururken personel dropdown'unu görmesin, otomatik kendisi seçili olsun.

**Claude:** Personel rolündeyse dropdown yerine hidden input kullanın:

```php
<?php if ($kendi): ?>
    <input type="hidden" name="personel_id" value="<?= $kendi_personel_id ?>">
<?php else: ?>
    <select name="personel_id">...</select>
<?php endif; ?>
```

Form gönderilince sunucu tarafında da doğrulayın — hidden input değeri kullanıcı tarafından değiştirilebilir:
```php
$personel_id = $kendi ? (int)$_SESSION['ilgili_id'] : (int)($_POST['personel_id'] ?? 0);
```

---

## Sohbet 6 — Etkinlik Modülü

**Ben:** Öğrencilerin etkinliklere kayıt olabilmesi için hangi tabloları oluşturmalıyım?

**Claude:** İki tablo yeterli:

```sql
CREATE TABLE etkinlikler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    baslik VARCHAR(200) NOT NULL,
    etkinlik_tarihi DATE NOT NULL,
    kapasite INT NOT NULL DEFAULT 50,
    konum VARCHAR(200),
    durum ENUM('aktif','iptal','tamamlandi') NOT NULL DEFAULT 'aktif'
);

CREATE TABLE etkinlik_katilim (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etkinlik_id INT NOT NULL,
    ogrenci_id INT NOT NULL,
    UNIQUE KEY uk_etkinlik_ogrenci (etkinlik_id, ogrenci_id), -- aynı kişi iki kez kayıt olamaz
    FOREIGN KEY (etkinlik_id) REFERENCES etkinlikler(id) ON DELETE CASCADE,
    FOREIGN KEY (ogrenci_id) REFERENCES ogrenciler(id) ON DELETE CASCADE
);
```

`UNIQUE KEY` ile aynı öğrenci aynı etkinliğe iki kez kayıt olamaz — uygulama katmanında kontrol yetersiz, veritabanı seviyesinde garanti altına alın.

**Ben:** Kapasite kontrolünü nasıl yaparım?

**Claude:** Kayıt sırasında mevcut katılımcı sayısını kontrol edin:

```php
$stmt = $db->prepare("SELECT COUNT(*) FROM etkinlik_katilim WHERE etkinlik_id = ?");
$stmt->execute([$etkinlik_id]);
$mevcut = $stmt->fetchColumn();

if ($mevcut >= $etkinlik['kapasite']) {
    // Kontenjan dolu, kayıt alma
}
```

Yarış koşulunu (race condition) önlemek için bu kontrolü ve INSERT'i tek transaction içinde yapabilirsiniz; düşük trafikli sistemlerde ayrı sorgu da yeterlidir.

---

## Sohbet 7 — CSRF Güvenliği

**Ben:** Sil butonlarım şu an `<a href="sil.php?id=5">` şeklinde. Birisi bana bu linki gönderip kaydı sildirebilir mi?

**Claude:** Evet, buna CSRF (Cross-Site Request Forgery) denir. Örneğin bir e-postadaki link veya başka sitedeki resim tag'i ile oturumunuz açıkken istemsiz silme yapılabilir.

Çözüm: silme işlemlerini POST'a çevirin ve her forma gizli token ekleyin.

```php
// functions.php
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_dogrula(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        die('Güvenlik hatası');
    }
}
```

Formda:
```html
<form method="POST" action="sil.php">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <input type="hidden" name="id" value="5">
    <button type="submit">Sil</button>
</form>
```

`sil.php`'de:
```php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: liste.php'); exit; }
csrf_dogrula();
$id = (int)$_POST['id'];
```

**Ben:** `hash_equals` neden kullandın, `===` yetmez mi?

**Claude:** `hash_equals` zamanlama saldırılarına (timing attack) karşı koruma sağlar. `===` operatörü karakterleri soldan sağa karşılaştırır ve ilk uyumsuzlukta durur — bu süre farkından token tahmin edilebilir. `hash_equals` her iki string eşit uzunluktaysa her zaman aynı sürede karşılaştırır.

---

## Sohbet 8 — İş Günü Hesaplama

**Ben:** İzin formunda başlangıç ve bitiş tarihinden hafta sonlarını çıkartarak iş günü sayısını hesaplatmak istiyorum.

**Claude:** PHP'nin `DatePeriod` sınıfıyla kolayca yapılır:

```php
function is_gunu_hesapla(string $baslangic, string $bitis): int {
    $start = new DateTime($baslangic);
    $end   = new DateTime($bitis);
    $end->modify('+1 day'); // bitiş günü dahil
    
    $period = new DatePeriod($start, new DateInterval('P1D'), $end);
    $gun = 0;
    foreach ($period as $tarih) {
        if ($tarih->format('N') < 6) { // 1=Pzt 5=Cum, 6=Cmt 7=Paz
            $gun++;
        }
    }
    return $gun;
}
```

**Ben:** Resmi tatilleri de çıkartmak istersem?

**Claude:** Bunun için tatil listesi tutmanız gerekir. En basit yol bir dizi:

```php
$tatiller = ['2026-01-01', '2026-04-23', '2026-05-01', '2026-05-19'];
// foreach içinde:
if ($tarih->format('N') < 6 && !in_array($tarih->format('Y-m-d'), $tatiller)) {
    $gun++;
}
```

Uzun vadeli proje için tatilleri veritabanında tutmak daha sürdürülebilir.

---

## Sohbet 9 — Arayüz: Rol Bazlı Navbar

**Ben:** Yönetici koyu navbar, personel mavi, öğrenci yeşil navbar görsün. Bunu nasıl yaparım?

**Claude:** `header.php`'de session'daki role göre renk sınıfını dinamik belirleyin:

```php
$rol = $_SESSION['rol'] ?? 'yonetici';

if ($rol === 'ogrenci') {
    $nav_renk = 'bg-success';
} elseif ($rol === 'personel') {
    $nav_renk = 'bg-primary';
} else {
    $nav_renk = 'bg-dark';
}
```

HTML'de:
```html
<nav class="navbar navbar-dark <?= $nav_renk ?>">
```

Bootstrap'ın `bg-dark`, `bg-primary`, `bg-success` sınıfları navbar için doğrudan çalışır.

**Ben:** Navbar'daki menü linkleri de role göre değişsin.

**Claude:**

```php
<?php if ($rol === 'yonetici'): ?>
    <li><a href="../ogrenci/liste.php">Öğrenciler</a></li>
    <li><a href="../personel/liste.php">Personel</a></li>
<?php elseif ($rol === 'personel'): ?>
    <li><a href="../personel/panel.php">Panelim</a></li>
    <li><a href="../personel/izin_ekle.php">İzin Talebi</a></li>
<?php elseif ($rol === 'ogrenci'): ?>
    <li><a href="../ogrenci/panel.php">Panelim</a></li>
    <li><a href="../etkinlik/liste.php">Etkinlikler</a></li>
<?php endif; ?>
```

---

## Sohbet 10 — Profil Sayfası Güvenliği

**Ben:** Profil sayfasında şifre değiştirirken mevcut şifreyi sormak istemiyordum ama bu güvenlik açığı mı?

**Claude:** Evet, önemli bir açık. Eğer oturumu açık bırakıp bilgisayar başından ayrılırsanız, başkası şifrenizi bilmeden değiştirebilir.

Çözüm basit — yeni şifre girilmek isteniyorsa önce mevcut şifreyi doğrulayın:

```php
if (!empty($_POST['yeni_sifre'])) {
    if (!password_verify($_POST['mevcut_sifre'], $kullanici['sifre'])) {
        $hatalar[] = "Mevcut şifreniz hatalı.";
    } elseif (strlen($_POST['yeni_sifre']) < 6) {
        $hatalar[] = "Yeni şifre en az 6 karakter olmalıdır.";
    }
}
```

Formda iki ayrı alan:
- **Mevcut Şifre** — doğrulama için
- **Yeni Şifre** — boş bırakılırsa şifre değişmez

**Ben:** Teşekkürler, hepsini uyguladım.

**Claude:** Rica ederim. Projeyi geliştirirken şunu da aklınızda tutun: `config/db.php` dosyasındaki gerçek veritabanı bilgilerini `.gitignore`'a ekleyin ve GitHub'a yüklemeyin. Bunun yerine `example_db.php` gibi şablon bir dosya bırakın.

---

## Sohbet 11 — Rol Sekmeli Giriş Sayfası

**Ben:** Giriş sayfasında üç sekme olsun: Yönetici, Personel, Öğrenci. Her sekmenin rengi farklı olsun ve yanlış sekmeyle giriş yapılamasın.

**Claude:** Giriş formuna gizli bir input ekleyin, sekmeler bunu JavaScript ile değiştirsin:

```html
<div class="d-flex border rounded overflow-hidden">
    <button type="button" class="sekme" data-rol="yonetici">Yönetici</button>
    <button type="button" class="sekme" data-rol="personel">Personel</button>
    <button type="button" class="sekme" data-rol="ogrenci">Öğrenci</button>
</div>
<input type="hidden" name="rol_secim" id="rolSecim" value="yonetici">
```

JavaScript ile aktif sekmeyi renklendirin:

```js
document.querySelectorAll('.sekme').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('rolSecim').value = this.dataset.rol;
        // aktif sınıfını güncelle, rengi değiştir
    });
});
```

PHP tarafında da gelen role ile veritabanındaki rolü karşılaştırın:

```php
if ($kullanici['rol'] !== $_POST['rol_secim']) {
    $hata = "Bu hesap " . $kullanici['rol'] . " rolüne sahip. Doğru sekmeyi seçin.";
}
```

**Ben:** Sekme seçilince header rengi de değişsin, buton yazısı da değişsin.

**Claude:** Sekme değişim fonksiyonuna DOM güncellemelerini ekleyin:

```js
function rolUygula(rol, bg, etiket) {
    document.getElementById('rolSecim').value = rol;
    document.getElementById('kartHeader').style.background = bg;
    document.getElementById('loginBtn').style.background = bg;
    document.getElementById('loginBtnYazi').textContent = etiket + ' Olarak Giriş Yap';
}
```

Böylece kullanıcı sekmeye tıkladığında sayfanın tüm rengi anında değişir — tek bir `data-bg` attribute'u ile her sekmenin rengini HTML'de tutun, JS dosyası küçük kalır.

---

## Sohbet 12 — Doğrudan Kayıt Akışı

**Ben:** Önceki sistemde öğrenci kayıt olmak için önce yöneticinin o öğrenciyi sisteme girmesi gerekiyordu, sonra okul no ile eşleşiyordu. Bu çok zahmetli. Nasıl değiştiririm?

**Claude:** Eski "bul ve eşleştir" akışı yerine "kayıt ol ve oluştur" akışına geçin. Kayıt sırasında ilgili tabloya direkt INSERT yapın:

```php
// Öğrenci kayıt olursa:
$ins = $db->prepare(
    "INSERT INTO ogrenciler (ad, soyad, okul_no, oda_no, email, durum)
     VALUES (?, ?, ?, ?, ?, 'aktif')"
);
$ins->execute([$ad, $soyad, $okul_no, $oda_no, $email]);
$ilgili_id = $db->lastInsertId();

// Sonra yoneticiler'e bağla:
$db->prepare(
    "INSERT INTO yoneticiler (ad_soyad, email, sifre, rol, ilgili_id) VALUES (?, ?, ?, 'ogrenci', ?)"
)->execute([$ad_soyad, $email, $kriptolu, $ilgili_id]);
```

**Ben:** İki INSERT başarısız olursa ne olur? Öğrenci oluşur ama hesap oluşmazsa?

**Claude:** `beginTransaction()` ile atomik yapın — ya her ikisi de başarılı olur ya da ikisi de geri alınır:

```php
$db->beginTransaction();
try {
    // INSERT ogrenciler...
    $ilgili_id = $db->lastInsertId();
    // INSERT yoneticiler...
    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    $hatalar[] = $e->getMessage();
}
```

Transaction olmadan yarım kayıt kalabilirdi: `ogrenciler` tablosunda kayıt var ama `yoneticiler`'de yok, ya da tam tersi. İkisi de aynı atomik işlem içinde yapılırsa bu sorun ortadan kalkar.

---

## Sohbet 13 — İki Tablolu Profil Güncelleme

**Ben:** Öğrenci profil sayfasında e-postayı değiştirince login de etkilenmeli. Ama login yoneticiler tablosunda, öğrenci bilgileri ogrenciler tablosunda. İkisini nasıl senkron tutarım?

**Claude:** Her iki tabloyu aynı anda güncelleyin:

```php
// ogrenciler tablosunda iletişim bilgilerini güncelle
$db->prepare("UPDATE ogrenciler SET telefon=?, email=?, adres=? WHERE id=?")
   ->execute([$telefon, $email, $adres, $ogrenci_id]);

// yoneticiler tablosunda login e-postasını senkronla
$db->prepare("UPDATE yoneticiler SET email=? WHERE id=?")
   ->execute([$email, $yonetici_id]);
```

Şifre değişikliğinde de aynı mantık — sadece `yoneticiler` tablosundaki hash'i güncelleyin, `ogrenciler`'de şifre sütunu zaten yok:

```php
if (!empty($yeni_sifre)) {
    $db->prepare("UPDATE yoneticiler SET email=?, sifre=? WHERE id=?")
       ->execute([$email, password_hash($yeni_sifre, PASSWORD_DEFAULT), $yonetici_id]);
}
```

**Ben:** E-posta benzersizliğini nasıl kontrol ederim? Başka biri aynı e-postayı kullanıyor olabilir.

**Claude:** Kontrol ederken kendi id'sini hariç tutun — yoksa mevcut e-postasını değiştirmeden kaydedince "bu e-posta kullanımda" hatası alır:

```php
$ck = $db->prepare("SELECT id FROM yoneticiler WHERE email = ? AND id != ?");
$ck->execute([$email, $yonetici_id]);
if ($ck->fetch()) {
    $hatalar[] = "Bu e-posta başka bir hesaba ait.";
}
```

`AND id != ?` olmadan her güncelleme kendi kaydını bulur ve hata verir.

---

## Sohbet 14 — Esnek Schema: Zorunlu Alanları Sonraya Bırakmak

**Ben:** Öğrenci kayıt olurken TC kimlik no sormak istemedim, yönetici sonradan eklesin. Ama şemada `tc_no NOT NULL UNIQUE` var. Bu çakışıyor.

**Claude:** Kolonu `NULL` yapılabilir hale getirin:

```sql
-- Eski:
tc_no CHAR(11) NOT NULL UNIQUE

-- Yeni:
tc_no CHAR(11) NULL UNIQUE
```

MySQL'de `UNIQUE` kolona birden fazla `NULL` değer girilebilir — her `NULL` birbirinden bağımsız kabul edilir. Bu sayede kayıt sırasında `tc_no` boş bırakılır, yönetici düzenleme sayfasından sonradan ekler.

**Ben:** Peki aynı durumu `bolum`, `gorevi`, `departman` için de uygulamak gerekiyor mu?

**Claude:** Evet. Öğrencinin bölümünü veya personelin görevini kayıt anında bilemiyorsa bunları da nullable yapın:

```sql
-- ogrenciler
bolum VARCHAR(100) NULL,

-- personel
gorevi    VARCHAR(100) NULL,
departman VARCHAR(100) NULL,
```

Yönetici `duzenle.php`'den bu alanları doldurur. PHP tarafında tabloda gösterirken null kontrolü yapın:

```php
<?= $ogrenci['bolum'] ?? '<span class="text-muted">Henüz eklenmedi</span>' ?>
```

Null-able alanları sonradan doldurmak; kayıt sürecini kullanıcı için kolaylaştırırken yöneticiye veri bütünlüğünü kontrol etme yetkisi verir.

---

## Sohbet 15 — Öğrenci İzin Sistemi (Evci / Dışarı İzni)

**Ben:** Personel için izin sistemi var ama öğrenciler için yok. Yurtta kalan öğrenciler de hafta sonu evlerine giderken (evci izni) izin alabilmeli, yönetici de onaylasın. Personeldeki yapıyı kopyalasam yeter mi?

**Claude:** Mantık aynı (talep → onay), ama öğrenci izni ayrı bir tablo olmalı çünkü `izinler` tablosu `personel_id`'ye bağlı. Yeni bir tablo açın:

```sql
CREATE TABLE ogrenci_izinleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ogrenci_id INT NOT NULL,
    izin_turu ENUM('evci','gunubirlik','saglik','diger') NOT NULL DEFAULT 'evci',
    baslangic_tarihi DATE NOT NULL,
    bitis_tarihi DATE NOT NULL,
    gun_sayisi INT NOT NULL,
    aciklama TEXT,
    durum ENUM('bekliyor','onaylandi','reddedildi') NOT NULL DEFAULT 'bekliyor',
    olusturuldu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ogrenci_izin
        FOREIGN KEY (ogrenci_id) REFERENCES ogrenciler(id) ON DELETE CASCADE
);
```

İzin türleri personelden farklı: personelde `yillik/mazeret/hastalik/ucretsiz`, öğrencide yurt bağlamına uygun `evci/gunubirlik/saglik/diger`.

**Ben:** Gün sayısını personeldeki `is_gunu_hesapla()` ile mi hesaplayayım?

**Claude:** Hayır — burada dikkat! Personelde hafta sonu **hariç** tutulur (mesai günü). Ama öğrencinin evci izni tam tersine **hafta sonunu kapsar**. Aynı fonksiyonu kullanırsanız 2 günlük hafta sonu izni "0 gün" çıkar. Hafta sonu **dahil** sayan ayrı bir fonksiyon yazın:

```php
// Takvim gün sayısı (hafta sonu DAHİL — öğrenci evci izni için)
function takvim_gun_hesapla(string $baslangic, string $bitis): int {
    $start = new DateTime($baslangic);
    $end   = new DateTime($bitis);
    return (int)$start->diff($end)->days + 1; // başlangıç günü de dahil
}
```

`diff()->days` iki tarih arasındaki farkı verir; `+1` ile başlangıç gününü de sayarız (13–15 arası = 3 gün).

**Ben:** Öğrenci kendi talebini oluştururken personeldeki gibi rolüne göre çalışsın istiyorum.

**Claude:** Personeldeki `$kendi` desenini birebir uygulayın — öğrenci rolündeyse kendi id'si gizli input, yöneticiyse dropdown. Ama **sunucu tarafında** mutlaka doğrulayın, hidden input değiştirilebilir:

```php
$kendi = ($rol === 'ogrenci');
$ogrenci_id = $kendi ? (int)$_SESSION['ilgili_id'] : (int)($_POST['ogrenci_id'] ?? 0);
```

İzin türünü de whitelist ile doğrulayın — kullanıcı ENUM dışı bir değer gönderirse INSERT hata verir:

```php
$gecerli_turler = ['evci', 'gunubirlik', 'saglik', 'diger'];
if (!in_array($izin_turu, $gecerli_turler)) $hatalar[] = 'Geçerli bir izin türü seçiniz.';
```

**Ben:** Onaylanınca öğrencinin durumunu 'izinli' yapayım mı, personeldeki gibi?

**Claude:** Yapamazsınız — `ogrenciler` tablosundaki `durum` ENUM'u sadece `aktif/pasif/mezun` içeriyor, `izinli` yok. Personelde `izinli` durumu var çünkü maaş/mesai takibi için anlamlı. Öğrenci izni daha çok bir "kayıt/onay" akışı; durumu değiştirmeden sadece izin tablosundaki `durum`'u güncelleyin yeterli. İsterseniz ENUM'a `izinli` ekleyebilirsiniz ama bu sefer liste/filtre/badge'leri de güncellemeniz gerekir.

---

## Sohbet 16 — Onay/Red İşlemlerini GET'ten POST'a Çevirmek

**Ben:** İzin onay/red butonlarım `<a href="izin_onayla.php?id=5&aksiyon=onayla">` şeklinde link. Silme işlemlerini POST yaptım ama onayı unutmuşum. Sorun olur mu?

**Claude:** Evet — onay/red de **durum değiştiren** bir işlem, tıpkı silme gibi. GET ile yapılması iki açık doğurur:

1. **CSRF:** Başka bir sitedeki `<img src="...izin_onayla.php?id=5&aksiyon=onayla">` tag'i, siz yöneticiyken sayfayı açtığınızda izni sessizce onaylatabilir.
2. **Önbellek/önyükleme:** Tarayıcı veya bir bot GET linklerini önceden çekebilir (prefetch) ve işlemi istemsiz tetikleyebilir.

Kural: **veriyi değiştiren hiçbir işlem GET ile yapılmamalı.** Onayı POST + CSRF token'a çevirin. `izin_onayla.php` başında:

```php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: izin_liste.php');
    exit;
}
csrf_dogrula();

$id      = (int)($_POST['id'] ?? 0);
$aksiyon = $_POST['aksiyon'] ?? '';
```

Listedeki linki de küçük bir POST formuna dönüştürün:

```php
<form method="POST" action="izin_onayla.php" class="d-inline"
      onsubmit="return confirm('İzni onaylamak istiyor musunuz?')">
    <?= csrf_input() ?>
    <input type="hidden" name="id" value="<?= $iz['id'] ?>">
    <input type="hidden" name="aksiyon" value="onayla">
    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i></button>
</form>
```

`class="d-inline"` ile form, eski `<a>` butonu gibi yan yana durur — görünüm bozulmaz.

**Ben:** Bir izin reddedilince personelin durumu 'izinli' kalıyordu, fark ettim. Onaylayınca 'izinli' yapıyorum ama reddedince geri almıyorum.

**Claude:** Klasik bir durum tutarsızlığı. Reddedince (veya iznini iptal edince) personelin başka **aktif/onaylı** izni kalmadıysa durumu `aktif`'e döndürün — ama körlemesine değil, önce kontrol edin:

```php
if ($aksiyon === 'reddet') {
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
```

`AND durum='izinli'` koşulu önemli — `pasif` yapılmış bir personeli yanlışlıkla `aktif`'e çevirmeyi önler. Yani: "sadece şu an izinli olan ve başka geçerli izni kalmayan personeli aktife al."

**Ben:** Aynı CSRF eksikliği etkinlik kayıt/iptalde de var mıydı?

**Claude:** Evet, `katil.php` ve `iptal.php` POST kontrolü yapıyordu ama token doğrulamıyordu. Formlarına `<?= csrf_input() ?>`, işleyici dosyalara `csrf_dogrula();` ekleyin. Genel ilke: **POST alan her dosyada `csrf_dogrula()`, durum değiştiren her formda `csrf_input()`** — ikisi her zaman çift gider.

---

## Sohbet 17 — Polymorphic İlişki ve Öksüz Giriş Hesabı Sorunu

**Ben:** `yoneticiler` tablosu diğer tablolardan kopuk görünüyor — `ilgili_id` var ama hiçbir `FOREIGN KEY` yok. Bu bir mantık hatası mı?

**Claude:** Hayır, kasıtlı bir desen — buna **polymorphic association** (çok-biçimli ilişki) denir. `ilgili_id`, `rol` sütununa göre farklı tabloyu işaret eder:

```
rol = 'yonetici'  → ilgili_id = NULL
rol = 'personel'  → ilgili_id → personel.id
rol = 'ogrenci'   → ilgili_id → ogrenciler.id
```

Bir kolon aynı anda iki farklı tabloya `FOREIGN KEY` olamayacağı için buraya gerçek FK koyamazsınız. Tek giriş tablosuyla üç rolü yönetmenin pratik yolu budur. Yani şema "kopuk" değil, sadece ilişki uygulama katmanında kuruluyor.

**Ben:** Peki bunun bir sakıncası var mı?

**Claude:** Evet, bir tane: **referans bütünlüğü (referential integrity) veritabanı tarafından garanti edilmez.** En somut sonucu silme işleminde görülür. FK + `CASCADE` olmadığı için, bir öğrenciyi silince `yoneticiler`'deki giriş hesabı otomatik silinmez:

```php
// ogrenci/sil.php — eski hali, SADECE öğrenciyi siler
$db->prepare("DELETE FROM ogrenciler WHERE id = ?")->execute([$id]);
```

Sonuç: `yoneticiler`'de `ilgili_id=5, rol='ogrenci'` satırı **öksüz** kalır. E-posta `UNIQUE` olduğu için o kişi tekrar kayıt da olamaz; hesap çöp olarak DB'de birikir.

**Ben:** Şemayı değiştirmeden nasıl çözerim?

**Claude:** Silme işleminde bağlı giriş hesabını **elle, aynı transaction içinde** silin. Transaction önemli — biri silinip diğeri kalırsa yine tutarsızlık olur:

```php
$db->beginTransaction();
try {
    // Önce bağlı giriş hesabı
    $db->prepare("DELETE FROM yoneticiler WHERE rol='ogrenci' AND ilgili_id = ?")
       ->execute([$id]);
    // Sonra asıl kayıt
    $db->prepare("DELETE FROM ogrenciler WHERE id = ?")->execute([$id]);
    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    mesaj_ayarla('Silme başarısız: ' . $e->getMessage(), 'danger');
}
```

`rol='ogrenci'` koşulu kritik: `ilgili_id` tek başına benzersiz değil (öğrenci 5 ile personel 5 ayrı kişiler). Rol filtresi olmadan yanlış hesabı silebilirsiniz.

**Ben:** Daha "doğru" bir tasarım olmaz mıydı?

**Claude:** Alternatif: `yoneticiler`'e ayrı `personel_id` ve `ogrenci_id` kolonları koyup ikisine de gerçek FK + `ON DELETE CASCADE` eklemek. O zaman veritabanı temizliği otomatik yapar. Ama bu, login/kayıt/header dahil `ilgili_id` kullanan tüm kodu değiştirmeyi gerektirir. Bu ölçekteki bir projede polymorphic desen + uygulama katmanında temizlik yeterli ve risksizdir. Önemli olan: **bilinçli tercih yapmak** — "FK koyamıyorum, o halde silmeyi elle ve transaction ile yöneteceğim" demek.
