<?php $pageTitle = 'แก้ไขผลการเรียน'; ?>

<div style="max-width: 640px; margin: 0 auto;">
    <div class="animate-fade-in">

        <!-- Back Button -->
        <div style="margin-bottom: 24px;">
            <a href="<?= url('academics') ?>" class="btn btn-secondary btn-sm"
               style="border-radius: var(--radius-full); padding: 8px 20px; font-weight: 700;">
                ← กลับหน้าผลการเรียน
            </a>
        </div>

        <!-- Header Card -->
        <div class="glass-card mb-4"
             style="border-radius: var(--radius-2xl); overflow: hidden; box-shadow: var(--shadow-xl); border: none;">
            <div style="padding: 32px 40px; background: linear-gradient(135deg, var(--primary-700), var(--primary-900)); color: white;">
                <div style="font-size: 2.5rem; margin-bottom: 8px;">✏️</div>
                <h1 style="font-size: 1.8rem; font-weight: 900; color: white; margin-bottom: 6px; letter-spacing: -0.5px;">
                    แก้ไขผลการเรียน
                </h1>
                <p style="color: rgba(255,255,255,0.7); font-size: 0.9rem; margin: 0;">
                    <?= e($record['subject']) ?> — ปีการศึกษา <?= e($record['academic_year']) ?> เทอม <?= e($record['semester']) ?>
                </p>
            </div>

            <!-- Flash Messages -->
            <?php if (Session::flash('error')): ?>
                <div style="margin: 16px 40px 0; padding: 12px 16px; background: #fee2e2; border-radius: 10px; color: #991b1b; font-weight: 600;">
                    ⚠️ <?= e(Session::flash('error')) ?>
                </div>
            <?php endif; ?>

            <div style="padding: 40px;">
                <form method="POST" action="<?= url('academics/' . $record['id'] . '/update') ?>">
                    <?= CSRF::field() ?>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <!-- ชื่อวิชา -->
                        <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 0;">
                            <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">
                                ชื่อวิชา <span style="color: var(--danger-500);">*</span>
                            </label>
                            <input type="text" name="subject" class="form-control"
                                   value="<?= e($record['subject']) ?>" required
                                   placeholder="เช่น คณิตศาสตร์เพิ่มเติม"
                                   style="border-radius: var(--radius-lg); font-size: 1rem; padding: 12px 16px;">
                        </div>

                        <!-- รหัสวิชา -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">รหัสวิชา</label>
                            <input type="text" name="subject_code" class="form-control"
                                   value="<?= e($record['subject_code'] ?? '') ?>"
                                   placeholder="เช่น ค32201"
                                   style="border-radius: var(--radius-lg); padding: 12px 16px;">
                        </div>

                        <!-- ปีการศึกษา -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">
                                ปีการศึกษา <span style="color: var(--danger-500);">*</span>
                            </label>
                            <input type="text" name="academic_year" class="form-control"
                                   value="<?= e($record['academic_year']) ?>" required
                                   placeholder="เช่น 2568"
                                   style="border-radius: var(--radius-lg); padding: 12px 16px;">
                        </div>

                        <!-- เทอม -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">
                                ภาคเรียนที่ <span style="color: var(--danger-500);">*</span>
                            </label>
                            <select name="semester" class="form-control"
                                    style="border-radius: var(--radius-lg); padding: 12px 16px;" required>
                                <option value="1" <?= $record['semester'] == 1 ? 'selected' : '' ?>>ภาคเรียนที่ 1</option>
                                <option value="2" <?= $record['semester'] == 2 ? 'selected' : '' ?>>ภาคเรียนที่ 2</option>
                                <option value="3" <?= $record['semester'] == 3 ? 'selected' : '' ?>>ภาคเรียนพิเศษ</option>
                            </select>
                        </div>

                        <!-- เกรด -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">เกรด (0.0 – 4.0)</label>
                            <input type="number" name="grade" class="form-control"
                                   value="<?= $record['grade'] !== null ? $record['grade'] : '' ?>"
                                   step="0.5" min="0" max="4"
                                   placeholder="เช่น 3.5"
                                   style="border-radius: var(--radius-lg); padding: 12px 16px;">
                            <small style="color: var(--gray-400); font-size: 0.78rem;">เว้นว่างถ้ายังไม่มีเกรด</small>
                        </div>

                        <!-- หน่วยกิต -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">หน่วยกิต</label>
                            <input type="number" name="credit" class="form-control"
                                   value="<?= e($record['credit'] ?? 1) ?>"
                                   min="0" max="6"
                                   style="border-radius: var(--radius-lg); padding: 12px 16px;">
                        </div>
                    </div>

                    <!-- Grade Preview -->
                    <div id="gradePreview"
                         style="background: var(--gray-50); border-radius: var(--radius-lg); padding: 16px; margin-bottom: 28px; display: flex; align-items: center; gap: 16px;">
                        <div id="gradeCircle"
                             style="width: 56px; height: 56px; border-radius: 50%; background: var(--primary-500); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; font-weight: 900; color: white; flex-shrink: 0;">
                            <?= $record['grade'] !== null ? number_format($record['grade'], 1) : '-' ?>
                        </div>
                        <div>
                            <div id="gradeLabel" style="font-weight: 800; font-size: 1rem; color: var(--gray-900);">
                                <?php
                                $g = $record['grade'];
                                echo $g === null ? 'ยังไม่มีเกรด' : ($g >= 3.5 ? 'ดีเยี่ยม 🏆' : ($g >= 3.0 ? 'ดีมาก ⭐' : ($g >= 2.5 ? 'ดี 👍' : ($g >= 2.0 ? 'พอใช้' : ($g >= 1.0 ? 'ผ่าน' : 'ไม่ผ่าน ❌')))));
                                ?>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--gray-500);">ตัวอย่างผลการประเมิน</div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div style="display: flex; gap: 12px;">
                        <a href="<?= url('academics') ?>" class="btn btn-secondary"
                           style="border-radius: var(--radius-full); padding: 12px 28px; font-weight: 700; flex: none;">
                            ยกเลิก
                        </a>
                        <button type="submit" class="btn btn-primary"
                                style="border-radius: var(--radius-full); padding: 12px 32px; font-weight: 800; flex: 1; font-size: 1rem;">
                            💾 บันทึกการแก้ไข
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    const gradeInput = document.querySelector('input[name="grade"]');
    const gradeCircle = document.getElementById('gradeCircle');
    const gradeLabel = document.getElementById('gradeLabel');

    function updateGradePreview() {
        const v = parseFloat(gradeInput.value);
        if (isNaN(v) || gradeInput.value === '') {
            gradeCircle.textContent = '-';
            gradeCircle.style.background = 'var(--gray-300)';
            gradeLabel.textContent = 'ยังไม่มีเกรด';
            return;
        }
        gradeCircle.textContent = v.toFixed(1);
        if (v >= 3.5) { gradeCircle.style.background = '#16a34a'; gradeLabel.textContent = 'ดีเยี่ยม 🏆'; }
        else if (v >= 3.0) { gradeCircle.style.background = '#2563eb'; gradeLabel.textContent = 'ดีมาก ⭐'; }
        else if (v >= 2.5) { gradeCircle.style.background = 'var(--primary-500)'; gradeLabel.textContent = 'ดี 👍'; }
        else if (v >= 2.0) { gradeCircle.style.background = '#d97706'; gradeLabel.textContent = 'พอใช้'; }
        else if (v >= 1.0) { gradeCircle.style.background = '#ea580c'; gradeLabel.textContent = 'ผ่าน'; }
        else { gradeCircle.style.background = '#dc2626'; gradeLabel.textContent = 'ไม่ผ่าน ❌'; }
    }

    gradeInput.addEventListener('input', updateGradePreview);
</script>
