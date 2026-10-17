<?php $pageTitle = 'ห้องปฏิบัติการโครงงาน (Project Workspace)'; ?>

<style>
    .workspace-container {
        max-width: 1200px;
        margin: 2rem auto;
        font-family: 'Inter', 'Sarabun', sans-serif;
        animation: fadeIn 0.4s ease-out;
    }

    .workspace-header {
        background: linear-gradient(135deg, #1e293b, #334155);
        color: white;
        padding: 2rem;
        border-radius: 20px;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .workspace-header::after {
        content: '🔬';
        position: absolute;
        right: -20px;
        top: -40px;
        font-size: 10rem;
        opacity: 0.1;
        transform: rotate(15deg);
    }

    .workspace-header h1 {
        margin: 0 0 0.5rem 0;
        font-size: 2.2rem;
        font-weight: 800;
    }

    .project-meta {
        display: flex;
        gap: 1.5rem;
        color: #cbd5e1;
        font-size: 0.9rem;
        margin-top: 1rem;
    }

    .grid-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
    }

    @media(max-width: 900px) {
        .grid-layout {
            grid-template-columns: 1fr;
        }
    }

    .card-panel {
        background: white;
        border-radius: 20px;
        padding: 1.8rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }

    .panel-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f1f5f9;
    }

    /* Logbook Styles */
    .logbook-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .logbook-item {
        background: #f8fafc;
        border-left: 4px solid #3b82f6;
        padding: 1rem 1.5rem;
        border-radius: 0 12px 12px 0;
    }

    .logbook-meta {
        font-size: 0.8rem;
        color: #64748b;
        margin-bottom: 0.5rem;
        display: flex;
        justify-content: space-between;
    }

    .logbook-content {
        color: #334155;
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .log-teacher-comment {
        margin-top: 0.8rem;
        padding: 0.8rem;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 0.85rem;
        border-radius: 8px;
    }

    /* Upload Styles */
    .upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        padding: 2.5rem 1rem;
        text-align: center;
        background: #f8fafc;
        transition: 0.3s;
        cursor: pointer;
        position: relative;
    }

    .upload-zone:hover,
    .upload-zone.dragover {
        border-color: #6366f1;
        background: #eef2ff;
    }

    .upload-zone h3 {
        color: #475569;
        margin-bottom: 0.5rem;
        font-size: 1.1rem;
    }

    .upload-zone p {
        color: #94a3b8;
        font-size: 0.85rem;
    }

    .upload-zone svg {
        width: 48px;
        height: 48px;
        color: #94a3b8;
        margin-bottom: 1rem;
    }

    .doc-list {
        margin-top: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
    }

    .doc-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
    }

    .doc-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .doc-icon {
        font-size: 1.5rem;
        color: #ef4444;
    }

    /* PDF Icon color */

    .form-control {
        width: 100%;
        padding: 0.8rem;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        margin-bottom: 1rem;
        font-family: inherit;
    }

    .btn-primary {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 0.8rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
    }

    .btn-primary:hover {
        background: #2563eb;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="workspace-container">

    <?php if (Session::hasFlash('success')): ?>
        <div class="alert alert-success"
            style="background:#dcfce7; color:#15803d; padding:1rem; border-radius:12px; margin-bottom:2rem;">
            ✅ <?= htmlspecialchars(Session::getFlash('success')) ?>
        </div>
    <?php endif; ?>

    <div class="workspace-header">
        <h1><?= htmlspecialchars($project['title_th']) ?></h1>
        <?php if (!empty($project['title_en'])): ?>
            <div style="font-style:italic; opacity:0.8; margin-bottom:1rem;"><?= htmlspecialchars($project['title_en']) ?>
            </div>
        <?php endif; ?>

        <span
            style="display:inline-block; padding:0.3rem 0.8rem; background:rgba(255,255,255,0.2); border-radius:999px; font-size:0.8rem; font-weight:600; text-transform:uppercase;">
            สถานะ: <?= str_replace('_', ' ', $project['status']) ?>
        </span>

        <div class="project-meta">
            <span>👨‍🏫 ที่ปรึกษา: <?= htmlspecialchars($project['advisor_name'] ?? 'TBA') ?></span>
            <span>📂 หมวด: <?= ucfirst(htmlspecialchars($project['project_type'])) ?></span>
        </div>

        <?php
        // ตรวจสอบว่าเป็น Leader หรือไม่ (สำหรับแสดงปุ่ม Export)
        $isLeader = false;
        $currentUserId = Auth::id();
        $currentUsername = Auth::user()['username'] ?? '';
        foreach ($members as $m) {
            $matchUser = ((int) ($m['student_id'] ?? 0) === $currentUserId) || ($currentUsername !== '' && ($m['username'] ?? '') === $currentUsername);
            if ($matchUser && $m['role_in_project'] === 'leader') {
                $isLeader = true;
                break;
            }
        }
        ?>

        <div style="margin-top: 1.5rem; display: flex; gap: 10px; flex-wrap: wrap;">
            <?php if ($isLeader): ?>
                <a href="<?= url('projects/export/' . $project['id']) ?>"
                    onclick="return confirm('ยืนยันการส่งออกโครงงานนี้เข้าสู่ระบบ Portfolio ของสมาชิกทุกคนในทีม?')"
                    class="btn-primary"
                    style="background: #10b981; text-decoration: none; display: flex; align-items: center; gap: 8px;">
                    🚀 ส่งออกเป็นผลงานเด่น (Export to Portfolio)
                </a>
            <?php endif; ?>
            <a href="<?= url('projects/' . $project['id'] . '/pdf') ?>" class="btn-primary"
                style="background: transparent; border: 2px solid rgba(255,255,255,0.4); text-decoration: none; display: flex; align-items: center; gap: 8px;"
                onmouseover="this.style.background='rgba(255,255,255,0.15)'"
                onmouseout="this.style.background='transparent'"
                title="ดาวน์โหลดรายงานความก้าวหน้าโครงงาน PDF (Sarabun Font)">
                📄 พิมพ์รายงาน PDF
            </a>
        </div>
    </div>

    <div class="grid-layout">

        <!-- Left: Upload & Documents -->
        <div>
            <div class="card-panel">
                <div class="panel-title">☁️ ส่งเอกสาร / สื่อความละเอียดสูง (Google Cloud Storage)</div>

                <form action="<?= url('documents/store') ?>" method="POST" id="uploadForm">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="project_id" value="<?= $project['id'] ?>">

                    <!-- 
                        SA Note: ใน Phase 2 ระบบ GCS Direct Upload 
                        เราจะใช้ Javascript ขีดเส้นขอ Signed URL จาก /documents/signed-url อัตโนมัติเมื่อกดเลือกไฟล์
                        และอัปโหลดไป GCS ตรงๆ แล้วค่อยส่ง GCS Object Path ไปเซฟลง Database ด้ายล่างนี้
                    -->
                    <div style="display:flex; gap:1rem; margin-bottom:1rem;">
                        <input type="text" name="title" class="form-control"
                            placeholder="ชื่อเอกสาร (เช่น ร่างบทที่ 1 v.1)" required style="margin-bottom:0;">
                        <select name="document_type" class="form-control" required
                            style="margin-bottom:0; flex-shrink:0; width:180px;">
                            <option value="">-- ประเภท --</option>
                            <option value="proposal">ข้อเสนอโครงงาน</option>
                            <option value="chapter1">บทที่ 1</option>
                            <option value="chapter2">บทที่ 2</option>
                            <option value="chapter3">บทที่ 3</option>
                            <option value="chapter4">บทที่ 4</option>
                            <option value="chapter5">บทที่ 5</option>
                            <option value="presentation">เล่มฉบับสมบูรณ์ (PDF)</option>
                            <option value="video">วิดีโอนำเสนอ (MP4)</option>
                        </select>
                    </div>

                    <div class="upload-zone" id="gcsUploadZone" onclick="document.getElementById('fileInput').click()">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.36 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z">
                            </path>
                        </svg>
                        <h3>คลิกเพื่อเลือกไฟล์ หรือ ลากไฟล์มาวางที่นี่</h3>
                        <p>รองรับไฟล์ PDF, Word, PowerPoint และ Video MP4 (สูงสุด 500MB ผ่าน GCS Direct)</p>

                        <!-- ซ่อน File Input ไว้ สำหรับรอต่อกับ Signed URL -->
                        <input type="file" id="fileInput" style="display:none;" onchange="handleFileSelect(event)">

                        <!-- จำลองผลลัพท์จากการ Upload GCS -> Path Storage -->
                        <input type="hidden" name="file_path_gcs" id="gcsPath" value="">
                    </div>

                    <!-- Progress Bar (Simulation for Demo) -->
                    <div id="uploadProgressContainer" style="display:none; margin-top:1rem;">
                        <div
                            style="font-size:0.8rem; color:#64748b; margin-bottom:5px; display:flex; justify-content:space-between;">
                            <span>กำลังเข้ารหัสและอัปโหลดไป Google Cloud Storage...</span>
                            <span id="uploadPercent">0%</span>
                        </div>
                        <div style="width:100%; background:#e2e8f0; border-radius:999px; height:8px; overflow:hidden;">
                            <div id="uploadBar" style="width:0%; background:#3b82f6; height:100%; transition:0.3s;">
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:1.5rem; text-align:right;">
                        <button type="submit" class="btn-primary" id="btnSubmitDoc"
                            disabled>บันทึกเข้าสู่ระบบโครงงาน</button>
                    </div>
                </form>

                <div class="panel-title" style="margin-top:3rem; border-color:#e2e8f0; font-size:1rem;">📄
                    เอกสารและการจัดส่งประเมิน</div>
                <div class="doc-list">
                    <?php if (empty($documents)): ?>
                        <p style="text-align:center; color:#94a3b8; font-size:0.9rem;">ยังไม่มีเอกสารใดๆ ในระบบ</p>
                    <?php else: ?>
                        <?php foreach ($documents as $doc): ?>
                            <div class="doc-item">
                                <div class="doc-info">
                                    <span class="doc-icon"><?= $doc['document_type'] === 'video' ? '🎬' : '📑' ?></span>
                                    <div>
                                        <div style="font-weight:600; color:#1e293b; font-size:0.9rem;">
                                            <?= htmlspecialchars($doc['title']) ?></div>
                                        <div style="font-size:0.75rem; color:#64748b;">
                                            เวอร์ชั่น: <?= $doc['version'] ?> •
                                            อัปโหลด: <?= date('d M Y', strtotime($doc['created_at'])) ?>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <span
                                        style="font-size:0.75rem; font-weight:700; padding:0.3rem 0.6rem; border-radius:6px; background:#f1f5f9; color:#475569;">
                                        <?= strtoupper($doc['review_status']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- Right: Logbook -->
        <div>
            <div class="card-panel">
                <div class="panel-title">📝 บันทึกการทำงาน (Logbook)</div>

                <form action="<?= url('logbooks/store') ?>" method="POST"
                    style="margin-bottom:2rem; background:#f8fafc; padding:1rem; border-radius:12px; border:1px solid #e2e8f0;">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="project_id" value="<?= $project['id'] ?>">

                    <textarea name="activity_detail" class="form-control"
                        placeholder="คุณทำอะไรบ้างในสัปดาห์นี้? (ตัวอย่าง: ออกแบบการทดลองตอนที่ 1 และเตรียมสารละลายเคมี)"
                        required rows="3" style="font-size:0.9rem;"></textarea>

                    <div style="display:flex; gap:10px; align-items:center;">
                        <div style="flex:1;">
                            <input type="number" step="0.5" name="hours_spent" class="form-control"
                                placeholder="ใช้เวลา (ชั่วโมง)" required style="margin-bottom:0; font-size:0.9rem;">
                        </div>
                        <button type="submit" class="btn-primary" style="padding: 0.8rem 1rem; font-size:0.9rem;">บันทึก
                            Log</button>
                    </div>
                </form>

                <div class="logbook-list">
                    <?php if (empty($logbooks)): ?>
                        <p style="text-align:center; color:#94a3b8; font-size:0.85rem;">ยังไม่มีบันทึกการทำงาน</p>
                    <?php else: ?>
                        <?php foreach ($logbooks as $log): ?>
                            <div class="logbook-item">
                                <div class="logbook-meta">
                                    <span
                                        style="font-weight:600; color:#1e293b;"><?= htmlspecialchars($log['student_name']) ?></span>
                                    <span>🕒 <?= date('d M Y', strtotime($log['created_at'])) ?> (<?= $log['hours_spent'] ?>
                                        ชม.)</span>
                                </div>
                                <div class="logbook-content">
                                    <?= nl2br(htmlspecialchars($log['activity_detail'])) ?>
                                </div>
                                <?php if (!empty($log['teacher_comment'])): ?>
                                    <div class="log-teacher-comment">
                                        <strong>ความเห็นที่ปรึกษา:</strong><br>
                                        <?= nl2br(htmlspecialchars($log['teacher_comment'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // Mock GCS Upload Simulation สำหรับโครงสร้างและ UI (ใช้สาธิตก่อนเชื่อม Key GCS จริง)
    function handleFileSelect(event) {
        const file = event.target.files[0];
        if (!file) return;

        const btn = document.getElementById('btnSubmitDoc');
        const prog = document.getElementById('uploadProgressContainer');
        const bar = document.getElementById('uploadBar');
        const txt = document.getElementById('uploadPercent');
        const pathInput = document.getElementById('gcsPath');
        const zoneH3 = document.querySelector('#gcsUploadZone h3');

        zoneH3.innerText = 'เลือกไฟล์: ' + file.name;

        // Reset
        btn.disabled = true;
        prog.style.display = 'block';
        bar.style.width = '0%';
        txt.innerText = '0%';

        // Mock Upload Timer to Simulate GCS Streaming
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress >= 100) {
                progress = 100;
                clearInterval(interval);
                setTimeout(() => {
                    txt.innerText = 'อัปโหลดไป Cloud Storage สมบูรณ์!';
                    bar.style.background = '#10b981'; // Green

                    // กำหนด Path ที่อัปโหลดเสร็จสมมติ
                    pathInput.value = 'gcs://pcshsksn-bucket/' + new Date().getFullYear() + '/project-doc/' + file.name;

                    // เปิดปุ่ม Submit ไปยัง Database PHP ของเรา
                    btn.disabled = false;
                    btn.style.background = '#22c55e'; // Green btn
                }, 500);
            }
            bar.style.width = progress + '%';
            if (progress < 100) txt.innerText = Math.round(progress) + '%';
        }, 200);
    }
</script>