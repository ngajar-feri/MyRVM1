import sys
import time
import json
import os
from src.services.api_client import RvmApiClient

# Constants
CONFIG_PATH = os.path.join(os.path.dirname(__file__), 'config', 'settings.json')
CREDENTIALS_PATH = os.path.join(os.path.dirname(__file__), 'config', 'credentials.json')

def load_config():
    if not os.path.exists(CONFIG_PATH):
        print("[!] Settings file not found. Generating default...")
        return {
            "api_url": "https://myrvm.penelitian.my.id/api/v1", 
            "device_id": "orin1", 
            "timeout": 10
        }
    with open(CONFIG_PATH, 'r') as f:
        return json.load(f)

def load_credentials():
    # In production, this might come from a secure vault or env vars
    # For now, we look for rvm-credentials.json or use hardcoded dev key
    if os.path.exists(CREDENTIALS_PATH):
        with open(CREDENTIALS_PATH, 'r') as f:
            return json.load(f)
    return {"api_key": "rvm_default_dev_key"} # Fallback

def main():
    print("=== MyRVM Edge Client v2.0 ===")
    
    # 1. Load Configuration
    config = load_config()
    creds = load_credentials()
    
    print(f"[*] Device ID: {config.get('device_id')}")
    print(f"[*] Server URL: {config.get('api_url')}")
    
    # 2. Initialize API Client
    client = RvmApiClient(
        base_url=config.get('api_url'), 
        api_key=creds.get('api_key'),
        device_id=config.get('device_id')
    )
    
    # 3. Handshake Loop
    handshake_success = False
    while not handshake_success:
        handshake_success, machine_info = client.handshake()
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
