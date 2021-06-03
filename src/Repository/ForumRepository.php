<?php

namespace App\Repository;

use App\Entity\Forum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Forum|null find($id, $lockMode = null, $lockVersion = null)
 * @method Forum|null findOneBy(array $criteria, array $orderBy = null)
 * @method Forum[]    findAll()
 * @method Forum[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Forum::class);
    }

    public function findAllForums($limit, $offset, $userId = 0) {
    	$where = '';
    	if (!empty(intval($userId))) {
    		$where = 'WHERE fr.user_id = '.intval($userId);
		}
		$sql = '
			SELECT
				fr.id,
			    fr.user_id as userId,
			    fr.message,
			    fr.post_date as postDate,
			   	fr.title,
			    COUNT(msg.id) as messageCount
			FROM
				forum fr
			LEFT JOIN 
				messages msg ON fr.id = msg.forum_id
			'.$where.'
			GROUP BY fr.id
			ORDER BY fr.id DESC				
			LIMIT '.(int)$limit.'  OFFSET '.(int)$offset.'
		';

		$conn = $this->getEntityManager()
			->getConnection();
		$stmt = $conn->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAllAssociative();
	}

	public function searchForum($title, $limit, $offset) {
		$sql = '
			SELECT
				fr.id,
			    fr.user_id as userId,
			    fr.message,
			    fr.post_date as postDate,
			   	fr.title,
			    COUNT(msg.id) as messageCount
			FROM
				forum fr
			LEFT JOIN 
				messages msg ON fr.id = msg.forum_id
			WHERE fr.title LIKE :title
			GROUP BY fr.id
			ORDER BY fr.id DESC				
			LIMIT '.(int)$limit.'  OFFSET '.(int)$offset.'
		';

		$conn = $this->getEntityManager()
			->getConnection();
		$stmt = $conn->prepare($sql);
		$title = '%'.$title.'%';
		$stmt->bindParam(':title', $title);

		$stmt->execute();
		return $stmt->fetchAllAssociative();
	}
}
