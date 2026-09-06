<?php

namespace App\Repository;

use App\Entity\PageContent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PageContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageContent::class);
    }

    public function findBySection(string $section): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.section = :section')
            ->setParameter('section', $section)
            ->getQuery()
            ->getResult();
    }

    public function findOneByKey(string $key): ?PageContent
    {
        return $this->findOneBy(['key' => $key]);
    }
}
