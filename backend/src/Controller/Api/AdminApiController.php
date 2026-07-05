<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Core\Request;
use App\Core\Response;
use App\Repository\InquiryRepository;
use App\Repository\PortfolioRepository;
use App\Repository\ServiceRepository;
use App\Repository\SettingRepository;
use App\Support\Uploader;
use OpenApi\Attributes as OA;

/**
 * Chráněné admin endpointy (Bearer JWT).
 */
#[OA\SecurityScheme(securityScheme: 'bearerAuth', type: 'http', scheme: 'bearer', bearerFormat: 'JWT')]
final class AdminApiController
{
    // --- Služby ---

    #[OA\Post(path: '/api/admin/services', summary: 'Vytvořit službu', tags: ['Admin'], security: [['bearerAuth' => []]], responses: [new OA\Response(response: 201, description: 'Vytvořeno')])]
    public function createService(Request $request): void
    {
        $id = (new ServiceRepository())->create($request->all());
        Response::json(['id' => $id], 201);
    }

    #[OA\Put(path: '/api/admin/services/{id}', summary: 'Upravit službu', tags: ['Admin'], security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function updateService(Request $request): void
    {
        (new ServiceRepository())->update((int) $request->param('id'), $request->all());
        Response::json(['ok' => true]);
    }

    #[OA\Delete(path: '/api/admin/services/{id}', summary: 'Smazat službu', tags: ['Admin'], security: [['bearerAuth' => []]], responses: [new OA\Response(response: 204, description: 'Smazáno')])]
    public function deleteService(Request $request): void
    {
        (new ServiceRepository())->delete((int) $request->param('id'));
        Response::noContent();
    }

    // --- Portfolio ---

    #[OA\Post(path: '/api/admin/portfolio', summary: 'Přidat portfolio položku (multipart s obrázkem)', tags: ['Admin'], security: [['bearerAuth' => []]], responses: [new OA\Response(response: 201, description: 'Vytvořeno')])]
    public function createPortfolio(Request $request): void
    {
        $data = $request->all();
        if (!empty($_FILES['image']['tmp_name'])) {
            $data['image_path'] = Uploader::store($_FILES['image']);
        }
        $id = (new PortfolioRepository())->create($data);
        Response::json(['id' => $id], 201);
    }

    #[OA\Delete(path: '/api/admin/portfolio/{id}', summary: 'Smazat portfolio položku', tags: ['Admin'], security: [['bearerAuth' => []]], responses: [new OA\Response(response: 204, description: 'Smazáno')])]
    public function deletePortfolio(Request $request): void
    {
        (new PortfolioRepository())->delete((int) $request->param('id'));
        Response::noContent();
    }

    // --- Nastavení ---

    #[OA\Put(path: '/api/admin/settings', summary: 'Uložit nastavení webu', tags: ['Admin'], security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function updateSettings(Request $request): void
    {
        (new SettingRepository())->setMany($request->all());
        Response::json(['ok' => true]);
    }

    // --- Poptávky ---

    #[OA\Get(path: '/api/admin/inquiries', summary: 'Seznam poptávek', tags: ['Admin'], security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Pole poptávek')])]
    public function inquiries(Request $request): void
    {
        Response::json((new InquiryRepository())->all());
    }

    #[OA\Delete(path: '/api/admin/inquiries/{id}', summary: 'Smazat poptávku', tags: ['Admin'], security: [['bearerAuth' => []]], responses: [new OA\Response(response: 204, description: 'Smazáno')])]
    public function deleteInquiry(Request $request): void
    {
        (new InquiryRepository())->delete((int) $request->param('id'));
        Response::noContent();
    }
}
