<?php $pageTitle = 'แก้ไขผลงาน'; ?>
<div style="max-width:800px;margin:0 auto;">
    <div class="flex-between mb-3">
        <h2>✏️ แก้ไขผลงาน</h2>
        <a href="<?= url('portfolio/' . $work['id']) ?>" class="btn btn-secondary">← กลับ</a>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= url('portfolio/' . $work['id'] . '/update') ?>" enctype="multipart/form-data">
                <?= CSRF::field() ?>
                <div class="form-group">
                    <label class="form-label">ชื่อผลงาน *</label>
                    <input type="text" name="title" class="form-control" value="<?= e($work['title']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">หมวดหมู่ *</label>
                    <select name="category_id" class="form-control" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $work['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['icon'] . ' ' . $cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">ระดับ</label>
                        <select name="achievement_level" class="form-control">
                            <option value="">-- เลือก --</option>
                            <?php foreach (['school' => '🏫 โรงเรียน', 'district' => '🏘️ เขต', 'province' => '🏛️ จังหวัด', 'national' => '🇹🇭 ชาติ', 'international' => '🌏 นานาชาติ'] as $k => $v): ?>
                                <option value="<?= $k ?>" <?= $work['achievement_level'] === $k ? 'selected' : '' ?>>
                                    <?= $v ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">วันที่</label>
                        <input type="date" name="event_date" class="form-control"
                            value="<?= e($work['event_date'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group"><label class="form-label">รางวัล</label><input type="text" name="award"
                        class="form-control" value="<?= e($work['award'] ?? '') ?>"></div>
                <div class="form-group">
                    <div class="flex-between mb-1">
                        <label for="description" class="form-label mb-0">รายละเอียด</label>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="aiRefineBtn" 
                                style="border-radius: 50px; padding: 4px 12px; font-size: 0.75rem;">
                            ✨ ขัดเกลาด้วย AI (Portfolio Builder)
                        </button>
                    </div>
                    <div id="aiLoading" style="display: none; padding: 10px; background: var(--blue-50); border-radius: 8px; margin-bottom: 8px; font-size: 0.8rem; color: var(--primary-700);">
                        ⏳ กำลังใช้ AI เรียบเรียงให้ใหม่ กรุณารอสักครู่...
                    </div>
                    <textarea id="description" name="description" class="form-control" rows="5"
                        placeholder="พิมพ์โน้ตย่อของคุณที่นี่ แล้วกดปุ่ม 'ขัดเกลาด้วย AI' ด้านบน..."><?= e($work['description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">🖼️ เปลี่ยนภาพประกอบผลงาน (ถ้ามี)</label>
                    <input type="file" name="file" class="form-control" accept="image/*" capture="environment">
                    <small style="color: var(--gray-500); display: block; margin-top: 4px;">รองรับเฉพาะรูปภาพ ถ่ายภาพจากมือถือได้เลย ระบบจะบีบอัดภาพให้อัตโนมัติ (โควตารวม 50 MB)</small>
                </div>
                
                <div class="form-group">
                    <label for="external_link" class="form-label">☁️ ลิงก์เอกสารอ้างอิงหรือวิดีโอ (Google Drive / OneDrive)</label>
                    <input type="url" id="external_link" name="external_link" class="form-control"
                        value="<?= e($work['external_link'] ?? '') ?>" placeholder="https://drive.google.com/file/d/...">
                    <small style="color: var(--gray-500); display: block; margin-top: 4px;">💡 แนะนำให้อัปโหลดไฟล์ขนาดใหญ่ขึ้นซัพพอร์ตลิงก์ แล้วเชื่อมมาที่นีเพื่อประหยัดพื้นที่เซิร์ฟเวอร์โรงเรียน</small>
                </div>

                <!-- Skills -->
                <?php if (!empty($skills)): ?>
                <div class="form-group">
                    <label class="form-label">🧠 ทักษะที่เกี่ยวข้อง</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; max-height: 200px; overflow-y: auto; padding: 12px; background: var(--gray-50); border-radius: var(--radius-md);">
                        <?php
                        $currentCat = '';
                        foreach ($skills as $skill):
                            if ($skill['category_name'] !== $currentCat):
                                $currentCat = $skill['category_name'];
                                ?>
                                <div style="width: 100%; font-size: 0.75rem; font-weight: 700; color: var(--gray-500); margin-top: 8px; margin-bottom: 4px;">
                                    <?= e($currentCat) ?>
                                </div>
                            <?php endif; ?>
                            <label style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: white; border: 1px solid var(--gray-200); border-radius: var(--radius-full); font-size: 0.8rem; cursor: pointer; transition: var(--transition);"
                                onmouseover="this.style.borderColor='var(--primary-400)'"
                                onmouseout="this.style.borderColor='var(--gray-200)'">
                                <input type="checkbox" name="skills[]" value="<?= $skill['id'] ?>"
                                    <?= in_array($skill['id'], $selectedSkillIds ?? []) ? 'checked' : '' ?>
                                    style="accent-color: var(--primary-600);">
                                <?= e($skill['skill_name']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php $tagsArr = json_decode($work['tags'] ?? '[]', true); ?>
                <div class="form-group"><label class="form-label">Tags</label><input type="text" name="tags"
                        class="form-control" value="<?= e(implode(', ', $tagsArr ?? [])) ?>"></div>
                <div style="display:flex;gap:12px;"><a href="<?= url('portfolio/' . $work['id']) ?>"
                        class="btn btn-secondary">ยกเลิก</a><button type="submit" class="btn btn-primary"
                        style="flex:1;">💾 บันทึก</button></div>
            </form>
        </div>
    </div>
</div>

<script>
    // AI Refinement logic
    const aiRefineBtn = document.getElementById('aiRefineBtn');
    const aiLoading = document.getElementById('aiLoading');
    const descriptionArea = document.getElementById('description');

    aiRefineBtn.addEventListener('click', async function() {
        const title = document.querySelector('input[name="title"]').value;
        const description = descriptionArea.value;
        const catSelect = document.querySelector('select[name="category_id"]');
        const category = catSelect.options[catSelect.selectedIndex].text;

        if (!description || description.length < 5) {
            alert('กรุณากรอกรายละเอียดผลงานเบื้องต้นก่อนใช้งาน');
            return;
        }

        aiRefineBtn.disabled = true;
        aiLoading.style.display = 'block';

        try {
            const formData = new FormData();
            formData.append('title', title);
            formData.append('description', description);
            formData.append('category', category);
            formData.append('_csrf_token', document.querySelector('input[name="_csrf_token"]').value);

            const response = await fetch('<?= url("ai-guide/refine") ?>', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            if (data.refined) {
                descriptionArea.value = data.refined;
            } else if (data.error) {
                alert(data.error);
            }
        } catch (err) {
            console.error('AI Error:', err);
            alert('ไม่สามารถเชื่อมต่อ AI ได้ในขณะนี้');
        } finally {
            aiRefineBtn.disabled = false;
            aiLoading.style.display = 'none';
        }
    });
</script>