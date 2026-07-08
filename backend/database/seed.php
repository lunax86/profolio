<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Database;
use App\Repository\AdminUserRepository;
use App\Repository\PortfolioRepository;
use App\Repository\ServiceRepository;
use App\Repository\SettingRepository;

require __DIR__ . '/../vendor/autoload.php';

Config::load(dirname(__DIR__));
$pdo = Database::connection();

// --- Výchozí admin ---
$users = new AdminUserRepository();
$email = (string) Config::get('ADMIN_EMAIL', 'admin@example.com');
if ($users->findByEmail($email) === null) {
    $users->create($email, password_hash((string) Config::get('ADMIN_PASSWORD', 'admin123'), PASSWORD_DEFAULT), true);
    echo "Vytvořen super admin: {$email}\n";
}

// --- Nastavení webu ---
$settings = new SettingRepository();
$settings->setMany([
    'site_title' => 'Vaše firma',
    'hero_title' => 'Stavíme věci, které vydrží',
    'hero_slogan' => 'Kvalitní řešení na míru od návrhu až po realizaci.',
    'hero_image' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=1920&q=80',
    'contact_email' => 'info@vasefirma.cz',
    'contact_phone' => '+420 777 123 456',
    'contact_address' => 'Hlavní 1, 110 00 Praha',
    'social_facebook' => 'https://facebook.com',
    'social_instagram' => 'https://instagram.com',
]);
echo "Nastavení webu uloženo.\n";

// --- Služby (jen pokud jsou prázdné) ---
$services = new ServiceRepository();
if ($services->all() === []) {
    foreach ([
        ['title' => 'Návrh na míru', 'description' => 'Připravíme řešení přesně podle vašich potřeb.', 'icon' => 'pencil-ruler', 'sort_order' => 1],
        ['title' => 'Realizace', 'description' => 'Profesionální provedení s důrazem na detail.', 'icon' => 'hammer', 'sort_order' => 2],
        ['title' => 'Servis a údržba', 'description' => 'Postaráme se o vše i po dokončení.', 'icon' => 'wrench', 'sort_order' => 3],
        ['title' => 'Konzultace', 'description' => 'Poradíme vám s výběrem i rozpočtem.', 'icon' => 'message-circle', 'sort_order' => 4],
    ] as $service) {
        $services->create($service);
    }
    echo "Služby vytvořeny.\n";
}

// --- Portfolio ---
$portfolio = new PortfolioRepository();
if ($portfolio->all() === []) {
    foreach ([
        ['title' => 'Projekt A', 'description' => 'Ukázka naší práce.', 'image_path' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?auto=format&fit=crop&w=800&q=80', 'sort_order' => 1],
        ['title' => 'Projekt B', 'description' => 'Ukázka naší práce.', 'image_path' => 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&w=800&q=80', 'sort_order' => 2],
        ['title' => 'Projekt C', 'description' => 'Ukázka naší práce.', 'image_path' => 'https://images.unsplash.com/photo-1523413363574-c30aa1c2a516?auto=format&fit=crop&w=800&q=80', 'sort_order' => 3],
    ] as $item) {
        $portfolio->create($item);
    }
    echo "Portfolio vytvořeno.\n";
}

echo "Seed dokončen.\n";
