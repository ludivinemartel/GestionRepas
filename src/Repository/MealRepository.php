<?php

namespace App\Repository;

use App\Entity\Meal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MealRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Meal::class);
    }

    /**
     * @param User $user
     * @return Meal[]
     */
    public function findByUser($user): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findByUserAndCategory($user, $category): array
    {
        return $this->createQueryBuilder('m')
            ->innerJoin('m.Categories', 'c')
            ->andWhere('m.user = :user')
            ->andWhere('c.id = :category')
            ->setParameter('user', $user)
            ->setParameter('category', $category)
            ->getQuery()
            ->getResult();
    }
}
