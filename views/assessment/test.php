<?php $pageTitle = 'ทำแบบประเมิน (RIASEC)'; ?>

<div style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <!-- Stepper / Status Card -->
    <div class="card mb-4 slide-up" style="border: none; background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: white; border-radius: 20px; box-shadow: 0 10px 25px rgba(30, 58, 138, 0.15); overflow: hidden; position: relative;">
        <div class="card-body" style="padding: 32px; position: relative; z-index: 1;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <span style="background: rgba(255,255,255,0.2); backdrop-filter: blur(8px); padding: 4px 12px; border-radius: 50px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">🧭 RIASEC Interest Profiler</span>
                    <h1 style="font-size: 1.8rem; font-weight: 900; margin: 8px 0 4px; color: #fff;">แบบวัดแววความสนใจอาชีพ</h1>
                    <p style="color: #dbeafe; font-size: 0.9rem; margin: 0; line-height: 1.5;">โปรดพิจารณาความรู้สึกของคุณต่อกิจกรรมต่างๆ โดยเลือกตอบตามตรงเพื่อผลลัพธ์ที่แม่นยำที่สุด</p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 2rem; font-weight: 900; color: #f59e0b;" id="progress-text">0%</div>
                    <div style="font-size: 0.75rem; color: #e0f2fe; text-transform: uppercase; letter-spacing: 1px;">ความคืบหน้า</div>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div style="background: rgba(255, 255, 255, 0.25); border-radius: 99px; height: 10px; overflow: hidden; margin-top: 20px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.2);">
                <div id="progress-bar" style="background: #f59e0b; height: 10px; width: 0%; border-radius: 99px; transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);"></div>
            </div>
        </div>
        <div style="position: absolute; right: -20px; bottom: -30px; font-size: 10rem; opacity: 0.08; transform: rotate(15deg); pointer-events: none;">🧭</div>
    </div>

    <!-- Alert Messages -->
    <?php if (Session::hasFlash('error')): ?>
        <div class="alert alert-error" style="border-radius: 12px; margin-bottom: 24px; padding: 16px; background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; display: flex; align-items: center; gap: 8px;">
            ❌ <?= e(Session::getFlash('error')) ?>
        </div>
    <?php endif; ?>

    <!-- Interactive Multistep Form -->
    <form method="POST" action="<?= url('assessment/store') ?>" id="riasec-form">
        <?= CSRF::field() ?>
        
        <div id="questions-container" style="display: flex; flex-direction: column; gap: 20px;">
            <?php foreach ($testItems as $index => $item): ?>
                <!-- Question Card -->
                <div class="riasec-question-card" data-index="<?= $index ?>" style="background: white; padding: 24px; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); display: flex; align-items: center; justify-content: space-between; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); gap: 16px; flex-wrap: wrap;">
                    
                    <div style="display: flex; align-items: flex-start; gap: 16px; flex: 1; min-width: 250px;">
                        <span style="font-size: 1.1rem; font-weight: 800; color: #3b82f6; background: #eff6ff; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #dbeafe;">
                            <?= $index + 1 ?>
                        </span>
                        <div style="flex: 1; padding-top: 4px;">
                            <span style="color: #1e293b; font-size: 1.05rem; font-weight: 600; line-height: 1.6; display: block;">
                                <?= e($item['text']) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Choice Area -->
                    <div style="display: flex; gap: 12px; align-items: center; flex-shrink: 0;">
                        <label class="choice-label" style="cursor: pointer; position: relative;">
                            <input type="radio" class="choice-radio btn-yes" name="q[<?= $item['id'] ?>]" value="1" required style="display: none;" onchange="updateProgress()">
                            <div class="choice-box yes-box">
                                <span style="font-size: 1.1rem; margin-right: 4px;">👍</span> ชอบ
                            </div>
                        </label>
                        <label class="choice-label" style="cursor: pointer; position: relative;">
                            <input type="radio" class="choice-radio btn-no" name="q[<?= $item['id'] ?>]" value="0" style="display: none;" onchange="updateProgress()">
                            <div class="choice-box no-box">
                                <span style="font-size: 1.1rem; margin-right: 4px;">👎</span> ไม่ชอบ
                            </div>
                        </label>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <!-- Submit Section -->
        <div style="margin-top: 50px; text-align: center; margin-bottom: 50px;">
            <button type="submit" id="submit-btn" class="btn btn-primary btn-lg" style="padding: 18px 60px; border-radius: 50px; font-size: 1.15rem; font-weight: 800; box-shadow: 0 10px 25px rgba(59, 130, 246, 0.35); transition: all 0.3s; opacity: 0.6; pointer-events: none;">
                ✨ ส่งแบบประเมินและดูผลลัพธ์
            </button>
            <p id="alert-helper" style="font-size: 0.85rem; color: #ef4444; margin-top: 12px; font-weight: 600;">โปรดตอบคำถามให้ครบทุกข้อเพื่อส่งแบบสอบถาม (เหลืออีก <span id="remaining-count">48</span> ข้อ)</p>
        </div>
    </form>
</div>

<style>
    .riasec-question-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        transform: translateY(-2px);
    }

    .riasec-question-card.answered {
        border-color: #cbd5e1;
        background: #f8fafc;
        box-shadow: none;
    }

    .choice-box {
        padding: 10px 24px;
        border-radius: 30px;
        border: 2px solid #e2e8f0;
        color: #64748b;
        font-weight: 700;
        font-size: 0.95rem;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        background: white;
        text-align: center;
        min-width: 110px;
        user-select: none;
    }

    .choice-box:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    /* Checked style for Yes (Like) */
    input[type="radio"].btn-yes:checked + .choice-box.yes-box {
        background: #ecfdf5;
        border-color: #10b981;
        color: #047857;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
        transform: scale(1.03);
    }

    /* Checked style for No (Dislike) */
    input[type="radio"].btn-no:checked + .choice-box.no-box {
        background: #fef2f2;
        border-color: #ef4444;
        color: #b91c1c;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
        transform: scale(1.03);
    }
</style>

<script>
    const totalQuestions = <?= count($testItems) ?>;

    function updateProgress() {
        // Count checked radios
        const checkedRadios = document.querySelectorAll('.choice-radio:checked');
        const answeredCount = checkedRadios.length;
        
        // Update progress indicators
        const progressPct = Math.round((answeredCount / totalQuestions) * 100);
        document.getElementById('progress-bar').style.width = progressPct + '%';
        document.getElementById('progress-text').innerText = progressPct + '%';

        // Highlight card state
        document.querySelectorAll('.riasec-question-card').forEach(card => {
            const index = card.getAttribute('data-index');
            const radios = card.querySelectorAll('.choice-radio');
            let isAnswered = false;
            radios.forEach(radio => {
                if (radio.checked) isAnswered = true;
            });
            if (isAnswered) {
                card.classList.add('answered');
            } else {
                card.classList.remove('answered');
            }
        });

        // Toggle submit button
        const remaining = totalQuestions - answeredCount;
        const submitBtn = document.getElementById('submit-btn');
        const helperText = document.getElementById('alert-helper');

        if (remaining <= 0) {
            submitBtn.style.opacity = '1';
            submitBtn.style.pointerEvents = 'auto';
            submitBtn.style.transform = 'scale(1.05)';
            helperText.innerHTML = '🎉 ตอบครบถ้วนแล้ว! สามารถกดยื่นแบบประเมินได้เลย';
            helperText.style.color = '#10b981';
        } else {
            submitBtn.style.opacity = '0.6';
            submitBtn.style.pointerEvents = 'none';
            submitBtn.style.transform = 'none';
            helperText.innerHTML = `โปรดตอบคำถามให้ครบทุกข้อเพื่อส่งแบบสอบถาม (เหลืออีก <span id="remaining-count" style="font-size:1.1rem; color:#ef4444; font-weight:800;">${remaining}</span> ข้อ)`;
            helperText.style.color = '#ef4444';
        }
    }

    // Initialize progress on load (useful if browser restores form state)
    document.addEventListener('DOMContentLoaded', () => {
        updateProgress();
    });
</script>
