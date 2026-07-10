<?php

declare(strict_types=1);

use App\Controller\Api\AdminApiController;
use App\Controller\Api\AuthController;
use App\Controller\Api\PublicController;
use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Support\Auth;

require dirname(__DIR__) . '/vendor/autoload.php';

Config::load(dirname(__DIR__));

// --- CORS ---
$origin = (string) Config::get('CORS_ALLOWED_ORIGIN', '*');
header('Access-Control-Allow-Origin: ' . $origin);
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$request = Request::fromGlobals();

// --- Statické servírování nahraných obrázků ---
if (str_starts_with($request->path, '/uploads/')) {
    $file = Config::basePath('/storage' . $request->path);
    if (is_file($file)) {
        header('Content-Type: ' . (mime_content_type($file) ?: 'application/octet-stream'));
        readfile($file);
        exit;
    }
    Response::error('Not found', 404);
    exit;
}

// --- Administrace (server-rendered PHP) ---
if ($request->path === '/admin' || str_starts_with($request->path, '/admin/')) {
    require dirname(__DIR__) . '/admin/index.php';
    exit;
}

// --- Swagger UI + OpenAPI JSON (jen mimo produkci) ---
$swaggerEnabled = Config::get('APP_ENV') !== 'production';
if ($swaggerEnabled && $request->path === '/api/openapi.json') {
    // swagger-php hlásí deprecations na PHP 8.5 (na cílovém 8.2 nevzniká) - ztlumíme je.
    $previous = error_reporting(error_reporting() & ~E_DEPRECATED);
    $json = \OpenApi\Generator::scan([dirname(__DIR__) . '/src/Controller'])->toJson();
    error_reporting($previous);
    header('Content-Type: application/json');
    echo $json;
    exit;
}
if ($swaggerEnabled && $request->path === '/swagger') {
    require dirname(__DIR__) . '/public/swagger.php';
    exit;
}

// --- robots.txt ---
if ($request->path === '/robots.txt') {
    $base = rtrim((string) Config::get('APP_URL', ''), '/');
    $allowIndex = ((new App\Repository\SettingRepository())->all()['seo_index'] ?? '1') !== '0';
    header('Content-Type: text/plain; charset=utf-8');
    echo $allowIndex
        ? "User-agent: *\nAllow: /\n\nSitemap: {$base}/sitemap.xml\n"
        : "User-agent: *\nDisallow: /\n";
    exit;
}

// --- sitemap.xml ---
if ($request->path === '/sitemap.xml') {
    $base = rtrim((string) Config::get('APP_URL', ''), '/');
    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
        . "  <url><loc>{$base}/</loc><changefreq>weekly</changefreq><priority>1.0</priority></url>\n"
        . '</urlset>' . "\n";
    exit;
}

// --- SPA stránky se serverově vloženými SEO meta (vše mimo /api) ---
if ($request->path !== '/api' && !str_starts_with($request->path, '/api/')) {
    $shell = dirname(Config::basePath()) . '/frontend/dist/index.html';
    if (is_file($shell)) {
        $settings = (new App\Repository\SettingRepository())->all();
        $base = (string) Config::get('APP_URL', '');
        header('Content-Type: text/html; charset=utf-8');
        $rendered = App\Support\SeoRenderer::render((string) file_get_contents($shell), $settings, $base, $request->path);
        $rendered = App\Support\ThemeRenderer::render($rendered, $settings);
        echo $rendered;
        exit;
    }
    Response::error('Not found', 404);
    exit;
}

// --- REST API routy ---
$router = new App\Core\Router();
$public = new PublicController();
$auth = new AuthController();
$admin = new AdminApiController();
$guard = [Auth::apiMiddleware()];

$router->get('/api/settings', [$public, 'settings']);
$router->get('/api/services', [$public, 'services']);
$router->get('/api/portfolio', [$public, 'portfolio']);
$router->post('/api/inquiries', [$public, 'createInquiry']);
$router->post('/api/hit', [$public, 'hit']);
$router->post('/api/auth/login', [$auth, 'login']);

$router->post('/api/admin/services', [$admin, 'createService'], $guard);
$router->put('/api/admin/services/{id}', [$admin, 'updateService'], $guard);
$router->delete('/api/admin/services/{id}', [$admin, 'deleteService'], $guard);
$router->post('/api/admin/portfolio', [$admin, 'createPortfolio'], $guard);
$router->delete('/api/admin/portfolio/{id}', [$admin, 'deletePortfolio'], $guard);
$router->put('/api/admin/settings', [$admin, 'updateSettings'], $guard);
$router->get('/api/admin/inquiries', [$admin, 'inquiries'], $guard);
$router->delete('/api/admin/inquiries/{id}', [$admin, 'deleteInquiry'], $guard);

$router->dispatch($request);
