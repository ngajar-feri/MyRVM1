# Rencana Pengujian Instalasi Day-0 (Simulasi Clean Install & Modernisasi Wizard)

Tujuan: Melakukan simulasi instalasi dari nol (*true clean install*) pada Raspberry Pi dan memperbarui antarmuka Setup Wizard agar lebih modern dan fungsional.

## 1. Modernisasi Setup Wizard (MyRVM-Edge)
Saya akan memperbarui komponen `setup_wizard` dengan standar proyek terbaru:
- **Backend (FastAPI)**: Refaktor [app.py](file:///Users/my/Documents/~dev/2025/MyRVM1/MyRVM-Edge/src/setup_wizard/app.py) ke FastAPI untuk stabilitas dan performa.
- **UI Bio-Digital Minimalism**: Mendesain ulang [index.html](file:///Users/my/Documents/~dev/2025/MyRVM1/MyRVM-Edge/src/setup_wizard/templates/index.html) dengan fitur:
    - **Auto Dark/Light Mode**: Mengikuti logika `themeStore.js` (Dark: 18:00-06:00, Light: 06:00-18:00) secara otomatis berdasarkan waktu internet.
    - **Formulir Manual Entry**: Menambahkan input form untuk Serial Number, API Key, dan RVM Name bagi teknisi yang tidak memiliki file JSON.
    - **Tab Interface**: Berpindah antara mode "Upload JSON" dan "Manual Entry".

## 2. Simulasi Clean Install (Manual di Raspberry Pi)
Sesuai permintaan, kita akan menghapus seluruh jejak proyek sebelumnya untuk pengujian yang benar-benar bersih:

### Langkah 1: Wipe Out & Re-clone
1. Masuk ke Raspberry Pi via SSH.
2. Hapus seluruh folder proyek: `rm -rf /home/raspi1/myrvm-edge-new`.
3. Clone ulang repositori: `git clone https://github.com/ngajar-feri/myrvm-edge.git /home/raspi1/myrvm-edge-new`.

### Langkah 2: Setup Service & Dependencies
1. Jalankan script instalasi: `cd /home/raspi1/myrvm-edge-new && sudo ./scripts/setup_service.sh`.
2. Script ini akan membuat `venv` baru dan mendaftarkan service sistem.
3. Karena `config/secrets.env` belum ada, service `myrvm-edge` akan otomatis masuk ke **Setup Mode** (Wizard aktif di port 8080).

### Langkah 3: Provisioning via New UI
1. Akses `http://100.97.142.34:8080` dari browser.
2. Uji **Manual Entry**: Masukkan kredensial `RVM-202601-UNU2` secara manual melalui form.
3. Simpan dan verifikasi transisi ke mode Operasional.

## 3. Milestone
- [ ] Update `app.py` (FastAPI) & `index.html` (Modern UI + Auto Theme + Manual Form).
- [ ] Push update ke GitHub.
- [ ] Eksekusi pembersihan total di Raspberry Pi.
- [ ] Jalankan setup awal dan verifikasi provisioning manual berhasil.

---
**Apakah rencana "Wipe Out" dan detail fitur Wizard ini sudah sesuai dengan keinginan Anda?**
