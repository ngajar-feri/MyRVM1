Untuk melakukan push ke masing-masing repositori, kuncinya adalah **berpindah direktori (folder)** di Terminal sebelum menjalankan perintah Git. Git akan secara otomatis mendeteksi repositori mana yang sedang aktif berdasarkan folder tempat Anda berada.

Berikut adalah langkah-langkah detailnya:

### 1. Memperbaiki Bug di Raspi (MyRVM-Edge)
Gunakan perintah ini jika Anda mengubah kode di dalam folder `MyRVM-Edge` :
```bash
# 1. Pindah ke folder Edge
cd /Users/my/Documents/~dev/2025/MyRVM1/MyRVM-Edge

# 2. Tambahkan, commit, dan push
git add .
git commit -m "Fix bug pada sensor di Raspi"
git push origin master
```

### 2. Memperbaiki Bug di Server (MyRVM-Server)
Gunakan perintah ini jika Anda mengubah kode di dalam folder `MyRVM-Server` :
```bash
# 1. Pindah ke folder Server
cd /Users/my/Documents/~dev/2025/MyRVM1/MyRVM-Server

# 2. Tambahkan, commit, dan push
git add .
git commit -m "Fix bug pada API di Server"
git push origin master
```

### 3. Mengupdate Docs atau Testing (MyRVM1 Root)
Gunakan perintah ini jika Anda mengubah file di folder `Docs` , `Testing` , atau file di luar folder `Edge/Server` :
```bash
# 1. Pindah ke folder root MyRVM1
cd /Users/my/Documents/~dev/2025/MyRVM1

# 2. Tambahkan, commit, dan push
git add .
git commit -m "Update dokumentasi atau testing"
git push origin master
```

### Tips Cepat di Trae IDE:
Jika Anda menggunakan Terminal di dalam Trae, perhatikan label di sebelah kanan atau atas terminal yang menunjukkan lokasi folder Anda.

- **Penting** : Saat Anda melakukan `git add .` di folder `Root (MyRVM1)` , Git di folder tersebut juga akan mendeteksi perubahan di dalam `MyRVM-Edge` dan `MyRVM-Server` . Jadi, jika Anda ingin semua repositori sinkron, urutan terbaiknya adalah:
  1. Push di `MyRVM-Edge` (jika ada perubahan kode edge).
  2. Push di `MyRVM-Server` (jika ada perubahan kode server).
  3. Terakhir, Push di `MyRVM1` (untuk mencatat semua perubahan tersebut ke repositori utama).
Apakah Anda ingin saya membuatkan script otomatis (misalnya `sync_all.sh` ) agar sekali klik semua repositori langsung ter-push ke GitHub masing-masing?
```bash
#!/bin/bash

# 1. Push MyRVM-Edge
cd /Users/my/Documents/~dev/2025/MyRVM1/MyRVM-Edge
git add .
git commit -m "Sync perubahan di Edge"
git push origin master

# 2. Push MyRVM-Server
cd /Users/my/Documents/~dev/2025/MyRVM1/MyRVM-Server
git add .
git commit -m "Sync perubahan di Server"
git push origin master

# 3. Push MyRVM1 Root
cd /Users/my/Documents/~dev/2025/MyRVM1
git add .
git commit -m "Sync perubahan di Root"
git push origin master
```