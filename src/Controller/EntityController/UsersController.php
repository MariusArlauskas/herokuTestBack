<?php

namespace App\Controller\EntityController;


use App\Controller\InitSerializer;
use App\Entity\Users;
use App\Entity\UsersFollowers;
use App\Entity\UsersMovies;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UsersController
 * @package App\Controller
 * @Route("/users")
 */
class UsersController extends AbstractController
{
	protected $serializer;

	public function __construct()
	{
		$this->serializer = new InitSerializer();
	}

	/**
	 * @Route("", name="user_create", methods={"POST"})
	 * @param Request $request
	 * @return JsonResponse
	 */
    public function createAction(Request $request)
    {
        if ($this->isGranted("ROLE_USER") || $this->isGranted("ROLE_ADMIN")) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "Access denied!!");
        }

        // Assingning data from request and removing unnecessary symbols
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }
        // Check if none of the data is missing
        if (isset($parametersAsArray['password']) &&
            isset($parametersAsArray['name']) &&
            isset($parametersAsArray['birthDate']) &&
            isset($parametersAsArray['email'])) {
            $email = htmlspecialchars($parametersAsArray['email']);
            $name = htmlspecialchars(trim($parametersAsArray['name']));
            $birthDate = htmlspecialchars(trim($parametersAsArray['birthDate']));
            $password = htmlspecialchars(trim($parametersAsArray['password']));
        } else {
            return $this->serializer->response("Missing data!");
        }

        // Validation
        $repository = $this->getDoctrine()->getRepository(Users::class);
        $user = $repository->findBy(['email' => $email]);
        if ($user) {
            return $this->serializer->response('Email '.$email.' is already taken.', Response::HTTP_BAD_REQUEST);
        }

        // Creating user object
		$dateNow = new \DateTime();
        $user = new Users();
        $user->setEmail($email);
        $user->setName($name);
        $user->setRegisterDate($dateNow);
        $user->setBirthDate(\DateTime::createFromFormat('Y-m-d', $birthDate));
        $user->setRoles(['ROLE_USER']);
        $user->setDescription('Hello I\'m here from '.$dateNow->format('Y-m-d'));
        $user->setPassword(password_hash($password, PASSWORD_DEFAULT));

        // Get the Doctrine service and manager
        $em = $this->getDoctrine()->getManager();

        // Add user to Doctrine for saving
        $em->persist($user);

        // Save user
        $em->flush();

        return $this->serializer->response('Saved new user with email - '.$user->getEmail());
    }

	/**
	 * @Route("/{id}/update", name="user_update", methods={"POST"}, requirements={"id"="\d+"})
	 * @param int $id
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function updateAction($id, Request $request)
	{
		if ($this->getUser()->getId() != $id) {
			if (!$this->isGranted("ROLE_ADMIN")) {
				throw new HttpException(Response::HTTP_UNAUTHORIZED, "Access denied!!");
			}
		}

		// Assingning data from request and removing unnecessary symbols
		$parametersAsArray = [];
		if ($content = $request->getContent()) {
			$parametersAsArray = json_decode($content, true);
		}

		$repository = $this->getDoctrine()->getRepository(Users::class);
		$user = $repository->findOneBy(['id' => $id]);
		if (empty($user)) {
			return $this->serializer->response('User not found!', Response::HTTP_NOT_FOUND);
		}

		if (!empty($parametersAsArray['chatBannedUntil'])) {
			$chatBannedUntil = htmlspecialchars(trim($parametersAsArray['chatBannedUntil']));
			$user->setChatBannedUntil(\DateTime::createFromFormat('Y-m-d', $chatBannedUntil));

			// Return because banning is seperate thing
			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();
			return $this->serializer->response('User banned from chat!!', Response::HTTP_OK);
		}

		// Set all data
		if (!empty($parametersAsArray['password'])) {
			$password = htmlspecialchars(trim($parametersAsArray['password']));
			$user->setPassword(password_hash($password, PASSWORD_DEFAULT));
		}
		if (!empty($parametersAsArray['name'])) {
			$name = htmlspecialchars(trim($parametersAsArray['name']));
			$user->setName($name);
		}
		if (!empty($parametersAsArray['description'])) {
			$description = htmlspecialchars(trim($parametersAsArray['description']));
			$user->setDescription($description);
		}

		if (!empty($_FILES['profilePicture'])) {
			// File name
			$filename = $_FILES['profilePicture']['name'];
			// Valid file extensions
			$valid_extensions = array("jpg","jpeg","png");
			// File extension
			$extension = pathinfo($filename, PATHINFO_EXTENSION);
			// Check extension
			if(in_array(strtolower($extension),$valid_extensions) ) {
				$date = new \DateTime();
				$filename = $date->format('U').'_'.htmlspecialchars(trim($filename));
				move_uploaded_file($_FILES['profilePicture']['tmp_name'], "./Files/".$filename);
				$user->setProfilePicture($filename);
			}
		}

		// Get the Doctrine service and manager
		$em = $this->getDoctrine()->getManager();

		// Add user to Doctrine for saving
		$em->persist($user);

		// Save user
		$em->flush();

		return $this->getOneAction($id);
	}

	/**
	 * @Route("/{id}/updateRole", name="user_change_role", methods={"POST"}, requirements={"id"="\d+"})
	 * @param int $id
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function changeRoleAction($id, Request $request)
	{
		if (!$this->isGranted("ROLE_ADMIN")) {
			throw new HttpException(Response::HTTP_FORBIDDEN, "Access denied!!");
		}

		// Assingning data from request and removing unnecessary symbols
		$parametersAsArray = [];
		if ($content = $request->getContent()) {
			$parametersAsArray = json_decode($content, true);
		}

		$repository = $this->getDoctrine()->getRepository(Users::class);
		$user = $repository->findOneBy(['id' => $id]);
		if (empty($user)) {
			return $this->serializer->response('User not found!', Response::HTTP_NOT_FOUND);
		}
		if (!empty($parametersAsArray['role'])) {
			$role = htmlspecialchars(trim($parametersAsArray['role']));
			$user->setRoles([$role]);
		}

		// Get the Doctrine service and manager
		$em = $this->getDoctrine()->getManager();

		// Add user to Doctrine for saving
		$em->persist($user);

		// Save user
		$em->flush();

		return $this->getOneAction($id);
	}

	/**
	 * @Route("/{id}", name="user_show_one", methods={"GET"}, requirements={"id"="\d+"})
	 * @param int $id
	 * @return JsonResponse
	 */
	public function getOneAction($id, $filter = true)
	{
		// Finding user
		$repository = $this->getDoctrine()->getRepository(Users::class);
		$user = $repository->find($id);
		if (empty($user)) {
			return $this->serializer->response('No user found for id '.$id, Response::HTTP_NOT_FOUND);
		}

		$userArray = $user->toArray();

		// Reformat values for front
		if (in_array('ROLE_ADMIN', $userArray['roles'])) {
			$userArray['role'] = "ROLE_ADMIN";
		}elseif (in_array('ROLE_USER', $userArray['roles'])){
			$userArray['role'] = "ROLE_USER";
		}else {
			$userArray['role'] = "ROLE_GUEST";
		}
		$userArray['birthDate'] = $user->getBirthDate()->format('Y-m-d');
		$userArray['registerDate'] = $user->getRegisterDate()->format('Y-m-d');
		if (!empty($userArray['chatBannedUntil'])) {
			$userArray['chatBannedUntil'] = $user->getChatBannedUntil()->format('Y-m-d');
		} else {
			$userArray['chatBannedUntil'] = '';
		}
		if (empty($userArray['profilePicture'])) {
			$userArray['profilePicture'] = 'http://'.$_SERVER['HTTP_HOST'].'/Files/defProfilePic.png';
		} else {
			$userArray['profilePicture'] = 'http://'.$_SERVER['HTTP_HOST'].'/Files/'.$userArray['profilePicture'];
		}

		$followsRepository = $this->getDoctrine()->getRepository(UsersFollowers::class);
		$following = $followsRepository->findBy(['userId' => $id]);
		if (!empty($following)) {
			$followingArr = [];
			foreach ($following as $item) {
				$followingArr[] = $item->getFollowedUserId();
			}
			$userArray['followingUsers'] = $followingArr;
		}
		$followers = $followsRepository->findBy(['followedUserId' => $id]);
		if (!empty($followers)) {
			$followersArr = [];
			foreach ($followers as $item) {
				$followersArr[] = $item->getUserId();
			}
			$userArray['followers'] = $followersArr;
		}

		// Unset important or unnecessary values
		unset($userArray['password']);
		unset($userArray['roles']);
		if ($filter) {
			unset($userArray['role']);
			unset($userArray['chatBannedUntil']);
			unset($userArray['email']);
		}

		return $this->serializer->response($userArray, 200, [], false, true);
	}

	/**
	 * @Route("", name="user_show_all", methods={"GET"})
	 * @return JsonResponse
	 */
	public function getAllAction()
	{
		if (!$this->isGranted("ROLE_ADMIN")) {
			throw new HttpException(Response::HTTP_FORBIDDEN, "Access denied!!");
		}
		// Finding users
		$repository = $this->getDoctrine()->getRepository(Users::class);
		$users = $repository->findAll();
		if (empty($users)) {
			return $this->serializer->response('No users found!!', Response::HTTP_NOT_FOUND);
		}

		$usersArray = [];
		foreach ($users as $user) {
			$userArray = $user->toArray();

			// Reformat values for front
			if (in_array('ROLE_ADMIN', $userArray['roles'])) {
				$userArray['role'] = "ROLE_ADMIN";
			}elseif (in_array('ROLE_USER', $userArray['roles'])){
				$userArray['role'] = "ROLE_USER";
			}else {
				$userArray['role'] = "ROLE_GUEST";
			}
			$userArray['birthDate'] = $user->getBirthDate()->format('Y-m-d');
			$userArray['registerDate'] = $user->getRegisterDate()->format('Y-m-d');
			if (!empty($userArray['chatBannedUntil'])) {
				$userArray['chatBannedUntil'] = $user->getChatBannedUntil()->format('Y-m-d');
			} else {
				$userArray['chatBannedUntil'] = '';
			}
			if (empty($userArray['profilePicture'])) {
				$userArray['profilePicture'] = 'http://'.$_SERVER['HTTP_HOST'].'/Files/defProfilePic.png';
			} else {
				$userArray['profilePicture'] = 'http://'.$_SERVER['HTTP_HOST'].'/Files/'.$userArray['profilePicture'];
			}

			// Unset important or unnecessary values
			unset($userArray['password']);
			unset($userArray['roles']);

			$usersArray[] = $userArray;
		}

		return $this->serializer->response($usersArray, 200, [], false, true);
	}

	/**
	 * @Route("/{userId}/apis/{apiId}/movies/{movieId}/status/{relationType}", name="user_add_movie_to_list", methods={"POST"}, requirements={"userId"="\d+", "apiId"="\d+", "movieId"="\d+", "relationType"="\d+"})
	 * @param int $userId
	 * @param int $apiId
	 * @param int $movieId
	 * @param int $relationType
	 * @return JsonResponse
	 */
	public function addMovieStatus($userId, $apiId, $movieId, $relationType) {
		if (!$this->isGranted("ROLE_USER") && !$this->isGranted("ROLE_ADMIN")) {
			throw new HttpException(Response::HTTP_FORBIDDEN, "Access denied!!");
		}

		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository(UsersMovies::class);
		$userMovies = $repository->findOneBy([
			'userId' => $userId,
			'apiId' => $apiId,
			'movieId' => $movieId,
		]);

		// if movie is not liked and removing it from list or if only liked and removing like - remove from db as well
		if ((!empty($userMovies) && $userMovies->getIsFavorite() == 0 && $userMovies->getRelationTypeId() == $relationType)
			|| (!empty($userMovies) && $userMovies->getIsFavorite() == 1 && empty($userMovies->getRelationTypeId()) && $relationType == 0)) {
			$em->remove($userMovies);
			$em->flush();
			return $this->serializer->response('Removed movie '.$movieId.' from user '.$userId);
		}

		if (empty($userMovies)) {
			$userMovies = new UsersMovies();
			$userMovies->setUserId($userId);
			$userMovies->setApiId($apiId);
			$userMovies->setMovieId($movieId);
		}
		if ($relationType == 0) {
			$userMovies->setIsFavorite(!$userMovies->getIsFavorite());
		} else {
			if ($userMovies->getRelationTypeId() == $relationType) {
				$userMovies->setRelationTypeId(0);
				$userMovies->setUserRating(null);
				$userMovies->setDateAdded(new \DateTime('0000-00-00'));
			} else {
				if (empty($userMovies->getRelationTypeId()) || $userMovies->getRelationTypeId() == 0) {
					$userMovies->setDateAdded(new \DateTime());
				}
				$userMovies->setRelationTypeId($relationType);
			}
		}

		// Get the Doctrine service and manager
		$em = $this->getDoctrine()->getManager();

		// Add user to Doctrine for saving
		$em->persist($userMovies);

		// Save user
		$em->flush();

		return $this->serializer->response('Set users '.$userId.' movie '.$movieId.' in list with status id '.$relationType);
	}

	/**
	 * @Route("/{userId}/apis/{apiId}/movies/{movieId}/rating/{rating}", name="user_add_movie_rating", methods={"POST"}, requirements={"userId"="\d+", "apiId"="\d+", "movieId"="\d+", "rating"="\d+"})
	 * @param int $userId
	 * @param int $apiId
	 * @param int $movieId
	 * @param int $rating
	 * @return JsonResponse
	 */
	public function addUsersRating($userId, $apiId, $movieId, $rating) {
		if (!$this->isGranted("ROLE_USER") && !$this->isGranted("ROLE_ADMIN")) {
			throw new HttpException(Response::HTTP_FORBIDDEN, "Access denied!!");
		}

		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository(UsersMovies::class);
		$userMovie = $repository->findOneBy([
			'userId' => $userId,
			'apiId' => $apiId,
			'movieId' => $movieId,
		]);

		if (empty($userMovie)) {
			return $this->serializer->response('Cannot rate movies not in list', Response::HTTP_NOT_FOUND);
		}
		$userMovie->setUserRating($rating);

		$em->persist($userMovie);
		$em->flush();

		return $this->serializer->response('Rated movie - '.$rating);
	}

	/**
	 * @Route("/{userId}/follow/{followedUserId}", name="user_follow", methods={"POST"}, requirements={"userId"="\d+", "followedUserId"="\d+"})
	 * @param int $userId
	 * @param int $followedUserId
	 * @return JsonResponse
	 */
	public function addUserFollow($userId, $followedUserId) {
		if (!$this->isGranted("ROLE_USER") && !$this->isGranted("ROLE_ADMIN")) {
			throw new HttpException(Response::HTTP_FORBIDDEN, "Access denied!!");
		}

		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository(Users::class);
		$user = $repository->findOneBy(['id' => $userId]);
		if (empty($user)) {
			return $this->serializer->response('User not found!', Response::HTTP_NOT_FOUND);
		}
		$followedUser = $repository->findOneBy(['id' => $followedUserId]);
		if (empty($followedUser)) {
			return $this->serializer->response('Followed user not found!', Response::HTTP_NOT_FOUND);
		}
		$followRepository = $em->getRepository(UsersFollowers::class);
		$record = $followRepository->findOneBy(['userId' => $userId, 'followedUserId' => $followedUserId]);
		if (empty($record)) {
			$record = new UsersFollowers();
			$record->setUserId($userId);
			$record->setFollowedUserId($followedUserId);
			$em->persist($record);
			$msg = 'User '.$userId.' followed user '.$followedUserId;
		} else {
			$em->remove($record);
			$msg = 'User '.$userId.' no longer follow user '.$followedUserId;
		}

		$em->flush();

		return $this->serializer->response($msg);
	}

	/**
	 * @Route("/{id}/movies", name="user_movies_list", methods={"GET"}, requirements={"id"="\d+"})
	 * @param $id
	 * @return Response
	 */
	public function getUsersMovies($id)
	{
		$result = $this->forward('App\Controller\EntityController\MoviesController::getUsersMovieList', [
			'userId' => $id,
		]);

		return $result;
	}

	/**
	 * @Route("/{id}/messages/{elementNumber}", name="user_get_messages_list", methods={"GET"}, requirements={"id"="\d+", "elementNumber"="\d+"})
	 * @param int $id
	 * @param int $elementNumber
	 * @return Response
	 */
	public function getUsersMessages($id, $elementNumber)
	{
		return $this->forward('App\Controller\EntityController\MessagesController::getAllUsersMessages', [
			'userId' => $id,
			'elementNumber' => $elementNumber,
		]);
	}

	/**
	 * @return JsonResponse
	 */
	public function getUsersFollows($id)
	{
		$id = intval($id);
		if (empty($id)) {
			return $this->serializer->response('User ID incorrect!', Response::HTTP_BAD_REQUEST);
		}
		// Finding users
		$repository = $this->getDoctrine()->getRepository(UsersFollowers::class);
		$follows = $repository->findBy(['userId' => $id]);
		if (empty($follows)) {
			return $this->serializer->response([], 200, [], false, true);
		}

		$usersIds = [];
		foreach ($follows as $followRecord) {
			$usersIds[] = $followRecord->getFollowedUserId();
		}
		$users = $this->getMainUsersDataByIds($usersIds);

		return $this->serializer->response($users, 200, [], false, true);
	}

	/**
	 * @return JsonResponse
	 */
	public function getUsersFollowers($id)
	{
		$id = intval($id);
		if (empty($id)) {
			return $this->serializer->response('User ID incorrect!', Response::HTTP_BAD_REQUEST);
		}
		// Finding users
		$repository = $this->getDoctrine()->getRepository(UsersFollowers::class);
		$followers = $repository->findBy(['followedUserId' => $id]);
		if (empty($followers)) {
			return $this->serializer->response([], 200, [], false, true);
		}

		$usersIds = [];
		foreach ($followers as $followRecord) {
			$usersIds[] = $followRecord->getUserId();
		}
		$users = $this->getMainUsersDataByIds($usersIds);

		return $this->serializer->response($users, 200, [], false, true);
	}

	/**
	 * @param array $usersIds
	 */
	protected function getMainUsersDataByIds($usersIds) {
		$usersRepository = $this->getDoctrine()->getRepository(Users::class);
		$users = $usersRepository->findAllMainDataByIds($usersIds);
		foreach ($users as &$user) {
			if (empty($user['profilePicture'])) {
				$user['profilePicture'] = 'http://'.$_SERVER['HTTP_HOST'].'/Files/defProfilePic.png';
			} else {
				$user['profilePicture'] = 'http://'.$_SERVER['HTTP_HOST'].'/Files/'.$user['profilePicture'];
			}
		}
		return $users;
	}
}