<?php $pageTitle = 'ความสนใจ & ความถนัด'; ?>
<div style="max-width: 700px; margin: 0 auto;">
    <h2 class="mb-3">💡 ความสนใจ & ความถนัด</h2>
    <div class="card mb-3 slide-up">
        <div class="card-header">
            <h3>➕ เพิ่มข้อมูลใหม่</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= url('interests/store') ?>">
                <?= CSRF::field() ?>
                <div class="grid-3" style="align-items: end;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">ประเภท</label>
                        <select name="type" class="form-control">
                            <option value="interest">💡 ความสนใจ</option>
                            <option value="talent">⭐ ความถนัด</option>
                            <option value="hobby">🎮 งานอดิเรก</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">รายการ *</label>
                        <input type="text" name="item" class="form-control" placeholder="เช่น การเขียนโปรแกรม" required>
                    </div>
                    <div style="display:flex;gap:8px;align-items:end;">
                        <div class="form-group" style="margin-bottom:0;flex:1;">
                            <label class="form-label">ระดับ</label>
                            <select name="proficiency" class="form-control">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3" selected>3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" style="height:42px;">➕</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
    $sections = [
        'interest' => ['title' => '💡 ความสนใจ', 'bg' => '#eef2ff'],
        'talent' => ['title' => '⭐ ความถนัด', 'bg' => '#fffbeb'],
        'hobby' => ['title' => '🎮 งานอดิเรก', 'bg' => '#ecfdf5'],
    ];
    foreach ($sections as $type => $cfg): ?>
        <div class="card mb-2 slide-up">
            <div class="card-header">
                <h3>
                    <?= $cfg['title'] ?>
                </h3>
            </div>
            <div class="card-body">
                <?php if (empty($grouped[$type])): ?>
                    <p class="text-muted text-center">ยังไม่มีข้อมูล</p>
                <?php else: ?>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        <?php foreach ($grouped[$type] as $item): ?>
                            <div
                                style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:<?= $cfg['bg'] ?>;border-radius:var(--radius-full);font-size:0.88rem;">
                                <strong>
                                    <?= e($item['item']) ?>
                                </strong>
                                <span style="opacity:0.5;font-size:0.7rem;">
                                    <?= str_repeat('●', $item['proficiency']) . str_repeat('○', 5 - $item['proficiency']) ?>
                                </span>
                                <form method="POST" action="<?= url('interests/' . $item['id'] . '/delete') ?>" style="display:inline;"
                                    onsubmit="return confirm('ลบ?')">
                                    <?= CSRF::field() ?>
                                    <button type="submit"
                                        style="background:none;border:none;cursor:pointer;color:var(--danger-400);">✕</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>