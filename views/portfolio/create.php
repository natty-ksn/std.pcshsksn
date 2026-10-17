<?php $pageTitle = 'เพิ่มผลงานใหม่'; ?>

<div style="max-width: 800px; margin: 0 auto;">
    <div class="flex-between mb-3">
        <h2>➕ เพิ่มผลงานใหม่</h2>
        <a href="<?= url('portfolio') ?>" class="btn btn-secondary">← กลับ</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= url('portfolio/store') ?>" enctype="multipart/form-data" id="portfolioForm">
                <?= CSRF::field() ?>

                <!-- Title -->
                <div class="form-group">
                    <label for="title" class="form-label">ชื่อผลงาน <span class="required">*</span></label>
                    <input type="text" id="title" name="title" class="form-control"
                        value="<?= old('title') ?>"
                        placeholder="เช่น โครงงานหุ่นยนต์ดูดฝุ่นอัตโนมัติ" required maxlength="200">
                </div>

                <!-- Category -->
                <div class="form-group">
                    <label for="category_id" class="form-label">หมวดหมู่ <span class="required">*</span></label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <option value="">-- เลือกหมวดหมู่ --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= old('category_id') == $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['icon'] . ' ' . $cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid-2">
                    <!-- Achievement Level -->
                    <div class="form-group">
                        <label for="achievement_level" class="form-label">ระดับผลงาน</label>
                        <select id="achievement_level" name="achievement_level" class="form-control">
                            <option value="">-- เลือกระดับ --</option>
                            <option value="school" <?= old('achievement_level') === 'school' ? 'selected' : '' ?>>🏫 ระดับโรงเรียน</option>
                            <option value="district" <?= old('achievement_level') === 'district' ? 'selected' : '' ?>>🏘️ ระดับเขต/อำเภอ</option>
                            <option value="province" <?= old('achievement_level') === 'province' ? 'selected' : '' ?>>🏛️ ระดับจังหวัด/ภาค</option>
                            <option value="national" <?= old('achievement_level') === 'national' ? 'selected' : '' ?>>🇹🇭 ระดับชาติ</option>
                            <option value="international" <?= old('achievement_level') === 'international' ? 'selected' : '' ?>>🌏 ระดับนานาชาติ</option>
                        </select>
                    </div>

                    <!-- Event Date -->
                    <div class="form-group">
                        <label for="event_date" class="form-label">วันที่ได้รับ/จัดกิจกรรม</label>
                        <input type="date" id="event_date" name="event_date" class="form-control"
                            value="<?= old('event_date') ?>">
                    </div>
                </div>

                <!-- Award -->
                <div class="form-group">
                    <label for="award" class="form-label">รางวัลที่ได้รับ</label>
                    <input type="text" id="award" name="award" class="form-control"
                        value="<?= old('award') ?>"
                        placeholder="เช่น รางวัลชนะเลิศ, เหรียญทอง">
                </div>

                <!-- Description -->
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
                        placeholder="พิมพ์โน้ตย่อของคุณที่นี่ แล้วกดปุ่ม 'ขัดเกลาด้วย AI' ด้านบน..."><?= old('description') ?></textarea>
                </div>

                <!-- File Upload -->
                <div class="form-group">
                    <label class="form-label">🖼️ ภาพประกอบผลงาน <span style="font-weight: normal; color: var(--gray-500);">(รองรับเฉพาะรูปภาพ)</span></label>
                    <div class="file-upload-area" id="dropZone" onclick="document.getElementById('fileInput').click()">
                        <div class="file-upload-icon">📸</div>
                        <div class="file-upload-text">
                            <strong>คลิกเพื่อเลือกไฟล์รูปภาพ</strong> หรือลากรูปมาวางที่นี่<br>
                            <span style="font-size: 0.85rem; color: var(--gray-500);">
                                รองรับ: JPG, PNG, GIF (ระบบจะบีบอัดภาพให้อัตโนมัติ)<br>
                                <span style="color: var(--warning-600);">⚠️ ให้สิทธิ์พื้นที่จัดเก็บรวม 50 MB / ผู้ใช้</span>
                            </span>
                        </div>
                        <div id="filePreview" style="display: none; margin-top: 12px;">
                            <div id="previewContent"></div>
                        </div>
                    </div>
                    <input type="file" id="fileInput" name="file" style="display: none;"
                        accept="image/*" capture="environment">
                </div>

                <!-- External Link for Documents / Large Files -->
                <div class="form-group">
                    <label for="external_link" class="form-label">☁️ ลิงก์เอกสารอ้างอิงหรือวิดีโอ (ทางเลือก)</label>
                    <div style="margin-bottom: 8px; font-size: 0.85rem; color: var(--gray-500); background: var(--blue-50); padding: 12px; border-radius: 8px; border: 1px solid var(--blue-100);">
                        💡 เพื่อประหยัดพื้นที่จัดเก็บ หากคุณมีไฟล์ PDF แบบรายงาน หรือคลิปวิดีโอที่เกี่ยวข้อง 
                        แนะนำให้อัปโหลดขึ้น <strong style="color: var(--primary-600);">Google Drive</strong> หรือ <strong style="color: var(--primary-600);">OneDrive</strong> 
                        แล้วตั้งค่าแชร์เป็น <em>"Anyone with the link"</em> จากนั้นนำลิงก์มาวางในช่องนี้
                    </div>
                    <input type="url" id="external_link" name="external_link" class="form-control"
                        value="<?= old('external_link') ?>"
                        placeholder="https://drive.google.com/file/d/...">
                </div>

                <!-- Tags -->
                <div class="form-group">
                    <label for="tags" class="form-label">🏷️ Tags (คั่นด้วยคอมม่า)</label>
                    <input type="text" id="tags" name="tags" class="form-control"
                        value="<?= old('tags') ?>"
                        placeholder="เช่น วิทยาศาสตร์, โครงงาน, หุ่นยนต์">
                </div>

                <!-- Teammates -->
                <div class="form-group">
                    <label class="form-label">👥 ผู้ร่วมจัดทำ (Teammates)</label>
                    <div style="background: var(--blue-50); padding: 15px; border-radius: 12px; border: 1px solid var(--blue-100); margin-bottom: 10px;">
                        <span style="font-size: 0.85rem; color: var(--blue-700);">
                            💡 <strong>งานกลุ่ม:</strong> หากงานนี้มีสมาชิกคนอื่นร่วมทำด้วย ให้พิมพ์ชื่อเพื่อนเพื่อเชิญเข้าร่วมผลงาน 
                            <br>เมื่อบันทึกผลงานและเพื่อนกดยืนยัน ผลงานนี้จะแสดงในพอร์ตโฟลิโอของเพื่อนร่วมทีมด้วยโดยอัตโนมัติ
                        </span>
                    </div>
                    
                    <div id="teammate-tags" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px;">
                        <!-- Tags will be added here via JS -->
                    </div>

                    <div style="position: relative;">
                        <input type="text" id="teammate-search" class="form-control" 
                               placeholder="ค้นหาชื่อเพื่อนหรือรหัสนักเรียน..." autocomplete="off">
                        <div id="search-results" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid var(--gray-200); border-radius: 8px; z-index: 100; box-shadow: var(--shadow-lg); max-height: 200px; overflow-y: auto;">
                            <!-- Search results will appear here -->
                        </div>
                    </div>
                    <div id="teammate-inputs">
                        <!-- Hidden inputs for form submission -->
                    </div>
                </div>

                <!-- Skills -->
                <div class="form-group">
                    <label class="form-label">🧠 ทักษะที่เกี่ยวข้อง</label>
                    <div
                        style="display: flex; flex-wrap: wrap; gap: 8px; max-height: 200px; overflow-y: auto; padding: 12px; background: var(--gray-50); border-radius: var(--radius-md);">
                        <?php
                        $currentCat = '';
                        foreach ($skills as $skill):
                            if ($skill['category_name'] !== $currentCat):
                                $currentCat = $skill['category_name'];
                                ?>
                                <div
                                    style="width: 100%; font-size: 0.75rem; font-weight: 700; color: var(--gray-500); margin-top: 8px; margin-bottom: 4px;">
                                    <?= e($currentCat) ?>
                                </div>
                            <?php endif; ?>
                            <label
                                style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: white; border: 1px solid var(--gray-200); border-radius: var(--radius-full); font-size: 0.8rem; cursor: pointer; transition: var(--transition);"
                                onmouseover="this.style.borderColor='var(--primary-400)'"
                                onmouseout="this.style.borderColor='var(--gray-200)'">
                                <input type="checkbox" name="skills[]" value="<?= $skill['id'] ?>"
                                    style="accent-color: var(--primary-600);">
                                <?= e($skill['skill_name']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Submit -->
                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <a href="<?= url('portfolio') ?>" class="btn btn-secondary">ยกเลิก</a>
                    <button type="submit" class="btn btn-primary btn-lg" style="flex: 1;">
                        💾 บันทึกผลงาน
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // File upload preview
    const fileInput = document.getElementById('fileInput');
    const dropZone = document.getElementById('dropZone');
    const preview = document.getElementById('filePreview');
    const previewContent = document.getElementById('previewContent');

    fileInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            showPreview(this.files[0]);
        }
    });

    // Drag & Drop
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('dragover');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            showPreview(e.dataTransfer.files[0]);
        }
    });

    function showPreview(file) {
        preview.style.display = 'block';
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewContent.innerHTML = `
                <img src="${e.target.result}" style="max-width: 200px; max-height: 150px; border-radius: 8px; margin-bottom: 8px;">
                <div style="font-size: 0.8rem; color: var(--gray-500);">${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)</div>
            `;
            };
            reader.readAsDataURL(file);
        } else {
            previewContent.innerHTML = `
            <div style="font-size: 1.5rem;">📄</div>
            <div style="font-size: 0.85rem; font-weight: 600;">${file.name}</div>
            <div style="font-size: 0.8rem; color: var(--gray-400);">${(file.size / 1024 / 1024).toFixed(2)} MB</div>
        `;
        }
    }

    // AI Refinement logic
    const aiRefineBtn = document.getElementById('aiRefineBtn');
    const aiLoading = document.getElementById('aiLoading');
    const descriptionArea = document.getElementById('description');

    aiRefineBtn.addEventListener('click', async function() {
        const title = document.getElementById('title').value;
        const description = descriptionArea.value;
        const catSelect = document.getElementById('category_id');
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

    // Teammates Search & Management
    const teammateSearch = document.getElementById('teammate-search');
    const searchResults = document.getElementById('search-results');
    const teammateTags = document.getElementById('teammate-tags');
    const teammateInputs = document.getElementById('teammate-inputs');
    let selectedTeammates = new Set();

    teammateSearch.addEventListener('input', async function() {
        const query = this.value.trim();
        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`<?= url("api/students/search") ?>?q=${encodeURIComponent(query)}`);
            const students = await response.json();
            
            if (students.length > 0) {
                let html = '';
                students.forEach(s => {
                    if (s.user_id != <?= Auth::id() ?> && !selectedTeammates.has(s.user_id.toString())) {
                        html += `
                            <div class="search-item" onclick="addTeammate('${s.user_id}', '${s.full_name}')" 
                                 style="padding: 10px 15px; cursor: pointer; border-bottom: 1px solid var(--gray-50); display: flex; align-items: center; gap: 10px;">
                                <div style="width: 30px; height: 30px; background: var(--primary-100); color: var(--primary-700); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800;">
                                    ${s.full_name.charAt(0)}
                                </div>
                                <div>
                                    <div style="font-weight: 700; font-size: 0.9rem;">${s.full_name}</div>
                                    <div style="font-size: 0.75rem; color: var(--gray-400);">ม.${s.class_year}/${s.class_room}</div>
                                </div>
                            </div>
                        `;
                    }
                });
                searchResults.innerHTML = html || '<div style="padding: 15px; color: var(--gray-400); font-size: 0.9rem;">ไม่พบรายชื่ออื่นที่ยังไม่ถูกเลือก</div>';
                searchResults.style.display = 'block';
            } else {
                searchResults.style.display = 'none';
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    });

    window.addTeammate = function(id, name) {
        selectedTeammates.add(id);
        
        // Add Tag
        const tag = document.createElement('div');
        tag.className = 'teammate-tag';
        tag.id = `tag-${id}`;
        tag.innerHTML = `
            <span style="font-weight: 700;">👤 ${name}</span>
            <span onclick="removeTeammate('${id}')" style="margin-left: 8px; cursor: pointer; color: var(--red-500); font-weight: 900;">×</span>
        `;
        tag.style = "background: var(--primary-50); color: var(--primary-700); padding: 6px 15px; border-radius: 50px; border: 1px solid var(--primary-100); display: flex; align-items: center; font-size: 0.85rem; margin-bottom: 5px;";
        teammateTags.appendChild(tag);
        
        // Add Hidden Input
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'teammates[]';
        input.value = id;
        input.id = `input-${id}`;
        teammateInputs.appendChild(input);
        
        teammateSearch.value = '';
        searchResults.style.display = 'none';
    };

    window.removeTeammate = function(id) {
        selectedTeammates.delete(id);
        document.getElementById(`tag-${id}`).remove();
        document.getElementById(`input-${id}`).remove();
    };

    // Close search on click outside
    document.addEventListener('click', (e) => {
        if (!teammateSearch.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });

    // Prevent double submit
    document.getElementById('portfolioForm').addEventListener('submit', function (e) {
        const btn = this.querySelector('button[type="submit"]');
        if (btn.disabled) { e.preventDefault(); return; }
        btn.disabled = true;
        btn.textContent = '⏳ กำลังบันทึก...';
    });
</script>