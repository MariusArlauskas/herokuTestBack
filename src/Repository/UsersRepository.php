<?php

namespace App\Repository;

use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsersRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof Users) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

	/**	Main users data needed for showing in users list
	 * @param array $ids
	 */
    public function findAllMainDataByIds($ids) {
		$sql = '
			SELECT
				id,
				name,
			    profile_picture as profilePicture
			FROM
				users
			WHERE 
				id IN ('.implode(',', $ids).')
		';

		$conn = $this->getEntityManager()
			->getConnection();
		$stmt = $conn->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAllAssociative();
	}

	public function searchUser($name, $limit, $offset) {
		$sql = '
			SELECT
				id,
				name,
			    profile_picture as profilePicture
			FROM
				users
			WHERE name LIKE :title
			ORDER BY id DESC				
			LIMIT '.(int)$limit.'  OFFSET '.(int)$offset.'
		';

		$conn = $this->getEntityManager()
			->getConnection();
		$stmt = $conn->prepare($sql);
		$name = '%'.$name.'%';
		$stmt->bindParam(':title', $name);

		$stmt->execute();
		return $stmt->fetchAllAssociative();
	}
}
