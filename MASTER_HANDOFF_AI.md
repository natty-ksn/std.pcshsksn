# 🚀 MASTER HANDOFF: PCSHS KSN e-Portfolio & Project Tracking System

**Version:** 3.0 — ALL PHASES COMPLETED ✅  
**Last Updated:** 7 เมษายน 2569  
**School:** โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์ (PCSHS KSN)  
**Developed by:** งานศูนย์ข้อมูลสารสนเทศ | กลุ่มบริหารงานวิชาการ  

---

## 🏗️ System Architecture

| Layer | Technology |
|-------|-----------|
| **Backend** | Vanilla PHP MVC-lite (Custom) |
| **Database** | MySQL 8.0 + Views + Indexes |
| **PDF Engine** | mPDF 8.3 + Sarabun Font (Google Fonts) |
| **AI Engine** | Google Gemini API + Rule-Based Fallback |
| **UI/UX** | Vanilla CSS — Glassmorphism, Premium Look |
| **Fonts** | Inter (UI) + Sarabun (Thai official docs) |
| **Security** | CSRF Protection, Auth Middlewares, Prepared Statements |

### Directory Structure
```
c:/xampp/htdocs/app/
├── app/
│   ├── controllers/          # 22 controllers (Auth, Portfolio, Project, Teacher, AI, etc.)
│   ├── services/             # 4 services: AIService, BadgeService, PDFService, ReportService
│   └── views/                # 30+ views organized by feature
├── core/                     # Framework: App, Auth, Controller, Database, Router, etc.
├── routes/web.php            # All route definitions (90+ routes)
├── database/
│   ├── migrations/           # 001-014 (all migrations)
│   ├── seeds/                # 001_default_data.sql
│   └── schema_combined.sql   # Full schema reference
├── storage/                  # Phase 3: fonts/ + mpdf_tmp/
├── vendor/                   # Composer: mPDF 8.3
└── public/                   # Web root + install scripts
```

---

## 📋 Completed Phases

### ✅ Phase 1: Collaborative Ecosystem & Productivity
- **Collaborative Portfolio** — ทีมงานกลุ่มใน Portfolio (ยืนยันสิทธิ์ PDPA)
- **Project-to-Portfolio Bridge** — Export โครงงานสู่ Portfolio ใน 1 คลิก
- **Teacher Bulk Approval** — ตรวจงานคราวละมากๆ (Batch Process)
- **Mentorship Intelligence** — สแกนหาเด็กกลุ่มเสี่ยง (Zero-Entry Alert)

### ✅ Phase 2: Teacher Insight & Quality Control
- **Quick Sweep API** — สแกนหาจุดบกพร่องอัตโนมัติ (ขาดรูป, บทคัดย่อสั้น)
- **Annotated Mentorship** — คอมเมนต์เจาะจงจุดในเอกสาร (portfolio_annotations)
- **PDPA Privacy Toggle** — เปิด/ปิดการเข้าถึงผลงานรายชิ้น

### ✅ Phase 3: Executive Hub & Premium PDF Engine
- **mPDF 8.3** ติดตั้ง + Sarabun Font (4 variants: R/B/I/BI)
- **PDFService.php** — Factory pattern, header/footer ราชการ, watermark, Thai date
- **3 PDF Templates:**
  - `project_progress_report.php` — รายงานความก้าวหน้าโครงงาน (7 sections)
  - `portfolio_digest.php` — แฟ้มสะสมงานรวมเล่ม (Premium cover + stats + works)
  - `executive_report.php` — สรุปผลการดำเนินงานระดับองค์กร (8 sections + Excellence Registry)
- **PDF Export Buttons** ในหน้า Portfolio, Project Show, Analytics Dashboard

### ✅ Phase 4: AI Insights & Field-Specific Readiness
- **AIService.php** — วิเคราะห์ความพร้อมรายสาขาวิชา 7 tracks:
  - MED_HEALTH, ENGINEERING, PURE_SCIENCE, COMPUTING, LIBERAL_ARTS, BUSINESS, DESIGN
- **Scoring Engine:** Weighted analysis (GPA 20%, Categories 25%, Skills 15%, RIASEC 15%, Achievement 10%, Logbook 5%, Awards 10%)
- **Portfolio Quality Audit** — ให้เกรด A-F รายผลงาน (6 criteria)
- **Smart Recommendations** — คำแนะนำ prioritized (high/medium/low)
- **AI Context Builder** — สำหรับส่งไป Gemini API (พร้อม fallback rule-based)
- **Views:** `ai/field_readiness.php` + `ai/portfolio_audit.php`

### ✅ Phase 5: Dynamic Gamification
- **BadgeService.php** — 25 เหรียญ across 5 tiers:
  - 🥉 Bronze (5) — เริ่มต้นเดินทาง
  - 🥈 Silver (7) — พัฒนาอย่างต่อเนื่อง
  - 🥇 Gold (6+3 AI) — โดดเด่น + AI-based
  - 💎 Platinum (4) — ตำนาน
  - 🔮 Secret (3) — เงื่อนไขลับ
- **Event-Driven System** — `onEvent()` ตรวจ badge หลังกระทำสำคัญ
- **AI Integration** — `checkAI()` เชื่อม Phase 4 → Phase 5
- **Badge Collection UI** — `rewards/badges.php` พร้อม tier progress cards

---

## 🔑 Key Services

### PDFService (`app/services/PDFService.php`)
```php
// Primary API
PDFService::renderView('pdf/portfolio_digest', $data, [
    'title' => '...', 'filename' => '...', 'mode' => 'D', 'watermark' => '...'
]);

// Legacy API
PDFService::download($html, 'filename', 'Title');
PDFService::stream($html, 'filename', 'Title');
```

### AIService (`app/services/AIService.php`)
```php
// Full field analysis
$analysis = AIService::analyzeFieldReadiness($userId);
// Single field detail
$single  = AIService::analyzeSingleField($userId, 'MED_HEALTH');
// Portfolio quality audit
$audit   = AIService::auditPortfolioQuality($studentId);
// Smart recommendations
$recs    = AIService::getSmartRecommendations($userId);
```

### BadgeService (`app/services/BadgeService.php`)
```php
// Check all badges → award new ones
$result  = BadgeService::checkAll($userId);
// Event-driven (call after key actions)
$result  = BadgeService::onEvent($userId, 'portfolio_created');
// AI-based badges
$result  = BadgeService::checkAI($userId);
// Get collection summary
$summary = BadgeService::getCollectionSummary($userId);
```

---

## 🗄️ Database Reference

### Core Tables
`users`, `student_profiles`, `portfolio_categories`, `portfolio_items`, `portfolio_collaborators`, `portfolio_skills`, `skills_master`, `skill_categories`, `academic_records`, `student_goals`, `goal_templates`, `student_interests`, `student_assessments`, `user_badges`, `badges_master`

### Project Tracking
`projects`, `project_members`, `project_subjects`, `project_documents`, `logbooks`, `project_evaluations`, `milestones`

### Analytics & AI
`ai_recommendations`, `ai_field_analysis_cache` (013), `ai_gap_notifications` (013), `readiness_scores`, `tcas_predictions`, `analytics_cache`, `engagement_metrics`

### Gamification
`rewards`, `user_rewards`, `badges_master`, `user_badges`

### Database Views
- `vw_student_360` — ข้อมูลนักเรียน 360°
- `vw_early_warning` — scoring ความเสี่ยง (low/medium/high/critical)
- `vw_school_analytics` — สถิติภาพรวมโรงเรียน
- `vw_field_readiness_summary` (013) — สรุปความพร้อมรายสาขาวิชา

### Migrations (001-014)
Latest: `013_phase4_ai_insights.sql`, `014_phase5_gamification.sql`

---

## 🔗 Route Quick Reference

| Feature | Route | Controller |
|---------|-------|-----------|
| Dashboard | `GET /dashboard` | DashboardController |
| Portfolio | `GET /portfolio` | PortfolioController |
| Portfolio PDF | `GET /portfolio/export-pdf` | PortfolioController@exportDigest |
| Projects | `GET /projects` | ProjectController |
| Project PDF | `GET /projects/{id}/pdf` | ProjectController@exportPdf |
| AI Guide | `GET /ai-guide` | AIController |
| Field Readiness | `GET /ai/field-readiness` | AIController@fieldReadiness |
| Portfolio Audit | `GET /ai/portfolio-audit` | AIController@portfolioAudit |
| Rewards | `GET /rewards` | RewardController |
| Badge Collection | `GET /rewards/badges` | RewardController@showBadges |
| Executive Report | `GET /analytics/dashboard/export` | AnalyticsController@exportExecutiveReport |
| Analytics Dashboard | `GET /analytics/dashboard` | AnalyticsController@schoolDashboard |
| Early Warning | `GET /analytics/early-warning` | AnalyticsController@earlyWarning |
| Teacher Reviews | `GET /teacher/pending-reviews` | TeacherController |
| Teacher Quick Sweep | `GET /teacher/quick-sweep` | TeacherController@quickSweep |

---

## 🚀 Setup / Deployment

### Fresh Install
1. Import `database/schema_combined.sql` into MySQL
2. Import `database/seeds/001_default_data.sql`
3. Run migrations 013 + 014 if schema_combined doesn't include them
4. Set `.env` with DB credentials + GEMINI_API_KEY (optional)
5. Run `install_pdf_fonts.php` in browser once
6. `composer install` (mPDF already in composer.json)

### Font Installation
```
Storage: storage/fonts/ (Sarabun-Regular.ttf, Bold, Italic, BoldItalic)
Temp:    storage/mpdf_tmp/
```

### Environment Variables (.env)
```
DB_HOST=localhost
DB_NAME=pcshsksn_portfolio
DB_USER=root
DB_PASS=
GEMINI_API_KEY=your_key_here   # Optional — rule-based fallback works without it
GEMINI_MODEL=gemini-2.0-flash
CRON_TOKEN=default-secret-change-me
```

---

## 📊 Feature Coverage Matrix

| Role | Features Available |
|------|-------------------|
| **Student** | Portfolio CRUD, Projects, AI Guide, Field Readiness, Portfolio Audit, Badge Collection, Rewards, TCAS Readiness, RIASEC Assessment, Goals |
| **Teacher** | Pending Reviews, Bulk Verify, Quick Sweep, Annotated Feedback, Mentor Dashboard, Student 360° View |
| **Admin** | User Management, Categories, Settings, Analytics, Mock Data Generation, System Rescue |
| **Executive** | Executive Summary, Early Warning, TCAS Prediction, Excellence Report, Showcase, Analytics Dashboard + PDF Export |

---

## ⚠️ Known Limitations & Future Work

1. **Google Gemini API** — Optional. System works 100% with rule-based analysis without it.
2. **School Name** — PDF header says "ขอนแก่น" but school is "กาฬสินธุ์" — update in PDFService::defaultHeader()
3. **Badge Event Triggers** — Currently only `portfolio_created` is wired. Add `onEvent()` calls to: logbook store, project create, goal set, RIASEC submit.
4. **Points System** — `users.points` column exists but auto-point earning from badges/AI not yet implemented.
5. **Real-time Notifications** — Uses DB-based notifications. Consider WebSocket/Pusher for live alerts.
6. **PDF Images** — Logo path `public/assets/img/logo-pcshs-ksn.png` may need to be added for official header.

---

## 📝 Coding Conventions

- **No framework** — Everything is vanilla PHP with custom MVC pattern
- **Database** — `Database::fetch()`, `Database::fetchAll()`, `Database::execute()`, `Database::insert()`
- **Auth** — `Auth::id()`, `Auth::hasAnyRole(['admin','teacher'])`, `Auth::logAction()`
- **Security** — `CSRF::check()` on all POST, `ErrorHandler::forbidden()` for auth failures
- **Redirect** — `App::redirect('route')`
- **Flash Messages** — `Session::flash('success'|'error'|'warning', 'message')`
- **Views** — `$this->view('path/to/view', $data)` in controllers
- **URLs** — `url('route')` helper function
- **URL Uploads** — `uploadUrl('path')` helper function
- **Logging** — `App::log('message', 'ERROR'|'WARNING'|'INFO')`

---

## 🏆 Project Statistics

| Metric | Count |
|--------|-------|
| **Controllers** | 22 |
| **Services** | 4 (AIService, BadgeService, PDFService, ReportService) |
| **Views** | 30+ |
| **Routes** | 90+ |
| **Database Tables** | 30+ |
| **Badges** | 25 dynamic + 3 AI |
| **PDF Templates** | 3 |
| **AI Field Tracks** | 7 |
| **Migration Files** | 14 |
| **PHP Files** | 674 |

---

*"ระบบนี้ถูกออกแบบมาเพื่อให้นักเรียนเก่งขึ้น ครูสะดวกขึ้น และผู้บริหารเห็นภาพชัดเจนขึ้น"*
