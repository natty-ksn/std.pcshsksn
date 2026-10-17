<?php $pageTitle = 'แผงควบคุมผู้ดูแลระบบ'; ?>

<div class="admin-dashboard">
    <div class="page-header">
        <div>
            <h1 class="page-title">ศูนย์บริหารจัดการระบบสารสนเทศ (Management System)</h1>
            <p class="page-subtitle">ภาพรวมสถานะและการใช้งานระบบ e-Portfolio โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: #6366f1;">👥</div>
            <div class="stat-info">
                <h3>
                    <?= number_format($stats['total_users']) ?>
                </h3>
                <p>จำนวนผู้ใช้งานทั้งหมด</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">👨‍🏫</div>
            <div class="stat-info">
                <h3>
                    <?= number_format($stats['total_teachers']) ?>
                </h3>
                <p>จำนวนบุคลากรทางการศึกษา</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">🎓</div>
            <div class="stat-info">
                <h3>
                    <?= number_format($stats['total_students']) ?>
                </h3>
                <p>จำนวนนักเรียนในระบบ</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">📁</div>
            <div class="stat-info">
                <h3>
                    <?= number_format($stats['total_items']) ?>
                </h3>
                <p>ผลงานที่เผยแพร่</p>
            </div>
        </div>
    </div>

    <div class="grid grid-2">
        <!-- Recent Users -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">ผู้ใช้งานใหม่ล่าสุด</h2>
                <a href="<?= url('admin/users') ?>" class="btn btn-sm btn-ghost">ดูข้อมูลทั้งหมด</a>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ชื่อผู้ใช้</th>
                                <th>บทบาท</th>
                                <th>วันที่สมัคร</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['recent_users'] as $user): ?>
                                <tr>
                                    <td class="font-bold">
                                        <?= e($user['username']) ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $user['role'] ?>">
                                            <?= e($user['role']) ?>
                                        </span>
                                    </td>
                                    <td class="text-sm text-gray-500">
                                        <?= timeAgo($user['created_at']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- System Logs -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">บันทึกกิจกรรมและเหตุการณ์ล่าสุด</h2>
                <a href="<?= url('admin/logs') ?>" class="btn btn-sm btn-ghost">ดูบันทึกทั้งหมด</a>
            </div>
            <div class="card-body">
                <div class="activity-feed">
                    <?php foreach ($stats['recent_logs'] as $log): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <?php
                                $icon = '📝';
                                if (str_contains($log['action'], 'login'))
                                    $icon = '🔐';
                                if (str_contains($log['action'], 'create'))
                                    $icon = '➕';
                                if (str_contains($log['action'], 'delete'))
                                    $icon = '🗑️';
                                echo $icon;
                                ?>
                            </div>
                            <div class="activity-content">
                                <div class="activity-header">
                                    <strong>
                                        <?= e($log['username'] ?? 'System') ?>
                                    </strong>
                                    <span>
                                        <?= e($log['action']) ?>
                                    </span>
                                </div>
                                <div class="activity-time">
                                    <?= timeAgo($log['created_at']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Mock Data Management -->
    <div class="card" style="border: 2px dashed #6366f1; background: rgba(99, 102, 241, 0.02);">
        <div class="card-header">
            <h2 class="card-title">🧪 ระบบจัดการข้อมูลทดสอบเชิงวิเคราะห์ (Mock Data Management)</h2>
        </div>
        <div class="card-body">
            <p class="text-sm text-gray-600 mb-4">
                ใช้สำหรับสร้างนักเรียนจำลอง 48 คน พร้อมผลงานและผลการเรียนที่คละความสามารถอย่างสมจริง 
                เพื่อทดสอบระบบวิเคราะห์ข้อมูล (TCAS Prediction / EWS) สำหรับผู้บริหารและคณะครู
            </p>
            <div style="display: flex; gap: 12px;">
                <form action="<?= url('admin/mock/generate') ?>" method="POST" onsubmit="return confirm('ยืนยันการเนรมิตข้อมูลนักเรียนจำลอง 48 คน?')">
                    <?= CSRF::field() ?>
                    <button type="submit" class="btn btn-primary">🪄 เนรมิตข้อมูลจำลอง 48 คน</button>
                </form>
                <form action="<?= url('admin/mock/clear') ?>" method="POST" onsubmit="return confirm('ยืนยันการลบข้อมูลจำลองทั้งหมดออกจากระบบ?')">
                    <?= CSRF::field() ?>
                    <button type="submit" class="btn btn-danger">🧹 ล้างข้อมูลจำลองทั้งหมด</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .admin-dashboard {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .stat-card {
        background: white;
        padding: 24px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--gray-100);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .stat-info h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--gray-900);
    }

    .stat-info p {
        font-size: 0.875rem;
        color: var(--gray-500);
    }

    .activity-feed {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .activity-item {
        display: flex;
        gap: 12px;
        padding-bottom: 12px;
        border-bottom: 1px dashed var(--gray-100);
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        width: 32px;
        height: 32px;
        background: var(--gray-50);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .activity-content {
        flex: 1;
    }

    .activity-header {
        font-size: 0.875rem;
        margin-bottom: 2px;
    }

    .activity-header strong {
        color: var(--primary-600);
    }

    .activity-time {
        font-size: 0.75rem;
        color: var(--gray-400);
    }

    .badge-admin {
        background: #fee2e2;
        color: #ef4444;
    }

    .badge-teacher {
        background: #e0e7ff;
        color: #4338ca;
    }

    .badge-student {
        background: #ecfdf5;
        color: #059669;
    }
</style>