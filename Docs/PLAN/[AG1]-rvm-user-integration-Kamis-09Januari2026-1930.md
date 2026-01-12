Versi Dokumen: 1.0
Tanggal Revisi: Kamis-09 Januari 2026 - 07:30 PM
Tujuan: Mendokumentasikan rencana pengembangan dan integrasi aplikasi mobile RVM-User (Flutter) dengan RVM-Server untuk transaksi, reward management, dan interaksi dengan mesin RVM.
Status: Belum

# Rencana Integrasi RVM-User Apps dengan RVM-Server

## 1. Pendahuluan

Dokumen ini merinci rencana pengembangan aplikasi mobile **RVM-User**, yaitu aplikasi Flutter cross-platform (iOS + Android) untuk end-user yang berinteraksi dengan mesin RVM. Aplikasi ini bertanggung jawab untuk:
- User authentication dan profile management
- QR code generation untuk RVM session
- Real-time transaction monitoring
- Reward points dan voucher management
- Transaction history
- Notification system

**Target Users**: End users yang menggunakan mesin RVM untuk deposit botol dan mendapatkan reward.

## 2. Arsitektur Sistem

### 2.1. Komponen RVM-User Apps
```
┌─────────────────────────────────────────────────┐
│       RVM-User Mobile App (Flutter)            │
│                                                 │
│  ┌──────────────────┐    ┌─────────────────┐  │
│  │ Authentication   │    │  QR Generator   │  │
│  │ (Sanctum Token)  │    │  (Session Code) │  │
│  └──────────────────┘    └─────────────────┘  │
│                                                 │
│  ┌──────────────────┐    ┌─────────────────┐  │
│  │ Transaction View │    │  Rewards        │  │
│  │ (Real-time)      │    │  Management     │  │
│  └──────────────────┘    └─────────────────┘  │
│                                                 │
│  ┌──────────────────┐    ┌─────────────────┐  │
│  │ WebSocket Client │    │  Push Notif     │  │
│  │ (Live Updates)   │    │  (FCM)          │  │
│  └──────────────────┘    └─────────────────┘  │
└─────────────────────────────────────────────────┘
           │
           │ HTTPS + WebSocket
           ▼
    ┌─────────────────┐
    │  RVM-Server     │
    │  (vm100)        │
    │  Laravel API    │
    └─────────────────┘
```

### 2.2. User Journey Flow (Shopping Cart Pattern)
```
1. User download app → Register/Login
2. User tap "Start Transaction"
3. App generate unique QR code (session_id, valid 5 menit)
4. User pergi ke RVM → Scan QR di touchscreen
5. RVM-Edge validate session:
   → Screen: "Selamat Datang, [User Name]!"
   → LED: Biru Blinking (session active)
6. User masukkan botol (multiple items seperti shopping cart):
   → Setiap botol: AI process → Auto accept/reject
   → LED Flash: Hijau (accepted) / Merah (rejected)
   → Screen update running total real-time
   → App receive WebSocket event per item
7. User tekan "Selesai" di touchscreen (atau timeout 5 menit):
   → Commit transaction ke Server
   → LED: Hijau Solid
8. User receive notification di app:
   → "Transaksi selesai! +150 poin"
   → Summary: 3 items accepted, total Rp 1,500
9. App display updated balance
10. User bisa redeem points untuk voucher
11. User gunakan voucher di tenant
```

**Opsi Session Ending**:
- **Normal**: User tekan "Selesai" → Commit → Show summary → Return idle
- **Cancel**: User tekan "Batal" → Konfirmasi → Cancel session → Return idle
- **Timeout**: Session idle 5 menit → Auto-commit (jika ada items) → Return idle

## 3. Spesifikasi Teknis

### 3.1. Platform & Dependencies
- **Framework**: Flutter 3.16+
- **Platform**: iOS 13+, Android 8+ (API 26+)
- **State Management**: Riverpod / Bloc
- **HTTP Client**: Dio
- **WebSocket**: socket_io_client
- **QR Code**: qr_flutter
- **Push Notifications**: firebase_messaging
- **Local Storage**: Hive / shared_preferences
- **Camera**: camera package (untuk scan voucher)

### 3.2. UI/UX Design Principles
- **Material Design 3** untuk Android
- **Cupertino** untuk iOS (adaptive widgets)
- **Dark Mode** support
- **Localization**: Indonesia + English
- **Accessibility**: Screen reader support, large fonts

## 4. Fitur Utama Aplikasi

### 4.1. Authentication & Profile
| Fitur                  | Deskripsi                                      |
|------------------------|------------------------------------------------|
| Register               | Email + password, phone verification (OTP)     |
| Login                  | Email/phone + password, biometric (fingerprint)|
| Social Login           | Google, Apple Sign In (opsional Phase 2)       |
| Forgot Password        | Email reset link                               |
| Profile Edit           | Name, photo, phone, address                    |
| Account Settings       | Change password, notification preferences      |

### 4.2. Transaction Features
| Fitur                  | Deskripsi                                      |
|------------------------|------------------------------------------------|
| QR Code Session        | Generate QR untuk start transaksi di RVM       |
| Live Transaction       | Real-time update via WebSocket saat deposit    |
| Transaction History    | List semua transaksi (filter by date, status)  |
| Transaction Detail     | Detail per botol (type, points, image)         |
| Receipt Export         | PDF/Image export untuk bukti transaksi         |

### 4.3. Rewards & Vouchers
| Fitur                  | Deskripsi                                      |
|------------------------|------------------------------------------------|
| Points Balance         | Display current points + gold (jika ada)       |
| Redeem Voucher         | Exchange points untuk voucher tenant           |
| Voucher List           | Active vouchers, expired, used                 |
| Voucher QR             | Generate QR untuk scan di tenant               |
| Voucher History        | Usage history                                  |

### 4.4. Notifications
| Fitur                  | Deskripsi                                      |
|------------------------|------------------------------------------------|
| Transaction Complete   | "Transaksi selesai! +500 poin"                 |
| Voucher Available      | "Voucher baru tersedia dari Tenant X"          |
| Points Expiring        | "100 poin akan hangus dalam 7 hari"            |
| Promo Notification     | Marketing push dari admin                      |

## 5. API Integration dengan RVM-Server

### 5.1. API Endpoints yang Digunakan (RVM-Server)

#### Authentication
| Method | Endpoint                    | Deskripsi                    |
|--------|-----------------------------|------------------------------|
| `POST` | `/api/v1/register`          | User registration            |
| `POST` | `/api/v1/login`             | User login (return token)    |
| `POST` | `/api/v1/logout`            | Logout (revoke token)        |
| `POST` | `/api/v1/forgot-password`   | Send reset email             |
| `POST` | `/api/v1/reset-password`    | Reset password dengan token  |

#### Profile
| Method | Endpoint                    | Deskripsi                    |
|--------|-----------------------------|------------------------------|
| `GET`  | `/api/v1/user/profile`      | Get user profile             |
| `PUT`  | `/api/v1/user/profile`      | Update profile               |
| `POST` | `/api/v1/user/upload-photo` | Upload profile photo         |

#### Transactions
| Method | Endpoint                           | Deskripsi                          |
|--------|------------------------------------|------------------------------------|
| `POST` | `/api/v1/transactions/session`     | Create QR session untuk RVM        |
| `GET`  | `/api/v1/transactions/history`     | Get transaction history            |
| `GET`  | `/api/v1/transactions/{id}`        | Get transaction detail             |
| `GET`  | `/api/v1/transactions/active`      | Get active session (if any)        |

#### Rewards
| Method | Endpoint                           | Deskripsi                          |
|--------|------------------------------------|------------------------------------|
| `GET`  | `/api/v1/user/balance`             | Get points balance                 |
| `POST` | `/api/v1/redemption/redeem`        | Redeem voucher                     |
| `GET`  | `/api/v1/redemption/vouchers`      | Get user vouchers                  |
| `GET`  | `/api/v1/redemption/voucher/{code}`| Get voucher detail                 |

#### WebSocket
| Event                  | Deskripsi                                      |
|------------------------|------------------------------------------------|
| `transaction.started`  | Session dimulai di RVM                         |
| `transaction.item`     | Item detected dan diproses                     |
| `transaction.completed`| Transaction selesai, points added              |
| `voucher.redeemed`     | Voucher berhasil digunakan                     |

## 6. Database Schema Changes (RVM-Server)

### 6.1. Update Tabel: `users`
Tambahkan kolom untuk mobile app:
```sql
ALTER TABLE users
ADD COLUMN phone_number VARCHAR(20) UNIQUE,
ADD COLUMN phone_verified_at TIMESTAMP,
ADD COLUMN fcm_token VARCHAR(255), -- Firebase Cloud Messaging
ADD COLUMN notification_enabled BOOLEAN DEFAULT true,
ADD COLUMN language VARCHAR(10) DEFAULT 'id';
```

### 6.2. Tabel Baru: `user_sessions`
```sql
CREATE TABLE user_sessions (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT REFERENCES users(id),
    session_code VARCHAR(100) UNIQUE NOT NULL, -- QR code content
    rvm_id BIGINT REFERENCES reverse_vending_machines(id),
    status VARCHAR(20) DEFAULT 'pending', -- pending, active, completed, expired
    qr_generated_at TIMESTAMP DEFAULT NOW(),
    expires_at TIMESTAMP, -- Session valid 5 menit
    activated_at TIMESTAMP,
    completed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW()
);
```

### 6.3. Tabel Baru: `push_notifications`
```sql
CREATE TABLE push_notifications (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT REFERENCES users(id),
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    notification_type VARCHAR(50), -- 'transaction', 'voucher', 'promo'
    data JSONB, -- Additional payload
    sent_at TIMESTAMP DEFAULT NOW(),
    read_at TIMESTAMP
);
```

## 7. User Flow Detail

### 7.1. Registration Flow
```
1. User open app → Tap "Register"
2. Form: Name, Email, Phone, Password
3. Submit → Server validate → Create user
4. Send OTP ke phone
5. User input OTP → Verify
6. Registration complete → Auto login
7. Redirect ke Home screen
```

### 7.2. Start Transaction Flow
```
1. User tap "Start Transaction" button
2. App check GPS location (optional: nearby RVM)
3. App call POST /api/v1/transactions/session
   Request: { user_id, rvm_id (optional) }
4. Server generate session_code (UUID)
5. Server return: { session_id, qr_code_data, expires_at }
6. App display QR code full screen
7. User scan QR di RVM-Edge
8. RVM-Edge validate session → POST /api/v1/transactions/start
9. Server activate session → Send WebSocket event "transaction.started"
10. App receive event → Show "Transaction Active" screen
11. User deposit botol → App receive "transaction.item" events
12. Transaction complete → App show summary + points earned
```

### 7.3. Redeem Voucher Flow
```
1. User tap "Rewards" tab
2. Display available vouchers (carousel/grid)
3. User select voucher → Show detail + "Redeem" button
4. User tap Redeem
5. App call POST /api/v1/redemption/redeem
   Request: { voucher_id }
6. Server validate points → Deduct points → Generate unique code
7. Server return: { voucher_code, qr_data, tenant_info }
8. App display voucher QR code + code
9. User pergi ke tenant → Tenant scan QR
10. Tenant validate → POST /api/v1/redemption/validate
11. Server mark voucher as "used"
12. App receive notification "Voucher berhasil digunakan"
```

## 8. Struktur Project RVM-User

```
MyRVM-User/
├── lib/
│   ├── main.dart
│   ├── app.dart                    # Material App config
│   ├── core/
│   │   ├── constants/
│   │   │   ├── api_constants.dart  # API URLs
│   │   │   └── app_constants.dart  # App config
│   │   ├── theme/
│   │   │   ├── app_theme.dart      # Theme config
│   │   │   └── colors.dart         # Color palette
│   │   └── utils/
│   │       ├── date_utils.dart
│   │       └── validators.dart
│   ├── data/
│   │   ├── models/
│   │   │   ├── user_model.dart
│   │   │   ├── transaction_model.dart
│   │   │   └── voucher_model.dart
│   │   ├── repositories/
│   │   │   ├── auth_repository.dart
│   │   │   ├── transaction_repository.dart
│   │   │   └── reward_repository.dart
│   │   └── services/
│   │       ├── api_service.dart      # Dio HTTP client
│   │       ├── websocket_service.dart # Socket.io client
│   │       └── notification_service.dart # FCM
│   ├── presentation/
│   │   ├── screens/
│   │   │   ├── auth/
│   │   │   │   ├── login_screen.dart
│   │   │   │   ├── register_screen.dart
│   │   │   │   └── forgot_password_screen.dart
│   │   │   ├── home/
│   │   │   │   ├── home_screen.dart
│   │   │   │   └── qr_scanner_screen.dart
│   │   │   ├── transaction/
│   │   │   │   ├── transaction_active_screen.dart
│   │   │   │   ├── transaction_history_screen.dart
│   │   │   │   └── transaction_detail_screen.dart
│   │   │   ├── rewards/
│   │   │   │   ├── rewards_screen.dart
│   │   │   │   ├── voucher_list_screen.dart
│   │   │   │   └── voucher_detail_screen.dart
│   │   │   └── profile/
│   │   │       ├── profile_screen.dart
│   │   │       └── edit_profile_screen.dart
│   │   └── widgets/
│   │       ├── custom_button.dart
│   │       ├── custom_text_field.dart
│   │       ├── qr_code_widget.dart
│   │       └── transaction_card.dart
│   └── providers/
│       ├── auth_provider.dart        # Riverpod provider
│       ├── transaction_provider.dart
│       └── reward_provider.dart
├── assets/
│   ├── images/
│   ├── icons/
│   └── fonts/
├── android/                          # Android config
├── ios/                              # iOS config
├── pubspec.yaml
└── README.md
```

## 9. Rencana Pengujian (Staging/Testing)

### 9.1. Unit Testing
- Test API service calls (mock responses)
- Test model parsing (JSON to Dart objects)
- Test validators (email, phone, password)
- Test date/time utilities

### 9.2. Widget Testing
- Test login form validation
- Test QR code display
- Test transaction history list
- Test voucher card display

### 9.3. Integration Testing
- **Skenario 1: Full Transaction Cycle**
  1. Login dengan test user
  2. Generate QR code session
  3. Simulate RVM scan → WebSocket event
  4. Verify transaction updates real-time
  5. Check points balance updated
  
- **Skenario 2: Voucher Redemption**
  1. User memiliki 1000 points
  2. Redeem voucher (500 points)
  3. Verify points berkurang → 500
  4. Check voucher appear di "My Vouchers"
  5. Display voucher QR code

### 9.4. User Acceptance Testing (UAT)
- **Beta Testing**: 10-20 users
- **Focus Areas**:
  - Registration ease
  - QR scan experience di RVM fisik
  - Real-time update responsiveness
  - Voucher redemption flow
- **Feedback Collection**: In-app survey

## 10. Deployment Plan

### 10.1. Android Deployment
```bash
# 1. Build release APK
flutter build apk --release

# 2. Build App Bundle (Google Play)
flutter build appbundle --release

# 3. Upload ke Google Play Console
# - Internal testing → Closed testing → Open testing → Production

# 4. Configure Firebase
# - Add google-services.json
# - Setup FCM
```

### 10.2. iOS Deployment
```bash
# 1. Build release IPA
flutter build ipa --release

# 2. Upload ke App Store Connect via Xcode
# - Archive → Distribute App → App Store Connect

# 3. TestFlight Beta Testing
# - Internal testers (10-30 users)
# - External testers (100+ users)

# 4. App Store Review
# - Submit for review
# - Estimated 24-48 hours
```

### 10.3. Version Management
- **Versioning**: Semantic versioning (1.0.0, 1.1.0, 2.0.0)
- **Build Number**: Auto-increment per build
- **OTA Updates**: CodePush untuk minor updates (tanpa store review)

## 11. Security Considerations

### 11.1. Authentication Security
- **Token Storage**: Secure storage (Keychain iOS, Keystore Android)
- **Biometric Auth**: Face ID, Touch ID, Fingerprint
- **Session Timeout**: Auto logout setelah 30 menit idle
- **Certificate Pinning**: SSL pinning untuk API calls

### 11.2. Data Security
- **Encryption**: Encrypt sensitive data di local storage
- **No Logging**: Jangan log password atau token
- **API Key**: Obfuscate API keys di build

### 11.3. QR Code Security
- **Session Expiry**: QR code valid hanya 5 menit
- **One-time Use**: QR tidak bisa digunakan 2x
- **UUID**: Gunakan UUID v4 untuk session code

## 12. Performance Optimization

### 12.1. App Performance
- **Lazy Loading**: List pagination (10 items per page)
- **Image Caching**: Cache profile photos, voucher images
- **Debouncing**: Search input debounce 300ms
- **Code Splitting**: Lazy load screens

### 12.2. Network Optimization
- **API Caching**: Cache GET responses 5 menit
- **Retry Logic**: 3x retry dengan exponential backoff
- **Timeout**: 30s timeout untuk API calls
- **Compression**: Enable gzip compression

## 13. Monitoring & Analytics

### 13.1. Crash Reporting
- **Firebase Crashlytics**: Auto crash reporting
- **Sentry** (alternative): Error tracking

### 13.2. Analytics
- **Firebase Analytics**: Track user behavior
- **Events to Track**:
  - `screen_view`: Track screen visits
  - `transaction_started`: User generate QR
  - `transaction_completed`: Transaction success
  - `voucher_redeemed`: Voucher redemption
  - `login_success`: User login
  - `registration_complete`: New user

## 14. Changelog

| Tanggal            | Perubahan                                          | Author |
|--------------------|----------------------------------------------------|--------|
| 09-01-2026 19:30   | Pembuatan dokumen rencana integrasi RVM-User       | AG1    |

## 15. Rollback Plan

### Level 1: App Crash / Critical Bug
- **Gejala**: App crash on launch, critical feature broken
- **Action**:
  1. Hotfix release → Submit new version ke store
  2. Google Play: ~2 jam review
  3. App Store: ~24 jam review
  4. Untuk urgent fix: CodePush OTA update (jika applicable)
- **Estimated Downtime**: 2-24 jam (tergantung store review)

### Level 2: API Breaking Changes
- **Gejala**: Server update API, app tidak compatible
- **Action**:
  1. Server maintain API versioning (/api/v1, /api/v2)
  2. Force update mechanism jika API deprecated
  3. Display "Update Required" screen di app
- **Estimated Downtime**: 0 (graceful migration)

### Level 3: Authentication Issues
- **Gejala**: User tidak bisa login, token invalid
- **Action**:
  1. Force logout all users
  2. Clear local storage → Force re-login
  3. Server re-issue tokens
  4. Push notification: "Please login again"
- **Estimated Downtime**: 5-10 menit per user

### Level 4: Store Account Suspended
- **Gejala**: App removed from Play Store / App Store
- **Action**:
  1. Appeal ke store support
  2. Fix violation (policy, content, etc)
  3. Re-submit app
  4. Provide APK download link sementara (Android)
- **Estimated Downtime**: 3-7 hari (appeal process)

## 16. Timeline Estimasi

| Fase                          | Durasi   | Deliverable                          |
|-------------------------------|----------|--------------------------------------|
| Project setup & UI design     | 3 hari   | Figma design approved                |
| Authentication module         | 5 hari   | Login, register, profile ready       |
| Transaction module            | 7 hari   | QR gen, history, real-time updates   |
| Rewards module                | 5 hari   | Points, vouchers, redemption ready   |
| WebSocket integration         | 3 hari   | Real-time events working             |
| Push notifications            | 3 hari   | FCM integrated                       |
| Testing & bug fixes           | 5 hari   | All tests passed                     |
| Store submission              | 2 hari   | Submitted to Play Store & App Store  |
| **Total**                     | **33 hari** | Production apps live              |

## 17. Catatan Tambahan

- **Offline Mode**: Implement offline cache untuk transaction history (SQLite)
- **Multi-language**: Support Indonesia dan English dari awal
- **Accessibility**: Follow WCAG 2.1 guidelines untuk screen reader
- **Referral System** (Phase 2): User invite friends → Bonus points
- **Dark Mode**: Auto-detect system preference atau manual toggle

---

**Dokumen ini akan di-update seiring dengan progress pengembangan.**
