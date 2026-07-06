<?php

declare(strict_types=1);

use App\Repository\AdminUserRepository;
use App\Repository\InquiryRepository;
use App\Repository\PortfolioRepository;
use App\Repository\ServiceRepository;
use App\Repository\SettingRepository;
use App\Support\Auth;
use App\Support\Csrf;
use App\Support\Uploader;

/*
 * Server-rendered administrace. Volané z public/index.php pro cesty /admin*.
 * Jednoduchý akční router: /admin/{action}.
 */

Auth::ensureSession();

$path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/', '/');
$action = trim(str_replace('/admin', '', $path), '/') ?: 'dashboard';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$post = static fn (string $k, $d = '') => $_POST[$k] ?? $d;

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
    if ($method === 'POST') {
        $verifyCsrf();
        $user = (new AdminUserRepository())->findByEmail((string) $post('email'));
        if ($user && password_verify((string) $post('password'), $user['password_hash'])) {
            Auth::login($user);
            $redirect('dashboard');
        }
        $render('login', ['error' => 'Neplatné přihlašovací údaje.'], 'Přihlášení');

        return;
    }
    $render('login', ['error' => null], 'Přihlášení');

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
            $keys = ['site_title', 'hero_title', 'hero_slogan', 'hero_image', 'contact_email', 'contact_phone', 'contact_address', 'social_facebook', 'social_instagram'];
            $repo->setMany(array_intersect_key($_POST, array_flip($keys)));
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

    case 'dashboard':
    default:
        $render('dashboard', [
            'servicesCount' => count((new ServiceRepository())->all()),
            'portfolioCount' => count((new PortfolioRepository())->all()),
            'unread' => (new InquiryRepository())->unreadCount(),
        ], 'Přehled');
}
