-- ============================================================
-- FULL SYNC SCRIPT: e.cpn1.go.th  →  ee_cpn1_go_th
-- วันที่: 2026-07-08
--
-- กลยุทธ์: REPLACE INTO (= INSERT หากยังไม่มี, UPDATE หากมีแล้ว)
-- ✅ ปลอดภัย: ไม่ DROP ตาราง ไม่กระทบ Laravel system tables
-- ✅ ครอบคลุม: ระบุ column list ชัดเจน ป้องกัน column mismatch
-- ✅ mapping: survey_* → teacher_*, users → legacy_users + users
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- ────────────────────────────────────────────────────────────
-- 1. BANNERS
--    old cols : id,title,image_path,link_url,sort_order,is_active,created_at,created_by
--    new extra: updated_at (nullable, auto)
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.banners
    (id, title, image_path, link_url, sort_order, is_active, created_at, created_by)
SELECT  id, title, image_path, link_url, sort_order, is_active, created_at, created_by
FROM `e.cpn1.go.th`.banners;

SELECT ROW_COUNT() AS `banners → replaced`;

-- ────────────────────────────────────────────────────────────
-- 2. SYSTEM_GROUP
--    old cols : id,code,name
--    new extra: created_at, updated_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.system_group
    (id, code, name)
SELECT  id, code, name
FROM `e.cpn1.go.th`.system_group;

SELECT ROW_COUNT() AS `system_group → replaced`;

-- ────────────────────────────────────────────────────────────
-- 3. SYSTEM_SCHOOL
--    old cols : id,smis,percode,ministry,schoolname,schoolname_eng,schoolgroup,muti,
--               road,muban,tambon,amper,province,postcode,lat,lng,length_km,maplink,
--               tel,email,website,statusID,statusDetail
--    new extra: created_at, updated_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.system_school
    (id, smis, percode, ministry, schoolname, schoolname_eng, schoolgroup, muti,
     road, muban, tambon, amper, province, postcode, lat, lng, length_km, maplink,
     tel, email, website, statusID, statusDetail)
SELECT  id, smis, percode, ministry, schoolname, schoolname_eng, schoolgroup, muti,
        road, muban, tambon, amper, province, postcode, lat, lng, length_km, maplink,
        tel, email, website, statusID, statusDetail
FROM `e.cpn1.go.th`.system_school;

SELECT ROW_COUNT() AS `system_school → replaced`;

-- ────────────────────────────────────────────────────────────
-- 4. SYSTEM_ANNOUNCEMENTS
--    old cols : id,message,is_active,updated_at,updated_by
--    new extra: created_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.system_announcements
    (id, message, is_active, updated_at, updated_by)
SELECT  id, message, is_active, updated_at, updated_by
FROM `e.cpn1.go.th`.system_announcements;

SELECT ROW_COUNT() AS `system_announcements → replaced`;

-- ────────────────────────────────────────────────────────────
-- 5. OBEC_MAJORS
--    cols เหมือนกัน: id,name,is_active,created_at,updated_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.obec_majors
    (id, name, is_active, created_at, updated_at)
SELECT  id, name, is_active, created_at, updated_at
FROM `e.cpn1.go.th`.obec_majors;

SELECT ROW_COUNT() AS `obec_majors → replaced`;

-- ────────────────────────────────────────────────────────────
-- 6. LOGIN_ATTEMPTS
--    old cols : id,ip_address,attempted_at
--    new extra: created_at, updated_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.login_attempts
    (id, ip_address, attempted_at)
SELECT  id, ip_address, attempted_at
FROM `e.cpn1.go.th`.login_attempts;

SELECT ROW_COUNT() AS `login_attempts → replaced`;

-- ────────────────────────────────────────────────────────────
-- 7. LMS_COURSES
--    cols เหมือนกัน: id,title,description,cover_url,certificate_bg_url,
--    pass_threshold,thumbnail_url,category,level,status,created_by,created_at,updated_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.lms_courses
    (id, title, description, cover_url, certificate_bg_url,
     pass_threshold, thumbnail_url, category, level, status,
     created_by, created_at, updated_at)
SELECT  id, title, description, cover_url, certificate_bg_url,
        pass_threshold, thumbnail_url, category, level, status,
        created_by, created_at, updated_at
FROM `e.cpn1.go.th`.lms_courses;

SELECT ROW_COUNT() AS `lms_courses → replaced`;

-- ────────────────────────────────────────────────────────────
-- 8. LMS_LESSONS
--    old cols : id,course_id,title,content_type,content_url,content_html,rubric_html,
--               content_text,sort_order,min_focus_seconds,require_submission,
--               min_video_seconds,duration_min,created_at
--    new extra: updated_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.lms_lessons
    (id, course_id, title, content_type, content_url, content_html, rubric_html,
     content_text, sort_order, min_focus_seconds, require_submission,
     min_video_seconds, duration_min, created_at)
SELECT  id, course_id, title, content_type, content_url, content_html, rubric_html,
        content_text, sort_order, min_focus_seconds, require_submission,
        min_video_seconds, duration_min, created_at
FROM `e.cpn1.go.th`.lms_lessons;

SELECT ROW_COUNT() AS `lms_lessons → replaced`;

-- ────────────────────────────────────────────────────────────
-- 9. LMS_QUIZZES
--    old cols : id,course_id,quiz_type,title,is_active,header_image,instructions,
--               draw_count,options_count,shuffle_mode,created_at
--    new extra: updated_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.lms_quizzes
    (id, course_id, quiz_type, title, is_active, header_image, instructions,
     draw_count, options_count, shuffle_mode, created_at)
SELECT  id, course_id, quiz_type, title, is_active, header_image, instructions,
        draw_count, options_count, shuffle_mode, created_at
FROM `e.cpn1.go.th`.lms_quizzes;

SELECT ROW_COUNT() AS `lms_quizzes → replaced`;

-- ────────────────────────────────────────────────────────────
-- 10. LMS_QUIZ_QUESTIONS
--     old cols : id,quiz_id,question_text,media_type,media_url,sort_order,
--                difficulty_value,discrimination_value
--     new extra: created_at, updated_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.lms_quiz_questions
    (id, quiz_id, question_text, media_type, media_url, sort_order,
     difficulty_value, discrimination_value)
SELECT  id, quiz_id, question_text, media_type, media_url, sort_order,
        difficulty_value, discrimination_value
FROM `e.cpn1.go.th`.lms_quiz_questions;

SELECT ROW_COUNT() AS `lms_quiz_questions → replaced`;

-- ────────────────────────────────────────────────────────────
-- 11. LMS_QUIZ_OPTIONS
--     old cols : id,question_id,option_text,option_image_url,is_correct
--     new extra: created_at, updated_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.lms_quiz_options
    (id, question_id, option_text, option_image_url, is_correct)
SELECT  id, question_id, option_text, option_image_url, is_correct
FROM `e.cpn1.go.th`.lms_quiz_options;

SELECT ROW_COUNT() AS `lms_quiz_options → replaced`;

-- ────────────────────────────────────────────────────────────
-- 12. LMS_ENROLLMENTS
--     old cols : id,user_id,course_id,enrolled_at
--     new extra: created_at, updated_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.lms_enrollments
    (id, user_id, course_id, enrolled_at)
SELECT  id, user_id, course_id, enrolled_at
FROM `e.cpn1.go.th`.lms_enrollments;

SELECT ROW_COUNT() AS `lms_enrollments → replaced`;

-- ────────────────────────────────────────────────────────────
-- 13. LMS_LESSON_ACTIVITY
--     old cols : id,user_id,course_id,lesson_id,event_type,seconds_spent,created_at
--     new extra: updated_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.lms_lesson_activity
    (id, user_id, course_id, lesson_id, event_type, seconds_spent, created_at)
SELECT  id, user_id, course_id, lesson_id, event_type, seconds_spent, created_at
FROM `e.cpn1.go.th`.lms_lesson_activity;

SELECT ROW_COUNT() AS `lms_lesson_activity → replaced`;

-- ────────────────────────────────────────────────────────────
-- 14. LMS_LESSON_PROGRESS
--     old cols : id,user_id,course_id,lesson_id,focus_seconds,video_seconds,completed_at
--     new extra: created_at, updated_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.lms_lesson_progress
    (id, user_id, course_id, lesson_id, focus_seconds, video_seconds, completed_at)
SELECT  id, user_id, course_id, lesson_id, focus_seconds, video_seconds, completed_at
FROM `e.cpn1.go.th`.lms_lesson_progress;

SELECT ROW_COUNT() AS `lms_lesson_progress → replaced`;

-- ────────────────────────────────────────────────────────────
-- 15. LMS_LESSON_SUBMISSIONS
--     old cols : id,user_id,course_id,lesson_id,file_url,status,student_note,
--                admin_comment,student_name,student_school,reviewed_by,reviewed_at,submitted_at
--     new extra: created_at, updated_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.lms_lesson_submissions
    (id, user_id, course_id, lesson_id, file_url, status, student_note,
     admin_comment, student_name, student_school, reviewed_by, reviewed_at, submitted_at)
SELECT  id, user_id, course_id, lesson_id, file_url, status, student_note,
        admin_comment, student_name, student_school, reviewed_by, reviewed_at, submitted_at
FROM `e.cpn1.go.th`.lms_lesson_submissions;

SELECT ROW_COUNT() AS `lms_lesson_submissions → replaced`;

-- ────────────────────────────────────────────────────────────
-- 16. LMS_QUIZ_ATTEMPTS
--     old cols : id,quiz_id,course_id,user_id,score,total,percent,submitted_at
--     new extra: created_at, updated_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.lms_quiz_attempts
    (id, quiz_id, course_id, user_id, score, total, percent, submitted_at)
SELECT  id, quiz_id, course_id, user_id, score, total, percent, submitted_at
FROM `e.cpn1.go.th`.lms_quiz_attempts;

SELECT ROW_COUNT() AS `lms_quiz_attempts → replaced`;

-- ────────────────────────────────────────────────────────────
-- 17. LMS_QUIZ_ANSWERS
--     old cols : id,attempt_id,question_id,option_id,is_correct
--     new extra: created_at, updated_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.lms_quiz_answers
    (id, attempt_id, question_id, option_id, is_correct)
SELECT  id, attempt_id, question_id, option_id, is_correct
FROM `e.cpn1.go.th`.lms_quiz_answers;

SELECT ROW_COUNT() AS `lms_quiz_answers → replaced`;

-- ────────────────────────────────────────────────────────────
-- 18. LMS_PROGRESS
--     old cols : id,user_id,lesson_id,course_id,completed_at
--     new extra: created_at, updated_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.lms_progress
    (id, user_id, lesson_id, course_id, completed_at)
SELECT  id, user_id, lesson_id, course_id, completed_at
FROM `e.cpn1.go.th`.lms_progress;

SELECT ROW_COUNT() AS `lms_progress → replaced`;

-- ────────────────────────────────────────────────────────────
-- 19. TEACHER_PROFILE  ←  survey_records
--     old cols : id,school_code,school_name,school_network,prefix,first_name,last_name,
--                personalid,email,recruitment_subject,birth_date,birth_year_be,age,
--                position,academic_rank,appointed_date,appointed_year_be,
--                bachelor_major,master_major,doctoral_major,other_workload,
--                profile_image_name,profile_image_path,profile_image_url,created_at
--     new extra: updated_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.teacher_profile
    (id, school_code, school_name, school_network, prefix, first_name, last_name,
     personalid, email, recruitment_subject, birth_date, birth_year_be, age,
     position, academic_rank, appointed_date, appointed_year_be,
     bachelor_major, master_major, doctoral_major, other_workload,
     profile_image_name, profile_image_path, profile_image_url, created_at)
SELECT  id, school_code, school_name, school_network, prefix, first_name, last_name,
        personalid, email, recruitment_subject, birth_date, birth_year_be, age,
        position, academic_rank, appointed_date, appointed_year_be,
        bachelor_major, master_major, doctoral_major, other_workload,
        profile_image_name, profile_image_path, profile_image_url, created_at
FROM `e.cpn1.go.th`.survey_records;

SELECT ROW_COUNT() AS `teacher_profile ← survey_records → replaced`;

-- ────────────────────────────────────────────────────────────
-- 20. TEACHER_AWARDS  ←  survey_awards
--     old cols : id,record_id,work_name,award_name,award_date,award_date_be,issuer
--     new extra: created_at, updated_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.teacher_awards
    (id, record_id, work_name, award_name, award_date, award_date_be, issuer)
SELECT  id, record_id, work_name, award_name, award_date, award_date_be, issuer
FROM `e.cpn1.go.th`.survey_awards;

SELECT ROW_COUNT() AS `teacher_awards ← survey_awards → replaced`;

-- ────────────────────────────────────────────────────────────
-- 21. TEACHER_CEFR  ←  survey_cefr
--     old cols : id,record_id,source,cefr_level,cert_no,cert_date,cert_date_be,issuer
--     new extra: created_at, updated_at
--     NOTE: old.source เป็น enum('obec','other') → new.source เป็น varchar(100) ✅ compatible
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.teacher_cefr
    (id, record_id, source, cefr_level, cert_no, cert_date, cert_date_be, issuer)
SELECT  id, record_id, source, cefr_level, cert_no, cert_date, cert_date_be, issuer
FROM `e.cpn1.go.th`.survey_cefr;

SELECT ROW_COUNT() AS `teacher_cefr ← survey_cefr → replaced`;

-- ────────────────────────────────────────────────────────────
-- 22. TEACHER_HSK  ←  survey_hsk
--     old cols : id,record_id,source,hsk_level,cert_no,cert_date,cert_date_be,issuer
--     new extra: created_at, updated_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.teacher_hsk
    (id, record_id, source, hsk_level, cert_no, cert_date, cert_date_be, issuer)
SELECT  id, record_id, source, hsk_level, cert_no, cert_date, cert_date_be, issuer
FROM `e.cpn1.go.th`.survey_hsk;

SELECT ROW_COUNT() AS `teacher_hsk ← survey_hsk → replaced`;

-- ────────────────────────────────────────────────────────────
-- 23. TEACHER_EDUCATIONS  ←  survey_educations
--     old cols : id,record_id,edu_level,field_of_study,major
--     new extra: created_at, updated_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.teacher_educations
    (id, record_id, edu_level, field_of_study, major)
SELECT  id, record_id, edu_level, field_of_study, major
FROM `e.cpn1.go.th`.survey_educations;

SELECT ROW_COUNT() AS `teacher_educations ← survey_educations → replaced`;

-- ────────────────────────────────────────────────────────────
-- 24. TEACHER_SUBJECTS  ←  survey_subjects
--     old cols : id,record_id,subject_name,subject_grade,subject_hours
--     new extra: created_at, updated_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.teacher_subjects
    (id, record_id, subject_name, subject_grade, subject_hours)
SELECT  id, record_id, subject_name, subject_grade, subject_hours
FROM `e.cpn1.go.th`.survey_subjects;

SELECT ROW_COUNT() AS `teacher_subjects ← survey_subjects → replaced`;

-- ────────────────────────────────────────────────────────────
-- 25. LEGACY_USERS  ←  users  (เก็บ schema เดิมของระบบ legacy)
--     old cols : id,user_id,username,title,fname,lname,personalid,school,email,
--                user_level,password_hash,role,is_active,last_login_at,created_at,last_login
--     new extra: updated_at
-- ────────────────────────────────────────────────────────────
REPLACE INTO ee_cpn1_go_th.legacy_users
    (id, user_id, username, title, fname, lname, personalid, school, email,
     user_level, password_hash, role, is_active, last_login_at, created_at, last_login)
SELECT  id, user_id, username, title, fname, lname, personalid, school, email,
        user_level, password_hash, role, is_active, last_login_at, created_at, last_login
FROM `e.cpn1.go.th`.users;

SELECT ROW_COUNT() AS `legacy_users ← users → replaced`;

-- ────────────────────────────────────────────────────────────
-- 26. USERS (Laravel)  ←  users
--     อัปเดต name ให้มีคำนำหน้า และ sync ข้อมูลพื้นฐาน
--     *** ไม่แตะ: password, logo, email_verified_at, remember_token ***
--     *** ไม่แตะ id=1 (system admin account) ***
-- ────────────────────────────────────────────────────────────
-- 26a. INSERT users ที่ยังไม่มีในฝั่งใหม่
INSERT INTO ee_cpn1_go_th.users
    (id, name, email, role, password, created_at, updated_at)
SELECT
    u.id,
    CONCAT(u.title, u.fname, ' ', u.lname),
    u.email,
    CASE WHEN u.role = 'admin' THEN 'admin' ELSE 'user' END,
    '!external_api_login!',
    u.created_at,
    NOW()
FROM `e.cpn1.go.th`.users u
WHERE u.id NOT IN (SELECT id FROM ee_cpn1_go_th.users);

SELECT ROW_COUNT() AS `users (Laravel) → new inserted`;

-- 26b. UPDATE name ให้มีคำนำหน้า (ทุก user ยกเว้น id=1)
UPDATE ee_cpn1_go_th.users nu
JOIN `e.cpn1.go.th`.users old ON old.id = nu.id
SET
    nu.name       = CONCAT(old.title, old.fname, ' ', old.lname),
    nu.email      = old.email,
    nu.updated_at = NOW()
WHERE nu.id != 1
  AND (
      nu.name  != CONCAT(old.title, old.fname, ' ', old.lname)
      OR nu.email != old.email
  );

SELECT ROW_COUNT() AS `users (Laravel) → name/email updated`;

SET FOREIGN_KEY_CHECKS = 1;

-- ────────────────────────────────────────────────────────────
-- FINAL SUMMARY
-- ────────────────────────────────────────────────────────────
SELECT 'banners'               AS table_name, COUNT(*) AS total FROM ee_cpn1_go_th.banners
UNION ALL SELECT 'system_group',              COUNT(*) FROM ee_cpn1_go_th.system_group
UNION ALL SELECT 'system_school',             COUNT(*) FROM ee_cpn1_go_th.system_school
UNION ALL SELECT 'system_announcements',      COUNT(*) FROM ee_cpn1_go_th.system_announcements
UNION ALL SELECT 'obec_majors',               COUNT(*) FROM ee_cpn1_go_th.obec_majors
UNION ALL SELECT 'login_attempts',            COUNT(*) FROM ee_cpn1_go_th.login_attempts
UNION ALL SELECT 'lms_courses',               COUNT(*) FROM ee_cpn1_go_th.lms_courses
UNION ALL SELECT 'lms_lessons',               COUNT(*) FROM ee_cpn1_go_th.lms_lessons
UNION ALL SELECT 'lms_quizzes',               COUNT(*) FROM ee_cpn1_go_th.lms_quizzes
UNION ALL SELECT 'lms_quiz_questions',        COUNT(*) FROM ee_cpn1_go_th.lms_quiz_questions
UNION ALL SELECT 'lms_quiz_options',          COUNT(*) FROM ee_cpn1_go_th.lms_quiz_options
UNION ALL SELECT 'lms_enrollments',           COUNT(*) FROM ee_cpn1_go_th.lms_enrollments
UNION ALL SELECT 'lms_lesson_activity',       COUNT(*) FROM ee_cpn1_go_th.lms_lesson_activity
UNION ALL SELECT 'lms_lesson_progress',       COUNT(*) FROM ee_cpn1_go_th.lms_lesson_progress
UNION ALL SELECT 'lms_lesson_submissions',    COUNT(*) FROM ee_cpn1_go_th.lms_lesson_submissions
UNION ALL SELECT 'lms_quiz_attempts',         COUNT(*) FROM ee_cpn1_go_th.lms_quiz_attempts
UNION ALL SELECT 'lms_quiz_answers',          COUNT(*) FROM ee_cpn1_go_th.lms_quiz_answers
UNION ALL SELECT 'lms_progress',              COUNT(*) FROM ee_cpn1_go_th.lms_progress
UNION ALL SELECT 'teacher_profile',           COUNT(*) FROM ee_cpn1_go_th.teacher_profile
UNION ALL SELECT 'teacher_awards',            COUNT(*) FROM ee_cpn1_go_th.teacher_awards
UNION ALL SELECT 'teacher_cefr',              COUNT(*) FROM ee_cpn1_go_th.teacher_cefr
UNION ALL SELECT 'teacher_hsk',               COUNT(*) FROM ee_cpn1_go_th.teacher_hsk
UNION ALL SELECT 'teacher_educations',        COUNT(*) FROM ee_cpn1_go_th.teacher_educations
UNION ALL SELECT 'teacher_subjects',          COUNT(*) FROM ee_cpn1_go_th.teacher_subjects
UNION ALL SELECT 'legacy_users',              COUNT(*) FROM ee_cpn1_go_th.legacy_users
UNION ALL SELECT 'users (Laravel)',           COUNT(*) FROM ee_cpn1_go_th.users;
