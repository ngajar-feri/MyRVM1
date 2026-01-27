import sys
import time
import json
import os
from src.services.api_client import RvmApiClient

# Constants
CONFIG_PATH = os.path.join(os.path.dirname(__file__), 'config', 'settings.json')
CREDENTIALS_PATH = os.path.join(os.path.dirname(__file__), 'config', 'credentials.json')

def get_device_info():
    """Extracts physical hardware serial and model name (Jetson/Pi)."""
    # 1. Check for Jetson
    if os.path.exists('/proc/device-tree/model'):
        try:
            with open('/proc/device-tree/model', 'r') as f:
                model = f.read().strip('\x00').strip()
            
            serial = "unknown_jetson"
            if os.path.exists('/proc/device-tree/serial-number'):
                with open('/proc/device-tree/serial-number', 'r') as f:
                    serial = f.read().strip('\x00').strip()
            return serial, model
        except Exception as e:
            print(f"[!] Error reading Jetson info: {e}")

    # 2. Check for Raspberry Pi
    if os.path.exists('/proc/cpuinfo'):
        try:
            serial = "unknown_pi"
            with open('/proc/cpuinfo', 'r') as f:
                for line in f:
                    if line.startswith('Serial'):
                        serial = line.split(':')[1].strip()
            return serial, "Raspberry Pi"
        except Exception as e:
            print(f"[!] Error reading Pi info: {e}")

    return "generic_dev", "Generic Linux Device"

def load_config():
    if not os.path.exists(CONFIG_PATH):
        print("[!] Settings file not found. Generating default...")
        return {
            "api_url": "http://100.123.143.87:8001/api/v1", 
            "timeout": 10
        }
    with open(CONFIG_PATH, 'r') as f:
        return json.load(f)

def load_credentials():
    if os.path.exists(CREDENTIALS_PATH):
        with open(CREDENTIALS_PATH, 'r') as f:
            return json.load(f)
    print("[!] Credentials not found! Please provide config/credentials.json")
    return None

def main():
    print("=== MyRVM Edge Client v2.1 ===")
    
    # 1. Load Configuration & Hardware Info
    config = load_config()
    creds = load_credentials()
    if not creds:
        return
        
    serial, model = get_device_info()
    
    print(f"[*] Hardware ID (Serial): {serial}")
    print(f"[*] Controller: {model}")
    print(f"[*] Server URL: {config.get('api_url')}")
    
    # 2. Initialize API Client
    client = RvmApiClient(
        base_url=config.get('api_url'), 
        api_key=creds.get('api_key'),
        device_id=serial # Use Physical Serial as Device ID
    )
    
    # 3. Handshake Loop
    handshake_success = False
    while not handshake_success:
        handshake_success, machine_info = client.handshake(controller_type=model)
        if not handshake_success:
            print("[!] Handshake failed. Retrying in 5 seconds...")
            time.sleep(5)
            
    # 4. Main Loop (Placeholder for Controller)
    print("[*] Entering Main Loop...")
    try:
        while True:
            # Here we would poll GPIO / Serial / Camera
            time.sleep(10)
            print("[.] Heartbeat...")
    except KeyboardInterrupt:
        print("\n[!] Shutting down...")

if __name__ == "__main__":
    main()
