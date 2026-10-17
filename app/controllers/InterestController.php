<?php
/**
 * InterestController — ความสนใจ/ความถนัด
 */
class InterestController extends Controller
{
    public function index(): void
    {
        $profile = Database::fetch("SELECT * FROM student_profiles WHERE user_id = ?", [Auth::id()]);
        if (!$profile) {
            App::redirect('profile/edit');
            return;
        }

        $interests = Database::fetchAll(
            "SELECT * FROM student_interests WHERE student_id = ? ORDER BY type, proficiency DESC",
            [$profile['id']]
        );

        $grouped = ['interest' => [], 'talent' => [], 'hobby' => []];
        foreach ($interests as $item) {
            $grouped[$item['type']][] = $item;
        }

        $this->view('interests.index', [
            'grouped' => $grouped,
        ]);
    }

    public function store(): void
    {
        CSRF::check();
        $profile = Database::fetch("SELECT * FROM student_profiles WHERE user_id = ?", [Auth::id()]);

        $errors = $this->validate(['item' => 'required|max:200']);
        if (!empty($errors)) {
            Session::flash('error', implode(', ', $errors));
            App::redirect('interests');
            return;
        }

        Database::insert(
            "INSERT INTO student_interests (student_id, type, item, proficiency) VALUES (?, ?, ?, ?)",
            [
                $profile['id'],
                $this->input('type') ?: 'interest',
                $this->input('item'),
                (int) ($this->input('proficiency') ?: 3),
            ]
        );

        Session::flash('success', 'เพิ่มข้อมูลเรียบร้อย ✨');
        App::redirect('interests');
    }

    public function delete(string $id): void
    {
        CSRF::check();
        $profile = Database::fetch("SELECT * FROM student_profiles WHERE user_id = ?", [Auth::id()]);
        Database::execute(
            "DELETE FROM student_interests WHERE id = ? AND student_id = ?",
            [(int) $id, $profile['id']]
        );
        Session::flash('success', 'ลบรายการแล้ว');
        App::redirect('interests');
    }
}
