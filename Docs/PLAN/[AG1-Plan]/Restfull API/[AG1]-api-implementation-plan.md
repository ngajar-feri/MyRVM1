# Missing API Endpoints - Implementation Plan

**Date**: 10 Januari 2026  
**Goal**: Implement all missing API endpoints for complete RVM integration  
**Total Endpoints**: 23 APIs  
**Estimated Effort**: 40-50 hours

---

## Implementation Strategy

### Priority Tiers

**Tier 1 - Critical (MUST HAVE)**: 8 endpoints - Blocks basic functionality  
**Tier 2 - High Priority**: 8 endpoints - Needed for production  
**Tier 3 - Medium Priority**: 7 endpoints - Enhances user experience

### Implementation Order

1. **Phase 1** (Day 1-2): Tier 1 Critical APIs - Transaction & Session Management
2. **Phase 2** (Day 3-4): Tier 1 Critical APIs - Edge Device Management  
3. **Phase 3** (Day 5-7): Tier 2 High Priority - User Features
4. **Phase 4** (Day 8-10): Tier 2 & 3 - CV Integration & Advanced Features

---

## TIER 1: CRITICAL APIs (8 endpoints)

### 1. POST /api/v1/transactions/session
**Purpose**: Generate QR code session for RVM-User app  
**Controller**: `TransactionController@createSession`  
**Priority**: 🔴 CRITICAL  
**Effort**: 2 hours

**Request**:
```json
{
  "user_id": 123,  // From auth user
  "rvm_id": 5      // Optional - specific RVM or any
}
```

**Response**:
```json
{
  "status": "success",
  "data": {
    "session_id": 456,
    "session_code": "uuid-v4-string",
    "qr_code_data": "base64-encoded-qr-image",
    "expires_at": "2026-01-10T08:35:00Z",
    "expires_in_seconds": 300
  }
}
```

**Implementation**:
- Generate UUID for session_code
- Create user_sessions record
- Set expiry to NOW() + 5 minutes
- Generate QR code using simplesoftwareio/simple-qrcode
- Return session data

---

### 2. POST /api/v1/transactions/cancel
**Purpose**: Cancel active transaction session  
**Controller**: `TransactionController@cancel`  
**Priority**: 🔴 CRITICAL  
**Effort**: 1 hour

**Request**:
```json
{
  "transaction_id": 789
}
```

**Response**:
```json
{
  "status": "success",
  "message": "Transaction cancelled",
  "data": {
    "transaction_id": 789,
    "previous_status": "pending",
    "new_status": "cancelled",
    "items_count": 2
  }
}
```

**Implementation**:
- Validate transaction belongs to user
- Check transaction status (only pending can be cancelled)
- Update status to 'cancelled'
- Broadcast WebSocket event (if configured)

---

### 3. GET /api/v1/user/balance
**Purpose**: Get user points balance  
**Controller**: `UserController@balance`  
**Priority**: 🔴 CRITICAL  
**Effort**: 1 hour

**Response**:
```json
{
  "status": "success",
  "data": {
    "user_id": 123,
    "points_balance": 1500,
    "tier": "silver",
    "total_earned": 5000,
    "total_redeemed": 3500
  }
}
```

---

### 4. POST /api/v1/edge/register
**Purpose**: Register Edge device on first boot  
**Controller**: `EdgeDeviceController@register`  
**Priority**: 🔴 CRITICAL  
**Effort**: 3 hours

**Request**:
```json
{
  "device_serial": "JETSON-12345",
  "rvm_id": 5,
  "tailscale_ip": "100.64.0.5",
  "hardware_info": {
    "cpu": "ARM Cortex-A78AE",
    "gpu": "NVIDIA Ampere",
    "memory_gb": 8,
    "storage_gb": 64
  }
}
```

**Response**:
```json
{
  "status": "success",
  "data": {
    "edge_device_id": 10,
    "api_key": "generated-api-key",
    "config": {
      "telemetry_interval_seconds": 300,
      "heartbeat_interval_seconds": 60,
      "model_sync_interval_minutes": 30
    }
  }
}
```

---

### 5. GET /api/v1/edge/model-sync
**Purpose**: Check if new AI model available  
**Controller**: `EdgeDeviceController@modelSync`  
**Priority**: 🔴 CRITICAL  
**Effort**: 2 hours

**Request**: Query params
```
?device_serial=JETSON-12345&current_version=v3.1&model_name=yolo11
```

**Response - Update Available**:
```json
{
  "status": "success",
  "update_available": true,
  "data": {
    "model_name": "yolo11",
    "latest_version": "v3.2",
    "current_version": "v3.1",
    "file_path": "models/yolo11/v3.2/best.pt",
    "file_size_mb": 45.3,
    "sha256_hash": "abc123...",
    "download_url": "/api/v1/edge/download-model/abc123",
    "deployed_at": "2026-01-09T10:00:00Z"
  }
}
```

**Response - Up to Date**:
```json
{
  "status": "success",
  "update_available": false,
  "current_version": "v3.1"
}
```

---

### 6. GET /api/v1/transactions/history
**Purpose**: Get user transaction history  
**Controller**: `TransactionController@history`  
**Priority**: 🔴 CRITICAL  
**Effort**: 3 hours

**Request**: Query params
```
?page=1&per_page=20&status=completed&from_date=2026-01-01
```

**Response**:
```json
{
  "status": "success",
  "data": {
    "current_page": 1,
    "per_page": 20,
    "total": 150,
    "data": [
      {
        "id": 789,
        "rvm_machine_id": 5,
        "rvm_name": "RVM Mall Central", 
        "status": "completed",
        "total_items": 3,
        "total_points": 150,
        "total_value": 1500,
        "started_at": "2026-01-10T08:00:00Z",
        "completed_at": "2026-01-10T08:05:00Z"
      }
    ]
  }
}
```

---

### 7. GET /api/v1/transactions/{id}
**Purpose**: Get transaction detail with items  
**Controller**: `TransactionController@show`  
**Priority**: 🔴 CRITICAL  
**Effort**: 2 hours

**Response**:
```json
{
  "status": "success",
  "data": {
    "id": 789,
    "user": {
      "id": 123,
      "name": "John Doe"
    },
    "rvm_machine": {
      "id": 5,
      "name": "RVM Mall Central",
      "location": "Mall Cen tral Jakarta"
    },
    "status": "completed",
    "items": [
      {
        "id": 1,
        "waste_type": "PET Bottle",
        "weight": 0.05,
        "points": 50,
        "original_image_url": "...",
        "processed_image_url": "...",
        "created_at": "2026-01-10T08:01:00Z"
      }
    ],
    "totals": {
      "items": 3,
      "weight": 0.15,
      "points": 150,
      "value": 1500
    },
    "timestamps": {
      "started_at": "2026-01-10T08:00:00Z",
      "completed_at": "2026-01-10T08:05:00Z"
    }
  }
}
```

---

### 8. POST /api/v1/edge/upload-image
**Purpose**: Upload bottle images to MinIO  
**Controller**: `EdgeDeviceController@uploadImage`  
**Priority**: 🔴 CRITICAL  
**Effort**: 4 hours

**Request**: multipart/form-data
```
original_image: File (JPEG)
processed_image: File (JPEG)
mask_image: File (PNG)
metadata: JSON string
```

**Metadata JSON**:
```json
{
  "session_id": "uuid",
  "item_sequence": 1,
  "captured_at": "2026-01-10T08:01:00Z",
  "detected_class": "PET Bottle",
  "confidence": 0.95
}
```

**Response**:
```json
{
  "status": "success",
  "data": {
    "original_url": "https://minio.../raw/2026-01-10/session-uuid/item-1-original.jpg",
    "processed_url": "https://minio.../processed/2026-01-10/session-uuid/item-1-annotated.jpg",
    "mask_url": "https://minio.../masks/2026-01-10/session-uuid/item-1-mask.png",
    "uploaded_at": "2026-01-10T08:01:30Z"
  }
}
```

---

## TIER 2: HIGH PRIORITY APIs (8 endpoints)

### 9. POST /api/v1/forgot-password
**Priority**: 🟡 HIGH | **Effort**: 2 hours

### 10. POST /api/v1/reset-password
**Priority**: 🟡 HIGH | **Effort**: 1 hour

### 11. GET /api/v1/transactions/active
**Priority**: 🟡 HIGH | **Effort**: 1 hour

### 12. POST /api/v1/edge/update-location
**Priority**: 🟡 HIGH | **Effort**: 2 hours

### 13. GET /api/v1/redemption/vouchers
**Priority**: 🟡 HIGH | **Effort**: 2 hours

### 14. POST /api/v1/cv/upload-model
**Priority**: 🟡 HIGH | **Effort**: 3 hours

### 15. POST /api/v1/cv/training-complete
**Priority**: 🟡 HIGH | **Effort**: 3 hours

### 16. GET /api/v1/edge/download-model/{hash}
**Priority**: 🟡 HIGH | **Effort**: 3 hours

---

## TIER 3: MEDIUM PRIORITY APIs (7 endpoints)

### 17. POST /api/v1/user/upload-photo
**Priority**: 🟢 MEDIUM | **Effort**: 2 hours

### 18. GET /api/v1/redemption/voucher/{code}
**Priority**: 🟢 MEDIUM | **Effort**: 1 hour

### 19. GET /api/v1/cv/datasets/{id}
**Priority**: 🟢 MEDIUM | **Effort**: 2 hours

### 20. GET /api/v1/cv/download-model/{version}
**Priority**: 🟢 MEDIUM | **Effort**: 2 hours

### 21. POST /api/v1/cv/job-status
**Priority**: 🟢 MEDIUM | **Effort**: 2 hours

### 22. POST /api/v1/cv/playground-inference
**Priority**: 🟢 MEDIUM | **Effort**: 3 hours

---

## Implementation Checklist

### Per Endpoint Tasks
- [ ] Create/update controller method
- [ ] Add route to routes/api.php
- [ ] Create FormRequest for validation
- [ ] Write business logic
- [ ] Add to API documentation
- [ ] Write feature test
- [ ] Test manually with Postman

### Supporting Infrastructure
- [ ] Install simple-qrcode package (for QR generation)
- [ ] Configure MinIO filesystem driver
- [ ] Create MinIO buckets (images/raw, images/processed, masks, models)
- [ ] Setup password reset mail template
- [ ] Configure WebSocket events (optional for Phase 1)

---

## Timeline

**Week 1**:
- Mon-Tue: Tier 1 Critical (APIs 1-8) 
- Wed-Thu: Tier 2 High Priority (APIs 9-16)
- Fri: Tier 3 Medium Priority (APIs 17-23)

**Deliverable**: All 23 APIs functional and tested

---

## Next Steps

1. ✅ Get user approval on this plan
2. Start with Tier 1 implementation
3. Test each API as completed
4. Move to next tier
5. Final integration testing

---

**Ready to start implementation immediately upon approval.**
