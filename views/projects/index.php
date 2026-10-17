<?php $pageTitle = $title ?? 'โครงงานของฉัน'; ?>

<style>
/* Modern Glassmorphism & Gradient Styles */
.project-dashboard {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
    animation: fadeIn 0.5s ease-out;
    font-family: 'Inter', 'Sarabun', sans-serif;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.dashboard-title h1 {
    font-size: 2.2rem;
    font-weight: 800;
    background: linear-gradient(135deg, #1e293b, #4f46e5);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin: 0;
}

.dashboard-title p {
    color: #64748b;
    margin-top: 0.5rem;
}

.btn-create {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: white;
    padding: 0.8rem 1.8rem;
    border-radius: 9999px;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 4px 14px 0 rgba(79, 70, 229, 0.39);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-create:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(79, 70, 229, 0.5);
    color: white;
}

.project-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 2rem;
}

.project-card {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: 20px;
    padding: 1.8rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.04);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.project-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
}

.project-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 6px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899);
}

.status-badge {
    position: absolute;
    top: 1.2rem;
    right: 1.2rem;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.4rem 1rem;
    border-radius: 9999px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-draft { background: #f1f5f9; color: #475569; }
.status-proposal_submitted { background: #fffbeb; color: #d97706; }
.status-proposal_approved { background: #ecfdf5; color: #059669; }
.status-proposal_rejected { background: #fef2f2; color: #dc2626; }
.status-in_progress { background: #e0e7ff; color: #4338ca; }
.status-completed { background: #dcfce7; color: #15803d; }

.project-type {
    font-size: 0.8rem;
    color: #6366f1;
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: inline-block;
    padding: 0.2rem 0.8rem;
    background: #eef2ff;
    border-radius: 6px;
}

.project-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0.5rem 0 1rem 0;
    line-height: 1.4;
}

.project-meta {
    margin-top: auto;
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
    border-top: 1px solid #f1f5f9;
    padding-top: 1.2rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.9rem;
    color: #64748b;
}

.meta-item i {
    color: #94a3b8;
    width: 16px;
}

.btn-view {
    margin-top: 1.5rem;
    width: 100%;
    text-align: center;
    padding: 0.75rem;
    background: #f8fafc;
    color: #475569;
    font-weight: 600;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-view:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    grid-column: 1 / -1;
}

.empty-state img {
    width: 180px;
    opacity: 0.6;
    margin-bottom: 1.5rem;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<div class="project-dashboard">

    <?php if (Session::hasFlash('success')): ?>
        <div class="alert alert-success" style="background:#ecfdf5; color:#059669; padding:1rem; border-radius:12px; margin-bottom:2rem; border-left:4px solid #10b981;">
            ✅ <?= htmlspecialchars(Session::getFlash('success')) ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-header">
        <div class="dashboard-title">
            <h1>🔬 โครงงานวิทยาศาสตร์ของฉัน</h1>
            <p>ติดตามสถานะ บันทึกความก้าวหน้า และส่งรายงานโครงงานวิทยาศาสตร์</p>
        </div>
        <a href="<?= url('projects/create') ?>" class="btn-create">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            เสนอโครงงานใหม่
        </a>
    </div>

    <div class="project-grid">
        <?php if (empty($projects)): ?>
            <div class="empty-state">
                <h3 style="color:#1e293b; font-size:1.5rem; margin-bottom:0.5rem;">ยังไม่มีข้อมูลโครงงาน</h3>
                <p style="color:#64748b; margin-bottom:1.5rem;">คุณยังไม่มีรายการโครงงานในระบบ เริ่มต้นด้วยการยื่นข้อเสนอโครงงานใหม่ได้เลย</p>
                <a href="<?= url('projects/create') ?>" class="btn-create" style="display:inline-flex;">+ เสนอโครงงานใหม่</a>
            </div>
        <?php else: ?>
            <?php foreach ($projects as $project): ?>
                <?php 
                    // Format Date
                    $date = new DateTime($project['updated_at']);
                    $formattedDate = $date->format('d M Y'); 
                ?>
                <div class="project-card">
                    <span class="status-badge status-<?= $project['status'] ?>">
                        <?= str_replace('_', ' ', $project['status']) ?>
                    </span>
                    
                    <span class="project-type"><?= htmlspecialchars($project['project_code']) ?></span>
                    <h3 class="project-title"><?= htmlspecialchars($project['title_th']) ?></h3>
                    
                    <div class="project-meta">
                        <div class="meta-item">
                            <span>👨‍🏫 ที่ปรึกษา: <strong style="color:#475569;"><?= htmlspecialchars($project['advisor_name'] ?? $project['advisor_username'] ?? 'กำลังรอผู้รับผิดชอบ') ?></strong></span>
                        </div>
                        <div class="meta-item">
                            <span>📚 รายวิชา: <?= htmlspecialchars($project['subject_name'] ?? 'โครงงานอิสระ') ?></span>
                        </div>
                        <div class="meta-item">
                            <span>🕒 อัปเดตล่าสุด: <?= $formattedDate ?></span>
                        </div>
                    </div>
                    
                    <a href="<?= url('projects/' . $project['id']) ?>" class="btn-view">ดูรายละเอียดโครงงาน &rarr;</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>
