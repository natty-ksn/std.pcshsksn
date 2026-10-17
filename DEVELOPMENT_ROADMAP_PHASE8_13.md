# 🗺️ แผนการพัฒนา PCSHSKSN e-Portfolio ระยะที่ 8-13

**โครงการ:** ระบบแฟ้มสะสมผลงานอัจฉริยะ — โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์
**จัดทำโดย:** งานศูนย์ข้อมูลสารสนเทศ | กลุ่มบริหารงานวิชาการ
**วันที่:** 7 เมษายน 2569
**สถานะ:** ✅ **ผ่านการรีวิวและปรับปรุงตาม Senior Dev Best Practices แล้ว**

---

## 🏆 Senior Dev Best Practices ที่นำเข้ามาใช้ (ปรับปรุงล่าสุด)

เอกสารนี้ได้รับการปรับปรุงตามข้อเสนอแนะจาก Senior Developer เพื่อให้เป็น **Enterprise-grade Standard** อย่างแท้จริง:

### ✅ 1. Shift-Left Testing (Task 8.0)

> **หลักการ:** "เขียน Test ก่อนแก้ Code" — ป้องกัน Regression Bugs

- **เพิ่ม Task 8.0** ให้เขียน Unit Tests สำหรับ EXPService และ BadgeService **ก่อน** แก้ไข Core Logic
- **GATE Mechanism:** Task 8.1-8.6 เริ่มไม่ได้จนกว่า Task 8.0 จะเสร็จและ tests ผ่าน 100%
- **ผลลัพธ์:** การันตีว่า Logic เดิมไม่พังตอนแก้ไข, มี Safety Net ตลอดการพัฒนา

### ✅ 2. MVP Approach สำหรับ Feature ใหม่ (Phase 10 & 11)

> **หลักการ:** "ปล่อยของน้อยแต่เร็ว แล้วค่อยเพิ่ม" — ลดความเสี่ยงเรื่อง Timeline

- **Task 10.1 Points Shop:** เริ่มต้นด้วยสินค้า 3-5 รายการแบบง่ายๆ ก่อน
  - MVP: Premium Theme (100pts), Exclusive Badge (200pts), PDF Export (150pts)
  - Phase 2: เพิ่มเงื่อนไขซับซ้อน (limited edition, bulk discounts)
- **เป้าหมาย:** เห็น Data Flow ทำงานจริงภายใน 1-2 สัปดาห์แรก

### ✅ 3. Database Audit Log System (Task 10.2.5)

> **หลักการ:** "ทุก Critical Action ต้อง Trace ได้" — Data Integrity & PDPA Compliance

- **เพิ่ม Task 10.2.5:** สร้างระบบเก็บ Activity Log แบบ Immutable
  - ใครทำอะไร (who), ทำเมื่อไหร่ (when), ที่ไหน (IP/User Agent)
  - Before/After values สำหรับข้อมูลสำคัญ (grades, EXP, verification)
- **ครอบคลุม:** `portfolio_deleted`, `grade_modified`, `project_approved`, `exp_awarded`, etc.
- **Admin Dashboard:** ดู logs, ค้นหา, export CSV, alert suspicious activity

### ✅ 4. Cache Invalidation Strategy (Task 9.1 Enhanced)

> **หลักการ:** "Cache โดยไม่มี Invalidation = ข้อมูลเก่า = ผู้ใช้เสีย kepercayaan"

- **Event-Driven Invalidation:** ใช้ Event Listeners ทำลาย cache เมื่อข้อมูลเปลี่ยน
  - ตัวอย่าง: `portfolio_created` → invalidate `dashboard_stats:{studentId}`
- **TTL (Time-To-Live):** ตั้ง expiry สั้นๆ (5 นาที) สำหรับข้อมูลเปลี่ยนบ่อย
- **Tag-Based Caching:** ใช้ tags เพื่อ invalidate เป็นกลุ่มได้
- **Monitoring:** มี log สำหรับ cache hits/misses เพื่อวิเคราะห์ performance

### ✅ 5. Migration Convention for Multi-Agent Dev (Phase 12 Note)

> **หลักการ:** "ป้องกัน Migration Conflicts ก่อนเกิดปัญหา"

- **Naming Convention:** `{sequence_number}_{descriptive_name}.sql`
  - ตัวอย่าง: `025_add_peer_review_indexes.sql`
- **Rollback Requirement:** ทุก migration ต้องมี DOWN script ใน comments
- **Pre-Flight Check:** Agents ต้องตรวจสอบ migration ล่าสุดก่อนสร้างใหม่
- **Agent Coordination:** ใช้ `MIGRATION_LOCK` file เพื่อป้องกัน conflicts
- **Rule:** Agents ต้องแจ้ง "กำลังสร้าง migration #{number}" ใน task assignment

---

## 📊 สรุปสถานะระบบปัจจุบัน

ระบบ PCSHSKSN e-Portfolio ผ่านการพัฒนาครบ **7 เฟส** แล้ว (Phase 1-7) ครอบคลุม:

| เฟส     | ชื่อ                                   | สถานะ           |
| ------- | -------------------------------------- | --------------- |
| Phase 1 | Collaborative Ecosystem & Productivity | ✅ เสร็จสมบูรณ์ |
| Phase 2 | Teacher Insight & Quality Control      | ✅ เสร็จสมบูรณ์ |
| Phase 3 | Executive Hub & Premium PDF Engine     | ✅ เสร็จสมบูรณ์ |
| Phase 4 | AI Insights & Field-Specific Readiness | ✅ เสร็จสมบูรณ์ |
| Phase 5 | Dynamic Gamification                   | ✅ เสร็จสมบูรณ์ |
| Phase 6 | Premium UX/UI                          | ✅ เสร็จสมบูรณ์ |
| Phase 7 | Production Readiness                   | ✅ เสร็จสมบูรณ์ |

**สถิติระบบ:**

- Controllers: 23 ตัว | Services: 6 ตัว | Views: 30+ ไฟล์
- Routes: 90+ เส้นทาง | Database Tables: 30+ ตาราง
- Badges: 28 เหรียญ (5 tiers) | AI Field Tracks: 7 สาขา
- Lines of Code: ~15,000+ บรรทัด

---

## 🔍 ผลการตรวจสอบระบบ (System Audit)

จากการตรวจสอบโค้ดฐานอย่างละเอียด พบประเด็นที่ต้องดำเนินการดังนี้:

### 🔴 ระดับวิกฤต (ต้องแก้ก่อน Production)

| ลำดับ | ปัญหา                                                                                                                                                  | ผลกระทบ                                            |
| ----- | ------------------------------------------------------------------------------------------------------------------------------------------------------ | -------------------------------------------------- |
| C1    | **EXP ไม่ถูกมอบใน action ส่วนใหญ่** — นักเรียนได้รับ EXP เฉพาะจาก badge_earned เท่านั้น ขาดจาก portfolio_created, project_completed, logbook_added ฯลฯ | ระบบ Gamification ไม่สมบูรณ์ นักเรียนไม่มีแรงจูงใจ |
| C2    | **Badge event triggers ไม่ครบ** — บาง action ไม่เรียก `BadgeService::onEvent()` เช่น award_added ที่แยกจาก portfolio_created, goal_set ไม่ทำงานถูกต้อง | เหรียญตราไม่ถูกมอบตามเงื่อนไข                      |
| C3    | **ชื่อโรงเรียนใน PDF** — เอกสารระบุว่ามี "ขอนแก่น" แทน "กาฬสินธุ์" ใน header ของ PDF (ต้องตรวจสอบจริงในเทมเพลต)                                        | เอกสารทางการไม่ถูกต้อง                             |
| C4    | **Migration 015** — เพิ่มคอลัมน์ `verification_status` และ index แต่ไม่แน่ใจว่ารันแล้วหรือไม่                                                          | Teacher review อาจทำงานผิดพลาด                     |

### 🟠 ระดับสูง (ควรแก้ไขโดยเร่งด่วน)

| ลำดับ | ปัญหา                                                                                                          | ผลกระทบ                                |
| ----- | -------------------------------------------------------------------------------------------------------------- | -------------------------------------- |
| H1    | **ไม่มี Points Spending** — `users.points` สะสมแต่ไม่มีระบบใช้จ่าย (rewards shop)                              | EXP/Points ไร้ความหมายนอกจากระดับ      |
| H2    | **No EXP trigger สำหรับ project_completed** — ไม่มีโค้ดเรียก `EXPService::award($userId, 'project_completed')` | นักเรียนไม่ได้รับรางวัลจากการจบโครงงาน |
| H3    | **No EXP trigger สำหรับ portfolio_verified** — ครู verify ผลงานแต่นักเรียนไม่ได้รับ EXP                        | ขาดแรงจูงใจในการส่งตรวจ                |
| H4    | **No EXP trigger สำหรับ profile_completed** — ไม่มี logic ตรวจสอบโปรไฟล์สมบูรณ์                                | นักเรียนไม่กรอกโปรไฟล์ครบ              |
| H5    | **Notification ไม่ครบ** — Notifier มีแต่ไม่ได้เรียกในทุกจุดที่เกี่ยวข้อง เช่น project_approved, comment_added  | นักเรียนพลาดการแจ้งเตือนสำคัญ          |
| H6    | **No Cache Implementation** — `analytics_cache` table มีแต่ไม่ได้ใช้                                           | Analytics dashboard ช้าเมื่อข้อมูลมาก  |

### 🟡 ระดับกลาง (ควรปรับปรุง)

| ลำดับ | ปัญหา                                                                      | ผลกระทบ                       |
| ----- | -------------------------------------------------------------------------- | ----------------------------- |
| M1    | **N+1 Query Risk** — หลาย controller ดึงข้อมูลแบบ loop query               | ประสิทธิภาพต่ำเมื่อข้อมูลเยอะ |
| M2    | **No API Rate Limiting** — AJAX endpoints ป้องกัน rate limit               | เสี่ยงถูกโจมตี                |
| M3    | **No Soft Deletes** — ลบข้อมูลแบบถาวร (portfolio, projects)                | กู้คืนข้อมูลไม่ได้            |
| M4    | **No Student Data Export** — ไม่มีระบบดาวน์โหลดข้อมูลทั้งหมด (สิทธิ์ PDPA) | ไม่สอดคล้อง PDPA              |
| M5    | **Font Installation Manual** — ต้องรัน install_pdf_fonts.php ในเบราว์เซอร์ | ติดตั้งลำบากใน production     |
| M6    | **No Automated Tests** — ไม่มี test coverage เลย                           | เสี่ยงเกิด regression         |

### 🟢 ระดับต่ำ (เพิ่มเติมในอนาคต)

| ลำดับ | รายการ                                  | ประโยชน์                                       |
| ----- | --------------------------------------- | ---------------------------------------------- |
| L1    | Real-time notifications (SSE/WebSocket) | แจ้งเตือนทันทีไม่ต้อง refresh                  |
| L2    | Portfolio template marketplace          | แชร์เทมเพลตผลงานระหว่างนักเรียน                |
| L3    | Peer review system                      | นักเรียนรีวิวผลงานกันเอง                       |
| L4    | Alumni tracking UI                      | ติดตามศิษย์เก่า (มี table แล้ว แต่ยังไม่มี UI) |
| L5    | Mobile app readiness                    | API สำหรับ mobile app ในอนาคต                  |

---

## 🎯 แผนการพัฒนาระยะที่ 8-13

---

## 🔧 Phase 8: ระบบมั่นคง (Stability & Integration Fixes)

**ระยะเวลา:** 1-2 สัปดาห์
**เป้าหมาย:** แก้ไข bugs และ integrate ระบบที่มีอยู่ให้ทำงานร่วมกันอย่างสมบูรณ์
**หลักการสำคัญ:** _"ห้ามสร้างตึกต่อหากฐานรากยังมีร้าว"_

### ⚠️ SENIOR DEV NOTE: Shift-Left Testing Strategy

> ก่อนแก้ไข Core Logic (EXP/Badge Services) ต้องเขียน **Unit Tests** ครอบไว้ก่อน เพื่อการันตีว่า:
>
> - Logic เดิมยังทำงานได้หลังแก้ไข (Regression Testing)
> - แต่ละ function ให้ผลลัพธ์ถูกต้องตาม expectation
> - ไม่มี side effects ที่คาดไม่ถึง
>
> **Task 8.0 ต้องเสร็จก่อน Task 8.1-8.6**

---

### Task 8.0: เขียน Unit Tests สำหรับ Core Services (Shift-Left Testing)

**ความสำคัญ:** 🔴 วิกฤต (ต้องทำก่อน Task อื่นใน Phase 8)

**สิ่งที่ต้องทำ:**

- ติดตั้ง PHPUnit และสร้าง test infrastructure:
  ```bash
  composer require --dev phpunit/phpunit
  ```
- สร้าง `phpunit.xml` configuration:
  ```xml
  <?xml version="1.0" encoding="UTF-8"?>
  <phpunit bootstrap="vendor/autoload.php"
           colors="true"
           stopOnFailure="false">
      <testsuites>
          <testsuite name="Unit">
              <directory>tests/Unit</directory>
          </testsuite>
      </testsuites>
      <php>
          <env name="APP_ENV" value="testing"/>
          <env name="DB_DATABASE" value="pcshsksn_test"/>
      </php>
  </phpunit>
  ```
- สร้าง **Unit Tests** สำหรับ Services ที่จะถูกแก้ไขใน Phase 8:
  - `tests/Unit/EXPServiceTest.php` — ทดสอบ EXP calculation ทุก action type
  - `tests/Unit/BadgeServiceTest.php` — ทดสอบ badge award logic ทุกเงื่อนไข
  - `tests/Unit/NotifierTest.php` — ทดสอบ notification creation
  - `tests/Unit/ValidatorTest.php` — ทดสอบ input validation

**ตัวอย่าง Test Case สำหรับ EXPService:**

```php
class EXPServiceTest extends TestCase
{
    public function test_award_portfolio_created_gives_50_exp()
    {
        $userId = 1;
        EXPService::award($userId, 'portfolio_created');

        $user = User::find($userId);
        $this->assertEquals(50, $user->exp_gained - $user->initial_exp);
        $this->assertEquals(50, $user->points);
    }

    public function test_award_project_completed_gives_200_exp()
    {
        $userId = 1;
        EXPService::award($userId, 'project_completed');

        $user = User::find($userId);
        $this->assertEquals(200, $user->exp_gained - $user->initial_exp);
    }

    public function test_award_invalid_action_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        EXPService::award(1, 'nonexistent_action');
    }
}
```

**ไฟล์ที่ต้องสร้าง:**

- `phpunit.xml` — PHPUnit configuration
- `tests/Unit/EXPServiceTest.php`
- `tests/Unit/BadgeServiceTest.php`
- `tests/Unit/NotifierTest.php`
- `tests/TestCase.php` — Base test case with DB setup
- `database/migrations/000_create_test_database.sql` — Test DB schema

**เกณฑ์สำเร็จ:**

- ✅ PHPUnit ติดตั้งและรันได้ (`phpunit` command)
- ✅ มี test coverage สำหรับ EXPService ≥ 80%
- ✅ มี test coverage สำหรับ BadgeService ≥ 80%
- ✅ ทุก test ผ่าน **ก่อน** แก้ไข Core Logic
- ✅ มี CI script สำหรับรัน tests อัตโนมัติ (`scripts/run_tests.sh`)

**⚠️ GATE:** Task 8.1-8.6 เริ่มไม่ได้จนกว่า Task 8.0 จะเสร็จและ tests ผ่าน 100%

### Task 8.1: แก้ไขระบบ EXP ให้ครบทุก action

**ความสำคัญ:** 🔴 วิกฤต

**สิ่งที่ต้องทำ:**

- เพิ่ม `EXPService::award()` ในทุก action ที่เกี่ยวข้อง:
  - `portfolio_created` — สร้างผลงานใหม่ (50 EXP) ✅ มีอยู่แล้ว แต่ต้องตรวจสอบว่าเรียกจริง
  - `portfolio_featured` — ตั้งผลงานเด่น (75 EXP) ❌ ยังไม่เรียก
  - `project_created` — สร้างโครงงาน (80 EXP) ❌ ยังไม่เรียก
  - `project_completed` — จบโครงงาน (200 EXP) ❌ ยังไม่เรียก
  - `logbook_added` — บันทึก logbook (20 EXP) ❌ ยังไม่เรียก
  - `goal_set` — ตั้งเป้าหมาย (30 EXP) ❌ ยังไม่เรียก
  - `goal_achieved` — บรรลุเป้าหมาย (150 EXP) ❌ ยังไม่เรียก
  - `riasec_taken` — ทำแบบประเมิน RIASEC (40 EXP) ❌ ยังไม่เรียก
  - `profile_completed` — กรอกโปรไฟล์ครบ (100 EXP) ❌ ยังไม่มี logic ตรวจสอบ
  - `comment_received` — ได้รับคอมเมนต์จากครู (10 EXP) ❌ ยังไม่เรียก

**ไฟล์ที่ต้องแก้ไข:**

- `app/controllers/PortfolioController.php` — เพิ่ม EXP เมื่อสร้าง/ตั้งเด่น/ได้รับ verify
- `app/controllers/ProjectController.php` — เพิ่ม EXP เมื่อสร้าง/จบโครงงาน
- `app/controllers/LogbookController.php` — เพิ่ม EXP เมื่อเพิ่ม logbook
- `app/controllers/GoalController.php` — เพิ่ม EXP เมื่อตั้ง/บรรลุเป้าหมาย
- `app/controllers/AssessmentController.php` — เพิ่ม EXP เมื่อทำ RIASEC
- `app/controllers/ProfileController.php` — เพิ่ม EXP เมื่อโปรไฟล์ครบ 100%
- `app/controllers/TeacherController.php` — เพิ่ม EXP ให้นักเรียนเมื่อ receive comment
- `app/controllers/AdvisorController.php` — เพิ่ม EXP เมื่อโครงงานผ่านอนุมัติ

**เกณฑ์สำเร็จ:**

- ✅ นักเรียนได้รับ EXP ทันทีหลังจากทำ action ทุกอย่าง
- ✅ Session `user_exp`, `user_level`, `user_points` อัพเดทถูกต้อง
- ✅ มี notification แจ้ง EXP gain ทุกครั้ง
- ✅ Level up notification ทำงานเมื่อถึงเกณฑ์

---

### Task 8.2: แก้ไข Badge Event Triggers ให้ครบ

**ความสำคัญ:** 🔴 วิกฤต

**สิ่งที่ต้องทำ:**

- ตรวจสอบว่าทุก action ที่เกี่ยวกับ badge เรียก `BadgeService::onEvent()` ถูกต้อง
- แก้ไข `portfolio_created` event ที่มี `award_added` ซ้อนอยู่ภายใน — แยกให้ชัดเจน
- เพิ่ม event trigger ที่ยังขาด:
  - `award_added` — เมื่อเพิ่มรางวัล/เกียรติบัตร (แยกจาก portfolio_created)
  - `skill_added` — เมื่อเพิ่มทักษะในผลงาน

**ไฟล์ที่ต้องแก้ไข:**

- `app/controllers/PortfolioController.php` — แยก award_added ออกจาก portfolio_created flow
- `app/controllers/TeacherController.php` — เพิ่ม event เมื่อ verify portfolio
- `app/controllers/AdvisorController.php` — เพิ่ม event เมื่อ approve project

**เกณฑ์สำเร็จ:**

- ✅ Badge ถูกมอบอัตโนมัติหลัง action ทุกอย่าง
- ✅ ไม่มี badge ที่พลาดเพราะไม่ได้ trigger
- ✅ Notification เหรียญตราแสดงผลถูกต้อง

---

### Task 8.3: ตรวจสอบและแก้ไขชื่อโรงเรียนใน PDF

**ความสำคัญ:** 🔴 วิกฤต

**สิ่งที่ต้องทำ:**

- ตรวจสอบทุก PDF template ว่าใช้ชื่อโรงเรียนถูกต้อง:
  - `views/pdf/project_progress_report.php`
  - `views/pdf/portfolio_digest.php`
  - `views/pdf/executive_analytics.php` (ถ้ามี)
  - `app/services/PDFService.php` — ตรวจสอบ `defaultHeader()` และ `defaultFooter()`
- แก้ไข "ขอนแก่น" → "กาฬสินธุ์" ทุกจุด

**ไฟล์ที่ต้องตรวจสอบ:**

- `app/services/PDFService.php` — header/footer มาตรฐาน
- PDF template views ทุกไฟล์

**เกณฑ์สำเร็จ:**

- ✅ ทุก PDF แสดงชื่อ "โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์" ถูกต้อง
- ✅ Logo และ watermark ถูกต้อง

---

### Task 8.4: รัน Migration 015 และตรวจสอบ Database Integrity

**ความสำคัญ:** 🔴 วิกฤต

**สิ่งที่ต้องทำ:**

- ตรวจสอบว่า migration 015 รันแล้วหรือยัง
- ตรวจสอบคอลัมน์ `verification_status` ใน `portfolio_items`
- ตรวจสอบ index `idx_verification_status`
- Sync ข้อมูล `is_verified` → `verification_status`

**ไฟล์ที่ต้องดำเนินการ:**

- `database/migrations/015_hotfix_verification_status.sql`
- สร้าง script ตรวจสอบ migration status

**เกณฑ์สำเร็จ:**

- ✅ Migration 015 รันสำเร็จ
- ✅ Teacher review ทำงานถูกต้อง
- ✅ ไม่มี SQL error จาก missing column

---

### Task 8.5: เชื่อมต่อ Notification ให้ครบทุกจุด

**ความสำคัญ:** 🟠 สูง

**สิ่งที่ต้องทำ:**

- เพิ่ม `Notifier::` call ในทุก event สำคัญ:
  - `project_approved` — ครูอนุมัติโครงงาน
  - `project_rejected` — ครูส่งโครงงานกลับ
  - `comment_added` — ครูคอมเมนต์ผลงาน
  - `portfolio_collaboration_invited` — เชิญร่วมผลงาน
  - `milestone_due_soon` — Milestone ใกล้ถึงกำหนด
  - `goal_achieved` — บรรลุเป้าหมาย
  - `badge_earned` — ได้รับเหรียญ (✅ มีอยู่แล้วใน BadgeService)

**ไฟล์ที่ต้องแก้ไข:**

- `app/controllers/AdvisorController.php` — project approved/rejected
- `app/controllers/TeacherController.php` — comment added, portfolio verified
- `app/controllers/MilestoneController.php` (ถ้ามี) หรือ `AnalyticsController@cronMilestones`
- `app/controllers/GoalController.php` — goal achieved
- `app/controllers/PortfolioController.php` — collaboration invited

**เกณฑ์สำเร็จ:**

- ✅ นักเรียนได้รับ notification ทุก event ที่เกี่ยวข้อง
- ✅ Notification badge ใน sidebar แสดงจำนวนถูกต้อง
- ✅ Mark as read ทำงาน

---

### Task 8.6: สร้าง System Health Check Script

**ความสำคัญ:** 🟠 สูง

**สิ่งที่ต้องทำ:**

- สร้าง script ตรวจสอบสุขภาพระบบ:
  - Database connection
  - Migration status (ค้างหรือไม่)
  - Sarabun font installation
  - Upload directory permissions
  - PHP version check (ต้อง 8.0+)
  - Required PHP extensions (PDO, GD, JSON, mbstring)
  - .env file validation
  - Vendor dependencies (mPDF)

**ไฟล์ที่ต้องสร้าง:**

- `public/health-check.php` — Web-based health dashboard
- หรือ `scripts/health_check.php` — CLI script

**เกณฑ์สำเร็จ:**

- ✅ แสดงสถานะทุก component ในหน้าเดียว
- ✅ แจ้งเตือนเมื่อมีปัญหา
- ✅ แนะนำวิธีแก้ไข

---

## ⚡ Phase 9: เพิ่มประสิทธิภาพ (Performance & Optimization)

**ระยะเวลา:** 2-3 สัปดาห์
**เป้าหมาย:** ทำให้ระบบเร็วขึ้น ลดภาระ database รองรับผู้ใช้พร้อมกันมากขึ้น

### Task 9.1: Implement Query Caching สำหรับ Analytics

**ความสำคัญ:** 🟡 กลาง

**สิ่งที่ต้องทำ:**

- ใช้ `analytics_cache` table ที่มีอยู่แล้วให้เป็นประโยชน์
- สร้าง `CacheService` สำหรับ caching:
  - School analytics (cache 30 นาที)
  - Early warning scores (cache 1 ชั่วโมง)
  - TCAS readiness (cache 2 ชั่วโมง)
  - Dashboard stats (cache 5 นาที)
- เพิ่ม cache invalidation เมื่อข้อมูลเปลี่ยน

### ⚠️ SENIOR DEV NOTE: Cache Invalidation Strategy

> การทำ Cache โดยไม่มี Invalidtion Strategy ที่ดีจะทำให้ผู้ใช้เห็นข้อมูลเก่าและสูญเสียความเชื่อมั่นในระบบ
>
> **หลักการสำคัญ:**
>
> 1. **Event-Driven Invalidation** — ใช้ Event Listeners ทำลาย cache เมื่อข้อมูลเปลี่ยน
> 2. **TTL (Time-To-Live)** — ตั้ง expiry time สั้นๆ (5 นาที) สำหรับข้อมูลที่มีการเปลี่ยนบ่อย
> 3. **Tag-Based Caching** — ใช้ tags เพื่อสามารถ invalidate เป็นกลุ่มได้
> 4. **Version-Based Invalidation** — เพิ่ม version key ใน cache key เพื่อ invalidate ทุก version

**Cache Invalidation Rules:**

| ข้อมูล               | TTL       | Event ที่ Invalidate                     | หมายเหตุ          |
| -------------------- | --------- | ---------------------------------------- | ----------------- |
| Dashboard stats      | 5 นาที    | `portfolio_created`, `project_completed` | ข้อมูลเปลี่ยนบ่อย |
| School analytics     | 30 นาที   | `badge_earned`, `level_up`               | สรุปภาพรวม        |
| Early warning scores | 1 ชั่วโมง | `assessment_completed`, `grade_changed`  | ไม่ค่อยเปลี่ยน    |
| TCAS readiness       | 2 ชั่วโมง | `grade_changed`, `assessment_completed`  | คำนวณซับซ้อน      |

**Event Listener Implementation:**

```php
// ตัวอย่าง: เมื่อเพิ่ม portfolio → invalidate dashboard cache
class PortfolioEventListener
{
    public static function onPortfolioCreated($studentId) {
        // Invalidate dashboard cache ของนักเรียน
        Cache::forget("dashboard_stats:{$studentId}");

        // Invalidate school analytics cache (บางส่วน)
        Cache::forget("school_analytics:summary");

        // Invalidate teacher pending reviews cache
        Cache::forget("teacher_pending_reviews");
    }
}
```

**CacheService API:**

```php
class CacheService {
    // Set cache with TTL และ tags
    public static function put(string $key, $value, int $ttl, array $tags = []): void;

    // Get cache
    public static function get(string $key, $default = null);

    // Invalidate by tag (ทำลายทุก keys ใน tag)
    public static function invalidateTags(array $tags): void;

    // Invalidate specific key
    public static function forget(string $key): void;

    // Force refresh (ทำลาย cache และสร้างใหม่)
    public static function remember(string $key, callable $callback, int $ttl): mixed;
}
```

**ไฟล์ที่ต้องสร้าง/แก้ไข:**

- สร้าง `core/Cache.php` — Simple file/DB cache engine พร้อม TTL และ tags
- สร้าง `core/EventDispatcher.php` — Event listener system
- สร้าง `core/EventListeners/CacheInvalidationListener.php` — Auto invalidate cache
- แก้ไข `app/services/ReportService.php` — ใช้ cache
- แก้ไข `app/controllers/AnalyticsController.php` — อ่านจาก cache
- แก้ไข `app/controllers/PortfolioController.php` — เพิ่ม event trigger

**เกณฑ์สำเร็จ:**

- ✅ Analytics dashboard โหลดเร็วขึ้น 70%+
- ✅ Cache auto-expire ตาม TTL
- ✅ Cache invalidate ทันทีเมื่อข้อมูลเปลี่ยน (ภายใน 5 นาที)
- ✅ Admin สามารถ force refresh cache ได้
- ✅ ไม่มีกรณีที่ผู้ใช้เห็นข้อมูลเก่าเกิน 5 นาที
- ✅ มี log สำหรับ cache hits/misses เพื่อวิเคราะห์

---

### Task 9.2: แก้ไข N+1 Query Problems

**ความสำคัญ:** 🟡 กลาง

**สิ่งที่ต้องทำ:**

- ตรวจสอบทุก controller ที่มี loop query
- เปลี่ยนเป็น eager loading / JOIN queries
- จุดที่มีแนวโน้ม N+1:
  - DashboardController — fetch portfolio items พร้อม category/skills ใน 1 query
  - ProjectController — fetch project members พร้อม user info ใน 1 query
  - PortfolioController — fetch skills พร้อม portfolio items ใน 1 query

**เทคนิค:**

```php
// แทนที่จะ query ใน loop:
foreach ($items as $item) {
    $skills = Database::fetchAll("SELECT ... WHERE portfolio_id = ?", [$item['id']]);
}

// ใช้ query เดียว:
$skills = Database::fetchAll(
    "SELECT ps.*, pi.id as portfolio_id FROM portfolio_skills ps
     JOIN portfolio_items pi ON ps.portfolio_id = pi.id
     WHERE pi.student_id = ?",
    [$studentId]
);
// แล้ว group ใน PHP
```

**เกณฑ์สำเร็จ:**

- ✅ จำนวน query ลดลง 50%+ ในหน้าหลัก
- ✅ ไม่มี query ใน loop
- ✅ ใช้ EXPLAIN ตรวจสอบ query plan แล้ว

---

### Task 9.3: เพิ่ม API Rate Limiting

**ความสำคัญ:** 🟡 กลาง

**สิ่งที่ต้องทำ:**

- สร้าง rate limiter สำหรับ API endpoints:
  - `api/stats` — 30 requests/นาที
  - `api/portfolio/search` — 20 requests/นาที
  - `api/notifications/count` — 60 requests/นาที
- ใช้ database-based rate limiting (table `api_rate_limits`)
- Return 429 Too Many Requests เมื่อเกิน limit

**ไฟล์ที่ต้องสร้าง/แก้ไข:**

- สร้าง `core/RateLimiter.php`
- เพิ่ม middleware `rate_limit` ใน Router
- แก้ไข `routes/web.php` — เพิ่ม middleware ให้ API routes

**เกณฑ์สำเร็จ:**

- ✅ ป้องกัน API abuse ได้
- ✅ Return 429 พร้อม Retry-After header
- ✅ ไม่กระทบการใช้งานปกติ

---

### Task 9.4: เพิ่ม Database Indexes เพิ่มเติม

**ความสำคัญ:** 🟡 กลาง

**สิ่งที่ต้องทำ:**

- ตรวจสอบ query ที่ช้าด้วย EXPLAIN
- เพิ่ม index ที่ยังขาด:
  - `portfolio_items(student_id, status, created_at)` — composite index สำหรับ dashboard
  - `projects(advisor_id, status)` — สำหรับ teacher review
  - `logbooks(student_id, created_at)` — สำหรับ logbook timeline
  - `notifications(user_id, is_read, created_at)` — สำหรับ notification inbox
- Migration 016 สำหรับ indexes ใหม่

**ไฟล์ที่ต้องสร้าง:**

- `database/migrations/016_performance_indexes_part2.sql`

**เกณฑ์สำเร็จ:**

- ✅ Query เวลา < 100ms สำหรับหน้าหลัก
- ✅ EXPLAIN แสดงใช้ index ถูกต้อง

---

### Task 9.5: ปรับปรุง Font Installation

**ความสำคัญ:** 🟡 กลาง

**สิ่งที่ต้องทำ:**

- สร้าง CLI script แทนการรันผ่านเบราว์เซอร์:
  ```bash
  php scripts/install_fonts.php
  ```
- เพิ่ม auto-detect ใน PDFService — ถ้าไม่มี Sarabun ให้ download อัตโนมัติ (ครั้งแรก)
- เพิ่ม font verification ใน health check

**ไฟล์ที่ต้องสร้าง/แก้ไข:**

- สร้าง `scripts/install_fonts.php` — CLI font installer
- แก้ไข `app/services/PDFService.php` — auto-detect + fallback

**เกณฑ์สำเร็จ:**

- ✅ ติดตั้งฟอนต์ด้วยคำสั่งเดียว
- ✅ PDFService แจ้งเตือนเมื่อไม่มีฟอนต์
- ✅ Auto-download จาก Google Fonts ได้

---

## 🚀 Phase 10: เพิ่มฟีเจอร์ใหม่ (Feature Enhancements)

**ระยะเวลา:** 3-4 สัปดาห์
**เป้าหมาย:** เพิ่มฟีเจอร์ที่ทำให้ระบบสมบูรณ์และเป็นประโยชน์มากขึ้น

### Task 10.1: ระบบร้านค้าแต้ม (Points Shop / Rewards Redemption)

**ความสำคัญ:** 🟠 สูง

**สิ่งที่ต้องทำ:**

- สร้างระบบใช้จ่าย points ที่สะสมมา:
  - แลก premium portfolio themes
  - แลก badge พิเศษ (limited edition)
  - แลกสิทธิ์ดู AI insights ขั้นสูง
  - แลกสิทธิ์ export PDF without watermark
- สร้าง `rewards_shop` table:
  ```sql
  CREATE TABLE rewards_shop (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(200),
      description TEXT,
      points_cost INT,
      type ENUM('theme', 'badge', 'privilege', 'custom'),
      config JSON,
      stock INT, -- NULL = unlimited
      is_active BOOLEAN DEFAULT true,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
  ```
- สร้าง `user_reward_redemptions` table:
  ```sql
  CREATE TABLE user_reward_redemptions (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT,
      reward_id INT,
      points_spent INT,
      redeemed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      status ENUM('pending', 'delivered', 'cancelled') DEFAULT 'pending'
  );
  ```

**ไฟล์ที่ต้องสร้าง:**

- `app/controllers/RewardsShopController.php`
- `views/rewards/shop.php` — หน้าร้านค้า
- `views/rewards/my_redemptions.php` — ประวัติการแลก
- `database/migrations/017_rewards_shop.sql`
- แก้ไข `routes/web.php` — เพิ่ม routes `/rewards/shop/*`

**เกณฑ์สำเร็จ:**

- ✅ นักเรียนดูสินค้าและแลกด้วย points ได้
- ✅ Points ถูกหักทันทีเมื่อแลก
- ✅ มีประวัติการแลก
- ✅ Admin จัดการสินค้าได้

### 🎯 MVP Approach for Points Shop

> **Minimum Viable Product:** เริ่มต้นด้วยสินค้า 3-5 รายการแบบง่ายๆ:
>
> 1. Premium Portfolio Theme (100 points)
> 2. Exclusive Badge (200 points)
> 3. PDF Export without Watermark (150 points)
>
> **Phase 2:** เพิ่มเงื่อนไขซับซ้อน (limited edition, seasonal offers, bulk discounts)
>
> **เป้าหมาย:** เห็น data flow ทำงานจริงภายใน 1-2 สัปดาห์แรก

---

### Task 10.2: Student Data Export (PDPA Compliance)

**ความสำคัญ:** 🟠 สูง

**สิ่งที่ต้องทำ:**

- สร้างระบบดาวน์โหลดข้อมูลส่วนตัวนักเรียนทั้งหมด (สิทธิ์ PDPA):
  - ข้อมูลโปรไฟล์
  - ผลงานทั้งหมด (portfolio items + ไฟล์แนบ)
  - โครงงานที่เข้าร่วม
  - Logbooks
  - Goals
  - Assessments (RIASEC)
  - Badges & EXP
  - Academic records
- Export เป็น ZIP file พร้อม JSON + CSV + PDF summary

**ไฟล์ที่ต้องสร้าง:**

- `app/controllers/DataExportController.php`
- `app/services/DataExportService.php`
- `views/profile/export_data.php`
- เพิ่มปุ่ม "ดาวน์โหลดข้อมูลของฉัน" ในหน้า Profile

**เกณฑ์สำเร็จ:**

- ✅ นักเรียนดาวน์โหลดข้อมูลทั้งหมดได้
- ✅ ไฟล์ครบถ้วนตาม PDPA requirement
- ✅ มี audit log ว่าใคร export เมื่อไหร่

---

### Task 10.2.5: Database Audit Log System (Activity Logging)

**ความสำคัญ:** 🟠 สูง (เพิ่มจากข้อเสนอแนะ Senior Dev)

**สิ่งที่ต้องทำ:**

- สร้างระบบเก็บ Activity Log พื้นฐานสำหรับ Data Integrity:
  - ใครทำอะไร (who did what)
  - ทำเมื่อไหร่ (timestamp)
  - ทำที่ไหน (IP address, user agent)
  - ผลลัพธ์เป็นอย่างไร (before/after values สำหรับข้อมูลสำคัญ)
- ครอบคลุม actions สำคัญ:
  - 🔴 **Critical:** `portfolio_deleted`, `project_deleted`, `grade_modified`, `user_role_changed`
  - 🟠 **High:** `portfolio_verified`, `project_approved`, `grade_changed`, `exp_awarded`
  - 🟡 **Medium:** `portfolio_created`, `logbook_added`, `comment_added`, `badge_earned`

**Database Schema:**

```sql
CREATE TABLE audit_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50), -- 'portfolio', 'project', 'grade', etc.
    entity_id INT,
    before_values JSON, -- สถานะก่อนเปลี่ยน (สำหรับ critical actions)
    after_values JSON, -- สถานะหลังเปลี่ยน
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_action (user_id, action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Implementation Approach:**

```php
// สร้าง Audit Trait สำหรับ models ที่ต้องการ logging
trait Auditable {
    public static function bootAuditable() {
        // Before update: เก็บ before_values
        static::updating(function($model) {
            $model->auditBefore();
        });

        // After update: เก็บ after_values
        static::updated(function($model) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => $model->getTable() . '_updated',
                'entity_type' => $model->getTable(),
                'entity_id' => $model->id,
                'before_values' => $model->getBeforeValues(),
                'after_values' => $model->getAfterValues(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }
}

// Usage ใน Models
class PortfolioItem extends Model {
    use Auditable;
}
```

**Admin Dashboard สำหรับ Audit Logs:**

- ดู logs ตาม user, action type, date range
- Filter by entity (portfolio #123, project #456)
- Export logs เป็น CSV
- Alert เมื่อมี suspicious activity (ลบข้อมูลจำนวนมาก, แก้ไขเกรด)

**ไฟล์ที่ต้องสร้าง:**

- `database/migrations/024_audit_logs.sql` — Migration สำหรับ audit_logs table
- `core/Traits/Auditable.php` — Trait สำหรับ auto-logging
- `app/models/AuditLog.php` — Audit Log model
- `app/controllers/Admin/AuditLogController.php` — Admin ดู logs
- `views/admin/audit_logs/index.php` — หน้าแสดง logs
- แก้ไข models สำคัญให้ใช้ `Auditable` trait: `PortfolioItem`, `Project`, `Grade`, `User`

**เกณฑ์สำเร็จ:**

- ✅ ทุก critical action ถูกบันทึกใน audit_logs
- ✅ มี before/after values สำหรับข้อมูลสำคัญ (grades, EXP, verification status)
- ✅ Admin ดูและค้นหา audit logs ได้
- ✅ สามารถ traceได้ว่า "ใคร แก้ อะไร เมื่อไหร่"
- ✅ มี alert เมื่อมี suspicious activity
- ✅ Audit logs ไม่สามารถแก้ไขหรือลบได้ (immutable)

---

### Task 10.3: Teacher Quick Comment Templates

**ความสำคัญ:** 🟡 กลาง

**สิ่งที่ต้องทำ:**

- สร้างระบบ comment template สำหรับครู:
  - สร้าง template ล่วงหน้า (เช่น "ผลงานดีมาก", "ควรเพิ่มรายละเอียด", "ขาดไฟล์แนบ")
  - เลือก template + แก้ไขเพิ่มเติมได้
  - Template แบ่งตามหมวดหมู่ (คุณภาพเนื้อหา, การนำเสนอ, ความคิดสร้างสรรค์)
- สร้าง `teacher_comment_templates` table:
  ```sql
  CREATE TABLE teacher_comment_templates (
      id INT AUTO_INCREMENT PRIMARY KEY,
      teacher_id INT,
      category VARCHAR(100),
      template_text TEXT,
      usage_count INT DEFAULT 0,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
  ```

**ไฟล์ที่ต้องสร้าง/แก้ไข:**

- `app/controllers/TemplateController.php` — จัดการ template
- แก้ไข `views/teacher/pending_reviews.php` — เพิ่ม template picker
- `views/teacher/templates.php` — หน้าจัดการ template
- `database/migrations/018_comment_templates.sql`

**เกณฑ์สำเร็จ:**

- ✅ ครูสร้าง/แก้ไข/ลบ template ได้
- ✅ เลือก template ได้ตอนตรวจงาน
- ✅ Template ที่ใช้บ่อยแสดงก่อน
- ✅ Admin ดูสถิติการใช้ template ได้

---

### Task 10.4: Parent Notification Preferences

**ความสำคัญ:** 🟡 กลาง

**สิ่งที่ต้องทำ:**

- สร้างหน้าตั้งค่าการแจ้งเตือนสำหรับผู้ปกครอง (parent role):
  - เลือกประเภทที่ต้องการรับแจ้งเตือน:
    - ✅ เมื่อลูกส่งผลงานใหม่
    - ✅ เมื่อลูกได้รับเหรียญตรา
    - ✅ เมื่อลูกมีความเสี่ยง (Early Warning)
    - ✅ เมื่อโครงงานผ่านอนุมัติ
    - ✅ เมื่อได้ระดับ/EXP ใหม่
  - เลือกช่องทาง (email, in-app, line notify — อนาคต)
- สร้าง `notification_preferences` table:
  ```sql
  CREATE TABLE notification_preferences (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT,
      notify_portfolio_new BOOLEAN DEFAULT true,
      notify_badge_earned BOOLEAN DEFAULT true,
      notify_early_warning BOOLEAN DEFAULT true,
      notify_project_approved BOOLEAN DEFAULT true,
      notify_level_up BOOLEAN DEFAULT true,
      notify_email BOOLEAN DEFAULT false,
      notify_in_app BOOLEAN DEFAULT true,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  );
  ```

**ไฟล์ที่ต้องสร้าง/แก้ไข:**

- `app/controllers/NotificationPrefsController.php`
- `views/notifications/preferences.php`
- แก้ไข `core/Notifier.php` — เช็ค preference ก่อนส่ง
- `database/migrations/019_notification_preferences.sql`

**เกณฑ์สำเร็จ:**

- ✅ ผู้ปกครองตั้งค่าการแจ้งเตือนได้
- ✅ Notifier เช็ค preference ก่อนส่ง
- ✅ Default value = เปิดทั้งหมด

---

### Task 10.5: Alumni Tracking UI

**ความสำคัญ:** 🟢 ต่ำ (มี table แล้ว ยังไม่มี UI)

**สิ่งที่ต้องทำ:**

- สร้างหน้าแสดงผลข้อมูลศิษย์เก่า:
  - Alumni profile (ข้อมูลมหาวิทยาลัย, สาขา, ผลงานหลังจบ)
  - Alumni achievements (รางวัล, ประสบความสำเร็จ)
  - Alumni network (เชื่อมโยงกับรุ่นน้อง)
- มี `alumni_records` table อยู่แล้ว — ต้องสร้าง UI

**ไฟล์ที่ต้องสร้าง:**

- `app/controllers/AlumniController.php`
- `views/alumni/index.php` — รายชื่อศิษย์เก่า
- `views/alumni/profile.php` — โปรไฟล์ศิษย์เก่า
- `views/alumni/register.php` — ลงทะเบียนศิษย์เก่า
- `database/migrations/020_alumni_ui_enhancements.sql` (ถ้าต้องปรับ table)

**เกณฑ์สำเร็จ:**

- ✅ ศิษย์เก่าลงทะเบียนและอัพเดทข้อมูลได้
- ✅ Admin ดูสถิติศิษย์เก่าได้
- ✅ เชื่อมโยงกับรุ่นน้องผ่านระบบ mentorship ได้

---

## 🌟 Phase 11: ฟีเจอร์ขั้นสูง (Advanced Features)

**ระยะเวลา:** 4-6 สัปดาห์
**เป้าหมาย:** เพิ่มฟีเจอร์ขั้นสูงที่ทำให้ระบบเป็นเลิศ

### Task 11.1: Real-time Notifications (Server-Sent Events)

**ความสำคัญ:** 🟢 ต่ำ

**สิ่งที่ต้องทำ:**

- ใช้ Server-Sent Events (SSE) สำหรับแจ้งเตือนแบบ real-time:
  - สร้าง endpoint `api/notifications/stream` — SSE endpoint
  - Frontend เปิด connection ค้างไว้
  - ส่ง event ใหม่ทันทีที่มี notification ใหม่
- Fallback: Polling ทุก 30 วินาที (สำหรับ browser ไม่รองรับ SSE)
- สร้าง `sse_notifications.php` ใน `public/`

**ไฟล์ที่ต้องสร้าง:**

- `public/sse/notifications.php` — SSE endpoint
- เพิ่ม JavaScript ใน `views/layouts/main.php` — SSE client
- `core/SSE.php` — SSE helper class

**เกณฑ์สำเร็จ:**

- ✅ นักเรียนได้รับ notification ทันทีโดยไม่ต้อง refresh
- ✅ Fallback polling ทำงานเมื่อ SSE ไม่รองรับ
- ✅ ไม่กระทบ performance ของ server

---

### Task 11.2: Peer Review System

**ความสำคัญ:** 🟢 ต่ำ

**สิ่งที่ต้องทำ:**

- สร้างระบบให้นักเรียนรีวิวผลงานกันเอง:
  - นักเรียนส่งผลงานเพื่อ peer review
  - ระบบสุ่ม assign ผู้รีวิว (3 คน/ผลงาน)
  - ผู้รีวิวให้คะแนนตาม rubric
  - สรุปคะแนน peer review ให้เจ้าของผลงาน
- สร้าง `peer_reviews` table:
  ```sql
  CREATE TABLE peer_reviews (
      id INT AUTO_INCREMENT PRIMARY KEY,
      portfolio_id INT,
      reviewer_id INT,
      rubric_scores JSON,
      comments TEXT,
      status ENUM('pending', 'completed', 'skipped') DEFAULT 'pending',
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      completed_at TIMESTAMP NULL
  );
  ```

**ไฟล์ที่ต้องสร้าง:**

- `app/controllers/PeerReviewController.php`
- `app/services/PeerReviewService.php`
- `views/peer-review/assign.php` — ดูผลงานที่ต้องรีวิว
- `views/peer-review/review.php` — ฟอร์มรีวิว
- `views/peer-review/results.php` — ผลรีวิวที่ได้รับ
- `database/migrations/021_peer_review.sql`

**เกณฑ์สำเร็จ:**

- ✅ นักเรียนได้รับมอบหมายรีวิวผลงานผู้อื่น
- ✅ ให้คะแนนตาม rubric ได้
- ✅ เจ้าของผลงานเห็น feedback จากเพื่อน
- ✅ Admin ดูสถิติ peer review ได้

---

### Task 11.3: Portfolio Template Marketplace

**ความสำคัญ:** 🟢 ต่ำ

**สิ่งที่ต้องทำ:**

- สร้างระบบแชร์ template portfolio ระหว่างนักเรียน:
  - นักเรียนสร้าง template จากผลงานของตัวเอง
  - แชร์เป็น template ให้คนอื่นใช้
  - นักเรียนคนอื่นนำไปใช้เป็นจุดเริ่มต้น
  - ระบบ vote/template rating
- สร้าง `portfolio_templates` table:
  ```sql
  CREATE TABLE portfolio_templates (
      id INT AUTO_INCREMENT PRIMARY KEY,
      creator_id INT,
      name VARCHAR(200),
      description TEXT,
      config JSON, -- category layout, theme, featured items
      preview_image VARCHAR(500),
      rating DECIMAL(3,2),
      usage_count INT DEFAULT 0,
      is_public BOOLEAN DEFAULT false,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
  ```

**ไฟล์ที่ต้องสร้าง:**

- `app/controllers/TemplateMarketController.php`
- `views/templates/marketplace.php` — หน้าตลาด template
- `views/templates/create.php` — สร้าง template
- `database/migrations/022_portfolio_templates.sql`

**เกณฑ์สำเร็จ:**

- ✅ นักเรียนสร้างและแชร์ template ได้
- ✅ นักเรียนคนอื่นนำไปใช้ได้
- ✅ มีระบบ rating และ vote
- ✅ แสดง template ยอดฮิต

---

### Task 11.4: Automated Milestone Checking (Cron Integration)

**ความสำคัญ:** 🟢 ต่ำ

**สิ่งที่ต้องทำ:**

- สร้างระบบตรวจสอบ milestone อัตโนมัติ:
  - Cron job เรียก `/cron/milestone-check?token=CRON_TOKEN` ทุกวัน
  - ตรวจสอบ milestone ที่ใกล้ครบกำหนด (7 วัน, 3 วัน, 1 วัน)
  - ส่ง notification ให้นักเรียนและครูที่ปรึกษา
  - อัพเดท status milestone ที่เกินกำหนดเป็น 'overdue'
- แก้ไข `AnalyticsController@cronMilestones` ให้ทำงานสมบูรณ์

**ไฟล์ที่ต้องแก้ไข:**

- แก้ไข `app/controllers/AnalyticsController.php@cronMilestones`
- เพิ่ม cron setup ใน `.env.production`:
  ```
  0 8 * * * curl http://localhost/app/cron/milestone-check?token=TOKEN
  ```

**เกณฑ์สำเร็จ:**

- ✅ Cron job ตรวจสอบ milestone ทุกวัน
- ✅ Notification ส่งถึงนักเรียนและครู
- ✅ Admin ดูรายงาน milestone status ได้

---

### Task 11.5: TCAS Prediction Model Refinement

**ความสำคัญ:** 🟢 ต่ำ

**สิ่งที่ต้องทำ:**

- ปรับปรุงความแม่นยำของ TCAS prediction:
  - เพิ่ม historical accuracy tracking — เปรียบเทียบ prediction vs ผลจริง
  - ปรับ weighting formula ตามข้อมูลจริง
  - เพิ่ม faculty-specific predictions (ไม่ใช่แค่ TCAS รอบทั่วไป)
  - เพิ่ม trend analysis — ความพร้อมเปลี่ยนไปอย่างไร
- สร้าง `tcas_prediction_history` table:
  ```sql
  CREATE TABLE tcas_prediction_history (
      id INT AUTO_INCREMENT PRIMARY KEY,
      student_id INT,
      prediction_data JSON,
      predicted_faculty VARCHAR(200),
      predicted_round VARCHAR(50),
      actual_result VARCHAR(100), -- เก็บผลจริงหลังสอบ
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
  ```

**ไฟล์ที่ต้องสร้าง/แก้ไข:**

- แก้ไข `app/services/ReportService.php@calculateTCASReadiness()`
- `database/migrations/023_tcas_prediction_history.sql`

**เกณฑ์สำเร็จ:**

- ✅ Prediction มีความแม่นยำขึ้น
- ✅ มี historical data เปรียบเทียบ
- ✅ แสดง trend graph ในหน้า TCAS readiness

---

## 🛡️ Phase 12: เตรียมพร้อม Production (Production Hardening)

**ระยะเวลา:** 2-3 สัปดาห์
**เป้าหมาย:** ทำให้ระบบพร้อมใช้งานจริง มีความปลอดภัยและเสถียร

### ⚠️ SENIOR DEV NOTE: Migration Convention for Multi-Agent Development

> เมื่อ AI Agents หลายตัวทำงานพร้อมกัน อาจเกิดกรณี:
>
> - **Migration ข้อนกัน:** Agents สร้าง migration files ที่ใช้ชื่อเดียวกันหรือรันลำดับผิด
> - **Dependency conflicts:** Migration A ต้องรันก่อน B แต่ถูกสร้างทีหลัง
>
> **Convention ที่ต้องปฏิบัติตาม:**
>
> 1. **Naming Convention:**
>
>    ```
>    {sequence_number}_{descriptive_name}.sql
>    ตัวอย่าง: 025_add_peer_review_indexes.sql
>    ```
>
>    - Sequence numbers ต้องเรียงตามลำดับ (ตรวจสอบใน `database/migrations/` ก่อนสร้าง)
>    - ห้ามใช้ชื่อซ้ำกับ migration ที่มีอยู่
>
> 2. **Rollback Requirement:**
>
>    ```sql
>    -- UP migration
>    ALTER TABLE users ADD COLUMN new_column VARCHAR(100);
>
>    -- DOWN migration (ต้อง มี!)
>    -- ROLLBACK: ALTER TABLE users DROP COLUMN new_column;
>    ```
>
>    - ทุก migration ต้องมี DOWN script ใน comments
>    - ต้องสามารถ rollback ได้โดยไม่เสียข้อมูล
>
> 3. **Pre-Flight Check:**
>    ```bash
>    # ก่อนสร้าง migration ใหม่ ต้องตรวจสอบ:
>    ls database/migrations/ | sort
>    # ดูว่าลำดับล่าสุดคืออะไร และตั้งชื่อให้ไม่ซ้ำ
>    ```
> 4. **Agent Coordination:**
>    - Agents ต้องแจ้งใน task assignment ว่า "กำลังสร้าง migration #{number}"
>    - Agents อื่นต้องรอให้ migration นั้้นเสร็จก่อนสร้างใหม่
>    - ใช้ task management tool (เช่น file `MIGRATION_LOCK`) เพื่อป้องกัน conflicts

---

### Task 12.1: Expand Test Suite (Integration & E2E Testing)

**ความสำคัญ:** 🟡 กลาง

**สิ่งที่ต้องทำ:**

- **หมายเหตุ:** Unit Tests สำหรับ Core Services (EXPService, BadgeService) สร้างไว้ใน Task 8.0 แล้ว
- ขยาย test suite ให้ครอบคลุมมากขึ้น:
  - **Integration tests** สำหรับ Controllers (auth, portfolio, projects)
  - **Database tests** สำหรับ migrations และ queries
  - **Security tests** (CSRF, SQL injection, XSS)
  - **E2E tests** สำหรับ user flows สำคัญ (login → create portfolio → earn badge)

**โฟลเดอร์ที่ต้องสร้าง/เพิ่มเติม:**

```
tests/
├── Unit/                    ✅ สร้างใน Task 8.0
│   ├── BadgeServiceTest.php ✅
│   ├── EXPServiceTest.php   ✅
│   └── NotifierTest.php     ✅
├── Integration/             🔧 สร้างใน Task 12.1
│   ├── AuthTest.php
│   ├── PortfolioControllerTest.php
│   ├── ProjectControllerTest.php
│   └── TeacherReviewTest.php
├── Database/                🔧 สร้างใน Task 12.1
│   ├── MigrationTest.php
│   ├── DatabaseTest.php
│   └── AuditLogTest.php
└── E2E/                     🔧 สร้างใน Task 12.1
    ├── StudentFlowTest.php
    └── TeacherFlowTest.php
```

**เกณฑ์สำเร็จ:**

- ✅ Test coverage > 60% (รวม Unit + Integration + E2E)
- ✅ ทุก test ผ่าน (`phpunit`)
- ✅ CI runs tests automatically
- ✅ มี test coverage report (HTML)

---

### Task 12.2: Security Audit (Penetration Testing)

**ความสำคัญ:** 🟠 สูง

**สิ่งที่ต้องทำ:**

- ตรวจสอบช่องโหว่ความปลอดภัย:
  - **XSS (Cross-Site Scripting)** — ตรวจสอบทุก view ที่แสดงข้อมูลผู้ใช้
  - **SQL Injection** — ตรวจสอบทุก query (ควรใช้ prepared statements ทั้งหมด)
  - **CSRF** — ตรวจสอบว่าทุก POST request มี CSRF token
  - **File Upload Vulnerabilities** — ตรวจสอบ validation ชนิดไฟล์
  - **Session Hijacking** — ตรวจสอบ session security
  - **Privilege Escalation** — ตรวจสอบ middleware ทุก route
- สร้าง `scripts/security_audit.php` — automated security scanner
- แก้ไขจุดที่พบ

**เกณฑ์สำเร็จ:**

- ✅ ไม่พบ XSS vulnerability
- ✅ ไม่พบ SQL injection
- ✅ ทุก POST request ป้องกัน CSRF
- ✅ File upload ปลอดภัย
- ✅ Middleware ครอบคลุมทุก route

---

### Task 12.3: Error Monitoring & Logging Enhancement

**ความสำคัญ:** 🟠 สูง

**สิ่งที่ต้องทำ:**

- ปรับปรุงระบบ logging:
  - แยก log ตามประเภท (error, warning, info, debug)
  - Log rotation — ลบ/ย้าย log เก่า (ทุก 30 วัน)
  - Structured logging — JSON format สำหรับวิเคราะห์ง่าย
  - เพิ่ม error context (user ID, URL, request data)
- พิจารณาใช้ Sentry หรือบริการ error monitoring (ถ้ามีงบประมาณ)
- สร้าง `logs/` directory structure:
  ```
  logs/
  ├── error.log
  ├── warning.log
  ├── info.log
  ├── debug.log
  └── archive/  — log เก่า
  ```

**ไฟล์ที่ต้องแก้ไข:**

- แก้ไข `core/App.php@log()` — structured logging
- สร้าง `scripts/log_rotate.php` — log rotation cron
- สร้าง `core/LogFormatter.php` — log analysis tool

**เกณฑ์สำเร็จ:**

- ✅ Log แยกตามประเภท
- ✅ Log auto-rotate ทุก 30 วัน
- ✅ Admin ดูและวิเคราะห์ log ได้
- ✅ Error มี context ครบ

---

### Task 12.4: Backup Automation

**ความสำคัญ:** 🟠 สูง

**สิ่งที่ต้องทำ:**

- สร้างระบบ backup อัตโนมัติ:
  - Backup database ทุกวัน (cron job)
  - Backup uploads directory ทุกสัปดาห์
  - เก็บ backup ย้อนหลัง 30 วัน
  - Backup file มี encryption (optional)
- สร้าง `scripts/backup.php` — CLI backup script
- สร้าง `scripts/restore.php` — CLI restore script
- เพิ่ม cron schedule:
  ```
  0 2 * * * php /path/to/app/scripts/backup.php
  ```

**ไฟล์ที่ต้องสร้าง:**

- `scripts/backup.php` — database + file backup
- `scripts/restore.php` — restore from backup
- `backup/` directory — เก็บ backup files

**เกณฑ์สำเร็จ:**

- ✅ Backup database ทุกวันอัตโนมัติ
- ✅ Restore ได้เมื่อต้องการ
- ✅ Backup ย้อนหลัง 30 วัน
- ✅ Admin ดูสถานะ backup ได้

---

### Task 12.5: CI/CD Pipeline

**ความสำคัญ:** 🟡 กลาง

**สิ่งที่ต้องทำ:**

- สร้าง CI/CD pipeline:
  - GitHub Actions หรือ GitLab CI
  - Auto-run tests เมื่อ push code
  - Auto-deploy เมื่อ merge ไป main branch
  - Rollback ได้ถ้า deploy แล้วมีปัญหา
- สร้าง `.github/workflows/deploy.yml` หรือ `.gitlab-ci.yml`

**Pipeline stages:**

1. **Lint** — PHP syntax check
2. **Test** — PHPUnit tests
3. **Build** — สร้าง deployment package
4. **Deploy** — อัพโหลดไปยัง server
5. **Health Check** — ตรวจสอบว่าระบบทำงานหลัง deploy

**เกณฑ์สำเร็จ:**

- ✅ Push code → tests run อัตโนมัติ
- ✅ Merge to main → deploy อัตโนมัติ
- ✅ Rollback ได้ภายใน 5 นาที
- ✅ มี deployment log

---

### Task 12.6: Production Environment Setup

**ความสำคัญ:** 🟠 สูง

**สิ่งที่ต้องทำ:**

- เตรียม `.env.production` สำหรับ production:
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `APP_MAINTENANCE=false`
  - Database credentials สำหรับ production server
  - SMTP credentials สำหรับ email notification
  - Google SSO credentials สำหรับ production
  - Gemini API key (ถ้าใช้)
- สร้าง deployment checklist:
  - [ ] Migration ran
  - [ ] Fonts installed
  - [ ] Dependencies installed (`composer install --no-dev`)
  - [ ] Upload directory writable
  - [ ] Log directory writable
  - [ ] HTTPS enabled
  - [ ] Backup configured
  - [ ] Cron jobs configured
- สร้าง `scripts/deploy.sh` หรือ `build_deploy.ps1` (ปรับให้สมบูรณ์)

**เกณฑ์สำเร็จ:**

- ✅ Deploy ไป production ได้ไม่มีปัญหา
- ✅ ระบบทำงานถูกต้องใน production
- ✅ มี deployment checklist
- ✅ มี rollback plan

---

## 🚀 Phase 13: อนาคต (Future Enhancements)

**ระยะเวลา:** ไม่กำหนด
**เป้าหมาย:** ฟีเจอร์และทิศทางในอนาคต เมื่อระบบหลักมั่นคงแล้ว

### Task 13.1: Mobile App Readiness

- สร้าง RESTful API ที่สมบูรณ์สำหรับ mobile app
- API documentation (Swagger/OpenAPI)
- Authentication via API tokens (JWT)
- Versioned API endpoints (`/api/v1/...`)

### Task 13.2: AI/ML Enhancements

- Gemini API integration เต็มรูปแบบ (ตอนนี้ rule-based fallback)
- Personalized recommendations จาก AI
- Auto-categorize portfolio items ด้วย AI
- Predict student dropout risk ด้วย ML model

### Task 13.3: Multi-School Support

- รองรับหลายโรงเรียนในระบบเดียว
- School-level admin
- Inter-school portfolio showcase
- Cross-school analytics

### Task 13.4: Blockchain Credentialing

- เก็บ portfolio hashes บน blockchain
- Verifiable credentials สำหรับ TCAS
- Anti-tampering protection

### Task 13.5: Advanced Analytics & BI

- Business Intelligence dashboard
- Predictive analytics
- Custom report builder
- Data warehouse integration

---

## 📋 สรุปแผนการพัฒนา

| เฟส          | ชื่อ              | ระยะเวลา    | ความสำคัญ | งานหลัก                                                                                  |
| ------------ | ----------------- | ----------- | --------- | ---------------------------------------------------------------------------------------- |
| **Phase 8**  | ระบบมั่นคง        | 1-2 สัปดาห์ | 🔴 สูงสุด | **Task 8.0:** Unit Tests (Shift-Left), แก้ EXP, Badge, PDF, Migration, Notification      |
| **Phase 9**  | เพิ่มประสิทธิภาพ  | 2-3 สัปดาห์ | 🟠 สูง    | Cache **(พร้อม Invalidation Strategy)**, N+1 fix, Rate limit, Indexes                    |
| **Phase 10** | เพิ่มฟีเจอร์ใหม่  | 3-4 สัปดาห์ | 🟠 สูง    | Points shop **(MVP first)**, PDPA export, **Audit Log**, Templates, Parent prefs, Alumni |
| **Phase 11** | ฟีเจอร์ขั้นสูง    | 4-6 สัปดาห์ | 🟡 กลาง   | Real-time notify, Peer review, Template market, Cron, TCAS                               |
| **Phase 12** | เตรียม Production | 2-3 สัปดาห์ | 🟠 สูง    | **Expand Tests (Integration+E2E)**, Security audit, Logging, Backup, CI/CD               |
| **Phase 13** | อนาคต             | ไม่กำหนด    | 🟢 ต่ำ    | Mobile API, AI/ML, Multi-school, Blockchain                                              |

**รวมระยะเวลาโดยประมาณ:** 12-18 สัปดาห์ (3-4 เดือน) สำหรับ Phase 8-12

---

## 🎯 ลำดับความสำคัญในการดำเนินงาน

### รอบที่ 0 (สัปดาห์ 1): Phase 8 Task 8.0 — Shift-Left Testing ⚠️ **GATE**

- ✅ **Task 8.0:** ติดตั้ง PHPUnit, สร้าง Unit Tests สำหรับ EXPService, BadgeService, Notifier
- ⚠️ **GATE:** Task 8.1-8.6 เริ่มไม่ได้จนกว่า Task 8.0 จะเสร็จและ tests ผ่าน 100%

### รอบที่ 1 (สัปดาห์ 1-2): Phase 8 — ระบบมั่นคง

- ✅ Task 8.1: แก้ EXP integration
- ✅ Task 8.2: แก้ Badge event triggers
- ✅ Task 8.3: แก้ชื่อโรงเรียนใน PDF
- ✅ Task 8.4: รัน Migration 015

### รอบที่ 2 (สัปดาห์ 3-4): Phase 8 ต่อ + Phase 9 เริ่มต้น

- ✅ Task 8.5: เชื่อมต่อ Notification
- ✅ Task 8.6: System health check
- ✅ Task 9.1: Query caching **(พร้อม Event-Driven Invalidation)**
- ✅ Task 9.2: แก้ N+1 queries

### รอบที่ 3 (สัปดาห์ 5-8): Phase 9 ต่อ + Phase 10

- ✅ Task 9.3-9.5: Rate limit, Indexes, Fonts
- ✅ Task 10.1: Points shop **(MVP: 3-5 items)**
- ✅ Task 10.2: PDPA data export
- ✅ **Task 10.2.5: Audit Log System** (เพิ่มจากข้อเสนอแนะ)

### รอบที่ 4 (สัปดาห์ 9-12): Phase 10 ต่อ + Phase 11

- ✅ Task 10.3-10.5: Teacher templates, Parent prefs, Alumni
- ✅ Task 11.1-11.2: Real-time notify, Peer review

### รอบที่ 5 (สัปดาห์ 13-16): Phase 11 ต่อ + Phase 12

- ✅ Task 11.3-11.5: Template market, Cron, TCAS
- ✅ Task 12.1: Expand Test Suite (Integration + E2E)
- ✅ Task 12.2-12.6: Security audit, Logging, Backup, CI/CD

---

## ✅ เกณฑ์วัดความสำเร็จโดยรวม

| ตัวชี้วัด                 | ค่าเป้าหมาย               | วิธีวัด                         |
| ------------------------- | ------------------------- | ------------------------------- |
| **EXP ถูกมอบถูกต้อง**     | 100% ของ actions          | ตรวจสอบทุก controller           |
| **Badge ถูกมอบอัตโนมัติ** | 100% ของ events           | ตรวจสอบ BadgeService::onEvent() |
| **Dashboard โหลดเวลา**    | < 2 วินาที                | วัด page load time              |
| **Test Coverage**         | > 60%                     | PHPUnit --coverage              |
| **Security Audit**        | 0 critical/high issues    | Penetration testing             |
| **Backup Success Rate**   | 100% ทุกวัน               | ตรวจสอบ backup log              |
| **Audit Log Coverage**    | 100% ของ critical actions | ตรวจสอบ audit_logs table        |
| **Cache Hit Rate**        | > 80%                     | Cache hits/misses log           |
| **User Satisfaction**     | > 80%                     | สำรวจความพึงพอใจ                |

---

## 📞 การบริหารจัดการโครงการ

### ทีมพัฒนา (AI Agents)

- **Lead Architect (Qwen Code)** — ออกแบบระบบ, เขียนโค้ดหลัก, ตรวจสอบคุณภาพ
- **Development Agents (Claude, Qwen Extensions)** — พัฒนาฟีเจอร์ตาม task ที่มอบหมาย
- **Testing Agent** — สร้างและรัน test suite
- **Documentation Agent** — อัพเดทเอกสาร

### การติดตามความคืบหน้า

- อัพเดท status ทุก task ในเอกสารนี้
- รายงานความคืบหน้ารายสัปดาห์
- ทบทวนและปรับแผนตามความเหมาะสม

### การส่งมอบงาน

- ทุก task ต้องมีเกณฑ์สำเร็จชัดเจน
- Code review ก่อน merge
- Test ผ่าน 100%
- อัพเดทเอกสารที่เกี่ยวข้อง

---

_"ระบบนี้ถูกออกแบบมาเพื่อให้นักเรียนเก่งขึ้น ครูสะดวกขึ้น และผู้บริหารเห็นภาพชัดเจนขึ้น"_

**© 2026 PCSHSKSN — งานศูนย์ข้อมูลสารสนเทศ | กลุ่มบริหารงานวิชาการ**
**โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์**
