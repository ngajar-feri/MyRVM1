# MyRVM-Server - API Testing Guide & Next Steps

**Date**: 10 Januari 2026  
**Status**: Ready for Testing  
**Server URL**: `http://localhost:8000` (atau sesuai Docker config)

---

## 🧪 Quick Start Testing

### 1. Test Login (Dashboard)
**URL**: http://localhost:8000/login

**Demo Accounts**:
```
Super Admin: superadmin@myrvm.com / password123
Admin: admin@myrvm.com / password123
Operator: operator@myrvm.com / password123
User: john@example.com / password123
Tenant: tenant@starbucks.com / password123
Technician: tech@myrvm.com / password123
```

**Expected**: Dashboard accessible setelah login

---

### 2. Test API Authentication

#### Register New User
```bash
POST http://localhost:8000/api/v1/register
Content-Type: application/json

{
  "name": "Test User",
  "email": "test@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Expected Response**:
```json
{
  "status": "success",
  "message": "Registrasi berhasil",
  "data": {
    "user": { ... },
    "token": "1|xxxxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

#### Login
```bash
POST http://localhost:8000/api/v1/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123",
  "device_name": "PostmanTest"
}
```

**Save the token** dari response untuk request selanjutnya!

---

## 📋 Complete API Test Suite

### Authentication & User APIs

#### 1. Get User Profile
```bash
GET http://localhost:8000/api/v1/me
Authorization: Bearer {your_token}
```

#### 2. Update Profile
```bash
PUT http://localhost:8000/api/v1/profile
Authorization: Bearer {your_token}
Content-Type: application/json

{
  "name": "Updated Name",
  "email": "john@example.com"
}
```

#### 3. Change Password
```bash
PUT http://localhost:8000/api/v1/change-password
Authorization: Bearer {your_token}
Content-Type: application/json

{
  "current_password": "password123",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}
```

#### 4. Get Balance
```bash
GET http://localhost:8000/api/v1/user/balance
Authorization: Bearer {your_token}
```

**Expected**: Points balance dengan total earned/redeemed

---

### Transaction APIs

#### 5. Create QR Session
```bash
POST http://localhost:8000/api/v1/transactions/session
Authorization: Bearer {your_token}
Content-Type: application/json

{
  "rvm_id": 1
}
```

**Expected**: QR code (base64) + session info

#### 6. Start Transaction
```bash
POST http://localhost:8000/api/v1/transactions/start
Authorization: Bearer {your_token}
Content-Type: application/json

{
  "rvm_id": 1
}
```

**Save transaction_id** dari response!

#### 7. Deposit Item
```bash
POST http://localhost:8000/api/v1/transactions/item
Authorization: Bearer {your_token}
Content-Type: application/json

{
  "transaction_id": 1,
  "waste_type": "PET Bottle",
  "weight": 0.05,
  "points": 50
}
```

#### 8. Commit Transaction
```bash
POST http://localhost:8000/api/v1/transactions/commit
Authorization: Bearer {your_token}
Content-Type: application/json

{
  "transaction_id": 1
}
```

**Expected**: Points ditambahkan ke user balance

#### 9. Get Transaction History
```bash
GET http://localhost:8000/api/v1/transactions/history?page=1&per_page=10&status=completed
Authorization: Bearer {your_token}
```

#### 10. Get Transaction Detail
```bash
GET http://localhost:8000/api/v1/transactions/1
Authorization: Bearer {your_token}
```

#### 11. Get Active Session
```bash
GET http://localhost:8000/api/v1/transactions/active
Authorization: Bearer {your_token}
```

#### 12. Cancel Transaction
```bash
POST http://localhost:8000/api/v1/transactions/cancel
Authorization: Bearer {your_token}
Content-Type: application/json

{
  "transaction_id": 1
}
```

---

### Edge Device APIs

#### 13. Register Edge Device
```bash
POST http://localhost:8000/api/v1/edge/register
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "device_serial": "JETSON-TEST-001",
  "rvm_id": 1,
  "tailscale_ip": "100.64.0.10",
  "hardware_info": {
    "cpu": "ARM Cortex-A78AE",
    "gpu": "NVIDIA Ampere",
    "memory_gb": 8
  }
}
```

**Save api_key** dari response!

#### 14. Check Model Sync
```bash
GET http://localhost:8000/api/v1/edge/model-sync?device_serial=JETSON-TEST-001&current_version=v1.0&model_name=yolo11
Authorization: Bearer {admin_token}
```

#### 15. Update Location
```bash
POST http://localhost:8000/api/v1/edge/update-location
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "edge_device_id": 1,
  "latitude": -6.1944,
  "longitude": 106.8229,
  "location_source": "gps_module",
  "accuracy_meters": 10,
  "address": "Jakarta Pusat"
}
```

#### 16. Upload Image
```bash
POST http://localhost:8000/api/v1/edge/upload-image
Authorization: Bearer {admin_token}
Content-Type: multipart/form-data

original_image: [file]
processed_image: [file]
mask_image: [file]
metadata: {"session_id": "uuid", "item_sequence": 1, "detected_class": "PET Bottle", "confidence": 0.95}
```

---

### CV Integration APIs

#### 17. Upload Model
```bash
POST http://localhost:8000/api/v1/cv/upload-model
Authorization: Bearer {admin_token}
Content-Type: multipart/form-data

model_name: yolo11
version: v3.2
model_file: [file .pt]
metrics: {"mAP": 0.95, "precision": 0.93}
```

#### 18. Training Complete Callback
```bash
POST http://localhost:8000/api/v1/cv/training-complete
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "training_job_id": 1,
  "status": "success",
  "metrics": {"loss": 0.05, "accuracy": 0.98}
}
```

#### 19. Download Model
```bash
GET http://localhost:8000/api/v1/cv/download-model/v3.2
Authorization: Bearer {admin_token}
```

**Expected**: File download stream

---

### Redemption APIs

#### 20. Get User Vouchers
```bash
GET http://localhost:8000/api/v1/redemption/vouchers
Authorization: Bearer {your_token}
```

#### 21. Get Voucher Detail
```bash
GET http://localhost:8000/api/v1/redemption/voucher/ABC123XYZ
Authorization: Bearer {your_token}
```

---

## 🔧 Postman Collection Structure

### Environment Variables
```
base_url: http://localhost:8000
api_base: {{base_url}}/api/v1
user_token: (akan diisi setelah login)
admin_token: (akan diisi setelah login admin)
transaction_id: (akan diisi setelah start transaction)
```

### Collections
1. **Authentication**
   - Register
   - Login
   - Logout
   - Forgot Password
   - Reset Password
   - Get Profile

2. **Transactions**
   - Create Session
   - Start
   - Deposit Item
   - Commit
   - Cancel
   - History
   - Detail
   - Active Session

3. **Edge Device**
   - Register
   - Model Sync
   - Update Location
   - Upload Image
   - Heartbeat
   - Telemetry

4. **CV Integration**
   - Upload Model
   - Training Complete
   - Get Dataset
   - Download Model
   - Playground Inference

5. **User Management**
   - Update Profile
   - Change Password
   - Upload Photo
   - Get Balance

6. **Redemption**
   - Get Vouchers
   - Get Voucher Detail
   - Redeem

---

## 🎯 Integration Testing Scenarios

### Scenario 1: Complete Transaction Flow (RVM-User)
1. ✅ Login user
2. ✅ Create QR session
3. ✅ Get active session
4. ✅ Check balance (before)
5. ✅ Start transaction
6. ✅ Deposit items (multiple)
7. ✅ Commit transaction
8. ✅ Check balance (after)
9. ✅ View history

**Expected**: Points increased, transaction in history

---

### Scenario 2: Edge Device Registration & Sync (RVM-Edge)
1. ✅ Register device
2. ✅ Check model sync
3. ✅ Download model (if available)
4. ✅ Update location
5. ✅ Send heartbeat
6. ✅ Upload images

**Expected**: Device registered, can sync models

---

### Scenario 3: Model Training Pipeline (RVM-CV)
1. ✅ Upload trained model
2. ✅ Training complete callback
3. ✅ Edge device checks sync
4. ✅ Edge downloads new model

**Expected**: Model version updated, Edge can download

---

## ⚠️ Common Issues & Solutions

### Issue 1: Token Expired
**Error**: `Unauthenticated`  
**Solution**: Login lagi untuk mendapatkan token baru

### Issue 2: 404 Not Found
**Error**: `Route not found`  
**Check**:
- URL correct? `/api/v1/...`
- Method correct? (GET/POST/PUT)
- Server running?

### Issue 3: Validation Error
**Error**: `422 Unprocessable Entity`  
**Solution**: Check request body sesuai dengan validation rules

### Issue 4: Image Upload Fails
**Error**: `File too large`  
**Solution**: 
- Cek file size (max 5MB untuk images)
- Format: JPEG untuk photos, PNG untuk masks

### Issue 5: Migration Not Run
**Error**: `Table doesn't exist`  
**Solution**: Run migrations
```bash
docker-compose exec app php artisan migrate --force
```

---

## 📦 Storage Testing

### Test File Upload
1. Profile photo upload
2. Bottle image upload (3 files)
3. Model file upload

### Check Storage
```bash
# List uploaded files
ls -la storage/app/public/profile-photos/
ls -la storage/app/public/images/
ls -la storage/app/public/models/
```

---

## 🚀 Deployment Checklist

### Pre-Production
- [ ] All APIs tested manually
- [ ] Authentication working
- [ ] File uploads working
- [ ] Database migrations executed
- [ ] Seeders run successfully
- [ ] .env configured for production
- [ ] Storage linked: `php artisan storage:link`

### Production Setup
- [ ] PostgreSQL configured
- [ ] MinIO/S3 configured
- [ ] Email service (SMTP)
- [ ] SSL certificates
- [ ] Domain configured
- [ ] Backup strategy
- [ ] Monitoring setup
- [ ] Rate limiting enabled
- [ ] Logs configured

### Security
- [ ] Change all default passwords
- [ ] API keys rotated
- [ ] CORS properly configured
- [ ] Environment variables secured
- [ ] SQL injection testing
- [ ] XSS prevention tested

---

## 📊 Performance Testing

### Load Testing Endpoints
```bash
# Example with Apache Bench
ab -n 1000 -c 10 http://localhost:8000/api/v1/transactions/history
```

**Target Performance**:
- Response time: < 200ms (average)
- Throughput: > 100 req/sec
- Error rate: < 0.1%

---

## 🔄 Next Development Phases

### Phase 1: RVM-User Mobile App (Week 1-2)
- [ ] Flutter app setup
- [ ] Authentication screens
- [ ] QR scanner integration
- [ ] Transaction history UI
- [ ] Points & vouchers screen
- [ ] Profile management

### Phase 2: RVM-Edge Firmware (Week 2-3)
- [ ] Device registration flow
- [ ] Model sync & download
- [ ] Image capture & upload
- [ ] Transaction integration
- [ ] LED status indicators
- [ ] Local AI processing

### Phase 3: RVM-CV Training Server (Week 3-4)
- [ ] Training pipeline setup
- [ ] Model upload automation
- [ ] Callback integration
- [ ] Dataset management
- [ ] Metrics tracking

### Phase 4: Production Beta (Week 4-6)
- [ ] Integration testing
- [ ] Bug fixes
- [ ] Performance optimization
- [ ] User acceptance testing
- [ ] Documentation finalization

---

## 📖 Additional Resources

### API Documentation
- Postman Collection: (to be created)
- Swagger Doc: http://localhost:8000/api/documentation (in progress)
- OpenAPI Spec: storage/api-docs/api-docs.json

### Database
- Schema Diagram: (to be created)
- Migration Files: database/migrations/
- Seeders: database/seeders/

### Code References
- Controllers: app/Http/Controllers/Api/
- Models: app/Models/
- Routes: routes/api.php

---

## ✅ Testing Checklist

### API Testing
- [ ] Authentication APIs (6 endpoints)
- [ ] Transaction APIs (8 endpoints)
- [ ] Edge Device APIs (8 endpoints)
- [ ] CV Integration APIs (5 endpoints)
- [ ] User Management APIs (4 endpoints)
- [ ] Redemption APIs (3 endpoints)

### Integration Testing
- [ ] Complete transaction flow
- [ ] Edge device registration
- [ ] Model sync workflow
- [ ] File upload/download
- [ ] Error handling
- [ ] Edge cases

### Security Testing
- [ ] Authentication required
- [ ] Authorization (roles)
- [ ] Input validation
- [ ] SQL injection prevention
- [ ] XSS prevention
- [ ] CSRF protection

---

**Status**: ✅ **READY FOR TESTING**

Semua endpoint sudah siap ditest. Mulai dengan login, lalu test transaction flow, kemudian edge device integration!

**Happy Testing!** 🧪🚀
