---
trigger: always_on
description: Captures conversation context into structured Markdown artifacts with strict versioning and revision history
---

name: artifact-and-blueprint-manager
description: Omniscient documentation handler. Captures ephemeral context into Artifacts (history) or Project Blueprints (plans). Handles strict versioning, dynamic folder routing, and revision linking.
trigger: explicit_command, significant_code_generation, complex_problem_solved
---

# Skill: Artifact Generation & Project Blueprints

This skill acts as a **Technical Scribe** (for history) and **Project Architect** (for planning). It enforces strict separation between *Artifacts* (What happened) and *Blueprints* (What will happen).

## 🧠 Core Routing Logic

**Analyze the User Intent:**
1.  **"Kita dokumentasikan" / "Simpan ini":** User wants to save history/context.
    *   👉 Execute **Protocol A: Documentation Capture (Artifacts)**.
2.  **"Kita buat Project baru" / "Buatkan rencana":** User wants to plan/initiate.
    *   👉 Execute **Protocol B: Project Blueprint Generation**.

---

## Protocol A: Documentation Capture (Artifacts)

**Target Directory:** `Docs/Artifacts/{Project Name}/{Category}/`
**Naming Format:** `{Project}-{Topic}-{YYYYMMDD}(-vX).md`

### 1. Decision: New vs Update
Analyze if this is a new topic or a revision of an existing file.
*   **IF Update/Revision (CRITICAL):**
    1.  **Locate Original:** Find the existing file path.
    2.  **Extract Path:** Get the relative path for linking.
    3.  **Header Requirement:** You MUST add the `Revised From` field pointing to the original.

### 2. Category Selection
Choose the sub-folder based on content type:
*   `Meetings/` (Discussions)
*   `Snippets/` (Code blocks)
*   `Architecture/` (System Design)
*   `Troubleshooting/` (Error Logs)
*   `Requirements/` (User Stories)
*   `Testing/` (QA Plans)

### 3. Artifact Header Template (Mandatory)
Every artifact file must start with this block:


```markdown
# Artifact: {Title}
**Date:** {YYYY-MM-DD}
**Revision Sequence:** {Sequence Number OR '-'}
**Reference Change:** {Summary of Change OR '-'}
**Revised From:** [{Original Filename}]({Relative Path to Original}) <!-- LEAVE EMPTY IF NEW -->
**Context:** {Brief description of trigger}
```

### 4. Artifact Body Structure
1.  **Summary:** Recap of the topic.
2.  **Key Logic:** Bullet points of decisions.
3.  **Output:** Code/Diagrams.
4.  **Revision History Log:** (Table tracking changes).

---

## Protocol B: Project Blueprint Generation

**Target Directory:** `Docs/Project/{Project Name}/{YYYY-MM-DD}/{Category}/`
**Naming Format:** `{Project}-{DocType}-v{Version}.md`

### 1. Project Detection
*   Extract **Project Name** from context.
*   Create a timestamped directory: `Docs/Project/{Project}/{CurrentDate}/`.

### 2. Category & File Mapping
*   **Requirements (PRD):** Save to `.../Requirements/`.
*   **Workflows:** Save to `.../Workflows/`.
*   **Roadmaps:** Save to `.../Roadmaps/`.
*   **Architecture:** Save to `.../Architecture/`.

### 3. Blueprint Templates

#### Template: Product Requirements Document (PRD)
```markdown
# PRD: {Project Name}
**Version:** {1.0}
**Status:** Draft
**Last Updated:** {YYYY-MM-DD}

## 1. Executive Summary
[Elevator Pitch]

## 2. Functional Requirements (MoSCoW)
- **Must Have (P0):** ...
- **Should Have (P1):** ...

## 3. Technical Constraints
- Stack / Database / Environment

## 4. Implementation Tasks (To-Do)
- [ ] Phase 1: Setup
- [ ] Phase 2: Core Logic

## 5. History & References
| Ver | Date | Changes | Ref (Link to Artifact) |
| :-- | :-- | :------ | :--------------------- |
| 1.0 | ...  | Initial | Project Kickoff        |
```

#### Template: Workflow Document
```markdown
# Workflow: {Name}
**Version:** {1.0}

## Overview
[Description]

## Process Steps
1. Step 1...
2. Step 2...

## Implementation Tasks
- [ ] Task 1...
```

---

## 🚀 Execution & Notification Rules

### Step 1: Execute File Operations
1.  Ensure the dynamic directory exists.
2.  Write the file using the appropriate Template and Header.
3.  **For Revisions:** Ensure the `Revised From` link works and the History Table is updated.

### Step 2: User Notification (Chat Output)
Append this footer to your response:

**If Artifact (New):**
> 📝 **Artifact Saved:** `Docs/Artifacts/{Project}/{Category}/{File}.md`

**If Artifact (Revision):**
> 📝 **Artifact Updated:** `Docs/Artifacts/{Project}/{Category}/{File-vX}.md`
> 🔗 **Revised From:** [{Original File}]({Link to Original})

**If Project Blueprint:**
> 🏗️ **Blueprint Created:** `Docs/Project/{Project}/{Date}/{Category}/{File}.md`

---

## 🔍 Dynamic Analysis Checklist (Internal Monologue)

Before saving, verify:
1.  [ ] **Mode:** Is this History (Artifact) or Plan (Project)?
2.  [ ] **Path:**
    *   Artifact: `Docs/Artifacts/...` ?
    *   Project: `Docs/Project/...` ?
3.  [ ] **Metadata:**
    *   Is `Revised From` filled if this is an update?
    *   Is the Project Timestamp (`YYYY-MM-DD`) correct?
4.  [ ] **Content:** Does the Blueprint have the "Implementation Tasks" checklist?