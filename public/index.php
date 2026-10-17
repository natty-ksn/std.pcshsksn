<?php
// PHP 8+ strict mode
declare(strict_types=1);

// Error reporting — controlled by environment
error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Display errors only in development (will be overridden after Env loads)
ini_set('display_errors', '0');

// Define base paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEW_PATH', ROOT_PATH . '/views');
define('PUBLIC_PATH', __DIR__);
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('LOG_PATH', ROOT_PATH . '/logs');

// Load core files
require_once CORE_PATH . '/Env.php';
require_once CORE_PATH . '/App.php';
require_once CORE_PATH . '/Helpers.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Router.php';
require_once CORE_PATH . '/Controller.php';
require_once CORE_PATH . '/Model.php';
require_once CORE_PATH . '/Session.php';
require_once CORE_PATH . '/CSRF.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/Settings.php';
require_once CORE_PATH . '/Validator.php';
require_once CORE_PATH . '/ErrorHandler.php';
require_once CORE_PATH . '/Notifier.php';
require_once CORE_PATH . '/AIEngine.php';
require_once CORE_PATH . '/Cache.php';
require_once CORE_PATH . '/EventDispatcher.php';
require_once CORE_PATH . '/EventListeners/CacheInvalidationListener.php';
require_once CORE_PATH . '/EventListeners/AuditLogListener.php';
require_once CORE_PATH . '/RateLimiter.php';

// Load composer autoload if exists
if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
}

// Load service layer
require_once APP_PATH . '/services/StudentService.php';
require_once APP_PATH . '/services/ReportService.php';
require_once APP_PATH . '/services/PDFService.php';
require_once APP_PATH . '/services/BadgeService.php';
require_once APP_PATH . '/services/EXPService.php';
require_once APP_PATH . '/services/AIService.php';

// Load API controllers and middleware
require_once APP_PATH . '/middleware/ApiMiddleware.php';
require_once APP_PATH . '/controllers/Api/BaseApiController.php';
require_once APP_PATH . '/controllers/Api/AuthController.php';
require_once APP_PATH . '/controllers/Api/StudentController.php';
require_once APP_PATH . '/controllers/Api/PublicController.php';

// Load environment variables
Env::load(ROOT_PATH . '/.env');

// Register centralized error handler
ErrorHandler::register();

// Apply API CORS & middleware (before any output)
ApiMiddleware::handle();

// Check maintenance mode
if (Env::get('APP_MAINTENANCE', 'false') === 'true') {
    ErrorHandler::maintenance();
}

// Global Rate Limiting (500 requests per minute per IP)
RateLimiter::check(500, 60);

// Toggle error display based on environment
if (Env::get('APP_DEBUG', 'false') === 'true') {
    ini_set('display_errors', '1');
}

// Set timezone
date_default_timezone_set(Env::get('APP_TIMEZONE', 'Asia/Bangkok'));

// Force UTF-8 output
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');

// Start session
Session::start();

// Initialize CSRF protection
CSRF::init();

// Register Event Listeners for Cache Invalidation and Audit Logging
\Core\EventListeners\CacheInvalidationListener::register();
\Core\EventListeners\AuditLogListener::register();

// Load routes and dispatch — API routes first, then web routes
$router = new Router();
require_once ROOT_PATH . '/routes/api.php';   // RESTful API v1 Gateway
require_once ROOT_PATH . '/routes/web.php';   // Web Application Routes
$router->dispatch();
