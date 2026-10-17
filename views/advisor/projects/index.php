<?php $pageTitle = $title ?? 'แผงควบคุมที่ปรึกษาโครงงาน'; ?>

<style>
.advisor-dashboard {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
    font-family: 'Inter', 'Sarabun', sans-serif;
    animation: fadeIn 0.4s ease-out;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    padding-bottom: 1.5rem;
}

.dashboard-title h1 {
    font-size: 2.2rem;
    font-weight: 800;
    color: #1e293b;
    margin: 0 0 0.5rem 0;
}

.dashboard-title p {
    color: #64748b;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    border: 1px solid rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 1.5rem;
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.06);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
}

.stat-info h3 {
    margin: 0;
    font-size: 2rem;
    font-weight: 800;
    color: #1e293b;
}

.stat-info p {
    margin: 0;
    color: #64748b;
    font-weight: 500;
    font-size: 0.9rem;
}

.bg-primary { background: #eff6ff; color: #3b82f6; }
.bg-warning { background: #fffbeb; color: #d97706; }
.bg-success { background: #f0fdf4; color: #16a34a; }
.bg-purple  { background: #faf5ff; color: #9333ea; }

.table-container {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    overflow: hidden;
}

.project-table {
    width: 100%;
    border-collapse: collapse;
}

.project-table th {
    background: #f8fafc;
    padding: 1.2rem;
    text-align: left;
    font-weight: 600;
    color: #475569;
    font-size: 0.9rem;
    border-bottom: 2px solid #e2e8f0;
}

.project-table td {
    padding: 1.2rem;
    border-bottom: 1px solid #f1f5f9;
    color: #1e293b;
    vertical-align: middle;
}

.project-table tr:hover {
    background: #f8fafc;
}

.project-code {
    font-weight: 700;
    color: #3b82f6;
    font-size: 0.85rem;
    display: block;
    margin-bottom: 4px;
}

.project-title {
    font-weight: 600;
    font-size: 1.05rem;
    color: #1e293b;
}

.badge {
    padding: 0.4rem 0.8rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}
.badge-pending { background: #fef3c7; color: #d97706; }
.badge-active { background: #e0e7ff; color: #4338ca; }
.badge-success { background: #dcfce7; color: #15803d; }
.badge-default { background: #f1f5f9; color: #475569; }

.btn-sm-action {
    padding: 0.5rem 1rem;
    background: #f1f5f9;
    color: #475569;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.85rem;
    transition: all 0.2s;
}

.btn-sm-action:hover {
    background: #3b82f6;
    color: white;
}

.empty-state { text-align: center; padding: 4rem; color: #64748b; }

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<div class="advisor-dashboard">

    <?php if (Session::hasFlash('error')): ?>
        <div class="alert alert-error" style="background:#fef2f2; color:#dc2626; padding:1rem; border-radius:12px; margin-bottom:2rem; border-left:4px solid #ef4444;">
            ❌ <?= htmlspecialchars(Session::getFlash('error')) ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-header">
        <div class="dashboard-title">
            <h1>แผงควบคุมที่ปรึกษา (Advisor Workspace)</h1>
            <p>ติดตาม ดูแล และให้คำปรึกษาโครงงานนักเรียนในความดูแลของคุณ</p>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon bg-primary">📚</div>
            <div class="stat-info">
                <h3><?= $stats['total'] ?? 0 ?></h3>
                <p>โครงงานทั้งหมด</p>
            </div>
        </div>
        <div class="stat-card" style="<?= ($stats['action_required'] ?? 0) > 0 ? 'border: 2px solid #f59e0b;' : '' ?>">
            <div class="stat-icon bg-warning">⚠️</div>
            <div class="stat-info">
                <h3><?= $stats['action_required'] ?? 0 ?></h3>
                <p>รอการพิจารณา (Action Required)</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-purple">⚙️</div>
            <div class="stat-info">
                <h3><?= $stats['in_progress'] ?? 0 ?></h3>
                <p>กำลังดำเนินการ (In Progress)</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-success">✅</div>
            <div class="stat-info">
                <h3><?= $stats['completed'] ?? 0 ?></h3>
                <p>เสร็จสิ้นแล้ว (Completed)</p>
            </div>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="table-container">
        <table class="project-table">
            <thead>
                <tr>
                    <th width="35%">รหัส / ชื่อโครงงาน</th>
                    <th width="15%">ผู้แต่ง / จำนวนคน</th>
                    <th width="20%">รายวิชา</th>
                    <th width="15%">สถานะล่าสุด</th>
                    <th width="15%" style="text-align: center;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($projects)): ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <h3>ยังไม่มีโครงงานที่อยู่ในการดูแล</h3>
                                <p>เมื่อนักเรียนระบุชื่อคุณเป็นครูที่ปรึกษา โครงงานจะแสดงที่นี่</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($projects as $project): ?>
                        <?php 
                            // Determine Badge Colors
                            $statusBadge = 'badge-default';
                            if (in_array($project['status'], ['proposal_submitted', 'chapter_review'])) $statusBadge = 'badge-pending';
                            elseif ($project['status'] === 'in_progress') $statusBadge = 'badge-active';
                            elseif ($project['status'] === 'completed') $statusBadge = 'badge-success';
                        ?>
                        <tr>
                            <td>
                                <span class="project-code"><?= htmlspecialchars($project['project_code']) ?></span>
                                <div class="project-title"><?= htmlspecialchars($project['title_th']) ?></div>
                                <?php if (($project['pending_docs'] ?? 0) > 0): ?>
                                    <span style="display:inline-block; margin-top:5px; color:#dc2626; font-size:0.8rem; font-weight:600;">
                                        📦 มีเอกสารส่งใหม่ <?= $project['pending_docs'] ?> รายการ
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                👤 <?= $project['total_members'] ?? 0 ?> คน
                            </td>
                            <td>
                                <?= htmlspecialchars($project['subject_name'] ?? 'โครงงานอิสระ') ?>
                            </td>
                            <td>
                                <span class="badge <?= $statusBadge ?>">
                                    <?= str_replace('_', ' ', $project['status']) ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <a href="<?= url('advisor/projects/' . $project['id']) ?>" class="btn-sm-action">
                                    พิจารณา &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
