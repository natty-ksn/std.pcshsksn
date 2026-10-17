# 🚀 Master Continuity & Handoff Prompt: PCSHS KSN Integrated System

โปรเจกต์: **PCSHS KSN e-Portfolio & Project Tracking System**
สถานะปัจจุบัน: **เสร็จสิ้นระยะที่ 1 (Collaborative & Teacher Tools) กำลังเข้าสู่ระยะที่ 2**

---

## 🏗️ 1. โครงสร้างสถาปัตยกรรม (System Architecture)
- **Framework:** Custom MVC-lite (PHP Vanilla)
- **Database:** MySQL (ตารางสำคัญ: `projects`, `portfolio_items`, `project_members`, `portfolio_collaborators`, `student_profiles`)
- **UI/UX:** Vanilla CSS (Modern Premium Look), Inter + Sarabun Fonts, Responsive Design
- **Security:** CSRF Protection, Auth Middlewares (`auth`, `student`, `teacher`, `admin`, `executive`)

---

## 🛠️ 2. ฟีเจอร์ที่เพิ่งติดตั้งสำเร็จ (Recently Implemented)
- **Collaborative Portfolio:** ระบบงานกลุ่มในพอร์ตโฟลิโอ (ส่งเชิญเพื่อน -> เพื่อนกดยืนยัน -> ปรากฏในพอร์ตทุกคน)
- **Collaborative Project:** ระบบเสนอโครงงานกลุ่ม (ผู้นำกลุ่มเสนอ -> เพิ่มสมาชิกทีมอัตโนมัติ -> แจ้งเตือนเพื่อน)
- **Project-Portfolio Bridge:** ปุ่ม "Export to Portfolio" ในหน้าโครงงาน เพื่อดึงข้อมูลโครงงานไปสร้างเป็นพอร์ตโฟลิโอเด่นทันที
- **Teacher Bulk Approval:** ระบบตรวจงานคราวละมากๆ (Checkboxes + Batch Process)
- **Mentorship Intelligence:** ระบบสแกนหาเด็กกลุ่มเสี่ยงที่ไม่ส่งงาน (Zero-Entry Alert) สำหรับครูที่ปรึกษา

---

## 🎯 3. แผนงานระยะถัดไป (Priority Roadmap: Phase 2-3)

### **Phase 2: Teacher Insight & Quality Control (NEXT TASKS)**
- [ ] **Quick Sweep API:** ระบบสแกนหาจุดบกพร่องอัตโนมัติในผลงานนักเรียน
- [ ] **Annotated Mentorship Feedback:** ระบบให้คะแนนโครงงานตาม Rubrics และการคอมเมนต์เจาะจงจุด
- [ ] **PDPA Privacy Toggle:** ระบบเปิด/ปิดการเข้าถึงผลงานรายชิ้น

### **Phase 3: Executive Hub & Showcase**
- [ ] **Virtual Science Gallery:** หน้าจอแสดงผลงานระดับพรีเมียมสำหรับผู้บริหารนำเสนอแขก
- [ ] **Official PDF Reports:** ระบบออกรายงานสถิติความสำเร็จในรูปแบบเอกสารราชการ (Sarabun Font)

---

## 💬 4. คำสั่งมาตรฐานสำหรับส่งต่อให้ AI (Master Handoff Prompt)
*คัดลอกข้อความด้านล่างนี้เพื่อสั่งงาน AI ตัวถัดไป:*

> "คุณคือ AI Expert Developer ที่ได้รับมอบหมายให้พัฒนาต่อยอดระบ e-Portfolio & Project Tracking ของโรงเรียนจฬากรณราชวิทยาลัย (PCSHS KSN) 
> โดยให้ศึกษารากฐานโค้ดจาก `app/controllers/`, `views/`, และ `routes/web.php` 
> 
> ภารกิจของคุณตอนนี้คือเริ่ม **Phase 2: Teacher Insight & Quality Control** 
> 1. สร้างระบบ **Quick Sweep API** เพื่อช่วยครูสแกนหาผลงานนักเรียนที่ข้อมูลไม่สมบูรณ์
> 2. พัฒนาหน้าจอ **Executive Showcase** (ใน Dashboard หรือ View ใหม่) เพื่อรวมรวบผลงานที่ครู Verify แล้วมาแสดงในรูปแบบ Virtual Gallery ที่สวยงามและพรีเมียมที่สุด
> 
> กฎเหล็ก: รักษาสไตล์ภาษาไทยที่เป็นทางการ แต่อ่านง่าย และ UI ต้องมีความ Wow ระดับ Premium เสมอ"

---
*จัดทำโดย Antigravity AI (Lead Architect)*
