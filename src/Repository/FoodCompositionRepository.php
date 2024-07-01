<?php

namespace App\Repository;

use App\Entity\FoodComposition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FoodCompositionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FoodComposition::class);
    }

    public function findByPartialName(string $name): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.name LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
}
