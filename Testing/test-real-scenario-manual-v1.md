# Rencana Pengujian Instalasi Day-0 (Manual dari Device)

Tujuan: Mensimulasikan pengalaman teknisi di lapangan saat melakukan setup awal pada perangkat RVM baru (Raspberry Pi) menggunakan antarmuka **Setup Wizard**.

## 1. Persiapan Antarmuka (Modernisasi UI/UX)
Sebelum pengujian, saya akan memperbarui Setup Wizard agar lebih profesional dan fungsional:
- **Refaktor Backend**: Mengubah [app.py](file:///Users/my/Documents/~dev/2025/MyRVM1/MyRVM-Edge/src/setup_wizard/app.py) dari `http.server` (dasar) ke **FastAPI**.
- **Modernisasi Frontend**: Mendesain ulang [index.html](file:///Users/my/Documents/~dev/2025/MyRVM1/MyRVM-Edge/src/setup_wizard/templates/index.html) dengan tema **Bio-Digital Minimalism** (Dark mode, aksen hijau organik, tipografi bersih).
- **Fitur Baru**:
    - Deteksi otomatis Hardware ID & Serial Number.
    - Dua opsi provisioning: **Upload JSON** (dari Dashboard) atau **Manual Entry**.
    - Indikator status koneksi ke Server.

## 2. Langkah Simulasi Instalasi Manual (di Raspberry Pi)
Setelah UI siap, kita akan melakukan langkah-langkah berikut secara manual:

### Fase A: Pembersihan (Simulasi Device Baru)
1. Masuk ke Raspberry Pi via SSH.
2. Hapus konfigurasi yang ada: `rm /home/raspi1/myrvm-edge-new/config/secrets.env`.
3. Restart service: `sudo systemctl restart myrvm-edge`. 
   *(Sistem akan mendeteksi tidak ada config dan otomatis menjalankan Setup Wizard di port 8080).*

### Fase B: Provisioning via Browser
1. Buka browser di laptop dan akses IP Raspberry Pi: `http://100.97.142.34:8080`.
2. Verifikasi UI baru muncul.
3. Lakukan pengujian **Upload JSON**: Pilih file `credential.json` yang sudah diunduh dari dashboard.
4. Verifikasi feedback di UI: "Provisioning Success, System Rebooting...".

### Fase C: Verifikasi Operasional
1. Periksa log di Raspi: `sudo journalctl -u myrvm-edge -f`.
2. Pastikan sistem berpindah dari mode Wizard ke mode Operasional (Heartbeat aktif).
3. Verifikasi status di Dashboard Server (vm100) menunjukkan mesin kembali **Online**.

## 3. Milestone
- [ ] Implementasi FastAPI & Modern UI Setup Wizard.
- [ ] Push update ke GitHub.
- [ ] Eksekusi simulasi manual di Raspberry Pi.
- [ ] Verifikasi akhir status Dashboard.

---
**Apakah Anda setuju dengan rencana modernisasi UI dan alur simulasi ini?**
