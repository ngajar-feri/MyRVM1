# Prosedur 1: Pengujian Instalasi Day-0 (Manual via Localhost & Touchscreen)

Tujuan: Melakukan simulasi instalasi bersih (*clean install*) di mana teknisi melakukan setup langsung pada layar touchscreen perangkat (Portrait Mode) tanpa menggunakan keyboard fisik.

## 1. Modernisasi Setup Wizard (MyRVM-Edge)
Saya akan memperbarui komponen `setup_wizard` dengan fitur:
- **FastAPI Backend**: Implementasi backend menggunakan FastAPI di [app.py](file:///Users/my/Documents/~dev/2025/MyRVM1/MyRVM-Edge/src/setup_wizard/app.py).
- **Modern UI (Bio-Digital Minimalism)**:
    - **Portrait Optimization**: Layout khusus untuk layar vertikal (Portrait) pada touchscreen RVM.
    - **Auto Theme Switching**: Berubah otomatis antara Dark/Light mode berdasarkan waktu internet.
    - **Manual Entry Form**: Input data kredensial via form.
- **Custom Virtual Keyboard (On-Screen)**:
    - **Responsive Design**: Ukuran tombol menyesuaikan resolusi layar secara dinamis.
    - **Touch-Friendly**: Optimasi untuk input sentuh (Portrait mode).
    - **Integration**: Keyboard otomatis muncul saat input field pada form dipilih.

## 2. Simulasi Clean Install (Raspberry Pi)
Langkah-langkah Prosedur 1:

### Fase A: Pembersihan Total
1. SSH ke Raspberry Pi.
2. Hapus seluruh folder proyek: `rm -rf /home/raspi1/myrvm-edge-new`.
3. Clone ulang: `git clone https://github.com/ngajar-feri/myrvm-edge.git /home/raspi1/myrvm-edge-new`.

### Fase B: Instalasi & Pemicuan Setup
1. Jalankan instalasi: `cd /home/raspi1/myrvm-edge-new && sudo ./scripts/setup_service.sh`.
2. Service akan otomatis menjalankan Wizard di `localhost:8080` karena belum ada konfigurasi.

### Fase C: Provisioning via Touchscreen Simulation
1. Akses `http://127.0.0.1:8080` (menggunakan simulasi layar Portrait di browser).
2. Gunakan **Virtual Keyboard** untuk memasukkan:
   - Serial: `RVM-202601-UNU2`
   - API Key: `9L5R2ivFV5Ikt1JcuBcXUYXmmUjFLv482C7OE56xuqo9dx6rukpyFp6wHpHG0a46`
   - Name: `UNU Yogyakarta 2`
3. Simpan dan verifikasi mesin berpindah ke mode operasional.

## 3. Milestone
- [ ] Implementasi FastAPI, Modern UI (Portrait), dan Custom Virtual Keyboard.
- [ ] Push ke GitHub.
- [ ] Eksekusi "Clean Install" di Raspi.
- [ ] Verifikasi penggunaan Virtual Keyboard dan keberhasilan provisioning lokal.

---
**Apakah fitur Virtual Keyboard untuk layar Portrait ini sudah sesuai dengan spesifikasi yang Anda butuhkan?**
