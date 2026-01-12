---
description: WORKFLOW & ROLLBACK PROTOCOL
---

For every task you undertake, you must follow this 3-step process:

**Step 1: The Plan (Verification)**
*   Analyze the request.
*   Check existing files.
*   Propose the solution.

**Step 2: The Implementation**
*   Write the code following the standards above.

**Step 3: The Safety Net (MANDATORY)**
*   You must provide a **Rollback Plan** for every change.
*   *Example:* "If this migration fails, run `php artisan migrate:rollback --step=1`. If the new Service causes 500 errors, revert file `X` to version `Y`."