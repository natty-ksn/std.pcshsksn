<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ระบบ e-Portfolio โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์">
    <title>
        <?= e($pageTitle ?? 'e-Portfolio') ?> — PCSHSKSN
    </title>
    <link rel="icon" type="image/x-icon" href="<?= asset('favicon.ico') ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&family=Noto+Sans+Thai:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <!-- Marked.js for Markdown Rendering -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

    <style>
        :root {
            /* === UNIFIED PCSHSKSN COLOR SYSTEM === */
            /* Primary: Indigo — brand color */
            --primary-500: #6366f1;
            --primary-600: #4f46e5;
            --primary-700: #4338ca;
            --primary-800: #3730a3;
            --primary-900: #312e81;

            /* Accent: Emerald — success, achievement */
            --accent-500: #10b981;
            --accent-600: #059669;

            /* Role-based colors */
            --student-color: #6366f1;
            --teacher-color: #059669;
            --admin-color: #dc2626;
            --exec-color: #7c3aed;

            /* Sidebar */
            --bg-sidebar: #0f172a;

            /* Layout */
            --sidebar-width: 280px;
            --topbar-height: 70px;
        }

        /* Essential Global Typography */
        * {
            font-family: 'Sarabun', 'Noto Sans Thai', sans-serif;
            box-sizing: border-box;
        }

        body {
            background: #f1f5f9;
            margin: 0;
            overflow-x: hidden;
            color: #1e293b;
        }

        .app-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Mechanics */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-sidebar);
            color: #cbd5e1;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }

        /* Mobile Adjustments */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0 !important;
                width: 100%;
            }
        }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
            min-width: 0;
            /* Important for flex child wrapping */
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
            backdrop-filter: blur(2px);
        }

        .sidebar-overlay.active {
            display: block;
        }

        .btn-primary {
            background-color: var(--primary-800);
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .btn-primary:hover {
            background-color: var(--primary-700);
            transform: translateY(-1px);
        }

        .sidebar-logo-text {
            color: white;
        }

        /* Navigation Items */
        .nav-item {
            display: flex;
            align-items: center;
            color: #94a3b8;
            text-decoration: none;
            border-left: 4px solid transparent;
            margin: 4px 16px;
            border-radius: 8px;
            padding: 10px 12px;
            transition: all 0.2s;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }

        .nav-item.active {
            background: rgba(255, 255, 255, 0.1);
            border-left: 4px solid var(--accent-500);
            color: white;
            font-weight: 600;
        }

        /* === Collapsible Nav Sections === */
        .nav-section-title {
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 8px 16px 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-toggle {
            transition: transform 0.2s;
            font-size: 0.7rem;
            display: inline-block;
            transform: rotate(-90deg);
        }

        .nav-toggle.open {
            transform: rotate(0deg);
        }

        .nav-sub {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .nav-sub.open {
            max-height: 500px;
        }

        .nav-sub-item {
            font-size: 0.85rem;
            padding: 8px 12px 8px 32px;
            margin: 2px 16px;
            border-left: none;
            color: rgba(255, 255, 255, 0.6);
        }

        .nav-sub-item:hover {
            color: white;
            background: rgba(255, 255, 255, 0.05);
        }

        .nav-sub-item.active {
            color: white;
            background: rgba(255, 255, 255, 0.08);
            font-weight: 600;
        }

        /* === Breadcrumb === */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 0;
            margin-bottom: 16px;
            font-size: 0.875rem;
            border-bottom: 1px solid var(--gray-100);
        }

        .breadcrumb a {
            color: var(--gray-500);
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumb a:hover {
            color: var(--primary-500);
        }

        .breadcrumb-sep {
            color: var(--gray-300);
        }

        .breadcrumb-current {
            color: var(--gray-800);
            font-weight: 600;
        }

        .sidebar-user {
            background: rgba(255, 255, 255, 0.03);
            margin: 20px 16px;
            border-radius: 12px;
            padding: 16px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .top-bar {
            height: var(--topbar-height);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 500;
        }

        .mobile-menu-btn {
            display: none;
            background: var(--primary-800);
            color: white;
            border: none;
            width: 42px;
            height: 42px;
            border-radius: 8px;
            font-size: 1.5rem;
            cursor: pointer;
            margin-right: 15px;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 1024px) {
            .mobile-menu-btn {
                display: flex;
            }
        }

        .alert {
            margin: 10px 0 20px 0;
            padding: 15px;
            border-radius: 12px;
            animation: slideIn 0.3s ease-out;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        @keyframes slideIn {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        footer {
            background: white;
            color: var(--gray-500);
        }

        /* === Loading States & Skeleton Loaders === */
        .skeleton {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 8px;
        }

        @keyframes shimmer {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .skeleton-text {
            height: 16px;
            margin-bottom: 8px;
        }

        .skeleton-text:last-child {
            width: 60%;
        }

        .skeleton-title {
            height: 24px;
            width: 40%;
            margin-bottom: 12px;
        }

        .skeleton-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .skeleton-card {
            height: 120px;
            border-radius: 16px;
            margin-bottom: 16px;
        }

        .skeleton-stat {
            height: 100px;
            border-radius: 16px;
        }

        /* Spinner */
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e2e8f0;
            border-top-color: #6366f1;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spinner-sm {
            width: 20px;
            height: 20px;
            border-width: 2px;
        }

        .spinner-lg {
            width: 60px;
            height: 60px;
            border-width: 5px;
        }

        /* Page loading overlay */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: white;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 16px;
            transition: opacity 0.3s;
        }

        .page-loader.hidden {
            opacity: 0;
            pointer-events: none;
        }

        /* === Notification Dropdown === */
        .notif-wrapper {
            position: relative;
        }

        .notif-dropdown {
            position: absolute;
            top: 50px;
            right: 0;
            width: 360px;
            max-height: 400px;
            overflow-y: auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            border: 1px solid #e2e8f0;
            z-index: 1000;
            display: none;
        }

        @media (max-width: 640px) {
            .notif-dropdown {
                position: fixed;
                top: 75px;
                left: 12px;
                right: 12px;
                width: auto;
                max-width: none;
            }
        }

        .btn-sidebar-close {
            display: none;
        }

        @media (max-width: 1024px) {
            .btn-sidebar-close {
                display: block !important;
            }
        }

        .notif-dropdown.show {
            display: block;
        }

        .notif-dropdown-header {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notif-dropdown-header h4 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
        }

        .notif-dropdown-header a {
            font-size: 0.8rem;
            color: #6366f1;
            text-decoration: none;
            font-weight: 600;
        }

        .notif-item {
            display: flex;
            gap: 12px;
            padding: 12px 20px;
            border-bottom: 1px solid #f8fafc;
            transition: background 0.2s;
            cursor: pointer;
        }

        .notif-item:hover {
            background: #f8fafc;
        }

        .notif-item.unread {
            background: #eef2ff;
        }

        .notif-item-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
            background: #f1f5f9;
        }

        .notif-item-content {
            flex: 1;
            min-width: 0;
        }

        .notif-item-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1e293b;
        }

        .notif-item-msg {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .notif-item-time {
            font-size: 0.7rem;
            color: #94a3b8;
            margin-top: 4px;
        }

        .notif-empty {
            padding: 30px 20px;
            text-align: center;
            color: #94a3b8;
        }

        /* === Mobile Bottom Navigation === */
        .mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #e2e8f0;
            z-index: 900;
            padding-bottom: env(safe-area-inset-bottom, 0);
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
        }

        .mobile-bottom-nav-items {
            display: flex;
            justify-content: space-around;
        }

        .mobile-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px 12px;
            text-decoration: none;
            color: #94a3b8;
            font-size: 0.65rem;
            font-weight: 600;
            transition: color 0.2s;
            position: relative;
        }

        .mobile-nav-item.active {
            color: #6366f1;
        }

        .mobile-nav-item-icon {
            font-size: 1.3rem;
            margin-bottom: 2px;
        }

        .mobile-nav-badge {
            position: absolute;
            top: 2px;
            right: 4px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #ef4444;
            color: white;
            font-size: 0.6rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        @media (max-width: 768px) {
            .mobile-bottom-nav {
                display: block;
            }

            .page-content {
                padding-bottom: 80px !important;
            }

            footer {
                padding-bottom: 80px !important;
            }
        }

        /* ====== SSE Real-Time Toast Notifications ====== */
        #sse-toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 12px;
            pointer-events: none;
            max-width: 420px;
        }

        .sse-toast {
            pointer-events: auto;
            background: rgba(15, 23, 42, 0.88);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            padding: 16px 20px;
            color: #f1f5f9;
            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.35),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: flex-start;
            gap: 14px;
            animation: toastSlideIn 0.45s cubic-bezier(0.22, 1, 0.36, 1) both;
            transition: opacity 0.35s ease, transform 0.35s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .sse-toast::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            border-radius: 16px 16px 0 0;
        }

        .sse-toast.toast-notification::before {
            background: linear-gradient(90deg, #6366f1, #a78bfa);
        }

        .sse-toast.toast-badge::before {
            background: linear-gradient(90deg, #f59e0b, #fbbf24);
        }

        .sse-toast.toast-levelup::before {
            background: linear-gradient(90deg, #10b981, #34d399);
        }

        .sse-toast.toast-project::before {
            background: linear-gradient(90deg, #06b6d4, #22d3ee);
        }

        .sse-toast.toast-default::before {
            background: linear-gradient(90deg, #8b5cf6, #c084fc);
        }

        .sse-toast.toast-exit {
            opacity: 0;
            transform: translateX(120%);
        }

        .sse-toast-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .sse-toast-content {
            flex: 1;
            min-width: 0;
        }

        .sse-toast-title {
            font-size: 0.9rem;
            font-weight: 700;
            margin-bottom: 4px;
            line-height: 1.3;
        }

        .sse-toast-message {
            font-size: 0.78rem;
            color: rgba(241, 245, 249, 0.85);
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .sse-toast-close {
            background: none;
            border: none;
            color: rgba(241, 245, 249, 0.6);
            font-size: 1.1rem;
            cursor: pointer;
            padding: 0;
            line-height: 1;
            flex-shrink: 0;
            transition: color 0.2s;
        }

        .sse-toast-close:hover {
            color: #f1f5f9;
        }

        /* Progress bar — visual countdown */
        .sse-toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 0 0 16px 16px;
            animation: toastProgressShrink 5s linear forwards;
        }

        @keyframes toastSlideIn {
            from {
                opacity: 0;
                transform: translateX(120%);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes toastProgressShrink {
            from {
                width: 100%;
            }

            to {
                width: 0%;
            }
        }

        @media (max-width: 1024px) {
            #sse-toast-container {
                right: 12px;
                left: 12px;
                max-width: none;
            }

            .sse-toast {
                padding: 14px 16px;
            }
        }
    </style>
</head>

<body>
    <a href="#main-content" class="skip-link" style="position:absolute;top:-100px;left:10px;background:#4f46e5;color:white;padding:8px 16px;border-radius:6px;z-index:99999;transition:top 0.2s;" onfocus="this.style.top='10px'" onblur="this.style.top='-100px'">ข้ามไปเนื้อหาหลัก (Skip to content)</a>
    <div class="app-layout animate-fade-in">
        <!-- Sidebar Overlay (Mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header" style="display:flex;align-items:center;justify-content:space-between;">
                <div class="sidebar-logo">
                    <img src="<?= asset('images/logo.png') ?>" alt="Logo" onerror="this.onerror=null; this.src='https://krunat.online/logo3.png';"
                        style="width: 42px; height: 42px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2)); object-fit: contain;">
                    <div class="sidebar-logo-text">
                        PCSHSKSN e-Portfolio
                        <small>จุฬาภรณ์ฯ กาฬสินธุ์</small>
                    </div>
                </div>
                <button type="button" class="btn-sidebar-close" onclick="toggleSidebar()" aria-label="ปิดเมนู"
                    style="background:none; border:none; color:#cbd5e1; font-size:1.4rem; cursor:pointer; padding:6px; min-width:44px; min-height:44px;">✕</button>
            </div>

            <!-- User Info & Gamification -->
            <div class="sidebar-user glass-card" style="border: 1px solid rgba(255,255,255,0.1);">
                <?php
                Auth::sync();
                $u = Auth::user();
                $userAvatar = $u['avatar'];
                $userEmail = $u['email'];
                $userName = $u['name'];
                $avatarUrl = $userAvatar ? uploadUrl($userAvatar) : defaultAvatar($userEmail, $userName);

                $exp = $u['exp'] ?? 0;
                $level = $u['level'] ?? 1;
                $progress = getLevelProgress($exp);
                $rank = getRankBadge($level);
                ?>
                <div style="position: relative;">
                    <img src="<?= $avatarUrl ?>" alt="avatar" class="sidebar-user-avatar"
                        style="border: 2px solid var(--accent-500); background: white;">
                    <?php if (Auth::hasRole('student')): ?>
                        <div
                            style="position: absolute; bottom: -2px; right: -2px; background: white; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                            <?= $rank['icon'] ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"
                        style="color: white; font-weight: 700; display: flex; align-items: center; gap: 5px;">
                        <?= e($userName) ?>
                        <?php if (Auth::hasRole('student')): ?>
                            <span class="level-badge">Lv.<?= $level ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="sidebar-user-role"
                        style="color: var(--accent-500); font-weight: 600; font-size: 0.7rem; display: flex; justify-content: space-between; align-items: center;">
                        <span><?= roleLabel(Auth::role()) ?></span>
                        <?php if (Auth::hasRole('student')): ?>
                            <span class="rank-text <?= $rank['class'] ?>"><?= $rank['label'] ?></span>
                        <?php elseif (!empty($u['position'])): ?>
                            <span style="color: #94a3b8; font-weight: 500; font-size: 0.68rem;"><?= e(truncate($u['position'], 22)) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (Auth::hasRole('student')): ?>
                        <!-- EXP Bar -->
                        <div class="exp-bar-container" title="EXP: <?= $exp ?> / <?= (floor($exp / 1000) + 1) * 1000 ?>">
                            <div class="exp-bar-fill" style="width: <?= $progress ?>%;"></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav">
                <!-- Dashboard & Profile — always visible -->
                <div class="nav-section">
                    <div class="nav-section-title">เมนูหลัก</div>
                    <a href="<?= url('dashboard') ?>" class="nav-item <?= isActivePage('dashboard') ?>">
                        <span class="nav-item-icon">📊</span> แผงหน้าหลัก
                    </a>
                    <a href="<?= url('profile') ?>" class="nav-item <?= isActivePage('profile') ?>">
                        <span class="nav-item-icon">👤</span> ข้อมูลส่วนตัว
                    </a>
                </div>

                <?php if (Auth::hasRole('student')): ?>
                    <!-- Collapsible: Portfolio -->
                    <div class="nav-section collapsable" id="nav-portfolio">
                        <div class="nav-section-title" onclick="toggleNav('nav-portfolio')"
                            style="cursor:pointer; user-select:none;">
                            แฟ้มสะสมผลงาน <span class="nav-toggle" id="toggle-nav-portfolio">▾</span>
                        </div>
                        <div class="nav-sub">
                            <a href="<?= url('portfolio') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('portfolio') ?>">
                                📁 ผลงานทั้งหมด
                            </a>
                            <a href="<?= url('portfolio/create') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('portfolio/create') ?>">
                                ➕ เพิ่มผลงานใหม่
                            </a>
                            <a href="<?= url('academics') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('academics') ?>">
                                📝 ข้อมูลการเรียน
                            </a>
                            <a href="<?= url('interests') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('interests') ?>">
                                🌟 ความสนใจและทักษะ
                            </a>
                        </div>
                    </div>

                    <!-- Collapsible: Projects -->
                    <div class="nav-section collapsable" id="nav-projects">
                        <div class="nav-section-title" onclick="toggleNav('nav-projects')"
                            style="cursor:pointer; user-select:none;">
                            โครงงานวิทยาศาสตร์ <span class="nav-toggle" id="toggle-nav-projects">▾</span>
                        </div>
                        <div class="nav-sub">
                            <a href="<?= url('projects') ?>" class="nav-item nav-sub-item <?= isActivePage('projects') ?>">
                                🔬 โครงงานของฉัน
                            </a>
                            <a href="<?= url('projects/create') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('projects/create') ?>">
                                ✨ เสนอหัวข้อใหม่
                            </a>
                        </div>
                    </div>

                    <!-- Collapsible: Future Planning -->
                    <div class="nav-section collapsable" id="nav-future">
                        <div class="nav-section-title" onclick="toggleNav('nav-future')"
                            style="cursor:pointer; user-select:none;">
                            การวางแผนอนาคต <span class="nav-toggle" id="toggle-nav-future">▾</span>
                        </div>
                        <div class="nav-sub">
                            <a href="<?= url('goals') ?>" class="nav-item nav-sub-item <?= isActivePage('goals') ?>">
                                🎯 เป้าหมายการศึกษา
                            </a>
                            <a href="<?= url('tcas-readiness') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('tcas-readiness') ?>">
                                🎯 TCAS Readiness
                            </a>
                            <a href="<?= url('assessment') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('assessment') ?>">
                                💡 ประเมินตนเอง (RIASEC)
                            </a>
                            <a href="<?= url('ai-guide') ?>" class="nav-item nav-sub-item <?= isActivePage('ai-guide') ?>">
                                🤖 AI แนะแนวการศึกษา
                            </a>
                            <a href="<?= url('ai/field-readiness') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('ai/field-readiness') ?>">
                                🧭 ความพร้อมรายสาขา
                            </a>
                            <a href="<?= url('ai/tcas-advisor') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('ai/tcas-advisor') ?>">
                                🎓 TCAS AI Advisor
                            </a>
                            <a href="<?= url('ai/portfolio-audit') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('ai/portfolio-audit') ?>">
                                🔍 AI ตรวจคุณภาพพอร์ต
                            </a>
                            <a href="<?= url('opportunities') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('opportunities') ?>">
                                🚀 โอกาสและการแข่งขัน
                            </a>
                            <a href="<?= url('rewards') ?>" class="nav-item nav-sub-item <?= isActivePage('rewards') ?>">
                                🎁 The Exchange
                            </a>
                            <a href="<?= url('rewards/badges') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('rewards/badges') ?>">
                                🏅 เหรียญตราความสำเร็จ
                            </a>
                        </div>
                    </div>

                    <!-- Profile 360 -->
                    <div class="nav-section">
                        <a href="<?= url('profile/360') ?>" class="nav-item <?= isActivePage('profile/360') ?>">
                            <span class="nav-item-icon">🎓</span> Student 360° Profile
                        </a>
                    </div>

                    <!-- Rewards Shop -->
                    <div class="nav-section">
                        <a href="<?= url('shop') ?>" class="nav-item <?= isActivePage('shop') ?>">
                            <span class="nav-item-icon">🛍️</span> Rewards Shop
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (Auth::hasRole('teacher')): ?>
                    <div class="nav-section collapsable" id="nav-teacher">
                        <div class="nav-section-title" onclick="toggleNav('nav-teacher')"
                            style="cursor:pointer; user-select:none;">
                            ครูและที่ปรึกษา <span class="nav-toggle" id="toggle-nav-teacher">▾</span>
                        </div>
                        <div class="nav-sub">
                            <a href="<?= url('teacher/students') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('teacher/students') ?>">
                                👥 รายชื่อนักเรียน
                            </a>
                            <a href="<?= url('advisor/projects') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('advisor/projects') ?>">
                                🧑‍🔬 ที่ปรึกษาโครงงาน
                            </a>
                            <a href="<?= url('teacher/pending-reviews') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('teacher/pending-reviews') ?>">
                                ✅ ตรวจงานเร่งด่วน (Bulk)
                            </a>
                            <a href="<?= url('teacher/quick-sweep') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('teacher/quick-sweep') ?>">
                                🛰️ Quick Sweep คุณภาพ
                            </a>
                            <a href="<?= url('mentor/intelligence') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('mentor/intelligence') ?>">
                                🕵️‍♂️ ติดตามนักเรียน (Mentor)
                            </a>
                            <a href="<?= url('analytics/early-warning') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('analytics/early-warning') ?>">
                                🚨 Early Warning System
                            </a>
                            <a href="<?= url('academics') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('academics') ?>">
                                📝 จัดการผลการเรียน
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (Auth::hasRole('admin')): ?>
                    <div class="nav-section collapsable" id="nav-admin">
                        <div class="nav-section-title" onclick="toggleNav('nav-admin')"
                            style="cursor:pointer; user-select:none;">
                            ศูนย์บริหารจัดการ <span class="nav-toggle" id="toggle-nav-admin">▾</span>
                        </div>
                        <div class="nav-sub">
                            <a href="<?= url('admin') ?>" class="nav-item nav-sub-item <?= isActivePage('admin') ?>">
                                ⚙️ แผงควบคุมระบบ
                            </a>
                            <a href="<?= url('admin/users') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('admin/users') ?>">
                                👥 จัดการผู้ใช้
                            </a>
                            <a href="<?= url('admin/categories') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('admin/categories') ?>">
                                🏷️ หมวดหมู่ผลงาน
                            </a>
                            <a href="<?= url('analytics/dashboard') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('analytics/dashboard') ?>">
                                📊 Analytics Dashboard
                            </a>
                            <a href="<?= url('analytics/early-warning') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('analytics/early-warning') ?>">
                                🚨 Early Warning
                            </a>
                            <a href="<?= url('admin/logs') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('admin/logs') ?>">
                                📜 ประวัติใช้งาน
                            </a>
                            <a href="<?= url('admin/reports') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('admin/reports') ?>">
                                📈 รายงานสถิติ
                            </a>
                            <a href="<?= url('admin/settings') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('admin/settings') ?>">
                                ⚙️ ตั้งค่าระบบ
                            </a>
                            <a href="<?= url('db_doctor.php') ?>" target="_blank"
                                class="nav-item nav-sub-item" style="color: #6ee7b7;">
                                🩺 ตรวจสอบฐานข้อมูล
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (Auth::hasRole('executive') || Auth::hasRole('admin')): ?>
                    <div class="nav-section collapsable" id="nav-executive">
                        <div class="nav-section-title" onclick="toggleNav('nav-executive')"
                            style="cursor:pointer; user-select:none; color: var(--rank-gold); border-left: 3px solid var(--rank-gold); padding-left: 10px;">
                            วิเคราะห์ข้อมูลเชิงบริหาร <span class="nav-toggle" id="toggle-nav-executive">▾</span>
                        </div>
                        <div class="nav-sub">
                            <a href="<?= url('executive/summary') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('executive/summary') ?>">
                                📊 สรุปประมวลผลรวม
                            </a>
                            <a href="<?= url('executive/ews') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('executive/ews') ?>">
                                🚨 ระบบเฝ้าระวัง (EWS)
                            </a>
                            <a href="<?= url('analytics/early-warning') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('analytics/early-warning') ?>">
                                🔔 แจ้งเตือนความเสี่ยงเชิงลึก
                            </a>
                            <a href="<?= url('executive/tcas') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('executive/tcas') ?>">
                                🎓 วิเคราะห์ความพร้อม TCAS
                            </a>
                            <a href="<?= url('executive/excellence') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('executive/excellence') ?>">
                                🏆 สถิติผลงานดีเด่น
                            </a>
                            <a href="<?= url('executive/showcase') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('executive/showcase') ?>"
                                style="color: var(--rank-gold); font-weight: 700;">
                                🏛️ Showcase หอเกียรติยศ
                            </a>
                            <a href="<?= url('analytics/dashboard') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('analytics/dashboard') ?>">
                                📈 แดชบอร์ดวิจัยและโครงงาน
                            </a>
                            <a href="<?= url('teacher/students') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('teacher/students') ?>">
                                👥 รายชื่อนักเรียน 360°
                            </a>
                            <a href="<?= url('admin/reports') ?>"
                                class="nav-item nav-sub-item <?= isActivePage('admin/reports') ?>">
                                📋 รายงานสถิติโรงเรียน
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Bottom: Notifications + Logout -->
                <div class="nav-section" style="margin-top: auto;">
                    <a href="<?= url('notifications') ?>" class="nav-item <?= isActivePage('notifications') ?>">
                        <span class="nav-item-icon">🔔</span> การแจ้งเตือน
                        <?php if (($unreadNotifs ?? 0) > 0): ?>
                            <span class="nav-badge" style="background: var(--accent-500);">
                                <?= $unreadNotifs ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <a href="<?= url('logout') ?>" class="nav-item" style="color: #ff9999;">
                        <span class="nav-item-icon">🚪</span> ออกจากระบบ
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content" id="main-content">
            <!-- Top Bar -->
            <header class="top-bar">
                <div class="top-bar-left">
                    <button class="mobile-menu-btn" onclick="toggleSidebar()" id="menuBtn">☰</button>
                    <h2 class="top-bar-title" style="color: var(--primary-500);">
                        <?= e($pageTitle ?? 'แผงหน้าหลัก') ?>
                    </h2>
                </div>
                <div class="top-bar-right">
                    <!-- Notification Dropdown -->
                    <div class="notif-wrapper">
                        <button class="btn-icon" title="แจ้งเตือน" onclick="toggleNotifDropdown()" id="notifBtn">
                            🔔
                            <?php if (($unreadNotifs ?? 0) > 0): ?>
                                <span class="notif-dot" style="background: var(--accent-500);"></span>
                            <?php endif; ?>
                        </button>
                        <div class="notif-dropdown" id="notifDropdown">
                            <div class="notif-dropdown-header">
                                <h4>🔔 การแจ้งเตือน</h4>
                                <a href="<?= url('notifications') ?>">ดูทั้งหมด</a>
                            </div>
                            <div id="notifDropdownContent">
                                <div class="notif-empty">
                                    <div style="font-size: 2rem; margin-bottom: 8px;">🔔</div>
                                    <div>ไม่มีแจ้งเตือน</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- User Profile Avatar (Top Bar) -->
                    <button class="btn-icon" onclick="location.href='<?= url('profile') ?>'"
                        title="<?= e(Auth::name()) ?>"
                        style="padding: 0; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 50%; border: 2px solid #e2e8f0; background: white; transition: all 0.2s;">
                        <img src="<?= $avatarUrl ?>" alt="Profile"
                            style="width: 100%; height: 100%; object-fit: cover;">
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <div class="page-content">
                <!-- Breadcrumb -->
                <?php if (!empty($breadcrumbs ?? [])): ?>
                    <nav class="breadcrumb" aria-label="breadcrumb">
                        <?php foreach ($breadcrumbs as $i => $crumb): ?>
                            <?php if ($i < count($breadcrumbs) - 1): ?>
                                <a href="<?= url($crumb['url']) ?>"><?= e($crumb['label']) ?></a>
                                <span class="breadcrumb-sep">/</span>
                            <?php else: ?>
                                <span class="breadcrumb-current"><?= e($crumb['label']) ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </nav>
                <?php endif; ?>

                <!-- Flash Messages -->
                <?php if (Session::hasFlash('success')): ?>
                    <div class="alert alert-success">✅
                        <?= e(Session::getFlash('success')) ?>
                    </div>
                <?php endif; ?>
                <?php if (Session::hasFlash('error')): ?>
                    <div class="alert alert-error">❌
                        <?= e(Session::getFlash('error')) ?>
                    </div>
                <?php endif; ?>
                <?php if (Session::hasFlash('warning')): ?>
                    <div class="alert alert-warning">⚠️
                        <?= e(Session::getFlash('warning')) ?>
                    </div>
                <?php endif; ?>
                <?php if (Session::hasFlash('info')): ?>
                    <div class="alert alert-info">ℹ️
                        <?= e(Session::getFlash('info')) ?>
                    </div>
                <?php endif; ?>

                <!-- Page Content Injected Here -->
                <?= $content ?>
            </div>

            <!-- Footer -->
            <footer
                style="padding: 40px 24px; text-align: center; color: var(--gray-500); font-size: 0.85rem; border-top: 1px solid var(--gray-100); background: #fafafa;">
                <p style="margin-bottom: 6px;">พัฒนาโดย งานศูนย์ข้อมูลสารสนเทศ | กลุ่มบริหารงานวิชาการ</p>
                <p style="margin-bottom: 12px; color: var(--primary-500); font-weight: 700;">
                    โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์</p>
                <p style="font-weight: 600;">© 2026 PCSHSKSN</p>
            </footer>
        </main>
    </div>

    <!-- Page Loader -->
    <div class="page-loader" id="pageLoader">
        <div class="spinner spinner-lg"></div>
        <div style="color: #64748b; font-size: 0.9rem;">กำลังโหลด...</div>
    </div>

    <!-- Mobile Bottom Navigation -->
    <nav class="mobile-bottom-nav">
        <div class="mobile-bottom-nav-items">
            <a href="<?= url('dashboard') ?>" class="mobile-nav-item <?= isActivePage('dashboard') ? 'active' : '' ?>">
                <span class="mobile-nav-item-icon">🏠</span>
                หน้าหลัก
            </a>
            <?php if (Auth::hasRole('student')): ?>
                <a href="<?= url('portfolio') ?>" class="mobile-nav-item <?= isActivePage('portfolio') ? 'active' : '' ?>">
                    <span class="mobile-nav-item-icon">📁</span>
                    ผลงาน
                </a>
                <a href="<?= url('projects') ?>" class="mobile-nav-item <?= isActivePage('projects') ? 'active' : '' ?>">
                    <span class="mobile-nav-item-icon">🔬</span>
                    โครงงาน
                </a>
            <?php endif; ?>
            <?php if (Auth::hasRole('teacher')): ?>
                <a href="<?= url('teacher/students') ?>"
                    class="mobile-nav-item <?= isActivePage('teacher/students') ? 'active' : '' ?>">
                    <span class="mobile-nav-item-icon">👥</span>
                    นักเรียน
                </a>
                <a href="<?= url('advisor/projects') ?>"
                    class="mobile-nav-item <?= isActivePage('advisor/projects') ? 'active' : '' ?>">
                    <span class="mobile-nav-item-icon">🧑‍🔬</span>
                    ที่ปรึกษา
                </a>
            <?php endif; ?>
            <?php if (Auth::hasRole('admin')): ?>
                <a href="<?= url('admin') ?>" class="mobile-nav-item <?= isActivePage('admin') ? 'active' : '' ?>">
                    <span class="mobile-nav-item-icon">⚙️</span>
                    จัดการ
                </a>
                <a href="<?= url('analytics/dashboard') ?>"
                    class="mobile-nav-item <?= isActivePage('analytics/dashboard') ? 'active' : '' ?>">
                    <span class="mobile-nav-item-icon">📊</span>
                    Analytics
                </a>
            <?php endif; ?>
            <?php if (Auth::hasRole('executive')): ?>
                <a href="<?= url('executive/summary') ?>"
                    class="mobile-nav-item <?= isActivePage('executive/summary') ? 'active' : '' ?>">
                    <span class="mobile-nav-item-icon">🏛️</span>
                    สรุปบริหาร
                </a>
                <a href="<?= url('executive/ews') ?>"
                    class="mobile-nav-item <?= isActivePage('executive/ews') ? 'active' : '' ?>">
                    <span class="mobile-nav-item-icon">🚨</span>
                    EWS
                </a>
            <?php endif; ?>
            <a href="<?= url('notifications') ?>"
                class="mobile-nav-item <?= isActivePage('notifications') ? 'active' : '' ?>">
                <span class="mobile-nav-item-icon">🔔</span>
                <?php if (($unreadNotifs ?? 0) > 0): ?>
                    <span class="mobile-nav-badge"><?= $unreadNotifs ?></span>
                <?php endif; ?>
                แจ้งเตือน
            </a>
            <a href="<?= url('profile') ?>" class="mobile-nav-item <?= isActivePage('profile') ? 'active' : '' ?>">
                <span class="mobile-nav-item-icon">👤</span>
                โปรไฟล์
            </a>
        </div>
    </nav>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        }

        // Collapsible nav sections
        function toggleNav(sectionId) {
            const section = document.getElementById(sectionId);
            const sub = section.querySelector('.nav-sub');
            const toggle = section.querySelector('.nav-toggle');
            if (sub) {
                const isOpen = sub.classList.toggle('open');
                if (toggle) toggle.classList.toggle('open', isOpen);
            }
        }

        // Notification dropdown toggle
        function toggleNotifDropdown() {
            const dropdown = document.getElementById('notifDropdown');
            dropdown.classList.toggle('show');
            // Fetch notifications if first open
            if (dropdown.classList.contains('show')) {
                fetchNotifs();
            }
        }

        // Fetch notifications via AJAX
        function fetchNotifs() {
            fetch('<?= url('api/notifications/count') ?>')
                .then(r => r.json())
                .then(data => {
                    // If we have notifications, render them
                    if (data && data.notifications && data.notifications.length > 0) {
                        let html = '';
                        data.notifications.slice(0, 5).forEach(n => {
                            html += `<a href="<?= url('notifications') ?>" style="text-decoration:none;color:inherit;">
                                <div class="notif-item ${n.is_read ? '' : 'unread'}">
                                    <div class="notif-item-icon">${getNotifIcon(n.type)}</div>
                                    <div class="notif-item-content">
                                        <div class="notif-item-title">${escapeHtml(n.title)}</div>
                                        <div class="notif-item-time">${timeAgo(n.created_at)}</div>
                                    </div>
                                </div>
                            </a>`;
                        });
                        document.getElementById('notifDropdownContent').innerHTML = html;
                    }
                })
                .catch(() => { });
        }

        function getNotifIcon(type) {
            const icons = {
                'project.approved': '✅',
                'project.rejected': '❌',
                'comment.added': '💬',
                'badge.awarded': '🏅',
                'default': '🔔'
            };
            return icons[type] || icons.default;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function timeAgo(dateStr) {
            const now = new Date();
            const date = new Date(dateStr);
            const diff = Math.floor((now - date) / 1000);
            if (diff < 60) return 'เมื่อกี้';
            if (diff < 3600) return Math.floor(diff / 60) + ' นาทีที่แล้ว';
            if (diff < 86400) return Math.floor(diff / 3600) + ' ชั่วโมงที่แล้ว';
            return Math.floor(diff / 86400) + ' วันที่แล้ว';
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            const dropdown = document.getElementById('notifDropdown');
            const wrapper = document.querySelector('.notif-wrapper');
            if (dropdown && wrapper && !wrapper.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });

        // Close modals, dropdowns, and sidebar on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.key === 'Esc') {
                const notif = document.getElementById('notifDropdown');
                if (notif) notif.classList.remove('show');

                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebarOverlay');
                if (sidebar && sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                    if (overlay) overlay.classList.remove('active');
                }

                document.querySelectorAll('.modal.active').forEach(function(m) {
                    m.classList.remove('active');
                });
            }
        });

        // Page loader - hide after page loads
        window.addEventListener('load', function () {
            const loader = document.getElementById('pageLoader');
            if (loader) {
                loader.classList.add('hidden');
                setTimeout(() => loader.remove(), 300);
            }
        });

        // Auto-expand nav section if active sub-item
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.nav-sub-item.active').forEach(function (item) {
                const section = item.closest('.collapsable');
                if (section) {
                    const sub = section.querySelector('.nav-sub');
                    const toggle = section.querySelector('.nav-toggle');
                    if (sub) sub.classList.add('open');
                    if (toggle) toggle.classList.add('open');
                }
            });
        });

        // Auto-dismiss alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });

        // Initialize Markdown parsing globally
        document.addEventListener("DOMContentLoaded", function () {
            if (typeof marked !== 'undefined') {
                document.querySelectorAll('.markdown-content').forEach(function (el) {
                    el.innerHTML = marked.parse(el.textContent || el.innerText);
                });
            }
        });

        // ==========================================
        // SSE Real-Time Notification Stream
        // ==========================================
        (function initSSENotifications() {
            // Only activate SSE for logged-in students
            var sseUrl = '<?= url('api/stream/notifications') ?>';
            var maxToasts = 3; // Cap simultaneous toasts on screen
            var toastLifetime = 4000; // ms before auto-dismiss

            // Toast type → icon mapping
            var typeIcons = {
                'notification': '🔔',
                'badge': '🏅',
                'system.levelup': '🎉',
                'project.approved': '✅',
                'project.rejected': '❌',
                'project.revision_needed': '🔄',
                'project.submitted': '📝',
                'comment.added': '💬',
                'badge.awarded': '🏅',
                'logbook.verified': '📋',
                'milestone.due_soon': '⏰',
                'milestone.overdue': '🚨',
                'early_warning.alert': '⚠️',
                'default': '🔔'
            };

            // Determine toast color class from event type
            function getToastClass(eventType) {
                if (!eventType) return 'toast-default';
                if (eventType.indexOf('badge') !== -1 || eventType.indexOf('levelup') !== -1) return 'toast-levelup';
                if (eventType.indexOf('project') !== -1) return 'toast-project';
                if (eventType.indexOf('comment') !== -1) return 'toast-default';
                return 'toast-' + eventType.split('.')[0];
            }

            // Create the toast container if it doesn't exist
            function getToastContainer() {
                var container = document.getElementById('sse-toast-container');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'sse-toast-container';
                    document.body.appendChild(container);
                }
                return container;
            }

            // Build and show a glassmorphism toast
            function showToast(data) {
                var container = getToastContainer();

                // Cap max toasts on screen
                var existing = container.querySelectorAll('.sse-toast:not(.toast-exit)');
                if (existing.length >= maxToasts) {
                    // Remove oldest
                    existing[0].classList.add('toast-exit');
                    setTimeout(function () { existing[0].remove(); }, 350);
                }

                var icon = typeIcons[data.type] || typeIcons[data.source] || typeIcons['default'];
                var title = data.title || 'การแจ้งเตือน';
                var message = data.message || '';
                var link = data.link || '';
                var toastClass = getToastClass(data.type || '');

                var toast = document.createElement('div');
                toast.className = 'sse-toast ' + toastClass;
                toast.innerHTML =
                    '<div class="sse-toast-icon">' + icon + '</div>' +
                    '<div class="sse-toast-content">' +
                    '<div class="sse-toast-title">' + escapeHtml(title) + '</div>' +
                    (message ? '<div class="sse-toast-message">' + escapeHtml(message) + '</div>' : '') +
                    '</div>' +
                    '<button class="sse-toast-close" aria-label="ปิด">&times;</button>' +
                    '<div class="sse-toast-progress"></div>';

                container.appendChild(toast);

                // Close on X button click
                var closeBtn = toast.querySelector('.sse-toast-close');
                closeBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    dismissToast(toast);
                });

                // Navigate on toast click (if link provided)
                if (link) {
                    toast.style.cursor = 'pointer';
                    // Ensure link has the /app prefix for relative paths
                    if (link.startsWith('/') && !link.includes('://')) {
                        link = '<?= url() ?>/' + link.replace(/^\/+/, '');
                    }
                    toast.addEventListener('click', function () {
                        window.location.href = link;
                    });
                }

                // Auto-dismiss after lifetime
                setTimeout(function () {
                    dismissToast(toast);
                }, toastLifetime);
            }

            // Dismiss a toast with exit animation
            function dismissToast(toast) {
                if (!toast || toast.classList.contains('toast-exit')) return;
                toast.classList.add('toast-exit');
                setTimeout(function () {
                    if (toast.parentNode) toast.remove();
                }, 350);
            }

            // Escape HTML to prevent XSS in toast content
            function escapeHtml(text) {
                var div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // ---- Lightweight Non-Blocking Notification Polling (Conserves MySQL max_user_connections) ----
            var pollTimer = null;
            var pollIntervalMs = 45000; // Poll every 45s when tab is active
            var lastEventId = 0;
            var isPolling = false;

            function pollNotifications() {
                if (document.hidden || isPolling) return;
                isPolling = true;

                fetch(sseUrl + '?mode=poll&last_id=' + lastEventId, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                    .then(function (r) { return r.ok ? r.json() : null; })
                    .then(function (data) {
                        isPolling = false;
                        if (!data) return;
                        if (data.last_id && data.last_id > lastEventId) {
                            lastEventId = data.last_id;
                        }
                        if (Array.isArray(data.events)) {
                            data.events.forEach(function (ev) {
                                if (ev && ev.source) showToast(ev);
                            });
                        }
                    })
                    .catch(function () {
                        isPolling = false;
                    });
            }

            function startPolling() {
                if (pollTimer) clearInterval(pollTimer);
                pollTimer = setInterval(pollNotifications, pollIntervalMs);
                // Initial check 3s after page load
                setTimeout(pollNotifications, 3000);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', startPolling);
            } else {
                startPolling();
            }

            // Pause polling when tab is hidden, resume when visible
            document.addEventListener('visibilitychange', function () {
                if (document.hidden) {
                    if (pollTimer) {
                        clearInterval(pollTimer);
                        pollTimer = null;
                    }
                } else {
                    if (!pollTimer) startPolling();
                }
            });

            // Global PDF export loading feedback
            document.addEventListener('click', function (e) {
                const link = e.target.closest("a[href*='export-pdf'], a[href*='/pdf'], a[href*='dashboard/export']");
                if (link && !link.dataset.exporting) {
                    link.dataset.exporting = 'true';
                    const originalHtml = link.innerHTML;
                    link.innerHTML = '⏳ กำลังสร้าง PDF...';
                    link.style.pointerEvents = 'none';
                    link.style.opacity = '0.75';
                    setTimeout(function () {
                        link.innerHTML = originalHtml;
                        link.style.pointerEvents = 'auto';
                        link.style.opacity = '1';
                        delete link.dataset.exporting;
                    }, 4000);
                }
            });
        })();
    </script>
</body>

</html>