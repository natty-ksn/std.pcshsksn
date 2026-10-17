# 🤖 มาตรฐานพรอมต์สำหรับ Qwen AI (Qwen Master Prompts)

**Role:** Senior AI Developer (Qwen)
**Auditor:** Senior System Analyst (Gemini)
**โปรเจค:** PCSHS KSN e-Portfolio

เอกสารนี้รวบรวม Prompt มาตรฐานที่ใช้สั่งงาน Qwen AI เพื่อให้เขียนโค้ดได้อย่างมีประสิทธิภาพ ตรงตามมาตรฐาน Enterprise-grade 

---

## 📋 โครงสร้างมาตรฐานของ Prompt สำหรับสั่ง Qwen
ทุกครั้งที่สั่งงาน Qwen ควรจะมีองค์ประกอบเหล่านี้ใน Prompt:
1. **Context & Role:** กำหนดบทบาทและเป้าหมายของบริบทนั้นๆ
2. **Task Definition:** ระบุงานที่ต้องการชัดเจนแบบ Step-by-step
3. **Files to modify:** ระบุไฟล์ที่เกี่ยวข้อง
4. **Constraints & Rules:** ข้อจำกัด (เช่น ห้ามใช้ Framework นอก, ห้ามแก้ Core เดิมโดยไม่จำเป็น)
5. **Testing Requirements:** สิ่งที่ต้องทดสอบว่าโค้ดนั้นทำงานถูกต้อง

---

## 🎯 พรอมต์เริ่มต้นที่ 1: การแก้ปัญหา N+1 Query (ตัวอย่างสำหรับ Phase 2.1)
*คัดลอกข้อความด้านล่างนี้ให้ Qwen ทำงาน*

```text
[Role]
You are an Expert PHP Developer specializing in Database Optimization and Custom MVC architecture.

[Context]
We are optimizing the PCSHS KSN e-Portfolio system. Currently, we face an "N+1 Query" bottleneck in the application controllers (specifically DashboardController and ProjectController). We need to refactor loop queries into single, efficient JOIN SQL statements (Eager Loading).

[Task]
1. Analyze `app/controllers/DashboardController.php` and `app/controllers/ProjectController.php`.
2. Locate loops (foreach/for) that execute `Database::fetch()` or `Database::fetchAll()`.
3. Refactor these loops. Group the primary keys, perform a single `IN()` or `JOIN` query to fetch related data.
4. Map the fetched relation data back to the primary objects in PHP space.

[Rules & Constraints]
- Using pure PHP 8.0+ syntax.
- Framework: Custom MVC-lite (Use strictly `Database::fetchAll()` / `Database::execute()`).
- DO NOT use any ORM like Eloquent. Write raw, native SQL.
- Must ensure queries execute under 50ms.
- DO NOT change the UI/view variables structure. The `$data` passed to `$this->view()` must remain strictly identical.

[Output]
Please provide the refactored controller code and explicit instructions on any new Database Indexes needed for your optimized queries.
```
