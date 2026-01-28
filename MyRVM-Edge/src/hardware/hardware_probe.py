import subprocess
import os
import glob
import json

class HardwareProbe:
    """
    Scans system buses to detect active hardware peripherals.
    Acts as the 'Reality' layer to complement the 'Intent' (hardware_map.json).
    """

    def __init__(self):
        self.discovery_results = {
            "cameras": [],
            "i2c_devices": [],
            "serial_ports": [],
            "storage": []
        }

    def probe_all(self):
        """Runs all discovery probes."""
        self.discovery_results["cameras"] = self._probe_cameras()
        self.discovery_results["i2c_devices"] = self._probe_i2c()
        self.discovery_results["serial_ports"] = self._probe_serial()
        return self.discovery_results

    def _probe_cameras(self):
        """Scans for video devices using v4l2-ctl."""
        cameras = []
        try:
            # Check for /dev/video*
            video_devices = glob.glob("/dev/video*")
            for dev in video_devices:
                # Get device name/info
                cmd = ["v4l2-ctl", "--device", dev, "--info"]
                result = subprocess.run(cmd, capture_output=True, text=True)
                if result.returncode == 0:
                    info = result.stdout
                    name = "Unknown Camera"
                    for line in info.split('\n'):
                        if "Card type" in line:
                            name = line.split(':')[1].strip()
                            break
                    cameras.append({"path": dev, "name": name})
        except Exception as e:
            print(f"[!] Camera Probe Error: {e}")
        return cameras

    def _probe_i2c(self):
        """Scans I2C buses for active addresses."""
        devices = []
        try:
            # List I2C buses
            # Commonly i2c-1 (bus 1) or i2c-8 on Orin
            for bus_id in range(10):
                bus_path = f"/dev/i2c-{bus_id}"
                if os.path.exists(bus_path):
                    # Use i2cdetect -y [bus]
                    cmd = ["i2cdetect", "-y", str(bus_id)]
                    result = subprocess.run(cmd, capture_output=True, text=True)
                    if result.returncode == 0:
                        # Parse i2cdetect output (grid)
                        lines = result.stdout.strip().split('\n')[1:]
                        for line in lines:
                            parts = line.split(':')[1].split()
                            for part in parts:
                                if part != "--" and part != "UU":
                                    devices.append({
                                        "bus": bus_id,
                                        "address": f"0x{part.lower()}"
                                    })
        except Exception as e:
            # Often fails if i2c-tools is not installed or permission denied
            print(f"[!] I2C Probe Warning: {e}")
        return devices

    def _probe_serial(self):
        """Scans for active UART/USB-Serial ports."""
        ports = []
        # Common Jetson/Pi patterns
        patterns = ["/dev/ttyUSB*", "/dev/ttyACM*", "/dev/ttyTHS*"]
        for p in patterns:
            for port in glob.glob(p):
                ports.append(port)
        return ports

if __name__ == "__main__":
    probe = HardwareProbe()
    print("=== Hardware Discovery Probe ===")
    results = probe.probe_all()
    print(json.dumps(results, indent=2))
