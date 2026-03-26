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