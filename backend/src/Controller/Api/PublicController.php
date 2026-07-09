<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Repository\InquiryRepository;
use App\Repository\PortfolioRepository;
use App\Repository\ServiceRepository;
use App\Repository\SettingRepository;
use App\Repository\VisitRepository;
use App\Support\Clock;
use App\Support\RateLimiter;
use App\Support\UserAgent;
use App\Support\Validator;
use OpenApi\Attributes as OA;

#[OA\Info(version: '1.0.0', title: 'Firemní web API', description: 'Veřejné a admin rozhraní pro firemní web.')]
#[OA\Server(url: '/', description: 'Tento server')]
final class PublicController
{
    #[OA\Get(
        path: '/api/settings',
        summary: 'Nastavení webu (title, slogan, kontakt, hero obrázek)',
        tags: ['Veřejné'],
        responses: [new OA\Response(response: 200, description: 'Key/value nastavení webu')]
    )]
    public function settings(Request $request): void
    {
        Response::json((new SettingRepository())->all());
    }

    #[OA\Get(
        path: '/api/services',
        summary: 'Seznam služeb (cards)',
        tags: ['Veřejné'],
        responses: [new OA\Response(response: 200, description: 'Pole služeb')]
    )]
    public function services(Request $request): void
    {
        Response::json((new ServiceRepository())->all());
    }

    #[OA\Get(
        path: '/api/portfolio',
        summary: 'Ukázky práce (fotky)',
        tags: ['Veřejné'],
        responses: [new OA\Response(response: 200, description: 'Pole portfolio položek')]
    )]
    public function portfolio(Request $request): void
    {
        Response::json((new PortfolioRepository())->all());
    }

    #[OA\Post(
        path: '/api/inquiries',
        summary: 'Odeslání nezávazné poptávky',
        tags: ['Veřejné'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'phone', type: 'string'),
                    new OA\Property(property: 'message', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Poptávka přijata'),
            new OA\Response(response: 422, description: 'Chyba validace'),
        ]
    )]
    public function createInquiry(Request $request): void
    {
        $data = $request->all();

        // Anti-spam 1: honeypot - skryté pole "website" musí zůstat prázdné.
        // Anti-spam 2: time-trap - formulář odeslaný dřív než za 2 s je nejspíš bot.
        // U obou se tváříme úspěšně, ale nic neuložíme (neprozrazujeme detekci).
        $elapsed = (float) ($data['elapsed'] ?? 0);
        if (trim((string) ($data['website'] ?? '')) !== '' || ($elapsed > 0 && $elapsed < 2)) {
            Response::json(['message' => 'Děkujeme, ozveme se vám.'], 201);

            return;
        }

        // Anti-spam 3: rate-limit podle IP (max 5 poptávek / 10 min).
        $limiter = new RateLimiter(Config::basePath('/storage/ratelimit'));
        if (!$limiter->allow('inquiry:' . $request->clientIp(), 5, 600)) {
            Response::error('Příliš mnoho poptávek z této adresy. Zkuste to prosím za chvíli.', 429);

            return;
        }

        $validator = new Validator();
        if (!$validator->validate($data, [
            'name' => 'required|max:120',
            'email' => 'required|email|max:180',
            'phone' => 'max:40',
            'message' => 'max:2000',
        ])) {
            Response::error('Neplatná data', 422, ['fields' => $validator->errors()]);

            return;
        }

        $id = (new InquiryRepository())->create($data);
        Response::json(['id' => $id, 'message' => 'Děkujeme, ozveme se vám.'], 201);
    }

    #[OA\Post(
        path: '/api/hit',
        summary: 'Anonymní záznam návštěvy (počítadlo návštěvnosti)',
        tags: ['Veřejné'],
        responses: [new OA\Response(response: 204, description: 'Zaznamenáno')]
    )]
    public function hit(Request $request): void
    {
        $day = Clock::today();

        // Anonymní otisk návštěvníka: hash z IP + user-agent, solený tajným klíčem a dnem.
        // Sůl obsahuje den → hash se denně mění a IP se nikde neukládá v čitelné podobě.
        $salt = (string) Config::get('JWT_SECRET', 'salt');
        $hash = hash('sha256', $request->clientIp() . '|' . $request->userAgent() . '|' . $day . '|' . $salt);

        // Odvozené štítky z hlaviček (žádné syrové IP/UA se neukládá).
        $agent = UserAgent::parse($request->userAgent());

        (new VisitRepository())->record([
            'day' => $day,
            'visitor_hash' => $hash,
            'referrer_host' => self::hostFromReferrer((string) $request->input('ref', '')),
            'device' => $agent['device'],
            'browser' => $agent['browser'],
            'os' => $agent['os'],
            'language' => self::primaryLanguage($request->header('Accept-Language')),
        ]);
        Response::noContent();
    }

    /** Host zdroje (externí referrer). Vlastní doména i prázdno → „(přímý)". */
    private static function hostFromReferrer(string $referrer): string
    {
        $host = strtolower((string) (parse_url($referrer, PHP_URL_HOST) ?: ''));
        $host = preg_replace('/^www\./', '', $host) ?? $host;
        if ($host === '' || $host === ($_SERVER['HTTP_HOST'] ?? '')) {
            return '(přímý)';
        }

        return mb_substr($host, 0, 120);
    }

    /** První jazyk z Accept-Language (např. „cs-CZ,en;q=0.8" → „cs"). */
    private static function primaryLanguage(string $acceptLanguage): string
    {
        $first = trim(explode(',', $acceptLanguage)[0] ?? '');
        $first = trim(explode(';', $first)[0] ?? '');
        $primary = strtolower(trim(explode('-', $first)[0] ?? ''));

        return preg_match('/^[a-z]{2,3}$/', $primary) ? $primary : '';
    }
}
