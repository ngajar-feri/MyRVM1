import os
import sys

sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..'))

from main import get_device_info

serial, model = get_device_info()

print("-" * 30)
print("HASIL DETEKSI HARDWARE")
print("-" * 30)
print(f"Hardware Serial : {serial}")
print(f"Controller Model: {model}")
print("-" * 30) 
