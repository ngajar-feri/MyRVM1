Versi Dokumen: 1.0
Tanggal Revisi: Kamis-09 Januari 2026 - 07:30 PM
Tujuan: Mendokumentasikan rencana pengembangan dan integrasi komponen RVM-CV (vm102 GPU Computing Server) dengan RVM-Server untuk AI model training, remote inference, dan GPU-accelerated processing.
Status: Belum

# Rencana Integrasi RVM-CV dengan RVM-Server

## 1. Pendahuluan

Dokumen ini merinci rencana pengembangan komponen **RVM-CV**, yaitu dedicated GPU computing server (vm102) yang bertanggung jawab untuk:
- AI model training (YOLO11 + SAM2) untuk menghasilkan best.pt
- Remote GPU-accelerated inference yang di-trigger oleh RVM-Server (bukan oleh RVM-Edge)
- Fraud detection dan playground testing untuk gambar dari MinIO
- Batch processing untuk dataset management
- **Pure computation** tanpa permanent storage (stateless architecture)
- Model versioning dan deployment ke RVM-Edge fleet

**Prinsip Utama**: 
1. RVM-CV adalah **stateless compute node** - semua data (dataset, model, hasil) di-download dari RVM-Server (MinIO), diproses di memory/temp storage, hasil di-upload kembali, lalu temp data dihapus.
2. **RVM-Server adalah controller** - RVM-CV tidak pernah dipanggil langsung oleh RVM-Edge. RVM-Server yang memutuskan kapan menggunakan RVM-CV.

## 2. Arsitektur Sistem

### 2.1. Komponen RVM-CV
```
┌─────────────────────────────────────────────────┐
│         RVM-CV (vm102 - GPU Server)            │
│                                                 │
│  ┌──────────────────┐    ┌─────────────────┐  │
│  │ FastAPI Server   │    │  GPU Computing  │  │
│  │ (Uvicorn)        │───►│  - YOLO11       │  │
│  └────────┬─────────┘    │  - SAM2         │  │
│           │              │  - PyTorch      │  │
│  ┌────────▼─────────┐    │  - CUDA 12.x    │  │
│  │ Job Queue        │    └─────────────────┘  │
│  │ (Redis/Celery)   │                          │
│  └──────────────────┘                          │
│           │                                     │
│  ┌────────▼─────────┐    ┌─────────────────┐  │
│  │ Temp Storage     │    │ Tailscale VPN   │  │
│  │ /tmp/processing  │    │ 100.98.142.94   │  │
│  └──────────────────┘    └─────────────────┘  │
└─────────────────────────────────────────────────┘
           │
           │ HTTPS API Calls
           ▼
    ┌─────────────────┐
    │  RVM-Server     │
    │  (vm100)        │
    │  - MinIO        │
    │  - Model Store  │
    └─────────────────┘
```

### 2.2. Stateless Workflow
```
1. Server triggers job → RVM-CV API
2. RVM-CV downloads dataset from MinIO → /tmp/job_<id>/
3. Process AI training/inference
4. Upload results (best.pt, metrics) to MinIO
5. Delete /tmp/job_<id>/ → Clean state
6. Return job_id + status to Server
```

## 3. Spesifikasi Teknis

### 3.1. Platform & Dependencies
- **Hardware**: 
  - GPU: NVIDIA RTX 4090 / A100 (24GB+ VRAM)
  - CPU: 16+ cores
  - RAM: 64GB+
  - Storage: 500GB NVMe (temp only)
- **OS**: Ubuntu 22.04 LTS
- **Python**: 3.10+
- **Framework**: FastAPI + Uvicorn
- **AI**: PyTorch 2.x, Ultralytics YOLO11, SAM2
- **Task Queue**: Celery + Redis
- **Network**: Tailscale VPN (100.98.142.94)

### 3.2. GPU Configuration
- **CUDA**: 12.1+
- **cuDNN**: Compatible version
- **Driver**: NVIDIA 535+
- **Multi-GPU**: Support untuk parallel training jika ada >1 GPU

## 4. API Endpoints RVM-CV

### 4.1. Endpoints yang Disediakan oleh RVM-CV (FastAPI)

| Method | Endpoint                          | Deskripsi                                      |
|--------|-----------------------------------|------------------------------------------------|
| `GET`  | `/api/cv/health`                  | Health check + GPU status                      |
| `POST` | `/api/cv/train`                   | Trigger training job untuk YOLO11 atau SAM2    |
| `POST` | `/api/cv/inference`               | Remote inference untuk single image            |
| `POST` | `/api/cv/batch-inference`         | Batch inference untuk multiple images          |
| `GET`  | `/api/cv/job/{job_id}`            | Status job (queued, processing, completed)     |
| `DELETE` | `/api/cv/job/{job_id}/cancel`   | Cancel running job                             |
| `GET`  | `/api/cv/metrics`                 | Server metrics (GPU util, memory, queue size)  |

### 4.2. Request/Response Examples

#### Training Job Request
```json
POST /api/cv/train
{
  "job_type": "yolo11",
  "dataset_url": "https://minio.server/datasets/waste-detection-v3.zip",
  "base_model": "yolo11n.pt",
  "epochs": 100,
  "img_size": 640,
  "batch_size": 16,
  "callback_url": "https://rvm-server/api/v1/cv/training-complete"
}
```

#### Training Job Response
```json
{
  "job_id": "train_yolo11_20260109_193000",
  "status": "queued",
  "estimated_duration": "2 hours",
  "queue_position": 1
}
```

#### Inference Request
```json
POST /api/cv/inference
{
  "image_url": "https://minio.server/images/bottle_123.jpg",
  "model_version": "yolo11_v3_best.pt",
  "confidence_threshold": 0.75
}
```

#### Inference Response
```json
{
  "detections": [
    {
      "class": "PET_bottle",
      "confidence": 0.92,
      "bbox": [120, 80, 340, 520],
      "segmentation": "base64_encoded_mask"
    }
  ],
  "processing_time_ms": 152,
  "model_used": "yolo11_v3_best.pt"
}
```

## 5. Integration dengan RVM-Server

### 5.1. API Endpoints di RVM-Server untuk RVM-CV

| Method | Endpoint (Server)                      | Deskripsi                                |
|--------|----------------------------------------|------------------------------------------|
| `POST` | `/api/v1/cv/training-complete`         | Callback dari RVM-CV saat training done  |
| `GET`  | `/api/v1/cv/datasets/{id}`             | Download dataset dari MinIO              |
| `POST` | `/api/v1/cv/upload-model`              | Upload trained model ke MinIO            |
| `GET`  | `/api/v1/cv/download-model/{version}`  | Download specific model version          |
| `POST` | `/api/v1/cv/job-status`                | Update job status dari RVM-CV            |

### 5.2. Authentication
- **RVM-CV → RVM-Server**: Bearer token (service account)
  - Example: `Authorization: Bearer cv_service_token_xxxx`
- **RVM-Server → RVM-CV**: API Key dalam header
  - Example: `X-API-Key: server_api_key_xxxx`

## 6. Database Schema Changes (RVM-Server)

### 6.1. Tabel Baru: `cv_training_jobs`
```sql
CREATE TABLE cv_training_jobs (
    id BIGSERIAL PRIMARY KEY,
    job_id VARCHAR(255) UNIQUE NOT NULL,
    job_type VARCHAR(50) NOT NULL, -- 'yolo11', 'sam2'
    dataset_id BIGINT REFERENCES datasets(id),
    status VARCHAR(50) DEFAULT 'queued', -- queued, processing, completed, failed
    config JSONB, -- Training config (epochs, batch_size, dll)
    result_model_path TEXT, -- MinIO path to best.pt
    metrics JSONB, -- Training metrics (mAP, loss, dll)
    started_at TIMESTAMP,
    completed_at TIMESTAMP,
    error_message TEXT,
    created_by BIGINT REFERENCES users(id),
    created_at TIMESTAMP DEFAULT NOW()
);
```

### 6.2. Tabel Baru: `cv_inference_logs`
```sql
CREATE TABLE cv_inference_logs (
    id BIGSERIAL PRIMARY KEY,
    edge_device_id BIGINT REFERENCES edge_devices(id),
    image_path TEXT NOT NULL, -- MinIO path
    model_version VARCHAR(100),
    detections JSONB, -- AI detection results
    processing_time_ms INT,
    processed_by VARCHAR(50) DEFAULT 'edge', -- 'edge' or 'cv-server'
    created_at TIMESTAMP DEFAULT NOW()
);
```

### 6.3. Tabel Baru: `ai_model_versions`
```sql
CREATE TABLE ai_model_versions (
    id BIGSERIAL PRIMARY KEY,
    model_name VARCHAR(100) NOT NULL, -- 'yolo11', 'sam2'
    version VARCHAR(50) NOT NULL,
    file_path TEXT NOT NULL, -- MinIO path to best.pt
    file_size_mb DECIMAL(10,2),
    sha256_hash VARCHAR(64) NOT NULL,
    training_job_id BIGINT REFERENCES cv_training_jobs(id),
    metrics JSONB, -- Performance metrics
    is_active BOOLEAN DEFAULT false,
    deployed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(model_name, version)
);
```

## 7. Training Workflow (Detail)

### 7.1. Automated Training Pipeline
```
1. Admin upload dataset baru ke MinIO via RVM-Server
2. Admin trigger "Train Model" dari dashboard
3. RVM-Server create job di cv_training_jobs table
4. RVM-Server call RVM-CV: POST /api/cv/train
5. RVM-CV:
   a. Add job ke Redis queue
   b. Celery worker pick job
   c. Download dataset dari MinIO → /tmp/job_123/
   d. Start YOLO11 training (Ultralytics)
   e. Monitor progress, send periodic updates ke Server
   f. Training complete → best.pt generated
   g. Upload best.pt ke MinIO
   h. Callback ke Server: POST /api/v1/cv/training-complete
   i. Delete /tmp/job_123/
6. RVM-Server:
   a. Receive callback
   b. Create record di ai_model_versions
   c. If metrics better → Set is_active = true
   d. Notify RVM-Edge devices: "New model available"
7. RVM-Edge devices poll → Download new best.pt → Update local model
```

### 7.2. Training Config Template (YOLO11)
```yaml
# Auto-generated by RVM-Server
task: detect
mode: train
model: yolo11n.pt
data: /tmp/job_123/dataset.yaml
epochs: 100
batch: 16
imgsz: 640
device: 0  # First GPU
workers: 8
project: /tmp/job_123/runs
name: training
patience: 50
save: True
exist_ok: True
```

## 8. Remote Inference Workflow

### 8.1. Use Cases untuk Remote Inference
RVM-Server men-trigger RVM-CV untuk remote inference dalam kasus:
- **Fraud Detection**: Gambar dari transaksi yang dicurigai fraud (confidence anomaly, pattern mismatch)
- **Playground/Testing**: Admin test model baru atau experimental model sebelum deploy ke Edge
- **Re-analysis**: Audit trail untuk transaksi yang di-dispute user/tenant
- **Batch Analysis**: Periodic re-processing untuk quality assurance

> **Catatan Penting**: RVM-Edge **TIDAK PERNAH** memanggil RVM-CV directly. RVM-Edge selalu process 100% lokal. RVM-Server yang memutuskan kapan perlu remote inference ke RVM-CV untuk gambar yang sudah tersimpan di MinIO.

### 8.2. Remote Inference Flow (Server-triggered)
```
1. RVM-Edge: Capture image → Local AI process → Send hasil + image ke RVM-Server
2. RVM-Server: Save image ke MinIO, save detection result ke database
3. RVM-Server: Analyze result → If suspect fraud → Trigger RVM-CV
4. RVM-Server call RVM-CV: POST /api/cv/inference (dengan image URL dari MinIO)
5. RVM-CV:
   a. Download image dari MinIO
   b. Run YOLO11 + SAM2
   c. Return detections ke RVM-Server
6. RVM-Server: Compare Edge result vs CV result
7. RVM-Server: Save comparison di cv_inference_logs dengan flag "fraud_check"
8. RVM-Server: If significant difference → Alert admin untuk review
```

**Playground Flow** (Manual trigger dari dashboard):
```
1. Admin pilih gambar dari MinIO via dashboard
2. Admin select model version untuk testing
3. Dashboard call RVM-Server: POST /api/v1/cv/playground-inference
4. RVM-Server forward ke RVM-CV: POST /api/cv/inference
5. RVM-CV process → Return result
6. Dashboard display result (bounding boxes, confidence, segmentation mask)
7. Admin compare dengan hasil Edge (jika ada) untuk model evaluation
```

## 9. Monitoring & Observability

### 9.1. Metrics yang Dipantau
- **GPU Metrics**:
  - GPU Utilization %
  - VRAM Usage (GB)
  - GPU Temperature (°C)
  - Power Consumption (W)
  
- **Job Metrics**:
  - Queue size (pending jobs)
  - Average processing time
  - Success/failure rate
  - Throughput (jobs/hour)

### 9.2. Dashboard Integration (RVM-Server)
- **AI Vision Dashboard** (sudah ada di `/admin/edge-vision`)
- Tambahkan section "RVM-CV Status":
  - GPU Stats chart
  - Active training jobs table
  - Model versions list
  - Inference throughput graph

## 10. Struktur Project RVM-CV

```
MyRVM-CV/
├── app/
│   ├── __init__.py
│   ├── main.py                # FastAPI app
│   ├── api/
│   │   ├── health.py          # Health check
│   │   ├── training.py        # Training endpoints
│   │   ├── inference.py       # Inference endpoints
│   │   └── jobs.py            # Job management
│   ├── services/
│   │   ├── yolo_trainer.py    # YOLO11 training logic
│   │   ├── sam2_trainer.py    # SAM2 training logic
│   │   ├── inference_engine.py # Inference logic
│   │   ├── dataset_manager.py  # Download/extract datasets
│   │   └── minio_client.py     # MinIO integration
│   ├── tasks/
│   │   ├── celery_app.py      # Celery config
│   │   └── workers.py         # Celery task definitions
│   └── utils/
│       ├── gpu_monitor.py     # GPU metrics
│       └── cleanup.py         # Temp file cleanup
├── models/                    # Temp model storage
├── config/
│   ├── settings.py
│   └── .env                   # API keys, Server URL
├── requirements.txt
├── Dockerfile
├── docker-compose.yml         # FastAPI + Redis + Celery
└── README.md
```

## 11. Rencana Pengujian (Staging/Testing)

### 11.1. Unit Testing
- Test dataset download dari MinIO
- Test YOLO11 training dengan sample dataset (10 images, 5 epochs)
- Test SAM2 segmentation
- Test model upload ke MinIO
- Verify temp cleanup (no files left after job)

### 11.2. Performance Testing
- **Training Performance**:
  - Dataset: 1000 images, 100 epochs
  - Expected: < 2 hours (RTX 4090)
  - Monitor GPU utilization > 90%
  
- **Inference Performance**:
  - Batch size: 10 images
  - Expected: < 1 second total (100ms per image)
  - Test with concurrent requests (10 parallel)

### 11.3. Integration Testing
- **Skenario 1: Full Training Cycle**
  1. Upload dataset via RVM-Server dashboard
  2. Trigger training job
  3. Monitor progress updates
  4. Verify best.pt uploaded ke MinIO
  5. Check ai_model_versions table
  6. Deploy model ke test RVM-Edge device
  
- **Skenario 2: Fraud Detection Flow**
  1. RVM-Edge process transaction → Send image + result ke Server
  2. RVM-Server detect anomaly (e.g., confidence = 0.65, threshold 0.75)
  3. Server auto-trigger RVM-CV inference
  4. Compare Edge vs CV results
  5. If mismatch → Log fraud case
  6. Verify alert sent ke admin dashboard

### 11.4. Stress Testing
- **Queue Overload**:
  - Submit 50 training jobs simultaneously
  - Verify queue handling (FIFO)
  - No crashes, graceful queueing
  
- **GPU Memory Stress**:
  - Use large batch size (batch=64)
  - Monitor VRAM usage
  - Verify OOM handling (reduce batch auto)

## 12. Deployment Plan

### 12.1. Docker Deployment (vm102)
```bash
# 1. Install NVIDIA Docker runtime
curl -fsSL https://nvidia.github.io/nvidia-docker/gpgkey | sudo gpg --dearmor -o /usr/share/keyrings/nvidia-docker.gpg
distribution=$(. /etc/os-release;echo $ID$VERSION_ID)
curl -s -L https://nvidia.github.io/nvidia-docker/$distribution/nvidia-docker.list | \
  sed 's#deb https://#deb [signed-by=/usr/share/keyrings/nvidia-docker.gpg] https://#g' | \
  sudo tee /etc/apt/sources.list.d/nvidia-docker.list
sudo apt-get update && sudo apt-get install -y nvidia-docker2
sudo systemctl restart docker

# 2. Clone repository
git clone <repo-url> /opt/myrvm-cv
cd /opt/myrvm-cv

# 3. Setup environment
cp .env.example .env
nano .env  # Edit API keys, Server URL

# 4. Build and run
docker-compose up -d --build
```

### 12.2. Docker Compose Configuration
```yaml
version: '3.8'
services:
  fastapi:
    build: .
    runtime: nvidia
    environment:
      - NVIDIA_VISIBLE_DEVICES=all
    ports:
      - "8100:8000"
    volumes:
      - /tmp/cv-processing:/tmp
    depends_on:
      - redis
  
  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"
  
  celery-worker:
    build: .
    runtime: nvidia
    command: celery -A app.tasks.celery_app worker --loglevel=info
    environment:
      - NVIDIA_VISIBLE_DEVICES=all
    volumes:
      - /tmp/cv-processing:/tmp
    depends_on:
      - redis
```

### 12.3. Monitoring Setup
- Install `nvidia-smi` monitoring
- Setup Prometheus exporters untuk GPU metrics
- Integrate dengan RVM-Server dashboard

## 13. Security Considerations

### 13.1. Network Security
- Semua komunikasi via Tailscale VPN (100.98.142.94)
- Firewall: Block public internet, allow only Tailscale subnet
- Rate limiting: 100 requests/minute per IP

### 13.2. Data Security
- Dataset encryption at rest di MinIO
- Temp data di `/tmp` auto-wipe setelah job
- No logging sensitive user data

### 13.3. Resource Protection
- Memory limit: 60GB per Celery worker
- GPU memory limit: 22GB per job (RTX 4090 24GB)
- Disk quota: Max 100GB di /tmp, auto-cleanup jika > 80GB

## 14. Changelog

| Tanggal            | Perubahan                                          | Author |
|--------------------|----------------------------------------------------|--------|
| 09-01-2026 19:30   | Pembuatan dokumen rencana integrasi RVM-CV         | AG1    |

## 15. Rollback Plan

### Level 1: Training Job Failure
- **Gejala**: Training crash, OOM error, invalid dataset
- **Action**:
  1. Job auto-retry dengan reduced batch size
  2. If retry fails → Mark job as "failed" di database
  3. Admin notified via email/dashboard alert
  4. Temp files auto-cleanup
- **Estimated Downtime**: 0 (tidak affect production)

### Level 2: API Service Down
- **Gejala**: FastAPI crash, Celery worker down
- **Action**:
  1. Docker auto-restart (restart: unless-stopped)
  2. If fails 3x → Alert admin
  3. All fraud detection temporarily disabled (rely on Edge results only)
- **Estimated Downtime**: 1-2 menit (self-healing)

### Level 3: GPU Driver/Hardware Issue
- **Gejala**: GPU not detected, driver crash
- **Action**:
  1. Restart server: `sudo reboot`
  2. If GPU still down → Disable RVM-CV temporarily
  3. All RVM-Edge force local mode
  4. Admin investigate hardware (reseat GPU, check PSU)
- **Estimated Downtime**: 30-60 menit

### Level 4: Critical Data Corruption
- **Gejala**: Bad model uploaded, corrupt dataset
- **Action**:
  1. Rollback model version:
     - Set previous version `is_active = true`
     - Notify all RVM-Edge to downgrade
  2. Re-run training dengan clean dataset
- **Estimated Downtime**: 2-4 jam (re-training time)

## 16. Timeline Estimasi

| Fase                          | Durasi   | Deliverable                          |
|-------------------------------|----------|--------------------------------------|
| Setup vm102 + Docker          | 2 hari   | GPU server ready, Docker working     |
| FastAPI development           | 4 hari   | REST API endpoints complete          |
| YOLO11 training integration   | 5 hari   | Training pipeline working            |
| SAM2 integration              | 4 hari   | Segmentation pipeline working        |
| Celery job queue              | 3 hari   | Async task management ready          |
| MinIO integration             | 3 hari   | Dataset/model upload-download OK     |
| RVM-Server integration        | 4 hari   | Full integration test passed         |
| Monitoring & dashboard        | 3 hari   | GPU metrics dashboard live           |
| **Total**                     | **28 hari** | Production-ready RVM-CV           |

## 17. Catatan Tambahan

- **Auto-scaling**: Future improvement - multiple GPU nodes dengan load balancer
- **Model A/B Testing**: Support untuk parallel deployment 2 model versions
- **Synthetic Data**: Integrate DALL-E/Stable Diffusion untuk augment training data
- **Edge Priority**: RVM-Edge devices dengan low local confidence get priority di queue

---

**Dokumen ini akan di-update seiring dengan progress pengembangan.**
