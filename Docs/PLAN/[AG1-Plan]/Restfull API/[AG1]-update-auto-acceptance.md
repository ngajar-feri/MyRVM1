# Update: Automatic Acceptance Flow - RVM-Edge

## Changes Made

### 1. LCD Touchscreen UI Flow - Automated Decision
**Old**: Manual Accept/Reject buttons yang memerlukan user interaction
**New**: Automatic decision berdasarkan AI confidence threshold (75%)

**Flow Baru**:
```
AI Processing → 
  IF confidence >= 75% → Auto ACCEPTED (hijau, 2 detik) → Motor open
  IF confidence < 75% → Auto REJECTED (merah, 2 detik) → Return to input
→ Send data ke Server
→ Display transaction summary
```

### 2. API Payload Specification

Ditambahkan spesifikasi lengkap untuk `/api/v1/transactions/item`:

#### Data yang Dikirim ke RVM-Server:
**Core Data (Implementasi Awal)**:
- `status`: "accepted" / "rejected"
- `confidence`: 0.87 (AI confidence score)
- `detected_class`: "PET_bottle"
- `image_url`: Path ke MinIO
- `bounding_box`, `segmentation_mask`
- `model_version` & `model_hash`

**Recommended Future Fields**:
- **Physical Measurements**: `estimated_weight`, `estimated_volume`
- **Quality Metrics**: `is_damaged`, `contamination_level`, `label_readable`
- **Environmental Data**: `temperature`, `humidity`
- **Material Details**: `color_detected`, `material_type`

#### Data yang Diterima dari RVM-Server:
**Core Response**:
- `points_earned`: 50 poin
- `monetary_value`: Rp 500
- `session_summary`: Total items, points, value
- `user.current_balance`: Updated balance

**Recommended Future Fields**:
- **Fraud Detection**: `requires_review`, `confidence_mismatch`, `duplicate_item`
- **Gamification**: `user.tier` (bronze/silver/gold), bonus multipliers
- **Promo System**: `bonus.type`, `points_multiplier`, promotional messages

### 3. Benefits

#### User Experience:
- ✅ Faster transaction (no manual button press needed)
- ✅ Clear visual feedback (green ✓ / red ✗)
- ✅ Consistent decision making (no human error)

#### System:
- ✅ Configurable threshold dari Server
- ✅ Comprehensive data logging untuk fraud detection
- ✅ Future-ready dengan gamification & promo support
- ✅ Quality metrics untuk continuous improvement

### 4. Configuration

**Confidence Threshold**: Configurable via RVM-Server dashboard
- Default: 75%
- Admin dapat adjust per RVM atau global
- Real-time update via WebSocket

**Display Duration**: 
- Accepted message: 2 detik (hijau)
- Rejected message: 2 detik (merah)

### 5. Integration Points

**RVM-Edge**:
- Auto-decision logic setelah AI inference
- Image upload ke MinIO sebelum submit item
- Display transaction summary dari server response

**RVM-Server**:
- Calculate points & monetary value
- Fraud detection checks
- Update user balance
- Send real-time update ke RVM-User app via WebSocket

**RVM-User App**:
- Real-time notification saat item accepted
- Transaction summary update
- Bonus/promo notifications

---

*Updated: 09 Januari 2026 - 08:05 PM*
