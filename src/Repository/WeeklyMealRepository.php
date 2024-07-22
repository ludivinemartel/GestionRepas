<?php

namespace App\Repository;

use App\Entity\WeeklyMeal;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WeeklyMeal>
 */
class WeeklyMealRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeeklyMeal::class);
    }

    public function findDailyMenu(User $user, \DateTime $date)
    {
        return $this->createQueryBuilder('w')
            ->where('w.user = :user')
            ->andWhere(':date BETWEEN w.weekStart AND w.weekEnd')
            ->setParameter('user', $user)
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery()
            ->getOneOrNullResult();
    }
}
