# AI Usage Disclosure (`AI_USAGE.md`)

This document discloses the use of AI assistance during this assessment, as permitted in Section 10 of the assignment instructions.

---

### 1. Tools Used
- **AI Coding Assistant** (Antigravity / LLM)

---

### 2. How AI Helped
1. Provided initial guidance on Laravel 11 routing syntax and controller setup.
2. Assisted in formulating the PHP Carbon date difference calculation for inclusive travel days (`diffInDays + 1`) and age calculation.
3. Helped write basic PHPUnit feature tests to verify API endpoints.

---

### 3. Incomplete / Incorrect Suggestions Encountered
- **Age Calculation Precision**:
  - The initial AI suggestion used `$dob->diffInYears($start)` without casting to integer, which returned a decimal float in newer Carbon versions.
  - *Fix*: Cast to `(int)$dob->diffInYears($start)` to obtain the completed integer age.

---

### 4. Verification Method
- Executed `php artisan test` locally.
- Manually tested the endpoints using curl and browser to confirm expected status codes (201, 200, 422).
