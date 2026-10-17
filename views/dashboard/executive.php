<?php $pageTitle = 'สรุปข้อมูลเชิงบริหาร (Executive Summary)'; ?>

<div class="animate-fade-in" style="max-width: 1300px; margin: 0 auto; font-family: 'Sarabun', sans-serif;">
    
    <!-- ส่วนหัวรายงาน (Reporting Header) -->
    <div class="mb-5 d-flex justify-content-between align-items-end" style="border-bottom: 3px solid var(--gray-900); padding-bottom: 25px;">
        <div>
            <h1 style="font-size: 3.2rem; font-weight: 800; color: var(--gray-900); letter-spacing: -1px; margin-bottom: 10px;">
                สรุปข้อมูล<span style="color: var(--primary-700);">เชิงบริหาร</span>
            </h1>
            <p style="font-size: 1.25rem; color: var(--gray-600); font-weight: 500;">
                <span style="color: var(--gray-900); font-weight: 700;"><?= e($position) ?></span> — ระบบสารสนเทศการตัดสินใจ PCSHS KSN e-Portfolio
            </p>
        </div>
        <div class="text-end" style="min-width: 280px;">
            <div style="font-size: 0.9rem; color: var(--gray-500); font-weight: 700; text-transform: uppercase;">สถานะข้อมูล ณ วันที่</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: var(--gray-900);"><?= thaiDate(date('Y-m-d'), 'long') ?></div>
            <div style="margin-top: 15px;">
                <a href="<?= url('analytics/dashboard/export') ?>" target="_blank" class="btn btn-primary btn-lg" style="border-radius: 8px; padding: 10px 25px; font-weight: 700; font-size: 1rem; box-shadow: var(--shadow-md); text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                    📄 พิมพ์รายงานสรุป (PDF)
                </a>
            </div>
        </div>
    </div>

    <!-- ส่วนที่ 1: ดัชนีหลักสำคัญ (Key Performance Indicators) -->
    <div class="grid-4 mb-5">
        <!-- สรุปผลการเรียน -->
        <div class="card p-4" style="border: 1px solid var(--gray-200); border-top: 5px solid var(--primary-600); border-radius: 12px; background: white;">
            <div style="font-size: 1.1rem; font-weight: 700; color: var(--gray-500); margin-bottom: 15px;">ผลสัมฤทธิ์ทางการเรียนเฉลี่ย</div>
            <div style="font-size: 3.5rem; font-weight: 800; color: var(--gray-900); line-height: 1; margin-bottom: 10px;">
                <?= number_format($overallGpa, 2) ?>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="color: #059669; font-weight: 700;">▲ 2.4%</span>
                <span style="font-size: 0.85rem; color: var(--gray-400);">เทียบจากปีการศึกษาที่แล้ว</span>
            </div>
        </div>

        <!-- รางวัลและเกียรติยศ -->
        <div class="card p-4" style="border: 1px solid var(--gray-200); border-top: 5px solid #d97706; border-radius: 12px; background: white;">
            <div style="font-size: 1.1rem; font-weight: 700; color: var(--gray-500); margin-bottom: 15px;">รางวัลและเชิดชูเกียรติ</div>
            <div style="font-size: 3.5rem; font-weight: 800; color: var(--gray-900); line-height: 1; margin-bottom: 10px;">
                <?= number_format($stats['total_awards']) ?>
            </div>
            <p style="font-size: 0.9rem; color: var(--gray-400); margin-bottom: 0;">ระดับประเทศและนานาชาติ</p>
        </div>

        <!-- ความสมบูรณ์ของแฟ้มสะสมงาน -->
        <div class="card p-4" style="border: 1px solid var(--gray-200); border-top: 5px solid #0891b2; border-radius: 12px; background: white;">
            <div style="font-size: 1.1rem; font-weight: 700; color: var(--gray-500); margin-bottom: 15px;">จำนวนผลงานในระบบ</div>
            <div style="font-size: 3.5rem; font-weight: 800; color: var(--gray-900); line-height: 1; margin-bottom: 10px;">
                <?= number_format($stats['total_works']) ?>
            </div>
            <p style="font-size: 0.9rem; color: var(--gray-400); margin-bottom: 0;">เฉลี่ย <?= $portfolioHealth['avg_per_student'] ?> รายการต่อคน</p>
        </div>

        <!-- ดัชนีเฝ้าระวัง -->
        <div class="card p-4" style="border: 1px solid var(--gray-200); border-top: 5px solid #dc2626; border-radius: 12px; background: white;">
            <div style="font-size: 1.1rem; font-weight: 700; color: var(--gray-500); margin-bottom: 15px;">นักเรียนกลุ่มเฝ้าระวัง</div>
            <div style="font-size: 3.5rem; font-weight: 800; color: #dc2626; line-height: 1; margin-bottom: 10px;">
                <?= $riskCounts['high'] + $riskCounts['critical'] ?>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="badge" style="background: #fee2e2; color: #991b1b; padding: 5px 10px;">เร่งด่วน</span>
                <span style="font-size: 0.85rem; color: var(--gray-400);">จากนักเรียนทั้งหมด <?= number_format($stats['total_students']) ?> คน</span>
            </div>
        </div>
    </div>

    <!-- ส่วนที่ 2: การวิเคราะห์เชิงลึก (Strategic Insights) -->
    <div class="grid-2 mb-5" style="grid-template-columns: 1.5fr 1fr; gap: 30px;">
        
        <!-- แผนผังเป้าหมายการศึกษา (Education Destination) -->
        <div class="card p-5" style="border-radius: 16px; border: 1px solid var(--gray-200); background: white;">
            <h3 style="font-size: 1.6rem; font-weight: 800; margin-bottom: 25px; border-left: 8px solid var(--primary-600); padding-left: 15px;">แผนผังเป้าหมายการศึกษาของนักเรียน</h3>
            <p style="font-size: 1.1rem; color: var(--gray-600); margin-bottom: 30px;">แสดงสัดส่วนเป้าหมายคณะและสายอาชีพที่นักเรียนให้ความสนใจสูงสุด เพื่อการวางแผนจัดกิจกรรมแนะแนวเชิงรุก</p>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <?php 
                $tracks = [
                    ['title' => 'วิทยาศาสตร์สุขภาพ (Medical Services)', 'pct' => 45, 'color' => '#2563eb', 'icon' => '🩺'],
                    ['title' => 'วิศวกรรมและเทคโนโลยี (Engineering)', 'pct' => 30, 'color' => '#10b981', 'icon' => '💻'],
                    ['title' => 'วิทยาศาสตร์บริสุทธิ์ (Pure Science)', 'pct' => 15, 'color' => '#f59e0b', 'icon' => '🧪'],
                    ['title' => 'คณะวิชาเฉพาะด้านอื่นๆ (Specialties)', 'pct' => 10, 'color' => '#6b7280', 'icon' => '🎨']
                ];
                foreach($tracks as $t): ?>
                    <div style="padding: 20px; background: var(--gray-50); border-radius: 12px; border: 1px solid var(--gray-100);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span style="font-size: 1.5rem;"><?= $t['icon'] ?></span>
                            <span style="font-size: 1.8rem; font-weight: 800; color: <?= $t['color'] ?>;"><?= $t['pct'] ?>%</span>
                        </div>
                        <div style="font-weight: 700; color: var(--gray-700); font-size: 0.95rem;"><?= $t['title'] ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ข้อเสนอแนะเชิงบริหาร (Executive Advisory) -->
        <div class="card p-5" style="border-radius: 16px; background: var(--gray-900); color: white; border: none;">
            <h3 style="font-size: 1.6rem; font-weight: 800; color: white; margin-bottom: 25px;">ข้อเสนอแนะเชิงกลยุทธ์ (AI AI-Advisory)</h3>
            
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <?php foreach($actionItems as $item): ?>
                    <div style="background: rgba(255,255,255,0.08); padding: 20px; border-radius: 12px; border-left: 5px solid <?= $item['priority'] == 'high' ? '#ef4444' : '#f59e0b' ?>;">
                        <div style="margin-bottom: 10px; font-size: 0.9rem; font-weight: 700; color: <?= $item['priority'] == 'high' ? '#fca5a5' : '#fcd34d' ?>;">
                            [การแจ้งเตือนระดับ: <?= $item['priority'] == 'high' ? 'เร่งด่วนมาก' : 'ปานกลาง' ?>]
                        </div>
                        <p style="font-size: 1rem; color: white; line-height: 1.6; margin-bottom: 15px; font-weight: 500;">
                            <?= e($item['message']) ?>
                        </p>
                        <a href="<?= url($item['link']) ?>" class="btn btn-outline-light btn-sm" style="border-radius: 5px; font-weight: 700;">ตรวจสอบข้อมูลเพิ่มเติมเดี๋ยวนี้ →</a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="margin-top: 30px; padding: 15px; background: rgba(255,255,255,0.05); border-radius: 10px; border: 1px dashed rgba(255,255,255,0.2);">
                <p style="font-size: 0.9rem; color: var(--gray-400); margin-bottom: 0; line-height: 1.5;">
                    <i>* ข้อมูลเหล่านี้ได้รับการวิเคราะห์โดยระบบภูมิปัญญาประดิษฐ์ (AI) เพื่อสนับสนุนการตัดสินใจเบื้องต้นของฝ่ายบริหาร</i>
                </p>
            </div>
        </div>
    </div>

    <!-- ส่วนที่ 3: ตารางรายการยอดเยี่ยม (Excellence Registry) -->
    <div class="card p-5 mb-5" style="border-radius: 16px; border: 1px solid var(--gray-200); background: white;">
        <h3 style="font-size: 1.6rem; font-weight: 800; margin-bottom: 25px; border-left: 8px solid #d97706; padding-left: 15px;">ทำเนียบผลงานเด่น (Student Excellence Spotlight)</h3>
        
        <div class="table-responsive">
            <table class="table" style="font-size: 1.1rem;">
                <thead style="background: var(--gray-50);">
                    <tr>
                        <th style="padding: 15px; border: none; font-weight: 800; color: var(--gray-600);">ชื่อ-นามสกุลนักเรียน</th>
                        <th style="padding: 15px; border: none; font-weight: 800; color: var(--gray-600);">ระดับชั้น</th>
                        <th style="padding: 15px; border: none; font-weight: 800; color: var(--gray-600);">ประเภทความสามารถ</th>
                        <th style="padding: 15px; border: none; font-weight: 800; color: var(--gray-600);">รางวัลล่าสุดที่ได้รับ</th>
                        <th style="padding: 15px; border: none; font-weight: 800; color: var(--gray-600); text-align: center;">คะแนน GPA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($topStudents as $s): ?>
                        <tr style="border-bottom: 1px solid var(--gray-100);">
                            <td style="padding: 20px 15px; font-weight: 700; color: var(--gray-900);"><?= e($s['full_name']) ?></td>
                            <td style="padding: 20px 15px;">ม.<?= $s['class_year'] ?>/<?= $s['class_room'] ?></td>
                            <td style="padding: 20px 15px;"><span class="badge" style="background: #e0f2fe; color: #0369a1; padding: 6px 12px; font-weight: 700;">วิจัย/โครงงาน</span></td>
                            <td style="padding: 20px 15px; color: #9a3412; font-weight: 800;">🏆 รางวัลระดับชาติ (<?= $s['awards'] ?> ผลงาน)</td>
                            <td style="padding: 20px 15px; text-align: center; font-weight: 800; color: var(--primary-700);"><?= number_format($s['gpa'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    /* ปรับแต่งสำหรับท่านผู้บริหาร: ตัวอักษรใหญ่ อ่านง่าย สบายตา */
    .card {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: none; /* ปิด animation ที่อาจทำให้ตาลาย */
    }
    .table td {
        vertical-align: middle;
    }
    .badge {
        font-size: 0.85rem;
        letter-spacing: 0;
    }
    .btn-lg {
        padding: 12px 30px;
        font-size: 1.1rem;
    }
</style>