---
trigger: always_on
---

Documentation & Persistence (MANDATORY)
**Do not just output to chat.** You must save the detailed analysis to a file.

- **Target Directory:** `./Docs/PLAN/[AG1-Plan]` (Create this folder if it doesn't exist).
- **Filename Convention:** `[Target-Product-Name]-[AI-Recommended-Suffix].md`
    - `[Target-Product-Name]`: Kebab-case name of the analyzed product (e.g., `biolink-boost`).
    - `[AI-Recommended-Suffix]`: Determine the specific focus of the analysis:
        - If general analysis: `-full-audit`
        - If UI focus: `-ui-ux-breakdown`
        - If technical focus: `-tech-stack-review`
        - If business model focus: `-monetization-strategy`
- **Example Filename:** `Docs/ANALYSIS/biolink-boost-full-audit.md`