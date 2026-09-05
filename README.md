# Warext Studios | XenForo S.S.S. Sistemi

XenForo 2.3+ için, varsayılan tema ile uyumlu kategori tabanlı Sıkça Sorulan Sorular eklentisi.

## Hedef yapı
- Kategori -> soru -> açılır cevap düzeni
- XenForo varsayılan tema bileşenleri
- ACP üzerinden kategori ve SSS yönetimi
- BBCode destekli cevaplar
- Sıralama, görünürlük ve izin sistemi
- Doğrudan soru bağlantıları
- Basit liste filtreleme
- Widget ve navigasyon entegrasyonu

## Geliştirme durumu

### v1.0.0 / Adım 1 - Tamamlandı
- XenForo add-on iskeleti
- `xf_wrxt_sss_category` ve `xf_wrxt_sss_faq` tabloları
- Category / Faq entity yapıları
- Category / Faq repository yapıları
- `/sss/` public route
- SSS görüntüleme izni
- XenForo default tema tabanlı ilk çalışan SSS görünümü
- BBCode cevap render altyapısı

### Sonraki adım
ACP üzerinden kategori ve soru/cevap CRUD yönetimi, sıralama ve editör entegrasyonu.
