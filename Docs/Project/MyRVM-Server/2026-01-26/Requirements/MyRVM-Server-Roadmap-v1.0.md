# PRD: MyRVM-Server Integration Roadmap
**Version:** 1.0
**Status:** Draft
**Last Updated:** 2026-01-26

## 1. Executive Summary
Fase ini berfokus pada penyempurnaan integrasi antara MyRVM-Server dengan pilar ekosistem lainnya (Edge, CV, dan Mobile Apps). Fokus utama adalah pembersihan utang teknis, pengamanan URL Kiosk, dan dashboard telemetry real-time.

## 2. Functional Requirements (MoSCoW)
- **Must Have (P0):**
    - [x] Migrasi UUID untuk akses Signed URL Kiosk.
    - [x] Konfigurasi Trust Proxies untuk SSL/Cloudflare.
    - [ ] Endpoint Hardware Test otomatis untuk teknisi.
- **Should Have (P1):**
    - [ ] Refactoring API Handshake untuk support batch registration.
    - [ ] Implementasi live status (WebSocket) di Dashboard Admin v2.
- **Could Have (P2):**
    - [ ] Export log telemetry ke format CSV/PDF.

## 3. Technical Constraints
- Stack: Laravel 12, PostgreSQL, Redis.
- Auth: Sanctum (API) & Signed URLs (Web).
- Environment: Docker Compose (Production-similar).

## 4. Implementation Tasks (To-Do)
- [x] Phase 1: Security Hardening (UUID & Signed URL)
- [ ] Phase 2: Telemetry Enhancement (Live Updates)
- [ ] Phase 3: Technician Tools (Manual Command & Tests)

## 5. History & References
| Ver | Date | Changes | Ref (Link to Artifact) |
| :-- | :-- | :------ | :--------------------- |
| 1.0 | 2026-01-26 | Initial | [System Overview](file:///home/my/MyRVM1/Docs/Artifacts/MyRVM-Server/Architecture/System-Overview-20260126.md) |
