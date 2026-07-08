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
            $keys = ['site_title', 'hero_title', 'hero_slogan', 'hero_image', 'contact_email', 'contact_phone', 'contact_address', 'social_facebook', 'social_instagram', 'privacy_policy', 'seo_title', 'seo_description', 'seo_image', 'seo_index', 'timezone'];
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
        $periods = ['24h' => 86400, '7d' => 604800, '30d' => 2592000];
        $period = array_key_exists((string) ($_GET['obdobi'] ?? ''), $periods) ? (string) $_GET['obdobi'] : '24h';
        $seconds = $periods[$period];
        $cookieParams = session_get_cookie_params();
        $render('security', [
            'attempts' => $attempts->since($seconds, 200),
            'total' => $attempts->countSince($seconds),
            'failed' => $attempts->countSince($seconds, true),
            'blockedIps' => $attempts->blockedIps(5, 900),
            'period' => $period,
            'status' => [
                'https' => !empty($_SERVER['HTTPS']),
                'httpOnly' => (bool) ($cookieParams['httponly'] ?? false),
                'secure' => (bool) ($cookieParams['secure'] ?? false),
                'sameSite' => (string) ($cookieParams['samesite'] ?? ''),
            ],
        ], 'Bezpečnost');
        break;

    case 'account':
        $users = new AdminUserRepository();
        $currentUser = $users->findById((int) Auth::user()['id']);
        if ($currentUser === null) {
            Auth::logout();
            $redirect('login');
        }
        $isSuper = (int) $currentUser['is_super'] === 1;

        if ($method === 'POST') {
            $verifyCsrf();
            $formAction = $post('_action');

            if ($formAction === 'change_password') {
                if (!password_verify((string) $post('current_password'), $currentUser['password_hash'])) {
                    $redirect('account?err=' . rawurlencode('Současné heslo není správné.'));
                }
                $newPassword = (string) $post('new_password');
                if (mb_strlen($newPassword) < 8) {
                    $redirect('account?err=' . rawurlencode('Nové heslo musí mít alespoň 8 znaků.'));
                }
                if ($newPassword !== (string) $post('new_password_confirm')) {
                    $redirect('account?err=' . rawurlencode('Nová hesla se neshodují.'));
                }
                $users->updatePassword((int) $currentUser['id'], password_hash($newPassword, PASSWORD_DEFAULT));
                $redirect('account?ok=' . rawurlencode('Heslo bylo změněno.'));
            }

            if ($formAction === 'change_email') {
                if (!password_verify((string) $post('current_password'), $currentUser['password_hash'])) {
                    $redirect('account?err=' . rawurlencode('Současné heslo není správné.'));
                }
                $newEmail = trim((string) $post('new_email'));
                if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                    $redirect('account?err=' . rawurlencode('Neplatný e-mail.'));
                }
                if ($users->emailExists($newEmail, (int) $currentUser['id'])) {
                    $redirect('account?err=' . rawurlencode('Tento e-mail už používá jiný účet.'));
                }
                $users->updateEmail((int) $currentUser['id'], $newEmail);
                $_SESSION['admin_email'] = $newEmail;
                $redirect('account?ok=' . rawurlencode('E-mail byl změněn.'));
            }

            // Správu ostatních účtů smí jen super admin (ověřeno na serveru, ne jen skrytím tlačítek).
            if (!$isSuper) {
                http_response_code(403);
                exit('Nedostatečná oprávnění.');
            }

            if ($formAction === 'add') {
                $newEmail = trim((string) $post('email'));
                $newPassword = (string) $post('password');
                if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                    $redirect('account?err=' . rawurlencode('Neplatný e-mail.'));
                }
                if (mb_strlen($newPassword) < 8) {
                    $redirect('account?err=' . rawurlencode('Heslo správce musí mít alespoň 8 znaků.'));
                }
                if ($users->emailExists($newEmail)) {
                    $redirect('account?err=' . rawurlencode('Tento e-mail už používá jiný účet.'));
                }
                $users->create($newEmail, password_hash($newPassword, PASSWORD_DEFAULT), false);
                $redirect('account?ok=' . rawurlencode('Správce byl přidán.'));
            }

            if ($formAction === 'delete') {
                $targetId = (int) $post('id');
                $target = $users->findById($targetId);
                if ($target === null) {
                    $redirect('account?err=' . rawurlencode('Účet neexistuje.'));
                }
                if ((int) $target['is_super'] === 1) {
                    $redirect('account?err=' . rawurlencode('Super admina nelze smazat.'));
                }
                if ($targetId === (int) $currentUser['id']) {
                    $redirect('account?err=' . rawurlencode('Nelze smazat vlastní účet.'));
                }
                $users->delete($targetId);
                $redirect('account?ok=' . rawurlencode('Správce byl smazán.'));
            }

            $redirect('account');
        }

        $render('account', [
            'currentUser' => $currentUser,
            'isSuper' => $isSuper,
            'admins' => $users->all(),
            'ok' => isset($_GET['ok']) ? (string) $_GET['ok'] : null,
            'err' => isset($_GET['err']) ? (string) $_GET['err'] : null,
        ], 'Účet');
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
