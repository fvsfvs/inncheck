<?php

namespace App\Repository;

use App\Entity\InnCheck;
use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Organization>
 */
class OrganizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }

    public function findDeletedByInnCheck(InnCheck $innCheck): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.innCheck = :innCheck')
            ->andWhere('o.deletedAt IS NOT NULL')
            ->setParameter('innCheck', $innCheck)
            ->getQuery()
            ->getResult();
    }
}
