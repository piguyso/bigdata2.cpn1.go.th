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

## Mandatory Asset Building & Cache Clearing Rule

1. **Automatic Asset Compiling & OPcache Clearing**:
   * **Rule:** The assistant **MUST** perform the following steps after making edits to any frontend assets, views, blade files, configuration, backend code, or styles, before ending its turn or reporting task completion:
     1. Run the compiled asset building command: `$env:PATH += ";C:\Program Files\nodejs"; npm run build`
     2. Send an HTTP request to clear PHP OPcache: Run `Invoke-WebRequest -UseBasicParsing -Uri "https://ee.cpn1.go.th/clear_opcache.php" -ErrorAction SilentlyContinue` via the terminal command or use the `read_url_content` tool.
   * This ensures that the Vite compiled bundle is up-to-date and the server's PHP OPcache is cleared, so that all changes are reflected immediately on the live server.

---

## API Design & Client-Side Integration Rules

This project uses **Laravel (web.php) + Axios (client)** — there is no separate `api.php` file. All API endpoints are defined in `routes/web.php` and consume CSRF-protected Laravel sessions.

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
