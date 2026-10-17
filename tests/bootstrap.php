<?php
/**
 * PHPUnit Bootstrap
 * 
 * Loads the application for testing
 */

// Define paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');
define('VIEW_PATH', ROOT_PATH . '/views');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Load core dependencies
require_once CORE_PATH . '/Helpers.php';
require_once CORE_PATH . '/Env.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Session.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/App.php';
require_once CORE_PATH . '/Notifier.php';
require_once CORE_PATH . '/Validator.php';
require_once CORE_PATH . '/CSRF.php';

// Load services
require_once APP_PATH . '/services/EXPService.php';
require_once APP_PATH . '/services/BadgeService.php';

// Load .env for test database
Env::load(ROOT_PATH . '/.env');

// Set error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', 1);
