sekarang lakukan pengujian Instalasi day-0 dari kedua device orin dan raspberrypi.
Host: https://myrvm.penelitian.my.id/
Jenis: PWA -> Popup modal
Project Name and Technology Stack: 
- MyRVM-Server: Docker
- MyRVM-Edge: python
    - Devices: 
        - orin = NVIDIA Jetson Orin
        - raspberrypi = Raspberry Pi 4 Model B

1. Jika anda melakukan perubahan pada Codebase @MyRVM-Server dan @MyRVM-Edge, pastikan melakukan PUSH ke Github.
2. Login host menggunakan credential ada di rules anda
3. Buat RVM Machines gunakan lokasi:
- Kasetsart University 1: 13.841324806213379,100.57474517822266
    - Address: 50, Ngam Wong Wan Road, Chatuchak District, 10900, Thailand
- UNU Yogyakarta 1: -7.788767502160791,110.33038360699398
    - Address: unnamed road, Banyuraden 55293, Special Region of Yogyakarta, Indonesia
4. Address sesuaikan dengan titik lokasi.
5. Tambahkan Technician Assignments (Hak Akses RVM)
6. User Assign:
- Tech Support
- Operator RVM
7. Pilih RVM Machine:
- Tech Support = Kasetsart University 1
- Operator RVM = UNU Yogyakarta 2
8. Download masing-masing credential.json
9. sebagai bukti anda sudah mendownload, letakan berkas credential di Testing/Real-Scenario dengan subdirectory tanggal bulan tahun jam saat anda melakukannya.
10. persiapan credential RVM Machines -> SELESAI!
11. setelah persiapan credential RVM Machines SELESAI.
12. Login Remote-SSH ke orin lakukan Instalasi day-0 menggunakan credential orin (UNU Yogyakarta 1).
13. Update status Instalasi day-0 -> Selesai
14. exit dari orin
15. Login Remote-SSH ke raspberrypi lakukan instalasi day-0 menggunakan credential raspberrypi (UNU Yogyakarta 2).
16. update status Instalasi day-0 -> Selesai
17. exit dari raspberrypi
18. Dokumentasikan semua dan simpan di Testing/Real-Scenario dengen refrensikan ke credential masing-masing devices.
19. 
Metode A: Pengecekan di https://myrvm.penelitian.my.id/dashboard/machines
Apakah mesin sudah Online dengan data yang sudah di kirim (handsake) berhasil. Jika berhasil -> selesai. Jika belum -> ulangi langkah tadi dengan membuat RVM Machines baru. dan menghapus RVM Machines. Cara menghapus yaitu 1. lakukan penghapusan Technician Assignments (Hak Akses RVM) yang mengarah ke RVM Machines yang akan dihapus terlebih dahulu. 2 Hapus RVM Machines.
Metode B: Menggunakan API Endpoint RVM Machines.
Metode C: Melihat langsung data RVM Machines dari Basisdata.

20. Jika semua mesin sudah Online dan data sesuai yang dikirim melalui Handshake berarti **Pengujian Selesai**