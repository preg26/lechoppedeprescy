<?php

namespace App\Repository;

use App\Entity\Creation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CreationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Creation::class);
    }

    public function findLatestPublished(int $limit = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.isPublished = :published')
            ->setParameter('published', true)
            ->orderBy('c.createdAt', 'DESC');
        
        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }
        
        return $qb->getQuery()->getResult();
    }

    public function findPublishedByCategory(int $categoryId, int $limit = 9, int $offset = 0): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.isPublished = :published')
            ->andWhere('c.category = :category')
            ->setParameter('published', true)
            ->setParameter('category', $categoryId)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    public function countPublishedByCategory(int $categoryId): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.isPublished = :published')
            ->andWhere('c.category = :category')
            ->setParameter('published', true)
            ->setParameter('category', $categoryId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
