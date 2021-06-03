<?php

namespace App\Controller\EntityController;

use App\Controller\InitSerializer;
use App\Entity\Forum;
use App\Entity\Messages;
use App\Entity\Users;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ForumController
 * @package App\Controller\EntityController
 * @Route("/forum")
 */
class ForumController extends AbstractController {
	protected $serializer;

	public function __construct() {
		$this->serializer = new InitSerializer();
	}

	/**
	 * @Route("", name="forum_create", methods={"POST"})
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
		if (isset($parametersAsArray['message']) && isset($parametersAsArray['title'])) {
			$message = htmlspecialchars($parametersAsArray['message']);
			$title = htmlspecialchars($parametersAsArray['title']);
		} else {
			return $this->serializer->response("Missing data!", Response::HTTP_BAD_REQUEST);
		}
		$userId = $this->getUser()->getId();
		$repository = $this->getDoctrine()->getRepository(Users::class);
		$user = $repository->find($userId);
		if ($user->getChatBannedUntil() > new \DateTime())  {
			return $this->serializer->response('User is banned from chat!', Response::HTTP_FORBIDDEN);
		}

		// Creating user object
		$forum = new Forum();
		$forum->setUserId($userId);
		$forum->setMessage($message);
		$forum->setTitle($title);
		$forum->setPostDate(new \DateTime());

		// Get the Doctrine service and manager
		$em = $this->getDoctrine()->getManager();

		// Add to Doctrine for saving
		$em->persist($forum);

		// Save
		$em->flush();

		return $this->serializer->response($forum, 200, [], false, false, true);
	}

	/**
	 * @Route("/page/{pageNr}", name="forum_get_all", methods={"GET"}, requirements={"pageNr"="\d+"})
	 * @return JsonResponse|Response
	 */
	public function getAllAction($pageNr = 0, $userId = 0) {
		$em = $this->getDoctrine()->getManager();
		$repForum = $em->getRepository(Forum::class);
		$pageSize = 20;

		$forumAll = $repForum->findAllForums($pageSize, $pageNr * $pageSize, $userId);

		if (empty($forumAll)) {
			return $this->serializer->response([], 200);
		}

		return $this->serializer->response($forumAll, 200, [], false, false);
	}

	/**
	 * @Route("/{id}", name="forum_delete", methods={"POST"}, requirements={"id"="\d+"})
	 * @param int $id
	 * @return JsonResponse|Response
	 */
	public function deleteAction($id)
	{
		if (!$this->isGranted("ROLE_USER") && !$this->isGranted("ROLE_ADMIN")) {
			throw new HttpException(Response::HTTP_FORBIDDEN, "Access denied!!");
		}

		$em = $this->getDoctrine()->getManager();
		$repForum = $em->getRepository(Forum::class);

		$forum = $repForum->find($id);
		if (!$this->isGranted("ROLE_ADMIN") && $forum->getUserId() != $this->getUser()->getId()) {
			throw new HttpException(Response::HTTP_FORBIDDEN, "Access denied!!");
		}

		if (empty($forum)) {
			return $this->serializer->response('Forum not found', Response::HTTP_NOT_FOUND);
		}
		$em->remove($forum);

		// Save
		$em->flush();

		return $this->serializer->response($forum, 200, [], false, false, true);
	}

	/**
	 * @Route("/find", name="find_forum", methods={"POST"})
	 * @param Request $request
	 * @return JsonResponse|Response
	 */
	public function findForum(Request $request) {
		// Assingning data from request and removing unnecessary symbols
		$parametersAsArray = [];
		if ($content = $request->getContent()) {
			$parametersAsArray = json_decode($content, true);
		}

		// Check if none of the data is missing
		if (isset($parametersAsArray['search'])) {
			$search = htmlspecialchars($parametersAsArray['search']);
		} else {
			return $this->serializer->response("Missing search data!", Response::HTTP_BAD_REQUEST);
		}
		$pageNr = 0;
		if (isset($parametersAsArray['page']) && !empty(intval($parametersAsArray['page']))) {
			$pageNr = intval($parametersAsArray['page']);
		}
		$pageSize = 20;

		$em = $this->getDoctrine()->getManager();
		$repForum = $em->getRepository(Forum::class);
		$forums = $repForum->searchForum($search, $pageSize, $pageNr * $pageSize);
		if (empty($forums)) {
			return $this->serializer->response("Nothing found!", Response::HTTP_NOT_FOUND);
		}

		return $this->serializer->response($forums, 200, [], false, true);
	}

	/**
	 * @Route("/{id}/messages/page/{pageNr}", name="forum_get_messages", methods={"GET"}, requirements={"id"="\d+", "pageNr"="\d+"})
	 * @param int $id
	 * @param int $pageNr
	 * @return Response
	 */
	public function getForumMessagesAction($id, $pageNr = 0) {
		return $this->forward('App\Controller\EntityController\MessagesController::getForumThreadMessages', [
			'forumId' => $id,
			'pageNr' => $pageNr,
		]);
	}

	/**
	 * @Route("/{id}/messages", name="forum_message_create", methods={"POST"}, requirements={"id"="\d+"})
	 * @return Response
	 */
	public function createForumMessageAction(Request $request) {
		return $this->forward('App\Controller\EntityController\MessagesController::createAction', [
			'request' => $request,
		]);
	}
}