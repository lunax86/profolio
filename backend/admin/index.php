<?php

declare(strict_types=1);

use App\Core\Config;
use App\Repository\AdminUserRepository;
use App\Repository\InquiryRepository;
use App\Repository\LoginAttemptRepository;
use App\Repository\PageViewRepository;
use App\Repository\PortfolioRepository;
use App\Repository\ServiceRepository;
use App\Repository\SettingRepository;
use App\Support\Auth;
use App\Support\Csrf;
use App\Support\RateLimiter;
use App\Support\Uploader;
use App\Support\Version;

/*
 * Server-rendered administrace. Volané z public/index.php pro cesty /admin*.
 * Jednoduchý akční router: /admin/{action}.
 */

Auth::ensureSession();

$path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/', '/');
$action = trim(str_replace('/admin', '', $path), '/') ?: 'dashboard';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$post = static fn (string $key, $default = '') => $_POST[$key] ?? $default;

/** Vykreslení šablony s layoutem. */
$render = static function (string $view, array $data = [], string $title = 'Administrace'): void {
    extract($data, EXTR_SKIP);
    require __DIR__ . '/layout/header.php';
    require __DIR__ . "/views/{$view}.php";
    require __DIR__ . '/layout/footer.php';
};
$redirect = static function (string $to): never {
    header('Location: /admin/' . ltrim($to, '/'));
    exit;
};
$verifyCsrf = static function () use ($post): void {
    if (!Csrf::check($post('_csrf'))) {
        http_response_code(419);
        exit('Neplatný CSRF token.');
    }
};

// --- Login / logout (bez přihlášení) ---
if ($action === 'login') {
    $limiter = new RateLimiter(Config::basePath('/storage/ratelimit'));
    $attempts = new LoginAttemptRepository();
    $ipAddress = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $loginKey = 'login:' . $ipAddress;
    $lockedMessage = 'Příliš mnoho pokusů o přihlášení. Zkuste to prosím za 15 minut.';
    $locked = $limiter->tooMany($loginKey, 5, 900); // max 5 neúspěchů / 15 min na IP

    if ($method === 'POST') {
        $verifyCsrf();
        $email = (string) $post('email');
        if ($locked) {
            $attempts->record($email, $ipAddress, false);
            $render('login', ['error' => $lockedMessage], 'Přihlášení');

            return;
        }
        $user = (new AdminUserRepository())->findByEmail($email);
        if ($user && password_verify((string) $post('password'), $user['password_hash'])) {
            $attempts->record($email, $ipAddress, true);
            Auth::login($user);
            $redirect('dashboard');
        }
        $attempts->record($email, $ipAddress, false);
        $limiter->record($loginKey, 900); // započítej neúspěšný pokus
        $render('login', ['error' => 'Neplatné přihlašovací údaje.'], 'Přihlášení');

        return;
    }
    $render('login', ['error' => $locked ? $lockedMessage : null], 'Přihlášení');

    return;
}
if ($action === 'logout') {
    Auth::logout();
    $redirect('login');
}

// --- Od tohoto místa je vyžadováno přihlášení ---
if (!Auth::check()) {
    $redirect('login');
}

switch ($action) {
    case 'services':
        $repo = new ServiceRepository();
        if ($method === 'POST') {
            $verifyCsrf();
            $data = ['title' => $post('title'), 'description' => $post('description'), 'icon' => $post('icon', 'sparkles'), 'sort_order' => (int) $post('sort_order', 0)];
            if ($post('_action') === 'delete') {
                $repo->delete((int) $post('id'));
            } elseif ($post('id')) {
                $repo->update((int) $post('id'), $data);
            } else {
                $repo->create($data);
            }
            $redirect('services');
        }
        $render('services', ['services' => $repo->all()], 'Služby');
        break;

    case 'portfolio':
        $repo = new PortfolioRepository();
        if ($method === 'POST') {
            $verifyCsrf();
            if ($post('_action') === 'delete') {
                $repo->delete((int) $post('id'));
            } else {
                $imagePath = '';
                if (!empty($_FILES['image']['tmp_name'])) {
                    $imagePath = Uploader::store($_FILES['image']);
                }
                $repo->create(['title' => $post('title'), 'description' => $post('description'), 'image_path' => $imagePath ?: $post('image_url'), 'sort_order' => (int) $post('sort_order', 0)]);
            }
            $redirect('portfolio');
        }
        $render('portfolio', ['items' => $repo->all()], 'Portfolio');
        break;

    case 'settings':
        $repo = new SettingRepository();
        if ($method === 'POST') {
            $verifyCsrf();
            $keys = ['site_title', 'hero_title', 'hero_slogan', 'hero_image', 'contact_email', 'contact_phone', 'contact_address', 'social_facebook', 'social_instagram', 'privacy_policy', 'seo_title', 'seo_description', 'seo_image', 'seo_index'];
            $repo->setMany(array_intersect_key($_POST, array_flip($keys)));

            if ($post('favicon_remove')) {
                $repo->setMany(['favicon_path' => '']);
            } elseif (!empty($_FILES['favicon']['tmp_name'])) {
                try {
                    $repo->setMany(['favicon_path' => Uploader::store($_FILES['favicon'])]);
                } catch (\RuntimeException $exception) {
                    $redirect('settings?err=' . rawurlencode($exception->getMessage()));
                }
            }
            $redirect('settings');
        }
        $render('settings', ['settings' => $repo->all()], 'Nastavení');
        break;

    case 'inquiries':
        $repo = new InquiryRepository();
        $archivedView = ($_GET['archiv'] ?? '') === '1';
        if ($method === 'POST') {
            $verifyCsrf();
            $id = (int) $post('id');
            if ($post('_action') === 'read') {
                $repo->markRead($id);
            } elseif ($post('_action') === 'archive') {
                $repo->setArchived($id, true);
            } elseif ($post('_action') === 'unarchive') {
                $repo->setArchived($id, false);
            } elseif ($post('_action') === 'delete') {
                $repo->deleteArchived($id);
            }
            $redirect('inquiries' . ($archivedView ? '?archiv=1' : ''));
        }
        $render('inquiries', [
            'inquiries' => $archivedView ? $repo->archived() : $repo->active(),
            'archivedView' => $archivedView,
            'archivedCount' => count($repo->archived()),
        ], 'Poptávky');
        break;

    case 'security':
        $attempts = new LoginAttemptRepository();
        $cookieParams = session_get_cookie_params();
        $render('security', [
            'attempts' => $attempts->recent(50),
            'failed24h' => $attempts->failedSince(86400),
            'blockedIps' => $attempts->blockedIps(5, 900),
            'status' => [
                'https' => !empty($_SERVER['HTTPS']),
                'httpOnly' => (bool) ($cookieParams['httponly'] ?? false),
                'secure' => (bool) ($cookieParams['secure'] ?? false),
                'sameSite' => (string) ($cookieParams['samesite'] ?? ''),
            ],
        ], 'Bezpečnost');
        break;

    case 'dashboard':
    default:
        $version = ['current' => Version::current(), 'latest' => null, 'slug' => Version::repoSlug(), 'upToDate' => null, 'error' => null, 'checked' => false];
        if ($method === 'POST' && $post('_action') === 'check_updates') {
            $verifyCsrf();
            $version = Version::status() + ['checked' => true];
        }
        $render('dashboard', [
            'servicesCount' => count((new ServiceRepository())->all()),
            'portfolioCount' => count((new PortfolioRepository())->all()),
            'unread' => (new InquiryRepository())->unreadCount(),
            'views' => (new PageViewRepository())->stats(),
            'version' => $version,
        ], 'Přehled');
}
