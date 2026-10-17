<?php $pageTitle = e($work['title']); ?>

<div class="animate-fade-in" style="max-width: 1000px; margin: 0 auto;">
    <div style="margin-bottom: 30px;">
        <a href="<?= url('portfolio') ?>" class="btn btn-secondary btn-sm"
            style="border-radius: var(--radius-full); padding: 8px 20px; font-weight: 700; background: white; color: var(--gray-700);">
            ← Back to Archive
        </a>
    </div>

    <!-- Magazine Feature Header -->
    <div class="card mb-5 glass-card"
        style="border: none; overflow: hidden; box-shadow: var(--shadow-2xl); border-radius: var(--radius-2xl);">
        <!-- Hero Section -->
        <?php if ($work['file_path']): ?>
            <?php
            $ext = pathinfo($work['file_path'], PATHINFO_EXTENSION);
            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
            $workFileUrl = uploadUrl(str_starts_with($work['file_path'], 'portfolio/') ? $work['file_path'] : 'portfolio/' . $work['file_path']);
            ?>
            <?php if ($isImage): ?>
                <div
                    style="width: 100%; max-height: 600px; overflow: hidden; background: #000; display: flex; align-items: center; justify-content: center; position: relative;">
                    <img src="<?= $workFileUrl ?>" alt="<?= e($work['title']) ?>"
                        style="width: 100%; height: auto; object-fit: cover;">
                    <div
                        style="position: absolute; bottom: 0; left: 0; right: 0; height: 150px; background: linear-gradient(transparent, rgba(0,0,0,0.8));">
                    </div>
                </div>
            <?php else: ?>
                <div
                    style="padding: 60px 24px; background: linear-gradient(135deg, var(--primary-800), var(--primary-900)); text-align: center; color: white;">
                    <div style="font-size: 4rem; margin-bottom: 20px;">📄</div>
                    <h3 style="color: white; margin-bottom: 20px;">Digital Document Attachment</h3>
                    <a href="<?= $workFileUrl ?>" target="_blank" class="btn btn-primary btn-lg"
                        style="border-radius: var(--radius-full); background: white; color: var(--primary-800); border: none;">
                        Download Full Document (<?= strtoupper($ext) ?>)
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="card-body" style="padding: 50px;">
            <!-- Category & Metadata Grid -->
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-4 mb-5">
                <div style="flex: 1; min-width: 300px;">
                    <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;">
                        <span
                            style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; color: <?= e($work['category_color']) ?>; border: 1px solid <?= e($work['category_color']) ?>; padding: 5px 15px; border-radius: var(--radius-full);">
                            <?= e($work['category_icon']) ?> <?= e($work['category_name']) ?>
                        </span>
                        <?php if ($work['achievement_level']): ?>
                            <div style="transform: scale(0.9); transform-origin: left;">
                                <?= achievementBadge($work['achievement_level']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <h1
                        style="font-size: 3rem; font-weight: 900; line-height: 1.1; margin-bottom: 25px; color: var(--gray-900); letter-spacing: -1.5px;">
                        <?= e($work['title']) ?>
                    </h1>

                    <?php if ($work['award']): ?>
                        <div
                            style="display: inline-block; background: #fff7ed; border-left: 4px solid #f97316; padding: 12px 20px; border-radius: 0 12px 12px 0; margin-bottom: 25px;">
                            <span style="color: #9a3412; font-weight: 800; font-size: 1.1rem;">🏆
                                <?= e($work['award']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="glass-card"
                    style="width: 280px; padding: 25px; border-radius: 20px; background: rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.05); flex-shrink: 0;">
                    <h4
                        style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--gray-400); margin-bottom: 15px;">
                        Contribution Info</h4>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <div>
                            <small
                                style="display: block; font-size: 0.65rem; color: var(--gray-400); text-transform: uppercase; font-weight: 700;">Student
                                Author</small>
                            <span style="font-weight: 700; color: var(--gray-900);"><?= e($work['full_name']) ?></span>
                        </div>
                        <div>
                            <small
                                style="display: block; font-size: 0.65rem; color: var(--gray-400); text-transform: uppercase; font-weight: 700;">Class
                                / Section</small>
                            <span
                                style="font-weight: 700; color: var(--gray-900);"><?= classYearLabel($work['class_year']) ?>/<?= e($work['class_room']) ?></span>
                        </div>
                        <div>
                            <small
                                style="display: block; font-size: 0.65rem; color: var(--gray-400); text-transform: uppercase; font-weight: 700;">Date
                                of Record</small>
                            <span
                                style="font-weight: 700; color: var(--gray-900);"><?= $work['event_date'] ? thaiDate($work['event_date'], 'long') : timeAgo($work['created_at']) ?></span>
                        </div>
                        <div
                            style="display: flex; align-items: center; gap: 8px; margin-top: 5px; color: var(--gray-400); font-size: 0.8rem;">
                            <span>👁️ <?= $work['view_count'] ?> views</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Body -->
            <style>
                .portfolio-show-grid {
                    display: grid;
                    grid-template-columns: 1fr 300px;
                    gap: 40px;
                }
                @media (max-width: 992px) {
                    .portfolio-show-grid {
                        grid-template-columns: 1fr;
                        gap: 24px;
                    }
                }
            </style>
            <div class="portfolio-show-grid">
                <div style="font-family: 'Inter', system-ui, -apple-system, sans-serif;">
                    <div
                        style="line-height: 2; color: var(--gray-800); font-size: 1.1rem; white-space: pre-wrap; margin-bottom: 40px; text-align: justify;">
                        <?= nl2br(e($work['description'])) ?>
                    </div>

                </div>

                <div style="display: flex; flex-direction: column; gap: 30px;">
                    <!-- Verification Status Badge -->
                    <?php if (isset($work['verification_status'])): ?>
                        <div class="glass-card"
                            style="padding: 20px; border-radius: 15px; border: 1px solid rgba(0,0,0,0.05);">
                            <h4
                                style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--gray-400); margin-bottom: 12px;">
                                Verification Status</h4>
                            <?php if ($work['verification_status'] === 'verified'): ?>
                                <div
                                    style="color: #166534; background: #dcfce7; padding: 10px; border-radius: 10px; font-weight: 700; font-size: 0.9rem; text-align: center;">
                                    ✅ Verifed by Institution</div>
                            <?php elseif ($work['verification_status'] === 'rejected'): ?>
                                <div
                                    style="color: #991b1b; background: #fee2e2; padding: 10px; border-radius: 10px; font-weight: 700; font-size: 0.9rem; text-align: center;">
                                    ❌ Action Required</div>
                            <?php else: ?>
                                <div
                                    style="color: #4b5563; background: #f3f4f6; padding: 10px; border-radius: 10px; font-weight: 700; font-size: 0.9rem; text-align: center;">
                                    ⏳ Pending Review</div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Skills & Tags -->
                    <div class="glass-card" style="padding: 25px; border-radius: 20px;">
                        <h4
                            style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--gray-400); margin-bottom: 15px;">
                            Knowledge & Skills</h4>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px;">
                            <?php foreach ($skills as $skill): ?>
                                <span
                                    style="background: var(--primary-500); color: white; font-size: 0.7rem; font-weight: 700; padding: 4px 12px; border-radius: 50px;">
                                    <?= e($skill['skill_name']) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>

                        <?php $tags = json_decode($work['tags'] ?? '[]', true); ?>
                        <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                            <?php foreach ($tags as $tag): ?>
                                <span
                                    style="color: var(--gray-400); font-size: 0.75rem; font-weight: 500;">#<?= e($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Collaborator Status (Accept/Reject Invitation) -->
                    <?php
                    $profileCheck = Database::fetch("SELECT id FROM student_profiles WHERE user_id = ?", [Auth::id()]);
                    $myInvitation = Database::fetch(
                        "SELECT * FROM portfolio_collaborators WHERE portfolio_id = ? AND (student_id = ? OR student_id = ?) AND acceptance_status = 'pending'",
                        [$work['id'], $profileCheck['id'] ?? 0, Auth::id()]
                    );
                    ?>
                    <?php if ($myInvitation): ?>
                        <div class="glass-card"
                            style="padding: 25px; border-radius: 20px; border: 2px solid var(--primary-200); background: var(--primary-50);">
                            <h4
                                style="font-size: 0.9rem; font-weight: 800; color: var(--primary-700); margin-bottom: 12px;">
                                📣 คุณถูกเชิญร่วมผลงานกลุ่ม</h4>
                            <p
                                style="font-size: 0.85rem; color: var(--primary-600); margin-bottom: 15px; line-height: 1.5;">
                                คุณต้องการให้ผลงานนี้แสดงในหน้า Portfolio ของคุณด้วยหรือไม่?
                            </p>
                            <div style="display: flex; gap: 8px;">
                                <form method="POST"
                                    action="<?= url('portfolio/collaborate/' . $myInvitation['id'] . '/accept') ?>"
                                    style="flex: 1;">
                                    <?= CSRF::field() ?>
                                    <button type="submit" class="btn btn-primary"
                                        style="width: 100%; border-radius: 10px; font-weight: 700; padding: 8px;">✅
                                        ตกลง</button>
                                </form>
                                <form method="POST"
                                    action="<?= url('portfolio/collaborate/' . $myInvitation['id'] . '/reject') ?>"
                                    style="flex: 1;">
                                    <?= CSRF::field() ?>
                                    <button type="submit" class="btn btn-outline-danger"
                                        style="width: 100%; border-radius: 10px; font-weight: 700; padding: 8px; background: white;">❌
                                        ไม่ใช่</button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Collaborators -->
                    <?php if (!empty($collaborators)): ?>
                        <div class="glass-card" style="padding: 25px; border-radius: 20px;">
                            <h4
                                style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--gray-400); margin-bottom: 15px;">
                                Project Team</h4>
                            <?php foreach ($collaborators as $collab): ?>
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                                    <div
                                        style="width: 32px; height: 32px; border-radius: 50%; background: var(--gray-100); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 800; color: var(--gray-500);">
                                        <?= mb_substr($collab['full_name'], 0, 1) ?>
                                    </div>
                                    <div style="flex: 1;">
                                        <div
                                            style="font-size: 0.85rem; font-weight: 700; color: var(--gray-900); display: flex; align-items: center; gap: 5px;">
                                            <?= e($collab['full_name']) ?>
                                            <?php if ($collab['acceptance_status'] === 'accepted'): ?>
                                                <span title="ยืนยันแล้ว" style="font-size: 0.7rem;">✅</span>
                                            <?php elseif ($collab['acceptance_status'] === 'pending'): ?>
                                                <span title="รอการยืนยัน" style="font-size: 0.7rem; filter: grayscale(1);">⏳</span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-size: 0.65rem; color: var(--gray-400);">
                                            <?= e($collab['role_in_project'] ?? 'Teammate') ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- External Links (Full Width) -->
            <?php 
            $extLink = trim($work['external_link'] ?? '');
            $isSafeLink = preg_match('#^https?://#i', $extLink);
            ?>
            <?php if (!empty($extLink) && $isSafeLink): ?>
                <div
                    style="margin-top: 40px; padding: 30px; background: var(--gray-900); color: white; border-radius: 24px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h4 style="color: white; font-size: 1.2rem; margin-bottom: 5px;">Reference Assets Detected</h4>
                        <p style="color: var(--gray-400); font-size: 0.9rem; margin-bottom: 0;">Access additional data,
                            high-resolution media, or project source code via the cloud link.</p>
                    </div>
                    <a href="<?= e($extLink) ?>" target="_blank" rel="noopener noreferrer"
                        class="btn btn-primary btn-lg" style="border-radius: var(--radius-full); padding: 12px 30px;">
                        🔗 Explore External Data
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <?php
        $profileCheck = Database::fetch("SELECT id FROM student_profiles WHERE user_id = ?", [Auth::id()]);
        $isOwner = $profileCheck && Database::fetch("SELECT id FROM portfolio_items WHERE id = ? AND student_id = ?", [$work['id'], $profileCheck['id']]);
        ?>
        <?php if ($isOwner): ?>
            <div class="card-footer" style="display: flex; gap: 8px; flex-wrap: wrap;">
                <a href="<?= url('portfolio/' . $work['id'] . '/edit') ?>" class="btn btn-sm btn-outline">✏️ แก้ไข</a>
                <form method="POST" action="<?= url('portfolio/' . $work['id'] . '/feature') ?>" style="display:inline;">
                    <?= CSRF::field() ?>
                    <button type="submit" class="btn btn-sm btn-secondary">
                        <?= $work['is_featured'] ? '⭐ ยกเลิกปักหมุด' : '⭐ ปักหมุดผลงานเด่น' ?>
                    </button>
                </form>
                <form method="POST" action="<?= url('portfolio/' . $work['id'] . '/delete') ?>"
                    onsubmit="return confirm('ต้องการลบผลงานนี้หรือไม่?')" style="display:inline; margin-left: auto;">
                    <?= CSRF::field() ?>
                    <button type="submit" class="btn btn-sm btn-danger">🗑️ ลบ</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- Annotated Mentorship Feedback (ข้อเสนอแนะเฉพาะจุดจากครู) -->
    <div class="card slide-up mb-4" id="annotations">
        <div class="card-header"
            style="background: linear-gradient(135deg, #1e293b, #0f172a); color: #fff; border-radius: var(--radius-xl) var(--radius-xl) 0 0;">
            <h3 style="color: #fff; margin: 0;">📝 ข้อเสนอแนะเฉพาะจุดจากครู (Annotated Feedback)</h3>
            <div style="font-size: 0.8rem; color: #cbd5e1; margin-top: 4px;">ระบบนี้ครูสามารถระบุจุดผิดเฉพาะในผลงาน
                เพื่อให้นักเรียนแก้ไขได้อย่างตรงจุด</div>
        </div>
        <div class="card-body">
            <?php
            $sevMap = [
                'critical' => ['#7f1d1d', '#fee2e2', '🚨', 'วิกฤติ'],
                'issue' => ['#9a3412', '#ffedd5', '⚠️', 'จุดที่ควรแก้'],
                'suggestion' => ['#1e40af', '#dbeafe', '💡', 'ข้อเสนอแนะ'],
                'info' => ['#1f2937', '#f3f4f6', 'ℹ️', 'ข้อมูล'],
            ];
            ?>

            <?php if (empty($annotations ?? [])): ?>
                <p class="text-muted text-center" style="padding: 20px;">ยังไม่มีข้อเสนอแนะเฉพาะจุด</p>
            <?php else: ?>
                <div style="display: grid; gap: 12px;">
                    <?php foreach ($annotations as $ann): ?>
                        <?php $sev = $sevMap[$ann['severity']] ?? $sevMap['info']; ?>
                        <div
                            style="border-left: 5px solid <?= $sev[0] ?>; background: <?= $sev[1] ?>; padding: 14px 18px; border-radius: 12px; <?= $ann['is_resolved'] ? 'opacity: 0.55;' : '' ?>">
                            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                <div style="flex: 1;">
                                    <div
                                        style="font-size: 0.7rem; font-weight: 800; color: <?= $sev[0] ?>; text-transform: uppercase; letter-spacing: 1px;">
                                        <?= $sev[2] ?>         <?= $sev[3] ?> · <?= e($ann['target_field']) ?>
                                        <?php if ($ann['is_resolved']): ?>
                                            <span
                                                style="margin-left: 8px; background: #166534; color: #fff; padding: 2px 8px; border-radius: 50px;">✓
                                                แก้ไขแล้ว</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($ann['snippet'])): ?>
                                        <div
                                            style="margin-top: 6px; font-style: italic; color: var(--gray-600); font-size: 0.85rem; border-left: 2px dashed <?= $sev[0] ?>; padding-left: 10px;">
                                            "<?= e(mb_substr($ann['snippet'], 0, 200)) ?><?= mb_strlen($ann['snippet']) > 200 ? '…' : '' ?>"
                                        </div>
                                    <?php endif; ?>
                                    <div style="margin-top: 8px; color: var(--gray-900); font-size: 0.95rem; line-height: 1.6;">
                                        <?= nl2br(e($ann['comment'])) ?>
                                    </div>
                                    <div style="margin-top: 6px; font-size: 0.75rem; color: var(--gray-500);">
                                        — ครู <?= e($ann['teacher_name']) ?> · <?= timeAgo($ann['created_at']) ?>
                                    </div>
                                </div>
                                <?php if (!$ann['is_resolved']): ?>
                                    <form method="POST" action="<?= url('teacher/annotation/' . $ann['id'] . '/resolve') ?>">
                                        <?= CSRF::field() ?>
                                        <button type="submit" class="btn btn-sm btn-secondary" style="border-radius: 50px;">✓
                                            แก้ไขแล้ว</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (Auth::hasRole('teacher') || Auth::hasRole('admin')): ?>
                <hr style="margin: 24px 0; border: none; border-top: 1px dashed var(--gray-200);">
                <h4 id="annotation-form" style="color: var(--primary-700); margin-bottom: 16px;">✏️ เขียนข้อเสนอแนะเฉพาะจุด
                </h4>
                <form method="POST" action="<?= url('teacher/portfolio/' . $work['id'] . '/annotate') ?>"
                    style="background: white; border: 1px solid var(--primary-200); border-radius: 12px; padding: 20px;">
                    <?= CSRF::field() ?>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                        <div>
                            <label
                                style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 0.85rem;">จุดที่ต้องการเจาะจง</label>
                            <select name="target_field" class="form-control">
                                <option value="general">ภาพรวม</option>
                                <option value="title">หัวเรื่อง</option>
                                <option value="description">คำอธิบาย / บทคัดย่อ</option>
                                <option value="thumbnail">ภาพประกอบ / Thumbnail</option>
                                <option value="file">ไฟล์แนบ</option>
                                <option value="tags">แท็ก / คำสำคัญ</option>
                                <option value="category">หมวดหมู่</option>
                                <option value="award">ระดับรางวัล</option>
                            </select>
                        </div>
                        <div>
                            <label
                                style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 0.85rem;">ระดับความสำคัญ</label>
                            <select name="severity" class="form-control">
                                <option value="suggestion">💡 ข้อเสนอแนะ</option>
                                <option value="info">ℹ️ ข้อมูลเพิ่มเติม</option>
                                <option value="issue">⚠️ จุดที่ควรแก้</option>
                                <option value="critical">🚨 วิกฤติ ต้องแก้ทันที</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-bottom: 14px;">
                        <label
                            style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 0.85rem;">คัดลอกข้อความที่ต้องการอ้างถึง
                            (ถ้ามี)</label>
                        <input type="text" name="snippet" class="form-control"
                            placeholder="วางข้อความบางส่วนของผลงานที่กำลังพูดถึง...">
                    </div>
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 0.85rem;">ข้อเสนอแนะ
                            <span style="color:#dc2626;">*</span></label>
                        <textarea name="comment" class="form-control" rows="3" required
                            placeholder="ระบุข้อสังเกต/ข้อแก้ไขในจุดนี้อย่างชัดเจน..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="border-radius: 8px;">📌
                        บันทึกข้อเสนอแนะเฉพาะจุด</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Teacher Comments -->
    <div class="card slide-up">
        <div class="card-header">
            <h3>💬 ความคิดเห็นจากครู</h3>
        </div>
        <div class="card-body">
            <?php if (empty($comments)): ?>
                <p class="text-muted text-center" style="padding: 20px;">ยังไม่มีความคิดเห็น</p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div
                        style="padding: 12px; border-left: 3px solid var(--primary-300); margin-bottom: 12px; background: var(--gray-50); border-radius: 0 var(--radius-md) var(--radius-md) 0;">
                        <div style="font-weight: 600; font-size: 0.85rem; color: var(--primary-700); margin-bottom: 4px;">
                            👩‍🏫
                            <?= e($comment['teacher_name']) ?>
                            <span style="font-weight: 400; color: var(--gray-400);"> •
                                <?= timeAgo($comment['created_at']) ?>
                            </span>
                        </div>
                        <p style="font-size: 0.9rem; color: var(--gray-700);">
                            <?= nl2br(e($comment['comment'])) ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (Auth::hasRole('teacher') || Auth::hasRole('admin')): ?>
                <hr style="margin: 24px 0; border: none; border-top: 1px dashed var(--gray-200);">

                <h4 style="color: var(--primary-700); margin-bottom: 16px;">☑️ ระบบตั้งรับรองผลงานมาตรฐาน (Standardized
                    Verification)</h4>
                <div
                    style="background: white; border: 1px solid var(--primary-200); border-radius: 12px; padding: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.02);">
                    <form method="POST" action="<?= url('teacher/portfolio/' . $work['id'] . '/verify') ?>">
                        <?= CSRF::field() ?>

                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px;">ผลการรับรอง (Verification
                                Result)</label>
                            <label
                                style="display: inline-flex; align-items: center; gap: 8px; margin-right: 16px; cursor: pointer;">
                                <input type="radio" name="verification_status" value="verified" required
                                    <?= (isset($work['verification_status']) && $work['verification_status'] === 'verified') ? 'checked' : '' ?>>
                                <span style="color: #166534; font-weight: 600;">✅ ผ่านการรับรอง (Verified)</span>
                            </label>
                            <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="verification_status" value="rejected" required
                                    <?= (isset($work['verification_status']) && $work['verification_status'] === 'rejected') ? 'checked' : '' ?>>
                                <span style="color: #991b1b; font-weight: 600;">❌ ไม่ผ่าน (Rejected/Needs Work)</span>
                            </label>
                        </div>

                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px;">คะแนนประเมิน (Rubrics
                                Score)</label>
                            <input type="number" name="points_awarded" class="form-control" placeholder="0 - 100" min="0"
                                max="100" style="max-width: 200px;" value="<?= $work['points_awarded'] ?? '' ?>">
                            <small class="text-muted text-sm mt-1">ให้คะแนนความสมบูรณ์ของผลงาน โดยอิงตาม Rubrics
                                มาตรฐานของโรงเรียน</small>
                        </div>

                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px;">ข้อเสนอแนะเพิ่มเติม
                                (Feedback)</label>
                            <textarea name="comment" class="form-control" rows="3"
                                placeholder="ระบุเหตุผล หรือข้อเสนอแนะในการปรับปรุงข้อบกพร่อง..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; border-radius: 8px;">✔️
                            บันทึกการรับรองผลงาน</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>