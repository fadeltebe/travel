# Product Requirements Document (PRD)
**Produk**: Sistem Manajemen Travel & Kargo Modern  
**Dokumen**: Dokumen Panduan Komprehensif  

---

## 1. Menyelaraskan Tim (Alignment)
Dokumen ini berfungsi sebagai acuan utama yang menyatukan pemahaman antara tim bisnis (Owner/Stakeholder) dan tim teknis (Engineering & Design). 
**Visi Produk**: Menghadirkan ekosistem manajemen transportasi dan pengiriman barang yang 100% *mobile-first*, menutup rapat celah kebocoran dana operasional perusahaan, dan mendigitalisasi setiap kertas cetak (tiket & resi) ke dalam sistem serba otomatis.

---

## 2. Tujuan & Konteks (Objective & Context)
### Konteks Masalah
Sebelum sistem ini dibuat, bisnis travel/kargo menghadapi tantangan klasik:
- Sistem setoran agen (penjualan tiket) sering kali tersamar menjadi piutang yang macet.
- Agen di lapangan masih mencatat form manifes, tiket, dan tagihan COD secara manual atau menggunakan alat kasir yang tidak saling terhubung ke pusat.
- Pemilik kesulitan melacak dan mendapat visibilitas uang masuk secara harian/real-time karena rekonsiliasi data lambat.

### Tujuan Bisnis (Business Goals)
- **Otomatisasi Pembayaran**: Mencegah piutang ke agen dengan model pra-bayar (top-up) ke dalam "Dompet Digital Agen". Saldo terpotong langsung saat tiket rilis.
- **Digitalisasi Operasional**: Mampu mencetak tiket/kargo lewat gawai (HP) dan printer *Thermal Bluetooth*, atau sekadar diteruskan via WhatsApp.
- **Pusat Kendali Jelas**: Setiap peran (Role: SuperAdmin, Owner, Admin, Agen, Sopir) memiliki batas dan tampilan aplikasi tersendiri sesuai tanggung jawab masing-masing.

---

## 3. Fitur Utama & User Stories
Berikut adalah penjabaran kemampuan fungsional produk beserta skenario penggunanya.

### A. Ekosistem Dompet Prabayar (Multi-Agent Wallet)
- **Sebagai** Pemilik (Owner), **Saya ingin** sistem secara otomatis mendeteksi dan memblokir agen yang tidak memiliki cukup saldo dari menerbitkan tiket **sehingga** mencegah agen berutang pada perusahaan.
- **Sebagai** Agen, **Saya ingin** bisa melakukan top-up token secara instan (Virtual Account/QRIS 24 jam) melalui Midtrans **sehingga** saya tidak perlu menunggu jam kerja Admin Pusat untuk verifikasi mutasi bank.

### B. Modul Penumpang & Pemesanan Terintegrasi
- **Sebagai** Agen Kasir, **Saya ingin** dapat mengklik *layout* bangku bus secara visual **sehingga** meminimalisir kesalahan penetapan nomor kursi kepada penumpang.
- **Sebagai** Agen Kasir, **Saya ingin** form pemesanan secara otomatis menampilkan harga tiket berdasarkan Rute (Titik Asal -> Tujuan) yang saya pilih **sehingga** saya tidak perlu menghafal harga tiket.

### C. Modul Ekspedisi Kargo & COD Terpadu
- **Sebagai** Admin Kargo, **Saya ingin** menginput resi ekspedisi berdasarkan satuan berat (KG) atau Koli **sehingga** total tarif otomatis terkalkulasi.
- **Sebagai** Supir/Agen Tujuan, **Saya ingin** sistem mengelompokkan paket dengan tipe pembayaran COD secara khusus **sehingga** saya tahu mana barang yang harus ditarik bayarannya terlebih dahulu dari penerima paket sebelum diserahkan.

### D. Utilitas Cetak Termal & Integrasi Obrolan
- **Sebagai** Agen Lapangan, **Saya ingin** menekan satu tombol "Cetak RawBT" **sehingga** tiket penumpang atau resi barang langsung ter-print dari *bluetooth thermal printer* berukuran 58mm yang saya bawa.
- **Sebagai** Penumpang, **Saya ingin** menerima bukti E-Tiket saya rapi di WhatsApp tanpa harus agen mengambil kertas fisik (Share via *Web Share* API).

### E. Manajemen Dasbor & Peran
- **Sebagai** Owner, **Saya ingin** dasbor ringkas yang menunjukkan laporan omzet penjualan uang tiket maupun uang kiriman barang di setiap akhir bulan **sehingga** evaluasi bisnis bisa dilakukan sekilas.
- **Sebagai** Supir, **Saya ingin** membuka HP dan melihat dokumen pelaporan manifes tugas (daftar absensi penumpang untuk hari keberangkatan ini) **sehingga** saya tidak butuh print out kertas dari kantor pusat.

---

## 4. Kriteria Keberhasilan (Success Criteria)

Untuk mengetahui apakah solusi *software* ini berhasil diciptakan dan diterima dengan baik, kita menggunakan beberapa tolok ukur (metrik) berikut:

| Metrik | Target Keberhasilan |
| :--- | :--- |
| **Kolektibilitas Piutang Agen** | Turun hingga **0%** karena sistem Top-Up Token Otomatis memblokir kasbon. |
| **Waktu Pelayanan Transaksi** | Waktu mulai proses pesan tiket hingga E-Tiket terkirim ke WhatsApp pelanggan **<= 90 Detik**. |
| **Adopsi Sopir & Agen** | **95%** Agen Lapangan dan Sopir mampu operasional secara mandiri pakai *smartphone* (UI/UX berhasil memandu mereka). |
| **Downtime Operasional Pembayaran** | Proses integrasi *Midtrans* berjalan dengan sukses (*Success Rate* `webhook`) nyaris **100%**, tanpa adanya uang tertahan. |

---
*Dokumen PRD ini adalah "Living Document" yang akan terus disesuaikan setiap kali terdapat penyesuaian iterasi (*sprint*) pengembangan lebih lanjut berdasarkan penambahan ide baru maupun temuan teknis aplikasi.*
