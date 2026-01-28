# Changelog: MyRVM Project

All notable changes to this project will be documented in this file.

## [2026-01-28] - Hardware Integration & Discovery

### Added
- **`HardwareProbe` Service:** Dynamic scanning for USB Cameras, I2C devices, and Serial ports.
- **`Sensor Scaling`:** Automatic calculation of bin capacity (50cm empty -> 5cm full).
- **Physical Verification Audit:** Persistent record of 10-minute stress tests on Orin Nano.
- **`myrobot` User Support:** Full systemd service configuration for non-root hardware access.

### Changed
- **`HardwareManager`:** Integrated real-time discovery reporting into heartbeats.
- **`api_client.py`:** Heartbeats now include a `discovery` payload for remote monitoring.
- **Security:** Switched GPIO access to `rvm-devs` group permissions.

### Fixed
- **Jetson Detection:** Resolved "Engineering Reference" model error via `JETSON_MODEL_NAME` environment override.
- **`SensorDriver`:** Fixed `KeyError: 'trigger'` when initializing single-pin proximity sensors.
