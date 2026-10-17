<?php
$pageTitle = 'TCAS AI Advisor';
/**
 * TCAS AI Advisor — Phase 5.1: Gemini-Powered Gap Analysis
 *
 * Displays faculty selector, triggers async AI analysis,
 * and renders results in a premium Glassmorphism UI.
 */
?>

<style>
    /* ====== TCAS AI Advisor — Premium Dark Glass UI ====== */
    .tcas-page {
        min-height: 100vh;
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
        padding: 40px 20px;
        position: relative;
        overflow: hidden;
    }

    .tcas-page::before {
        content: '';
        position: absolute;
        width: 600px;
        height: 600px;
        border-radius: 50%;
        background: radial-gradient(circle, #6366f1, transparent 70%);
        filter: blur(150px);
        opacity: 0.2;
        top: -200px;
        right: -200px;
        animation: orbFloat 25s ease-in-out infinite;
        pointer-events: none;
    }

    .tcas-page::after {
        content: '';
        position: absolute;
        width: 500px;
        height: 500px;
        border-radius: 50%;
        background: radial-gradient(circle, #10b981, transparent 70%);
        filter: blur(150px);
        opacity: 0.15;
        bottom: -150px;
        left: -150px;
        animation: orbFloat 20s ease-in-out infinite reverse;
        pointer-events: none;
    }

    .tcas-content {
        position: relative;
        z-index: 1;
        max-width: 1100px;
        margin: 0 auto;
    }

    /* Header */
    .tcas-header {
        text-align: center;
        margin-bottom: 48px;
        animation: slideUp 0.6s ease-out;
    }

    .tcas-header h1 {
        font-size: 2.5rem;
        font-weight: 900;
        color: #ffffff;
        margin: 0 0 12px;
        letter-spacing: -1px;
    }

    .tcas-header h1 span {
        background: linear-gradient(135deg, #6366f1, #a78bfa, #c084fc);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .tcas-header p {
        color: rgba(255, 255, 255, 0.6);
        font-size: 1.05rem;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Student Summary Card */
    .tcas-student-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        padding: 24px 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 36px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
        animation: slideUp 0.6s ease-out 0.1s both;
    }

    .tcas-student-info {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .tcas-avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #a78bfa);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 800;
        color: #fff;
    }

    .tcas-student-info h3 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 700;
        color: #ffffff;
    }

    .tcas-student-info small {
        color: rgba(255, 255, 255, 0.5);
        font-size: 0.85rem;
    }

    .tcas-gpa-badge {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 14px;
        padding: 12px 20px;
        text-align: center;
    }

    .tcas-gpa-badge .gpa-value {
        font-size: 1.6rem;
        font-weight: 900;
        color: #fbbf24;
    }

    .tcas-gpa-badge .gpa-label {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: rgba(255, 255, 255, 0.5);
        font-weight: 700;
    }

    /* Faculty Selector */
    .tcas-selector {
        background: rgba(255, 255, 255, 0.04);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        padding: 28px;
        margin-bottom: 36px;
        animation: slideUp 0.6s ease-out 0.2s both;
    }

    .tcas-selector h2 {
        color: #ffffff;
        font-size: 1.15rem;
        font-weight: 800;
        margin: 0 0 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .faculty-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 14px;
    }

    .faculty-btn {
        background: rgba(255, 255, 255, 0.04);
        border: 2px solid rgba(255, 255, 255, 0.08);
        border-radius: 14px;
        padding: 16px 20px;
        color: #ffffff;
        font-size: 0.95rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: left;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .faculty-btn:hover {
        background: rgba(99, 102, 241, 0.15);
        border-color: rgba(99, 102, 241, 0.4);
        transform: translateY(-2px);
    }

    .faculty-btn.active {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.25), rgba(167, 139, 250, 0.15));
        border-color: #6366f1;
        box-shadow: 0 0 20px rgba(99, 102, 241, 0.2);
    }

    .faculty-btn .emoji {
        font-size: 1.4rem;
    }

    .analyze-btn {
        margin-top: 20px;
        width: 100%;
        padding: 16px;
        border: none;
        border-radius: 16px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #ffffff;
        font-size: 1.05rem;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .analyze-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(99, 102, 241, 0.4);
    }

    .analyze-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }

    /* Loading State */
    .tcas-loading {
        text-align: center;
        padding: 80px 20px;
        color: rgba(255, 255, 255, 0.5);
        display: none;
    }

    .tcas-loading.visible {
        display: block;
        animation: fadeIn 0.4s ease;
    }

    .spinner-ring {
        width: 60px;
        height: 60px;
        border: 4px solid rgba(255, 255, 255, 0.1);
        border-top-color: #6366f1;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    /* Results Area */
    .tcas-results {
        display: none;
    }

    .tcas-results.visible {
        display: block;
        animation: slideUp 0.6s ease-out;
    }

    /* Overall Readiness Gauge */
    .gauge-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
        padding: 40px;
        text-align: center;
        margin-bottom: 28px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
    }

    .gauge-value {
        font-size: 4rem;
        font-weight: 900;
        line-height: 1;
        margin-bottom: 8px;
    }

    .gauge-bar-container {
        width: 100%;
        max-width: 400px;
        height: 12px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 99px;
        margin: 16px auto;
        overflow: hidden;
    }

    .gauge-bar-fill {
        height: 100%;
        border-radius: 99px;
        transition: width 1.2s cubic-bezier(0.22, 1, 0.36, 1);
        width: 0%;
    }

    .gauge-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 3px;
        color: rgba(255, 255, 255, 0.5);
        font-weight: 700;
    }

    .gauge-faculty-name {
        font-size: 1.3rem;
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 4px;
    }

    .gauge-ai-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 99px;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 12px;
    }

    .gauge-ai-badge.ai {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.3), rgba(167, 139, 250, 0.2));
        border: 1px solid rgba(99, 102, 241, 0.5);
        color: #c4b5fd;
    }

    .gauge-ai-badge.fallback {
        background: rgba(251, 191, 36, 0.15);
        border: 1px solid rgba(251, 191, 36, 0.4);
        color: #fbbf24;
    }

    /* Analysis Cards Grid */
    .analysis-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
        margin-bottom: 28px;
    }

    .analysis-card {
        background: rgba(255, 255, 255, 0.04);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 18px;
        padding: 24px;
        animation: slideUp 0.5s ease-out both;
    }

    .analysis-card:nth-child(1) {
        animation-delay: 0.1s;
    }

    .analysis-card:nth-child(2) {
        animation-delay: 0.2s;
    }

    .analysis-card:nth-child(3) {
        animation-delay: 0.3s;
    }

    .analysis-card:nth-child(4) {
        animation-delay: 0.4s;
    }

    .analysis-card h3 {
        font-size: 1rem;
        font-weight: 800;
        color: #ffffff;
        margin: 0 0 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .analysis-card h3 .icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .analysis-card p,
    .analysis-card .advice-text {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.6);
        line-height: 1.6;
        margin: 0 0 12px;
    }

    /* Verdict Pills */
    .verdict-pill {
        display: inline-block;
        padding: 4px 14px;
        border-radius: 99px;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .verdict-ready {
        background: rgba(16, 185, 129, 0.2);
        color: #34d399;
        border: 1px solid rgba(16, 185, 129, 0.4);
    }

    .verdict-improve {
        background: rgba(245, 158, 11, 0.2);
        color: #fbbf24;
        border: 1px solid rgba(245, 158, 11, 0.4);
    }

    .verdict-critical {
        background: rgba(239, 68, 68, 0.2);
        color: #f87171;
        border: 1px solid rgba(239, 68, 68, 0.4);
    }

    /* Bullet Lists */
    .bullet-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .bullet-list li {
        padding: 6px 0;
        font-size: 0.82rem;
        color: rgba(255, 255, 255, 0.65);
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }

    .bullet-list li::before {
        content: '▸';
        color: #6366f1;
        font-weight: 700;
        flex-shrink: 0;
    }

    .bullet-list li.has-item::before {
        content: '✅';
    }

    .bullet-list li.missing-item::before {
        content: '⚠️';
    }

    /* Action Plan Table */
    .action-plan-card {
        background: rgba(255, 255, 255, 0.04);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 18px;
        padding: 24px;
        margin-bottom: 28px;
        animation: slideUp 0.5s ease-out 0.5s both;
    }

    .action-plan-card h3 {
        font-size: 1.05rem;
        font-weight: 800;
        color: #ffffff;
        margin: 0 0 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .action-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
    }

    .action-table th {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: rgba(255, 255, 255, 0.4);
        font-weight: 700;
        padding: 0 12px 10px;
        text-align: left;
    }

    .action-table td {
        font-size: 0.82rem;
        color: rgba(255, 255, 255, 0.7);
        padding: 12px;
        background: rgba(255, 255, 255, 0.03);
    }

    .action-table tr td:first-child {
        border-radius: 10px 0 0 10px;
    }

    .action-table tr td:last-child {
        border-radius: 0 10px 10px 0;
    }

    .priority-high {
        color: #f87171;
        font-weight: 800;
    }

    .priority-mid {
        color: #fbbf24;
        font-weight: 700;
    }

    .priority-low {
        color: #60a5fa;
        font-weight: 600;
    }

    /* Encouragement Footer */
    .encouragement-card {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.12), rgba(16, 185, 129, 0.08));
        backdrop-filter: blur(20px);
        border: 1px solid rgba(99, 102, 241, 0.2);
        border-radius: 20px;
        padding: 32px;
        text-align: center;
        animation: slideUp 0.5s ease-out 0.6s both;
    }

    .encouragement-card p {
        font-size: 1.1rem;
        color: #ffffff;
        font-weight: 700;
        margin: 0;
        line-height: 1.6;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .tcas-header h1 {
            font-size: 1.8rem;
        }

        .tcas-student-card {
            flex-direction: column;
            text-align: center;
        }

        .faculty-grid {
            grid-template-columns: 1fr 1fr;
        }

        .analysis-grid {
            grid-template-columns: 1fr;
        }

        .gauge-value {
            font-size: 3rem;
        }

        .action-table {
            font-size: 0.75rem;
        }

        .action-table th,
        .action-table td {
            padding: 8px 6px;
        }
    }
    
    /* Markdown Tables (Dynamic AI generated) */
    .markdown-content table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin: 20px 0;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: transparent !important;
    }
    .markdown-content th {
        background: rgba(99, 102, 241, 0.2) !important;
        color: #c4b5fd !important;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 800;
        padding: 14px;
        text-align: left;
        border: none !important;
    }
    .markdown-content td {
        color: rgba(255, 255, 255, 0.8) !important;
        padding: 12px 14px;
        background: rgba(255, 255, 255, 0.03) !important;
        border: none !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
    }
    .markdown-content tr:last-child td {
        border-bottom: none !important;
    }
    .markdown-content tr:hover td {
        background: rgba(99, 102, 241, 0.1) !important;
        color: #fff !important;
    }
</style>

<div class="tcas-page">
    <div class="tcas-content">

        <!-- Header -->
        <div class="tcas-header">
            <h1>🤖 <span>TCAS AI Advisor</span></h1>
            <p>ระบบวิเคราะห์ช่องว่างการเข้าศึกษาต่อด้วย AI (Gemini-Powered Gap Analysis) — วิเคราะห์จุดแข็ง-จุดอ่อน
                และวางแผนสู่คณะในฝัน</p>
        </div>

        <!-- Student Summary -->
        <div class="tcas-student-card">
            <div class="tcas-student-info">
                <div class="tcas-avatar"><?= e(mb_substr($profile['full_name'] ?? 'S', 0, 1, 'UTF-8')) ?></div>
                <div>
                    <h3><?= e($profile['full_name']) ?></h3>
                    <small>ม.<?= $profile['class_year'] ?>/<?= e($profile['class_room']) ?></small>
                </div>
            </div>
            <div class="tcas-gpa-badge">
                <div class="gpa-value"><?= number_format($gpa, 2) ?></div>
                <div class="gpa-label">Current GPA</div>
            </div>
        </div>

        <!-- Faculty Selector -->
        <div class="tcas-selector">
            <h2>🎯 เลือกคณะที่ต้องการวิเคราะห์</h2>
            <div class="faculty-grid">
                <?php foreach ($faculties as $key => $label): ?>
                    <button class="faculty-btn <?= $key === $selectedFaculty ? 'active' : '' ?>" data-faculty="<?= $key ?>">
                        <span class="emoji"><?= explode(' ', $label)[0] ?></span>
                        <span><?= trim(substr($label, strpos($label, ' ') + 1)) ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
            <button class="analyze-btn" id="analyzeBtn">
                ⚡ วิเคราะห์ด้วย AI
            </button>
        </div>

        <!-- Loading State -->
        <div class="tcas-loading" id="loadingState">
            <div class="spinner-ring"></div>
            <p style="font-size:1.1rem; font-weight:700;">🧠 AI กำลังวิเคราะห์ข้อมูลของคุณ...</p>
            <p style="font-size:0.85rem;">ประมวลผล GPA, Portfolio, ทักษะ, RIASEC และกิจกรรม</p>
        </div>

        <!-- Results Area -->
        <div class="tcas-results" id="resultsArea"></div>

    </div>
</div>

<script>
    (function initTCASAdvisor() {
        var selectedFaculty = '<?= $selectedFaculty ?>';
        var analyzeBtn = document.getElementById('analyzeBtn');
        var loadingState = document.getElementById('loadingState');
        var resultsArea = document.getElementById('resultsArea');

        // Faculty button selection
        document.querySelectorAll('.faculty-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.faculty-btn').forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                selectedFaculty = btn.getAttribute('data-faculty');
            });
        });

        // Analyze button click
        analyzeBtn.addEventListener('click', function () {
            analyzeBtn.disabled = true;
            analyzeBtn.textContent = '⏳ กำลังวิเคราะห์...';
            loadingState.classList.add('visible');
            resultsArea.classList.remove('visible');
            resultsArea.innerHTML = '';

            // AJAX call
            var formData = new FormData();
            formData.append('faculty', selectedFaculty);
            formData.append('_csrf_token', '<?= CSRF::token() ?>');

            fetch('<?= url('ai/api/tcas-gap') ?>', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    loadingState.classList.remove('visible');
                    analyzeBtn.disabled = false;
                    analyzeBtn.textContent = '⚡ วิเคราะห์ด้วย AI';

                    if (data.success && data.analysis) {
                        renderResults(data.analysis);
                        resultsArea.classList.add('visible');
                    } else {
                        resultsArea.innerHTML = '<div class="gauge-card"><p style="color:#f87171;">❌ เกิดข้อผิดพลาด: ' + escapeHtml(data.error || 'ไม่ทราบข้อผิดพลาด') + '</p></div>';
                        resultsArea.classList.add('visible');
                    }
                })
                .catch(function (err) {
                    loadingState.classList.remove('visible');
                    analyzeBtn.disabled = false;
                    analyzeBtn.textContent = '⚡ วิเคราะห์ด้วย AI';
                    resultsArea.innerHTML = '<div class="gauge-card"><p style="color:#f87171;">❌ การเชื่อมต่อล้มเหลว — กรุณาลองอีกครั้ง</p></div>';
                    resultsArea.classList.add('visible');
                });
        });

        // Render analysis results
        function renderResults(a) {
            var readiness = a.overall_readiness_pct || 0;
            var isAI = a.ai_generated === true;
            var gaugeColor = readiness >= 80 ? '#10b981' : (readiness >= 60 ? '#f59e0b' : '#ef4444');
            var gaugeGradient = 'linear-gradient(90deg, ' + gaugeColor + ', ' + gaugeColor + '88)';

            var html = '';

            // ---- Overall Readiness Gauge ----
            html += '<div class="gauge-card">';
            html += '<div class="gauge-faculty-name">' + escapeHtml(a.faculty_name || selectedFaculty) + '</div>';
            html += '<div class="gauge-value" style="color:' + gaugeColor + ';">' + readiness + '%</div>';
            html += '<div class="gauge-bar-container">';
            html += '<div class="gauge-bar-fill" id="gaugeBar" style="background:' + gaugeGradient + ';"></div>';
            html += '</div>';
            html += '<div class="gauge-label">Overall Readiness</div>';
            html += '<div class="gauge-ai-badge ' + (isAI ? 'ai' : 'fallback') + '">';
            html += isAI ? '✨ AI-Generated (Gemini)' : '⚙️ Rule-Based Analysis';
            html += '</div>';
            if (a.fallback_message) {
                html += '<div style="margin-top:10px; font-size:0.75rem; color:#fbbf24;">⚠️ ' + escapeHtml(a.fallback_message) + '</div>';
            }
            // ---- Copy Prompt Button (when fallback) ----
            if (!isAI) {
                window._tcasPromptData = {
                    faculty: a.target_faculty || selectedFaculty,
                    student_name: '<?= e($profile['full_name']) ?>',
                    class_year: '<?= $profile['class_year'] ?>',
                    class_room: '<?= e($profile['class_room']) ?>',
                    gpa: '<?= number_format($gpa, 2) ?>',
                    readiness: readiness,
                    gpa_analysis: a.gpa_analysis || {},
                    portfolio_analysis: a.portfolio_analysis || {},
                    skills_gap: a.skills_gap || {},
                    riasec_alignment: a.riasec_alignment || {}
                };
                html += '<div style="margin-top:16px;">';
                html += '<button id="copyPromptBtn" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:12px;padding:12px 24px;font-weight:700;font-size:0.9rem;cursor:pointer;transition:all 0.3s;">';
                html += '📋 คัดลอก Prompt ไปวางใน ChatGPT / Gemini';
                html += '</button>';
                html += '<div style="font-size:0.7rem; color:rgba(255,255,255,0.4); margin-top:6px;">คลิกเพื่อคัดลอก prompt ที่รวมข้อมูลพอร์ตโฟลิโอของคุณ ไปถาม AI ภายนอกได้ทันที</div>';
                html += '</div>';
            }
            html += '</div>';

            // ---- Analysis Cards Grid ----
            html += '<div class="analysis-grid">';

            // GPA Analysis Card
            var gpa = a.gpa_analysis || {};
            var verdictClass = gpa.verdict === 'พร้อม' ? 'verdict-ready' : (gpa.verdict === 'วิกฤต' ? 'verdict-critical' : 'verdict-improve');
            html += buildAnalysisCard(
                '📊 ผลการเรียน',
                'bg-gradient-to-br from-indigo-500/20 to-purple-500/20',
                '#6366f1',
                [
                    '<div class="d-flex justify-content-between align-items-center mb-2">' +
                    '<span style="color:rgba(255,255,255,0.6); font-size:0.85rem;">GPA ปัจจุบัน</span>' +
                    '<strong style="color:#fbbf24; font-size:1.2rem;">' + (gpa.current_gpa || 0).toFixed(2) + '</strong>' +
                    '</div>',
                    '<div class="d-flex justify-content-between align-items-center mb-2">' +
                    '<span style="color:rgba(255,255,255,0.6); font-size:0.85rem;">GPA ที่ต้องการ</span>' +
                    '<strong style="color:#ffffff; font-size:1.1rem;">' + (gpa.expected_gpa || 0).toFixed(2) + '</strong>' +
                    '</div>',
                    '<div class="d-flex justify-content-between align-items-center mb-3">' +
                    '<span style="color:rgba(255,255,255,0.6); font-size:0.85rem;">สถานะ</span>' +
                    '<span class="verdict-pill ' + verdictClass + '">' + escapeHtml(gpa.verdict || '—') + '</span>' +
                    '</div>',
                    '<p class="advice-text">' + escapeHtml(gpa.advice || '') + '</p>'
                ].join('')
            );

            // Portfolio Analysis Card
            var port = a.portfolio_analysis || {};
            html += buildAnalysisCard(
                '📁 พอร์ตโฟลิโอ',
                'from-emerald-500/20 to-teal-500/20',
                '#10b981',
                [
                    '<div style="font-size:0.85rem; color:rgba(255,255,255,0.6); margin-bottom:8px;">' +
                    'ผลงาน <strong style="color:#fff;">' + (port.total_items || 0) + '</strong> รายการ ' +
                    '| คุณภาพ <strong style="color:#fbbf24;">' + (port.quality_score_pct || 0) + '%</strong>' +
                    '</div>',
                    port.relevant_categories && port.relevant_categories.length > 0
                        ? '<div style="margin-bottom:8px;"><span style="color:rgba(255,255,255,0.5); font-size:0.75rem;">หมวดที่มี:</span> ' +
                        port.relevant_categories.map(function (c) { return '<span style="display:inline-block; background:rgba(16,185,129,0.15); color:#34d399; padding:2px 10px; border-radius:99px; font-size:0.7rem; margin:2px;">✅ ' + escapeHtml(c) + '</span>'; }).join('') + '</div>'
                        : '',
                    port.missing_categories && port.missing_categories.length > 0
                        ? '<div style="margin-bottom:8px;"><span style="color:rgba(255,255,255,0.5); font-size:0.75rem;">หมวดที่ขาด:</span> ' +
                        port.missing_categories.map(function (c) { return '<span style="display:inline-block; background:rgba(239,68,68,0.15); color:#f87171; padding:2px 10px; border-radius:99px; font-size:0.7rem; margin:2px;">⚠️ ' + escapeHtml(c) + '</span>'; }).join('') + '</div>'
                        : '',
                    '<p class="advice-text">' + escapeHtml(port.advice || '') + '</p>'
                ].join('')
            );

            // Skills Gap Card
            var skills = a.skills_gap || {};
            html += buildAnalysisCard(
                '🔧 ทักษะ',
                'from-amber-500/20 to-orange-500/20',
                '#f59e0b',
                [
                    skills.existing_skills && skills.existing_skills.length > 0
                        ? '<div style="margin-bottom:10px;"><span style="color:rgba(255,255,255,0.5); font-size:0.75rem;">ทักษะที่มี:</span><br>' +
                        skills.existing_skills.map(function (s) { return '<span style="display:inline-block; background:rgba(16,185,129,0.12); color:#34d399; padding:3px 12px; border-radius:99px; font-size:0.72rem; margin:3px 3px 3px 0;">' + escapeHtml(s) + '</span>'; }).join('') + '</div>'
                        : '',
                    skills.missing_skills && skills.missing_skills.length > 0
                        ? '<div style="margin-bottom:10px;"><span style="color:rgba(255,255,255,0.5); font-size:0.75rem;">ทักษะที่ขาด:</span><br>' +
                        skills.missing_skills.map(function (s) { return '<span style="display:inline-block; background:rgba(239,68,68,0.12); color:#f87171; padding:3px 12px; border-radius:99px; font-size:0.72rem; margin:3px 3px 3px 0;">' + escapeHtml(s) + '</span>'; }).join('') + '</div>'
                        : '',
                    '<p class="advice-text">' + escapeHtml(skills.advice || '') + '</p>'
                ].join('')
            );

            // RIASEC Alignment Card
            var riasec = a.riasec_alignment || {};
            var rAlignClass = riasec.alignment === 'สอดคล้อง' ? 'verdict-ready' : (riasec.alignment === 'ไม่สอดคล้อง' ? 'verdict-critical' : 'verdict-improve');
            html += buildAnalysisCard(
                '🧭 RIASEC',
                'from-cyan-500/20 to-blue-500/20',
                '#06b6d4',
                [
                    '<div class="d-flex justify-content-between align-items-center mb-3">' +
                    '<span style="color:rgba(255,255,255,0.6); font-size:0.85rem;">ความสอดคล้อง</span>' +
                    '<span class="verdict-pill ' + rAlignClass + '">' + escapeHtml(riasec.alignment || '—') + '</span>' +
                    '</div>',
                    riasec.dominant_traits && riasec.dominant_traits.length > 0
                        ? '<div style="margin-bottom:8px;"><span style="color:rgba(255,255,255,0.5); font-size:0.75rem;">Traits เด่น:</span> ' +
                        riasec.dominant_traits.map(function (t) { return '<span style="display:inline-block; background:rgba(6,182,212,0.15); color:#22d3ee; padding:2px 10px; border-radius:99px; font-size:0.7rem; margin:2px;">' + escapeHtml(t) + '</span>'; }).join('') + '</div>'
                        : '',
                    '<p class="advice-text">' + escapeHtml(riasec.advice || '') + '</p>'
                ].join('')
            );

            html += '</div>'; // End analysis-grid

            // ---- Soft Skills & Extracurricular Gaps ----
            if ((a.soft_skills_missing && a.soft_skills_missing.length > 0) || (a.extracurricular_gaps && a.extracurricular_gaps.length > 0)) {
                html += '<div class="analysis-card" style="margin-bottom:28px; animation-delay:0.45s;">';
                html += '<h3><span class="icon" style="background:rgba(239,68,68,0.15);">⚡</span> ช่องว่างที่ควรเติม</h3>';
                if (a.soft_skills_missing) {
                    html += '<ul class="bullet-list">';
                    a.soft_skills_missing.forEach(function (item) {
                        html += '<li>' + escapeHtml(item) + '</li>';
                    });
                    html += '</ul>';
                }
                if (a.extracurricular_gaps) {
                    html += '<ul class="bullet-list" style="margin-top:12px;">';
                    a.extracurricular_gaps.forEach(function (item) {
                        html += '<li>' + escapeHtml(item) + '</li>';
                    });
                    html += '</ul>';
                }
                html += '</div>';
            }

            // ---- Action Plan ----
            if (a.action_plan && a.action_plan.length > 0) {
                html += '<div class="action-plan-card">';
                html += '<h3>📋 แผนปฏิบัติการ (Action Plan)</h3>';
                html += '<table class="action-table">';
                html += '<thead><tr><th>ลำดับ</th><th>ความสำคัญ</th><th>สิ่งที่ต้องทำ</th><th>กรอบเวลา</th><th>ผลกระทบ</th></tr></thead>';
                html += '<tbody>';
                a.action_plan.forEach(function (item, i) {
                    var pClass = item.priority === 'สูง' ? 'priority-high' : (item.priority === 'กลาง' ? 'priority-mid' : 'priority-low');
                    html += '<tr>';
                    html += '<td>' + (i + 1) + '</td>';
                    html += '<td class="' + pClass + '">' + escapeHtml(item.priority) + '</td>';
                    html += '<td>' + escapeHtml(item.action) + '</td>';
                    html += '<td>' + escapeHtml(item.timeline) + '</td>';
                    html += '<td>' + escapeHtml(item.impact) + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table>';
                html += '</div>';
            }

            // ---- Encouragement ----
            if (a.encouragement) {
                html += '<div class="encouragement-card">';
                html += '<p>' + escapeHtml(a.encouragement) + '</p>';
                html += '</div>';
            }

            resultsArea.innerHTML = html;

            // Animate gauge bar
            setTimeout(function () {
                var bar = document.getElementById('gaugeBar');
                if (bar) bar.style.width = readiness + '%';
            }, 200);

            // Bind copy button
            var copyBtn = document.getElementById('copyPromptBtn');
            if (copyBtn) {
                copyBtn.addEventListener('click', function() {
                    var d = window._tcasPromptData;
                    if (!d) { alert('ไม่มีข้อมูล'); return; }
                    var gpa = d.gpa_analysis || {};
                    var port = d.portfolio_analysis || {};
                    var skills = d.skills_gap || {};
                    var riasec = d.riasec_alignment || {};
                    var prompt = '📋 **TCAS Gap Analysis — โปรดวิเคราะห์ให้ฉันด้วย**\n\n';
                    prompt += 'ฉันเป็นนักเรียนโรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์ ต้องการเข้าศึกษาต่อคณะ ' + d.faculty + '\n\n';
                    prompt += '--- ข้อมูลส่วนตัว ---\n';
                    prompt += 'ชื่อ: ' + d.student_name + '\n';
                    prompt += 'ระดับชั้น: ม.' + d.class_year + '/' + d.class_room + '\n';
                    prompt += 'GPA ปัจจุบัน: ' + d.gpa + '\n';
                    prompt += 'ความพร้อมโดยรวม: ' + d.readiness + '%\n\n';
                    prompt += '--- ผลการเรียน ---\n';
                    prompt += 'GPA ปัจจุบัน: ' + (gpa.current_gpa || 0) + '\n';
                    prompt += 'GPA ที่ต้องการ: ' + (gpa.expected_gpa || 0) + '\n';
                    prompt += 'สถานะ: ' + (gpa.verdict || 'ไม่ทราบ') + '\n';
                    if (gpa.advice) prompt += 'คำแนะนำ: ' + gpa.advice + '\n';
                    prompt += '\n--- พอร์ตโฟลิโอ ---\n';
                    prompt += 'ผลงานทั้งหมด: ' + (port.total_items || 0) + ' รายการ\n';
                    prompt += 'คุณภาพพอร์ต: ' + (port.quality_score_pct || 0) + '%\n';
                    if (port.relevant_categories && port.relevant_categories.length) prompt += 'หมวดที่มี: ' + port.relevant_categories.join(', ') + '\n';
                    if (port.missing_categories && port.missing_categories.length) prompt += 'หมวดที่ขาด: ' + port.missing_categories.join(', ') + '\n';
                    if (port.advice) prompt += 'คำแนะนำ: ' + port.advice + '\n';
                    prompt += '\n--- ทักษะ ---\n';
                    if (skills.existing_skills && skills.existing_skills.length) prompt += 'ทักษะที่มี: ' + skills.existing_skills.join(', ') + '\n';
                    if (skills.missing_skills && skills.missing_skills.length) prompt += 'ทักษะที่ขาด: ' + skills.missing_skills.join(', ') + '\n';
                    if (skills.advice) prompt += 'คำแนะนำ: ' + skills.advice + '\n';
                    prompt += '\n--- RIASEC ---\n';
                    prompt += 'ความสอดคล้อง: ' + (riasec.alignment || 'ไม่ทราบ') + '\n';
                    if (riasec.dominant_traits && riasec.dominant_traits.length) prompt += 'Traits เด่น: ' + riasec.dominant_traits.join(', ') + '\n';
                    if (riasec.advice) prompt += 'คำแนะนำ: ' + riasec.advice + '\n';
                    prompt += '\n--- โปรดช่วย ---\n';
                    prompt += '1. วิเคราะห์จุดแข็งและจุดอ่อนของฉัน\n';
                    prompt += '2. แนะนำกิจกรรม/ผลงานที่ควรทำเพิ่มเพื่อเพิ่มโอกาสสอบติด\n';
                    prompt += '3. วางแผน timeline การเตรียมตัว (เช่น ควรทำอะไรก่อน-หลัง)\n';
                    prompt += '4. ให้คำแนะนำเฉพาะสำหรับการเข้าคณะ' + d.faculty + '\n';
                    prompt += '5. ให้กำลังใจและแนวทางที่นำไปปฏิบัติได้จริง';
                    var done = function() { alert('✅ คัดลอก prompt สำเร็จ! นำไปวางใน ChatGPT หรือ Gemini ได้เลย'); };
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(prompt).then(done).catch(done);
                    } else {
                        var ta = document.createElement('textarea');
                        ta.value = prompt; document.body.appendChild(ta); ta.select();
                        document.execCommand('copy'); document.body.removeChild(ta);
                        done();
                    }
                });
            }
        }

        // Helper: Build analysis card HTML
        function buildAnalysisCard(title, gradientBg, iconColor, content) {
            return '<div class="analysis-card">' +
                '<h3>' + title + '</h3>' +
                '<div>' + content + '</div>' +
                '</div>';
        }

        // Helper: Escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Copy TCAS Prompt — generates a well-formatted prompt for ChatGPT/Gemini
        window.copyTCASPrompt = function (faculty, readiness, gpa, portfolio, skills, riasec) {
            var profileInfo = 'นักเรียนรหัส: <?= e($profile['full_name']) ?>\n' +
                'ระดับชั้น: ม.<?= $profile['class_year'] ?>/<?= e($profile['class_room']) ?>\n' +
                'GPA ปัจจุบัน: <?= number_format($gpa, 2) ?>\n';

            var prompt = '📋 **TCAS Gap Analysis — โปรดวิเคราะห์ให้ฉันด้วย**\n\n' +
                'ฉันเป็นนักเรียนโรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์ ต้องการเข้าศึกษาต่อคณะ ' + faculty + '\n\n' +
                '--- ข้อมูลส่วนตัว ---\n' + profileInfo + '\n' +
                'ความพร้อมโดยรวม: ' + readiness + '%\n\n' +
                '--- ผลการเรียน ---\n' +
                'GPA ปัจจุบัน: ' + (gpa.current_gpa || 0) + '\n' +
                'GPA ที่ต้องการ: ' + (gpa.expected_gpa || 0) + '\n' +
                'สถานะ: ' + (gpa.verdict || 'ไม่ทราบ') + '\n' +
                (gpa.advice ? 'คำแนะนำ: ' + gpa.advice + '\n' : '') + '\n' +
                '--- พอร์ตโฟลิโอ ---\n' +
                'ผลงานทั้งหมด: ' + (portfolio.total_items || 0) + ' รายการ\n' +
                'คุณภาพพอร์ต: ' + (portfolio.quality_score_pct || 0) + '%\n' +
                (portfolio.relevant_categories && portfolio.relevant_categories.length > 0 ? 'หมวดที่มี: ' + portfolio.relevant_categories.join(', ') + '\n' : '') +
                (portfolio.missing_categories && portfolio.missing_categories.length > 0 ? 'หมวดที่ขาด: ' + portfolio.missing_categories.join(', ') + '\n' : '') +
                (portfolio.advice ? 'คำแนะนำ: ' + portfolio.advice + '\n' : '') + '\n' +
                '--- ทักษะ ---\n' +
                (skills.existing_skills && skills.existing_skills.length > 0 ? 'ทักษะที่มี: ' + skills.existing_skills.join(', ') + '\n' : '') +
                (skills.missing_skills && skills.missing_skills.length > 0 ? 'ทักษะที่ขาด: ' + skills.missing_skills.join(', ') + '\n' : '') +
                (skills.advice ? 'คำแนะนำ: ' + skills.advice + '\n' : '') + '\n' +
                '--- RIASEC ---\n' +
                'ความสอดคล้อง: ' + (riasec.alignment || 'ไม่ทราบ') + '\n' +
                (riasec.dominant_traits ? ' Traits เด่น: ' + riasec.dominant_traits.join(', ') + '\n' : '') +
                (riasec.advice ? 'คำแนะนำ: ' + riasec.advice + '\n' : '') + '\n' +
                '--- โปรดช่วย ---\n' +
                '1. วิเคราะห์จุดแข็งและจุดอ่อนของฉัน\n' +
                '2. แนะนำกิจกรรม/ผลงานที่ควรทำเพิ่มเพื่อเพิ่มโอกาสสอบติด\n' +
                '3. วางแผน timeline การเตรียมตัว (เช่น ควรทำอะไรก่อน-หลัง)\n' +
                '4. ให้คำแนะนำเฉพาะสำหรับการเข้าคณะ' + faculty + '\n' +
                '5. ให้กำลังใจและแนวทางที่นำไปปฏิบัติได้จริง';

            navigator.clipboard.writeText(prompt).then(function () {
                alert('✅ คัดลอก prompt สำเร็จ! นำไปวางใน ChatGPT หรือ Gemini ได้เลย');
            }).catch(function () {
                // Fallback for older browsers
                var textarea = document.createElement('textarea');
                textarea.value = prompt;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                alert('✅ คัดลอก prompt สำเร็จ! นำไปวางใน ChatGPT หรือ Gemini ได้เลย');
            });
        };

        // Auto-analyze on page load if faculty is pre-selected
        // ( Uncomment to enable auto-analysis )
        // analyzeBtn.click();
    })();
</script>