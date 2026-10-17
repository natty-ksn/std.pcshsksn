# PCSHSKSN e-Portfolio Design System & UI Guide

เอกสารคู่มือมาตรฐานการออกแบบ (Design System Specification) สำหรับระบบแฟ้มสะสมผลงานอัจฉริยะ โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์

---

## 1. Brand & Stakeholder Color Tokens

| Role / Feature | CSS Variable | Hex Code | คำอธิบาย / การใช้งาน |
| :--- | :--- | :--- | :--- |
| **Primary (Brand)** | `--primary-600` | `#4f46e5` | สีน้ำเงินคราม อัตลักษณ์ระบบ e-Portfolio |
| **Accent / Success** | `--accent-500` | `#10b981` | สีเขียวมรกต ความสำเร็จ และผลงานผ่านการรับรอง |
| **Student (นักเรียน)** | `--student-color` | `#6366f1` | สีธีมหลักสำหรับนักเรียน และ Badge ทั่วไป |
| **Teacher (ครู)** | `--teacher-color` | `#059669` | สีธีมสำหรับครูที่ปรึกษา และระบบ Mentorship |
| **Admin (ผู้ดูแลระบบ)** | `--admin-color` | `#dc2626` | สีธีมสำหรับเมนูผู้ดูแลระบบและการจัดการระบบ |
| **Executive (ผู้บริหาร)** | `--exec-color` | `#7c3aed` | สีธีมสำหรับฝ่ายบริหาร แดชบอร์ดภาพรวม และ EWS |
| **Dark Sidebar** | `--bg-sidebar` | `#0f172a` | สีกรมท่าลึก (Slate 900) สำหรับ Sidebar Desktop/Mobile |

### Secondary & Status Colors
- **Warning / Alert:** `#f59e0b` (Amber 500)
- **Danger / Error:** `#ef4444` (Red 500)
- **Surface / Cards:** `#ffffff` (Card Light) / `rgba(255, 255, 255, 0.05)` (Glassmorphism Dark)
- **Border Neutral:** `#e2e8f0` (Slate 200)

---

## 2. Typography & Font Hierarchy

- **Global Font Family:** `'Sarabun', 'Noto Sans Thai', 'Inter', -apple-system, sans-serif`
- **Root Font Size:** `html { font-size: 100%; }` (16px base, รองรับ User Font Scaling และ Browser Accessibility)
- **Line Height:** Body `1.6`, Headings `1.3 - 1.5` เพื่อป้องกันข้อความทับซ้อนและตัดขาดบนอุปกรณ์พกพา

| Element | Size | Weight | Line Height | Usage |
| :--- | :--- | :--- | :--- | :--- |
| **H1** | `2.25rem` (36px) | 800 | 1.3 | Page Primary Title / Dashboard Header |
| **H2** | `1.75rem` (28px) | 700 | 1.35 | Section Header / Modal Title |
| **H3** | `1.35rem` (21.6px) | 700 | 1.4 | Card Title / Subheader |
| **Body (Normal)** | `1rem` (16px) | 400 | 1.6 | Paragraphs, Descriptions |
| **Small / Meta** | `0.875rem` (14px) | 500 | 1.5 | Timestamps, Badges, Secondary details |
| **Caption** | `0.75rem` (12px) | 600 | 1.4 | Tag labels, Status pills |

---

## 3. Spacing, Elevation & Radius

### Border Radius Tokens
- Small (`--radius-sm`): `6px` (Pills, Form Inputs)
- Medium (`--radius-md`): `10px` (Buttons, Select Menus)
- Large (`--radius-lg`): `16px` (Standard Cards, Tables)
- Extra Large (`--radius-xl`): `24px` (Modal Windows, Showcase Cards)
- Full (`--radius-full`): `9999px` (Avatars, Filter Chips, Action Pills)

### Shadows & Depth
- Card Standard: `0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05)`
- Elevated / Hover: `0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.04)`
- Modal Backdrop: `background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);`

---

## 4. UI Components & States

1. **Button States:**
   - `:hover`: ยกระดับสีพื้นหลังและ Box Shadow สว่างขึ้น 10-15%
   - `:active`: ยุบตัวลงเล็กน้อย (`transform: scale(0.98)`)
   - `:disabled`: `opacity: 0.55; cursor: not-allowed; pointer-events: none;`
   - `:focus-visible`: `outline: 3px solid #6366f1; outline-offset: 2px;`

2. **Touch Targets (Mobile & Responsive):**
   - ปุ่มกด ลิงก์เมนู และไอคอนโต้ตอบทุกชิ้นมีขนาดอย่างน้อย `44x44px` (WCAG AAA Touch Target Standard)

3. **Tables & Responsive Containers:**
   - ทุกตารางบรรจุอยู่ภายใต้ `.table-responsive` พร้อม `overflow-x: auto;` ป้องกันหน้าจอแตกหรือเลื่อนแนวนอนบนสมาร์ตโฟน

4. **Modals & Drawers:**
   - รองรับการกดปุ่ม `Escape` จากคีย์บอร์ดเพื่อปิดกล่องข้อความ
   - มีปุ่ม `✕` (Close Button) พร้อม Overlay Backdrop เมื่อคลิกนอกกล่องจะปิดหน้าต่างอัตโนมัติ

---

## 5. Accessibility (WCAG 2.1 AA) Compliance

- **Contrast Ratios:** ข้อความบนพื้นหลังสีสว่างต้องมีค่า Contrast อย่างน้อย 4.5:1
  - Badges (Gold/Silver/Platinum/Bronze) ใช้สีตัวอักษรเข้ม `#0f172a` บนพื้นทอง/เงิน ป้องกันตัวอักษรขาวกลืนกับพื้นหลัง
- **Skip Links:** มีลิงก์ `ข้ามไปเนื้อหาหลัก` เป็น Focus แรกของหน้าเว็บ
- **Semantic HTML:** หน้าเว็บมีโครงสร้าง `<main id="main-content">`, `<nav>`, `<aside>`, และ `<header>` ชัดเจน
