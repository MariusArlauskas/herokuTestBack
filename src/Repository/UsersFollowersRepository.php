<?php

namespace App\Repository;

use App\Entity\UsersFollowers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UsersFollowers|null find($id, $lockMode = null, $lockVersion = null)
 * @method UsersFollowers|null findOneBy(array $criteria, array $orderBy = null)
 * @method UsersFollowers[]    findAll()
 * @method UsersFollowers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsersFollowersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsersFollowers::class);
    }
}
