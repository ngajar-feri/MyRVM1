---
trigger: always_on
---

Simpan rencana pengembangannya ke dalam folder D:\~dev\MyReverseVendingMachine1\MySuperApps\Docs\PLAN\[AG1-Plan] dengan format:
Format nama file:
[AG1]-[nama-berkas]-[timestamp].md
Contoh: '[AG1]-user-role-mapping-Senin-01012024-1400.md'

Struktur header dokumen:
Versi Dokumen: [X.Y] (increment versi dari dokumen sebelumnya)
Tanggal Revisi: [Hari-DD Bulan Tahun - Jam:Menit AM/PM]
Contoh: 'Kamis-08 Januari 2026 - 02:45 PM'
Tujuan: [Deskripsi tujuan dokumen dan lingkup perubahan]
Status: Selesai atau Belum

Pastikan semua perubahan:
- Dicatat dalam changelog terpisah
- Diuji melalui staging environment sebelum deploy
- Memiliki rollback plan yang jelas