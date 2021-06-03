<?php

namespace App\Repository;

use App\Entity\Messages;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\DBALException;

/**
 * @method Messages|null find($id, $lockMode = null, $lockVersion = null)
 * @method Messages|null findOneBy(array $criteria, array $orderBy = null)
 * @method Messages[]    findAll()
 * @method Messages[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessagesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Messages::class);
    }

	/**
	 * @param int $limit
	 * @param int $offset
	 * @param array $users
	 * @return array
	 * @throws DBALException
	 */
	public function findMessagesSortedByDate($limit, $offset, $users = []) {
		$and = '';
		if (!empty($users)) {
			$and = 'AND u.id IN ('.implode(',', $users).')';
		}
		$sql = '
			SELECT
				msg.id,
				msg.movie_id as movieId,
				msg.message,
			    msg.post_date as postDate,
			    msg.parent_id as parentId,
				msg.shared_api_id as sharedApiId,
			    u.name as userName,
			    u.id as userId,
			   	u.profile_picture as userProfilePicture,
				m.title,
				m.poster_path AS posterPath
			FROM
				messages msg
			LEFT JOIN
				users u ON
				msg.user_id = u.id
			LEFT JOIN
				movies m ON 
					m.movie_id = msg.movie_id
			WHERE 
				msg.parent_id IS NULL 
				AND msg.forum_id IS NULL '.$and.'
			ORDER BY
				post_date DESC
			LIMIT '.(int)$limit.' OFFSET '.(int)$offset.'
		';

		$conn = $this->getEntityManager()
			->getConnection();
		$stmt = $conn->prepare($sql);
		$stmt->execute();

		return $stmt->fetchAllAssociative();
	}

	/**
	 * @param int $limit
	 * @param int $offset
	 * @param int $user
	 * @return array
	 * @throws DBALException
	 */
	public function findMovieMessagesSortedByDate($limit, $offset, $movie = 0) {
		$and = '';
		if (!empty($movie)) {
			$and = 'AND msg.movie_id = '.intval($movie);
		}
		$sql = '
			SELECT
				msg.id,
				msg.message,
			    msg.post_date as postDate,
			    msg.parent_id as parentId,
			    msg.movie_id as movieId,
				msg.shared_api_id as sharedApiId,
			    u.name as userName,
			    u.id as userId,
			   	u.profile_picture as userProfilePicture
			FROM
				messages msg
			LEFT JOIN
				users u ON
					msg.user_id = u.id
			WHERE 
				msg.parent_id IS NULL
				AND msg.forum_id IS NULL '.$and.'
			ORDER BY
				msg.post_date DESC
			LIMIT '.(int)$limit.' OFFSET '.(int)$offset.'
		';

		$conn = $this->getEntityManager()
			->getConnection();
		$stmt = $conn->prepare($sql);
		$stmt->execute();

		return $stmt->fetchAllAssociative();
	}

	/**
	 * @param int $limit
	 * @param int $offset
	 * @param int $user
	 * @return array
	 * @throws DBALException
	 */
	public function findForumMessagesSortedByDate($limit, $offset, $forumId = 0) {
		if (empty(intval($forumId))) {
			return [];
		}
		$and = 'AND msg.forum_id = '.intval($forumId);
		$sql = '
			SELECT
				msg.id,
				msg.message,
			    msg.post_date as postDate,
			    msg.parent_id as parentId,
			    msg.movie_id as movieId,
				msg.shared_api_id as sharedApiId,
			    u.name as userName,
			    u.id as userId,
			   	u.profile_picture as userProfilePicture
			FROM
				messages msg
			LEFT JOIN
				users u ON
					msg.user_id = u.id
			WHERE 
				msg.parent_id IS NULL '.$and.'
			ORDER BY
				msg.id DESC
			LIMIT '.(int)$limit.' OFFSET '.(int)$offset.'
		';

		$conn = $this->getEntityManager()
			->getConnection();
		$stmt = $conn->prepare($sql);
		$stmt->execute();

		return $stmt->fetchAllAssociative();
	}

	/**
	 * @param array $parents
	 * @param int $user
	 * @return array
	 * @throws DBALException
	 */
	public function findMessagesCommentsSortedByDate($parents) {
		$sql = '
			SELECT
				msg.id,
				msg.message,
			    msg.post_date as postDate,
			    msg.parent_id as parentId,
				msg.shared_api_id as sharedApiId,
			    u.name as userName,
			    u.id as userId,
			   	u.profile_picture as userProfilePicture
			FROM
				messages msg
			LEFT JOIN
				users u ON
				msg.user_id = u.id
			WHERE 
				msg.parent_id IN ('.implode(',', $parents).')
			ORDER BY
				post_date DESC
		';

		$conn = $this->getEntityManager()
			->getConnection();
		$stmt = $conn->prepare($sql);
		$stmt->execute();

		return $stmt->fetchAllAssociative();
	}
}
