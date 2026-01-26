# Changelog

Semua perubahan penting pada proyek ini akan didokumentasikan dalam file ini.

## [Unreleased] - 2026-01-09

### Added
- **Core API Transactions**: Implementasi endpoint `/api/v1/transactions/start`, `/item`, `/commit` untuk menangani siklus transaksi RVM.
- **Redemption System**: Implementasi endpoint `/api/v1/redemption/redeem` (User) dan `/validate` (Tenant) beserta tabel `user_vouchers`.
- **Technician Module**: Implementasi endpoint `/api/v1/technician/*` untuk assignment, PIN generation, dan validasi akses mesin.
- **Database Schema**:
    - `rvm_transactions` & `transaction_items` (menggantikan tabel transactions lama).
    - `user_vouchers` untuk menyimpan voucher milik user.
    - `technician_assignments` & `maintenance_logs` untuk modul teknisi.
    - Kolom `points_balance` & `gold_balance` pada tabel `users`.
    - Kolom `name`, `last_ping` pada tabel `rvm_machines`.
- **API Documentation**: Update manual `api-docs.json` untuk mencakup endpoint transaksi utama.

### Changed
- **Database**: Restrukturisasi tabel `transactions` untuk mendukung model Header-Detail (Session & Items).
- **Routes**: Penambahan group route untuk Transaction, Redemption, dan Technician di `routes/api.php`.

### Fixed
- **Swagger Docs**: Masalah path item yang hilang pada auto-generate Swagger sementara diatasi dengan update manual file JSON.

---
