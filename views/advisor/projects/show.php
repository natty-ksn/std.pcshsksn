<?php $pageTitle = $title ?? 'พิจารณาข้อเสนอโครงงาน'; ?>

<style>
.detail-container {
    max-width: 1200px;
    margin: 2rem auto;
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    font-family: 'Inter', 'Sarabun', sans-serif;
    animation: fadeIn 0.4s ease-out;
}

@media (max-width: 900px) {
    .detail-container { grid-template-columns: 1fr; }
}

.panel {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
}

.panel-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f1f5f9;
}

.info-group { margin-bottom: 1.25rem; }
.info-label { font-size: 0.85rem; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 0.3rem; }
.info-value { font-size: 1.1rem; color: #1e293b; line-height: 1.5; }

.status-badge {
    display: inline-block;
    padding: 0.4rem 1rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    background: #eef2ff;
    color: #4f46e5;
}

/* Actions Form */
.action-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 1.5rem;
    margin-top: 2rem;
}

.btn-approve { background: #16a34a; color: white; border: none; padding: 0.8rem 1.5rem; border-radius: 12px; font-weight: 600; cursor: pointer; transition: 0.2s; width: 100%; margin-bottom: 0.8rem; }
.btn-approve:hover { background: #15803d; }

.btn-revise { background: #f59e0b; color: white; border: none; padding: 0.8rem 1.5rem; border-radius: 12px; font-weight: 600; cursor: pointer; transition: 0.2s; width: 100%; margin-bottom: 0.8rem; }
.btn-revise:hover { background: #d97706; }

.btn-reject { background: #ef4444; color: white; border: none; padding: 0.8rem 1.5rem; border-radius: 12px; font-weight: 600; cursor: pointer; transition: 0.2s; width: 100%; }
.btn-reject:hover { background: #dc2626; }

textarea.comment-box {
    width: 100%; border: 1px solid #cbd5e1; border-radius: 12px; padding: 1rem; min-height: 100px; margin-bottom: 1rem;
    font-family: inherit;
    resize: vertical;
}

.member-list { list-style: none; padding: 0; margin: 0; }
.member-list li { padding: 0.8rem; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 10px; }
.member-list li:last-child { border-bottom: none; }
.member-role { font-size: 0.75rem; background: #e2e8f0; color: #475569; padding: 0.2rem 0.6rem; border-radius: 6px; }

@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="detail-container">

    <!-- Left Column: Details -->
    <div class="panel">
        <div class="info-group" style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <span class="status-badge"><?= str_replace('_', ' ', $project['status']) ?></span>
                <p style="margin-top:0.5rem; color:#64748b; font-size:0.9rem;">รหัสโครงงาน: <strong><?= htmlspecialchars($project['project_code']) ?></strong></p>
            </div>
        </div>

        <h1 style="font-size:1.8rem; font-weight:800; color:#1e293b; margin-top:1rem; margin-bottom:0.5rem;">
            <?= htmlspecialchars($project['title_th']) ?>
        </h1>
        <?php if (!empty($project['title_en'])): ?>
            <p style="color:#64748b; font-style:italic; margin-bottom:2rem;"><?= htmlspecialchars($project['title_en']) ?></p>
        <?php endif; ?>

        <div class="info-group">
            <div class="info-label">บทคัดย่อ / ข้อเสนอ (Abstract)</div>
            <div class="info-value" style="background:#f8fafc; padding:1.5rem; border-radius:12px; border-left:4px solid #3b82f6; font-size:1rem;">
                <?= nl2br(htmlspecialchars($project['abstract_th'])) ?>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-top:2rem;">
            <div class="info-group">
                <div class="info-label">ประเภทโครงงาน</div>
                <div class="info-value"><?= ucfirst(htmlspecialchars($project['project_type'])) ?></div>
            </div>
            <div class="info-group">
                <div class="info-label">วิชาอ้างอิง</div>
                <div class="info-value"><?= htmlspecialchars($project['subject_name'] ?? 'โครงงานอิสระ') ?></div>
            </div>
        </div>

        <!-- Project Documents Section -->
        <div style="margin-top: 2.5rem; padding-top: 1.5rem; border-top: 2px solid #f1f5f9;">
            <div class="panel-title" style="margin-bottom: 1rem;">📄 เอกสารโครงงานที่ส่งตรวจ (<?= count($documents ?? []) ?> รายการ)</div>
            <?php if (empty($documents)): ?>
                <div style="text-align: center; padding: 2rem; background: #f8fafc; border-radius: 12px; color: #64748b;">
                    ยังไม่มีเอกสารที่นักเรียนอัปโหลดในโครงงานนี้
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($documents as $doc): ?>
                        <?php
                            $stColor = $doc['review_status'] === 'approved' ? '#16a34a' : ($doc['review_status'] === 'revision_required' ? '#d97706' : '#4f46e5');
                            $stBg = $doc['review_status'] === 'approved' ? '#f0fdf4' : ($doc['review_status'] === 'revision_required' ? '#fffbeb' : '#eef2ff');
                            $stLabel = $doc['review_status'] === 'approved' ? 'อนุมัติแล้ว' : ($doc['review_status'] === 'revision_required' ? 'ส่งกลับแก้ไข' : 'รอตรวจ');
                        ?>
                        <div style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem; background: #ffffff;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
                                <div>
                                    <div style="font-weight: 700; color: #1e293b; font-size: 1rem;">
                                        📑 <?= e($doc['title']) ?> <span style="font-size: 0.8rem; color: #64748b;">(v<?= (int) $doc['version'] ?> • <?= e($doc['document_type']) ?>)</span>
                                    </div>
                                    <div style="font-size: 0.8rem; color: #64748b; margin-top: 4px;">
                                        ส่งเมื่อ: <?= date('d/m/Y H:i', strtotime($doc['created_at'])) ?>
                                        <?php if (!empty($doc['file_path_gcs'])): ?>
                                            • <span style="color: #2563eb;"><?= e($doc['file_path_gcs']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span style="padding: 4px 12px; border-radius: 999px; font-size: 0.78rem; font-weight: 700; background: <?= $stBg ?>; color: <?= $stColor ?>;">
                                    <?= $stLabel ?>
                                </span>
                            </div>

                            <form action="<?= url('advisor/documents/' . $doc['id'] . '/review') ?>" method="POST" style="margin-top: 1rem; display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                                <?= CSRF::field() ?>
                                <input type="text" name="comment" placeholder="ความเห็นเกี่ยวกับเอกสารนี้ (ถ้ามี)..." style="flex: 1; min-width: 200px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.85rem;">
                                <button type="submit" name="review_status" value="approved" style="background: #16a34a; color: white; border: none; padding: 8px 14px; border-radius: 8px; font-size: 0.82rem; font-weight: 600; cursor: pointer;">✅ อนุมัติเอกสาร</button>
                                <button type="submit" name="review_status" value="revision_required" style="background: #f59e0b; color: white; border: none; padding: 8px 14px; border-radius: 8px; font-size: 0.82rem; font-weight: 600; cursor: pointer;">⚠️ ให้แก้ไข</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Column: Actions & Members -->
    <div>
        <div class="panel" style="margin-bottom: 2rem;">
            <div class="panel-title">👥 สมาชิกโครงงาน</div>
            <ul class="member-list">
                <?php foreach ($members as $mem): ?>
                    <li>
                        👤 <strong><?= htmlspecialchars($mem['full_name'] ?? $mem['username']) ?></strong>
                        <span class="member-role"><?= htmlspecialchars($mem['role_in_project']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="panel" style="border: 2px solid #e2e8f0;">
            <div class="panel-title">⚡ การพิจารณา (Action)</div>
            
            <?php if (Session::hasFlash('success')): ?>
                <div style="color:#16a34a; background:#f0fdf4; padding:1rem; border-radius:8px; margin-bottom:1rem; font-weight:600;">
                    ✅ <?= htmlspecialchars(Session::getFlash('success')) ?>
                </div>
            <?php endif; ?>

            <form action="<?= url('advisor/projects/' . $project['id'] . '/approve') ?>" method="POST">
                <?= CSRF::field() ?>
                
                <p style="font-size:0.9rem; color:#475569; margin-bottom:0.5rem;">ข้อเสนอแนะสำหรับนักเรียน (ถ้ามี)</p>
                <textarea name="feedback" class="comment-box" placeholder="พิมพ์ข้อเสนอแนะเพื่อให้เด็กนำไปปรับปรุงแก้ไข..."></textarea>
                
                <?php if ($project['status'] === 'proposal_submitted' || $project['status'] === 'proposal_revision'): ?>
                    <button type="submit" name="action" value="approve_proposal" class="btn-approve">✅ อนุมัติหัวข้อโครงงาน</button>
                    <button type="submit" name="action" value="revise_proposal" class="btn-revise">⚠️ ส่งกลับไปแก้ไข</button>
                    <button type="submit" name="action" value="reject_proposal" class="btn-reject" onclick="return confirm('ยืนยันประเมินตกสำหรับโครงงานนี้ ไม่สามารถย้อนกลับได้?')">❌ ไม่อนุมัติโครงงาน</button>
                <?php elseif ($project['status'] === 'proposal_approved'): ?>
                    <button type="submit" name="action" value="start_progress" class="btn-approve">▶️ กดเพื่อเริ่มกระบวนการ (In Progress)</button>
                    <button type="submit" name="action" value="complete_project" class="btn-approve" style="background:#0284c7;">🏆 รับรองโครงงานเสร็จสมบูรณ์ (Completed)</button>
                <?php elseif ($project['status'] === 'in_progress' || $project['status'] === 'chapter_review'): ?>
                    <button type="submit" name="action" value="complete_project" class="btn-approve" style="background:#0284c7;">🏆 รับรองโครงงานเสร็จสมบูรณ์ (Completed)</button>
                    <button type="submit" name="action" value="comment_only" class="btn-revise" style="background:#475569;">💬 บันทึกข้อเสนอแนะเพิ่มเติม</button>
                <?php else: ?>
                    <p style="text-align:center; color:#64748b; font-size:0.9rem; padding:1rem; background:#f1f5f9; border-radius:8px;">สถานะปัจจุบัน: <?= e($project['status']) ?></p>
                    <button type="submit" name="action" value="comment_only" class="btn-revise" style="background:#475569;">💬 เพิ่มความเห็นใหม่เท่านั้น</button>
                <?php endif; ?>
            </form>
        </div>
    </div>

</div>
