<?php
/**
 * API Routes — RESTful API v1 Gateway
 *
 * All endpoints are prefixed with /api/v1/ and return strict JSON.
 * Authentication uses Bearer Tokens (not sessions).
 * CSRF checks are bypassed — tokens provide stateless auth.
 *
 * Base URL: /app/api/v1/
 *
 * Usage:
 *   curl -X POST http://localhost/app/api/v1/auth/login \
 *     -H "Content-Type: application/json" \
 *     -d '{"username":"student001","password":"secret123"}'
 *
 *   curl http://localhost/app/api/v1/student/profile \
 *     -H "Authorization: Bearer <token>"
 *
 * @package  PCSHSKSN e-Portfolio
 * @category API Routes
 */

// ============================================================
// PUBLIC API ROUTES (No Token Required)
// ============================================================

// Authentication
Router::get('api/v1/auth/status', 'ApiAuthController@status');
Router::post('api/v1/auth/login', 'ApiAuthController@login');

// Public portfolio sharing (read-only, no auth)
Router::get('api/v1/share/{token}', 'ApiPublicController@getSharedPortfolio');
Router::get('api/v1/public/student/{code}', 'ApiPublicController@getStudentByCode');

// ============================================================
// AUTHENTICATED API ROUTES (Bearer Token Required)
// ============================================================

// Auth — Token Lifecycle
Router::get('api/v1/auth/me', 'ApiAuthController@me');
Router::post('api/v1/auth/refresh', 'ApiAuthController@refresh');
Router::post('api/v1/auth/logout', 'ApiAuthController@logout');
Router::get('api/v1/auth/tokens', 'ApiAuthController@listTokens');
Router::post('api/v1/auth/tokens/{id}/revoke', 'ApiAuthController@revokeToken');
Router::post('api/v1/auth/tokens/revoke-all', 'ApiAuthController@revokeAllTokens');

// Student 360° Profile
Router::get('api/v1/student/profile', 'ApiStudentController@getProfile');

// Portfolio
Router::get('api/v1/student/portfolio', 'ApiStudentController@listPortfolio');
Router::get('api/v1/student/portfolio/{id}', 'ApiStudentController@getPortfolioItem');

// TCAS AI Analysis
Router::get('api/v1/student/tcas-analysis', 'ApiStudentController@getTCASAnalysis');

// Gamification Stats
Router::get('api/v1/student/stats', 'ApiStudentController@getStats');

// Notifications
Router::get('api/v1/student/notifications', 'ApiStudentController@getNotifications');
Router::post('api/v1/student/notifications/{id}/read', 'ApiStudentController@markNotificationRead');
