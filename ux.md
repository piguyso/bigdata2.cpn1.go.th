# UX/UI Guidelines

ใช้ไฟล์นี้เป็น reference ก่อนแก้ UI/UX ทุกครั้ง โดยเฉพาะงาน Laravel แบบ API + client

## Layout หลัก

- หน้า public/admin รุ่นใหม่ใช้ `<x-layout>` ไม่ใช่ `<x-app-layout>`
- font หลักคือ `Anuphan` และ fallback `Inter`
- background หลัก `#f8fafc`
- สีหลักของระบบคือ orange เช่น `orange-500`, `orange-600`
- navigation อยู่ใน `resources/views/components/layout.blade.php`
- nav desktop ใช้ dropdown แบบ hover ด้วย Alpine `x-data`, `x-show`, `x-transition`
- mobile menu แยก section ใต้ hamburger ต้องเพิ่มเมนูทั้ง desktop และ mobile เสมอ

## Navigation Pattern

- เมนูหลักใช้ icon Font Awesome ขนาดเล็ก สี orange สำหรับหัวข้อหลัก
- dropdown item ใช้ class ประมาณ:
  - `flex items-center gap-3 px-4 py-3 text-xs font-bold`
  - `text-slate-600 hover:text-orange-600 hover:bg-orange-50`
  - `rounded-xl transition duration-200`
- mobile submenu item ใช้ `ml-6 block text-sm font-bold ... py-2 flex items-center gap-2`
- ถ้าเพิ่มเมนู admin import ต้องเพิ่มในกลุ่ม `นำเข้าข้อมูล` ทั้ง desktop และ mobile

## Admin Import Page Pattern

ต้นแบบหลักคือ `resources/views/admin/schoolmis.blade.php`

โครงหน้า:
- ใช้ `<x-layout>`
- ตั้ง title ด้วย `<x-slot:title>`
- wrapper: `py-10 max-w-7xl mx-auto px-6`
- root Alpine component เช่น `x-data="schoolmisManager()" x-init="init()"`
- มี toast fixed bottom-right:
  - success `bg-emerald-500`
  - error `bg-rose-500`
- header มี eyebrow, H2, description และ action buttons ด้านขวา
- summary cards 4 ช่อง ใช้ white card, `rounded-2xl`, `border-slate-100`, `shadow-sm`
- content ใช้ grid:
  - left: upload/preview/import
  - right: import history
  - `xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]`

## Form/Input Pattern

- ใช้ class `.form-input` ในหน้า admin import
- input/select:
  - border `#e2e8f0`
  - radius `0.75rem`
  - background `#f8fafc`
  - padding `0.625rem 1rem`
  - font-size `0.75rem`
  - focus border orange และ shadow orange อ่อน
- label ใช้ `text-xs font-bold text-slate-500`
- required field ใส่ `*`
- file upload ใช้ dashed card:
  - `border-2 border-dashed border-slate-200 rounded-2xl p-5 bg-slate-50/50`
  - hover `border-orange-300`
  - มี icon box `w-12 h-12 rounded-2xl bg-orange-100 text-orange-600`

## Button Pattern

- Primary validate/import:
  - ตรวจสอบไฟล์: orange button
  - ยืนยันนำเข้า: emerald button
  - ลบข้อมูล: rose button
- button ใช้:
  - `px-5 py-2.5 rounded-xl font-bold text-xs`
  - `inline-flex items-center justify-center gap-2`
  - loading icon `fa-circle-notch animate-spin`
- ปุ่มต้อง disabled ระหว่าง loading และก่อน preview ผ่าน

## Preview/Validation Pattern

ทุกหน้า import ต้องมี preview ก่อน import:
- แสดง summary cards:
  - แถวทั้งหมด
  - แถวที่อ่านได้
  - แถวไม่สมบูรณ์/ผิดโครงสร้าง
  - ไม่ match โรงเรียน
- warning ใช้ amber box:
  - `bg-amber-50 border border-amber-200 rounded-2xl`
- sample table ใช้ compact table:
  - header `text-slate-400 font-bold`
  - row divider `divide-y divide-slate-50`
- แสดงข้อมูลไฟล์ที่ระบบตรวจพบ เช่น ชื่อไฟล์, schema, ปี/รอบ, mode
- ห้าม import ถ้ายังไม่มี `preview.uploadToken`

## API + Client Pattern

- หน้า client ใช้ Alpine + axios
- controller คืน JSON ด้วย `status`, `message`, `data` หรือ shape เฉพาะ dashboard
- form submit ผ่าน axios
- import flow แยก endpoint:
  - `GET .../data`
  - `POST .../preview`
  - `POST .../import`
  - optional `DELETE .../data-set`
- ต้อง validate request ใน controller ก่อนส่ง service
- งาน write ใช้ DB transaction ใน service/controller

## Security Rules

- route admin ต้องอยู่ใน middleware `auth` และ `role:admin`
- upload ต้อง validate:
  - required file
  - allowlist extension/MIME เช่น `csv,txt,xlsx`
  - ไม่ใช้ filename ตรงจาก user เป็น path
  - ใช้ `basename()` กับ upload token
- import ต้อง preview ก่อน และตรวจ token ไฟล์ชั่วคราวก่อนบันทึก
- query ใช้ query builder/Eloquent binding ห้ามต่อ SQL string จาก input
- output ใน Blade ให้ใช้ `{{ }}` หรือ Alpine `x-text` ไม่ใช้ `x-html` ถ้าไม่จำเป็น
- replace/delete ต้องจำกัดด้วย `academic_year`, `term`, `data_type`
- file template download ต้อง validate data type จาก registry ก่อน

## Student Extra Import Decision

- หน้า `ข้อมูลนักเรียนเพิ่มเติม` ต้องใช้ template เดียวกับ `/admin/schoolmis`
- เมนูต้องอยู่ที่ `นำเข้าข้อมูล -> ข้อมูลนักเรียนเพิ่มเติม`
- ไม่รวม `จำนวนนักเรียนแยกชั้น,เพศ` เพราะมีใน SchoolMIS แล้ว
- ต้องมี `Download Template` ตามชนิดข้อมูลที่เลือก
- ต้อง preview/validate ก่อน import เสมอ
