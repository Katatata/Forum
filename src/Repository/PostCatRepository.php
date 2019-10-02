<?php

namespace App\Repository;

use App\Entity\PostCat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PostCat|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostCat|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostCat[]    findAll()
 * @method PostCat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostCatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostCat::class);
    }

    // /**
    //  * @return PostCat[] Returns an array of PostCat objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PostCat
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
