<?php
// Ortak yardımcı fonksiyonlar

// Flash (Geçici) mesaj ayarla
function mesaj_ayarla(string $mesaj, string $tur = 'success'): void {
    $_SESSION['mesaj']     = $mesaj;
    $_SESSION['mesaj_tur'] = $tur; // success | danger | warning | info
}

// Güvenli POST verisi al (XSS Koruması)
function post(string $key, string $default = ''): string {
    return htmlspecialchars(trim($_POST[$key] ?? $default), ENT_QUOTES, 'UTF-8');
}

// Güvenli GET verisi al
function get(string $key, $default = ''): string {
    return htmlspecialchars(trim($_GET[$key] ?? $default), ENT_QUOTES, 'UTF-8');
}

// TC Kimlik No doğrulama (basit 11 hane kontrolü)
function tc_gecerli(string $tc): bool {
    return preg_match('/^\d{11}$/', $tc) === 1;
}

// Para birimini Türk Lirası formatına çevir
function para_format(float $miktar): string {
    return number_format($miktar, 2, ',', '.') . ' ₺';
}

// İzin gün sayısı hesapla (hafta sonu hariç)
function is_gunu_hesapla(string $baslangic, string $bitis): int {
    $start = new DateTime($baslangic);
    $end   = new DateTime($bitis);
    $end->modify('+1 day');
    $interval = new DateInterval('P1D');
    $period   = new DatePeriod($start, $interval, $end);
    $gunler   = 0;
    foreach ($period as $gun) {
        if ($gun->format('N') < 6) $gunler++; // 1=Pzt ... 5=Cum
    }
    return $gunler;
}

// Tarihi gün.ay.yıl formatına çevir
function tarih_format(string $tarih): string {
    if (!$tarih || $tarih === '0000-00-00') return '-';
    return date('d.m.Y', strtotime($tarih));
}

// CSRF token üret (session'da saklar, yoksa oluşturur)
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF token doğrula — başarısızsa yönlendirir
function csrf_dogrula(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        mesaj_ayarla('Güvenlik doğrulaması başarısız. Lütfen tekrar deneyin.', 'danger');
        $geri = $_SERVER['HTTP_REFERER'] ?? '../index.php';
        header('Location: ' . $geri);
        exit;
    }
}

// Forma göm (hidden input olarak)
function csrf_input(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

// Rol tabanlı erişim kontrolü — yetkisizse yönlendirir
function rol_kontrol(string ...$izinli_roller): void {
    if (!isset($_SESSION['yonetici_id'])) {
        header('Location: ../login.php');
        exit;
    }
    $rol = $_SESSION['rol'] ?? 'yonetici';
    if (!in_array($rol, $izinli_roller)) {
        // Yönlendirme: role göre doğru panele gönder
        if ($rol === 'ogrenci') {
            header('Location: ../ogrenci/panel.php');
        } elseif ($rol === 'personel') {
            header('Location: ../personel/panel.php');
        } else {
            header('Location: ../index.php');
        }
        exit;
    }
}

// Mevcut kullanıcının rolü verilen roller arasında mı?
function yetkili_mi(string ...$roller): bool {
    $rol = $_SESSION['rol'] ?? '';
    return in_array($rol, $roller);
}

// Tablolardaki durumlar için Bootstrap renkli etiket (Badge) üret
function durum_badge(string $durum): string {
    $renkler = [
        'aktif'      => 'success',
        'pasif'      => 'secondary',
        'izinli'     => 'warning',
        'mezun'      => 'primary',
        'bekliyor'   => 'warning',
        'onaylandi'  => 'success',
        'reddedildi' => 'danger',
        'odendi'     => 'success',
    ];
    $renk = $renkler[$durum] ?? 'secondary';
    // İlk harfi büyütüp Bootstrap badge içine alıyoruz
    $guzel_durum = mb_strtoupper(mb_substr($durum, 0, 1)) . mb_substr($durum, 1);
    return "<span class=\"badge bg-{$renk}\">" . htmlspecialchars($guzel_durum) . "</span>";
}
?>