<?php

namespace App\Repository;

use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Person|null find($id, $lockMode = null, $lockVersion = null)
 * @method Person|null findOneBy(array $criteria, array $orderBy = null)
 * @method Person[]    findAll()
 * @method Person[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

	public function findOneByPersonId($id) {
		/**
		 * @var Person $person
		 */
    	$person = parent::findOneBy(['personId' => intval($id)]);
    	if (empty($person)) {
    		return false;
		}

		$sql = '
			SELECT
				UNCOMPRESS(movies) as movies
			FROM
				person
			WHERE 
				person_id = '.intval($id).'
		';

		$conn = $this->getEntityManager()
			->getConnection();
		$stmt = $conn->prepare($sql);
		$stmt->execute();
		$movies = $stmt->fetchAllAssociative();
		if (empty($movies)) {
			return ['err'];
		}
		$person->setMovies(json_decode($movies[0]['movies'], true));
		return $person;
	}

	/**
	 * @param int $id
	 * @param array $movies
	 * @return Person|false
	 * @throws \Doctrine\DBAL\Driver\Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	public function savePersonMovies($id, $movies) {
		/**
		 * @var Person $person
		 */
		$person = parent::findOneBy(['personId' => intval($id)]);
		if (empty($person)) {
			return false;
		}

		$sql = '
			UPDATE person
			SET
				movies = COMPRESS(\''.json_encode($movies, JSON_HEX_QUOT   | JSON_HEX_APOS).'\')
			WHERE 
				person_id = '.intval($id).'
		';

		$conn = $this->getEntityManager()
			->getConnection();
		$stmt = $conn->prepare($sql);
		return $stmt->execute();
	}
}
