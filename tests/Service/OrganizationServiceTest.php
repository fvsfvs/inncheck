<?php

namespace App\Tests\Service;

use App\Entity\InnCheck;
use App\Entity\Organization;
use App\Repository\InnCheckRepository;
use App\Repository\OrganizationRepository;
use App\Service\DaDataService;
use App\Service\OrganizationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

class OrganizationServiceTest extends TestCase
{
    private function createService(
        InnCheckRepository $innRepo,
        OrganizationRepository $orgRepo,
        DaDataService $daData,
        EntityManagerInterface $em,
        int $ttl = 10
    ): OrganizationService {
        return new OrganizationService(
            $innRepo,
            $orgRepo,
            $ttl,
            $daData,
            $em
        );
    }

    private function fakeDaDataResponse(): ResponseInterface
    {
        $response = $this->createMock(ResponseInterface::class);

        $response->method('toArray')->willReturn([
            'suggestions' => [
                [
                    'data' => [
                        'name' => ['short_with_opf' => 'Тестовая организация'],
                        'state' => ['status' => 'ACTIVE'],
                        'okved' => '62.01',
                        'okved_type' => '2014',
                        'ogrn' => '123456',
                    ]
                ]
            ]
        ]);

        return $response;
    }

    private function mockInnCheck(?InnCheck $innCheck = null): InnCheckRepository
    {
        $repo = $this->createMock(InnCheckRepository::class);

        $repo->method('findOneBy')
            ->willReturn($innCheck);

        return $repo;
    }

    private function mockOrgRepo(array $organizations = []): OrganizationRepository
    {
        $repo = $this->createMock(OrganizationRepository::class);

        $repo->method('findBy')
            ->willReturn($organizations);

        return $repo;
    }

    private function mockEntityManager(): EntityManagerInterface
    {
        $em = $this->createMock(EntityManagerInterface::class);

        $em->expects($this->any())
            ->method('persist');

        $em->expects($this->any())
            ->method('flush');

        return $em;
    }

    public function test_it_creates_organizations_from_dadata(): void
    {
        $innCheck = new InnCheck();

        $innCheck->setInn('1234567890')->setCheckedAt(new \DateTimeImmutable());

        $createdOrg = new Organization();

        $createdOrg
            ->setName('Тестовая организация')
            ->setInnCheck($innCheck)
            ->setOgrn('123456')
            ->setOkved('62.01')
            ->setOkvedType('2014')
            ->setIsActive(true);

        $innRepo = $this->mockInnCheck($innCheck);

        $orgRepo = $this->createMock(OrganizationRepository::class);

        $orgRepo->method('findBy')
            ->willReturnOnConsecutiveCalls(
                [],
                [$createdOrg]
            );

        $daData = $this->createMock(DaDataService::class);

        $daData->method('getOrganizationsByInn')
            ->willReturn($this->fakeDaDataResponse());

        $em = $this->mockEntityManager();

        $service = $this->createService(
            $innRepo,
            $orgRepo,
            $daData,
            $em,
            0
        );

        $result = $service->getOrganizationsByInn('1234567890');

        $this->assertCount(1, $result);

        $this->assertEquals(
            'Тестовая организация',
            $result[0]->name
        );
    }

    public function test_it_returns_cached_data_when_not_expired(): void
    {
        $innCheck = new InnCheck();
        $innCheck->setInn('1234567890')->setCheckedAt(new \DateTimeImmutable());

        $org = new Organization();

        $org
            ->setName('Тестовая организация')
            ->setInnCheck($innCheck)
            ->setOgrn('123456')
            ->setOkved('62.01')
            ->setOkvedType('2014')
            ->setIsActive(true);

        $innRepo = $this->mockInnCheck($innCheck);
        $orgRepo = $this->mockOrgRepo([$org]);

        $daData = $this->createMock(DaDataService::class);

        $em = $this->mockEntityManager();

        $service = $this->createService(
            $innRepo,
            $orgRepo,
            $daData,
            $em
        );

        $result = $service->getOrganizationsByInn('1234567890');

        $this->assertCount(1, $result);

        $this->assertEquals('Тестовая организация', $result[0]->name);

        $this->assertEquals('123456', $result[0]->ogrn);

        $this->assertEquals('62.01', $result[0]->okved);

        $this->assertTrue($result[0]->isActive);
    }
}
