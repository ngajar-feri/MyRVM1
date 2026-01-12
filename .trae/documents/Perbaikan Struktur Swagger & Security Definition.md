Saya telah menemukan cara untuk membuat proses Copy-Paste Token menjadi otomatis di Swagger UI.
Berikut adalah rencana implementasinya:

1.  **Cek Nama Security Scheme**: Saya akan memeriksa file `config/l5-swagger.php` untuk memastikan nama kunci keamanan (apakah `bearerAuth`, `sanctum`, atau lainnya). Ini penting agar script otomatisasi bisa menargetkan kolom yang benar.
2.  **Modifikasi View Swagger (`index.blade.php`)**:
    *   Saya akan mengedit file `resources/views/vendor/l5-swagger/index.blade.php`.
    *   Saya akan menambahkan fungsi `responseInterceptor` di dalam konfigurasi Swagger UI.
    *   Fungsi ini akan otomatis mendeteksi jika ada respon sukses dari endpoint `/login`.
    *   Jika login sukses, script akan mengambil token dari JSON response dan langsung menyuntikkannya ke sistem Authorize Swagger.
3.  **Hasilnya**: Setelah Anda melakukan request login di Swagger dan mendapat respon 200 OK, tombol gembok (Authorize) akan otomatis terkunci dengan token baru, tanpa perlu copy-paste manual.

Apakah Anda setuju dengan implementasi otomatisasi ini?