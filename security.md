# Security Audit Rules

ใช้ไฟล์นี้คู่กับ `ux.md` ทุกครั้งที่ตรวจ code Laravel, Blade, API, import, upload, dashboard และ client interaction

## security_audit Scope

ตรวจอย่างน้อย:
- Routes และ middleware
- Controller validation
- Authorization / role guard
- File upload
- Database write / transaction
- Query safety
- Output escaping
- Client-side data flow
- Error handling
- URL health check หลังแก้/ตรวจ

## Route & Access Control

- Admin routes ต้องอยู่ใต้ `Route::middleware(['auth', 'role:admin'])`
- Teacher/shared routes ต้องระบุ role ชัดเจน เช่น `role:admin,teacher`
- Public API ต้องคืนเฉพาะข้อมูล public-safe
- ห้าม trust `user_id`, `role`, `school_id`, `created_by` จาก client โดยตรง
- ใช้ `auth()->id()` หรือ `$request->user()` จาก server เท่านั้น

## Request Validation

- ทุก write endpoint ต้อง `$request->validate([...])` ก่อน write
- ใช้ strict rules:
  - ปีการศึกษา: `digits:4`
  - รอบ: `integer|min:1|max:3`
  - enum/type: `in:` หรือ validate จาก registry
  - id: `integer|exists:...`
- Error response ต้องเป็น JSON มี `status`, `message`
- ห้ามปล่อย unhandled exception แสดง stack trace ต่อ user

## File Upload

- อนุญาตเฉพาะไฟล์ที่จำเป็น เช่น `csv`, `txt`, `xlsx`
- ต้อง validate file:
  - `required`
  - `file`
  - `mimes:csv,txt,xlsx`
  - max size ถ้าเหมาะสม
- ห้ามใช้ชื่อไฟล์จาก user เป็น path จริง
- temp token/path ต้องใช้ `basename()`
- path ต้องอยู่ใต้ storage folder ที่ควบคุมได้
- import ต้อง preview/validate ก่อนบันทึกฐานข้อมูล
- ถ้า schema ไม่ถูกต้อง ต้องไม่บันทึกข้อมูล

## Database Safety

- ใช้ Query Builder/Eloquent binding
- ห้ามต่อ raw SQL จาก request input
- write หลายตารางต้องใช้ `DB::transaction`
- replace/delete ต้องมี where scope ครบ เช่น `academic_year`, `term`, `data_type`
- ห้าม destructive operation แบบกว้าง เช่น delete ทั้งตาราง โดยไม่มี scope ชัดเจน
- upsert unique key ต้องป้องกันข้อมูลทับผิดชุด

## Output & XSS

- Blade ใช้ `{{ }}` ไม่ใช้ `{!! !!}` ยกเว้น trusted sanitized HTML
- Alpine ใช้ `x-text` ไม่ใช้ `x-html` ยกเว้น sanitized แล้ว
- ห้าม inline user content ใน JavaScript string โดยไม่ encode
- อย่าแสดง raw exception message ที่มี path/SQL/secret ต่อ user

## CSRF & Client

- ใช้ Axios global CSRF จาก `resources/js/app.js`
- ห้าม hardcode token ซ้ำในแต่ละ request ถ้า global setup มีแล้ว
- ใช้ named route helper ใน Blade JS เช่น `{{ route('...') }}`
- อย่า hardcode API URL ถ้าใช้ route helper ได้
- หลัง save/delete ให้ refresh data ผ่าน API ไม่ reload ทั้งหน้า

## URL Health Check

ทุกครั้งที่ตรวจหรือแก้หน้าเว็บ ต้องเปิด URL ที่เกี่ยวข้องเพื่อตรวจ error:

- ใช้ HTTP request เช่น `Invoke-WebRequest -UseBasicParsing -Uri "<url>"`
- ตรวจ HTTP status:
  - public page ควรเป็น `200`
  - protected admin page ถ้าไม่มี session อาจเป็น `302` ได้ แต่ redirect target ต้องเป็น `/login` หรือ route login เท่านั้น
- ตรวจ body ว่าไม่มี:
  - `Whoops`
  - `Laravel`
  - `500 Server Error`
  - `Undefined variable`
  - `Route [`
  - `Vite manifest not found`
  - `Axios is not defined`
  - `Alpine is not defined`
- ถ้าเป็น admin page ที่ต้อง login ให้รายงานว่า health check แบบ unauthenticated ได้ redirect หรือใช้ authenticated browser/session ถ้ามี
- ห้าม bypass auth เพื่อดู admin page
- ถ้ามี browser automation/Playwright ใช้งานได้ ให้เปิดหน้าจริงและตรวจ browser console เพิ่มว่าไม่มี JavaScript error
- ถ้าไม่มี browser automation ให้รายงานว่าไม่ได้ตรวจ console และใช้ HTTP/body scan แทน

ตัวอย่าง PowerShell:

```powershell
$response = Invoke-WebRequest -UseBasicParsing -Uri "http://localhost/admin/schoolmis" -MaximumRedirection 0 -ErrorAction SilentlyContinue
$response.StatusCode
```

## Audit Output Format

รายงานแบบสั้น:
- `PASS` สิ่งที่ผ่าน
- `FAIL` สิ่งที่ต้องแก้ พร้อมไฟล์/บรรทัด
- `RISK` ความเสี่ยงที่ยังเหลือ
- `URL` ผลเปิด URL และ HTTP status
- `TEST` command ที่รัน

## laravel_audit Rules

- อ่าน `ux.md` และ `security.md` ก่อน audit ทุกครั้ง
- ตรวจ code ตาม UX/UI pattern และ security rules
- เปิด URL ที่เกี่ยวข้องทุกครั้งเพื่อตรวจ error
- ถ้า URL ไม่ระบุ ให้ถาม agent หลัก หรือ infer จาก route ที่เปลี่ยน
- ไม่แก้ไฟล์เอง เว้นแต่ agent หลักมอบหมายให้ fix
- คุยกับ agent หลักแบบสั้นมาก แต่ technical ครบ
