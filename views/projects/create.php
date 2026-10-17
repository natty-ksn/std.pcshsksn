<?php $pageTitle = $title ?? 'เสนอข้อเสนอโครงงาน'; ?>

<style>
.proposal-container {
    max-width: 800px;
    margin: 2rem auto;
    animation: slideUp 0.5s ease-out;
    font-family: 'Inter', 'Sarabun', sans-serif;
}

.proposal-card {
    background: #ffffff;
    border-radius: 24px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.06);
    padding: 3rem;
    position: relative;
    overflow: hidden;
}

.proposal-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 8px;
    background: linear-gradient(90deg, #ec4899, #8b5cf6, #3b82f6);
}

.header-section {
    text-align: center;
    margin-bottom: 2.5rem;
}

.header-section h1 {
    font-size: 2rem;
    color: #1e293b;
    font-weight: 800;
    margin-bottom: 0.5rem;
}

.header-section p {
    color: #64748b;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.5rem;
}

.form-label span.req {
    color: #ef4444;
}

.form-control {
    width: 100%;
    padding: 1rem 1.2rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 1rem;
    color: #1e293b;
    background: #f8fafc;
    transition: all 0.3s;
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: #8b5cf6;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

textarea.form-control {
    min-height: 120px;
    resize: vertical;
}

.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.submit-btn {
    background: linear-gradient(135deg, #3b82f6, #6366f1);
    color: white;
    width: 100%;
    padding: 1.2rem;
    font-size: 1.1rem;
    font-weight: 700;
    border: none;
    border-radius: 14px;
    cursor: pointer;
    margin-top: 1rem;
    transition: all 0.3s;
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(99, 102, 241, 0.4);
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<div class="proposal-container">
    
    <?php if (Session::hasFlash('error')): ?>
        <div class="alert alert-error" style="background:#fef2f2; color:#dc2626; padding:1rem; border-radius:12px; margin-bottom:2rem; border-left:4px solid #ef4444;">
            ❌ <?= htmlspecialchars(Session::getFlash('error')) ?>
        </div>
    <?php endif; ?>

    <div class="proposal-card">
        <div class="header-section">
            <h1>✨ เสนอหัวข้อโครงงาน</h1>
            <p>กรอกข้อมูลเบื้องต้นเพื่อส่งให้ครูที่ปรึกษาพิจารณาโครงงานของคุณ</p>
        </div>

        <form action="<?= url('projects/store') ?>" method="POST">
            <?= CSRF::field() ?>

            <div class="form-group">
                <label class="form-label">ชื่อโครงงาน (ภาษาไทย) <span class="req">*</span></label>
                <input type="text" name="title_th" class="form-control" placeholder="ระบุชื่อโครงงานที่กระชับและสื่อความหมาย" required>
            </div>

            <div class="form-group">
                <label class="form-label">ชื่อโครงงาน (ภาษาอังกฤษ)</label>
                <input type="text" name="title_en" class="form-control" placeholder="Project Title (Optional)">
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">ประเภทโครงงาน <span class="req">*</span></label>
                    <select name="project_type" class="form-control" required>
                        <option value="experimental">ทดลอง (Experimental)</option>
                        <option value="survey">สำรวจ (Survey)</option>
                        <option value="invention">สิ่งประดิษฐ์ (Invention/Innovation)</option>
                        <option value="applied">ประยุกต์การใช้งาน (Applied Science)</option>
                        <option value="theoretical">ทฤษฎี (Theoretical)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">เชื่อมโยงกับรายวิชา</label>
                    <select name="subject_id" class="form-control">
                        <option value="">- โครงงานอิสระ (ไม่มีวิชาบังคับ) -</option>
                        <?php foreach($subjects as $subj): ?>
                            <option value="<?= $subj['id'] ?>">วิชา <?= htmlspecialchars($subj['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">สมาชิกในทีม (Teammates)</label>
                <div style="background: #f0f9ff; padding: 1.2rem; border-radius: 12px; border: 1px solid #bae6fd; margin-bottom: 1rem;">
                    <span style="font-size: 0.85rem; color: #0369a1;">
                        💡 <strong>งานกลุ่ม:</strong> หากโครงงานนี้ทำร่วมกับเพื่อน ให้พิมพ์ชื่อเพื่อนเพื่อเพิ่มเข้าทีม (ผู้นำกลุ่มจะเป็นคนเสนอหัวข้อนี้)
                    </span>
                </div>
                
                <div id="teammate-tags" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px;">
                    <!-- Tags will be added here via JS -->
                </div>

                <div style="position: relative;">
                    <input type="text" id="teammate-search" class="form-control" 
                           placeholder="ค้นหาชื่อเพื่อนหรือรหัสนักเรียน..." autocomplete="off">
                    <div id="search-results" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #e2e8f0; border-radius: 12px; z-index: 100; box-shadow: 0 10px 25px rgba(0,0,0,0.1); max-height: 200px; overflow-y: auto;">
                        <!-- Search results will appear here -->
                    </div>
                </div>
                <div id="teammate-inputs">
                    <!-- Hidden inputs for form submission -->
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">เลือกครูที่ปรึกษาหลัก <span class="req">*</span></label>
                <select name="advisor_id" class="form-control" required>
                    <option value="">- ค้นหาและเลือกครูที่ปรึกษา -</option>
                    <?php foreach($teachers as $teacher): ?>
                        <option value="<?= $teacher['id'] ?>">ครู <?= htmlspecialchars($teacher['display_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">บทคัดย่อ / แนวคิดเบื้องต้น (Abstract) <span class="req">*</span></label>
                <textarea name="abstract_th" class="form-control" placeholder="อธิบายปัญหาที่คุณต้องการแก้ วิธีการดำเนินการ และผลที่คาดว่าจะได้รับ (ความยาว 3-5 บรรทัด)" required></textarea>
            </div>

            <button type="submit" class="submit-btn">ส่งข้อเสนอโครงงาน 🚀</button>
        </form>
    </div>
</div>
<script>
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
                            <div onclick="addTeammate('${s.user_id}', '${s.full_name}')" 
                                 style="padding: 12px 15px; cursor: pointer; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 10px;">
                                <div style="width: 32px; height: 32px; background: #e0e7ff; color: #4338ca; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 800;">
                                    ${s.full_name.charAt(0)}
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 700; font-size: 0.9rem; color: #1e293b;">${s.full_name}</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">ม.${s.class_year}/${s.class_room}</div>
                                </div>
                            </div>
                        `;
                    }
                });
                searchResults.innerHTML = html || '<div style="padding: 15px; color: #94a3b8; font-size: 0.9rem;">ไม่พบรายชื่ออื่น</div>';
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
        
        const tag = document.createElement('div');
        tag.id = `tag-${id}`;
        tag.style = "background: #eff6ff; color: #1d4ed8; padding: 6px 14px; border-radius: 50px; border: 1px solid #bfdbfe; display: flex; align-items: center; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px;";
        tag.innerHTML = `
            <span>👤 ${name}</span>
            <span onclick="removeTeammate('${id}')" style="margin-left: 8px; cursor: pointer; color: #ef4444; font-size: 1.2rem; line-height: 1;">&times;</span>
        `;
        teammateTags.appendChild(tag);
        
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

    document.addEventListener('click', (e) => {
        if (!teammateSearch.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
</script>
