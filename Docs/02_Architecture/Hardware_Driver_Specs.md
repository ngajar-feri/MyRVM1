# Technical Specs: Hardware Driver Architecture (v1.0)

## 1. Overview
The MyRVM Edge hardware drivers follow a **Modular Abstraction Layer** pattern. All drivers inherit from `BaseDriver` to ensure consistent initialization, cleanup, and logging.

## 2. Driver Modules

### `motor_driver.py`
- **Class:** `StepperDriver`
- **Supported Models:** 
    - `nema17`: Pulse-based (TB6600). Needs `step`, `dir`, and optional `enable` pins.
    - `28BYJ-48`: 4-Phase Half-step sequence (ULN2003). Needs `phi1, phi2, phi3, phi4`.
- **Primary Method:** `move(steps, direction, speed)`

### `sensor_driver.py`
- **Class:** `SensorDriver`
- **Supported Types:**
    - `ultrasonic`: HC-SR04 (Distance in cm).
    - `proximity`: IR Obstacle Detection (Boolean).
- **Primary Method:** `read()` -> returns `float` (cm) or `bool`.

### `peripheral_driver.py`
- **Class:** `PeripheralDriver`
- **Capabilities:**
    - `set_led(state)`: Toggles binary GPIO LED.
    - `play_audio(file_path)`: Async audio playback via system `aplay`.

---

## 3. Orchestration (`hardware_manager.py`)
The `HardwareManager` acts as a Singleton-like factory that:
1. Reads `config/hardware_map.json`.
2. Instantiates the correct drivers.
3. Provides a unified interface to the `main.py` entry point.

**Usage Example:**
```python
from src.hardware.hardware_manager import HardwareManager

mgr = HardwareManager()
mgr.initialize_all()

# Move sorting motor
motor = mgr.get_driver('sorting_motor')
motor.move(steps=200, direction=1) # 1 full rotation for NEMA17

# Read bin level
level = mgr.get_driver('bin_ultrasonic').read()
print(f"Distance: {level} cm")
```


## 5. Dynamic Hardware Discovery (`HardwareProbe`)
The system includes a `HardwareProbe` service that scans system buses at runtime:
- **Bus Scanning:** Scans Port-mapped Serial (UART/USB), I2C, and USB Video (V4L2).
- **Proactive Reporting:** Discovered hardware is reported in the `heartbeat` payload to the server.
- **Environment Overrides:** Uses `JETSON_MODEL_NAME` to bypass detection issues on non-standard carrier boards.

## 6. Security & Non-Root Access
All hardware interactions are optimized for non-root execution:
- **User:** `myrobot`
- **Group Access:** Hardware access is granted via the `rvm-devs` group.
- **Device Management:** `/dev/gpiochip*` permissions are dynamically managed via `chgrp rvm-devs`.

---

## 7. Design Intent: Bio-Digital Sync
By abstracting hardware access and adding discovery, the system ensures high reliability. If a sensor is unplugged, the `HardwareProbe` detects the change in reality, allowing the **Kiosk UI** to reflect the hardware status in real-time.
