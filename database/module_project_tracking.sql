-- ============================================
-- PCSHSKS Project Tracking System Schema
-- Version: 1.0
-- Description: ส่วนขยายจากฐานข้อมูล pccks_eportfolio เดิม
-- ============================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

USE pccks_eportfolio;

-- =====================
-- 1. วิชาโครงงาน (Subjects)
-- =====================
DROP TABLE IF EXISTS project_subjects;
CREATE TABLE project_subjects (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    code            VARCHAR(20) NOT NULL,
    name            VARCHAR(200) NOT NULL,
    class_year      TINYINT NOT NULL,
    semester        TINYINT NOT NULL,
    academic_year   VARCHAR(9) NOT NULL,
    teacher_id      INT,
    description     TEXT,
    is_active       TINYINT(1) DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id),
    INDEX idx_year (academic_year, semester)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================
-- 2. ข้อมูลหลักโครงงาน (Projects)
-- แนะนำโดย SA: รองรับข้อมูลเครือข่าย/ผู้เชี่ยวชาญ และงบประมาณ/จริยธรรมการวิจัย (IRB)
-- =====================
DROP TABLE IF EXISTS projects;
CREATE TABLE projects (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    project_code    VARCHAR(30) UNIQUE NOT NULL,
    title_th        VARCHAR(300) NOT NULL,
    title_en        VARCHAR(300),
    project_type    ENUM('experimental','survey','invention','applied','theoretical') NOT NULL,
    subject_id      INT,
    abstract_th     TEXT,
    abstract_en     TEXT,
    
    -- Status Workflow
    status          ENUM(
        'draft','proposal_submitted','proposal_approved','proposal_revision',
        'proposal_rejected','in_progress','chapter_review','presentation_ready',
        'completed','archived'
    ) DEFAULT 'draft',
    
    -- Ethics & Budget (การพิจารณาจริยธรรมวิจัยและงบประมาณ)
    ethics_status   ENUM('not_required', 'pending', 'approved', 'rejected') DEFAULT 'not_required',
    budget_requested DECIMAL(10,2) DEFAULT 0.00,
    budget_approved  DECIMAL(10,2) DEFAULT 0.00,
    
    -- Advising Team
    advisor_id      INT NOT NULL,
    co_advisor_id   INT,
    external_expert VARCHAR(200),
    
    -- Academic Timeline
    academic_year   VARCHAR(9) NOT NULL,
    semester        TINYINT NOT NULL DEFAULT 1,
    class_year      TINYINT,
    class_room      VARCHAR(10),
    start_date      DATE,
    expected_end    DATE,
    actual_end      DATE,
    overall_score   DECIMAL(5,2),
    tags            JSON,
    
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (subject_id) REFERENCES project_subjects(id),
    FOREIGN KEY (advisor_id) REFERENCES users(id),
    FOREIGN KEY (co_advisor_id) REFERENCES users(id),
    INDEX idx_status (status),
    INDEX idx_advisor (advisor_id),
    INDEX idx_year (academic_year, semester),
    FULLTEXT idx_search (title_th, title_en, abstract_th)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================
-- 3. สมาชิกโครงงาน (Project Members)
-- =====================
DROP TABLE IF EXISTS project_members;
CREATE TABLE project_members (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    project_id      INT NOT NULL,
    student_id      INT NOT NULL,
    role_in_project ENUM('leader','member','secretary') DEFAULT 'member',
    joined_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_member (project_id, student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================
-- 4. บันทึกการทำงาน (Logbooks) - จาก SA Blueprint
-- =====================
DROP TABLE IF EXISTS logbooks;
CREATE TABLE logbooks (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    project_id      INT NOT NULL,
    student_id      INT NOT NULL,
    activity_detail TEXT NOT NULL,
    hours_spent     DECIMAL(4,2),
    teacher_comment TEXT,
    is_verified     TINYINT(1) DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id),
    INDEX idx_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================
-- 5. จุดหมาย (Milestones) & การส่งงาน
-- =====================
DROP TABLE IF EXISTS milestones;
CREATE TABLE milestones (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    project_id      INT NOT NULL,
    phase_name      VARCHAR(200) NOT NULL,
    description     TEXT,
    milestone_order TINYINT DEFAULT 1,
    due_date        DATE,
    completed_date  DATE,
    status          ENUM('pending','in_progress','submitted','approved','overdue') DEFAULT 'pending',
    progress_pct    TINYINT DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_project (project_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS milestone_submissions;
CREATE TABLE milestone_submissions (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    milestone_id    INT NOT NULL,
    submitted_by    INT NOT NULL,
    note            TEXT,
    file_path_gcs   VARCHAR(500),
    advisor_comment TEXT,
    status          ENUM('submitted','revise','accepted') DEFAULT 'submitted',
    submitted_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (milestone_id) REFERENCES milestones(id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================
-- 6. เอกสารและการตรวจ (Project Documents & Reviews)
-- =====================
DROP TABLE IF EXISTS project_documents;
CREATE TABLE project_documents (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    project_id      INT NOT NULL,
    uploaded_by     INT NOT NULL,
    document_type   ENUM(
        'proposal','chapter1','chapter2','chapter3','chapter4','chapter5',
        'full_report','presentation','poster','appendix','other'
    ) NOT NULL,
    chapter_number  TINYINT,
    title           VARCHAR(200),
    file_path_gcs   VARCHAR(500) NOT NULL,
    file_size       INT DEFAULT 0,
    file_type       VARCHAR(50),      -- e.g., 'application/pdf', 'video/mp4'
    version         INT DEFAULT 1,    -- Version Control ตาม SA Blueprint
    review_status   ENUM('pending','reviewing','approved','revision_needed','rejected') DEFAULT 'pending',
    reviewed_by     INT,
    reviewed_at     DATETIME,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id),
    INDEX idx_project (project_id),
    INDEX idx_status (review_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS document_comments;
CREATE TABLE document_comments (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    document_id     INT NOT NULL,
    commenter_id    INT NOT NULL,
    comment         TEXT NOT NULL,
    comment_type    ENUM('general','suggestion','correction','praise') DEFAULT 'general',
    page_number     INT,
    is_resolved     TINYINT(1) DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES project_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (commenter_id) REFERENCES users(id),
    INDEX idx_document (document_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================
-- 7. การให้คะแนนโครงงาน (Project Reviews/Evaluations)
-- =====================
DROP TABLE IF EXISTS project_evaluations;
CREATE TABLE project_evaluations (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    project_id      INT NOT NULL,
    evaluator_id    INT NOT NULL,      -- ผู้ประเมิน
    eval_type       ENUM('proposal','progress','chapter','presentation','final') NOT NULL,
    scores          JSON,              -- เก็บเกณฑ์และคะแนนแต่ละข้อ
    total_score     DECIMAL(5,2),
    feedback        TEXT,
    result          ENUM('pass','fail','revise') DEFAULT 'pass',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluator_id) REFERENCES users(id),
    INDEX idx_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================
-- 8. คลังโครงงาน (Project Repository & Knowledge Bank)
-- =====================
DROP TABLE IF EXISTS project_repository;
CREATE TABLE project_repository (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    project_id      INT UNIQUE NOT NULL,
    keywords        TEXT,
    category        VARCHAR(100),
    summary         TEXT,
    poster_path_gcs VARCHAR(500),
    report_path_gcs VARCHAR(500),
    video_path_gcs  VARCHAR(500),
    view_count      INT DEFAULT 0,
    download_count  INT DEFAULT 0,
    is_featured     TINYINT(1) DEFAULT 0,
    is_public       TINYINT(1) DEFAULT 1,
    published_at    DATETIME,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FULLTEXT idx_search (keywords, summary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- End of Schema
-- ============================================
