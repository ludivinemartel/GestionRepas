<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class UserFiltreService
{
    private $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function getEntitiesForCurrentUser(string $entityClass, string $userProperty = 'user')
    {
        $user = $this->security->getUser();

        return $this->entityManager->getRepository($entityClass)
            ->createQueryBuilder('e')
            ->andWhere(sprintf('e.%s = :user', $userProperty))
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function getQueryBuilderForCurrentUser(string $entityClass, string $userProperty = 'user')
    {
        $user = $this->security->getUser();

        return $this->entityManager->getRepository($entityClass)
            ->createQueryBuilder('e')
            ->andWhere(sprintf('e.%s = :user', $userProperty))
            ->setParameter('user', $user);
    }
}
