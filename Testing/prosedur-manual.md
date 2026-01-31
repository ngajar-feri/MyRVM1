Berikut adalah cara melakukannya secara manual (untuk referensi Anda di masa mendatang):

### 1. Manajemen Service (Stop/Start)
Gunakan perintah ini untuk mengelola status service di Raspberry Pi:

**Untuk Menghentikan (Stop):**
```bash
sudo systemctl stop myrvm-edge myrvm-updater
sudo systemctl disable myrvm-edge myrvm-updater
```

**Untuk Menjalankan Kembali (Start):**
```bash
sudo systemctl enable myrvm-edge myrvm-updater
sudo systemctl start myrvm-edge myrvm-updater
```

**Untuk Mengecek Status:**
```bash
sudo systemctl status myrvm-edge
```

### 2. Cara Membersihkan Total (Wipe Out)
Setelah bersih, gunakan langkah ini untuk instalasi segar:
```bash
# Clone repositori terbaru
git clone https://github.com/ngajar-feri/myrvm-edge.git /home/raspi1/myrvm-edge-new
cd /home/raspi1/myrvm-edge-new

# Beri izin eksekusi pada script
chmod +x scripts/*.sh

# Jalankan installer (otomatis membuat venv dan mendaftarkan service)
sudo ./scripts/setup_service.sh
```
### 3. Cara Mengakses Setup Wizard Sekarang
Saat ini, Setup Wizard sudah aktif di Raspi Anda. Silakan buka browser di laptop Anda dan akses alamat berikut:

👉 http://192.168.1.11:8080

Apa yang akan Anda lihat?

- UI Modern : Desain baru dengan tema Bio-Digital Minimalism.
- Auto-Theme : Jika sekarang malam hari, UI otomatis menjadi Dark Mode.
- Virtual Keyboard : Klik pada kolom input "Manual Entry", keyboard on-screen akan muncul secara otomatis (Portrait optimized).
Silakan dicoba akses, dan beri tahu saya jika ada kendala pada tampilan atau fungsionalitas keyboard-nya!