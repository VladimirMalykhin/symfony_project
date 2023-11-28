<?php

namespace App\Repository;

use App\Entity\Images;
use App\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method Images|null find($id, $lockMode = null, $lockVersion = null)
 * @method Images|null findOneBy(array $criteria, array $orderBy = null)
 * @method Images[]    findAll()
 * @method Images[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImagesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Images::class);
    }


    public function SetImage($epackage, $url, $size){
        $epack = new Images();
        $epack->setEpackage($epackage);
        $epack->setUrl($url);
        $epack->setSize($size);
        $em = $this->getDoctrine()->getManager();
        $em->persist($epack);
        $em->flush();
    }


    public function deleteImages($epackageId)
    {
        $sql = 'DELETE FROM `images`  
                WHERE `epackage_id` = :epackage';
        $connection = $this->_em->getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->bindValue('epackage', $epackageId);
        $stmt->execute();

    }


    public function getImages($epackageId)
    {
		$images = $this->createQueryBuilder('e')
            ->andWhere('e.epackageId = :epackage')
            ->setParameter('epackage', $epackageId)
            ->getQuery()
            ->getResult();

        return $images;
    }

    public function copyImages($epackageId, $nameNew)
    {
        $sql = 'SELECT `url`, `size` FROM `images`  
                WHERE `epackage_id` = :epackage';
        $connection = $this->_em->getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->bindValue('epackage', $epackageId);
        $stmt->execute();

        $images = $stmt->fetchAll();

        $requestAdding = '';

        foreach($images as $image){
            $urlNew = str_replace($epackageId, $nameNew, $image['url']);
            $requestAdding .= 'INSERT INTO `images` (`epackage_id`, `url`, `size`) VALUES("'. $nameNew . '", "'. $urlNew .'", "'. $image['size'].'");';
        }

        $sql = $requestAdding;
        $connection = $this->_em->getConnection();
        $stmt->execute();
    }
}