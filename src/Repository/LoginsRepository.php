<?php

namespace App\Repository;

use App\Entity\Logins;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Logins|null find($id, $lockMode = null, $lockVersion = null)
 * @method Logins|null findOneBy(array $criteria, array $orderBy = null)
 * @method Logins[]    findAll()
 * @method Logins[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LoginsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Logins::class);
    }

    // /**
    //  * @return Logins[] Returns an array of Logins objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Logins
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
