<?php
/**
 * AcademicController — ผลการเรียน
 */
class AcademicController extends Controller
{
    public function index(): void
    {
        $profile = Database::fetch("SELECT * FROM student_profiles WHERE user_id = ?", [Auth::id()]);
        if (!$profile && Auth::hasRole('student')) {
            App::redirect('profile/edit');
            return;
        }

        $studentId = $profile['id'] ?? 0;

        // ผลการเรียนแยกตามปีการศึกษา
        $records = Database::fetchAll(
            "SELECT * FROM academic_records WHERE student_id = ? ORDER BY academic_year DESC, semester, subject",
            [$studentId]
        );

        // จัดกลุ่มตามปีการศึกษา
        $grouped = [];
        foreach ($records as $r) {
            $key = $r['academic_year'] . '_S' . $r['semester'];
            $grouped[$key][] = $r;
        }

        // GPA รวม
        $gpa = Database::fetch(
            "SELECT ROUND(SUM(grade * credit) / NULLIF(SUM(credit), 0), 2) as gpa, SUM(credit) as total_credits FROM academic_records WHERE student_id = ?",
            [$studentId]
        );

        // GPA แยกตามเทอม (สำหรับกราฟ)
        $gpaByTerm = Database::fetchAll(
            "SELECT academic_year, semester, ROUND(SUM(grade * credit) / NULLIF(SUM(credit), 0), 2) as term_gpa
             FROM academic_records WHERE student_id = ?
             GROUP BY academic_year, semester ORDER BY academic_year, semester",
            [$studentId]
        );

        $this->view('academics.index', [
            'grouped' => $grouped,
            'gpa' => $gpa['gpa'] ?? 0,
            'totalCredits' => $gpa['total_credits'] ?? 0,
            'gpaByTerm' => $gpaByTerm,
            'profile' => $profile,
        ]);
    }

    public function store(): void
    {
        CSRF::check();
        $profile = Database::fetch("SELECT * FROM student_profiles WHERE user_id = ?", [Auth::id()]);

        $errors = $this->validate([
            'subject' => 'required',
            'academic_year' => 'required',
            'semester' => 'required|numeric',
        ]);

        if (!empty($errors)) {
            Session::flash('error', implode(', ', $errors));
            App::redirect('academics');
            return;
        }

        $grade = $this->input('grade');
        if ($grade !== '' && ($grade < 0 || $grade > 4)) {
            Session::flash('error', 'เกรดต้องอยู่ระหว่าง 0.00 - 4.00');
            App::redirect('academics');
            return;
        }

        Database::query(
            "INSERT INTO academic_records (student_id, subject, subject_code, semester, academic_year, grade, credit)
             VALUES (?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE grade = VALUES(grade), credit = VALUES(credit)",
            [
                $profile['id'],
                $this->input('subject'),
                $this->input('subject_code'),
                (int) $this->input('semester'),
                $this->input('academic_year'),
                $grade !== '' ? (float) $grade : null,
                (int) ($this->input('credit') ?: 1),
            ]
        );

        Session::flash('success', 'บันทึกผลการเรียนเรียบร้อย');
        App::redirect('academics');
    }

    /**
     * Batch Import จาก CSV/Excel (Teacher only)
     */
    public function import(): void
    {
        CSRF::check();

        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'กรุณาเลือกไฟล์');
            App::redirect('academics');
            return;
        }

        $file = $_FILES['import_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($ext !== 'csv') {
            Session::flash('error', 'รองรับเฉพาะไฟล์ .csv');
            App::redirect('academics');
            return;
        }

        $handle = fopen($file['tmp_name'], 'r');
        $header = fgetcsv($handle); // Skip header row
        $imported = 0;
        $errors = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 6) {
                $errors++;
                continue;
            }

            [$studentCode, $subject, $subjectCode, $semester, $academicYear, $grade, $credit] = array_pad($row, 7, null);

            // หา student_id จาก student_code
            $student = Database::fetch(
                "SELECT id FROM student_profiles WHERE student_code = ?",
                [trim($studentCode)]
            );

            if (!$student) {
                $errors++;
                continue;
            }

            try {
                Database::query(
                    "INSERT INTO academic_records (student_id, subject, subject_code, semester, academic_year, grade, credit)
                     VALUES (?, ?, ?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE grade = VALUES(grade), credit = VALUES(credit)",
                    [
                        $student['id'],
                        trim($subject),
                        trim($subjectCode),
                        (int) $semester,
                        trim($academicYear),
                        $grade !== '' ? (float) $grade : null,
                        (int) ($credit ?: 1),
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $errors++;
            }
        }

        fclose($handle);

        Auth::logAction('import_grades', 'academic_records', 0, null, ['imported' => $imported, 'errors' => $errors]);
        Session::flash('success', "นำเข้าสำเร็จ {$imported} รายการ" . ($errors > 0 ? " (ผิดพลาด {$errors} รายการ)" : ''));
        App::redirect('academics');
    }

    /**
     * แสดงฟอร์มแก้ไขผลการเรียนรายวิชา
     */
    public function edit(string $id): void
    {
        $profile = Database::fetch("SELECT * FROM student_profiles WHERE user_id = ?", [Auth::id()]);
        if (!$profile) {
            App::redirect('profile/edit');
            return;
        }

        $record = Database::fetch(
            "SELECT * FROM academic_records WHERE id = ? AND student_id = ?",
            [(int) $id, $profile['id']]
        );

        if (!$record) {
            Session::flash('error', 'ไม่พบข้อมูลผลการเรียน หรือคุณไม่มีสิทธิ์แก้ไข');
            App::redirect('academics');
            return;
        }

        $this->view('academics.edit', [
            'record' => $record,
            'profile' => $profile,
        ]);
    }

    /**
     * บันทึกการแก้ไขผลการเรียน
     */
    public function update(string $id): void
    {
        CSRF::check();

        $profile = Database::fetch("SELECT * FROM student_profiles WHERE user_id = ?", [Auth::id()]);
        if (!$profile) {
            App::redirect('academics');
            return;
        }

        $record = Database::fetch(
            "SELECT * FROM academic_records WHERE id = ? AND student_id = ?",
            [(int) $id, $profile['id']]
        );

        if (!$record) {
            Session::flash('error', 'ไม่พบข้อมูลผลการเรียน');
            App::redirect('academics');
            return;
        }

        $errors = $this->validate([
            'subject'       => 'required',
            'academic_year' => 'required',
            'semester'      => 'required|numeric',
        ]);

        if (!empty($errors)) {
            Session::flash('error', implode(', ', $errors));
            App::redirect('academics/' . $id . '/edit');
            return;
        }

        $grade = $this->input('grade');
        if ($grade !== '' && ($grade < 0 || $grade > 4)) {
            Session::flash('error', 'เกรดต้องอยู่ระหว่าง 0.00 - 4.00');
            App::redirect('academics/' . $id . '/edit');
            return;
        }

        Database::execute(
            "UPDATE academic_records
             SET subject = ?, subject_code = ?, semester = ?, academic_year = ?, grade = ?, credit = ?
             WHERE id = ? AND student_id = ?",
            [
                $this->input('subject'),
                $this->input('subject_code'),
                (int) $this->input('semester'),
                $this->input('academic_year'),
                $grade !== '' ? (float) $grade : null,
                (int) ($this->input('credit') ?: 1),
                (int) $id,
                $profile['id'],
            ]
        );

        Auth::logAction('update_grade', 'academic_records', (int) $id);
        Session::flash('success', 'แก้ไขผลการเรียนเรียบร้อยแล้ว');
        App::redirect('academics');
    }

    /**
     * ลบผลการเรียนรายวิชา
     */
    public function delete(string $id): void
    {
        CSRF::check();

        $profile = Database::fetch("SELECT * FROM student_profiles WHERE user_id = ?", [Auth::id()]);
        if (!$profile) {
            App::redirect('academics');
            return;
        }

        $deleted = Database::execute(
            "DELETE FROM academic_records WHERE id = ? AND student_id = ?",
            [(int) $id, $profile['id']]
        );

        if ($deleted) {
            Auth::logAction('delete_grade', 'academic_records', (int) $id);
            Session::flash('success', 'ลบรายวิชาเรียบร้อยแล้ว');
        } else {
            Session::flash('error', 'ไม่พบข้อมูลหรือคุณไม่มีสิทธิ์ลบ');
        }

        App::redirect('academics');
    }
}
