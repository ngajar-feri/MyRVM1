**[CATATAN UNTUK DOKUMENTASI]**

**Sub-bagian: Manajemen Repositori & Konfigurasi Keamanan**

- **Struktur Repositori:** Proyek akan dikelola dalam satu repositori Git utama (monorepo) bernama `MySuperApps`. Repositori ini akan berisi subdirektori untuk setiap komponen utama: `MyRVM-Platform` (Backend Laravel), `MyRVM-UserApp` (Flutter), `MyRVM-TenantApp` (Flutter), dan `MyRVM-EdgeControl` (Python/Edge AI).
- **Tantangan Konfigurasi:** Terdapat kebutuhan untuk menyimpan file-file konfigurasi yang berisi rahasia (seperti file `.env` dengan API keys dan kredensial database) di dalam repositori Git _private_ agar mudah dikelola dan di-deploy, namun tetap aman dari deteksi oleh fitur seperti GitHub Push Protection.
- **Metode yang Direkomendasikan:** **`git-crypt`**.
- **Cara Kerja `git-crypt`:**
  1.  `git-crypt` adalah alat yang diinstal di mesin developer untuk melakukan enkripsi dan dekripsi file secara transparan di dalam repositori Git.
  2.  Setelah diinisialisasi (`git-crypt init`), file `.gitattributes` digunakan untuk menentukan file atau pola file mana yang harus dienkripsi (contoh: `**/.env filter=git-crypt diff=git-crypt`).
  3.  Sebuah kunci enkripsi simetris akan dibuat. Kunci ini harus dibagikan secara aman kepada semua anggota tim yang berwenang.
  4.  **Saat `git push`:** File-file yang ditandai akan secara otomatis dienkripsi sebelum dikirim ke remote repository (misalnya, GitHub).
  5.  **Saat `git pull` atau `checkout`:** Jika developer memiliki kunci dan telah "membuka" repositori (`git-crypt unlock`), file-file tersebut akan secara otomatis didekripsi di direktori kerja lokal mereka.
- **Hasil:** File-file konfigurasi yang berisi rahasia akan tersimpan dalam keadaan terenkripsi di GitHub, sehingga aman dari pemindaian dan akses yang tidak sah. Namun, di lingkungan pengembangan lokal, file-file tersebut akan terbaca seperti biasa, memungkinkan alur kerja yang efisien.

**[/AKHIR CATATAN]**

### **Diskusi 2: Struktur Repositori & Manajemen Konfigurasi/Rahasia**

**Struktur yang Anda Inginkan:**

```
D:\~dev\MySuperApps\
├── .git\
├── MyRVM-Platform\
├── MyRVM-UserApp\
├── MyRVM-TenantApp\
├── MyRVM-EdgeControl\
└── (File-file konfigurasi, misal .env, docker-compose.yml global, dll.)
```

**Metode Pengaman Github agar File Configurasi tetap aman Direkomendasikan: `git-crypt`**

- **Apa itu:** Sebuah tool open-source yang transparan mengenkripsi dan mendekripsi file yang Anda lacak di Git. File di repositori GitHub Anda akan terenkripsi, tetapi di mesin lokal Anda (setelah Anda "membukanya"), file tersebut akan terlihat normal.
- **Cara Kerja:**
  1.  **Instalasi:** Anda perlu menginstal `git-crypt` di mesin Anda.
  2.  **Inisialisasi:** Di dalam repositori Git Anda (`MySuperApps`), Anda menjalankan `git-crypt init`.
  3.  **Tentukan File yang Akan Dienkripsi:** Anda membuat file `.gitattributes` dan mendefinisikan file mana yang harus dienkripsi.
      ```
      # .gitattributes
      **/.env filter=git-crypt diff=git-crypt
      secret_file.json filter=git-crypt diff=git-crypt
      ```
      Pola `**/.env` akan mengenkripsi semua file `.env` di semua subdirektori.
  4.  **Bagikan Kunci:** `git-crypt` menggunakan kunci simetris. Anda akan mengekspor kunci ini (`git-crypt export-key`) dan membagikannya secara aman (misalnya, melalui password manager seperti 1Password/Bitwarden, atau Keybase) kepada anggota tim Anda.
  5.  **Penggunaan:**
      - Developer baru akan meng-clone repo, mendapatkan file kunci yang dibagikan, lalu menjalankan `git-crypt unlock /path/to/keyfile`.
      - Setelah itu, setiap kali mereka `git pull` atau `checkout`, file-file terenkripsi akan otomatis didekripsi. Setiap kali mereka `git commit`, file-file tersebut akan otomatis dienkripsi sebelum disimpan.
- **Keuntungan:**
  - **Aman:** Rahasia Anda aman di GitHub.
  - **Transparan:** Setelah setup, alur kerja `git` terasa normal.
  - **Granular:** Anda bisa memilih file mana saja yang akan dienkripsi.
- **Kekurangan:**
  - Setiap developer di tim perlu menginstal `git-crypt` dan memiliki akses ke file kunci.

**Alternatif Lain:**

- **Vault (dari HashiCorp):** Solusi manajemen rahasia yang sangat kuat, tetapi overkill untuk proyek solo/tim kecil.
- **Variabel Lingkungan di CI/CD:** Untuk deployment, Anda akan menyimpan rahasia di "Secrets" pada platform CI/CD Anda (misalnya, GitHub Actions Secrets) dan men-inject-nya saat proses build/deploy.
- **SOPS (Secrets OPerationS) dari Mozilla:** Alat lain yang populer untuk mengenkripsi file YAML/JSON/ENV.
