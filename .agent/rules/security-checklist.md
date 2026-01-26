---
trigger: always_on
---

---
name: security-checklist
description: Mandatory security checklist for all code artifacts. Reviews code for injection vulnerabilities, authentication flaws, XSS/CSRF risks, sensitive data exposure, and file upload security. Use automatically during code reviews, before committing code involving user input, authentication, or database operations, or when the user requests a security review.
---

# Security Checklist

This skill acts as the **Security Auditor**. It is a mandatory check before any code is considered "Done".

## When to Use

- **Automatically active** during code reviews
- Before committing any code involving user input, authentication, or database operations
- When the user explicitly requests a security review

## Security Checklist

Copy this checklist and verify each item:

### 1. Injection Prevention

- [ ] **SQL Injection:** Using parameterized queries (PDO/ORM bindings)? No string concatenation in SQL.
- [ ] **Command Injection:** User inputs passed to `exec()`, `system()`, or `eval()`? (Forbidden)
- [ ] **Code Injection:** User inputs passed to `unserialize()`? (Forbidden)

### 2. Authentication & Authorization

- [ ] **Password Storage:** Passwords hashed using Bcrypt/Argon2? Never stored in plain text.
- [ ] **Session Security:** Session ID regenerated after login? (Prevents session fixation)
- [ ] **Access Control:** Every API endpoint checks authorization (`user->can('action')` or equivalent)?
- [ ] **IDOR Prevention:** Can users access other users' data by changing IDs in URLs/parameters?

### 3. Data Protection (XSS & CSRF)

- [ ] **Output Encoding:** All user-generated content escaped before display? (React escapes by default; watch for `dangerouslySetInnerHTML`)
- [ ] **CSRF Protection:** Forms include CSRF tokens?
- [ ] **Security Headers:** `X-Content-Type-Options: nosniff` and `Strict-Transport-Security` enabled?

### 4. Sensitive Data

- [ ] **Logging:** No passwords, API keys, or credit card numbers in logs?
- [ ] **Git Security:** `.env` and sensitive files in `.gitignore`?
- [ ] **Error Messages:** Stack traces disabled in production? Errors don't reveal sensitive information?

### 5. File Uploads

- [ ] **File Validation:** File type (MIME) and extension validated?
- [ ] **File Naming:** Uploaded files renamed to random UUIDs? (Prevents overwriting and execution)
- [ ] **Storage Location:** Files stored outside web root when possible?

## Review Process

1. **Go through each section** systematically
2. **Mark items as checked** only after verification
3. **Flag critical issues** that must be fixed before merge/deployment
4. **Document findings** with specific code locations and recommendations

## Critical vs. Warning

- **🔴 Critical:** Must fix before merge/deployment (injection vulnerabilities, exposed secrets, broken auth)
- **🟡 Warning:** Should fix soon (missing headers, weak validation, minor IDOR risks)
- **🟢 Info:** Best practice improvements (enhanced logging, additional validation layers)
