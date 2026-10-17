<?php $pageTitle = 'สรุปผลการประเมิน RIASEC Dashboard'; ?>

<div class="result-dashboard" style="max-width: 1100px; margin: 0 auto; padding: 20px;">
    
    <!-- Hero Banner (Top) -->
    <div class="hero-banner" style="background: linear-gradient(135deg, var(--primary-800) 0%, var(--primary-600) 100%); color: white; border-radius: 24px; padding: 48px; text-align: center; margin-bottom: 32px; box-shadow: 0 10px 30px rgba(30, 58, 138, 0.2); position: relative; overflow: hidden;">
        <!-- Decorative element -->
        <div style="position: absolute; top: -30px; right: -20px; font-size: 15rem; opacity: 0.05; transform: rotate(15deg) scale(1.2);">🧭</div>
        <div style="position: absolute; bottom: -30px; left: -20px; font-size: 15rem; opacity: 0.05; transform: rotate(-15deg); filter: blur(4px);">🎓</div>
        
        <div style="position: relative; z-index: 10;">
            <span style="display: inline-block; background: rgba(255,255,255,0.1); backdrop-filter: blur(8px); padding: 8px 24px; border-radius: 50px; font-size: 0.95rem; font-weight: 600; letter-spacing: 1px; color: #cbd5e1; border: 1px solid rgba(255,255,255,0.2); margin-bottom: 24px;">
                รหัสบุคลิกภาพที่โดดเด่นของคุณคือ
            </span>
            <h1 style="font-size: 5.5rem; font-weight: 900; margin: 0 0 16px 0; letter-spacing: 8px; color: var(--accent-500); text-shadow: 0 4px 16px rgba(0,0,0,0.3); line-height: 1;">
                <?= e($assessment['dominant_trait']) ?>
            </h1>
            <p style="font-size: 1.15rem; color: #e2e8f0; max-width: 650px; margin: 0 auto; line-height: 1.6;">
                ผลการประเมินนี้สะท้อนตัวตน ความฝัน และทักษะแฝงในตัวคุณ <br> นี่คือกุญแจสำคัญที่จะช่วยปลดล็อกคณะและสายอาชีพที่เข้ากับคุณที่สุด!
            </p>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="dashboard-grid" style="display: grid; grid-template-columns: 1.3fr 1fr; gap: 32px; align-items: start;">
        
        <!-- ฝั่งซ้าย: ขยายความ 3 อันดับแรก -->
        <div class="traits-breakdown" style="display: flex; flex-direction: column; gap: 24px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <span style="font-size: 1.8rem;">🥇</span>
                <h3 style="color: var(--gray-900); font-size: 1.5rem; font-weight: 800; margin: 0;">บุคลิกภาพ 3 อันดับแรกของคุณ</h3>
            </div>
            
            <?php 
            // รายละเอียดของแต่ละตัวอักษร
            $descriptions = [
                'Realistic' => 'คุณชอบลงมือทำจริง เรียนรู้ผ่านการปฏิบัติ ถนัดการใช้เครื่องมือซ่อมแซม ชอบงานช่าง หรือรักการทำกิจกรรมกลางแจ้ง',
                'Investigative' => 'คุณมีความคิดเป็นเหตุเป็นผล ชอบคิด วิเคราะห์ วิจัย ค้นหาเหตุผล และแก้ปัญหาที่ซับซ้อน มักจะเจิดจรัสในสายงานวิทย์-คณิต',
                'Artistic' => 'คุณมีอารมณ์ศิลปิน รักอิสระ มีจินตนาการล้ำเลิศ ชอบแสดงออกทางอารมณ์และสร้างสรรค์ผลงานศิลปะ ดนตรี หรืองานออกแบบ',
                'Social' => 'คุณมีจิตใจโอบอ้อมอารี มนุษยสัมพันธ์ยอดเยี่ยม ชอบช่วยเหลือ สอน หรือให้คำปรึกษาผู้อื่น ถนัดการทำงานร่วมกับผู้คน',
                'Enterprising' => 'คุณเกิดมาเพื่อเป็นผู้นำ! มีวาทศิลป์ในการโน้มน้าวใจ กล้าคิดกล้าทำเชิงธุรกิจ ชอบสร้างเป้าหมายและการแข่งขัน',
                'Conventional' => 'คุณเป็นคนเจ้าระเบียบ ทำงานอย่างเป็นระบบ แม่นยำ ละเอียดลออ ถนัดการจัดการข้อมูล งานเอกสาร และการทำตามแบบแผน'
            ];
            
            foreach($topTraits as $index => $traitName): 
                $traitType = explode(' ', $traitName)[0]; // ดึงเฉพาะคำฝรั่ง (e.g. "Realistic")
                $desc = '';
                foreach ($descriptions as $key => $val) {
                    if (str_contains($traitType, $key)) {
                        $desc = $val;
                        break;
                    }
                }
            ?>
                <!-- Card รายละเอียด -->
                <div style="background: white; border-radius: 20px; padding: 24px; border: 1px solid var(--gray-200); border-left: 6px solid var(--primary-<?= 500 + ($index*100) ?>); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; gap: 24px; align-items: flex-start; transition: transform 0.3s ease;" class="hover-elevate">
                    
                    <div style="width: 56px; height: 56px; flex-shrink: 0; background: var(--primary-50); color: var(--primary-600); border-radius: 16px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.5rem;">
                        #<?= $index + 1 ?>
                    </div>
                    
                    <div>
                        <h4 style="font-size: 1.25rem; font-weight: 700; color: var(--gray-900); margin-bottom: 8px;">
                            <?= e($traitName) ?>
                        </h4>
                        <p style="color: var(--gray-600); line-height: 1.6; font-size: 1rem; margin: 0;">
                            <?= $desc ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- กล่องแนะนำเพิ่มเติม -->
            <div style="margin-top: 8px; padding: 24px; background: rgba(245, 158, 11, 0.08); border-radius: 20px; border: 1px solid rgba(245, 158, 11, 0.2);">
                <?php
                // Data for Faculty Recommendations based on Top Trait
                $topLetter = substr(strtoupper($assessment['dominant_trait']), 0, 1);
                $recommendations = [
                    'R' => ['วิศวกรรมศาสตร์', 'สถาปัตยกรรมศาสตร์ (ปฏิบัติ)', 'เกษตรศาสตร์/วนศาสตร์', 'วิทยาศาสตร์การกีฬา', 'เทคโนโลยีอุตสาหกรรม'],
                    'I' => ['แพทยศาสตร์/ทันตแพทยศาสตร์', 'วิทยาศาสตร์/คณิตศาสตร์', 'วิศวกรรมคอมพิวเตอร์/AI', 'เภสัชศาสตร์/เทคนิคการแพทย์', 'เศรษฐศาสตร์ (วิเคราะห์)'],
                    'A' => ['ศิลปกรรมศาสตร์/วิจิตรศิลป์', 'นิเทศศาสตร์/วารสารศาสตร์', 'สถาปัตยกรรมศาสตร์ (ออกแบบ)', 'อักษรศาสตร์/ศิลปศาสตร์', 'ดุริยางคศิลป์'],
                    'S' => ['ครุศาสตร์/ศึกษาศาสตร์', 'พยาบาลศาสตร์', 'สังคมสงเคราะห์ศาสตร์', 'จิตวิทยา', 'สาธารณสุขศาสตร์'],
                    'E' => ['บริหารธุรกิจ/การตลาด', 'รัฐศาสตร์ (ความสัมพันธ์ระหว่างประเทศ)', 'นิติศาสตร์', 'การจัดการการท่องเที่ยวและการโรงแรม', 'นิเทศศาสตร์ (ประชาสัมพันธ์)'],
                    'C' => ['พาณิชยศาสตร์และการบัญชี', 'วิทยาการคอมพิวเตอร์/IT data', 'สถิติประยุกต์', 'บรรณารักษศาสตร์', 'บริหารธุรกิจ (การจัดการทั่วไป)']
                ];
                
                $suggestedFaculties = $recommendations[$topLetter] ?? [];
                ?>
                <div style="display: flex; gap: 16px; align-items: flex-start;">
                    <div style="font-size: 2.2rem; filter: drop-shadow(0 2px 4px rgba(245,158,11,0.3));">🎯</div>
                    <div style="flex: 1;">
                        <h4 style="color: #b45309; font-weight: 800; font-size: 1.15rem; margin-bottom: 12px;">คณะที่น่าจะใช่ สำหรับคุณ!</h4>
                        <p style="color: #92400e; font-size: 0.95rem; line-height: 1.6; margin-bottom: 12px;"> 
                            จากบุคลิกภาพที่โดดเด่นที่สุดของคุณในด้าน <strong><?= e($topTraits[0]) ?></strong> เราขอแนะนำกลุ่มคณะและสาขาวิชาเหล่านี้เพื่อเป็นแนวทาง:
                        </p>
                        
                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                            <?php foreach($suggestedFaculties as $fac): ?>
                                <span style="background: white; border: 1px solid rgba(245,158,11,0.3); color: #b45309; padding: 6px 14px; border-radius: 50px; font-size: 0.9rem; font-weight: 600; box-shadow: 0 2px 4px rgba(245,158,11,0.05);">
                                    🎓 <?= e($fac) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ฝั่งขวา: กราฟและปุ่มสถิติ -->
        <div class="visuals-breakdown" style="display: flex; flex-direction: column; gap: 24px;">
            
            <!-- Card กราฟที่ทันสมัยขึ้น -->
            <div style="background: white; border-radius: 24px; padding: 32px 24px; border: 1px solid var(--gray-200); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); position: relative;">
                <h3 style="color: var(--gray-900); font-size: 1.2rem; margin-bottom: 24px; text-align: center; font-weight: 700;"> Radar Chart การวิเคราะห์ 6 มิติ</h3>
                
                <div style="position: relative; height: 350px; width: 100%;">
                    <canvas id="riasecChart"></canvas>
                </div>
                
                <!-- สรุปคะแนนดิบใต้กราฟ -->
                <div style="margin-top: 32px; border-top: 1px dashed var(--gray-200); padding-top: 24px;">
                    <h4 style="color: var(--gray-500); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; text-align: center;">สรุปคะแนนแต่ละด้าน</h4>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: center;">
                        <?php 
                        $abbr = ['R' => 'นักปฏิบัติ', 'I' => 'นักวิเคราะห์', 'A' => 'นักสร้างสรรค์', 'S' => 'เพื่อสังคม', 'E' => 'นักบริหาร', 'C' => 'นักจัดการ'];
                        foreach ($scores as $label => $score): 
                        ?>
                            <div style="background: var(--gray-50); padding: 8px 16px; border-radius: 50px; border: 1px solid var(--gray-200); font-size: 0.85rem; color: var(--gray-700); font-weight: 500;">
                                <strong style="color: var(--primary-600); font-size: 1rem;"><?= $label ?></strong> : <?= $score ?> / 8 คะแนน
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <a href="<?= url('assessment/test') ?>" class="btn" style="background: white; color: var(--gray-700); border: 1px solid var(--gray-300); display: flex; align-items: center; justify-content: center; gap: 12px; padding: 16px; border-radius: 16px; font-weight: 600; font-size: 1.05rem; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: all 0.2s ease;">
                🔄 เริ่มทำแบบประเมินใหม่อีกครั้ง
            </a>

        </div>

    </div>
</div>

<style>
    .hover-elevate {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-elevate:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px -5px rgba(0,0,0,0.1) !important;
        border-color: var(--primary-300) !important;
    }

    @media (max-width: 900px) {
        .dashboard-grid {
            grid-template-columns: 1fr !important;
        }
        .hero-banner {
            padding: 40px 24px !important;
        }
        .hero-banner h1 {
            font-size: 4rem !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('riasecChart').getContext('2d');
        
        // ข้อมูลคะแนน (ผ่านจาก PHP)
        const rawScores = <?= json_encode($scores) ?>;
        
        const dataValues = [
            rawScores['R'] || 0,
            rawScores['I'] || 0,
            rawScores['A'] || 0,
            rawScores['S'] || 0,
            rawScores['E'] || 0,
            rawScores['C'] || 0
        ];

        new Chart(ctx, {
            type: 'radar',
            data: {
                labels: ['R (ปฏิบัติ)', 'I (วิเคราะห์)', 'A (สร้างสรรค์)', 'S (สังคม)', 'E (บริหาร)', 'C (จัดการ)'],
                datasets: [{
                    label: 'คะแนนความสนใจ',
                    data: dataValues,
                    backgroundColor: 'rgba(37, 99, 235, 0.15)',
                    borderColor: 'rgba(37, 99, 235, 1)',
                    pointBackgroundColor: 'rgba(245, 158, 11, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(245, 158, 11, 1)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: { color: 'rgba(0,0,0,0.05)' },
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        suggestedMin: 0,
                        suggestedMax: 8,
                        ticks: {
                            stepSize: 1,
                            display: false /* ซ่อนตัวเลขบอกสเกลให้ดูกราฟสะอาดตาขึ้น */
                        },
                        pointLabels: {
                            font: {
                                size: 13,
                                family: "'Inter', 'Noto Sans Thai'",
                                weight: '600'
                            },
                            color: '#475569'
                        }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>

