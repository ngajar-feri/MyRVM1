# Deployment: Edge Client Systemd Service

This guide explains how to install and manage the MyRVM Edge client as a background service on the Jetson Orin Nano.

## 1. Prerequisites
- User `myrobot` must be a member of the `rvm-devs` and `gpio` groups.
- `venv` is not required if using system Python, otherwise update `ExecStart`.

## 2. Installation
1.  **Copy Service File:**
    ```bash
    sudo cp myrvm-edge.service /etc/systemd/system/
    ```
2.  **Enable and Start:**
    ```bash
    sudo systemctl daemon-reload
    sudo systemctl enable myrvm-edge
    sudo systemctl start myrvm-edge
    ```

## 3. Environment Variables
The service requires specific environment variables for Jetson Orin compatibility:
- `JETSON_MODEL_NAME=JETSON_ORIN_NANO`: Forces the GPIO library to recognize the board.
- `PYTHONUNBUFFERED=1`: Ensures logs are captured in real-time by `journalctl`.

## 4. Monitoring
Check service status and logs:
```bash
sudo systemctl status myrvm-edge
journalctl -u myrvm-edge -f
```
