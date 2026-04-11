# 🚀 Executive Summary: Sistem Manajemen Travel & Kargo Digital

Dokumen ini disusun sebagai gambaran menyeluruh (Overview) mengenai platform yang sedang kita kembangkan. Sistem ini dirancang bukan sekadar sebagai alat pencatatan (pembukuan), melainkan sebagai **mesin penggerak operasional bisnis travel dan ekspedisi** yang modern, serba otomatis, dan sangat berfokus pada kemudahan penggunaan (*user-friendly*) baik melalui komputer maupun *smartphone*.

---

## 🎯 Nilai Jual Utama (Value Proposition)

Bagi pemilik perusahaan (*Owner*), tantangan terbesar dalam bisnis travel biasanya berpusat pada **kebocoran dana agen, kesulitan memantau armada secara real-time, dan sistem pencatatan resi/tiket yang masih manual (kertas)**. Sistem kita memecahkan masalah tersebut dengan cara:

1. **Sistem Token & Dompet Prabayar (Anti-Piutang Agen)**
   Setiap agen diwajibkan memiliki saldo token (Deposit) sebelum bisa mencetak tiket atau merilis resi kargo. Saldo akan dipotong *secara otomatis* detik itu juga ketika tiket dikonfirmasi. Ini mengeliminasi risiko uang tiket tertahan/dibawa lari oleh agen lapangan.
2. **"Mobile-First" & Ramah Agen Penjualan**
   Agen tidak perlu membawa laptop. Mulai dari pendaftaran penumpang, melihat posisi bangku kosong (*Seat Selection*), hingga menghubungkan HP ke Printer Thermal Bluetooth dan nge-*share* tiket ke WhatsApp pelanggan, semuanya bisa dilakukan 100% mulus lewat antarmuka HP.
3. **Sentralisasi Laporan Keuangan & Operasional**
   *Owner* cukup membuka satu halaman Dashboard untuk melihat ringkasan omzet harian, performa rute paling ramai, hingga status setoran cabang.
4. **Terintegrasi secara Profesional dengan Payment Gateway (Midtrans)**
   Kewajiban agen untuk *Top-up* saldo token dilakukan layaknya belanja online. Agen bisa transfer via Virtual Account / QRIS yang otomatis diverifikasi sistem dalam hitungan detik tanpa campur tangan Admin Pusat (tersedia 24/7).

---

## 📦 Modul & Fitur Unggulan

### 🚌 1. Manajemen Penumpang & Perjalanan Terpadu
- **Pemesanan Super Cepat (4-Langkah)**: Memilih Jadwal -> Entri Data Pemesan -> Entri Penumpang -> Pilih Kursi Visual & Bayar.
- **Harga Dinamis**: Harga tiket disesuaikan otomatis dari rute perjalanan (Titik A ke Titik B).
- **Seat Layout Interaktif**: Agen maupun admin dapat melihat skema posisi kursi bus/hiace secara visual.

### 📦 2. Ekspedisi Kargo & Alur COD Cerdas
- Form pencatatan resi barang yang ringkas dengan penghitungan tarif berbasis berat (KG) atau dimensi koli.
- **Fitur Khusus COD (Cash on Delivery)**: Alur penerimaan kargo dan penagihan biaya ekspedisi di tempat tujuan yang diawasi dengan ketat oleh sistem.
- **Generasi QR Code Pelacakan (Tracking):** Setiap kargo memiliki resi pintar berupa kode QR yang kelak dapat di-*scan* untuk mengetahui lokasi barang.

### 💳 3. Ekosistem Dompet Digital (Multi-Agent Wallet)
- Dasbor dompet yang secara *real-time* menampilkan sisa saldo token.
- Histori Mutasi Keuangan (Keluar-Masuk) saldo untuk setiap agen dicatat rinci bak buku tabungan bank tingkat keamanan tinggi (*Database Transaction Lock*).
- Status pembayaran kargo/penumpang saling menempel kuat dengan saldo token agen, menutup peluang *human-error*.

### 📱 4. Utilitas Lapangan yang Kuat (Cetak & Bagikan)
- E-Tiket dan Resi Kargo dikalibrasi sempurna menyesuaikan kertas Printer Kasir/Thermal Bluetooth standar yang dibawa agen (ukuran 58mm).
- Di satu sentuhan *"Share to WA"*, gambar setruk bersih dapat diteruskankan secara virtual ke nomor penumpang.

---

## 🔒 Teknologi & Keamanan

- **Framework Enterprise (Laravel 11+ & Livewire Volt)**: Standar *coding* masa kini yang meminimalisir waktu *loading*, di mana aplikasi berjalan selayaknya peramban Web modern tanpa perlu di-*refresh* ("*Single Page Application Feel*").
- **Akses Berbasis Peran (Granular Roles)**: Pemilik (Owner) dapat melihat segalanya (Omzet dsb), sementara Sopir (Driver) hanya bisa melihat manifes tugasnya hari ini, dan Agen (Agent) hanya bisa melihat penjualan titik lokasinya.
- **Desain UI/UX Bintang 5**: UI dikemas profesional ala *fintech launcher/banking app* dengan *vibrant colors* dan navigasi bawah gawai yang tidak melelahkan mata bila dipakai seharian penuh.

---

## 📈 Kesimpulan untuk Pemilik Perusahaan

Aplikasi ini tidak dibangun sebagai perangkat lunak sekilas lewat. Ini adalah **Software as a Service (SaaS)** internal perusahaan yang **menyelamatkan uang operasional dan menutup celah "nakal" agen lapangan** lewat otomatisasi Dompet Prabayar, di sisi lain mempercepat pelayanan penumpang. Harapannya, sistem ini akan membuat perusahaan berskala daerah tampak memiliki fasilitas canggih setara dengan armada transportasi antarprovinsi nasional.
