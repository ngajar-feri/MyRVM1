# MyRVM-Server API Endpoints Summary

**Versi Dokumen**: 1.0  
**Tanggal Revisi**: Sabtu-11 Januari 2026 - 03:02 PM  
**Tujuan**: Rangkuman lengkap semua RESTful API endpoints yang tersedia di MyRVM-Server.  
**Status**: ✅ Dokumentasi Lengkap

---

## Base URL

```
http://localhost:8000/api/v1
```

---

## 1. Authentication (Public)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/v1/register` | Register new user |
| `POST` | `/v1/login` | Login & get Bearer token |
| `POST` | `/v1/forgot-password` | Request password reset |
| `POST` | `/v1/reset-password` | Reset password with token |

---

## 2. Authentication (Protected)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `POST` | `/v1/logout` | Logout & revoke token | ✅ |
| `GET` | `/v1/me` | Get current user info | ✅ |

---

## 3. User Profile (Protected)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `PUT` | `/v1/profile` | Update user profile | ✅ |
| `PUT` | `/v1/change-password` | Change password | ✅ |
| `POST` | `/v1/user/upload-photo` | Upload profile photo | ✅ |
| `GET` | `/v1/user/balance` | Get user point balance | ✅ |

---

## 4. Vouchers (Public)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/v1/vouchers` | List available vouchers |

---

## 5. Tenant Voucher Management (Role: Tenant)

| Method | Endpoint | Description | Auth | Role |
|--------|----------|-------------|------|------|
| `GET` | `/v1/tenant/vouchers` | List tenant vouchers | ✅ | tenant |
| `POST` | `/v1/tenant/vouchers` | Create new voucher | ✅ | tenant |
| `PUT` | `/v1/tenant/vouchers/{id}` | Update voucher | ✅ | tenant |
| `DELETE` | `/v1/tenant/vouchers/{id}` | Delete voucher | ✅ | tenant |
| `POST` | `/v1/tenant/redemption/validate` | Validate voucher | ✅ | tenant |

---

## 6. RVM Machines

### Public Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/v1/rvm-machines` | List all RVM machines |
| `GET` | `/v1/rvm-machines/{id}` | Get machine details |

### Protected Endpoints (Role: Admin/Operator)
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/v1/rvm-machines` | List machines | ✅ |
| `POST` | `/v1/rvm-machines` | Create machine | ✅ |
| `GET` | `/v1/rvm-machines/{id}` | Get machine | ✅ |
| `PUT` | `/v1/rvm-machines/{id}` | Update machine | ✅ |
| `DELETE` | `/v1/rvm-machines/{id}` | Delete machine | ✅ |

---

## 7. Edge Devices & IoT (Protected)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `POST` | `/v1/edge/register` | Register edge device | ✅ |
| `GET` | `/v1/edge/model-sync` | Sync ML model | ✅ |
| `POST` | `/v1/edge/update-location` | Update device location | ✅ |
| `POST` | `/v1/edge/upload-image` | Upload image | ✅ |
| `GET` | `/v1/edge/devices` | List edge devices | ✅ |
| `POST` | `/v1/devices/{id}/telemetry` | Send telemetry data | ✅ |
| `POST` | `/v1/devices/{id}/heartbeat` | Device heartbeat | ✅ |
| `GET` | `/v1/edge/download-model/{hash}` | Download ML model | ✅ |

---

## 8. Transactions (Protected)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `POST` | `/v1/transactions/session` | Create session | ✅ |
| `POST` | `/v1/transactions/start` | Start transaction | ✅ |
| `POST` | `/v1/transactions/item` | Deposit item | ✅ |
| `POST` | `/v1/transactions/commit` | Commit transaction | ✅ |
| `POST` | `/v1/transactions/cancel` | Cancel transaction | ✅ |
| `GET` | `/v1/transactions/history` | Get history | ✅ |
| `GET` | `/v1/transactions/active` | Get active session | ✅ |
| `GET` | `/v1/transactions/{id}` | Get transaction detail | ✅ |

---

## 9. Redemption (Protected)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `POST` | `/v1/redemption/redeem` | Redeem voucher | ✅ |
| `GET` | `/v1/redemption/vouchers` | Get user vouchers | ✅ |
| `GET` | `/v1/redemption/voucher/{code}` | Get voucher detail | ✅ |

---

## 10. CV Server Integration (Protected)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `POST` | `/v1/cv/upload-model` | Upload ML model | ✅ |
| `POST` | `/v1/cv/training-complete` | Training complete callback | ✅ |
| `GET` | `/v1/cv/datasets/{id}` | Get dataset | ✅ |
| `GET` | `/v1/cv/download-model/{versionOrHash}` | Download model | ✅ |
| `POST` | `/v1/cv/playground-inference` | Test inference | ✅ |
| `GET` | `/v1/cv/training-jobs` | List training jobs | ✅ |
| `GET` | `/v1/cv/models` | List ML models | ✅ |

---

## 11. Technician & Maintenance (Protected)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/v1/technician/assignments` | Get assignments | ✅ |
| `POST` | `/v1/technician/generate-pin` | Generate PIN | ✅ |
| `POST` | `/v1/technician/validate-pin` | Validate PIN | ✅ |

---

## 12. System Logs (Protected, Role-Based)

| Method | Endpoint | Description | Auth | Roles |
|--------|----------|-------------|------|-------|
| `GET` | `/v1/logs` | Get activity logs | ✅ | super_admin, admin, operator, teknisi |
| `GET` | `/v1/logs/stats` | Get log statistics | ✅ | super_admin, admin, operator, teknisi |

---

## 13. Admin Endpoints (Public - for Dashboard)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/v1/admin/users` | Get all users |
| `GET` | `/v1/admin/users/{id}/stats` | Get user stats |

---

## 14. Dashboard API (Web Auth)

> These endpoints use web session auth instead of Sanctum Bearer token.

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/api/v1/dashboard/users` | Get all users | Web Session |
| `GET` | `/api/v1/dashboard/users/{id}/stats` | Get user stats | Web Session |
| `GET` | `/api/v1/dashboard/machines` | Get machines | Web Session |
| `GET` | `/api/v1/dashboard/machines/{id}` | Get machine detail | Web Session |
| `GET` | `/api/v1/dashboard/devices` | Get edge devices | Web Session |

---

## Authentication Methods

### Bearer Token (Sanctum)
```
Authorization: Bearer <token>
```

### Web Session (Cookie-based)
Used for dashboard internal API calls with CSRF token.

---

## Role-Based Access

| Role | Access Level |
|------|--------------|
| `super_admin` | Full access |
| `admin` | Full access |
| `operator` | Machine management |
| `teknisi` | Maintenance tasks |
| `tenant` | Voucher management |
| `user` | Basic user features |

---

## Total Endpoints Summary

| Category | Count |
|----------|-------|
| Authentication | 6 |
| User Profile | 4 |
| Vouchers | 6 |
| RVM Machines | 7 |
| Edge Devices | 7 |
| Transactions | 8 |
| Redemption | 3 |
| CV Server | 7 |
| Technician | 3 |
| Logs | 2 |
| Admin | 2 |
| Dashboard API | 5 |
| **TOTAL** | **60** |

---

## Changelog

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 11 Jan 2026 3:02 PM | Initial documentation |
