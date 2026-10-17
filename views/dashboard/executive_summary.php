<?php $pageTitle = 'บทสรุปผู้บริหาร — PCSHSKSN'; ?>

<div class="mb-5">
    <div class="flex-between align-start" style="border-bottom: 2px solid var(--primary-800); padding-bottom: 20px;">
        <div>
            <h1 style="color: var(--primary-800); font-weight: 800; font-size: 2.2rem; margin-bottom: 8px; letter-spacing: -0.5px;">📋 บทสรุปเชิงรวบยอดสำหรับการบริหารจัดการ</h1>
            <p style="color: var(--gray-600); font-size: 1.1rem; font-weight: 500;">
                <span style="color: var(--primary-600);">●</span> รายงานสถานะและผลสัมฤทธิ์เชิงยุทธศาสตร์ ประจำวันที่ <?= thaiDate(date('Y-m-d'), 'full') ?>
            </p>
        </div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <button onclick="window.print()" class="btn btn-secondary" style="padding: 10px 20px; font-weight: 600;">🖨️ พิมพ์รายงานราชการ</button>
            <a href="<?= url('dashboard') ?>" class="btn btn-primary" style="padding: 10px 20px; font-weight: 600;">← แดชบอร์ดหลัก</a>
        </div>
    </div>
</div>

<!-- Executive Metrics Grid -->
<div class="stats-grid mb-5" style="gap: 24px;">
    <div class="stat-card" style="padding: 24px; border: 1px solid var(--gray-200); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <div class="stat-icon" style="background: var(--blue-50); color: var(--blue-700); font-size: 1.5rem; width: 56px; height: 56px;">👩‍🎓</div>
        <div class="stat-info">
            <span class="stat-label" style="font-size: 0.9rem; color: var(--gray-500); font-weight: 600;">จำนวนนักเรียนรวมทั้งสิ้น</span>
            <span class="stat-value" style="font-size: 2rem; color: var(--gray-900);"><?= number_format($stats['total_students']) ?></span>
            <span class="stat-trend text-sm" style="color: var(--gray-400);">ราย (Person)</span>
        </div>
    </div>
    <div class="stat-card" style="padding: 24px; border: 1px solid var(--gray-200); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <div class="stat-icon" style="background: var(--indigo-50); color: var(--indigo-700); font-size: 1.5rem; width: 56px; height: 56px;">📁</div>
        <div class="stat-info">
            <span class="stat-label" style="font-size: 0.9rem; color: var(--gray-500); font-weight: 600;">รายการผลการสะสมงานรวม</span>
            <span class="stat-value" style="font-size: 2rem; color: var(--gray-900);"><?= number_format($stats['total_works']) ?></span>
            <span class="stat-trend text-sm" style="color: var(--gray-400);">รายการ (Entries)</span>
        </div>
    </div>
    <div class="stat-card" style="padding: 24px; border: 1px solid var(--gray-200); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <div class="stat-icon" style="background: var(--amber-50); color: var(--amber-700); font-size: 1.5rem; width: 56px; height: 56px;">🌟</div>
        <div class="stat-info">
            <span class="stat-label" style="font-size: 0.9rem; color: var(--gray-500); font-weight: 600;">รางวัลเชิดชูเกียรติเป็นเลิศ</span>
            <span class="stat-value" style="font-size: 2rem; color: var(--amber-600);"><?= number_format($stats['excellence_awards']) ?></span>
            <span class="stat-trend text-amber-700 text-sm" style="font-weight: 700;">เกียรติประวัติสูงสุด</span>
        </div>
    </div>
    <div class="stat-card" style="padding: 24px; border: 1px solid var(--red-50); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <div class="stat-icon" style="background: #fee2e2; color: #dc2626; font-size: 1.5rem; width: 56px; height: 56px;">⚠️</div>
        <div class="stat-info">
            <span class="stat-label" style="font-size: 0.9rem; color: var(--gray-500); font-weight: 600;">นร. กลุ่มเป้าหมายเฝ้าระวัง</span>
            <span class="stat-value" style="font-size: 2rem; color: #b91c1c;"><?= number_format($stats['at_risk_count']) ?></span>
            <span class="stat-trend text-danger text-sm" style="font-weight: 700;">ติดตามเร่งด่วน (EWS)</span>
        </div>
    </div>
</div>

<div class="grid" style="grid-template-columns: 1.8fr 1.2fr; gap: 32px;">
    <!-- Excellence Registry -->
    <div class="card" style="border-radius: 16px; border: 1px solid var(--gray-200); overflow: hidden;">
        <div class="card-header flex-between" style="padding: 20px 24px; background: #fafafa;">
            <h3 style="color: var(--primary-800); font-weight: 700;">🏆 บัญชีรายชื่อนักเรียนที่มีผลงานเป็นเลิศ (Excellence Registry)</h3>
            <a href="<?= url('executive/excellence') ?>" class="text-sm text-primary font-bold">ข้อมูลฉบับเต็ม →</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f1f5f9;">
                    <tr>
                        <th style="padding: 16px 24px; text-align: left; color: var(--gray-600); font-size: 0.85rem;">ประเภท</th>
                        <th style="padding: 16px 24px; text-align: left; color: var(--gray-600); font-size: 0.85rem;">นามนักเรียน</th>
                        <th style="padding: 16px 24px; text-align: left; color: var(--gray-600); font-size: 0.85rem;">รายการความสำเร็จ</th>
                        <th style="padding: 16px 24px; text-align: left; color: var(--gray-600); font-size: 0.85rem;">ระดับรางวัล</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($hallOfFame)): ?>
                        <tr><td colspan="4" style="padding: 40px; text-align: center; color: var(--gray-400);">ไมพบข้อมูลรายการเป็นเลิศในขณะนี้</td></tr>
                    <?php endif; ?>
                    <?php foreach ($hallOfFame as $award): ?>
                        <tr style="border-bottom: 1px solid var(--gray-100);">
                            <td style="padding: 16px 24px;">
                                <span class="badge" style="background: <?= $award['achievement_level'] == 'international' ? '#fef3c7' : '#e0e7ff' ?>; color: var(--gray-800); border-radius: 4px; padding: 4px 8px; font-weight: 700; font-size: 0.75rem;">
                                    <?= strtoupper(substr($award['achievement_level'], 0, 4)) ?>
                                </span>
                            </td>
                            <td style="padding: 16px 24px;">
                                <div style="font-weight: 700; color: var(--gray-800);"><?= e($award['full_name']) ?></div>
                                <div style="font-size: 0.75rem; color: var(--gray-500);">ชั้นมัธยมศึกษาปีที่ <?= e($award['class_year']) ?></div>
                            </td>
                            <td style="padding: 16px 24px; color: var(--gray-700); font-size: 0.9rem;" title="<?= e($award['title']) ?>">
                                <?= e(truncate($award['award'] ?: $award['title'], 45)) ?>
                            </td>
                            <td style="padding: 16px 24px; font-weight: 600;">
                                <?php 
                                    if($award['achievement_level'] == 'international') echo '<span style="color: #d97706;">🌍 นานาชาติ</span>'; 
                                    else echo '<span style="color: var(--primary-700);">🇹🇭 ระดับชาติ</span>'; 
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Academic Performance Status -->
    <div class="card" style="border-radius: 16px; border: 1px solid var(--gray-200);">
        <div class="card-header" style="padding: 20px 24px; background: #fafafa;">
            <h3 style="color: var(--primary-800); font-weight: 700;">📊 สถานะผลสัมฤทธิ์ทางการเรียน (Academic Performance)</h3>
        </div>
        <div class="card-body" style="padding: 24px;">
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <?php foreach ($gradeDist as $grade): ?>
                    <div>
                        <div class="flex-between mb-2">
                            <span style="font-size: 0.95rem; font-weight: 700; color: var(--gray-700);">ชั้นมัธยมศึกษาปีที่ <?= $grade['class_year'] ?></span>
                            <span style="font-size: 1.1rem; font-weight: 800; color: var(--primary-700);"><?= number_format($grade['avg_grade'], 2) ?></span>
                        </div>
                        <div class="progress-bar" style="height: 12px; background: #f1f5f9; border-radius: 50px; overflow: hidden;">
                            <div class="progress-fill" style="width: <?= ($grade['avg_grade'] / 4) * 100 ?>%; background: linear-gradient(90deg, var(--primary-500), var(--primary-700)); height: 100%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-5 p-4" style="background: #eff6ff; border-radius: 12px; border: 1px solid #dbeafe; position: relative; overflow: hidden;">
                <div style="position: absolute; right: -10px; top: -10px; font-size: 4rem; opacity: 0.05; color: var(--primary-600);">💡</div>
                <h4 style="color: var(--primary-800); margin-bottom: 8px; font-size: 1rem;">ข้อเสนอแนะเชิงยุทธศาสตร์เพื่อการพัฒนา:</h4>
                <p style="font-size: 0.85rem; color: var(--primary-700); line-height: 1.6; font-weight: 500;">
                    แนวโน้มผลสัมฤทธิ์ทางการเรียนมีความสม่ำเสมอในทุกระดับชั้น ควรมุ่งเน้นการส่งเสริมกิจกรรมเสริมหลักสูตร (Co-curricular activities) เพื่อยกระดับสมรรถนะนักเรียนที่เป็นเลิศอย่างยั่งยืน
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Roadmap Strategic Navigation -->
<div class="mt-5" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; padding-bottom: 40px;">
    <a href="<?= url('executive/ews') ?>" class="card p-4 text-center hover-up" style="text-decoration: none; border-radius: 16px; border: 1px solid var(--gray-200); transition: all 0.3s ease;">
        <div style="font-size: 3rem; margin-bottom: 16px;">🏢</div>
        <h4 style="color: var(--gray-800); font-weight: 700; font-size: 1.1rem;">ระบบติดตามกลุ่มนักเรียนเฝ้าระวัง</h4>
        <p style="font-size: 0.8rem; color: var(--gray-500); margin-top: 8px;">(Early Warning Service Analysis)</p>
    </a>
    <a href="<?= url('executive/tcas') ?>" class="card p-4 text-center hover-up" style="text-decoration: none; border-radius: 16px; border: 1px solid var(--gray-200); transition: all 0.3s ease;">
        <div style="font-size: 3rem; margin-bottom: 16px;">📈</div>
        <h4 style="color: var(--gray-800); font-weight: 700; font-size: 1.1rem;">ทิศทางการศึกษาต่อระดับอุดมศึกษา</h4>
        <p style="font-size: 0.8rem; color: var(--gray-500); margin-top: 8px;">(TCAS Strategic Forecasting)</p>
    </a>
    <a href="<?= url('executive/excellence') ?>" class="card p-4 text-center hover-up" style="text-decoration: none; border-radius: 16px; border: 1px solid var(--gray-200); transition: all 0.3s ease;">
        <div style="font-size: 3rem; margin-bottom: 16px;">📚</div>
        <h4 style="color: var(--gray-800); font-weight: 700; font-size: 1.1rem;">หอเกียรติยศและคลังความสำเร็จ</h4>
        <p style="font-size: 0.8rem; color: var(--gray-500); margin-top: 8px;">(Hall of Fame & Achievement Vault)</p>
    </a>
</div>
