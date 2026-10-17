<?php
/**
 * Web Routes — กำหนดเส้นทาง URL ทั้งหมด
 */

// ==========================================
// PUBLIC ROUTES (ไม่ต้อง Login)
// ==========================================
Router::get('', 'HomeController@index');
Router::get('login', 'AuthController@loginForm', ['guest']);
Router::post('login', 'AuthController@login', ['guest']);
Router::get('logout', 'AuthController@logout');

// Emergency Rescue System — DISABLED (security: was a public privilege-escalation endpoint)
// กู้คืนสิทธิ์แอดมินให้ทำผ่าน CLI เท่านั้น: scripts/maintenance/fix_admin.php

// Google SSO
Router::get('login/google', 'SsoController@redirect', ['guest']);
Router::get('login/google/callback', 'SsoController@callback', ['guest']);

// PDPA Consent
Router::get('consent', 'AuthController@consentForm', ['auth']);
Router::post('consent', 'AuthController@saveConsent', ['auth']);

// Public Portfolio Sharing
Router::get('share/{token}', 'ShareController@render');
Router::get('p/{code}', 'PublicController@profile');

// ==========================================
// AUTHENTICATED ROUTES (ต้อง Login)
// ==========================================

// Dashboard
Router::get('dashboard', 'DashboardController@index', ['auth']);

// Student Profile
Router::get('profile', 'ProfileController@show', ['auth']);
Router::get('profile/edit', 'ProfileController@edit', ['auth']);
Router::post('profile/update', 'ProfileController@update', ['auth']);
Router::post('profile/avatar', 'ProfileController@updateAvatar', ['auth']);
Router::get('profile/export-data', 'DataExportController@download', ['auth']);

// Portfolio
Router::get('portfolio', 'PortfolioController@index', ['auth']);
Router::get('portfolio/create', 'PortfolioController@create', ['auth', 'student']);
Router::post('portfolio/store', 'PortfolioController@store', ['auth', 'student']);
Router::get('portfolio/export-pdf', 'PortfolioController@exportDigest', ['auth']);
Router::get('portfolio/{id}', 'PortfolioController@show', ['auth']);
Router::post('portfolio/collaborate/{id}/accept', 'PortfolioController@acceptCollaboration', ['auth', 'student']);
Router::post('portfolio/collaborate/{id}/reject', 'PortfolioController@rejectCollaboration', ['auth', 'student']);
Router::get('portfolio/{id}/edit', 'PortfolioController@edit', ['auth', 'student']);
Router::post('portfolio/{id}/update', 'PortfolioController@update', ['auth', 'student']);
Router::post('portfolio/{id}/delete', 'PortfolioController@delete', ['auth', 'student']);
Router::post('portfolio/{id}/feature', 'PortfolioController@toggleFeatured', ['auth', 'student']);

// Academic Records
Router::get('academics', 'AcademicController@index', ['auth']);
Router::post('academics/store', 'AcademicController@store', ['auth']);
Router::post('academics/import', 'AcademicController@import', ['auth', 'teacher']);
Router::get('academics/{id}/edit', 'AcademicController@edit', ['auth', 'student']);
Router::post('academics/{id}/update', 'AcademicController@update', ['auth', 'student']);
Router::post('academics/{id}/delete', 'AcademicController@delete', ['auth', 'student']);

// Goals
Router::get('goals', 'GoalController@index', ['auth', 'student']);
Router::post('goals/store', 'GoalController@store', ['auth', 'student']);
Router::post('goals/{id}/update', 'GoalController@update', ['auth', 'student']);
Router::post('goals/{id}/delete', 'GoalController@delete', ['auth', 'student']);

// Self Assessment (RIASEC)
Router::get('assessment', 'AssessmentController@index', ['auth', 'student']);
Router::get('assessment/test', 'AssessmentController@test', ['auth', 'student']);
Router::post('assessment/store', 'AssessmentController@store', ['auth', 'student']);

// Interests & Talents
Router::get('interests', 'InterestController@index', ['auth', 'student']);
Router::post('interests/store', 'InterestController@store', ['auth', 'student']);
Router::post('interests/{id}/delete', 'InterestController@delete', ['auth', 'student']);

// AI Guidance
Router::get('ai-guide', 'AIController@index', ['auth', 'student']);
Router::post('ai-guide/analyze', 'AIController@analyze', ['auth', 'student']);
Router::post('ai-guide/refine', 'AIController@refineDescription', ['auth', 'student']);
Router::get('opportunities', 'AIController@opportunities', ['auth', 'student']);

// Phase 4: AI Insights
Router::get('ai/field-readiness', 'AIController@fieldReadiness', ['auth']);
Router::get('ai/portfolio-audit', 'AIController@portfolioAudit', ['auth']);
Router::post('ai/api/recommendations', 'AIController@getRecommendations', ['auth']);
Router::post('ai/api/field-readiness', 'AIController@getFieldReadiness', ['auth']);

// Phase 5.1: TCAS AI Advisor (Gemini-Powered Gap Analysis)
Router::get('ai/tcas-advisor', 'AIController@tcasAdvisor', ['auth', 'student']);
Router::post('ai/api/tcas-gap', 'AIController@analyzeTCASGap', ['auth', 'student']);

// Rewards System
Router::get('rewards', 'RewardController@index', ['auth']);
Router::get('rewards/badges', 'RewardController@showBadges', ['auth']);
Router::post('rewards/redeem/{id}', 'RewardController@redeem', ['auth', 'student']);

// Phase 4.1: Rewards Shop (Points Shop MVP)
Router::get('shop', 'RewardsShopController@index', ['auth']);
Router::post('shop/redeem/{id}', 'RewardsShopController@redeem', ['auth']);

// Notifications
Router::get('notifications', 'NotificationController@index', ['auth']);
Router::get('notifications/mark-all-read', 'NotificationController@markAllRead', ['auth']);
Router::post('notifications/mark-all-read', 'NotificationController@markAllRead', ['auth']);
Router::get('notifications/{id}/read', 'NotificationController@markRead', ['auth']);
Router::post('notifications/{id}/read', 'NotificationController@markRead', ['auth']);

// ==========================================
// PROJECT TRACKING ROUTES (นักเรียน)
// ==========================================
Router::get('projects', 'ProjectController@index', ['auth']);
Router::get('projects/create', 'ProjectController@create', ['auth']);
Router::post('projects/store', 'ProjectController@store', ['auth']);
Router::get('projects/{id}', 'ProjectController@show', ['auth']);
Router::get('projects/export/{id}', 'ProjectController@exportToPortfolio', ['auth', 'student']);
Router::get('projects/{id}/pdf', 'ProjectController@exportPdf', ['auth']);

// การบันทึกเอกสารและ Logbook ของโครงงาน
Router::post('documents/store', 'DocumentController@store', ['auth']);
Router::post('logbooks/store', 'LogbookController@store', ['auth']);

// ==========================================
// ADVISOR ROUTES (ครูที่ปรึกษา)
// ==========================================
Router::get('advisor/projects', 'AdvisorController@index', ['auth', 'teacher']);
Router::get('advisor/projects/{id}', 'AdvisorController@show', ['auth', 'teacher']);
Router::post('advisor/projects/{id}/approve', 'AdvisorController@approve', ['auth', 'teacher']);
Router::post('advisor/documents/{id}/review', 'AdvisorController@reviewDocument', ['auth', 'teacher']);

// ==========================================
// TEACHER ROUTES
// ==========================================
Router::get('teacher/students', 'TeacherController@students', ['auth', 'staff']);
Router::get('teacher/student/{id}', 'TeacherController@viewStudent', ['auth', 'staff']);
Router::get('teacher/student/{id}/export-ai', 'TeacherController@exportStudentToMarkdown', ['auth', 'staff']);
Router::post('teacher/comment', 'TeacherController@addComment', ['auth', 'teacher']);
Router::post('teacher/portfolio/{id}/verify', 'TeacherController@verifyPortfolio', ['auth', 'teacher']);
Router::get('teacher/pending-reviews', 'TeacherController@pendingReviews', ['auth', 'teacher']);
Router::post('teacher/bulk-verify', 'TeacherController@bulkVerify', ['auth', 'teacher']);
Router::get('mentor/intelligence', 'TeacherController@mentorDashboard', ['auth', 'teacher']);

// Phase 2: Quality Control
Router::get('teacher/quick-sweep', 'TeacherController@quickSweep', ['auth', 'teacher']);
Router::post('teacher/portfolio/{id}/annotate', 'TeacherController@annotate', ['auth', 'teacher']);
Router::post('teacher/annotation/{id}/resolve', 'TeacherController@resolveAnnotation', ['auth']);

// ==========================================
// ADMIN ROUTES
// ==========================================
Router::get('admin', 'AdminController@index', ['auth', 'admin']);
Router::get('admin/users', 'AdminController@users', ['auth', 'admin']);
Router::post('admin/users/create', 'AdminController@createUser', ['auth', 'admin']);
Router::get('admin/users/{id}/edit', 'AdminController@editUser', ['auth', 'admin']);
Router::post('admin/users/{id}/update', 'AdminController@updateUser', ['auth', 'admin']);
Router::post('admin/users/{id}/delete', 'AdminController@deleteUser', ['auth', 'admin']);
Router::post('admin/users/{id}/toggle', 'AdminController@toggleUser', ['auth', 'admin']);
Router::post('admin/users/{id}/promote', 'AdminController@promoteToTeacher', ['auth', 'admin']);
Router::post('admin/users/import', 'AdminController@importUsers', ['auth', 'admin']);
Router::get('admin/categories', 'AdminController@categories', ['auth', 'admin']);
Router::post('admin/categories/store', 'AdminController@storeCategory', ['auth', 'admin']);
Router::post('admin/categories/{id}/update', 'AdminController@updateCategory', ['auth', 'admin']);
Router::post('admin/categories/{id}/delete', 'AdminController@deleteCategory', ['auth', 'admin']);
Router::get('admin/logs', 'AdminController@logs', ['auth', 'admin']);
Router::get('admin/reports', 'AdminController@reports', ['auth', 'executive']);
Router::get('admin/settings', 'AdminController@settings', ['auth', 'admin']);
Router::post('admin/settings/update', 'AdminController@updateSettings', ['auth', 'admin']);

// Executive Roadmap Routes
Router::get('executive/summary', 'DashboardController@executiveSummary', ['auth', 'executive']);
Router::get('executive/ews', 'DashboardController@executiveEarlyWarning', ['auth', 'executive']);
Router::get('executive/tcas', 'DashboardController@executiveTcasPrediction', ['auth', 'executive']);
Router::get('executive/excellence', 'DashboardController@executiveExcellenceReport', ['auth', 'executive']);
Router::get('executive/showcase', 'DashboardController@executiveShowcase', ['auth', 'executive']);

// Mock Data Testing
Router::post('admin/mock/generate', 'AdminController@generateMockData', ['auth', 'admin']);
Router::post('admin/mock/clear', 'AdminController@clearMockData', ['auth', 'admin']);

// ==========================================
// API ROUTES (for AJAX)
// ==========================================
Router::get('api/stats', 'ApiController@stats', ['auth']);
Router::get('api/portfolio/search', 'ApiController@searchPortfolio', ['auth']);
Router::get('api/students/search', 'ApiController@searchStudents', ['auth']);
Router::get('api/notifications/count', 'ApiController@notificationCount', ['auth']);

// Real-Time SSE Notification Stream (no middleware — auth checked inside controller)
Router::get('api/stream/notifications', 'SSENotificationsController@stream');

// ==========================================
// DATA PRODUCTS ROUTES
// ==========================================

// Student 360° Profile
Router::get('profile/360', 'ProfileController@show360', ['auth']);

// Early Warning System (Admin/Teacher)
Router::get('analytics/early-warning', 'AnalyticsController@earlyWarning', ['auth']);
Router::post('analytics/early-warning/notify', 'AnalyticsController@notifyRisk', ['auth']);

// TCAS Readiness Predictor
Router::get('tcas-readiness', 'AnalyticsController@tcasReadiness', ['auth']);

// School Analytics Dashboard (Admin/Executive)
Router::get('analytics/dashboard', 'AnalyticsController@schoolDashboard', ['auth']);
Router::get('analytics/dashboard/export', 'AnalyticsController@exportExecutiveReport', ['auth']);

// Cron job for milestone checks (can be called via system cron)
Router::get('cron/milestone-check', 'AnalyticsController@cronMilestones');
