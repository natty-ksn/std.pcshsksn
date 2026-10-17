<?php $pageTitle = 'PCSHSKSN e-Portfolio'; ?>
<div class="auth-page"
    style="flex-direction: column; gap: 32px; text-align: center; background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%); min-height: 100vh; padding: 40px 20px;">
    <div style="position: relative; z-index: 10; animation: fadeUp 0.8s ease; max-width: 860px; margin: 0 auto;">
        <img src="<?= asset('images/logo.png') ?>" alt="PCSHSKSN Logo"
            onerror="this.onerror=null; this.src='https://krunat.online/logo3.png';"
            style="height: 120px; width: auto; max-width: 110px; margin-bottom: 24px; filter: drop-shadow(0 6px 16px rgba(0,0,0,0.4)); object-fit: contain;">
        <h1 style="font-size: 2.8rem; color: white; margin-bottom: 12px; font-weight: 800; letter-spacing: -0.5px;">
            PCSHSKSN e-Portfolio</h1>
        <p style="color: #cbd5e1; font-size: 1.25rem; max-width: 700px; line-height: 1.6; margin: 0 auto;">
            ระบบแฟ้มสะสมผลงานอัจฉริยะ สำหรับนักเรียนผู้มีความสามารถพิเศษ<br>
            <span style="color: #fbbf24; font-weight: 600;">โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์</span>
        </p>
        <p style="color: #94a3b8; font-size: 1rem; max-width: 620px; margin: 12px auto 0; line-height: 1.5;">
            แพลตฟอร์มบูรณาการ เชื่อมโยงนักเรียน ครูที่ปรึกษา และฝ่ายบริหาร เข้ากับระบบประเมิน AI และการเตรียมความพร้อมสู่ TCAS
        </p>
        <div style="margin-top: 36px; display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <a href="<?= url('login') ?>" class="btn btn-lg"
                style="background: #f59e0b; color: #451a03; font-weight: 700; box-shadow: 0 10px 25px -5px rgba(245,158,11,0.4); border:none; padding: 14px 36px; border-radius: 12px; transition: all 0.3s ease; text-decoration: none; font-size: 1.1rem;">
                🔑 เข้าสู่ระบบใช้งาน
            </a>
        </div>
    </div>

    <!-- Stakeholder Feature Cards (Students, Teachers, Executives) -->
    <div
        style="position: relative; z-index: 10; display: flex; gap: 20px; flex-wrap: wrap; justify-content: center; max-width: 1000px; animation: fadeUp 1s ease; margin-top: 24px; margin-left: auto; margin-right: auto;">
        
        <!-- Student Card -->
        <div
            style="background: rgba(255,255,255,0.06); backdrop-filter: blur(12px); padding: 28px 22px; border-radius: 20px; flex: 1 1 280px; max-width: 310px; border: 1px solid rgba(255,255,255,0.12); text-align: left; transition: transform 0.2s ease;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                <span style="font-size: 2rem;">🎓</span>
                <h4 style="color: white; font-size: 1.15rem; font-weight: 700; margin: 0;">สำหรับนักเรียน</h4>
            </div>
            <p style="color: #cbd5e1; font-size: 0.9rem; line-height: 1.55; margin: 0;">
                บันทึกแฟ้มสะสมผลงาน เกียรติบัตร โครงงานวิทยาศาสตร์ พร้อมระบบ <strong>AI Advisor</strong> วิเคราะห์ความพร้อมรอบ TCAS Portfolio
            </p>
        </div>

        <!-- Teacher Card -->
        <div
            style="background: rgba(255,255,255,0.06); backdrop-filter: blur(12px); padding: 28px 22px; border-radius: 20px; flex: 1 1 280px; max-width: 310px; border: 1px solid rgba(255,255,255,0.12); text-align: left; transition: transform 0.2s ease;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                <span style="font-size: 2rem;">👨‍🏫</span>
                <h4 style="color: white; font-size: 1.15rem; font-weight: 700; margin: 0;">สำหรับครูและที่ปรึกษา</h4>
            </div>
            <p style="color: #cbd5e1; font-size: 0.9rem; line-height: 1.55; margin: 0;">
                ตรวจสอบและรับรองผลงานนักเรียน ให้คำปรึกษาโครงงาน (Mentorship) พร้อมเครื่องมือ <strong>Quick Sweep</strong> ช่วยตรวจอย่างรวดเร็ว
            </p>
        </div>

        <!-- Executive Card -->
        <div
            style="background: rgba(255,255,255,0.06); backdrop-filter: blur(12px); padding: 28px 22px; border-radius: 20px; flex: 1 1 280px; max-width: 310px; border: 1px solid rgba(255,255,255,0.12); text-align: left; transition: transform 0.2s ease;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                <span style="font-size: 2rem;">📊</span>
                <h4 style="color: white; font-size: 1.15rem; font-weight: 700; margin: 0;">สำหรับฝ่ายบริหาร</h4>
            </div>
            <p style="color: #cbd5e1; font-size: 0.9rem; line-height: 1.55; margin: 0;">
                แดชบอร์ดสถิติภาพรวมโรงเรียน ระบบเตือนภัยล่วงหน้า (Early Warning System) เพื่อช่วยเหลือผู้เรียนได้ทันท่วงทีและส่งออกรายงาน
            </p>
        </div>
    </div>

    <footer style="margin-top: 40px; color: #64748b; font-size: 0.85rem; z-index: 10;">
        &copy; <?= date('Y') ?> Princess Chulabhorn Science High School Kalasin. All rights reserved.
    </footer>
</div>