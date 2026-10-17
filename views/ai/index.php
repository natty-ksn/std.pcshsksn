<?php $pageTitle = 'AI แนะแนว'; ?>
<div style="max-width: 800px; margin: 0 auto;">
    <h2 class="mb-3">🤖 AI แนะแนวการศึกษา</h2>

    <style>
        .ai-option-card {
            display: flex;
            gap: 16px;
            padding: 20px;
            border: 2px solid var(--gray-200);
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            align-items: flex-start;
        }

        .ai-option-card:hover {
            border-color: var(--primary-400);
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.06);
        }

        .ai-option-card.selected {
            border-color: var(--primary-600);
            background: #eef2ff;
            box-shadow: 0 8px 16px rgba(99, 102, 241, 0.15);
        }

        .ai-option-card input[type="radio"] {
            display: none;
        }

        .ai-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
            transition: all 0.3s;
        }

        .ai-option-card.selected .ai-icon-box {
            background: var(--primary-600);
            color: white;
            transform: rotate(-5deg) scale(1.1);
        }
    </style>

    <!-- Welcome & Context -->
    <div class="card mb-4 slide-up"
        style="border: none; background: linear-gradient(135deg, #1e293b, #0f172a); color: white; border-radius: 20px; overflow: hidden; position: relative;">
        <div class="card-body" style="padding: 32px; position: relative; z-index: 1;">
            <div style="max-width: 600px;">
                <h1 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 12px; color: #fff;">
                    <span style="font-size: 2rem;">🧠</span> เข็มทิศอนาคตอัจฉริยะ (AI Navigator)
                </h1>
                <p style="font-size: 1rem; color: #cbd5e1; line-height: 1.6; margin-bottom: 0;">
                    ระบบจะวิเคราะห์ประวัติผลงานวิชาการ ทักษะความสามารถ (RIASEC) และเป้าหมายของคุณ
                    เพื่อสร้าง <strong>"แผนปฏิบัติการล่วงหน้า (Action Plan)"</strong> ให้นำไปใช้จริงได้ทันที
                </p>
            </div>
        </div>
        <div
            style="position: absolute; right: -20px; bottom: -40px; font-size: 12rem; opacity: 0.05; transform: rotate(15deg); pointer-events: none;">
            🧭</div>
    </div>

    <!-- Analysis Options -->
    <div class="card mb-4 slide-up" style="border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border-radius: 20px;">
        <div class="card-body" style="padding: 32px;">
            <h3 style="margin-bottom: 24px; color: var(--gray-800); font-weight: 700;">1. ให้ AI
                ช่วยคุณเรื่องอะไรดีในวันนี้?</h3>
            <form method="POST" action="<?= url('ai-guide/analyze') ?>" id="ai-form" onsubmit="showAILoading()">
                <?= CSRF::field() ?>

                <div class="grid-2" style="gap: 20px; margin-bottom: 30px;">
                    <label class="ai-option-card selected">
                        <input type="radio" name="analysis_type" value="portfolio_gap" checked>
                        <div class="ai-icon-box">📋</div>
                        <div>
                            <div
                                style="font-weight: 700; color: var(--gray-800); font-size: 1.1rem; margin-bottom: 4px;">
                                วิเคราะห์หาจุดอ่อนพอร์ตโฟลิโอ</div>
                            <div style="font-size: 0.85rem; color: var(--gray-500); line-height: 1.5;">
                                เทียบข้อมูลการประเมินตนเองกับคณะในฝัน หากมีจุดไหนแหว่ง AI
                                จะบอกกิจกรรมให้ไปเติมเต็มพอร์ตทันที</div>
                        </div>
                    </label>

                    <label class="ai-option-card">
                        <input type="radio" name="analysis_type" value="career_match">
                        <div class="ai-icon-box">🎓</div>
                        <div>
                            <div
                                style="font-weight: 700; color: var(--gray-800); font-size: 1.1rem; margin-bottom: 4px;">
                                ค้นหาคณะ/สาขาที่เกิดมาเพื่อคุณ</div>
                            <div style="font-size: 0.85rem; color: var(--gray-500); line-height: 1.5;">
                                เบื่อไหมที่ไม่รู้จะเรียนอะไรดีให้ AI ประเมินจุดแข็งจากประวัติต่างๆ แล้วแนะนำ 3
                                สาขาหลักที่เปอร์เซนต์สอบติดสูงสุด</div>
                        </div>
                    </label>

                    <label class="ai-option-card">
                        <input type="radio" name="analysis_type" value="study_guide">
                        <div class="ai-icon-box">📚</div>
                        <div>
                            <div
                                style="font-weight: 700; color: var(--gray-800); font-size: 1.1rem; margin-bottom: 4px;">
                                ออกแบบ Roadmap การเตรียมตัวสอบ</div>
                            <div style="font-size: 0.85rem; color: var(--gray-500); line-height: 1.5;">ให้ AI
                                วางแผนเตรียมความพร้อมในระยะยาว ควรลงค่ายอะไร ช่วงไหน หรือฝึกเนื้อหาวิชาใดเพิ่ม</div>
                        </div>
                    </label>

                    <label class="ai-option-card">
                        <input type="radio" name="analysis_type" value="skill_suggest">
                        <div class="ai-icon-box">🧩</div>
                        <div>
                            <div
                                style="font-weight: 700; color: var(--gray-800); font-size: 1.1rem; margin-bottom: 4px;">
                                ชี้เป้า Skill สำคัญที่เด็กยุคนี้ต้องมี</div>
                            <div style="font-size: 0.85rem; color: var(--gray-500); line-height: 1.5;">เด็กวิทยาศาสตร์ฯ
                                ควบคุมเครื่องมือสมัยใหม่ได้แค่ไหน ให้ AI ชี้แนะทักษะ (Soft/Hard Skills) แห่งอนาคต</div>
                        </div>
                    </label>
                </div>

                <?php if (!$hasApiKey): ?>
                    <div class="alert alert-warning"
                        style="margin-bottom: 20px; border-radius: 12px; background: #fffbeb; border: 1px solid #fef3c7; color: #b45309;">
                        ⚠️ <strong>หมายเหตุ:</strong> ขณะนี้ระบบไม่ได้เชื่อมต่อ API ภายนอก
                        จะใช้ระบบวิเคราะห์สคริปต์สถิติมาตรฐาน (Rule-based) จากทางโรงเรียนแทน
                    </div>
                <?php endif; ?>

                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" id="ai-submit-btn" class="btn btn-primary btn-lg"
                        style="padding: 16px 48px; border-radius: 50px; font-size: 1.1rem; font-weight: 700; box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3); transition: all 0.3s;">
                        ✨ เริ่มการประมวลผล (Start Analysis)
                    </button>

                    <div id="ai-loading" style="display: none; padding: 16px;">
                        <div
                            style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px;">
                            <div class="spinner"
                                style="width: 40px; height: 40px; border: 4px solid #e0e7ff; border-top-color: #4f46e5; border-radius: 50%; animation: spin 1s linear infinite;">
                            </div>
                            <div style="font-weight: 600; color: var(--primary-600); font-size: 1.1rem;">
                                กำลังเชื่อมต่อสมองกล AI ...</div>
                            <div style="font-size: 0.85rem; color: var(--gray-500);">กระบวนการนี้อาจใช้เวลา 5 - 15
                                วินาที ขึ้นอยู่กับปริมาณข้อมูลในเซิร์ฟเวอร์</div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Previous Recommendations -->
    <?php if (!empty($recommendations)): ?>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:32px;margin-bottom:16px;">
            <h3 class="mb-0">📜 ผลการวิเคราะห์ก่อนหน้า</h3>
            <button onclick="copyAIPrompt()"
                style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:10px;padding:8px 18px;font-weight:700;font-size:0.82rem;cursor:pointer;">
                📋 คัดลอก Prompt ไป ChatGPT / Gemini
            </button>
        </div>
        <?php foreach ($recommendations as $i => $rec): ?>
            <?php
            $typeLabels = ['portfolio_gap' => '📋 วิเคราะห์ Portfolio', 'career_match' => '🎓 แนะนำคณะ', 'study_guide' => '📚 แผนการเรียน', 'skill_suggest' => '🧩 พัฒนาทักษะ'];
            $response = json_decode($rec['response_data'], true);
            $text = is_string($response) ? $response : ($response['summary'] ?? json_encode($response, JSON_UNESCAPED_UNICODE));
            ?>
            <div class="card mb-4 slide-up"
                style="border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border-radius: 16px; overflow: hidden;">
                <div class="card-header"
                    style="background: white; border-bottom: 1px solid var(--gray-100); padding: 20px 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="margin: 0; font-size: 1.1rem; color: var(--primary-700);">
                            <?= $typeLabels[$rec['analysis_type']] ?? '🤖 AI Prediction' ?>
                        </h3>
                        <span
                            style="font-size:0.8rem;color:var(--gray-400); background: var(--gray-50); padding: 4px 10px; border-radius: 20px;">
                            วันที่ทำรายการ: <?= date('d M Y H:i', strtotime($rec['generated_at'])) ?>
                        </span>
                    </div>
                </div>
                <div class="card-body" style="padding: 24px;">
                    <div class="markdown-content" style="line-height: 1.8; color: var(--gray-800); font-size: 0.95rem;">
                        <?= htmlspecialchars($text) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Hidden data for copy -->
        <script>
            window._aiRecData = <?= json_encode([
                'student' => $profile['full_name'] ?? '',
                'class_year' => $profile['class_year'] ?? '',
                'class_room' => $profile['class_room'] ?? '',
                'bio' => $profile['bio'] ?? '',
                'recommendations' => array_map(function ($r) {
                    $resp = json_decode($r['response_data'], true);
                    return [
                        'type' => $r['analysis_type'],
                        'text' => is_string($resp) ? $resp : ($resp['summary'] ?? json_encode($resp, JSON_UNESCAPED_UNICODE))
                    ];
                }, $recommendations)
            ], JSON_UNESCAPED_UNICODE) ?>;
        </script>
    <?php endif; ?>
</div>

<style>
    @keyframes spin {
        100% {
            transform: rotate(360deg);
        }
    }
</style>
<script>
    // Radio Card Selection Highlight
    document.querySelectorAll('.ai-option-card').forEach(card => {
        card.addEventListener('click', function () {
            document.querySelectorAll('.ai-option-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            const radio = this.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });

    // Loading Animation
    function showAILoading() {
        document.getElementById('ai-submit-btn').style.display = 'none';
        document.getElementById('ai-loading').style.display = 'block';
    }

    // Copy AI Analysis Prompt
    function copyAIPrompt() {
        var d = window._aiRecData;
        if (!d) { alert('ไม่มีข้อมูล'); return; }
        var typeLabels = {
            'portfolio_gap': 'วิเคราะห์หาจุดอ่อนพอร์ตโฟลิโอ',
            'career_match': 'ค้นหาคณะ/สาขาที่เหมาะสม',
            'study_guide': 'ออกแบบแผนการเรียน',
            'skill_suggest': 'ชี้เป้าทักษะสำคัญ'
        };
        var prompt = '📋 **AI แนะแนวการศึกษา — โปรดวิเคราะห์ให้ฉันด้วย**\n\n';
        prompt += 'ฉันเป็นนักเรียนโรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์\n\n';
        prompt += '--- ข้อมูลส่วนตัว ---\n';
        prompt += 'ชื่อ: ' + d.student + '\n';
        prompt += 'ระดับชั้น: ม.' + d.class_year + '/' + d.class_room + '\n';
        if (d.bio) prompt += 'ข้อมูลส่วนตัว: ' + d.bio + '\n';
        prompt += '\n--- ผลการวิเคราะห์จากระบบ ---\n';
        d.recommendations.forEach(function (rec, i) {
            prompt += (i + 1) + '. [' + (typeLabels[rec.type] || rec.type) + ']\n';
            prompt += rec.text + '\n\n';
        });
        prompt += '--- โปรดช่วย ---\n';
        prompt += '1. วิเคราะห์ข้อมูลข้างต้นและให้คำแนะนำเพิ่มเติม\n';
        prompt += '2. แนะนำกิจกรรม/ผลงานที่ควรทำเพิ่ม\n';
        prompt += '3. วางแผนการพัฒนาทักษะในอนาคต\n';
        prompt += '4. ให้กำลังใจและแนวทางที่นำไปปฏิบัติได้จริง';

        var done = function () { alert('✅ คัดลอก prompt สำเร็จ! นำไปวางใน ChatGPT หรือ Gemini ได้เลย'); };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(prompt).then(done).catch(done);
        } else {
            var ta = document.createElement('textarea');
            ta.value = prompt; document.body.appendChild(ta); ta.select();
            document.execCommand('copy'); document.body.removeChild(ta);
            done();
        }
    }
</script>