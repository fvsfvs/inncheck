<?php

namespace App\Controller;

use App\Service\OrganizationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

#[Route('/api/organizations')]
final class OrganizationController extends AbstractController
{
    public function __construct(
        private readonly OrganizationService $organizationService,
    ) {}

    #[Route('/inn/{inn}', name: 'app_showByInn', methods: ['GET'])]
    public function showByInn(string $inn): JsonResponse
    {
        try {
            $response = [];
            if (!$this->organizationService->validateInn($inn)) {
                return $this->json(
                    ['message' => 'Неправильный формат. ИНН должен состоять из 10 или 12 цифр.'],
                    400
                );
            }

            $response['data'] = $this->organizationService->getOrganizationsByInn($inn);

            return $this->json($response);
        } catch (Throwable $e) {
            return $this->json(
                ['message' => 'Ошибка сервера.'],
                500
            );
        }
    }
}
