pada aplikasi travel kita ada 4 role : super admin, owner, admin agen, dan driver

berikut beberapa permissionnya
- melihat jadwal :
super admin : semua
owner : semua
admin agen : hanya jadwal dari dan ke agennya
driver : hanya jadwal yang dia dipilih menjadi sopir/driver

- tambah/edit/hapus jadwal :
super admin : semua
owner : semua
admin agen : hanya jadwal dari agennya (jika staus masih scheduled, kalau status sudah tiba/sampai/selesai, sudah tidak bisa diedit/hapus)
driver : tidak bisa

- melihat penumpang :
super admin : semua
owner : semua
admin agen : hanya penumpang dari dan ke agennya
driver : hanya penumpang yang dia dipilih menjadi sopir/driver

- tambah/edit/hapus penumpang :
super admin : semua
owner : semua
admin agen : hanya penumpang dari agennya 
driver : tidak bisa

- melihat barang/cargo :
owner : semua
admin agen : hanya barang/cargo dari dan ke agennya
driver : hanya barang/cargo yang dia dipilih menjadi sopir/driver

- tambah/edit/hapus barang/cargo :
super admin : semua
owner : semua
admin agen : hanya barang/cargo dari agennya (edit status sudah dibayar jika barang dibayar lunas, jika dibayar cod/bayar tujuan maka yang bisa edit adalah agen tujuan, begitupun status pengambilan, yang bisa edit status hanya agen tujuan)
driver : tidak bisa



Memikirkan skala (scalability) sejak awal akan menyelamatkan Anda dari aplikasi yang tiba-tiba lemot atau crash saat data sudah mencapai puluhan ribu baris.

Untuk mempertahankan performa tampilan Jadwal, Penumpang, dan Barang di masa depan, berikut adalah strategi bertahap yang wajib Anda terapkan dari yang paling mudah hingga level arsitektur:

Tahap 1: Optimasi Level Database (Wajib Dilakukan Sekarang)
Ini adalah fondasi. Sebagus apa pun kode PHP Anda, jika databasenya lambat, aplikasi akan lambat.

Gunakan Indexing (Pengindeksan):
Saat ini Anda sering memfilter berdasarkan tanggal, status, dan agen. Jika data sudah ratusan ribu, pencarian ini akan membuat server bekerja keras. Anda harus menambahkan index pada file migration Anda.
Contoh di tabel schedules:

PHP
$table->index(['departure_date', 'status']);
$table->index(['route_id']);
Analogi: Indexing itu seperti daftar isi pada buku tebal. Server tidak perlu membaca halaman satu per satu untuk mencari jadwal hari ini.

Pertahankan Eager Loading (with()):
Anda sudah melakukan ini dengan sangat baik di kode Anda (Schedule::with(['route', 'bus'])). Ini mencegah masalah N+1 Query Problem (memanggil database berkali-kali di dalam looping tampilan). Tetap pertahankan pola ini.

Tahap 2: Optimasi Tampilan (Livewire & UI)
Jangan pernah me-load semua data sekaligus ke layar (menggunakan ->get()).

Ganti get() dengan Pagination:
Jika jadwal sebulan ada 1.000, HP pengguna akan nge-lag merender 1.000 kotak HTML sekaligus.
Ubah ->get() di akhir query Anda menjadi ->paginate(20). Di Livewire, ini sangat mudah diimplementasikan.

Gunakan Cursor Pagination untuk Data Super Besar:
Jika data sudah jutaan, paginate(20) biasa akan lambat jika user mengklik "Halaman 500". Gunakan ->cursorPaginate(20). Ini metode yang dipakai Facebook/Instagram untuk fitur Infinite Scroll (gulir tanpa batas) karena sangat ringan di memori.

Gunakan Debounce pada Fitur Pencarian:
Jika nanti Anda membuat kotak pencarian (misal cari nama penumpang), jangan biarkan sistem mencari setiap kali user mengetik 1 huruf. Gunakan wire:model.live.debounce.500ms="search". Ini memberi jeda setengah detik sebelum query dijalankan.

Tahap 3: Strategi Bisnis & Arsitektur (Untuk Jangka Panjang)
Data travel tahun 2024 tidak relevan lagi untuk ditampilkan secara harian di tahun 2026.

Default Filter "Data Aktif" (Seperti yang sudah Anda buat!):
Sistem filter Bulan dan Tahun yang Anda buat di halaman Jadwal itu sudah sangat tepat. Dengan memaksa user hanya melihat data 1 bulan tertentu, query withSum (menghitung total pendapatan/penumpang) tidak akan membebani server karena yang dihitung paling banyak hanya ratusan jadwal, bukan jutaan jadwal sejak aplikasi berdiri.

Sistem "Archiving" (Arsip):
Nanti jika aplikasi sudah berjalan 1-2 tahun, Anda bisa membuat perintah (Cron Job) otomatis di Laravel yang memindahkan jadwal dengan status completed atau cancelled yang usianya sudah lebih dari 3 bulan ke tabel terpisah (misalnya historical_schedules), atau setidaknya memberikan status is_archived = true agar disembunyikan secara default dari pandangan agen.

Pemisahan Laporan dan Operasional:
Halaman List (Operasional) jangan digabung dengan Laporan Keuangan (Reporting). Jika Owner ingin melihat total pendapatan setahun, buatkan halaman Dashboard Laporan tersendiri yang mungkin datanya di-cache (disimpan sementara), sehingga tidak menghitung ulang jutaan data resi setiap kali halaman direfresh.


perbaikan penulisan kode program :
Component-Based Architecture: Ini adalah paradigma pengembangan perangkat lunak yang memecah aplikasi menjadi bagian-bagian kecil (komponen) yang mandiri, rapi, dan fungsional. Komponen seperti tombol (button), navbar, atau formulir dibuat sekali dan dapat dipasang di halaman mana pun tanpa menulis ulang kodenya.
Don't Repeat Yourself (DRY): Ini adalah prinsip inti dari penulisan kode rapi. DRY menekankan untuk menghindari duplikasi kode dengan cara memindahkan logika atau UI yang sama ke dalam fungsi, kelas, atau komponen terpisah agar bisa dipakai ulang.
Modularization (Modul/Modul-based): Teknik memecah kode menjadi modul-modul kecil berdasarkan fungsinya (misalnya: file untuk komponen UI, file untuk logika API, dll).
Atomic Design: Salah satu teknik atau metodologi dalam component-based architecture yang menyusun komponen dari level terkecil (atom) seperti tombol, hingga tingkat yang lebih kompleks (organisme) seperti navbar. 
Mendix
Mendix
 +7
Manfaat dari teknik ini:
Reusability: Komponen dapat digunakan di banyak tempat.
Maintainability: Mudah diperbaiki, karena jika ada perubahan, Anda hanya perlu mengubah satu komponen tersebut, bukan di semua tempat.
Readability: Kode menjadi lebih rapi dan mudah dipahami.

kita mulai dulu ke Optimasi Database (Indexing): Menambahkan index migration pada tabel schedules, passengers, dan rute sehingga pencarian rentang tanggal lebih ngebut.
Penerapan Pagination Visual: Beralih ke paginasi (dan Cursor Pagination) untuk data jutaan baris daripada meload semua list pemesanan sekaligus di tampilan UI Livewire., tapi sebelum mulai, coba cek kambali penulisan kode aplikasi kita, coba cek kambali apakah ada fitur/componen pada aplikasi kita yang digunakan berulang ulang tapi belum modular, contoh yang sudah kita terapkan adalah resources\views\components\card\passenger-card.blade.php jadi card bisa dipanggil di beberapa tempat sekaligus,  coba baca catatan.md mulai baris 86, dan buat rencana pengembangan/implementation plan terlebih dahulu


Berikut adalah ringkasan sejauh mana progres kita saat ini dan apa saja yang direncanakan untuk dikembangkan selanjutnya:

✅ **Sejauh Mana Aplikasi Kita Saat Ini (Yang Sudah Selesai)?**
Aplikasi sudah memiliki fondasi operasional dan anti-fraud yang kokoh. Berdasarkan perkembangan terbaru, fitur unggulan yang sudah berjalan/terselesaikan meliputi:

- **Hak Akses Lengkap (Role-based):** Pemisahan wewenang ketat antara Super Admin, Owner, Admin Agen, dan Supir. Ditambah dengan **Manajemen User khusus Super Admin**.
- **Sistem Token & Dompet Prabayar (Anti-Piutang Agen):** Saldo agen dipotong presisi otomatis ketika transaksi pemesanan atau kargo terjadi.
- **Interactive Seat Mapper:** Agen dapat melihat denah sisa kuota kursi bus secara visual untuk menghindari status kursi ganda (double-booking).
- **Proteksi Sistem Ekstra:** Pencegahan double-submit dan limitasi akses sopir (hanya melihat manifes perjalanannya).
- **Modul Kargo & COD Intuitif:** Alur uang masuk kargo terekam jelas. Laporan performa agen sudah diperbaiki, termasuk **Akurasi Perhitungan Pendapatan Agen vs Kargo**.
- **Sistem Soft Delete Global & Manajemen Rute:** Penerapan *Soft Delete* berantai (Cascade) dan halaman Master Rute (CRUD). Perbaikan **Route Model Binding** pada jadwal (mendukung `id` dan `schedule_code`).
- **Pembaruan Arsitektur Database:** Konsolidasi file *migrations* dan *seeders* untuk database yang lebih bersih.
- **Peningkatan UI & Mobile:** Perbaikan *clipping* halaman login di perangkat mobile dan inisiasi penyesuaian aplikasi menjadi **Progressive Web App (PWA)**.
- **Automasi Status Jadwal:** Penyesuaian logika penyelesaian perjalanan otomatis/semi-otomatis.
- **Transformasi ke SaaS Multi-Tenant:** Migrasi dari aplikasi *Single-Tenant* menjadi *SaaS Multi-Database* menggunakan `stancl/tenancy`. Data antar perusahaan travel/PO kini 100% terisolasi dalam database masing-masing, memungkinkan kita mengelola banyak perusahaan dalam satu instalasi utama (Central Portal).

🚧 **Apa Selanjutnya (Yang Belum & Akan Dikembangkan)?**
Menurut Peta Jalan (Roadmap) dan catatan sebelumnya, ada beberapa tahap perbaikan dan fitur baru yang bisa dikerjakan selanjutnya:

**1. Modularisasi UI & Component-Based Architecture (Prioritas Refactoring)**
- Menerapkan prinsip DRY (Don't Repeat Yourself) dan Atomic Design pada komponen Livewire & Blade agar kode UI (seperti form, pencarian, tombol) lebih rapi dan dapat di-reuse secara maksimal.

**2. Perbaikan Skalabilitas & Performa (Wajib Jika Data Makin Besar)**
- **Optimasi Database (Indexing):** Menambahkan index migration pada tabel schedules, passengers, dan rute sehingga pencarian rentang tanggal lebih ngebut.
- **Penerapan Pagination Visual:** Beralih ke paginasi (dan Cursor Pagination) untuk data bervolume tinggi agar tidak meload semua list pemesanan sekaligus di tampilan UI Livewire.
- **Sistem Arsip (Archival System):** Menyiapkan logika (atau Cron Job otomatis) untuk memisahkan data jadwal berusia tua (lebih dari 3 bulan) ke data historis.

**3. Fase Terdekat (Utilitas & Ekspor Data Tambahan)**
- **Ekspor Dokumen ke Excel/PDF:** Fitur rekap data (Laporan performa, arus kas harian agen, daftar kargo) agar bisa di-download berbentuk format file Spreadsheet / PDF untuk dibukukan secara per-Bulan.
- **Pencetakan Manifes Fisik (Sopir):** Butuh sebuah tombol agen/admin di Terminal yang mencetak Kertas Manifes/Daftar Absen 1-lembar buat supir untuk kontrol di pos pantau/jalan.
- **Auto-Kirim Gateway WhatsApp:** Sistem terintegrasi ke layanan provider WA pihak ke-3 tanpa perlu agen mem-forward link tiket PDF-nya secara manual.

**4. Fase Menengah & Jangka Panjang (Ekspansi Bisnis)**
- **Portal Pemesanan Pelanggan Awam (B2C):** Portal mandiri untuk Pelanggan melakukan order tiket dan bayar pakai metode otomatis ke rekening mutasi pusat secara independen.
- **Sistem Payroll & Komisi Karyawan/Supir:** Menghitung otomatis Uang Jalan, Uang Bensin, Potongan, hingga komisi persenan sang sopir di setiap penugasan.
- **Pengingat Maintenance Armada:** Pelacakan jadwal bengkel (Pajak STNK, Servis Ban / ganti Oli) sesuai odometer operasional travel.

Jika Anda ingin kita mulai bekerja sekarang, mana dari 4 kategori di atas (Modularisasi, Skalabilitas, Ekspor Dokumen, atau B2C) yang ingin Anda kerjakan terlebih dahulu?

multy Tenant:
- ketika menambahkan tenant baru maka akan ada database baru, tambahkan pengecekkan apakah tenant yang akan ditambahkan sudah ada ditabel tenant,jika sudah ada tampilakan pesan error, dan database/domain tidak akan dibuat sehingga tidak double
-   ketika membuat tenant baru, maka akan ada database baru secara otomatis, tambahkan juga data awal untuk user yaitu, akun saya sebagai developer dengan role super admin, owner, admin agen, dan sopir, buatkan juga satu data rute,bus,layout bus
 