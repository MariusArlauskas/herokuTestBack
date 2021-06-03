<?php

namespace App\Controller\EntityController;

use App\Controller\InitSerializer;
use App\Entity\Forum;
use App\Entity\Messages;
use App\Entity\Users;
use App\Entity\UsersFollowers;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MessagesController
 * @package App\Controller\EntityController
 * @Route("/messages")
 */
class MessagesController extends AbstractController
{
	protected $serializer;

	public function __construct()
	{
		$this->serializer = new InitSerializer();
	}

	/**
	 * @Route("", name="message_create", methods={"POST"})
	 * @param Request $request
	 * @return JsonResponse|Response
	 * @throws Exception
	 */
	public function createAction(Request $request)
	{
		if (!$this->isGranted("ROLE_USER") && !$this->isGranted("ROLE_ADMIN")) {
			throw new HttpException(Response::HTTP_FORBIDDEN, "Access denied!!");
		}

		// Assingning data from request and removing unnecessary symbols
		$parametersAsArray = [];
		if ($content = $request->getContent()) {
			$parametersAsArray = json_decode($content, true);
		}
		// Check if none of the data is missing
		if (isset($parametersAsArray['message'])) {
			$messageText = htmlspecialchars($parametersAsArray['message']);
		} else {
			return $this->serializer->response("Missing message!", Response::HTTP_BAD_REQUEST);
		}
		$userId = $this->getUser()->getId();
		$repository = $this->getDoctrine()->getRepository(Users::class);
		$user = $repository->find($userId);
		if (empty($user)) {
			return $this->serializer->response('No user found for id '.$userId, Response::HTTP_NOT_FOUND);
		} elseif ($user->getChatBannedUntil() > new \DateTime())  {
			return $this->serializer->response('User is banned from chat!', Response::HTTP_FORBIDDEN);
		}

		// Not required fields
		if (!empty($parametersAsArray['parentId'])) {
			$parentId = intval($parametersAsArray['parentId']);
		}
		if (!empty($parametersAsArray['movieId'])) {
			$movieId = intval($parametersAsArray['movieId']);
		}
		if (!empty($parametersAsArray['forumId'])) {
			$forumId = intval($parametersAsArray['forumId']);
		}

		if (!empty($parametersAsArray['id'])) {
			$id = intval($parametersAsArray['id']);
		}
		if (!empty($id)) {	// if update
			$em = $this->getDoctrine()->getManager();
			$repMessages = $em->getRepository(Messages::class);

			$message = $repMessages->find($id);
			$message->setMessage($messageText);

			// Add user to Doctrine for saving
			$em->persist($message);

			// Save
			$em->flush();

			return $this->serializer->response($message, 200, [], false, false, true);
		}

		// Creating object
		$message = new Messages();
		$message->setUserId($userId);
		$message->setMessage($messageText);
		$message->setPostDate(new \DateTime());
		if (!empty($parentId)) {
			$message->setParentId($parentId);
		}
		if (!empty($movieId)) {
			$message->setMovieId($movieId);
		}
		if (!empty($forumId)) {
			$message->setForumId($forumId);
		}

		// Get the Doctrine service and manager
		$em = $this->getDoctrine()->getManager();

		// Add user to Doctrine for saving
		$em->persist($message);

		// Save
		$em->flush();

		return $this->serializer->response($message, 200, [], false, false, true);
	}

	/**
	 * @Route("/{id}", name="message_delete", methods={"POST"}, requirements={"id"="\d+"})
	 * @param int $id
	 * @return JsonResponse|Response
	 */
	public function deleteAction($id)
	{
		if (!$this->isGranted("ROLE_USER") && !$this->isGranted("ROLE_ADMIN")) {
			throw new HttpException(Response::HTTP_FORBIDDEN, "Access denied!!");
		}

		$em = $this->getDoctrine()->getManager();
		$repMessages = $em->getRepository(Messages::class);

		$message = $repMessages->find($id);
		if (empty($message)) {
			return $this->serializer->response('Message not found', Response::HTTP_NOT_FOUND);
		}
		$em->remove($message);

		// Save
		$em->flush();

		return $this->serializer->response($message, 200, [], false, false, true);
	}

	/**
	 * @Route("/{elementNumber}/{lastId}/{followingOnly}", name="messages_get", methods={"GET"}, requirements={"elementNumber"="\d+", "lastId"="\d+"})
	 * @param int $elementNumber
	 * @param int $lastId
	 * @return JsonResponse|Response
	 */
	public function getAllAction($elementNumber, $lastId, $followingOnly = false){
		$em = $this->getDoctrine()->getManager();
		$repMessages = $em->getRepository(Messages::class);

		$followingOnly = $followingOnly == 'true';
		$followingArr = [];
		if ($followingOnly) {
			$followsRepository = $this->getDoctrine()->getRepository(UsersFollowers::class);
			$following = $followsRepository->findBy(['userId' => $this->getUser()->getId()]);
			if (!empty($following)) {
				foreach ($following as $item) {
					$followingArr[] = $item->getFollowedUserId();
				}
			}
		}

		if ($elementNumber == 0) {
			$messages = $repMessages->findMessagesSortedByDate(1, 0, $followingArr);
			if ($messages[0]['id'] == $lastId) {
				return $this->serializer->response([], 200);
			}
		} else {
			$elementNumber -= 1;
			$messages = $repMessages->findMessagesSortedByDate(10, $elementNumber, $followingArr);
		}

		if (empty($messages)) {
			return $this->serializer->response([], 200);
		}

		$childMessages = [];
		foreach ($messages as $message) {
			$childMessages[] = $message['id'];		// First put in parents ids for search
		}
		$childMessages = $repMessages->findMessagesCommentsSortedByDate($childMessages);

		foreach ($childMessages as $key => $message) {
			if (empty($message['userProfilePicture'])) {
				$childMessages[$key]['userProfilePicture'] = 'http://'.$_SERVER['HTTP_HOST'].'/Files/defProfilePic.png';
			} else {
				$childMessages[$key]['userProfilePicture'] = 'http://'.$_SERVER['HTTP_HOST'].'/Files/'.$message['userProfilePicture'];
			}
		}
		foreach ($messages as $key => $message) {
			$messages[$key]['children'] = [];
			if (empty($message['userProfilePicture'])) {
				$messages[$key]['userProfilePicture'] = 'http://'.$_SERVER['HTTP_HOST'].'/Files/defProfilePic.png';
			} else {
				$messages[$key]['userProfilePicture'] = 'http://'.$_SERVER['HTTP_HOST'].'/Files/'.$message['userProfilePicture'];
			}

			if (!empty($childMessages)) {
				foreach ($childMessages as $childMessage) {
					if ($childMessage['parentId'] == $message['id']) {
						$messages[$key]['children'][] = $childMessage;
					}
				}
			}
		}

		return $this->serializer->response($messages, 200, [], false, true);
	}

	public function getAllUsersMessages($userId, $elementNumber){
		$em = $this->getDoctrine()->getManager();
		$repMessages = $em->getRepository(Messages::class);

		$messages = $repMessages->findMessagesSortedByDate(10, $elementNumber, [intval($userId)] );

		if (empty($messages)) {
			return $this->serializer->response([], 200);
		}

		$childMessages = [];
		foreach ($messages as $message) {
			$childMessages[] = $message['id'];		// First put in parents ids for search
		}
		$childMessages = $repMessages->findMessagesCommentsSortedByDate($childMessages);

		foreach ($childMessages as $key => $message) {
			if (empty($message['userProfilePicture'])) {
				$childMessages[$key]['userProfilePicture'] = 'http://'.$_SERVER['HTTP_HOST'].'/Files/defProfilePic.png';
			} else {
				$childMessages[$key]['userProfilePicture'] = 'http://'.$_SERVER['HTTP_HOST'].'/Files/'.$message['userProfilePicture'];
			}
		}
		foreach ($messages as $key => $message) {
			$messages[$key]['children'] = [];
			if (empty($message['userProfilePicture'])) {
				$messages[$key]['userProfilePicture'] = 'http://'.$_SERVER['HTTP_HOST'].'/Files/defProfilePic.png';
			} else {
				$messages[$key]['userProfilePicture'] = 'http://'.$_SERVER['HTTP_HOST'].'/Files/'.$message['userProfilePicture'];
			}

			if (!empty($childMessages)) {
				foreach ($childMessages as $childMessage) {
					if ($childMessage['parentId'] == $message['id']) {
						$messages[$key]['children'][] = $childMessage;
					}
				}
			}
		}

		return $this->serializer->response($messages, 200, [], false, true);
	}

	public function getAllMovieMessages($movieId, $elementNumber){
		$em = $this->getDoctrine()->getManager();
		$repMessages = $em->getRepository(Messages::class);

		$messages = $repMessages->findMovieMessagesSortedByDate(10, $elementNumber, $movieId );

		if (empty($messages)) {
			return $this->serializer->response([], 200);
		}

		$childMessages = [];
		foreach ($messages as $message) {
			$childMessages[] = $message['id'];		// First put in parents ids for search
		}
		$childMessages = $repMessages->findMessagesCommentsSortedByDate($childMessages);

		foreach ($childMessages as $key => $message) {
			if (empty($message['userProfilePicture'])) {
				$childMessages[$key]['userProfilePicture'] = 'http://'.$_SERVER['HTTP_HOST'].'/Files/defProfilePic.png';
			} else {
				$childMessages[$key]['userProfilePicture'] = 'http://'.$_SERVER['HTTP_HOST'].'/Files/'.$message['userProfilePicture'];
			}
		}
		foreach ($messages as $key => $message) {
			$messages[$key]['children'] = [];
			if (empty($message['userProfilePicture'])) {
				$messages[$key]['userProfilePicture'] = 'http://'.$_SERVER['HTTP_HOST'].'/Files/defProfilePic.png';
			} else {
				$messages[$key]['userProfilePicture'] = 'http://'.$_SERVER['HTTP_HOST'].'/Files/'.$message['userProfilePicture'];
			}

			if (!empty($childMessages)) {
				foreach ($childMessages as $childMessage) {
					if ($childMessage['parentId'] == $message['id']) {
						$messages[$key]['children'][] = $childMessage;
					}
				}
			}
		}

		return $this->serializer->response($messages, 200, [], false, true);
	}

	/**
	 * @param $forumId
	 * @param $pageNr
	 * @return Response
	 * @throws \Doctrine\DBAL\DBALException
	 */
	public function getForumThreadMessages($forumId, $pageNr){
		$em = $this->getDoctrine()->getManager();
		$repMessages = $em->getRepository(Messages::class);
		$pageSize = 20;
		$result = [];
		if ($pageNr == 0) {
			$repForum = $em->getRepository(Forum::class);
			$forum = $repForum->find($forumId);
			$result['forum'] = $forum->toArray();

			$forumUser = $this->getMainUsersDataByIds([$forum->getUserId()]);
			$result['forum']['userName'] = $forumUser[0]['name'];
			$result['forum']['profilePicture'] = $forumUser[0]['profilePicture'];
		}

		$messages = $repMessages->findForumMessagesSortedByDate($pageSize, $pageNr * $pageSize, $forumId );

		if (empty($messages)) {
			$result['messages'] = [];
			return $this->serializer->response($result, 200, [], false, true);
		}

		$childMessages = [];
		foreach ($messages as $message) {
			$childMessages[] = $message['id'];		// First put in parents ids for search
		}
		$childMessages = $repMessages->findMessagesCommentsSortedByDate($childMessages);

		foreach ($childMessages as $key => $message) {
			if (empty($message['userProfilePicture'])) {
				$childMessages[$key]['userProfilePicture'] = 'http://'.$_SERVER['HTTP_HOST'].'/Files/defProfilePic.png';
			} else {
				$childMessages[$key]['userProfilePicture'] = 'http://'.$_SERVER['HTTP_HOST'].'/Files/'.$message['userProfilePicture'];
			}
		}
		foreach ($messages as $key => $message) {
			$messages[$key]['children'] = [];
			if (empty($message['userProfilePicture'])) {
				$messages[$key]['userProfilePicture'] = 'http://'.$_SERVER['HTTP_HOST'].'/Files/defProfilePic.png';
			} else {
				$messages[$key]['userProfilePicture'] = 'http://'.$_SERVER['HTTP_HOST'].'/Files/'.$message['userProfilePicture'];
			}

			if (!empty($childMessages)) {
				foreach ($childMessages as $childMessage) {
					if ($childMessage['parentId'] == $message['id']) {
						$messages[$key]['children'][] = $childMessage;
					}
				}
			}
		}

		$result['messages'] = $messages;

		return $this->serializer->response($result, 200, [], false, true);
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