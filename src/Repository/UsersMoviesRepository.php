<?php

namespace App\Repository;

use App\Entity\UsersMovies;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UsersMovies|null find($id, $lockMode = null, $lockVersion = null)
 * @method UsersMovies|null findOneBy(array $criteria, array $orderBy = null)
 * @method UsersMovies[]    findAll()
 * @method UsersMovies[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsersMoviesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsersMovies::class);
    }
}
