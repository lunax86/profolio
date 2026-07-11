<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Database;
use App\Repository\AdminUserRepository;
use App\Repository\PortfolioRepository;
use App\Repository\ServiceRepository;
use App\Repository\SettingRepository;
use App\Repository\TestimonialRepository;

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
    'site_title' => 'Jan Novák',
    // Obecný slogan = základ pro hlavní text hera, patičku i SEO (v úvodu/patičce lze přepsat vlastním).
    'slogan' => 'Poctivě a s osobním přístupem, od návrhu po úklid.',
    'hero_title' => 'Rekonstrukce bytů na klíč',
    'hero_place' => 'Praha a okolí',
    'hero_about' => 'Jsem Jan Novák, rekonstrukcím bytů se věnuji přes 12 let. Každou zakázku vedu osobně, od návrhu po předání. Řekněte, co potřebujete, rád poradím i s výběrem.',
    'hero_link' => 'portfolio',
    'hero_image' => 'https://images.unsplash.com/photo-1482731215275-a1f151646268?auto=format&fit=crop&w=1920&q=80',
    'contact_email' => 'jan.novak@email.cz',
    'contact_phone' => '+420 777 123 456',
    'contact_address' => 'Dílenská 12, 100 00 Praha',
    'social_facebook' => 'https://facebook.com',
    'social_instagram' => 'https://instagram.com',
]);
echo "Nastavení webu uloženo.\n";

// --- Služby (jen pokud jsou prázdné) ---
$services = new ServiceRepository();
if ($services->all() === []) {
    foreach ([
        ['title' => 'Rekonstrukce na klíč', 'description' => 'Kompletní přestavba bytu od bourání po finální úklid.', 'icon' => 'hammer', 'sort_order' => 1],
        ['title' => 'Malování a povrchy', 'description' => 'Malby, štuky, obklady a podlahy.', 'icon' => 'paintbrush', 'sort_order' => 2],
        ['title' => 'Koupelny a jádra', 'description' => 'Rekonstrukce koupelen a bytových jader.', 'icon' => 'droplet', 'sort_order' => 3],
        ['title' => 'Konzultace', 'description' => 'Nezávazně poradím s postupem i rozpočtem.', 'icon' => 'message-circle', 'sort_order' => 4],
    ] as $service) {
        $services->create($service);
    }
    echo "Služby vytvořeny.\n";
}

// --- Portfolio ---
$portfolio = new PortfolioRepository();
if ($portfolio->all() === []) {
    foreach ([
        ['title' => 'Rekonstrukce obývacího pokoje', 'description' => 'Z holé místnosti obývák k nastěhování.', 'image_path' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=800&q=80', 'image_before' => 'https://images.unsplash.com/photo-1768321901750-f7b96d774456?auto=format&fit=crop&w=800&q=80', 'sort_order' => 1],
        ['title' => 'Přestavba bytu', 'description' => 'Kompletní proměna od podlah po povrchy.', 'image_path' => 'https://images.unsplash.com/photo-1628744876497-eb30460be9f6?auto=format&fit=crop&w=800&q=80', 'image_before' => 'https://images.unsplash.com/photo-1757742690834-aa581b9f53b2?auto=format&fit=crop&w=800&q=80', 'sort_order' => 2],
        ['title' => 'Renovace pokoje', 'description' => 'Nové povrchy a světlejší prostor.', 'image_path' => 'https://images.unsplash.com/photo-1616137422495-1e9e46e2aa77?auto=format&fit=crop&w=800&q=80', 'image_before' => 'https://images.unsplash.com/photo-1776821456681-305bdcd81096?auto=format&fit=crop&w=800&q=80', 'sort_order' => 3],
    ] as $item) {
        $portfolio->create($item);
    }
    echo "Portfolio vytvořeno.\n";
}

// --- Recenze ---
$testimonials = new TestimonialRepository();
if ($testimonials->all() === []) {
    foreach ([
        ['author' => 'Petra K.', 'role' => 'Praha, rekonstrukce bytu', 'text' => 'Domluva bez problémů, práce odvedená pečlivě a v termínu. Můžu jen doporučit.', 'sort_order' => 1],
        ['author' => 'Martin H.', 'role' => 'Kladno', 'text' => 'Poradil, navrhl řešení a udělal to líp, než jsem čekal. Příště zase u něj.', 'sort_order' => 2],
        ['author' => 'Jana S.', 'role' => 'Beroun', 'text' => 'Oceňuji hlavně osobní přístup a čistou práci. Vše proběhlo hladce.', 'sort_order' => 3],
    ] as $testimonial) {
        $testimonials->create($testimonial);
    }
    echo "Recenze vytvořeny.\n";
}

echo "Seed dokončen.\n";
