<?php

namespace App\Repository;

use App\Entity\FormatFonts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FormatFonts|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormatFonts|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormatFonts[]    findAll()
 * @method FormatFonts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormatFontsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormatFonts::class);
    }

    // /**
    //  * @return FormatFonts[] Returns an array of FormatFonts objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FormatFonts
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
