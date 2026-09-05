# Warext Studios | XenForo S.S.S. Sistemi

XenForo 2.3+ için, varsayılan tema ile uyumlu kategori tabanlı Sıkça Sorulan Sorular eklentisi.

## Özellikler
- Kategori -> soru -> tıklayınca altına açılan cevap düzeni
- XenForo varsayılan tema bileşenleri
- ACP üzerinden kategori ve SSS yönetimi
- BBCode/XenForo editörü destekli cevaplar
- Aktif/pasif ve görüntüleme sırası
- Öne çıkan SSS işareti
- Doğrudan soru bağlantıları (`/sss/#...`)
- SSS listesi içinde anlık filtreleme
- Tümünü aç / tümünü kapat
- SSS görüntüleme izni
- Ayrı ACP yönetici izni

## Geliştirme durumu

### v1.0.0 / Adım 1 - Tamamlandı
- XenForo add-on iskeleti
- `xf_wrxt_sss_category` ve `xf_wrxt_sss_faq` tabloları
- Category / Faq entity ve repository yapıları
- `/sss/` public route
- İlk çalışan default tema görünümü
- BBCode render altyapısı

### v1.0.1 / ACP yönetimi - Tamamlandı
- SSS kategori ekleme, düzenleme ve silme
- SSS soru/cevap ekleme, düzenleme ve silme
- XenForo editörü
- Kategori filtresi
- Aktif/pasif ve öne çıkarma alanları
- Görüntüleme sırası
- Doğrudan bağlantı anahtarı doğrulaması
- ACP `İçerik` menüsü entegrasyonu
- Yönetici izin sistemi
- Frontend hızlı filtreleme ve tümünü aç/kapat

## Kurulum
Nihai sürüm tamamlandığında GitHub Releases bölümündeki kurulum ZIP dosyası XenForo ACP > Add-ons > Install/upgrade from archive alanından doğrudan yüklenebilir olacaktır.
