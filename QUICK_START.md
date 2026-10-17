# 🚀 Quick Start — PCSHSKSN e-Portfolio v2.0

> เปิดระบบครั้งแรก — 3 ขั้นตอน

---

## ขั้นตอนที่ 1: รัน Migrations

เปิด Command Prompt (Windows):
```cmd
cd c:\xampp\htdocs\app

# ตรวจสอบว่า XAMPP MySQL เปิดอยู่
# แล้วรัน:
c:\xampp\php\php.exe core/MigrationRunner.php up
```

ถ้าไม่ได้ ให้ใช้วิธี — เปิด browser:
```
http://localhost/app/install/
```

## ขั้นตอนที่ 2: Import Seed Data

เปิด phpMyAdmin:
```
http://localhost/phpmyadmin
```

เลือก database `pcshsksn_eportfolio` → Import → เลือกไฟล์:
```
c:\xampp\htdocs\app\database\seeds\001_default_data.sql
```

หรือใช้ command line:
```cmd
c:\xampp\mysql\bin\mysql.exe -u root pcshsksn_eportfolio < c:\xampp\htdocs\app\database\seeds\001_default_data.sql
```

## ขั้นตอนที่ 3: เข้าใช้งาน

```
http://localhost/app/

Login:
  Admin:      admin / admin1234
  Student:    64001 / 12345  (หรือ std1000 / 12345)
  Teacher:    teacher01 / teacher1234
  Executive:  vd002 / 12345
```

---

## 📍 URLs ที่ใช้ได้ทันที

| URL | Role | คำอธิบาย |
|-----|------|---------|
| `/dashboard` | ทุก role | แผงหน้าหลัก |
| `/profile/360` | Student | Student 360° Profile |
| `/tcas-readiness` | Student | TCAS Readiness |
| `/analytics/early-warning` | Admin/Teacher | Early Warning |
| `/analytics/dashboard` | Admin/Executive | School Analytics |
| `/admin` | Admin | แผงควบคุมระบบ |

---

## 🔧 Migration Commands

```cmd
# ดูสถานะ
c:\xampp\php\php.exe core/MigrationRunner.php status

# รัน migration
c:\xampp\php\php.exe core/MigrationRunner.php up

# สร้าง migration ใหม่
c:\xampp\php\php.exe core/MigrationRunner.php add_column_name
```

---

## 📁 ไฟล์สำคัญ

| ไฟล์ | คำอธิบาย |
|------|---------|
| `core/MigrationRunner.php` | ระบบ migration |
| `core/Validator.php` | Input validation |
| `core/ErrorHandler.php` | จัดการ error |
| `core/Notifier.php` | ส่งการแจ้งเตือน |
| `app/services/StudentService.php` | Student 360° logic |
| `app/services/ReportService.php` | Analytics logic |
| `database/migrations/` | Schema versioned files |
| `.env` | Environment config |

---

## ⚠️ Troubleshooting

### Database connection failed
- ตรวจสอบว่า MySQL service เปิดอยู่ใน XAMPP Control Panel
- ตรวจสอบชื่อ database ใน `.env`: `DB_NAME=pcshsksn_eportfolio`

### Migration ไม่รัน
- ตรวจสอบว่า migrations table มี: `SHOW TABLES LIKE 'migrations';`
- ถ้าไม่มี ให้รัน `up` ใหม่

### 500 Error
- เปิด `APP_DEBUG=true` ใน `.env` เพื่อดู error
- ตรวจสอบ `logs/error.log`

### ไม่เห็นเมนูใหม่ใน sidebar
- ลบ browser cache (Ctrl+Shift+Delete)
- Hard refresh (Ctrl+F5)

---

## 📞 ติดต่อผู้พัฒนา

**งานศูนย์ข้อมูลสารสนเทศ | กลุ่มบริหารงานวิชาการ**
โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์
© 2026 PCSHSKSN
