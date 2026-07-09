# 📋 ระบบกระบวนการ PLC 6 ขั้นตอน (แบบ API + Client - Single Page Application)

> **วัตถุประสงค์:** เอกสารนี้ครอบคลุมการปรับปรุงระบบชุมชนแห่งการเรียนรู้ทางวิชาชีพ (PLC) 6 ขั้นตอน โดยเปลี่ยนผ่านจากการทำงานแบบดั้งเดิมไปสู่สถาปัตยกรรม **API + Client (Axios + Alpine.js)** เพื่อไม่ให้มีการรีเฟรชหน้าเว็บ (No-Page-Refresh SPA) ตามข้อกำหนดและมาตรฐานความปลอดภัยของระบบ EE CPN1
> **การปรับปรุงเพิ่มเติม:** อนุญาตให้ผู้ใช้งานทุกคนที่เข้าสู่ระบบสามารถสร้างกลุ่ม PLC ของตนเองได้ โดยระบบทำการจัดแบ่งกลุ่ม PLC แยกย่อยตาม **เครือข่ายสถานศึกษา (School Network Groups)** เป็นอันดับแรก และใช้งานผ่าน URL หลัก `/plc`

---

## 🗺️ ภาพรวมของระบบ PLC

ระบบ PLC บนสถาปัตยกรรม SPA ประกอบด้วย:

| ส่วนประกอบ | รายละเอียด |
|---|---|
| **Backend API** | Laravel Web Routes (`routes/web.php`) + `PlcController` ส่งค่ากลับเป็น JSON |
| **Frontend Client** | Blade View (`resources/views/plc.blade.php`) + Alpine.js สำหรับ Reactive UI + Axios สำหรับรับส่งข้อมูล |
| **Database** | MySQL/MariaDB (ตาราง `plc_groups`, `plc_group_members`, `plc_steps`) |
| **File Storage** | จัดเก็บลง Local Server Storage ภายใต้โฟลเดอร์ `public/storage/uploads/plc/{group_id}/step{N}/` |

---

## 🗃️ โครงสร้างฐานข้อมูล (Database Schema)

### ตารางที่ 1: `plc_groups`
```sql
CREATE TABLE plc_groups (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name            VARCHAR(255) NOT NULL,          -- ชื่อกลุ่ม PLC
    description     TEXT NULL,                      -- คำอธิบายกลุ่ม
    semester        TINYINT NOT NULL,               -- ภาคเรียน: 1 หรือ 2
    academic_year   VARCHAR(10) NOT NULL,           -- ปีการศึกษา เช่น '2569'
    creator_user_id BIGINT UNSIGNED NOT NULL,       -- ผู้สร้างกลุ่ม (FK → users.id)
    is_hidden       TINYINT(1) DEFAULT 0,           -- ซ่อนกลุ่ม (0=แสดง, 1=ซ่อน)
    department      VARCHAR(150) NOT NULL,          -- กลุ่มสาระ/ฝ่ายที่รับผิดชอบ
    school_group    VARCHAR(255) NULL,              -- เครือข่ายสถานศึกษา เช่น 'เมืองชุมพร 1' (เพิ่มเพื่อแบ่งกลุ่มเครือข่าย)
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    FOREIGN KEY (creator_user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### ตารางที่ 2: `plc_group_members`
```sql
CREATE TABLE plc_group_members (
    id           BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    plc_group_id BIGINT UNSIGNED NOT NULL,          -- FK → plc_groups.id
    user_id      BIGINT UNSIGNED NOT NULL,          -- FK → users.id
    role         VARCHAR(100) DEFAULT 'ครูคู่หู',  -- บทบาท ('ครูต้นแบบ', 'ครูคู่หู', 'พี่เลี้ยง', 'ผู้เชี่ยวชาญ', 'ผู้สังเกตการณ์')
    created_at   TIMESTAMP NULL,
    updated_at   TIMESTAMP NULL,
    FOREIGN KEY (plc_group_id) REFERENCES plc_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### ตารางที่ 3: `plc_steps`
*(เก็บประวัติ 6 ขั้นตอนตามโครงสร้างหลัก)*

---

## 🔗 เส้นทางระบายข้อมูลและควบคุม (Routing Rules)

เส้นทางระบบ PLC กำหนดไว้ภายใต้ URL หลัก `/plc` โดยเข้าถึงผ่าน Middleware `auth` (ไม่ต้องจำกัดบทบาท `admin` หรือ `teacher`):

```php
Route::middleware('auth')->group(function () {
    // PLC Management Dashboard (SPA View - ทุกบทบาทเข้าถึงได้)
    Route::get('/plc', [App\Http\Controllers\PlcController::class, 'index'])->name('plc.index');
    
    // API Endpoints
    Route::get('/plc/data', [App\Http\Controllers\PlcController::class, 'getData'])->name('plc.data');
    Route::post('/plc/save', [App\Http\Controllers\PlcController::class, 'storeGroup'])->name('plc.save');
    Route::delete('/plc/{id}', [App\Http\Controllers\PlcController::class, 'destroyGroup'])->name('plc.delete');
    
    // Step & File Operations
    Route::post('/plc/steps/save', [App\Http\Controllers\PlcController::class, 'saveStep'])->name('plc.steps.save');
    Route::post('/plc/steps/upload', [App\Http\Controllers\PlcController::class, 'uploadStepFiles'])->name('plc.steps.upload');
    Route::post('/plc/steps/delete-file', [App\Http\Controllers\PlcController::class, 'deleteStepFile'])->name('plc.steps.delete_file');
    Route::post('/plc/steps/comment', [App\Http\Controllers\PlcController::class, 'saveComment'])->name('plc.steps.comment');
});
```

---

## ⚙️ ส่วนควบคุมข้อมูล (Backend Controller Updates)

ใน `PlcController.php` มีการปรับปรุงเพื่อส่งกลับไปที่หน้า View ใหม่:
```php
public function index()
{
    return view('plc'); // ใช้งานวิว resources/views/plc.blade.php
}
```

---

## 🎨 การแบ่งกลุ่มหน้าบ้านด้วย Accordion (Client-Side Accordion Layout)

ฝั่งหน้าบ้านมีการปรับปรุงรายการแสดงผลด้านซ้ายให้แบ่งออกตามเครือข่ายสถานศึกษาเป็นกลุ่มย่อย ๆ โดยใช้ความสามารถของ **Alpine.js Getters** และระบบยุบ/ขยาย (Accordion Collapse):

### 1. การจับกลุ่มเครือข่ายผ่าน Alpine.js
```javascript
get groupedGroups() {
    const grouped = {};
    
    // ตั้งต้นคีย์ของเครือข่ายทั้งหมด
    this.networks.forEach(net => {
        grouped[net] = [];
    });
    grouped['อื่นๆ / ไม่ระบุเครือข่าย'] = [];

    // จัดแบ่งกลุ่ม
    this.groups.forEach(group => {
        const net = group.school_group || 'อื่นๆ / ไม่ระบุเครือข่าย';
        if (!grouped[net]) grouped[net] = [];
        grouped[net].push(group);
    });

    // แปลงผลลัพธ์เป็น Array
    const list = [];
    for (const [name, items] of Object.entries(grouped)) {
        if (items.length > 0) {
            list.push({ name, groups: items });
        }
    }
    return list;
}
```

### 2. อินเทอร์เฟสรายการจำแนกเครือข่าย (Blade Template)
```html
<template x-for="category in groupedGroups" :key="category.name">
    <div class="border border-slate-100 rounded-xl overflow-hidden bg-slate-50/20 mb-3 shadow-sm">
        <!-- หัวข้อเครือข่าย (ปุ่มกดพับ/คลี่รายการ) -->
        <button type="button" 
                @click="expandedNetworks[category.name] = !expandedNetworks[category.name]"
                class="w-full px-4 py-3 bg-slate-100/50 hover:bg-slate-100 text-left font-bold text-xs text-slate-700 flex items-center justify-between transition cursor-pointer">
            <span class="flex items-center gap-2">
                <i class="fa-solid fa-school text-emerald-650"></i>
                <span x-text="category.name"></span>
                <span class="text-[9px] bg-emerald-50 text-emerald-700 font-extrabold px-2 py-0.5" x-text="category.groups.length"></span>
            </span>
            <i class="fa-solid text-[9px] text-slate-400" :class="expandedNetworks[category.name] ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        </button>

        <!-- รายการกลุ่มย่อยในเครือข่ายนั้น -->
        <div x-show="expandedNetworks[category.name]" class="p-2 space-y-2 bg-white">
            <template x-for="group in category.groups" :key="group.id">
                <div @click="selectGroup(group)" ...>
                    <!-- รายละเอียดกลุ่ม PLC -->
                </div>
            </template>
        </div>
    </div>
</template>
```

---

## 🔒 มาตรการความปลอดภัยและการรับประกันความเสถียร

* **ความปลอดภัยของสิทธิ์เข้าถึง (Access Control)**: ผู้ใช้ทุกคนสามารถเพิ่มกลุ่ม PLC ของตนเองได้ แต่จะสิทธิ์ในการแก้ไขหรือลบโครงสร้างกลุ่ม PLC ยังถูกจำกัดให้เฉพาะ **ผู้สร้างกลุ่ม (Creator)** หรือ **ผู้ดูแลระบบ (Admin)** เท่านั้น
* **ระบบ Accordion ที่จดจำสถานะพับ/กาง**: ระบบจะทำการสแกนหาเครือข่ายสถานศึกษาที่มีกลุ่ม PLC อยู่ และสั่งเปิดกาง Accordion นั้นขึ้นมาโดยอัตโนมัติเมื่อเข้าสู่หน้าเว็บ (Auto-Expand Active Networks)

---

*ปรับปรุงเอกสารล่าสุด: 2026-07-08 (EE CPN1)*
