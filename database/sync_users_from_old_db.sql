-- ============================================================
-- Users Sync Script: e.cpn1.go.th → ee_cpn1_go_th
-- Date: 2026-07-08
-- สิ่งที่ทำ:
--   1. INSERT users ที่หายไป (กรณี id ยังไม่มีในฝั่งใหม่)
--   2. UPDATE name ใน users ให้มีคำนำหน้า (title+fname+lname)
--   3. UPDATE legacy_users ให้ตรงกับข้อมูลล่าสุดจากฝั่งเก่า
-- กลยุทธ์: ปลอดภัย 100% — ไม่แตะ password, role(ของ admin), session
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- STEP 1: INSERT users ที่ยังไม่มีใน ee_cpn1_go_th
--         (จับคู่ด้วย id, role mapping: admin→admin, viewer→user)
-- ============================================================
INSERT IGNORE INTO ee_cpn1_go_th.users (
    id,
    name,
    email,
    role,
    password,
    created_at,
    updated_at
)
SELECT
    u.id,
    CONCAT(u.title, u.fname, ' ', u.lname)  AS name,
    u.email,
    CASE WHEN u.role = 'admin' THEN 'admin' ELSE 'user' END AS role,
    '!external_api_login!'                  AS password,  -- placeholder
    u.created_at,
    NOW()
FROM `e.cpn1.go.th`.users u
WHERE u.id NOT IN (SELECT id FROM ee_cpn1_go_th.users);

SELECT ROW_COUNT() AS new_users_inserted;

-- ============================================================
-- STEP 2: UPDATE name ใน ee_cpn1_go_th.users
--         ให้มีคำนำหน้า (title+fname+lname) จากฝั่งเก่า
--         เฉพาะ rows ที่ name ไม่ตรงกัน (ป้องกัน update ที่ไม่จำเป็น)
--         *** ไม่แตะ id=1 (system admin) ***
-- ============================================================
UPDATE ee_cpn1_go_th.users nu
JOIN `e.cpn1.go.th`.users old ON old.id = nu.id
SET nu.name = CONCAT(old.title, old.fname, ' ', old.lname),
    nu.updated_at = NOW()
WHERE nu.id != 1
  AND nu.name != CONCAT(old.title, old.fname, ' ', old.lname);

SELECT ROW_COUNT() AS users_name_updated;

-- ============================================================
-- STEP 3: INSERT legacy_users ที่ยังไม่มี
-- ============================================================
INSERT IGNORE INTO ee_cpn1_go_th.legacy_users (
    id, user_id, username, title, fname, lname,
    personalid, school, email, user_level,
    password_hash, role, is_active, last_login_at,
    created_at, last_login
)
SELECT
    id, user_id, username, title, fname, lname,
    personalid, school, email, user_level,
    password_hash, role, is_active, last_login_at,
    created_at, last_login
FROM `e.cpn1.go.th`.users;

SELECT ROW_COUNT() AS legacy_users_inserted;

-- ============================================================
-- STEP 4: UPDATE legacy_users ให้ sync ข้อมูลล่าสุด
--         (last_login_at, is_active, last_login)
-- ============================================================
UPDATE ee_cpn1_go_th.legacy_users lu
JOIN `e.cpn1.go.th`.users old ON old.id = lu.id
SET
    lu.last_login_at = old.last_login_at,
    lu.last_login    = old.last_login,
    lu.is_active     = old.is_active,
    lu.title         = old.title,
    lu.fname         = old.fname,
    lu.lname         = old.lname,
    lu.email         = old.email,
    lu.personalid    = old.personalid,
    lu.school        = old.school,
    lu.user_level    = old.user_level,
    lu.role          = old.role,
    lu.password_hash = old.password_hash,
    lu.updated_at    = NOW()
WHERE
    lu.last_login_at  != old.last_login_at
    OR lu.is_active   != old.is_active
    OR lu.title       != old.title
    OR lu.fname       != old.fname
    OR lu.lname       != old.lname
    OR lu.email       != old.email
    OR lu.personalid  != old.personalid
    OR lu.school      != old.school
    OR lu.user_level  != old.user_level
    OR lu.role        != old.role
    OR lu.password_hash != old.password_hash;

SELECT ROW_COUNT() AS legacy_users_updated;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- SUMMARY
-- ============================================================
SELECT 'users (Laravel)'  AS table_name, COUNT(*) AS total FROM ee_cpn1_go_th.users
UNION ALL
SELECT 'legacy_users',                   COUNT(*) FROM ee_cpn1_go_th.legacy_users;

-- ตรวจสอบตัวอย่าง name ที่อัปเดตแล้ว
SELECT id, name, email, role FROM ee_cpn1_go_th.users WHERE id != 1 ORDER BY id LIMIT 10;
