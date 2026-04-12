# 📋 Panduan Sistem & Rincian Fitur Aplikasi Manajemen Travel

Dokumen ini berisi penjelasan komprehensif mengenai batasan Hak Akses (Role), penjelasan mendetail fitur-fitur yang sudah berjalan, serta Rencana Pengembangan (Roadmap) aplikasi untuk masa depan. Dokumen ini bertujuan untuk menjadi panduan bagi *User*, *Stakeholder*, atau panduan internal (*User Guide*).

---

## 👥 1. Penjelasan Hak Akses (Role) & Wewenang

Aplikasi ini menggunakan sistem akses berbasis peran *(Role-Based Access Control / RBAC)*. Data yang dilihat oleh satu pengguna akan berbeda dengan pengguna lainnya tergantung posisinya.

### 👑 A. Role: Owner / Superadmin (Pemilik & Pusat)
**Fokus Utama:** Mengendalikan tata kelola perusahaan, manajemen keuangan, pemantauan performa harian secara global (*bird-eye view*), dan kontrol *Master Data*.
**Hak Akses Fitur:**
- **Dashboard Global (Monitoring):** Melihat ringkasan performa seluruh perusahaan (Total Omzet gabungan, total penumpang seluruh rute, total kargo semua agen).
- **Control Panel Pengguna:** Mendaftarkan akun Agen baru, mengatur *password*, membuat akun Supir, serta menentukan besaran batas atas komisi jika ada.
- **Master Data (Rute & Jadwal):** Membuka rute perjalanan baru, menentukan jadwal jam keberangkatan, dan menugaskan bus/armada beserta supirnya.
- **Konfigurasi Denah Bus (Layout Editor):** Bebas mendesain dan mengubah posisi kursi untuk berbagai tipe armada (Hiace 14-seat, Bus Medium, Bus VIP, dsb).
- **Keuangan & Dompet Pusat (Wallet Center):** Menyetujui formulir *Top-Up* dompet milik agen, melihat *cash-flow* token/saldo dari sistem secara menyeluruh, serta menyesuaikan tarif atau harga sistem (Settings).
- **Semua Laporan & Rekapitulasi:** Mampu menarik data operasional dan rekap mutasi harian secara global.

### 🏪 B. Role: Agen (Petugas Loket / Cabang)
**Fokus Utama:** Melayani transaksi harian penumpang, mengkoordinir pengiriman barang kargo, serta mencetak e-Tiket & Resi.
**Hak Akses Fitur:**
- **Dashboard Individu:** Hanya melihat grafik omzet, statistik penumpang, dan lalu-lintas kargo hasil pekerjaan *dirinya* sendiri (terisolasi dari data agen lain demi kerahasiaan operasional).
- **Pemesanan Tiket (Booking):** Mencari jadwal yang tersedia, mengisi biodata penumpang, memilih kursi interaktif yang masih kosong (berubah *real-time* jika ada yang booking), dan menerbitkan tiket.
- **Pengiriman Kargo:** Membuat kiriman barang (menghitung berat/kg). Agen asal (Origin) dapat menandai barang sebagai "Lunas" (bayar di tempat asal) atau "Sisa / Bayar di Tujuan". Agen tujuan (Destination) dapat menyelesaikan pembayaran jika ditagih ke penerima.
- **Dompet Agen Pribadi (Wallet):** Mengirim tagihan (Request Top-up) jika koin/saldo mereka habis, melihat sisa koin mereka sendiri di layar utama, dan melihat buku tabungan (Log Transaksi) terkait pemotongan saldo per tiket yang diisukan.
- **Cetak & Bagikan:** Mencetak resi khusus format *Thermal Printer* Bluetooth, atau langsung mengirim soft-copy tiket ke WhatsApp pelanggan menggunakan satu tombol.

### 🚌 C. Role: Driver (Supir)
**Fokus Utama:** Mengetahui jadwal keberangkatan secara aktual dan daftar manifes tugas, tanpa perlu mengurus fungsi administratif/pembukuan.
**Hak Akses Fitur:**
- **Jadwal Terbatas:** Hanya bisa melihat halaman Jadwal (Schedule) yang mana nama mereka secara spesifik ditugaskan ke mobil keberangkatan tersebut oleh pusat.
- **Manifes Penumpang Spesifik:** Melihat daftar nama dan urutan kursi penumpang *hanya* pada keberangkatan yang dibawanya.
- **Manifes Kargo Spesifik:** Melihat daftar barang, catatan titik kumpul, dan lokasi pengantaran untuk muatan kargo di mobilnya.
- **Batasan Ketat (Restriksi):** Supir *tidak bisa* menambah penumpang resmi, tidak memegang akses dompet, dan tidak melihat area konfigurasi web sama sekali.

---

## 💻 2. Penjelasan Fitur Unggulan Sistem (Saat Ini)

Berikut adalah ringkasan arsitektur fungsional atau modul unggulan yang sudah berjalan kokoh pada aplikasi hingga titik pengerjaan saat ini.

### 💸 A. Smart Auto-Billing (Sistem Token Dompet)
Sebuah sistem terintegrasi (Livewire `TokenService`) di mana agen diharuskan punya saldo ("Deposit"). Saat agen menyimpan data pesanan (baik tiket maupun kargo), sistem *bekerja di latar belakang* langsung memotong token agen (sebesar Rp.1.000 atau nominal sesuai harga rute). Fitur ini memiliki pengaman basis data agar saldo tidak minus jika koneksi gagal *(Database Transactions Logging)*.

### 🎟️ B. Interactive Seat Mapper
Antarmuka pendaftaran penumpang tidak hanya teks biasa. Agen disuguhi tampilan visual bangku dalam kendaraan. Jika bangku sudah dibeli oleh agen pusat di saat bersamaan, agen cabang secara *real-time* tidak bisa memblokir/mengklik kursi yang sama.

### 🛡️ C. Double-Submit Protector (Sistem Keamanan Form)
Menghindari "kebocoran" kerugian yang tidak disengaja. Di berbagai area krusial seperti 'Simpan Kargo' atau 'Checkout Transaksi', sistem mendisabel (mematikan) tombol dan menampilkan animasi *loading spinner* ketika agen terklik 2-kali, sehingga koin dompet hanya ditarik tepat 1-kali.

### 📱 D. UX Modern & Notifikasi Sistem Lebur (Toast)
Aplikasi membuang pop-up kaku bawaan *browser*. Semua aksi (berhasil buat agen, gagal top-up, kursi berhasil disetel) akan direspon oleh jendela "Notifikasi Toast" melayang modern di pojk atas (*Toast Notification Component*), memberi efek psikologis profesional bagi pengguna.

---

## 🚀 3. Rencana Pengembangan ke Depan (Roadmap)

Sistem sudah kokoh untuk fondasi manajemen dan pencegahan manipulasi kas lapangan. Berikut adalah daftar (*wishlist* dan peta jalan) fitur-fitur yang bisa kita kembangkan di fase selanjutnya:

### 🌟 Fase Terdekat (Optimasi & Utilitas Tambahan)
- [ ] **Export Data Kasir (Excel / PDF):** Fitur untuk mengunduh laporan keuangan harian, manifest penumpang, dan daftar log kargo bagi Agen dan Owner dalam format `.xls` atau `.pdf`, memudahkan tutup buku.
- [ ] **Pencetakan Manifes Fisik Supir:** Tombol sekali klik bagi agen terminal untuk mencetak "Kertas Tugas Manifes" kepada supir sebelum bus berjalan.
- [ ] **WhatsApp Notification Gateway Otomatis:** Saat Agen mengklik "Simpan", sistem langsung menghubungi API pihak ketiga *(seperti Fonnte / Watzap)* untuk mengirim tiket PDF mendarat di ponsel pelanggan tanpa agen perlu membuka WhatsApp Web.

### 🌟 Fase Menengah (Ekspansi Bisnis)
- [ ] **Aplikasi B2C (Portal Pelanggan Independen):** Website terpisah/Landing Page resmi di mana pelanggan (Orang awam) bisa mendaftar akun sendiri, mencari jadwal bus, dan membayar pesanan langsung via gerbang pembayaran *(Midtrans/Tripay QRIS)* ke rekening pusat, tanpa perantara agen.
- [ ] **Modul Absensi / Presensi Pegawai:** Mengizinkan staf cabang dan driver *Clock-in / Clock-out* (absen) di sistem secara digital berbasis Lokasi GPS.
- [ ] **Program Promosi (Kode Diskon / Voucher):** Logika kalkulator harga untuk memotong biaya tiket pelanggan apabila menggunakan diskon "*Event Lebaran*" yang diiklankan tim marketing.

### 🌟 Fase Jangka Panjang (Enterprise Scale)
- [ ] **Sistem Operasi Armada Berbasis GPS (Fleet Management & Maintenance):** Pencatatan riwayat bengkel mobil, usia siklus penggantian ban/oli armada, pengingat pajak STNK, hingga integrasi API G-Map guna mendapat ping lokasi saat kendaraan di lintas provinsi.
- [ ] **Sistem Penggajian Elektronik (Payroll & Kalkulasi Komisi Supir):** Otomatisasi uang jalan (kas bon bensin, uang makan) dengan komisi berbasis jumlah ritase keberangkatan supir untuk diakumulasi menjadi *slip* gaji digital di tangal tua.
