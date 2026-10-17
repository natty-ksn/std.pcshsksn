<?php
/**
 * Router — จัดการ URL Routing
 * รองรับ GET, POST และ Middleware
 */
class Router
{
    private static array $routes = [];
    private static array $middlewares = [];

    /**
     * Register GET route
     */
    public static function get(string $path, string $action, array $middleware = []): void
    {
        self::$routes['GET'][$path] = [
            'action' => $action,
            'middleware' => $middleware
        ];
    }

    /**
     * Register POST route  
     */
    public static function post(string $path, string $action, array $middleware = []): void
    {
        self::$routes['POST'][$path] = [
            'action' => $action,
            'middleware' => $middleware
        ];
    }

    /**
     * Get all registered routes
     */
    public static function getRoutes(): array
    {
        return self::$routes;
    }

    /**
     * Dispatch the current request
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $url = self::getUrl();
        $uri = $_SERVER['REQUEST_URI'] ?? 'unknown';

        App::log("ROUTER: Method: {$method}, URL: [{$url}], URI: [{$uri}]", 'DEBUG');

        // ค้นหา Route ที่ตรงกัน
        $matchedRoute = null;
        $params = [];

        if (isset(self::$routes[$method])) {
            foreach (self::$routes[$method] as $pattern => $route) {
                $regexPattern = $this->convertToRegex($pattern);
                if (preg_match($regexPattern, $url, $matches)) {
                    $matchedRoute = $route;
                    // เก็บ parameters จาก URL (เช่น {id})
                    array_shift($matches);
                    $params = $matches;
                    break;
                }
            }
        }

        if ($matchedRoute === null) {
            $this->notFound();
            return;
        }

        // ตรวจสอบ Middleware
        foreach ($matchedRoute['middleware'] as $mw) {
            if (!$this->runMiddleware($mw)) {
                return;
            }
        }

        // เรียก Controller@method
        $this->callAction($matchedRoute['action'], $params);
    }

    /**
     * แปลง Route pattern เป็น Regex
     * /portfolio/{id} → /^portfolio\/([^\/]+)$/
     */
    private function convertToRegex(string $pattern): string
    {
        $pattern = trim($pattern, '/');
        // แปลง {id} เป็น ([^/]+) และรองรับ slashes ในตัวแปรถ้าจำเป็น
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $pattern);
        return '#^' . $pattern . '$#';
    }

    /**
     * Get clean URL
     */
    private static function getUrl(): string
    {
        // 1. จาก URL query parameter (ที่ถูก Rewrite มา)
        if (isset($_GET['url'])) {
            return trim($_GET['url'], '/');
        }

        // 2. จาก PATH_INFO
        if (isset($_SERVER['PATH_INFO'])) {
            return trim($_SERVER['PATH_INFO'], '/');
        }

        // 3. ปรับแต่งจาก REQUEST_URI
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = str_replace('/index.php', '', $script);

        // ตัด Query String ออก
        $path = parse_url($uri, PHP_URL_PATH) ?? '';

        // ถ้าอยู่ใน subfolder ให้ตัดออก
        if ($base !== '/' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        return trim($path, '/');
    }

    /**
     * เรียก Controller method
     */
    private function callAction(string $action, array $params): void
    {
        [$controllerName, $methodName] = explode('@', $action);

        $controllerFile = APP_PATH . "/controllers/{$controllerName}.php";

        // Fallback: check subdirectories for namespaced controllers
        // e.g., ApiAuthController → Api/AuthController.php
        if (!file_exists($controllerFile)) {
            $subDir = $this->resolveControllerSubdirectory($controllerName);
            if ($subDir !== null) {
                $controllerFile = APP_PATH . "/controllers/{$subDir}/" . str_replace($subDir, '', $controllerName) . '.php';
                // Clean double slashes
                $controllerFile = str_replace('//', '/', $controllerFile);
            }
        }

        if (!file_exists($controllerFile)) {
            App::log("Controller not found: {$controllerName}", 'ERROR');
            $this->notFound();
            return;
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            App::log("Class not found: {$controllerName}", 'ERROR');
            $this->notFound();
            return;
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $methodName)) {
            App::log("Method not found: {$controllerName}@{$methodName}", 'ERROR');
            $this->notFound();
            return;
        }

        call_user_func_array([$controller, $methodName], $params);
    }

    /**
     * Run middleware check
     */
    private function runMiddleware(string $middleware): bool
    {
        switch ($middleware) {
            case 'auth':
                if (!Auth::check()) {
                    Session::flash('error', 'กรุณาเข้าสู่ระบบก่อน');
                    App::redirect('login');
                    return false;
                }
                return true;

            case 'admin':
                if (!Auth::hasRole('admin')) {
                    ErrorHandler::forbidden('คุณต้องมีสิทธิ์ผู้ดูแลระบบ');
                    return false;
                }
                return true;

            case 'teacher':
                if (!Auth::hasRole('teacher') && !Auth::hasRole('admin')) {
                    ErrorHandler::forbidden('คุณต้องมีสิทธิ์ครูผู้สอน');
                    return false;
                }
                return true;

            case 'student':
                if (!Auth::hasRole('student')) {
                    ErrorHandler::forbidden('คุณต้องมีสิทธิ์นักเรียน');
                    return false;
                }
                return true;

            case 'guest':
                if (Auth::check()) {
                    App::redirect('dashboard');
                    return false;
                }
                return true;

            case 'executive':
                if (!Auth::hasAnyRole(['admin', 'executive'])) {
                    ErrorHandler::forbidden('คุณต้องมีสิทธิ์ผู้บริหาร');
                    return false;
                }
                return true;

            case 'staff':
                if (!Auth::hasAnyRole(['admin', 'teacher', 'executive'])) {
                    ErrorHandler::forbidden('คุณต้องมีสิทธิ์บุคลากรหรือฝ่ายบริหาร');
                    return false;
                }
                return true;

            default:
                if (str_starts_with($middleware, 'role:')) {
                    $roles = array_map('trim', explode(',', substr($middleware, 5)));
                    if (!Auth::hasAnyRole($roles)) {
                        ErrorHandler::forbidden('คุณไม่มีสิทธิ์เข้าถึงส่วนนี้');
                        return false;
                    }
                }
                return true;
        }
    }

    /**
     * Resolve controller subdirectory from class name.
     *
     * Convention: ApiAuthController → Api/AuthController
     *             The prefix before the word "Controller" maps to a subdirectory.
     *
     * @param string $controllerName e.g., 'ApiAuthController'
     * @return string|null Subdirectory name or null if no match
     */
    private function resolveControllerSubdirectory(string $controllerName): ?string
    {
        // Known subdirectories in app/controllers/
        $knownDirs = ['Api', 'Web', 'Admin'];

        foreach ($knownDirs as $dir) {
            // Check if class name starts with the directory name
            if (str_starts_with($controllerName, $dir)) {
                $subFile = APP_PATH . "/controllers/{$dir}/" . substr($controllerName, strlen($dir)) . '.php';
                if (file_exists($subFile)) {
                    return $dir;
                }
            }
        }

        // Generic fallback: scan all subdirectories
        $controllersDir = APP_PATH . '/controllers';
        if (is_dir($controllersDir)) {
            $items = scandir($controllersDir);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..')
                    continue;
                $subPath = $controllersDir . '/' . $item;
                if (is_dir($subPath)) {
                    // Try: ApiAuthController → Api/AuthController.php
                    $fileName = substr($controllerName, strlen($item));
                    if (!empty($fileName)) {
                        $testFile = $subPath . '/' . $fileName . '.php';
                        if (file_exists($testFile)) {
                            return $item;
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * 404 Not Found
     */
    private function notFound(): void
    {
        ErrorHandler::notFound();
    }
}
