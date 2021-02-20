<?php

namespace App\Repository;

use App\Entity\Votos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Votos|null find($id, $lockMode = null, $lockVersion = null)
 * @method Votos|null findOneBy(array $criteria, array $orderBy = null)
 * @method Votos[]    findAll()
 * @method Votos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VotosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Votos::class);
    }

    // /**
    //  * @return Votos[] Returns an array of Votos objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Votos
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
