# 🗺️ Blueprint Proyek Travel Management

Dokumen ini adalah "Peta Pemandu" Anda. Jika Anda ingin memperbaiki, menambahkan fitur, atau melacak bug (error) secara mandiri, gunakan dokumen ini untuk memahami struktur dan alur aplikasi.

---

## 🛠️ 1. Teknologi yang Digunakan (Tech Stack)

Aplikasi ini dibangun menggunakan arsitektur modern Laravel:
*   **Backend:** Laravel (PHP)
*   **Frontend/UI:** Livewire **Volt** (PHP + Blade dalam satu file) & Tailwind CSS
*   **Database:** MySQL (dengan sistem Multi-Tenant menggunakan package `stancl/tenancy`)
*   **Pembayaran:** Midtrans (Snap & Webhook)

> [!IMPORTANT]
> **Apa itu Livewire Volt?**
> Berbeda dengan Livewire tradisional yang memisahkan file `Controller.php` dan `View.blade.php`, aplikasi ini menggunakan **Volt**. Artinya, logika PHP (fungsi save, ambil data) dan tampilan HTML (tombol, tabel) berada di dalam **SATU FILE YANG SAMA** (biasanya di dalam folder `resources/views/livewire/`).

---

## 🏢 2. Arsitektur Multi-Tenant (Banyak Perusahaan)

Aplikasi ini dirancang untuk menampung banyak perusahaan travel dalam satu sistem. 

*   **Aplikasi Pusat (Central App):** Mengatur pendaftaran travel baru, langganan (subscription), dan melihat laporan global.
    *   **Routing:** `routes/web.php`
    *   **Database:** Tabel utama (users pusat, tenants, domains).
*   **Aplikasi Cabang/Travel (Tenant App):** Aplikasi khusus untuk masing-masing perusahaan travel (mengatur bus, jadwal, tiket, rute, dll).
    *   **Routing:** `routes/tenant.php`
    *   **Database:** Setiap tenant punya tabel sendiri secara terpisah.

---

## 📂 3. Di Mana Saya Harus Mencari Kode?

Jika Anda ingin mengubah sesuatu, berikut adalah "Peta Direktori"-nya:

| Apa yang ingin Anda ubah? | Lokasi Folder/File |
| :--- | :--- |
| **URL / Alamat Web** | `routes/web.php` (Pusat) atau `routes/tenant.php` (Tenant/Travel) |
| **Tampilan & Logika Halaman** | `resources/views/livewire/...` (Misal: klik tombol simpan) |
| **Struktur Database (Tabel)** | `app/Models/` (Untuk query) & `database/migrations/` (Untuk kolom tabel) |
| **Pesan Error System** | `storage/logs/laravel.log` |

---

## 🧩 4. Daftar Fitur & Komponen (Livewire Volt)

Semua halaman antarmuka yang digunakan oleh admin travel berada di dalam folder `resources/views/livewire/`. Berikut adalah pembagian modulnya:

*   📁 **`settings/`** : Pengaturan dasar travel (Master Data).
    *   Bus, Layout Kursi (Bus Layout), Rute (Route).
*   📁 **`agents/`** : Manajemen Agen.
    *   Pendaftaran agen, komisi agen, dan laporan performa agen.
*   📁 **`schedules/`** : Manajemen Jadwal Keberangkatan.
    *   Membuat jadwal bus, menentukan harga, dan status keberangkatan.
*   📁 **`bookings/`** : Pemesanan Tiket Penumpang.
    *   Proses pemilihan kursi, input data penumpang, dan checkout.
*   📁 **`cargos/`** : Pemesanan Paket/Kargo.
    *   Pengiriman barang (resi, berat barang, rute pengiriman).
*   📁 **`wallets/`** : Sistem Saldo (Top-up).
    *   Riwayat saldo agen, top-up saldo via Midtrans.
*   📁 **`reports/`** : Laporan Keuangan.
    *   Total omset harian/bulanan dari penumpang maupun kargo.
*   📁 **`central/`** : Khusus untuk Super Admin (Pemilik Aplikasi).
    *   Manajemen tenant, statistik global, manajemen langganan.

---

## 🗄️ 5. Daftar Tabel / Model Database Utama

Berada di folder `app/Models/`. Ini adalah "Otak Data" Anda:

*   **`Bus` & `BusLayout`**: Menyimpan data armada dan formasi kursi.
*   **`Route`**: Rute perjalanan (Misal: Jakarta -> Bandung).
*   **`Agent`**: Data agen yang menjual tiket.
*   **`Schedule`**: Jadwal perjalanan (Kombinasi antara Bus + Rute + Tanggal/Jam).
*   **`Booking` & `Passenger`**: Data transaksi tiket penumpang dan kursi yang di-booking.
*   **`Cargo`**: Data pengiriman barang titipan.
*   **`Wallet` & `WalletTransaction`**: Mencatat keluar-masuk uang/saldo agen.

---

## 🔄 6. Alur Kerja (Workflow) Pemesanan Tiket

Jika Anda bingung bagaimana tiket bisa dipesan, ini alurnya:
1. Admin membuat **Bus** dan **Rute** di halaman `Settings`.
2. Admin membuat **Jadwal (Schedule)**. *Sistem akan otomatis membuat kursi kosong sesuai layout Bus.*
3. Agen masuk ke halaman **Booking**, memilih Jadwal.
4. Agen memilih **Kursi** yang masih kosong.
5. Agen mengisi data **Penumpang**.
6. Agen mengklik **Checkout** (Jika saldo cukup, potong Wallet. Jika bayar tunai, ubah status jadi Lunas).

---

## 🐞 7. Panduan Cepat Debugging Mandiri

Jika Anda mengalami *Error 500* atau fitur tidak berjalan, lakukan 3 langkah ini:

1. **Gunakan Inspect Element (Tab Network):**
   * Klik kanan di browser > `Inspect` > Buka tab `Network`.
   * Klik tombol yang error di web Anda.
   * Akan muncul request (biasanya bernama `update` atau `checkout`). Klik tulisan merah tersebut, dan lihat bagian **Preview** atau **Response** untuk melihat pesan error aslinya.

2. **Cek File Log (Wajib!):**
   * Buka file `storage/logs/laravel.log`.
   * Gulir (scroll) ke baris paling bawah. Anda akan melihat pesan error berbahasa Inggris yang sangat spesifik (misal: *Column 'price' cannot be null*, berarti Anda lupa mengisi harga).

3. **Gunakan `dd()`:**
   * Di dalam file Volt (`resources/views/livewire/...`), cari fungsi yang bermasalah.
   * Ketik `dd($variabel);` di tengah-tengah kode untuk menghentikan program sementara dan melihat isi data.

> [!TIP]
> **Cara Melacak Kode:**
> Jika Anda berada di halaman web "Tambah Kargo", lihat URL-nya (misal `/cargo/create`). Buka `routes/tenant.php`, cari `/cargo/create`. Anda akan melihat nama komponennya (misal `createcargo`). Lalu buka file `resources/views/livewire/bookings/createcargo.blade.php` untuk mengeditnya!
