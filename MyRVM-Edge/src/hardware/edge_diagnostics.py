import json
import socket
import subprocess
import time
import platform
try:
    import requests
except ImportError:
    requests = None
try:
    import psutil
except ImportError:
    psutil = None
import datetime
from .hardware_probe import HardwareProbe

class EdgeDiagnostics:
    """
    Module for gathering hardware specifications, system metrics, and running basic diagnostics 
    for the Edge device (Jetson/Raspberry Pi).
    """

    def __init__(self, hardware_manager=None):
        self.probe = HardwareProbe()
        self.hw_manager = hardware_manager # Optional, for syncing with intent
        self.device_id = self._get_device_id()

    def get_specs(self):
        """
        Returns a dictionary containing the full hardware specification and status.
        Matches the requested structure:
        1. hardware ID
        2. name
        3. ip_local
        4. ip_vpn
        5. timezone
        6. system
        7. controller_type
        8. hardware_info
        9. diagnostics
        10. health_metrics
        """
        
        # 1-7: Basic Info
        specs = {
            "device_id": self.device_id,
            "name": platform.node(),
            "ip_local": self._get_local_ip(),
            "ip_vpn": self._get_vpn_ip(),
            "timezone": self._get_remote_timezone(),
            "system": self._get_system_info(),
            "controller_type": self._detect_controller_type(),
        }

        # 8: Hardware Info (Detection)
        specs["hardware_info"] = self._get_hardware_info()

        # 9: Diagnostics
        specs["diagnostics"] = self.run_diagnostics(specs["hardware_info"])

        # 10: Health Metrics
        specs["health_metrics"] = self.get_health_metrics()

        return specs

    def _get_remote_timezone(self):
        """
        Determines timezone based on Public IP using ipapi.co.
        Falls back to system timezone if API fails.
        """
        try:
            if requests:
                # Using ipapi.co/json/ to ensure JSON response
                response = requests.get('https://ipapi.co/json/', timeout=5).json()
                return response.get('timezone', 'Unknown')
            else:
                return str(datetime.datetime.now().astimezone().tzinfo) + " (requests missing)"
        except Exception as e:
            # Fallback to local system timezone
            return str(datetime.datetime.now().astimezone().tzinfo)

    def _get_device_id(self):
        """Generates a unique ID based on MAC address or Machine ID."""
        try:
            # Try getting machine-id
            if os.path.exists("/etc/machine-id"):
                with open("/etc/machine-id", "r") as f:
                    return f.read().strip()
            # Fallback to MAC based
            return hex(uuid.getnode())
        except:
            return "unknown-device-id"

    def _get_local_ip(self):
        """Gets local LAN IP."""
        try:
            s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
            s.connect(("8.8.8.8", 80))
            ip = s.getsockname()[0]
            s.close()
            return ip
        except Exception:
            return "127.0.0.1"

    def _get_vpn_ip(self):
        """Gets Tailscale IP if available."""
        try:
            # Allow turbo-all for this command if simple
            result = subprocess.run(["tailscale", "ip", "-4"], capture_output=True, text=True, timeout=2)
            if result.returncode == 0:
                return result.stdout.strip()
        except FileNotFoundError:
            pass # Tailscale not installed
        except Exception as e:
            # print(f"VPN IP check failed: {e}") 
            pass
        return "Not Connected"

    def _get_system_info(self):
        """
        Gather system software information.
        1. jetpack_version
        2. firmware_version
        3. python_version
        4. ai_models
        """
        info = {
            "python_version": platform.python_version(),
            "firmware_version": "v2.1.0-beta", # Defaults for now, but should ideally come from a version file
            "jetpack_version": "Unknown",
            "ai_models": []
        }

        # 1. JetPack Version Detection
        try:
            if os.path.exists('/etc/nv_tegra_release'):
                with open('/etc/nv_tegra_release', 'r') as f:
                    content = f.read().strip()
                    # Content format: # R35 (release), REVISION: 4.1, GCID: 33958178, BOARD: t186ref, EABI: aarch64, DATE: Tue Aug  1 19:57:35 UTC 2023
                    # Extract Rxx.x
                    parts = content.split(',')
                    if len(parts) >= 2:
                        release = parts[0].replace('# ', '').strip()
                        revision = parts[1].replace('REVISION: ', '').strip()
                        info["jetpack_version"] = f"{release}.{revision}"
                    else:
                        info["jetpack_version"] = content
            elif os.path.exists('/proc/device-tree/model'):
                with open('/proc/device-tree/model', 'r') as f:
                     model = f.read().strip().replace('\x00', '')
                     if "Raspberry Pi" in model:
                         info["jetpack_version"] = "N/A (Raspberry Pi)"
                     else:
                         info["jetpack_version"] = "N/A (Generic)"
            else:
                info["jetpack_version"] = "N/A (Non-Jetson)"
        except Exception:
            info["jetpack_version"] = "Error Detecting"

        # 2. AI Models Detection
        # Check for models directory or config
        try:
             # Assume models are in specific directory relative to this file
             models_dir = os.path.join(os.path.dirname(__file__), '../../models')
             config_path = os.path.join(os.path.dirname(__file__), '../../config/config.json')
             
             models_list = []
             if os.path.exists(config_path):
                 with open(config_path, 'r') as f:
                     config = json.load(f)
                     if "ai_models" in config:
                         models_list = config["ai_models"]
            
             if not models_list and os.path.exists(models_dir):
                 # Auto-detect .pt files if no config
                 for file in os.listdir(models_dir):
                     if file.endswith(".pt") or file.endswith(".engine"):
                         models_list.append({
                             "model_name": file,
                             "model_version": "auto-detected",
                             "hash": "unknown",
                             "last_update": datetime.datetime.fromtimestamp(os.path.getmtime(os.path.join(models_dir, file))).isoformat()
                         })
             
             if not models_list:
                 models_list.append({
                     "model_name": "none",
                     "model_version": "0.0.0",
                     "hash": "00000000",
                     "last_update": datetime.datetime.now().isoformat()
                 })

             info["ai_models"] = models_list

        except Exception as e:
            info["ai_models"] = [{"error": str(e)}]
        
        return info

    def _detect_controller_type(self):
        """Detects if running on Nvidia Jetson or Raspberry Pi."""
        try:
            with open("/proc/device-tree/model", "r") as f:
                model = f.read().strip().replace('\x00', '')
                if "NVIDIA" in model or "Jetson" in model:
                    return "NVIDIA Jetson"
                if "Raspberry Pi" in model:
                    return "Raspberry Pi"
                return model 
        except FileNotFoundError:
            pass 
        
        machine = platform.machine()
        if "aarch64" in machine or "arm" in machine:
             return "Generic ARM Edge Device"
        return "Generic x86 Host"

    def _get_hardware_info(self):
        """
        Merges auto-detection with static map (if available).
        """
        detected = self.probe.probe_all()
        
        info = {
            "summary": "Hardware Auto-Detection Report",
            "detected_cameras": detected.get("cameras", []),
            "detected_i2c": detected.get("i2c_devices", []),
            "detected_serial": detected.get("serial_ports", []),
            "detected_mcu": [p for p in detected.get("serial_ports", []) if "USB" in p or "ACM" in p]
        }
        return info

    def run_diagnostics(self, hw_info=None):
        """
        Runs basic pass/fail checks.
        """
        if hw_info is None:
            hw_info = self._get_hardware_info()

        diag = {
            "network_check": "Pass" if self._get_local_ip() != "127.0.0.1" else "Not Pass",
            "camera_check": "Pass" if len(hw_info["detected_cameras"]) > 0 else "Not Pass",
            "sensor_check": "Pass" if len(hw_info["detected_i2c"]) > 0 else "Not Pass", # Assumption: Sensors are on I2C
            "mcu_check": "Pass" if len(hw_info["detected_mcu"]) > 0 else "Not Pass",
            "timestamp": datetime.datetime.now().isoformat()
        }
        return diag

    def get_health_metrics(self):
        """
        Returns real-time system usage metrics.
        """
        if psutil:
            return {
                "cpu_usage_percent": psutil.cpu_percent(interval=0.1),
                "memory_usage_percent": psutil.virtual_memory().percent,
                "disk_usage_percent": psutil.disk_usage('/').percent
            }
        else:
             return {
                "cpu_usage_percent": "N/A (psutil missing)",
                "memory_usage_percent": "N/A",
                "disk_usage_percent": "N/A"
            }

if __name__ == "__main__":
    # Self-test when run directly
    import os
    import uuid
    diag = EdgeDiagnostics()
    print(json.dumps(diag.get_specs(), indent=4))
