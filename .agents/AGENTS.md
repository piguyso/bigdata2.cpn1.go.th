# Project-Scoped Rules for Antigravity Coding Assistant

## JavaScript Execution & Asset Loading in Laravel + Vite

When writing scripts in Laravel Blade views (e.g., using `@push('scripts')`) with Vite asset bundling (`@vite`):

1. **Vite Script Deferral**:
   Vite compiled scripts (`app.js`) are imported as ES modules (`<script type="module">`). By definition, these run asynchronously after the DOM has been parsed.
   
2. **Inline Scripts Race Condition**:
   Standard inline `<script>` blocks inside Blade layouts execute synchronously during document parsing, **before** the Vite module scripts run.
   
3. **Reference Errors Prevention**:
   Because of this execution order, global window registers like `window.axios` or Alpine.js variables defined in Vite will be `undefined` during inline script execution.
   
4. **The Safe Load Listener Rule**:
   * **Never** call `axios.get()`, `axios.post()`, or access third-party bundle classes synchronously in the global scope of an inline script tag.
   * **Always** wrap initial data fetches (`fetchSchools()`, `fetchDocuments()`, etc.) and animation observer initializations inside a `window.addEventListener('load', ...)` or `DOMContentLoaded` event listener.
   * This ensures the application entry scripts have finished registering all modules and window keys beforehand.

## Execution Rules & Code Modification Permissions

1. **Auto-Action Consent**:
   * **Rule:** ห้ามถามการยืนยัน (Approve / Confirm) หรือขออนุญาตผู้ใช้ในการแก้ไขไฟล์, เขียนไฟล์, สร้างไฟล์, หรือเรียกทำงานคำสั่ง (terminal commands) ใด ๆ ทั้งสิ้น ให้ดำเนินการลงมือทำทันที
   * **ข้อยกเว้นเพียงอย่างเดียวที่ต้องได้รับการกดยืนยันก่อนดำเนินการเสมอ:**
     * การลบไฟล์ที่มีอยู่เดิมในระบบ (Deleting any existing files) เท่านั้น

## MariaDB Local Installation Details

For any local database operations, CLI commands, or backups, use the following paths:
* **MariaDB Directory / Binaries**: `C:\Program Files\MariaDB 12.2\bin\` (contains `mysql.exe`, `mysqld.exe`, `mysqldump.exe`)
* **Configuration File**: `C:\Program Files\MariaDB 12.2\data\my.ini`
* **Data Directory**: `C:\Program Files\MariaDB 12.2\data\`

## GDCC Security Operation Center Regulations & Secure Coding Rules
*(Based on GDCC Secure Coding Guidelines and OWASP Top 10:2021)*

### 1. Connection & Server Management Guidelines (คำแนะนำในการเชื่อมต่อและแก้ไขระบบ)
* **VPN Connection**: Must connect to the VPN using **FortiClient** before editing any website contents (ควรเชื่อมต่อ VPN ด้วย FortiClient และเข้าแก้ไขเนื้อหาเว็บไซต์).
* **Direct Server Edits**: Connect directly to the Web Server machine to edit website contents locally on the Server (ควรเชื่อมต่อไปยัง Web Server และเข้าแก้ไขเว็บไซต์เนื้อหาภายในเครื่อง Server).
* **Exception Request**: In case a URL is blocked by the GDCC WAF, administrators can request an exception by emailing GDCC Security Center to whitelist/exclude specific URLs under OWASP 10:2021 protection (ผู้ดูแลระบบสามารถขอ Add Exception สำหรับบาง URL ที่ถูกระงับได้ที่ GDCC Security Center).

### 2. Secure Coding Standards (OWASP Top 10:2021)
* **Injection (การป้องกันการแทรกคำสั่ง)**: 
  * Never concatenate raw user input into SQL queries, NoSQL queries, OS commands, or LDAP queries.
  * Always use parameterized queries (prepared statements) or Laravel Eloquent ORM.
* **Broken Authentication (การจัดการการยืนยันตัวตน)**: 
  * Secure all authentication mechanisms (login credentials, API keys, session tokens).
  * Use secure, non-reversible hashing algorithms like bcrypt for passwords. Keep session tokens in `HttpOnly` and `Secure` cookies.
* **Sensitive Data Exposure (การป้องกันข้อมูลสำคัญรั่วไหล)**: 
  * Carefully filter and protect personal data (e.g. ID card numbers, credit card details, phone numbers).
  * Encrypt sensitive database columns and enforce HTTPS for all transmissions.
* **XML External Entities - XXE (การจัดการ XML)**: 
  * Avoid SOAP web services and XML parsing where possible. Use JSON instead.
  * If XML processing is necessary, configure the XML parser to explicitly disable DTD (Document Type Definitions) and external entities.
* **Broken Access Control (การควบคุมสิทธิ์การเข้าถึง)**: 
  * Never trust authorization parameters sent from the client (e.g. user IDs or roles in the request body).
  * Always verify the user's permissions and session ownership directly on the server.
* **Security Misconfigurations (การจัดการการตั้งค่าระบบ)**: 
  * Avoid using default credentials, debug modes in production (`APP_DEBUG=false`), or exposing stack traces.
  * Set correct path permissions and implement required security headers (e.g., Content-Security-Policy, X-Frame-Options, X-Content-Type-Options). Do not output sensitive details in application logs.
* **Cross-Site Scripting - XSS (การป้องกันสคริปต์ข้ามไซต์)**: 
  * Do not use inline JavaScript in HTML attributes (e.g., `onerror`, `onload`, `onclick`). Use event listeners in separate script files instead.
  * Escape and encode all output data printed into HTML. Sanitize rich text inputs using libraries like DOMPurify.
* **Insecure Deserialization (การแปลงข้อมูลเป็นวัตถุที่ไม่ปลอดภัย)**: 
  * Validate JSON structure and input schemas (using Laravel Request validation, Zod, or Joi) before parsing/processing objects.
  * Avoid deserializing untrusted strings directly into executable classes.
* **Using Components with Known Vulnerabilities (การอัปเดต Library/Dependencies)**: 
  * Keep all software, server patches, packages, and vendor dependencies updated.
  * Periodically run dependency checks (e.g. `composer audit`, `npm audit`) and check CVE registers (https://cve.mitre.org/).
* **Insufficient Logging and Monitoring (การบันทึกและตรวจสอบเหตุการณ์)**: 
  * Log critical security events: login attempts (success/failure), administrative actions, access control failures.
  * Do **not** log raw passwords, credit cards, or personal identifiers in application log files. Protect logs from unauthorized modifications or deletions.




## API Design & Client-Side Integration Rules

This project uses **Laravel (web.php) + Axios (client)** — there is no separate `api.php` file. All API endpoints are defined in `routes/web.php` and consume CSRF-protected Laravel sessions.

---

## Subagent Profile: laravel-agent

ใช้ `laravel-agent` สำหรับงาน Laravel ที่มี UI/data flow หรือ feature ใหม่ที่ต้องเขียนแบบ API + client

### Core Rules

1. **API + Client เสมอ**
   * งาน Laravel ที่มีหน้าเว็บ ต้องแยก server endpoint และ client interaction
   * ใช้ `routes/web.php`, named routes, controller JSON, Blade + Alpine.js + Axios
   * หน้า import/dashboard ต้องมี API/data endpoint แยกจากหน้า view

2. **อ่าน `ux.md` ก่อนแก้ UI/UX ทุกครั้ง**
   * ก่อนแก้ Blade, form, input, modal, table, dashboard, import page, navigation ต้องเปิด `ux.md`
   * ถ้าไม่มี `ux.md` ต้องสร้างจากการสำรวจ UX/UI เดิมของเว็บก่อนเริ่มงาน
   * ถ้ามีคำสั่งแก้ UX/UI หรือเกิด pattern ใหม่ ต้องบันทึก decision ลง `ux.md` ทุกครั้ง

3. **ยึด UX/UI เดิมของเว็บ**
   * ใช้ `<x-layout>` สำหรับหน้าเว็บหลัก/admin รุ่นใหม่
   * หน้า import ต้องเทียบ pattern กับ `/admin/schoolmis`
   * ตรวจรูปแบบ input, form, upload box, summary card, preview, import history, toast, modal และ navigation ก่อนเขียน

4. **Security-first Laravel**
   * ทุก write endpoint ต้อง validate request ก่อน
   * admin route ต้องอยู่ใต้ `auth + role:admin`
   * upload ต้องใช้ allowlist extension/MIME และ controlled storage path
   * ใช้ `basename()` กับ token/path จาก client
   * ใช้ transaction สำหรับ import/replace/delete
   * ห้าม raw SQL จาก user input
   * Blade output ใช้ escaped output หรือ Alpine `x-text`
   * ห้าม trust role/user id จาก client

5. **Agent Communication**
   * คุยกับ agent หลักแบบ caveman: สั้นมาก แต่ technical ครบ
   * รายงานเฉพาะไฟล์ที่อ่าน/แก้, risk, decision, test result
   * ไม่ revert งานคนอื่นใน dirty workspace

---

## Subagent Profile: laravel_audit

ใช้ `laravel_audit` สำหรับตรวจงาน Laravel หลังแก้ code หรือก่อน merge โดยตรวจทั้ง UX/UI และ security

### Required References

1. เปิด `ux.md` ก่อน audit ทุกครั้ง
2. เปิด `security.md` ก่อน audit ทุกครั้ง
3. ถ้าไฟล์ใดไม่มี ต้องสร้างหรือแจ้ง agent หลักให้สร้างก่อน audit

### Audit Scope

1. **UX/UI Audit**
   * เทียบ Blade/UI กับ pattern ใน `ux.md`
   * ตรวจ layout, navigation, input, form, file upload, buttons, preview, table, modal, toast
   * หน้า import ต้องเทียบกับ `/admin/schoolmis`
   * ถ้ามี UX/UI decision ใหม่ ต้องบันทึกลง `ux.md`

2. **Security Audit**
   * ใช้กฎ `security_audit` ใน `security.md`
   * ตรวจ middleware `auth`, `role:admin`
   * ตรวจ request validation ก่อน write
   * ตรวจ upload allowlist และ safe temp path
   * ตรวจ DB transaction, scoped replace/delete, no raw SQL from user input
   * ตรวจ Blade escaping, Alpine `x-text`, no unsafe `x-html`
   * ตรวจ JSON response shape และ error handling

3. **URL Health Check**
   * ต้องเปิด URL ที่เกี่ยวข้องทุกครั้งเพื่อตรวจ error
   * ใช้ `Invoke-WebRequest -UseBasicParsing -Uri "<url>"`
   * public URL ควรได้ `200`
   * admin URL ที่ยังไม่ login ได้ `302` ได้ แต่ redirect target ต้องเป็น `/login` หรือ route login เท่านั้น
   * ตรวจ body ว่าไม่มี `Whoops`, `500 Server Error`, `Undefined variable`, `Route [`, `Vite manifest not found`, `Axios is not defined`, `Alpine is not defined`
   * ห้าม bypass auth เพื่อดู admin page
   * ถ้ามี browser automation/Playwright ให้ตรวจ browser console เพิ่ม
   * ถ้าไม่มี browser automation ให้รายงานว่าใช้ HTTP/body scan แทน

### Output Format

รายงานสั้น:

```text
PASS: ...
FAIL: file:line ...
RISK: ...
URL: ... status ...
TEST: ...
```

### Communication

* คุยกับ agent หลักแบบ caveman: สั้นมาก แต่ technical ครบ
* ไม่แก้ไฟล์เอง เว้นแต่ได้รับมอบหมายให้ fix
* ไม่ revert งานคนอื่นใน dirty workspace

---

## Main Agent Delegation Rule

เมื่อ main agent ได้รับงานที่เกี่ยวกับ Laravel, UX/UI, security, import, dashboard, API + client หรือการตรวจ code ให้พิจารณาสั่ง subagent ทำงานเสมอ ถ้างานนั้นแบ่งได้และไม่ทำให้ critical path ช้าลง

### Required Delegation Pattern

1. **ก่อนเริ่มงาน**
   * main agent อ่านคำสั่งผู้ใช้และแยกงานเป็นส่วน:
     * งานหลักที่ main agent ต้องทำเองทันที
     * งานข้างเคียงที่ subagent ทำคู่ขนานได้
   * ถ้าเป็นงานเขียน Laravel feature/UI ให้ใช้ `laravel-agent`
   * ถ้าเป็นงานตรวจ code/UX/security/URL ให้ใช้ `laravel_audit`

2. **ตอนสั่ง subagent**
   * ระบุ scope ชัดเจน:
     * ไฟล์ที่ให้ตรวจ/แก้
     * URL ที่ต้องเปิดดู
     * กฎที่ต้องอ่าน เช่น `ux.md`, `security.md`
     * สิ่งที่ห้ามทำ เช่น ห้าม revert งานคนอื่น
   * ถ้าให้แก้ code ต้องระบุ write set ให้ไม่ชน main agent หรือ subagent อื่น
   * ถ้าให้ audit ต้องบอกให้รายงานกลับเท่านั้น เว้นแต่สั่งให้ fix

3. **รายงานกลับ main agent**
   * subagent ต้องรายงานผลกลับ main agent เท่านั้น ไม่ถือว่าเป็นคำตอบสุดท้ายให้ผู้ใช้
   * format รายงานต้องสั้น:

```text
PASS: ...
FAIL: file:line ...
RISK: ...
URL: ... status ...
TEST: ...
CHANGED: ...
```

4. **main agent หลังรับรายงาน**
   * main agent ต้องอ่านรายงาน subagent
   * ตัดสินใจเองว่าจะ integrate/fix/ignore
   * ถ้ามี finding สำคัญ ต้องแก้หรือบอกผู้ใช้
   * final answer ต้องเป็นคำตอบของ main agent รวมผลทั้งหมด ไม่ใช่ paste รายงานดิบ

### laravel-agent และ laravel_audit Communication Loop

ระหว่างงาน Laravel ที่ใช้ทั้ง `laravel-agent` และ `laravel_audit` ให้ main agent ทำหน้าที่เป็นตัวกลางส่งข้อมูลระหว่างสอง agent ตลอดงาน

1. **ก่อนเขียน code**
   * main agent ส่ง scope, route, URL, UX requirement และ security requirement ให้ `laravel-agent`
   * main agent ส่ง plan เดียวกันให้ `laravel_audit` เพื่อเตรียม checklist จาก `ux.md` และ `security.md`

2. **ระหว่างเขียน code**
   * `laravel-agent` ต้องรายงาน decision สำคัญกลับ main agent เช่น files changed, route/API shape, validation, UI pattern
   * main agent ต้องส่ง decision เหล่านี้ต่อให้ `laravel_audit` เพื่อตรวจระหว่างทาง
   * ถ้า `laravel_audit` พบ risk ต้องรายงาน main agent
   * main agent ต้องส่ง risk ที่เกี่ยวกับ implementation ต่อให้ `laravel-agent` แก้หรือปรับ

3. **หลังเขียน code**
   * main agent สั่ง `laravel_audit` ตรวจ final patch, URL health check, UX, security
   * ถ้า audit fail:
     * main agent ส่ง finding ให้ `laravel-agent` หรือแก้เองถ้าเป็น critical path
     * audit ซ้ำเฉพาะจุดที่แก้
   * final answer ต้องระบุว่า audit ผ่านหรือเหลือ risk อะไร

4. **ข้อจำกัด**
   * subagent ไม่ต้องคุยกับผู้ใช้โดยตรง
   * subagent ไม่ถือสิทธิ์ตัดสินใจสุดท้าย
   * ถ้า platform ไม่มี direct subagent-to-subagent channel ให้ main agent forward ข้อความสำคัญให้แทน
   * ห้ามให้ทั้งสอง agent แก้ไฟล์เดียวกันพร้อมกัน เว้นแต่ main agent แบ่ง ownership ชัดเจน

### Public Agent Dashboard Logging

เมื่อ main agent ส่งข้อความหา subagent หรือรับรายงานจาก subagent ให้บันทึก log แบบปลอดภัยลง `public/agent-messages.json` เพื่อให้ `public/agent-dashboard.html` แสดงสถานะได้ตลอดเวลา

1. **ต้อง log เหตุการณ์เหล่านี้**
   * main agent ส่ง task ให้ `laravel-agent`
   * `laravel-agent` รายงานกลับ main agent
   * main agent ส่ง audit request ให้ `laravel_audit`
   * `laravel_audit` รายงานกลับ main agent
   * main agent forward finding/risk ระหว่างสอง agent

2. **รูปแบบไฟล์**

```json
{
  "updated_at": "ISO-8601 datetime",
  "agents": [
    { "name": "main-agent", "status": "active", "task": "router / integrator" },
    { "name": "laravel-agent", "status": "waiting", "task": "Laravel API + client builder" },
    { "name": "laravel_audit", "status": "waiting", "task": "UX + security auditor" }
  ],
  "messages": [
    {
      "at": "ISO-8601 datetime",
      "from": "main-agent",
      "to": "laravel-agent",
      "message": "short redacted summary"
    }
  ]
}
```

3. **Security**
   * log file อยู่ใน `public/` จึงห้ามใส่ secret, token, password, cookie, raw personal data, raw stack trace, full SQL หรือข้อมูล sensitive
   * ให้บันทึกเฉพาะ summary สั้นแบบ redacted
   * ถ้าข้อความ subagent มีข้อมูล sensitive ให้ main agent สรุปใหม่ก่อนเขียน log

4. **Dashboard URL**
   * เปิดผ่านเว็บที่ `/agent-dashboard.html`
   * dashboard poll `agent-messages.json` ทุก 1 วินาที
   * ถ้าไม่เห็นข้อมูลใหม่ ให้ตรวจว่า main agent append log แล้วหรือยัง

### Agent Live Dashboard Rule

เมื่อ main agent ส่งงานให้ `laravel-agent` หรือ `laravel_audit` หรือได้รับรายงานกลับ ต้องอัปเดตไฟล์ public dashboard เพื่อให้ผู้ใช้เปิดดูสถานะได้แบบ polling:

1. ไฟล์แสดงผลคือ `public/agent-dashboard.html`
2. ไฟล์ข้อมูลคือ `public/agent-messages.json`
3. ทุก message event ต้องเพิ่มใน `messages`:

```json
{
  "at": "ISO-8601 datetime",
  "from": "main-agent",
  "to": "laravel-agent",
  "message": "สรุปข้อความสั้น ไม่ใส่ secret/token/path sensitive"
}
```

4. ทุกครั้งต้องอัปเดต:
   * `status`
   * `updated_at`
   * `agents[].status`
   * `agents[].task`
5. ห้ามบันทึก secret, token, password, session cookie, personal identifier หรือ raw exception ที่มี path/SQL ละเอียดเกินจำเป็นลง dashboard JSON
6. dashboard เป็น public static file ดังนั้นข้อมูลต้องเป็น public-safe summary เท่านั้น

### When To Use Which Subagent

- `laravel-agent`
  - งานเขียน Laravel feature
  - งาน API + client
  - งานหน้า import/dashboard
  - งาน UX/UI ที่ต้องเทียบ pattern เดิม
  - ต้องอ่าน/อัปเดต `ux.md`

- `laravel_audit`
  - ตรวจหลังแก้ code
  - ตรวจ UX/UI ตาม `ux.md`
  - ตรวจ security ตาม `security.md`
  - เปิด URL ตรวจ error
  - ตรวจ route/middleware/validation/upload/transaction/output escaping

### Exceptions

ไม่ต้อง spawn subagent เมื่อ:
- งานเล็กมาก เช่นตอบคำถามสั้นหรือแก้ typo เดียว
- งานเป็น immediate blocker ที่ main agent ต้องทำเองทันที
- user สั่งชัดว่าไม่ต้องใช้ subagent
- เครื่องมือ subagent ไม่พร้อม

ถ้าไม่ใช้ subagent ในงานที่เกี่ยวกับ Laravel/UX/security ให้ main agent ระบุเหตุผลสั้น ๆ ใน internal progress หรือ final summary

### 1. API Route Conventions

All API routes MUST follow this naming and URL structure:

| Type | URL Pattern | Route Name Pattern | Auth Required |
|---|---|---|---|
| Public read | `GET /api/{resource}` | `api.{resource}.list` | No |
| Public single | `GET /api/{resource}/{id}` | `api.{resource}.show` | No |
| Admin read | `GET /admin/{resource}/data` | `admin.{resource}.data` | `auth + role:admin` |
| Admin create/update | `POST /admin/{resource}/save` | `admin.{resource}.save` | `auth + role:admin` |
| Admin delete | `DELETE /admin/{resource}/{id}` | `admin.{resource}.delete` | `auth + role:admin` |

* **Never** define API routes in `routes/api.php` — use `routes/web.php` only.
* **Always** name every route using `->name('...')` so Blade templates can use `{{ route('...') }}`.
* Group admin routes under `Route::middleware(['auth', 'role:admin'])` or `Route::middleware(['auth', 'role:admin,teacher'])` for teacher-accessible endpoints.

### 2. Controller API Response Shape

Every API endpoint controller method MUST return a consistent JSON structure:

```php
// ✅ Success response
return response()->json([
    'status'  => 'success',
    'message' => 'บันทึกข้อมูลสำเร็จ',
    'data'    => $data,          // array or collection
]);

// ✅ Error response
return response()->json([
    'status'  => 'error',
    'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
], 422);  // use appropriate HTTP status codes
```

Rules:
* Always include `status` (`'success'` or `'error'`) in every JSON response.
* Always include `message` — use Thai language for user-facing messages.
* Wrap list data in `data` key, not at root level.
* Use correct HTTP status codes: `200` OK, `201` Created, `422` Validation Error, `403` Forbidden, `404` Not Found, `500` Server Error.
* Wrap controller logic in `try/catch` and return structured error responses — never let unhandled exceptions leak stack traces.
* Validate all inputs using `$request->validate([...])` **before** any database write.

### 3. Client-Side Axios Usage Rules

The project uses **Axios** (bundled via `resources/js/app.js` with Vite) and **Alpine.js** for reactive UI.

#### 3.1 CSRF Token Handling
Axios is pre-configured in `app.js` with the Laravel CSRF token from the meta tag. Never manually set CSRF tokens in individual requests — rely on the global Axios setup:
```js
// ✅ This is already configured in app.js — do not duplicate
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
```

#### 3.2 Always Use Named Route Helpers in Blade
Never hardcode API URLs as strings in Blade/JS. Always use `{{ route('...') }}` to generate URLs:
```js
// ✅ Correct
axios.get('{{ route("api.schools.list") }}')
axios.post('{{ route("admin.schools.save") }}', payload)

// ❌ Wrong — hardcoded URL breaks in subdirectory deployments
axios.get('/api/schools')
```

#### 3.3 Wrap Fetches in Load Event
Per the **Safe Load Listener Rule**, never call Axios in the global scope of inline `<script>` blocks. Always wrap inside a `window.addEventListener('load', ...)`:
```js
// ✅ Correct
window.addEventListener('load', function() {
    fetchSchools();
});

// ❌ Wrong — axios may not be available yet
fetchSchools();
```

#### 3.4 Standard Fetch + Render Pattern
All data fetching in Blade views should follow this pattern:
```js
function fetchData() {
    axios.get('{{ route("api.resource.list") }}')
        .then(response => {
            if (response.data.status === 'success') {
                // handle data
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
        });
}
```

#### 3.5 Standard Save Pattern (Alpine.js)
For admin forms using Alpine.js, always follow this payload/save pattern:
```js
saveItem() {
    this.saving = true;
    const payload = { /* form fields */ };

    axios.post('{{ route("admin.resource.save") }}', payload)
        .then(response => {
            if (response.data.status === 'success') {
                this.showToast(response.data.message, 'success');
                this.modal.open = false;
                this.fetchData();         // refresh list
            } else {
                this.showToast(response.data.message || 'เกิดข้อผิดพลาด', 'error');
            }
        })
        .catch(error => {
            const msg = error.response?.data?.message ?? 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์';
            this.showToast(msg, 'error');
        })
        .finally(() => {
            this.saving = false;
        });
},
```

#### 3.6 Standard Delete Pattern (Alpine.js)
```js
deleteItem(id) {
    axios.delete(`{{ url('/admin/resource') }}/${id}`, {
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    })
        .then(response => {
            if (response.data.status === 'success') {
                this.showToast(response.data.message, 'success');
                this.fetchData();
            }
        })
        .catch(error => {
            this.showToast('ไม่สามารถลบข้อมูลได้', 'error');
        });
},
```

### 4. Access Control Rules

* **Public API endpoints** (`/api/*`): No authentication. Return only safe, non-sensitive public fields.
* **Admin API endpoints** (`/admin/*/data`, `/admin/*/save`, `/admin/*/delete`): Must be inside `Route::middleware(['auth', 'role:admin'])` group.
* **Teacher API endpoints**: Use `Route::middleware(['auth', 'role:admin,teacher'])` for endpoints accessible by both admins and teachers.
* **Never expose** `personalid`, `password`, raw emails, or personal identifiers in public API responses.
* **Always check** `$request->user()` on the server side for authenticated actions — never trust role/permission data sent from the client.

### 5. Data Sanitization & Output Rules

* **Input**: Always run `$request->validate()` with strict rules before saving. Use `nullable()` only for truly optional fields.
* **Output (API)**: Only select the columns needed using `->select([...])` or map the result — never return `SELECT *` in public endpoints.
* **Output (Blade)**: Always use `{{ $var }}` (escaped) not `{!! $var !!}` unless the content is explicitly safe HTML from a trusted source.
* **File Uploads**: Save to `storage/app/public/` via `Storage::disk('public')->put(...)`. Serve via `asset('storage/...')`. Validate MIME type and file size in the controller.

### 6. Adding a New API Resource (Checklist)

When adding a new resource (e.g., `announcements`), follow this checklist in order:

1. **Migration**: Create migration file with proper column types and indexes.
2. **Model**: Create Eloquent model in `app/Models/` with `$fillable` defined.
3. **Controller**: Create controller in `app/Http/Controllers/` with `getData()`, `store()`, `destroy()` methods following the response shape above.
4. **Routes**: Register routes in `routes/web.php` under the correct middleware group.
5. **Blade View** (admin): Create `resources/views/admin/{resource}.blade.php` using Alpine.js + Axios with standard fetch/save/delete patterns.
6. **Public API** (if needed): Add `GET /api/{resource}` public route and `getPublicList()` controller method returning sanitized data only.
7. **Build & Clear**: Run `npm run build` and clear OPcache.

---

## No-Page-Refresh (SPA-like) UI Rules

All form submissions and data mutations in this app **MUST NOT** cause a full page reload.  
Use **Axios + Alpine.js** for every user interaction that writes or reads data.

### 1. Core Principle — Never Use Traditional HTML Form Submit

```html
<!-- ❌ FORBIDDEN — causes page reload -->
<form method="POST" action="{{ route('profile.update') }}">
    @csrf
    <button type="submit">Save</button>
</form>

<!-- ✅ CORRECT — Axios + Alpine.js, no reload -->
<div x-data="myForm()">
    <button type="button" @click="save()">Save</button>
</div>
```

* **Never** use `<form method="POST">` with a submit button for data mutations.
* **Always** use `<button type="button" @click="...">` wired to an Alpine.js method that calls Axios.
* The only exception is the **logout form** and **account deletion**, which may use traditional POST for security redirect reasons.

### 2. Every Mutating Action Needs These 4 UI States

Every save/delete button **MUST** implement all four states:

| State | UI Requirement |
|---|---|
| **Idle** | Normal button, enabled |
| **Loading** | `fa-circle-notch fa-spin` icon + disabled, opacity-60 |
| **Success** | Toast notification (emerald/green) — bottom-right |
| **Error** | Toast notification (rose/red) + inline field errors |

```js
// Alpine.js template for a save button
{
    saving: false,
    save() {
        this.saving = true;
        axios.post(url, payload)
            .then(r  => this.showToast(r.data.message, 'success'))
            .catch(e => this.showToast(e.response?.data?.message ?? 'เกิดข้อผิดพลาด', 'error'))
            .finally(() => this.saving = false);
    }
}
```

### 3. Standard Toast Component (Copy-Paste Template)

Every form section that uses Axios MUST include this toast block inside its `x-data` wrapper:

```html
<!-- Toast — place inside the x-data section -->
<div
    x-show="toast.show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-end="opacity-0"
    :class="toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'"
    class="fixed bottom-6 right-6 z-[9999] text-white px-5 py-3.5 rounded-2xl shadow-xl flex items-center gap-3 text-xs font-bold"
    x-cloak
>
    <i :class="toast.type === 'success' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-xmark'"></i>
    <span x-text="toast.message"></span>
</div>
```

Alpine.js data object must include:
```js
toast: { show: false, message: '', type: 'success' },

showToast(message, type = 'success') {
    this.toast = { show: true, message, type };
    setTimeout(() => { this.toast.show = false; }, 3500);
},
```

### 4. Inline Field Validation Errors

Always display server-side validation errors inline under the relevant input — **never** via alert() or console:

```html
<!-- Under each input -->
<p class="mt-1.5 text-rose-500 text-[10px] font-bold"
   x-show="errors.field_name"
   x-text="errors.field_name"
   x-cloak>
</p>
```

```js
// In the .catch() handler, extract and map errors:
.catch(error => {
    if (error.response?.data?.errors) {
        const errs = error.response.data.errors;
        this.errors = {
            name:  errs.name?.[0]  || '',
            email: errs.email?.[0] || '',
        };
    }
    this.showToast(error.response?.data?.message ?? 'เกิดข้อผิดพลาด', 'error');
})
```

### 5. Loading Button Template

```html
<button type="button"
        @click="save()"
        :disabled="saving"
        class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600
               disabled:opacity-60 disabled:cursor-not-allowed
               active:scale-95 text-white font-bold text-xs py-3.5 px-7
               rounded-2xl shadow-lg transition cursor-pointer">
    <template x-if="saving">
        <i class="fa-solid fa-circle-notch fa-spin"></i>
    </template>
    <template x-if="!saving">
        <i class="fa-solid fa-floppy-disk"></i>
    </template>
    <span x-text="saving ? 'กำลังบันทึก...' : 'บันทึกการเปลี่ยนแปลง'"></span>
</button>
```

### 6. Password Fields — Required UX Enhancements

Any form with password input MUST include:

1. **Show/Hide toggle** — `<button type="button">` with eye icon that toggles `:type="show ? 'text' : 'password'"`
2. **Password strength bar** — thin `<div>` with dynamic width and color (rose → amber → emerald)
3. **Confirm match indicator** — real-time ✓/✗ under the confirmation field

```js
get passwordStrength() {
    const p = this.form.password;
    if (!p) return 0;
    let score = 0;
    if (p.length >= 8)           score += 25;
    if (p.length >= 12)          score += 15;
    if (/[A-Z]/.test(p))         score += 20;
    if (/[0-9]/.test(p))         score += 20;
    if (/[^A-Za-z0-9]/.test(p))  score += 20;
    return Math.min(score, 100);
},
get passwordStrengthColor() {
    const s = this.passwordStrength;
    if (s < 40)  return '#f43f5e';  // rose-500
    if (s < 70)  return '#f59e0b';  // amber-500
    return '#10b981';               // emerald-500
},
```

### 7. Data List Pages — Refresh Without Reload

After any create/update/delete action on a list page, **always call `this.fetchData()`** instead of `window.location.reload()`:

```js
// ✅ Correct — refresh only the data
saveItem() {
    axios.post(url, payload).then(r => {
        if (r.data.status === 'success') {
            this.modal.open = false;
            this.fetchData();   // re-fetch and re-render the list
            this.showToast(r.data.message, 'success');
        }
    });
},

// ❌ Wrong — full page reload
saveItem() {
    axios.post(url, payload).then(() => window.location.reload());
},
```

### 8. Post-Save State Cleanup

After a successful save, always reset relevant state:

```js
// Clear form fields
this.form.password = '';
this.form.password_confirmation = '';

// Clear errors
this.errors = {};

// Close modal (if applicable)
this.modal.open = false;

// Clear logo/file inputs
this.form.logo_data = '';
if (this.$refs.logoInput) this.$refs.logoInput.value = '';
```

---

## Mandatory Code Validation & Health Check

1. **Automatic Code Verification**:
   * **Rule:** The assistant **MUST** run the validation command `$env:PATH += ";C:\php\php-8.4.19-nts-Win32-vs17-x64"; php validate_code.php` after making any edits to PHP/Blade code files, before ending its turn or reporting task completion.
   * This ensures that any syntax errors or Laravel routing compile issues are caught and resolved immediately before delivery.
