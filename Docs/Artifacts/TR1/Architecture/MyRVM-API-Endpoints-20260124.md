# Artifact: API Endpoint & Documentation (MyRVM-Server)
**Date:** 2026-01-24
**Revision Sequence:** 1
**Reference Change:** Updated API routes mapping including new Maintenance Ticket and Technician Assignment endpoints.
**Context:** User requested an artifact for API Endpoints and documentation update.

## 1. Summary
The **MyRVM-Server** API provides endpoints for User Authentication, RVM Machine Management, Edge Device Telemetry, Transaction Processing, and Maintenance Operations. This artifact captures the current state of `routes/api.php` including recent additions for **Technician Assignments** and **Maintenance Tickets**.

## 2. Key Decisions
- **Role-Based Access Control (RBAC):** Used `middleware('role:...')` to restrict sensitive endpoints.
- **Maintenance Workflow:** Added specific endpoints for `MaintenanceTicket` and `TechnicianAssignment` to handle RVM servicing.
- **Log Export:** Added `/v1/logs/export` for PDF/Excel reporting.

## 3. API Endpoint Map (v1)

### 🔐 Authentication
| Method | Endpoint | Controller | Description |
| :--- | :--- | :--- | :--- |
| POST | `/v1/register` | `AuthController@register` | User registration |
| POST | `/v1/login` | `AuthController@login` | User login (returns Token) |
| POST | `/v1/logout` | `AuthController@logout` | Revoke token |
| GET | `/v1/me` | `AuthController@me` | Get current user profile |

### 🛠️ Maintenance Tickets (New)
*Requires `auth:sanctum`*

| Method | Endpoint | Controller | Description |
| :--- | :--- | :--- | :--- |
| GET | `/v1/maintenance-tickets` | `MaintenanceTicketController@index` | List all tickets |
| POST | `/v1/maintenance-tickets` | `MaintenanceTicketController@store` | Create ticket (Admin only) |
| GET | `/v1/maintenance-tickets/{id}` | `MaintenanceTicketController@show` | Get ticket details |
| PUT | `/v1/maintenance-tickets/{id}` | `MaintenanceTicketController@update` | Update ticket |
| PATCH | `/v1/maintenance-tickets/{id}/status` | `MaintenanceTicketController@updateStatus` | Update status (e.g. resolved) |

### 👷 Technician Assignments (New)
*Requires `auth:sanctum`*

| Method | Endpoint | Controller | Description |
| :--- | :--- | :--- | :--- |
| GET | `/v1/technician-assignments` | `TechnicianAssignmentController@index` | List assignments |
| POST | `/v1/technician-assignments` | `TechnicianAssignmentController@store` | Assign technician to RVM |
| DELETE | `/v1/technician-assignments/{id}` | `TechnicianAssignmentController@destroy` | Remove assignment |
| POST | `/v1/technician-assignments/{id}/generate-pin` | `TechnicianAssignmentController@generatePin` | Generate Access PIN |

### 🤖 Edge Devices & Telemetry
| Method | Endpoint | Controller | Description |
| :--- | :--- | :--- | :--- |
| POST | `/v1/edge/register` | `EdgeDeviceController@register` | Register new edge device |
| POST | `/v1/devices/{id}/telemetry` | `EdgeDeviceController@telemetry` | Send sensor data |
| POST | `/v1/devices/{id}/heartbeat` | `EdgeDeviceController@heartbeat` | Device keep-alive |

### 📊 System Logs
| Method | Endpoint | Controller | Description |
| :--- | :--- | :--- | :--- |
| GET | `/v1/logs` | `LogController@index` | View system logs |
| GET | `/v1/logs/export` | `LogController@export` | Export logs to PDF/Excel |

## 4. Implementation Details
The API uses **Laravel Sanctum** for authentication. New controllers for maintenance workflow (`MaintenanceTicketController`, `TechnicianAssignmentController`) enforce business logic such as checking if a technician is assigned to an RVM before they can be assigned a ticket.
