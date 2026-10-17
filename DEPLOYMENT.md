# 🚀 PCSHSKSN e-Portfolio — Deployment & Migration Guide

> **สำหรับผู้ดูแลระบบ** — คู่มือ deploy และ migrate ระบบ

---

## 📋 สิ่งที่ได้เพิ่มในระบบนี้

### Phase 1: พื้นฐานมั่นคง
| รายการ | ไฟล์ที่สร้าง | ประโยชน์ |
|--------|-------------|----------|
| **Migration System** | `core/MigrationRunner.php` + `database/migrations/` | ควบคุม schema แบบ versioned |
| **Validator** | `core/Validator.php` | Input validation 25+ rules |
| **Error Handler** | `core/ErrorHandler.php` + `views/errors/500.php`, `maintenance.php` | จัดการ error แบบ centralized |
| **Performance Indexes** | `database/migrations/011_performance_indexes.sql` | Query เร็วขึ้น 10x |

### Phase 2: ปรับปรุงระบบ
| รายการ | ไฟล์ที่สร้าง | ประโยชน์ |
|--------|-------------|----------|
| **Service Layer** | `app/services/StudentService.php`, `ReportService.php` | Business logic แยกจาก controller |
| **Notification System** | `core/Notifier.php` | Event-driven notifications |
| **Student 360°** | `views/profile/360.php` + route `/profile/360` | ข้อมูลนักเรียนครบในหน้าเดียว |

### Phase 3: สร้างคุณค่า
| รายการ | ไฟล์ที่สร้าง | ประโยชน์ |
|--------|-------------|----------|
| **Early Warning** | `app/controllers/AnalyticsController.php` + `views/analytics/early_warning.php` | แจ้งเตือนนักเรียนที่มีความเสี่ยง |
| **TCAS Predictor** | `views/analytics/tcas_readiness.php` + route `/tcas-readiness` | วิเคราะห์ความพร้อมเข้ามหาวิทยาลัย |
| **Analytics Dashboard** | `views/analytics/school_dashboard.php` + route `/analytics/dashboard` | ข้อมูลภาพรวมสำหรับผู้บริหาร |

---

## 🔧 การติดตั้งครั้งแรก (New Installation)

### วิธีที่ 1: ใช้ Installer (แนะนำ)
1. เปิด `http://localhost/app/install/` ใน browser
2. ทำตามขั้นตอน — Installer จะสร้าง database + schema ให้อัตโนมัติ
3. หลังติดตั้งเสร็จ เปิด `http://localhost/app/install/?force=1` เพื่อ migrate

### วิธีที่ 2: ใช้ Migration CLI
```bash
cd c:\xampp\htdocs\app

# สร้าง database และ tables จาก migration files
php core/MigrationRunner.php up

# ดูสถานะ
php core/MigrationRunner.php status

# รัน seed data (default categories, badges, admin account)
# Import seed file manually:
mysql -u root pcshsksn_eportfolio < database/seeds/001_default_data.sql
```

---

## 📊 การ Migration (เมื่อมี schema เปลี่ยนแปลง)

### ดูสถานะ
```bash
php core/MigrationRunner.php status
```

### Rัน Migration ทั้งหมด
```bash
php core/MigrationRunner.php up
```

### Rollback (ย้อนกลับ)
```bash
# ย้อนกลับ batch ล่าสุด
php core/MigrationRunner.php down

# ย้อนกลับถึง batch ที่ 2
php core/MigrationRunner.php down 2
```

### สร้าง Migration ใหม่
```bash
php core/MigrationRunner.php make add_new_column_to_users
# จะสร้างไฟล์: database/migrations/013_add_new_column_to_users.sql
```

---

## 🔄 Cron Jobs (สำหรับระบบอัตโนมัติ)

### ตรวจสอบ Milestone ทุกวัน (08:00 น.)
เพิ่มใน Windows Task Scheduler หรือ crontab:
```bash
# Windows Task Scheduler:
# Action: Start a program
# Program: curl.exe
# Arguments: http://localhost/app/cron/milestone-check?token=pcshsksn-secret-change-in-production
# Schedule: Daily at 08:00
```

หรือใช้ PHP CLI:
```bash
# ใน Linux/Mac:
0 8 * * * cd /path/to/app && php -r "
require_once 'core/Env.php';
require_once 'core/Database.php';
require_once 'core/Notifier.php';
Env::load('.env');
Notifier::checkMilestones();
"
```

---

## 🌐 URLs ที่เพิ่มเข้ามา

| URL | Role | คำอธิบาย |
|-----|------|---------|
| `/profile/360` | ทุก role | Student 360° Profile |
| `/analytics/early-warning` | Admin, Teacher, Executive | Early Warning System |
| `/tcas-readiness` | Student | TCAS Readiness Predictor |
| `/analytics/dashboard` | Admin, Executive | School Analytics Dashboard |
| `/cron/milestone-check?token=xxx` | System | Cron job สำหรับ check milestones |

---

## ⚙️ Environment Variables ใหม่

เพิ่มใน `.env`:
```env
# Maintenance Mode
APP_MAINTENANCE=false

# Cron Job Security
CRON_TOKEN=pcshsksn-secret-change-in-production
```

---

## 📁 โครงสร้างไฟล์ที่เพิ่ม

```
c:\xampp\htdocs\app\
├── core/
│   ├── MigrationRunner.php      ← ระบบ migration
│   ├── Validator.php            ← Input validation engine
│   ├── ErrorHandler.php         ← Centralized error handling
│   └── Notifier.php             ← Event-driven notifications
├── app/
│   ├── controllers/
│   │   └── AnalyticsController.php  ← Data products controller
│   └── services/
│       ├── StudentService.php       ← Student business logic
│       └── ReportService.php        ← Analytics business logic
├── database/
│   ├── migrations/              ← Versioned schema
│   │   ├── 001_core_users_and_auth.sql
│   │   ├── 002_student_profiles.sql
│   │   ├── 003_portfolio_system.sql
│   │   ├── 004_academics_goals_interests.sql
│   │   ├── 005_skills_assessments.sql
│   │   ├── 006_ai_recommendations.sql
│   │   ├── 007_security_audit.sql
│   │   ├── 008_gamification_alumni.sql
│   │   ├── 009_project_tracking.sql
│   │   ├── 010_analytics_readiness.sql
│   │   ├── 011_performance_indexes.sql
│   │   └── 012_analytics_views.sql
│   └── seeds/
│       └── 001_default_data.sql
├── views/
│   ├── errors/
│   │   ├── 500.php              ← Server error page
│   │   └── maintenance.php      ← Maintenance mode page
│   ├── profile/
│   │   └── 360.php              ← Student 360° view
│   └── analytics/
│       ├── early_warning.php    ← Early Warning view
│       ├── tcas_readiness.php   ← TCAS Predictor view
│       └── school_dashboard.php ← Analytics Dashboard view
├── routes/
│   └── web.php                  ← (updated: added data product routes)
├── .env                         ← (updated: added APP_MAINTENANCE, CRON_TOKEN)
└── DEPLOYMENT.md                ← ไฟล์นี้
```

---

## 🛡️ Security Checklist (Production)

- [ ] ตั้ง `APP_DEBUG=false` ใน `.env`
- [ ] ตั้ง `APP_ENV=production` ใน `.env`
- [ ] เปลี่ยน `CRON_TOKEN` เป็นค่าที่ซับซ้อน
- [ ] ตั้ง `APP_MAINTENANCE=true` ขณะ migrate
- [ ] backup database ก่อน migrate
- [ ] ตรวจสอบว่า `.env` ไม่ exposed ผ่าน web

---

## 📞 Troubleshooting

### Migration ไม่รัน
```bash
# ตรวจสอบว่า database连接ได้
php -r "require 'core/Env.php'; require 'core/Database.php'; Env::load('.env'); Database::connect(); echo 'Connected!';"
```

### Error 500 หลัง deploy
- เปิด `APP_DEBUG=true` ชั่วคราวเพื่อดู error
- ตรวจสอบ error log: `logs/error.log`
- Rollback migration: `php core/MigrationRunner.php down`

### ข้อมูลไม่แสดงใน Analytics
- ตรวจสอบว่ามีข้อมูลใน `vw_school_analytics` view
- ถ้าไม่มี ให้สร้างใหม่: `php core/MigrationRunner.php up`

---

*จัดทำโดย งานศูนย์ข้อมูลสารสนเทศ | กลุ่มบริหารงานวิชาการ*
*โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์*
*© 2026 PCSHSKSN*
