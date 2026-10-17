<?php $pageTitle = 'แบบประเมินตนเอง (RIASEC)'; ?>

<div class="card" style="max-width: 800px; margin: 0 auto; padding: 40px; text-align: center;">
    <div style="font-size: 4rem; margin-bottom: 20px;">🧭</div>
    <h2 style="color: var(--primary-700); font-weight: 700; font-size: 2rem;">ค้นหาดวงดาวแห่งอาชีพของคุณ</h2>
    <p style="color: var(--gray-500); font-size: 1.1rem; margin-top: 10px;">
        แบบประเมินความสนใจทางอาชีพ อ้างอิงตามทฤษฎีมาตรฐานของจอห์น ฮอลแลนด์ (John Holland's RIASEC Model)
    </p>

    <div style="margin: 32px 0; text-align: left; background: var(--gray-50); padding: 24px; border-radius: 12px; border: 1px solid var(--gray-200);">
        <h3 style="margin-bottom: 16px; color: var(--gray-800);">ทฤษฎี RIASEC คืออะไร?</h3>
        <p style="color: var(--gray-600); line-height: 1.7; margin-bottom: 16px;">
            ฮอลแลนด์เชื่อว่าเราทุกคนมีบุคลิกภาพที่โดดเด่นซึ่งเชื่อมโยงกับสภาพแวดล้อมการทำงานแบบต่างๆ หากเราเลือกเรียนและทำงานในสายที่ตรงกับบุคลิกภาพของเรา เราจะมีความสุขและมีโอกาสประสบความสำเร็จสูงขึ้น!
        </p>
        <ul style="color: var(--gray-600); line-height: 1.7; padding-left: 20px;">
            <li><strong>R - Realistic:</strong> นักปฏิบัติ ชอบลงมือทำ ชอบเครื่องยนต์</li>
            <li><strong>I - Investigative:</strong> นักวิเคราะห์ ชอบวิทยาศาสตร์ การวิจัย</li>
            <li><strong>A - Artistic:</strong> นักสร้างสรรค์ ชอบศิลปะ อิสระ</li>
            <li><strong>S - Social:</strong> นักเจรจา ชอบช่วยเหลือคนอื่น สังคม</li>
            <li><strong>E - Enterprising:</strong> นักบริหาร ชอบการเป็นผู้นำ ดำเนินธุรกิจ</li>
            <li><strong>C - Conventional:</strong> นักจัดการ ชอบความมีระเบียบ จัดการเอกสาร</li>
        </ul>
    </div>

    <p style="color: var(--warning-600); margin-bottom: 24px; font-weight: 500;">
        ⚠️ แบบประเมินนี้ใช้เวลาประมาณ 5 นาที กรุณาตอบตามความเป็นจริงมากที่สุดเพื่อความแม่นยำ
    </p>

    <a href="<?= url('assessment/test') ?>" class="btn btn-primary btn-lg" style="font-size: 1.1rem; padding: 12px 32px; border-radius: 50px;">
        🚀 เริ่มทำแบบประเมิน
    </a>
</div>
