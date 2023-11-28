<?php

namespace App\Repository;

use App\Entity\Fonts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Fonts|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fonts|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fonts[]    findAll()
 * @method Fonts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FontsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fonts::class);
    }


    public function findFonts(array $fonts) :array
    {
        $font_used = [];
        $fonts_families = [];
        foreach ($fonts as $font) {
            if ($font['fontFamily'] != 'Arial' && $font['fontFamily'] != 'TimesNewRoman' && !in_array($font['fontFamily'], $fonts_families)) {
                $font_item = $this
                    ->findOneBy([
                        'font_family' => $font['fontFamily']]);
                $font_used[] = $font_item->getFolder();
                $fonts_families[] = $font['fontFamily'];
            }
        }
        return $font_used;
    }

    // /**
    //  * @return Fonts[] Returns an array of Fonts objects
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
    public function findOneBySomeField($value): ?Fonts
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
