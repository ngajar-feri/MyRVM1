---
trigger: always_on
---

name: mock-data-consistency
description: Ensures that all test, simulation, and placeholder data is clearly identified as such across @MyRVM-Server (PHP/Laravel) and @MyRVM-Edge (Python).

# Mock Data Consistency Rules (Cross-Project)

This rule ensures a clear distinction between real production data and simulated/mock data across the entire RVM ecosystem.

## Scope
- **@MyRVM-Server**: PHP (Laravel) controllers, seeds, tests, and factories.
- **@MyRVM-Edge**: Python scripts, config files, and hardware simulation tests.

## Naming Conventions

### 1. Variables and Functions
Use the `mock_` prefix for any variable, object, or function holding simulated data.

- **Python (Edge)**: `mock_device_id = "MOCK_ORIN_1"`
- **PHP (Server)**: `$mockMachine = Machine::factory()->make();`

### 2. Configuration and Environment Values
- **JSON/YAML**: `{"device_id": "MOCK_DEVICE_001"}`
- **.env (Server)**: `RVM_API_KEY=MOCK_API_KEY_FOR_LOCAL_TEST`

### 3. Database Seeding (Server)
When creating seeds for development/testing, ensure names or identifiers indicate they are not real production units.
- **Good**: `Machine::create(['name' => 'MOCK RVM STATION 1', 'serial_number' => 'MOCK-SN-001']);`

## Logging and Output
All output from test scripts or mock implementations MUST include a clear indicator.

- **Edge**: `print("[MOCK] Handshake simulated.")`
- **Server (CLI)**: `$this->info("[MOCK] Seeding test machine data...");`

## Core Principles
1. **Never Shadow Production Logic**: Mock data should never interfere with real hardware/database production checks.
2. **Transparency**: Explicitly state in the `walkthrough.md` or PR if a verification was performed using mock data.
3. **Environment Isolation**: Mock keys or IDs used in the @MyRVM-Server database must match the `MOCK_` IDs used in @MyRVM-Edge config files for testing.
