<?php

namespace App\Repository;

use App\Entity\Photo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Photo>
 */
class PhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Photo::class);
    }

    public function findAllPublicPhotos()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.is_public = true')
            ->orderBy('p.uploaded_at', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
