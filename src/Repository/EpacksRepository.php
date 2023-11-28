<?php

namespace App\Repository;

use App\Entity\Epacks;
use App\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method Epacks|null find($id, $lockMode = null, $lockVersion = null)
 * @method Epacks|null findOneBy(array $criteria, array $orderBy = null)
 * @method Epacks[]    findAll()
 * @method Epacks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EpacksRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Epacks::class);
    }


    private function findBySearch($qb, $search) :QueryBuilder
    {
        $qb->setParameter('searchTerm', '%'.mb_strtolower($search).'%')
        ->setParameter('searchTermUp', '%'.mb_strtoupper($search).'%')
        ->setParameter('searchBrand', '%'.(binary)$search.'%')
            ->andWhere('e.file LIKE :searchTerm OR LOWER(e.productname) LIKE :searchTerm OR UPPER(e.productname) LIKE :searchTermUp OR LOWER(e.mpn) LIKE :searchTerm OR LOWER(e.ean) LIKE :searchTerm OR e.brandname LIKE :searchBrand')
        ;
        return $qb;
    }


    private function findEpackageByUser($userId, $epackageId)
    {
        $sql = 'SELECT e.* FROM epacks e 
                LEFT JOIN user_statistic_group ug ON e.user_id = ug.user_id
                LEFT JOIN user_statistic_group ug2 ON ug.statistic_group_id = ug2.statistic_group_id
                LEFT JOIN epacks e2 ON e2.user_id = ug2.user_id
                WHERE (ug2.user_id = :user OR e.user_id = :user) AND e.file = :epackage';

        $connection = $this->_em->getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->bindValue('user', $userId);
        $stmt->bindValue('epackage', $epackageId);
        $stmt->execute();

        return $stmt->fetch();
    }


    private function findEpackageByAdmin($epackageId)
    {
        $sql = 'SELECT e.* FROM epacks e 
                WHERE e.file = :epackage';
        $connection = $this->_em->getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->bindValue('epackage', $epackageId);
        $stmt->execute();

        return $stmt->fetch();
    }


    public function findOneEpackage($user_id, $epackage_id, $user_roles)
    {
        $epackage = (in_array('ROLE_ADMIN', $user_roles)) ? $this->findEpackageByAdmin($epackage_id) : $this->findEpackageByUser($user_id, $epackage_id);
        return $epackage;
    }


    public function findEpackageByNumber($epackageId): ?Epacks
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.file = :val')
            ->setParameter('val', $epackageId)
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function findByAdmin(int $page = 1, int $limit = 10, string $search = null): Paginator
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.file packageId', 'e.productname name', 'user.username creater', 'updating.username updater', 'e.mpn', 'e.ean', 'e.status', 'e.isUpdated is_updated', 'e.createdAt date_created', 'e.updatedAt date_updated', 'e.brandname')
            ->leftJoin('e.user', 'user')
            ->leftJoin('e.updater', 'updating')
            ->orderBy('e.updatedAt', 'DESC')
        ;
        if($search)
        {
            $qb = $this->findBySearch($qb, $search);
        }

        return (new Paginator($qb, $limit))->paginate($page);
    }

    // /**
    //  * @return Epacks[] Returns an array of Epacks objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Epacks
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}