---
description: Walkthrough
---

Advanced Workflows
Browser Testing: control, test, and screen record the application's UI automatically.
Vibe Coding/Iteration: If you see the application running and a visual not change, make visual look better to iteratively refine the design.
Artifact Review: produces verifiable artifacts like task lists, screenshots, recording and code differences (diffs) for me to review in the "Inbox" or Agent Manager. and save to  ke dalam folder D:\~dev\MyReverseVendingMachine1\MySuperApps\Docs\Artifacts dengan format:
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