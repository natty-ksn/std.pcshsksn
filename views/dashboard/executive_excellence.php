<?php $pageTitle = 'รายงานสถิตินวัตกรรมและผลลัพธ์ที่เป็นเลิศ'; ?>

<style>
    /* Executive Header Styling */
    .exec-header {
        background: white;
        padding: 30px;
        border-radius: 20px;
        border-left: 8px solid var(--primary-800);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        margin-bottom: 30px;
    }

    .excellence-tabs {
        display: flex;
        gap: 8px;
        background: #e2e8f0;
        padding: 6px;
        border-radius: 12px;
        margin-bottom: 25px;
        width: fit-content;
    }

    .excellence-tab-btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        background: transparent;
        color: #64748b;
        font-size: 0.95rem;
    }

    .excellence-tab-btn.active {
        background: white;
        color: var(--primary-800);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .tab-content {
        display: none;
        animation: fadeIn 0.3s ease-out;
    }

    .tab-content.active {
        display: block;
    }

    /* Stat Cards */
    .stat-card-premium {
        background: white;
        padding: 24px;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        transition: transform 0.2s;
        display: flex;
        flex-direction: column;
        justify-content: center;
        text-align: center;
    }

    .stat-card-premium:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
    }

    .award-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        border-top: 5px solid var(--primary-700);
        padding: 20px;
        transition: all 0.2s;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .award-card:hover {
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media print {
        .excellence-tabs, .no-print { display: none !important; }
        .tab-content { display: block !important; }
        .award-card { break-inside: avoid; border: 1px solid #000; }
    }
</style>

<div class="no-print">
    <div class="exec-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="color: var(--primary-800); font-weight: 800; font-size: 1.8rem; margin: 0;">🏛️ รายงานผลสัมฤทธิ์ทางการเรียนและการแข่งขัน</h1>
                <p style="color: #64748b; font-size: 1rem; margin-top: 5px;">สรุปผลงานระดับเชิงปริมาณและคุณภาพ ประจำปีการศึกษา <?= (date('Y') + 543) ?></p>
            </div>
            <div style="display: flex; gap: 10px;">
                <button onclick="window.print()" class="btn" style="background: #f1f5f9; color: #475569; padding: 10px 20px; border-radius: 8px;">🖨️ พิมพ์รายงาน</button>
                <a href="<?= url('executive/summary') ?>" class="btn btn-primary" style="padding: 10px 20px; border-radius: 8px;">ย้อนกลับ</a>
            </div>
        </div>
    </div>
</div>

<!-- Navigation Tabs -->
<div class="excellence-tabs no-print">
    <button class="excellence-tab-btn active" onclick="switchExTab('analytics', this)">📊 ภาพรวมสถิติ</button>
    <button class="excellence-tab-btn" onclick="switchExTab('international', this)">🌏 นานาชาติ</button>
    <button class="excellence-tab-btn" onclick="switchExTab('national', this)">🇹🇭 ระดับชาติ</button>
    <button class="excellence-tab-btn" onclick="switchExTab('regional', this)">📍 ภูมิภาค/จังหวัด</button>
</div>

<!-- PART 1: Analytics -->
<div id="tab-analytics" class="tab-content active">
    <div class="grid mb-4" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        <div class="stat-card-premium" style="border-top: 6px solid #d97706;">
            <div style="color: #64748b; font-size: 0.9rem; font-weight: 700; margin-bottom: 5px;">นานาชาติ</div>
            <div style="font-size: 2.5rem; font-weight: 800; color: #d97706;"><?= count(array_filter($awards, fn($a) => $a['achievement_level'] == 'international')) ?> <span style="font-size: 1.2rem; font-weight: 600; color: #94a3b8;">รางวัล</span></div>
        </div>
        <div class="stat-card-premium" style="border-top: 6px solid #1e40af;">
            <div style="color: #64748b; font-size: 0.9rem; font-weight: 700; margin-bottom: 5px;">ระดับชาติ</div>
            <div style="font-size: 2.5rem; font-weight: 800; color: #1e40af;"><?= count(array_filter($awards, fn($a) => $a['achievement_level'] == 'national')) ?> <span style="font-size: 1.2rem; font-weight: 600; color: #94a3b8;">รางวัล</span></div>
        </div>
        <div class="stat-card-premium" style="border-top: 6px solid #059669;">
            <div style="color: #64748b; font-size: 0.9rem; font-weight: 700; margin-bottom: 5px;">นักเรียนต้นแบบ</div>
            <div style="font-size: 2.5rem; font-weight: 800; color: #059669;"><?= $stats['total_students'] ?> <span style="font-size: 1.2rem; font-weight: 600; color: #94a3b8;">คน</span></div>
        </div>
    </div>

    <div class="grid mb-4" style="grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 24px;">
        <div class="card p-4" style="border-radius: 20px;">
            <h4 style="margin-bottom: 20px; font-weight: 800; color: var(--primary-800);">📊 สัดส่วนความสำเร็จแยกตามระดับ</h4>
            <div style="height: 300px;"><canvas id="levelChartEx"></canvas></div>
        </div>
        <div class="card p-4" style="border-radius: 20px;">
            <h4 style="margin-bottom: 20px; font-weight: 800; color: var(--primary-800);">📈 สาขาวิชาที่โดดเด่นสูงสุด</h4>
            <div style="height: 300px;"><canvas id="categoryChartEx"></canvas></div>
        </div>
    </div>
</div>

<!-- PART 2: Lists -->
<?php 
    $levels = [
        'international' => ['title' => 'รางวัลระดับนานาชาติ', 'color' => '#d97706', 'icon' => '🌏'],
        'national' => ['title' => 'รางวัลระดับชาติ', 'color' => '#1e40af', 'icon' => '🇹🇭'],
        'regional' => ['title' => 'รางวัลระดับภูมิภาคและจังหวัด', 'color' => '#059669', 'icon' => '🏆']
    ];
?>

<?php foreach ($levels as $key => $info): ?>
    <div id="tab-<?= $key ?>" class="tab-content">
        <?php
        $filtered = array_filter($awards, function($a) use ($key) {
            if ($key === 'regional') {
                return in_array($a['achievement_level'], ['regional', 'province', 'district'], true);
            }
            return $a['achievement_level'] === $key;
        });
        ?>
        
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px;">
            <h2 style="color: <?= $info['color'] ?>; font-weight: 800; font-size: 1.6rem; margin: 0; display: flex; align-items: center; gap: 15px;">
                <span style="background: <?= $info['color'] ?>; color: white; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 1.4rem;"><?= $info['icon'] ?></span>
                <?= $info['title'] ?>
            </h2>
            <span style="font-weight: 700; background: <?= $info['color'] ?>22; color: <?= $info['color'] ?>; padding: 6px 16px; border-radius: 50px;">ทั้งหมด <?= count($filtered) ?> รายการ</span>
        </div>

        <?php if (empty($filtered)): ?>
            <div class="card p-5 text-center" style="background: white; border: 2px dashed #cbd5e1; border-radius: 20px;">
                <p style="color: #64748b; font-size: 1.1rem; font-weight: 600;">ยังไม่พบฐานข้อมูลรางวัลในระดับนี้</p>
            </div>
        <?php else: ?>
            <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
                <?php foreach ($filtered as $award): ?>
                    <div class="award-card" style="border-top-color: <?= $info['color'] ?>;">
                        <div style="flex-grow: 1;">
                            <div style="font-size: 0.8rem; color: <?= $info['color'] ?>; font-weight: 800; margin-bottom: 8px; text-transform: uppercase;"><?= e($award['category_name']) ?></div>
                            <h3 style="margin: 0 0 15px 0; color: #1e293b; font-weight: 800; font-size: 1.1rem; line-height: 1.5;"><?= e($award['award'] ?: $award['title']) ?></h3>
                        </div>
                        <div style="margin-top: auto; padding-top: 15px; border-top: 1px solid #f1f5f9; display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; color: var(--primary-700);">👤</div>
                            <div>
                                <div style="font-weight: 800; color: #1e293b; font-size: 0.95rem;"><?= e($award['full_name']) ?></div>
                                <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">ชั้นมัธยมศึกษาปีที่ <?= e($award['class_year']) ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php
$exLevelNames = [
    'international' => 'ระดับนานาชาติ',
    'national' => 'ระดับชาติ',
    'province' => 'ระดับจังหวัด',
    'district' => 'ระดับเขตพื้นที่',
    'regional' => 'ระดับภูมิภาค',
    'school' => 'ระดับโรงเรียน',
];
?>
<script>
function switchExTab(tabId, btn) {
    // Buttons
    document.querySelectorAll('.excellence-tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // Content
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    document.getElementById('tab-' + tabId).classList.add('active');
}

document.addEventListener('DOMContentLoaded', function() {
    // Level Chart
    new Chart(document.getElementById('levelChartEx').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_map(fn($d) => $exLevelNames[$d['label']] ?? strtoupper($d['label']), $stats['by_level'])) ?>,
            datasets: [{
                data: <?= json_encode(array_map(fn($d) => $d['count'], $stats['by_level'])) ?>,
                backgroundColor: ['#d97706', '#1e40af', '#059669', '#0891b2', '#64748b'],
                borderWidth: 4,
                borderColor: '#ffffff',
                hoverOffset: 15
            }]
        },
        options: {
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { position: 'right', labels: { font: { family: 'Sarabun', weight: '700' } } }
            }
        }
    });

    // Category Chart
    new Chart(document.getElementById('categoryChartEx').getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_map(fn($d) => $d['label'], $stats['by_category'])) ?>,
            datasets: [{
                label: 'จำนวนรางวัล',
                data: <?= json_encode(array_map(fn($d) => $d['count'], $stats['by_category'])) ?>,
                backgroundColor: 'rgba(30, 64, 175, 0.8)',
                hoverBackgroundColor: '#1e40af',
                borderRadius: 8
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { display: false } },
                x: { grid: { display: false }, ticks: { font: { family: 'Sarabun', weight: '600' } } }
            }
        }
    });
});
</script>
