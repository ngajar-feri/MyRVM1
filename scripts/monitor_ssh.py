import os
import time
import subprocess
import re

LOG_FILE = "/var/log/auth.log"

def monitor_ssh():
    print(f"[*] Memulai pemantauan SSH pada {LOG_FILE}...")
    
    # Membuka file log dan pindah ke akhir file
    try:
        with open(LOG_FILE, "r") as f:
            f.seek(0, os.SEEK_END)
            
            while True:
                line = f.readline()
                if not line:
                    time.sleep(0.1)
                    continue
                
                # Deteksi Login Berhasil
                if "sshd" in line and "Accepted" in line:
                    match = re.search(r"Accepted \w+ for (\w+) from ([\d.]+) port (\d+)", line)
                    if match:
                        user, ip, port = match.groups()
                        print(f"\n[!] ALERT: LOGIN BERHASIL")
                        print(f"    User: {user}")
                        print(f"    IP  : {ip}")
                        print(f"    Port: {port}")
                        print(f"    Waktu: {line[:15]}")

                # Deteksi Login Gagal (Potensi Brute Force)
                elif "sshd" in line and "Failed password" in line:
                    match = re.search(r"Failed password for (?:invalid user )?(\w+) from ([\d.]+) port (\d+)", line)
                    if match:
                        user, ip, port = match.groups()
                        print(f"\n[W] WARNING: LOGIN GAGAL")
                        print(f"    User: {user}")
                        print(f"    IP  : {ip}")
                        print(f"    Waktu: {line[:15]}")

    except PermissionError:
        print("[!] Error: Anda harus menjalankan script ini dengan sudo (sudo python3 monitor_ssh.py)")
    except KeyboardInterrupt:
        print("\n[*] Pemantauan dihentikan.")

if __name__ == "__main__":
    monitor_ssh()
