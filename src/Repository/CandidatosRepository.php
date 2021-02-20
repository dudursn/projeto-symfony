<?php

namespace App\Repository;

use App\Entity\Candidatos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Candidatos|null find($id, $lockMode = null, $lockVersion = null)
 * @method Candidatos|null findOneBy(array $criteria, array $orderBy = null)
 * @method Candidatos[]    findAll()
 * @method Candidatos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CandidatosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidatos::class);
    }

    // /**
    //  * @return Candidatos[] Returns an array of Candidatos objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Candidatos
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
