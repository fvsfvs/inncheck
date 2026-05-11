<?php

namespace App\Service;

use App\Dto\OrganizationDto;
use App\Dto\OrganizationResponseDto;
use App\Entity\InnCheck;
use App\Entity\Organization;
use App\Repository\InnCheckRepository;
use App\Repository\OrganizationRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class OrganizationService
{
    public function __construct(
        private readonly InnCheckRepository $innCheckRepository,
        private readonly OrganizationRepository $organizationRepository,
        private readonly int $organizationTtlDays,
        private readonly DaDataService $daDataService,
        private readonly EntityManagerInterface $entityManager
    ) {}

    /**
     * @param string $inn
     * @return Organization[]
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getOrganizationsByInn(string $inn): array
    {
        $organizations = [];
        $innCheck = $this->innCheckRepository->findOneBy(['inn' => $inn]);

        if ($innCheck !== null) {
            $organizations = $this->organizationRepository->findBy(['innCheck' => $innCheck, 'deletedAt' => null]);
            if (!$innCheck->isExpired($this->organizationTtlDays)) {
                return $this->mapOrganizationsToOrganizationResponseDto($organizations);
            }
        }

        $responseData = $this->daDataService->getOrganizationsByInn($inn);
        $innCheck = $this->saveInnCheck($innCheck, $inn);
        $responseOrganizations = $this->getDataFromResponse($responseData->toArray(), $innCheck);

        if (empty($responseOrganizations) && empty($organizations)) {
            return [];
        }

        $deletedOrganizations = $this->organizationRepository->findDeletedByInnCheck($innCheck);

        // Обновление записанных организаций
        foreach (array_merge($organizations, $deletedOrganizations) as $organization) {
            $ogrn = $organization->getOgrn();

            if (isset($responseOrganizations[$ogrn])) {
                $this->updateOrganization($organization, $responseOrganizations[$ogrn]);
                unset($responseOrganizations[$ogrn]);
            } else {
                $organization->setDeletedAt(new DateTimeImmutable());
            }
        }

        foreach ($responseOrganizations as $responseOrganization) {
            $this->createOrganization($responseOrganization);
        }

        $this->entityManager->flush();
        $organizations = $this->organizationRepository->findBy(['innCheck' => $innCheck, 'deletedAt' => null]);

        return $this->mapOrganizationsToOrganizationResponseDto($organizations);
    }

    /**
     * @param Organization $organization
     * @param OrganizationDto $dto
     * @return void
     */
    private function updateOrganization(Organization $organization, OrganizationDto $dto): void
    {
        $organization
            ->setName($dto->name)
            ->setInnCheck($dto->innCheck)
            ->setOgrn($dto->ogrn)
            ->setOkved($dto->okved)
            ->setOkvedType($dto->okvedType)
            ->setDeletedAt(null)
            ->setIsActive($dto->isActive);
    }

    /**
     * @param OrganizationDto $dto
     * @return void
     */
    private function createOrganization(OrganizationDto $dto): void
    {
        $organization = new Organization();

        $organization
            ->setName($dto->name)
            ->setInnCheck($dto->innCheck)
            ->setOgrn($dto->ogrn)
            ->setOkved($dto->okved)
            ->setOkvedType($dto->okvedType)
            ->setIsActive($dto->isActive);

        $this->entityManager->persist($organization);
    }

    /**
     * @param InnCheck|null $innCheck
     * @param string $inn
     * @return InnCheck
     */
    private function saveInnCheck(?InnCheck $innCheck, string $inn): InnCheck
    {
        $innCheck ??= new InnCheck();
        $innCheck->setInn($inn)->setCheckedAt(new DateTimeImmutable());
        $this->entityManager->persist($innCheck);

        return $innCheck;
    }

    /**
     * @param array $suggestedOrganization
     * @return bool
     */
    private function validateOrganizationFields(array $suggestedOrganization): bool
    {
        if (!isset(
            $suggestedOrganization['data']['name']['short_with_opf'],
            $suggestedOrganization['data']['state']['status'],
            $suggestedOrganization['data']['okved'],
            $suggestedOrganization['data']['okved_type'],
            $suggestedOrganization['data']['ogrn'],
        )) {
            return false;
        }

        return
            is_string($suggestedOrganization['data']['name']['short_with_opf'])
            && is_string($suggestedOrganization['data']['state']['status'])
            && is_string($suggestedOrganization['data']['okved'])
            && is_string($suggestedOrganization['data']['okved_type'])
            && is_string($suggestedOrganization['data']['ogrn']);
    }

    /**
     * @param array $response
     * @param InnCheck $innCheck
     * @return OrganizationDto[]
     */
    private function getDataFromResponse(array $response, InnCheck $innCheck): array
    {
        if (empty($response['suggestions']) || !is_array($response['suggestions'])) {
            return [];
        }
        $organizations = [];

        foreach ($response['suggestions'] as $suggestedOrganization) {

            if (!$this->validateOrganizationFields($suggestedOrganization)) {
                continue;
            }

            $data = $suggestedOrganization['data'];
            $organizationData = $this->mapDataToOrganizationDto($data, $innCheck);

            $organizations[$organizationData->ogrn] = $organizationData;
        }

        return $organizations;
    }

    /**
     * @param array $data
     * @param InnCheck $innCheck
     * @return OrganizationDto
     */
    private function mapDataToOrganizationDto(array $data, InnCheck $innCheck): OrganizationDto
    {
        $organizationData = new OrganizationDto();

        $organizationData->innCheck = $innCheck;
        $organizationData->name = $data['name']['short_with_opf'];
        $organizationData->isActive = $data['state']['status'] === 'ACTIVE';
        $organizationData->okved = $data['okved'];
        $organizationData->okvedType = $data['okved_type'];
        $organizationData->ogrn = $data['ogrn'];

        return $organizationData;
    }

    /**
     * @param Organization[] $organizations
     * @return OrganizationResponseDto[]
     */
    private function mapOrganizationsToOrganizationResponseDto(array $organizations): array
    {
        $result = [];

        foreach ($organizations as $organization) {
            $dto = new OrganizationResponseDto();

            $dto->inn = $organization->getInnCheck()->getInn();
            $dto->name = $organization->getName();
            $dto->okved = $organization->getOkved();
            $dto->okvedType = $organization->getOkvedType();
            $dto->ogrn = $organization->getOgrn();
            $dto->isActive = $organization->isActive();

            $result[] = $dto;
        }

        return $result;
    }

    /**
     * @param string $inn
     * @return bool
     */
    public function validateInn(string $inn): bool
    {
        return !!preg_match('/^\d{10}$|^\d{12}$/', $inn);
    }


}
