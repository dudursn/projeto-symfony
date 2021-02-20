<?php

namespace App\Repository;

use App\Entity\Eleicoes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Eleicoes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Eleicoes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Eleicoes[]    findAll()
 * @method Eleicoes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EleicoesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Eleicoes::class);
    }

    // /**
    //  * @return Eleicoes[] Returns an array of Eleicoes objects
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
    public function findOneBySomeField($value): ?Eleicoes
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
