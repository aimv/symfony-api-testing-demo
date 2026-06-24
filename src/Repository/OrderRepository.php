<?php

namespace App\Repository;

use App\Dto\CompanyOrderDto;
use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * @return CompanyOrderDto[]
     */
    public function findCompanyOrdersDto(Uuid $companyId): array
    {
        return $this->createQueryBuilder('o')
            ->select(sprintf(
                'NEW %s(o.id, o.amountRub, o.rateEur, o.amountEur, c.name, u.email)',
                CompanyOrderDto::class
            ))
            ->innerJoin('o.company', 'c')
            ->innerJoin('c.owner', 'u')
            ->andWhere('o.company = :companyId')
            ->setParameter('companyId', $companyId)
            ->orderBy('o.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
