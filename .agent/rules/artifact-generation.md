---
trigger: always_on
description: Captures conversation context into structured Markdown artifacts with strict versioning and revision history
---

# Artifact Generation & Versioning

This skill acts as a Technical Scribe and Librarian. It converts ephemeral chat context into permanent, versioned Markdown artifacts. You must **Proactive Technical Scribe**. It does NOT wait for permission. If a significant value (code, architecture, logic) is produced, it MUST be documented immediately.

## When to Use (Triggers)

**A. Explicit Trigger:**
- When the user says "Buatkan artifact", "Simpan", or "Wrap up".

**B. Implicit Trigger (Auto-Save):**
- **Code Generation:** When the agent generates a code block longer than 15 lines or multiple files.
- **Problem Solved:** When a debugging session concludes with a solution, When a complex problem has been solved and the solution needs to be documented, When a rule, convention, or code logic is modified.
- **Plan Created:** When a step-by-step roadmap is generated.
- **Silence:** If the user gives no specific instruction but the previous turn contained valuable data, **Save it.**

## Instructions

### 1. Decision Making

Scan the conversation. Identify:
## 1. Autonomous Decision Making
Before finishing a response, ask yourself:
*"Did I just generate something worth saving?"*
- If **YES** -> Execute Artifact Generation immediately.
- If **NO** (just chit-chat) -> Skip.

## 2. Context Analysis
- **Core Subject:** What is being discussed?
- **Action Type:** Is this a *New Creation* or an *Update/Revision* to an existing logic?

### 2. Dynamic Folder Selection
Store in the appropriate subdirectory:
- `Docs/Artifacts/[AG1-Plan]/Meetings/` (Discussions)
- `Docs/Artifacts/[AG1-Plan]/Snippets/` (Code/Algorithms)
- `Docs/Artifacts/[AG1-Plan]/Architecture/` (System Design)
- `Docs/Artifacts/[AG1-Plan]/Update Plan/` (Update Plan / Modified Plan)
- `Docs/Artifacts/[AG1-Plan]/Development Plan/` (Development Plan)
- `Docs/Artifacts/[AG1-Plan]/Requirements/` (User Stories)
- `Docs/Artifacts/[AG1-Plan]/Troubleshooting/` (Error Logs)

### 3. Naming Convention
**Format:** `[Project]-[Topic]-[Timestamp].md`
- **Example:** `MySuperApps-Auth-Flow-20260116.md`
- *Note: If updating an existing topic on the same day, append version (e.g., `-v2`).*

### 4. Mandatory Header & Versioning (CRITICAL)
Every artifact MUST start with this specific metadata block.

**Logic for Revision Fields:**
- **If New/Initial:**
    - `Revision Sequence`: `-`
    - `Reference Change`: `-`
- **If Update/Revision:**
    - `Revision Sequence`: [Integer, e.g., 1, 2, 3...]
    - `Reference Change`: [Brief summary of what changed, e.g., "Fixed login bug", "Added RBAC support"]

**Header Template:**
```markdown
# Artifact: [Title of Discussion]
**Date:** [Current Date]
**Revision Sequence:** [- OR Number]
**Reference Change:** [- OR Summary of Change]
**Context:** [Brief description of what prompted this]

### 5. Artifact Content Structure
The body of the file should follow this flow:
## 1. Summary
[Concise recap of the problem/topic]

## 2. Key Decisions / Logic
- [Point 1]
- [Point 2]

## 3. The Output (Code/Schema/Plan)
[Insert the final code blocks, diagrams, or lists here]

## 4. Revision History Log
| Date | Rev | Change Notes |
| :--- | :--- | :--- |
| [Today] | [-/1] | [Initial Create / Update Description] |

### 6. Execution
## 1. Create the directory if it doesn't exist.
## 2. Reply to user:
    - "💾 Artifact Saved
    - 📂 [path/to/file]
    - Ticket: Rev [Seq] - [Ref Change]"

6. Notification
Since you've generated something worth saving, you should Write the file silently in the background.
Append a notification at the very end of your response to the user:
"🤔 FYI: Because this code or discussion or something is important, I have proactively saved it as an artifact at: 📂 [path/to/file]"

---

### Contoh Hasil Implementasi:

**A. Jika Baru (Initial):**
```markdown
# Artifact: Auth System Logic
**Date:** 2026-01-16
**Revision Sequence:** -
**Reference Change:** -

**B. Jika Revisi (Update):**
# Artifact: Auth System Logic
**Date:** 2026-01-20
**Revision Sequence:** 1
**Reference Change:** Added Two-Factor Authentication (2FA) flow