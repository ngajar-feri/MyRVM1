Berikut adalah cara melakukannya secara manual (untuk referensi Anda di masa mendatang):

### 1. Manajemen Service (Script ON/OFF/RESTART)
Untuk memudahkan, gunakan script `manage_service.sh` yang sudah tersedia di folder proyek:

```bash
cd /home/myrobot/myrvm-edge-new
./manage_service.sh {on|off|restart|status}
```

**Contoh Penggunaan:**
- **Nyalakan:** `./manage_service.sh on`
- **Matikan:** `./manage_service.sh off`
- **Restart:** `./manage_service.sh restart`
- **Cek Status:** `./manage_service.sh status`

---

### 2. Manajemen Service (Manual Systemctl)
Jika script tidak tersedia, gunakan perintah manual:

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