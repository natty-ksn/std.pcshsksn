<?php $pageTitle = 'เป้าหมาย'; ?>

<div style="max-width: 800px; margin: 0 auto;">
    <div class="flex-between mb-3" style="flex-wrap: wrap; gap: 12px;">
        <h2>🎯 เป้าหมายของฉัน</h2>
    </div>

    <!-- Stats -->
    <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
        <div class="stat-card purple">
            <div class="stat-card-value"><?= $stats['total'] ?></div>
            <div class="stat-card-label">เป้าหมายทั้งหมด</div>
        </div>
        <div class="stat-card orange">
            <div class="stat-card-value"><?= $stats['active'] ?></div>
            <div class="stat-card-label">กำลังดำเนินการ</div>
        </div>
        <div class="stat-card green">
            <div class="stat-card-value"><?= $stats['achieved'] ?></div>
            <div class="stat-card-label">บรรลุแล้ว 🎉</div>
        </div>
    </div>

    <!-- Comparative Analysis Chart (Success Intersection) -->
    <?php if ($riasec): ?>
        <div class="card mb-3 slide-up" style="border: 1px solid var(--primary-100); background: linear-gradient(to bottom right, white, #f8fafc);">
            <div class="card-header" style="background: transparent;">
                <h3 style="display: flex; align-items: center; gap: 10px;">
                    📊 แผนภาพวิเคราะห์ความสอดคล้องของเป้าหมายและผลงาน
                </h3>
            </div>
            <div class="card-body">
                <div class="grid-2" style="grid-template-columns: 1.2fr 0.8fr; gap: 32px; align-items: center;">
                    <div style="position: relative; height: 350px;">
                        <canvas id="triangulationChart"></canvas>
                    </div>
                    <div>
                        <h4 style="font-size: 1.1rem; color: var(--primary-800); margin-bottom: 18px;">💡 คำอธิบายองค์ประกอบแผนภาพ</h4>
                        <div style="display: flex; flex-direction: column; gap: 16px;">
                            <div style="display: flex; align-items: flex-start; gap: 12px;">
                                <div style="width: 16px; height: 16px; border-radius: 4px; background: rgba(79, 70, 229, 0.8); margin-top: 2px;"></div>
                                <div>
                                    <strong style="color: var(--gray-800);">🔵 พื้นที่สีน้ำเงิน: ความถนัดและความสนใจ (Potential)</strong>
                                    <p style="font-size: 0.8rem; color: var(--gray-600); margin-top: 2px;">สะท้อนคะแนนจากแบบประเมินความถนัด RIASEC แสดงจุดเด่นตามธรรมชาติของคุณในแต่ละมิติ</p>
                                </div>
                            </div>
                            <div style="display: flex; align-items: flex-start; gap: 12px;">
                                <div style="width: 16px; height: 16px; border-radius: 4px; background: rgba(16, 185, 129, 0.8); margin-top: 2px;"></div>
                                <div>
                                    <strong style="color: var(--gray-800);">🟢 พื้นที่สีเขียว: ผลงานและกิจกรรมสะสม (Reality)</strong>
                                    <p style="font-size: 0.8rem; color: var(--gray-600); margin-top: 2px;">สะท้อนผลงานและโครงงานที่คุณบันทึกไว้ในระบบ ยิ่งสะสมผลงานหลากหลาย พื้นที่ส่วนนี้จะยิ่งครอบคลุม</p>
                                </div>
                            </div>
                            <div style="display: flex; align-items: flex-start; gap: 12px;">
                                <div style="width: 16px; height: 16px; border-radius: 4px; border: 2px solid rgba(245, 158, 11, 1); background: transparent; margin-top: 2px;"></div>
                                <div>
                                    <strong style="color: var(--gray-800);">🟠 เส้นขอบสีส้ม: คุณลักษณะเป้าหมายที่คาดหวัง (Target)</strong>
                                    <p style="font-size: 0.8rem; color: var(--gray-600); margin-top: 2px;">เกณฑ์ที่แนะนำสำหรับสาขาวิชาที่คุณตั้งเป้าหมาย ช่วยให้เห็นว่าควรพัฒนาผลงานด้านใดเพิ่มเติม</p>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 24px; padding: 20px; background: linear-gradient(135deg, #fff, var(--primary-50)); border-radius: 16px; border: 1px solid var(--primary-100); font-size: 0.9rem; line-height: 1.6; color: var(--primary-900); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);">
                            🎯 <strong>แนวทางการพัฒนา:</strong> มุ่งสร้างผลงานและกิจกรรมเพื่อขยายพื้นที่สีเขียวให้สอดคล้องกับเส้นสีส้ม ซึ่งจะช่วยเพิ่มความพร้อมในการยื่นพอร์ตโฟลิโอตามเป้าหมายที่ตั้งไว้
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Add Goal Form (Redesigned) -->
    <div class="card mb-4 slide-up" style="border: none; border-radius: var(--radius-2xl); box-shadow: var(--shadow-lg); overflow: hidden;">
        <div style="background: linear-gradient(135deg, var(--primary-600), var(--primary-800)); padding: 24px 32px; color: white;">
            <h3 style="color: white; margin: 0; font-size: 1.3rem; display: flex; align-items: center; gap: 10px;">
                🎯 กำหนดเป้าหมายการศึกษาใหม่
            </h3>
            <p style="color: rgba(255,255,255,0.8); font-size: 0.85rem; margin: 6px 0 0 0;">
                กำหนดคณะ สาขาวิชา หรือมหาวิทยาลัยที่สนใจ เพื่อให้ระบบวิเคราะห์ความพร้อมและแนะนำการสะสมผลงาน
            </p>
        </div>
        <div class="card-body" style="padding: 32px;">
            <form method="POST" action="<?= url('goals/store') ?>">
                <?= CSRF::field() ?>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700; font-size: 0.85rem; margin-bottom: 8px;">
                            เป้าหมายหลัก <span class="required" style="color: var(--danger-500);">*</span>
                        </label>
                        <input type="text" name="target" class="form-control" 
                               placeholder="เช่น คณะแพทยศาสตร์ มหาวิทยาลัยขอนแก่น หรือ สถาปัตยกรรมศาสตร์ มจธ."
                               style="border-radius: var(--radius-lg); padding: 12px 16px;" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700; font-size: 0.85rem; margin-bottom: 8px;">
                            ประเภทเป้าหมาย <span class="required" style="color: var(--danger-500);">*</span>
                        </label>
                        <select name="goal_type" class="form-control" style="border-radius: var(--radius-lg); padding: 12px; height: 48px;" required>
                            <option value="university">🏫 คณะ / มหาวิทยาลัย</option>
                            <option value="major">📚 สาขาวิชาการเรียน</option>
                            <option value="career">💼 สายงาน / อาชีพในฝัน</option>
                            <option value="shortterm">📋 แผนการเรียนระยะสั้น</option>
                            <option value="longterm">🌟 แผนการเรียนระยะยาว</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" style="font-weight: 700; font-size: 0.85rem; margin-bottom: 8px;">รายละเอียดหรือเงื่อนไขเพิ่มเติม</label>
                    <textarea name="target_details" class="form-control" rows="2" 
                              placeholder="เช่น ต้องใช้เกรดเฉลี่ยสะสม 5 เทอม หรือผลคะแนนสอบเฉพาะทางรอบ Portfolio..."
                              style="border-radius: var(--radius-lg); padding: 12px 16px;"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 24px; align-items: end;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; background: var(--gray-50); padding: 16px; border-radius: var(--radius-xl); border: 1px solid var(--gray-100);">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-weight: 700; font-size: 0.8rem; margin-bottom: 6px; display: flex; align-items: center; gap: 4px;">
                                📈 เกณฑ์คะแนนเป้าหมาย
                            </label>
                            <input type="number" name="target_score" class="form-control" step="0.01"
                                   placeholder="เช่น 4.00 หรือ 10" style="border-radius: var(--radius-md); padding: 8px 12px; background: white;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-weight: 700; font-size: 0.8rem; margin-bottom: 6px; display: flex; align-items: center; gap: 4px;">
                                🚀 คะแนนเริ่มต้นปัจจุบัน
                            </label>
                            <input type="number" name="current_score" class="form-control" step="0.01"
                                   placeholder="เช่น 3.50 หรือ 2" style="border-radius: var(--radius-md); padding: 8px 12px; background: white;">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700; font-size: 0.85rem; margin-bottom: 8px; display: flex; align-items: center; gap: 4px;">
                            📅 กำหนดวันที่ต้องสำเร็จ
                        </label>
                        <input type="date" name="deadline" class="form-control" style="border-radius: var(--radius-lg); padding: 10px 14px; height: 48px;">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary" 
                            style="border-radius: var(--radius-full); padding: 12px 36px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                        🎯 บันทึกเป้าหมายการเรียน
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Goals List (Redesigned) -->
    <h3 style="margin-top: 32px; margin-bottom: 16px; font-size: 1.2rem; font-weight: 800; color: var(--gray-800);">
        📋 เป้าหมายและแผนปฏิบัติการสะสมผลงาน
    </h3>
    
    <?php if (empty($goals)): ?>
        <div class="card" style="border: none; border-radius: var(--radius-2xl); box-shadow: var(--shadow-sm); overflow: hidden;">
            <div class="empty-state" style="padding: 60px 20px;">
                <div class="empty-state-icon" style="font-size: 3.5rem; margin-bottom: 16px;">🎯</div>
                <h3 style="font-weight: 800;">ไม่มีเป้าหมายที่อยู่ระหว่างดำเนินการ</h3>
                <p style="color: var(--gray-500);">เริ่มวางแผนอนาคตเพื่อความสำเร็จและปักหมุดผลงานโดยการเพิ่มเป้าหมายแรกด้านบน</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($goals as $goal): ?>
            <?php 
            $statusLabels = ['planning' => '📋 วางแผน', 'inprogress' => '🔥 กำลังทำ', 'achieved' => '✅ สำเร็จ', 'cancelled' => '❌ ยกเลิก'];
            $statusColors = [
                'planning' => ['bg' => '#f3f4f6', 'text' => '#4b5563', 'border' => '#e5e7eb'],
                'inprogress' => ['bg' => '#eff6ff', 'text' => '#2563eb', 'border' => '#bfdbfe'],
                'achieved' => ['bg' => '#ecfdf5', 'text' => '#059669', 'border' => '#a7f3d0'],
                'cancelled' => ['bg' => '#fef2f2', 'text' => '#dc2626', 'border' => '#fecaca']
            ];
            $sc = $statusColors[$goal['status']] ?? $statusColors['planning'];
            ?>
            <div class="card mb-3 slide-up" style="border: 1px solid var(--gray-200); border-radius: var(--radius-2xl); box-shadow: var(--shadow-md); overflow: hidden;">
                <div class="card-body" style="padding: 28px;">
                    <!-- Card Header / Title -->
                    <div class="flex-between" style="flex-wrap: wrap; gap: 12px; margin-bottom: 20px; align-items: flex-start;">
                        <div style="flex: 1; min-width: 250px;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: var(--primary-600); background: var(--primary-50); padding: 4px 12px; border-radius: var(--radius-full);">
                                    <?php
                                    $typeLabels = ['university' => '🏫 คณะ/มหาวิทยาลัย', 'major' => '📚 สาขาวิชา', 'career' => '💼 อาชีพในฝัน', 'shortterm' => '📋 เป้าหมายระยะสั้น', 'longterm' => '🌟 เป้าหมายระยะยาว'];
                                    echo $typeLabels[$goal['goal_type']] ?? $goal['goal_type'];
                                    ?>
                                </span>
                                <?php if ($goal['deadline']): ?>
                                    <?php
                                    $daysLeft = (int) ((strtotime($goal['deadline']) - time()) / 86400);
                                    if ($daysLeft > 0): ?>
                                        <span style="font-size: 0.72rem; font-weight: 700; color: #d97706; background: #fffbeb; padding: 4px 10px; border-radius: var(--radius-full);">⏰ อีก <?= $daysLeft ?> วัน</span>
                                    <?php elseif ($daysLeft === 0): ?>
                                        <span style="font-size: 0.72rem; font-weight: 700; color: #dc2626; background: #fef2f2; padding: 4px 10px; border-radius: var(--radius-full); animation: pulse 2s infinite;">⏰ วันนี้!</span>
                                    <?php elseif ($goal['status'] !== 'achieved'): ?>
                                        <span style="font-size: 0.72rem; font-weight: 700; color: #dc2626; background: #fef2f2; padding: 4px 10px; border-radius: var(--radius-full);">⏰ เลยกำหนด <?= abs($daysLeft) ?> วัน</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <h3 style="font-size: 1.35rem; font-weight: 900; color: var(--gray-900); margin: 0; line-height: 1.3;">
                                <?= e($goal['target']) ?>
                            </h3>
                            <?php if ($goal['target_details']): ?>
                                <p style="font-size: 0.88rem; color: var(--gray-500); margin-top: 6px; line-height: 1.5;">
                                    <?= e($goal['target_details']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <span style="font-size: 0.8rem; font-weight: 800; background: <?= $sc['bg'] ?>; color: <?= $sc['text'] ?>; border: 1px solid <?= $sc['border'] ?>; padding: 6px 16px; border-radius: var(--radius-full); white-space: nowrap;">
                            <?= $statusLabels[$goal['status']] ?? $goal['status'] ?>
                        </span>
                    </div>

                    <!-- Progress Tracking Info -->
                    <?php if ($goal['target_score'] && $goal['current_score']): ?>
                        <?php $progress = min(100, ($goal['current_score'] / $goal['target_score']) * 100); ?>
                        <div style="background: var(--gray-50); padding: 16px 20px; border-radius: var(--radius-xl); border: 1px solid var(--gray-100); margin-bottom: 20px;">
                            <div class="flex-between" style="font-size: 0.82rem; font-weight: 700; color: var(--gray-700); margin-bottom: 8px;">
                                <span>📈 อัตราความสำเร็จเป้าหมาย</span>
                                <span style="color: var(--primary-700);"><?= number_format($progress, 0) ?>%</span>
                            </div>
                            <div class="progress-bar" style="height: 10px; background: var(--gray-200); border-radius: var(--radius-full); overflow: hidden;">
                                <div class="progress-bar-fill" style="width: <?= $progress ?>%; height: 100%; border-radius: var(--radius-full); background: linear-gradient(95deg, var(--primary-500), var(--primary-700));"></div>
                            </div>
                            <div style="font-size: 0.78rem; color: var(--gray-500); margin-top: 8px; display: flex; justify-content: space-between;">
                                <span>สถานะปัจจุบัน: <strong><?= number_format($goal['current_score'], 2) ?></strong></span>
                                <span>เป้าหมายที่ตั้งไว้: <strong><?= number_format($goal['target_score'], 2) ?></strong></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Heuristic AI Analysis Section -->
                    <?php if (isset($goal['analysis']) && $goal['analysis']): ?>
                        <?php 
                        $readiness = $goal['analysis']['readiness'];
                        $aiColor = $readiness >= 70 ? '#10b981' : ($readiness >= 40 ? '#f59e0b' : '#ef4444');
                        $aiBg = $readiness >= 70 ? '#ecfdf5' : ($readiness >= 40 ? '#fffbeb' : '#fef2f2');
                        $aiBorder = $readiness >= 70 ? '#a7f3d0' : ($readiness >= 40 ? '#fde68a' : '#fecaca');
                        ?>
                        <div style="background: <?= $aiBg ?>; border: 1px solid <?= $aiBorder ?>; border-radius: var(--radius-xl); padding: 20px; margin-bottom: 20px;">
                            <div class="flex-between" style="margin-bottom: 12px; align-items: flex-end; flex-wrap: wrap; gap: 8px;">
                                <div>
                                    <h4 style="color: var(--gray-900); font-weight: 800; font-size: 0.95rem; margin: 0; display: flex; align-items: center; gap: 6px;">
                                        🤖 ผลวิเคราะห์ความพร้อมโดย AI
                                    </h4>
                                    <span style="font-size: 0.8rem; color: var(--gray-500); display: inline-block; margin-top: 4px;">
                                        เปรียบเทียบกับเกณฑ์กลุ่ม: <strong><?= e($goal['analysis']['track_name']) ?></strong>
                                    </span>
                                </div>
                                <div style="text-align: right;">
                                    <span style="font-size: 0.75rem; color: var(--gray-500); display: block; margin-bottom: 2px;">เปอร์เซ็นต์ความพร้อมพอร์ตโฟลิโอ</span>
                                    <span style="font-weight: 900; color: <?= $aiColor ?>; font-size: 1.3rem;"><?= $readiness ?>%</span>
                                </div>
                            </div>
                            
                            <!-- Readiness Bar -->
                            <div class="progress-bar" style="height: 6px; background: rgba(0,0,0,0.06); border-radius: var(--radius-full); overflow: hidden; margin-bottom: 16px;">
                                <div class="progress-bar-fill" style="width: <?= $readiness ?>%; height: 100%; background: <?= $aiColor ?>; border-radius: var(--radius-full);"></div>
                            </div>

                            <!-- Recommendations -->
                            <?php if(!empty($goal['analysis']['recommendations'])): ?>
                                <div style="border-top: 1px dashed <?= $aiBorder ?>; padding-top: 12px; margin-top: 12px;">
                                    <span style="font-size: 0.78rem; font-weight: 800; color: var(--gray-700); display: block; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">📋 แผนปฏิบัติการที่แนะนำ (Recommended Actions):</span>
                                    <ul style="margin: 0; padding-left: 0; list-style-type: none; font-size: 0.85rem; color: var(--gray-700); display: flex; flex-direction: column; gap: 8px;">
                                        <?php foreach($goal['analysis']['recommendations'] as $rec): ?>
                                            <li style="display: flex; align-items: flex-start; gap: 8px;">
                                                <span style="color: <?= $aiColor ?>; font-weight: bold; margin-top: 2px;">•</span>
                                                <span style="line-height: 1.5;"><?= e($rec) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Action Bar / Quick Updates -->
                    <?php if ($goal['status'] !== 'achieved' && $goal['status'] !== 'cancelled'): ?>
                        <div style="border-top: 1px solid var(--gray-100); padding-top: 16px; margin-top: 16px; display: flex; gap: 12px; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                            <form method="POST" action="<?= url('goals/' . $goal['id'] . '/update') ?>" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                <?= CSRF::field() ?>
                                <input type="hidden" name="status" value="inprogress">
                                <?php if ($goal['target_score']): ?>
                                    <input type="number" name="current_score" placeholder="อัปเดตคะแนนสะสม..." step="0.01" required
                                           style="width: 160px; padding: 8px 12px; border: 1px solid var(--gray-200); border-radius: var(--radius-md); font-size: 0.82rem; height: 38px;">
                                <?php endif; ?>
                                <button type="submit" class="btn btn-sm btn-secondary" style="height: 38px; border-radius: var(--radius-md); padding: 0 16px; font-weight: 700;">
                                    📊 บันทึกคะแนน
                                </button>
                            </form>
                            
                            <div style="display: flex; gap: 8px;">
                                <form method="POST" action="<?= url('goals/' . $goal['id'] . '/update') ?>" style="display: inline;">
                                    <?= CSRF::field() ?>
                                    <input type="hidden" name="status" value="achieved">
                                    <button type="submit" class="btn btn-sm btn-success" style="border-radius: var(--radius-md); padding: 8px 16px; font-weight: 800;">
                                        🎉 พิชิตเป้าหมายสำเร็จ!
                                    </button>
                                </form>
                                <form method="POST" action="<?= url('goals/' . $goal['id'] . '/delete') ?>"
                                      onsubmit="return confirm('คุณแน่ใจว่าต้องการลบเป้าหมายนี้ใช่หรือไม่?')" style="display: inline;">
                                    <?= CSRF::field() ?>
                                    <button type="submit" class="btn btn-sm btn-danger" style="border-radius: var(--radius-md); padding: 8px 12px;">
                                        🗑️ ลบ
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if ($riasec): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Success Intersection Analysis Script ---
    const riasecRaw = <?= json_encode($riasec) ?>;
    const portfolioRaw = <?= json_encode($portfolioStats) ?>;
    
    // Mapping Portfolio Categories to RIASEC Concepts
    let reality = { R: 0, I: 0, A: 0, S: 0, E: 0, C: 0 };
    portfolioRaw.forEach(cat => {
        let name = cat.name.toLowerCase();
        let count = parseInt(cat.count);
        if (name.includes('วิทย์') || name.includes('โครงงาน')) reality.I += count;
        if (name.includes('อาสา') || name.includes('สังคม')) reality.S += count;
        if (name.includes('ศิลปะ') || name.includes('ภาษา')) reality.A += count;
        if (name.includes('ผู้') || name.includes('กิจกรรม')) reality.E += count;
        if (name.includes('วิชา') || name.includes('it') || name.includes('คอม')) reality.C += count;
        if (name.includes('กีฬา') || name.includes('ปฏิบัติ')) reality.R += count;
    });

    <?php 
    $activeTrack = 'none';
    foreach($goals as $g) {
        if($g['status'] === 'inprogress' && isset($g['analysis']['track'])) {
            $activeTrack = $g['analysis']['track'];
            break;
        }
    }
    ?>
    
    const activeTrack = "<?= $activeTrack ?>";
    let dream = { R: 5, I: 5, A: 5, S: 5, E: 5, C: 5 }; // Default (General)
    
    if (activeTrack === 'med')  dream = { R: 3, I: 9, A: 2, S: 9, E: 4, C: 6 };
    else if (activeTrack === 'eng')  dream = { R: 8, I: 9, A: 4, S: 2, E: 6, C: 8 };
    else if (activeTrack === 'sci')  dream = { R: 4, I: 10, A: 3, S: 4, E: 4, C: 7 };
    else if (activeTrack === 'art')  dream = { R: 2, I: 4, A: 10, S: 7, E: 5, C: 4 };
    else if (activeTrack === 'general') dream = { R: 6, I: 6, A: 6, S: 6, E: 6, C: 6 };

    const labels = [
        'นักปฏิบัติ (Realistic)', 
        'นักวิเคราะห์ (Investigative)', 
        'นักสร้างสรรค์ (Artistic)', 
        'นักบริการ (Social)', 
        'นักบริหาร (Enterprising)', 
        'นักจัดการ (Conventional)'
    ];
    
    try {
        new Chart(document.getElementById('triangulationChart'), {
            type: 'radar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'ศักยภาพ (จากแบบทดสอบ)',
                        data: [riasecRaw.R, riasecRaw.I, riasecRaw.A, riasecRaw.S, riasecRaw.E, riasecRaw.C],
                        fill: true,
                        backgroundColor: 'rgba(79, 70, 229, 0.2)',
                        borderColor: 'rgb(79, 70, 229)',
                        pointBackgroundColor: 'rgb(79, 70, 229)',
                        borderWidth: 2
                    },
                    {
                        label: 'ผลงานจริง (ในระบบ)',
                        data: [reality.R*2, reality.I*2, reality.A*2, reality.S*2, reality.E*2, reality.C*2],
                        fill: true,
                        backgroundColor: 'rgba(16, 185, 129, 0.2)',
                        borderColor: 'rgb(16, 185, 129)',
                        pointBackgroundColor: 'rgb(16, 185, 129)',
                        borderDash: [5, 5],
                        borderWidth: 2
                    },
                    {
                        label: 'ความต้องการของเป้าหมาย',
                        data: [dream.R, dream.I, dream.A, dream.S, dream.E, dream.C],
                        fill: false,
                        borderColor: 'rgb(245, 158, 11)',
                        pointBackgroundColor: 'rgb(245, 158, 11)',
                        borderWidth: 3,
                        pointRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: { color: '#e5e7eb' },
                        grid: { color: '#e5e7eb' },
                        suggestedMin: 0,
                        suggestedMax: 10,
                        ticks: { display: false }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    } catch(err) {
        console.error("Dashboard Chart Error:", err);
    }
});
</script>
<?php endif; ?>