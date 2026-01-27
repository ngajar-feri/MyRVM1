# Rollback Plan: MyRVM-Edge v2.0 Deployment

## Overview
This document outlines the procedures to revert changes introduced by the MyRVM-Edge v2.0 update (Day-0 Setup Wizard, Local Bridge, Handshake V2) in case of critical failures.

## Triggers for Rollback
- **Boot Loop**: Device fails to start `main.py` consistently.
- **Handshake Failure**: Device cannot authenticate with Server even with valid credentials.
- **Kiosk Freeze**: Local Bridge conficts with Browser ports.

## Rollback Procedures

### 1. Revert Edge Codebase
If the new Python code causes crashes:
```bash
cd ~/MyRVM1/MyRVM-Edge
# Revert to previous stable commit (assuming tagged v1.8 or similar)
git checkout main # or previous branch
git pull origin main
# Or specifically undo local changes
git checkout .
```

### 2. Disable Setup Wizard (Manual Override)
If Setup Wizard launches unexpectedly or crashes:
1. SSH into device.
2. Manually create `config/secrets.env` to bypass provisioning check.
```bash
echo "RVM_API_KEY=your_key_here" > config/secrets.env
echo "RVM_SERIAL_NUMBER=RVM-2026-X" >> config/secrets.env
```
3. Restart Service:
```bash
sudo systemctl restart myrvm-edge
```

### 3. Server-Side Rollback (MyRVM-Server)
If `Handshake V2` payload crashes the Edge client (backward compatibility issue):
1. **Revert Controller**: Restore `app/Http/Controllers/Api/EdgeDeviceController.php` to previous version.
   ```bash
   git checkout HEAD~1 -- app/Http/Controllers/Api/EdgeDeviceController.php
   ```
2. **Deploy**:
   ```bash
   git add .
   git commit -m "rollback: Revert Handshake V2 to V1"
   git push origin master
   ssh my@server "cd ~/MyRVM1/MyRVM-Server && git pull origin master"
   ```

### 4. Emergency Kiosk Bypass
If Kiosk URL is invalid (Signed URL error):
1. On Server DB, manually set `kiosk_url` override or check `rvm_machines` table UUID.
2. On Edge, check logs: `tail -f logs/edge.log`
