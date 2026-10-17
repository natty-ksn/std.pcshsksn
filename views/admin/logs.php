<?php $pageTitle = 'ประวัติการใช้งานระบบ'; ?>

<div class="admin-logs">
    <div class="page-header">
        <div>
            <h1 class="page-title">ประวัติการใช้งานระบบ (Audit Logs)</h1>
            <p class="page-subtitle">ตรวจสอบกิจกรรมย้อนหลังที่เกิดขึ้นในระบบทั้งหมด</p>
        </div>
    </div>

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th width="180">วัน-เวลา</th>
                        <th>ผู้ใช้งาน</th>
                        <th>กิจกรรม</th>
                        <th>ข้อมูลเป้าหมาย</th>
                        <th>IP Address</th>
                        <th class="text-right">รายละเอียด</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="text-sm">
                                <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div
                                        style="width: 24px; height: 24px; border-radius: 50%; background: var(--primary-100); color: var(--primary-600); display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: bold;">
                                        <?= e(mb_substr($log['username'] ?? 'S', 0, 1, 'UTF-8')) ?>
                                    </div>
                                    <strong>
                                        <?= e($log['username'] ?? 'System') ?>
                                    </strong>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background: var(--gray-100); color: var(--gray-700);">
                                    <?= e($log['action']) ?>
                                </span>
                            </td>
                            <td class="text-sm">
                                <span class="text-gray-500">
                                    <?= e($log['entity_type'] ?: '-') ?>
                                </span>
                                <?php if ($log['entity_id']): ?>
                                    <code
                                        style="font-size: 0.7rem; padding: 2px 4px; background: var(--gray-50); border-radius: 4px;">ID: <?= $log['entity_id'] ?></code>
                                <?php endif; ?>
                            </td>
                            <td class="text-sm text-gray-400">
                                <?= e($log['ip_address'] ?: '127.0.0.1') ?>
                            </td>
                            <td class="text-right">
                                <button type="button" class="btn btn-sm btn-ghost" title="ดูรายละเอียด"
                                    onclick='viewDetails(<?= json_encode([
                                        "id" => $log["id"],
                                        "created_at" => $log["created_at"],
                                        "username" => $log["username"] ?? "System",
                                        "action" => $log["action"],
                                        "entity_type" => $log["entity_type"] ?? "-",
                                        "entity_id" => $log["entity_id"] ?? "-",
                                        "ip_address" => $log["ip_address"] ?? "127.0.0.1",
                                        "user_agent" => $log["user_agent"] ?? "-",
                                        "old_value" => $log["old_value"] ?? null,
                                        "new_value" => $log["new_value"] ?? null,
                                    ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>🔍</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="card-footer"
            style="display: flex; justify-content: space-between; align-items: center; padding: 16px;">
            <p class="text-sm text-gray-500">แสดงผล 50 รายการล่าสุดต่อหน้า</p>
            <div style="display: flex; gap: 8px;">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>" class="btn btn-sm btn-ghost">⬅️ ก่อนหน้า</a>
                <?php endif; ?>
                <span class="btn btn-sm btn-primary">หน้า
                    <?= $page ?>
                </span>
                <a href="?page=<?= $page + 1 ?>" class="btn btn-sm btn-ghost">ถัดไป ➡️</a>
            </div>
        </div>
    </div>
</div>

<!-- Log Detail Modal -->
<div id="logDetailModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 id="logDetailTitle">รายละเอียดประวัติการใช้งาน</h3>
            <button class="close-modal" onclick="document.getElementById('logDetailModal').classList.remove('active')">&times;</button>
        </div>
        <div class="modal-body" id="logDetailBody" style="font-size: 0.9rem; line-height: 1.6;"></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('logDetailModal').classList.remove('active')">ปิดหน้าต่าง</button>
        </div>
    </div>
</div>

<script>
    function escapeHtmlLog(str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }
    function viewDetails(log) {
        document.getElementById('logDetailTitle').textContent = '🔍 รายละเอียด Log #' + log.id;
        let html = `
            <table class="table" style="margin-bottom: 12px;">
                <tr><th width="140">วัน-เวลา</th><td>${escapeHtmlLog(log.created_at)}</td></tr>
                <tr><th>ผู้ใช้งาน</th><td><strong>${escapeHtmlLog(log.username)}</strong></td></tr>
                <tr><th>กิจกรรม (Action)</th><td><code>${escapeHtmlLog(log.action)}</code></td></tr>
                <tr><th>เป้าหมาย</th><td>${escapeHtmlLog(log.entity_type)} (ID: ${escapeHtmlLog(log.entity_id)})</td></tr>
                <tr><th>IP Address</th><td>${escapeHtmlLog(log.ip_address)}</td></tr>
                <tr><th>User Agent</th><td style="word-break: break-all; font-size: 0.8rem; color: var(--gray-500);">${escapeHtmlLog(log.user_agent)}</td></tr>
            </table>
        `;
        if (log.old_value) {
            html += `<div style="margin-top:10px;"><strong>ข้อมูลเดิม (Old Value):</strong><pre style="background:var(--gray-50);padding:10px;border-radius:8px;overflow:auto;max-height:160px;font-size:0.8rem;">${escapeHtmlLog(log.old_value)}</pre></div>`;
        }
        if (log.new_value) {
            html += `<div style="margin-top:10px;"><strong>ข้อมูลใหม่ (New Value):</strong><pre style="background:var(--gray-50);padding:10px;border-radius:8px;overflow:auto;max-height:160px;font-size:0.8rem;">${escapeHtmlLog(log.new_value)}</pre></div>`;
        }
        document.getElementById('logDetailBody').innerHTML = html;
        document.getElementById('logDetailModal').classList.add('active');
    }
</script>