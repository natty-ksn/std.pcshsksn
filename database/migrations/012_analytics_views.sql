-- ============================================
-- Migration: 012_analytics_views.sql
-- Description: Database Views สำหรับ Analytics — Student 360°, Early Warning, School Stats
-- Created: 2026-04-03
-- ============================================

-- =====================================================
-- VIEW 1: Student 360° Profile — ข้อมูลนักเรียนครบใน query เดียว
-- =====================================================
CREATE OR REPLACE VIEW `vw_student_360` AS
SELECT
    u.id AS user_id,
    u.username,
    u.email,
    u.full_name AS user_full_name,
    u.role,
    u.last_login,
    sp.id AS profile_id,
    sp.student_code,
    sp.full_name,
    sp.nickname,
    sp.class_year,
    sp.class_room,
    sp.avatar_path,
    sp.bio,
    sp.date_of_birth,
    sp.phone,

    -- Academic Summary
    COUNT(DISTINCT ar.id) AS total_subjects,
    ROUND(AVG(ar.grade), 2) AS gpa,
    MAX(ar.academic_year) AS latest_year,

    -- Portfolio Summary
    COUNT(DISTINCT pi.id) AS portfolio_count,
    COUNT(DISTINCT CASE WHEN pi.is_featured = 1 THEN pi.id END) AS featured_count,
    COUNT(DISTINCT CASE WHEN pi.achievement_level IN ('national','international') THEN pi.id END) AS high_achievement_count,
    MAX(pi.created_at) AS last_portfolio_date,

    -- Project Summary
    COUNT(DISTINCT pm.project_id) AS project_count,
    COUNT(DISTINCT CASE WHEN pm.role_in_project = 'leader' THEN pm.project_id END) AS leader_count,

    -- Goals
    COUNT(DISTINCT sg.id) AS goal_count,
    COUNT(DISTINCT CASE WHEN sg.status = 'achieved' THEN sg.id END) as achieved_goals,

    -- Assessment
    sa.dominant_trait AS riasec_type,

    -- Badges
    COUNT(DISTINCT ub.badge_code) AS badge_count

FROM users u
JOIN student_profiles sp ON u.id = sp.user_id
LEFT JOIN academic_records ar ON sp.id = ar.student_id
LEFT JOIN portfolio_items pi ON sp.id = pi.student_id AND pi.status = 'published'
LEFT JOIN project_members pm ON u.id = pm.student_id
LEFT JOIN student_goals sg ON sp.id = sg.student_id
LEFT JOIN student_assessments sa ON u.id = sa.user_id
LEFT JOIN user_badges ub ON u.id = ub.user_id
WHERE u.role = 'student'
GROUP BY u.id, sp.id;

-- =====================================================
-- VIEW 2: Early Warning — คำนวณความเสี่ยงนักเรียน
-- =====================================================
CREATE OR REPLACE VIEW `vw_early_warning` AS
SELECT
    u.id AS user_id,
    u.username,
    sp.student_code,
    sp.full_name,
    sp.nickname,
    sp.class_year,
    sp.class_room,
    sp.avatar_path,

    -- Factor 1: GPA Score (25%)
    COALESCE(
        CASE 
            WHEN AVG(ar.grade) IS NULL THEN 0
            WHEN AVG(ar.grade) >= 3.5 THEN 25
            WHEN AVG(ar.grade) >= 3.0 THEN 20
            WHEN AVG(ar.grade) >= 2.5 THEN 15
            WHEN AVG(ar.grade) >= 2.0 THEN 8
            ELSE 3
        END, 0
    ) AS gpa_score,

    -- Factor 2: Portfolio Score (20%)
    COALESCE(
        LEAST(COUNT(DISTINCT pi.id) * 4, 20), 0
    ) AS portfolio_score,

    -- Factor 3: Activity Score (15%) — อัปเดตภายใน 30 วัน
    COALESCE(
        CASE WHEN MAX(pi.created_at) > DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 15 ELSE 0 END, 0
    ) AS activity_score,

    -- Factor 4: Goal Score (15%)
    COALESCE(
        CASE WHEN COUNT(DISTINCT sg.id) > 0 THEN 15 ELSE 0 END, 0
    ) AS goal_score,

    -- Factor 5: Project Score (15%)
    COALESCE(
        LEAST(COUNT(DISTINCT pm.project_id) * 5, 15), 0
    ) AS project_score,

    -- Factor 6: Logbook Score (10%)
    COALESCE(
        CASE WHEN COUNT(DISTINCT lb.id) >= 4 THEN 10
             WHEN COUNT(DISTINCT lb.id) >= 2 THEN 5
             ELSE 0 END, 0
    ) AS logbook_score,

    -- Total Score (out of 100)
    -- Risk Level = 100 - total_score (ยิ่ง score น้อย = ยิ่งเสี่ยง)
    COALESCE(
        CASE 
            WHEN AVG(ar.grade) IS NULL THEN 0
            WHEN AVG(ar.grade) >= 3.5 THEN 25
            WHEN AVG(ar.grade) >= 3.0 THEN 20
            WHEN AVG(ar.grade) >= 2.5 THEN 15
            WHEN AVG(ar.grade) >= 2.0 THEN 8
            ELSE 3
        END, 0
    )
    + COALESCE(LEAST(COUNT(DISTINCT pi.id) * 4, 20), 0)
    + COALESCE(CASE WHEN MAX(pi.created_at) > DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 15 ELSE 0 END, 0)
    + COALESCE(CASE WHEN COUNT(DISTINCT sg.id) > 0 THEN 15 ELSE 0 END, 0)
    + COALESCE(LEAST(COUNT(DISTINCT pm.project_id) * 5, 15), 0)
    + COALESCE(
        CASE WHEN COUNT(DISTINCT lb.id) >= 4 THEN 10
             WHEN COUNT(DISTINCT lb.id) >= 2 THEN 5
             ELSE 0 END, 0
    ) AS total_score,

    -- Risk Level
    CASE
        WHEN (
            COALESCE(CASE WHEN AVG(ar.grade) >= 3.5 THEN 25 WHEN AVG(ar.grade) >= 3.0 THEN 20 WHEN AVG(ar.grade) >= 2.5 THEN 15 WHEN AVG(ar.grade) >= 2.0 THEN 8 ELSE 3 END, 0)
            + COALESCE(LEAST(COUNT(DISTINCT pi.id) * 4, 20), 0)
            + COALESCE(CASE WHEN MAX(pi.created_at) > DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 15 ELSE 0 END, 0)
            + COALESCE(CASE WHEN COUNT(DISTINCT sg.id) > 0 THEN 15 ELSE 0 END, 0)
            + COALESCE(LEAST(COUNT(DISTINCT pm.project_id) * 5, 15), 0)
            + COALESCE(CASE WHEN COUNT(DISTINCT lb.id) >= 4 THEN 10 WHEN COUNT(DISTINCT lb.id) >= 2 THEN 5 ELSE 0 END, 0)
        ) >= 70 THEN 'low'
        WHEN (
            COALESCE(CASE WHEN AVG(ar.grade) >= 3.5 THEN 25 WHEN AVG(ar.grade) >= 3.0 THEN 20 WHEN AVG(ar.grade) >= 2.5 THEN 15 WHEN AVG(ar.grade) >= 2.0 THEN 8 ELSE 3 END, 0)
            + COALESCE(LEAST(COUNT(DISTINCT pi.id) * 4, 20), 0)
            + COALESCE(CASE WHEN MAX(pi.created_at) > DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 15 ELSE 0 END, 0)
            + COALESCE(CASE WHEN COUNT(DISTINCT sg.id) > 0 THEN 15 ELSE 0 END, 0)
            + COALESCE(LEAST(COUNT(DISTINCT pm.project_id) * 5, 15), 0)
            + COALESCE(CASE WHEN COUNT(DISTINCT lb.id) >= 4 THEN 10 WHEN COUNT(DISTINCT lb.id) >= 2 THEN 5 ELSE 0 END, 0)
        ) >= 40 THEN 'medium'
        WHEN (
            COALESCE(CASE WHEN AVG(ar.grade) >= 3.5 THEN 25 WHEN AVG(ar.grade) >= 3.0 THEN 20 WHEN AVG(ar.grade) >= 2.5 THEN 15 WHEN AVG(ar.grade) >= 2.0 THEN 8 ELSE 3 END, 0)
            + COALESCE(LEAST(COUNT(DISTINCT pi.id) * 4, 20), 0)
            + COALESCE(CASE WHEN MAX(pi.created_at) > DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 15 ELSE 0 END, 0)
            + COALESCE(CASE WHEN COUNT(DISTINCT sg.id) > 0 THEN 15 ELSE 0 END, 0)
            + COALESCE(LEAST(COUNT(DISTINCT pm.project_id) * 5, 15), 0)
            + COALESCE(CASE WHEN COUNT(DISTINCT lb.id) >= 4 THEN 10 WHEN COUNT(DISTINCT lb.id) >= 2 THEN 5 ELSE 0 END, 0)
        ) >= 20 THEN 'high'
        ELSE 'critical'
    END AS risk_level

FROM users u
JOIN student_profiles sp ON u.id = sp.user_id
LEFT JOIN academic_records ar ON sp.id = ar.student_id
LEFT JOIN portfolio_items pi ON sp.id = pi.student_id AND pi.status = 'published'
LEFT JOIN student_goals sg ON sp.id = sg.student_id
LEFT JOIN project_members pm ON u.id = pm.student_id
LEFT JOIN logbooks lb ON u.id = lb.student_id
WHERE u.role = 'student'
GROUP BY u.id, sp.id;

-- =====================================================
-- VIEW 3: School Analytics Summary — สำหรับผู้บริหาร
-- =====================================================
CREATE OR REPLACE VIEW `vw_school_analytics` AS
SELECT
    -- Student counts
    (SELECT COUNT(*) FROM users WHERE role = 'student' AND is_active = 1) AS total_students,
    (SELECT COUNT(*) FROM users WHERE role = 'teacher' AND is_active = 1) AS total_teachers,

    -- Portfolio stats
    (SELECT COUNT(*) FROM portfolio_items WHERE status = 'published') AS total_portfolios,
    (SELECT COUNT(DISTINCT student_id) FROM portfolio_items WHERE status = 'published' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS active_students_30d,

    -- Project stats
    (SELECT COUNT(*) FROM projects WHERE status IN ('draft','proposal_submitted','proposal_approved','in_progress','chapter_review','presentation_ready')) AS active_projects,
    (SELECT COUNT(*) FROM projects WHERE status = 'completed') AS completed_projects,

    -- Average GPA
    (SELECT ROUND(AVG(grade), 2) FROM academic_records) AS avg_gpa,

    -- Engagement: logbooks this month
    (SELECT COUNT(*) FROM logbooks WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS logbooks_30d,

    -- Risk distribution
    (SELECT COUNT(*) FROM vw_early_warning WHERE risk_level = 'low') AS risk_low_count,
    (SELECT COUNT(*) FROM vw_early_warning WHERE risk_level = 'medium') AS risk_medium_count,
    (SELECT COUNT(*) FROM vw_early_warning WHERE risk_level = 'high') AS risk_high_count,
    (SELECT COUNT(*) FROM vw_early_warning WHERE risk_level = 'critical') AS risk_critical_count;
