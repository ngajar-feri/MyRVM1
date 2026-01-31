sekarang lakukan pengujian Instalasi day-0 dari kedua device orin dan raspberrypi.
Host: https://myrvm.penelitian.my.id/
Jenis: PWA -> Popup modal
Project Name and Technology Stack: 
- MyRVM-Server: Docker
- MyRVM-Edge: python
    - Devices: 
        - orin = NVIDIA Jetson Orin
        - raspberrypi = Raspberry Pi 4 Model B

1. Jika anda melakukan perubahan pada Codebase @MyRVM-Server dan @MyRVM-Edge, pastikan melakukan PUSH ke Github **[multi-repo-git-protocol.md](file:///Users/my/Documents/~dev/2025/MyRVM1/.trae/rules/multi-repo-git-protocol.md)** jangan lupa versioning increment, MAJOR dan Minor untuk di gunakan untuk update (auto pull dan manual pull dengan membandingkan version di local edge dan di github sebagai midleman) dan Changelog di update (jika belum ada maka buatlah).
[]
2. Login host menggunakan credential ada di rules **[network-topology.md](file:///Users/my/Documents/~dev/2025/MyRVM1/.trae/rules/network-topology.md)**
3. Buat RVM Machines gunakan lokasi:
- Kasetsart University 2: 13.841324806213379,100.57474517822266
    - Address: 50, Ngam Wong Wan Road, Chatuchak District, 10900, Thailand
- UNU Yogyakarta 2: -7.788767502160791,110.33038360699398
    - Address: unnamed road, Banyuraden 55293, Special Region of Yogyakarta, Indonesia
4. Address sesuaikan dengan titik lokasi.
5. Tambahkan Technician Assignments (Hak Akses RVM)
6. User Assign:
- Tech Support
- Operator RVM
7. Pilih RVM Machine:
- Operator RVM = Kasetsart University 2 
- Tech Support = UNU Yogyakarta 2
8. Download masing-masing credential.json
9. sebagai bukti anda sudah mendownload, letakan berkas credential di /Users/my/Documents/~dev/2025/MyRVM1/Testing/Real-Scenario dengan subdirectory tanggal bulan tahun jam saat anda melakukannya.
10. persiapan credential RVM Machines -> SELESAI!
11. setelah persiapan credential RVM Machines SELESAI.
12. Login Remote-SSH ke orin lakukan Instalasi day-0 menggunakan credential orin (Kasetsart University 2).
13. Update status Instalasi day-0 -> Selesai
14. exit dari orin
15. Login Remote-SSH ke raspberrypi lakukan instalasi day-0 menggunakan credential raspberrypi (UNU Yogyakarta 2).
16. update status Instalasi day-0 -> Selesai
17. exit dari raspberrypi
18. 
    - Metode A: Pengecekan di https://myrvm.penelitian.my.id/dashboard/machines
    Apakah mesin sudah Online dengan data yang sudah di kirim (handsake) berhasil. Jika berhasil -> selesai. Jika belum -> ulangi langkah tadi dengan membuat RVM Machines baru. dan menghapus RVM Machines. Cara menghapus yaitu 
    1. lakukan penghapusan Technician Assignments (Hak Akses RVM) yang mengarah ke RVM Machines yang akan dihapus terlebih dahulu. 
    2. Hapus RVM Machines.
    - Metode B: Menggunakan API Endpoint RVM Machines.
    - Metode C: Melihat langsung data RVM Machines dari Basisdata.

19. Jika semua mesin sudah Online dan data sesuai yang dikirim melalui Handshake berarti **Pengujian Selesai**

20. Laporkan kepada saya, serta Dokumentasi simpan di Docs/07_Artifacts/Testing menggunakan rules .trae/rules/artifact-generation.md dan refrensikan ke credential masing-masing devices. jika terjadi error di tengah jalan atau di pertengahan tahap segera perbaiki, setelah itu laporkan error tersebut dan membuat Artifact Masukan ke dalam folder /Users/my/Documents/~dev/2025/MyRVM1/Docs/09_Troubleshooting/{Project Name}/{Error Name} dan di dalam dokumentasi tambahkan Changelog.