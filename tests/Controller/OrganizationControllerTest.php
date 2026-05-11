<?php

namespace App\Tests\Controller;

use App\Service\OrganizationService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrganizationControllerTest extends WebTestCase
{
    public function test_it_returns_400_for_invalid_inn(): void
    {
        $client = static::createClient();

        $organizationService = $this->createMock(OrganizationService::class);

        $organizationService
            ->method('validateInn')
            ->willReturn(false);

        static::getContainer()->set(
            OrganizationService::class,
            $organizationService
        );

        $client->request('GET', '/api/organizations/inn/123');

        $this->assertResponseStatusCodeSame(400);

        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'message' => 'Неправильный формат. ИНН должен состоять из 10 или 12 цифр.'
            ]),
            $client->getResponse()->getContent()
        );
    }

    public function test_it_returns_organizations(): void
    {
        $client = static::createClient();

        $organizationService = $this->createMock(OrganizationService::class);

        $organizationService
            ->method('validateInn')
            ->willReturn(true);

        $organizationService
            ->method('getOrganizationsByInn')
            ->willReturn([
                [
                    'name' => 'ООО Тестовая организация',
                    'ogrn' => '123456',
                ]
            ]);

        static::getContainer()->set(
            OrganizationService::class,
            $organizationService
        );

        $client->request(
            'GET',
            '/api/organizations/inn/1234567890'
        );

        $this->assertResponseIsSuccessful();

        $response = json_decode(
            $client->getResponse()->getContent(),
            true
        );

        $this->assertArrayHasKey('data', $response);

        $this->assertEquals(
            'ООО Тестовая организация',
            $response['data'][0]['name']
        );
    }

    public function test_it_returns_500_on_exception(): void
    {
        $client = static::createClient();

        $organizationService = $this->createMock(OrganizationService::class);

        $organizationService
            ->method('validateInn')
            ->willReturn(true);

        $organizationService
            ->method('getOrganizationsByInn')
            ->willThrowException(new \Exception());

        static::getContainer()->set(
            OrganizationService::class,
            $organizationService
        );

        $client->request(
            'GET',
            '/api/organizations/inn/1234567890'
        );

        $this->assertResponseStatusCodeSame(500);

        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'message' => 'Ошибка сервера.'
            ]),
            $client->getResponse()->getContent()
        );
    }
}
