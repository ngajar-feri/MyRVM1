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
        return {
            "os": platform.system(),
            "release": platform.release(),
            "version": platform.version(),
            "machine": platform.machine(),
            "processor": platform.processor(),
            "python_version": platform.python_version()
        }

    def _detect_controller_type(self):
        """Detects if running on Nvidia Jetson or Raspberry Pi."""
        try:
            with open("/proc/device-tree/model", "r") as f:
                model = f.read().strip().replace('\x00', '')
                if "NVIDIA" in model or "Jetson" in model:
                    return "NVIDIA Jetson"
                if "Raspberry Pi" in model:
                    return "Raspberry Pi"
                return model # Return actual model string if known
        except FileNotFoundError:
            pass # Not a device tree system (e.g. desktop)
        
        # Fallback based on architecture
        machine = platform.machine()
        if "aarch64" in machine or "arm" in machine:
             return "Generic ARM Edge Device"
        return "Generic x86 Host" # Likely dev laptop

    def _get_hardware_info(self):
        """
        Merges auto-detection with static map (if available).
        """
        detected = self.probe.probe_all()
        
        # In the future, we could merge this with self.hw_manager.drivers
        # For now, we return the raw detected reality + summary
        
        info = {
            "summary": "Hardware Auto-Detection Report",
            "detected_cameras": detected.get("cameras", []),
            "detected_i2c": detected.get("i2c_devices", []),
            "detected_serial": detected.get("serial_ports", []),
            # MCU detection via USB-Serial matching (heuristic)
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
