<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class ProfileController
 * @package App\Controller\Api
 * @Route("/profile")
 */
class ProfileController extends AbstractController
{
	protected $serializer;

	public function __construct()
	{
		$encoders = [new XmlEncoder(), new JsonEncoder()];
		$normalizers = [new ObjectNormalizer()];

		$this->serializer = new Serializer($normalizers, $encoders);
	}

    /**
     * @Route("", name="current_user_profile_data", methods={"GET"})
     * @return Response
     */
    public function getSelfAction()
    {
		if (!$this->isGranted("ROLE_USER") && !$this->isGranted("ROLE_ADMIN")) {
			throw new HttpException(Response::HTTP_FORBIDDEN, "Access denied!!");
		}
        $userId = $this->getUser()->getId();

        return $this->getAction($userId, false);
    }

	/**
	 * @Route("/{id}", name="user_profile_data", methods={"GET"}, requirements={"id"="\d+"})
	 * @return Response
	 */
	public function getAction($id, $filter = true)
	{
		return $this->forward('App\Controller\EntityController\UsersController::getOneAction', [
			'id' => $id,
			'filter' => $filter,
		]);
	}

	/**
	 * @Route("/find", name="profile_find", methods={"POST"})
	 * @param Request $request
	 * @return Response
	 */
	public function findAction(Request $request)
	{
		return $this->forward('App\Controller\EntityController\UsersController::findUserByName', [
			'request' => $request,
		]);
	}

	/**
	 * @Route("/{id}/update", name="profile_update", methods={"POST"}, requirements={"id"="\d+"})
	 * @param Request $request
	 * @return Response
	 */
	public function updateProfile(Request $request)
	{
		if (!$this->isGranted("ROLE_USER") && !$this->isGranted("ROLE_ADMIN")) {
			throw new HttpException(Response::HTTP_FORBIDDEN, "Access denied!!");
		}
		return $this->forward('App\Controller\EntityController\UsersController::updateAction', [
			'id' => $this->getUser()->getId(),
			'request' => $request,
		]);
	}

	/**
	 * @Route("/{id}/follows", name="user_profile_follows", methods={"GET"}, requirements={"id"="\d+"})
	 * @return Response
	 */
	public function getFollowsAction($id)
	{
		return $this->forward('App\Controller\EntityController\UsersController::getUsersFollows', [
			'id' => $id,
		]);
	}

	/**
	 * @Route("/{id}/followers", name="user_profile_followers", methods={"GET"}, requirements={"id"="\d+"})
	 * @return Response
	 */
	public function getFollowersAction($id)
	{
		return $this->forward('App\Controller\EntityController\UsersController::getUsersFollowers', [
			'id' => $id,
		]);
	}

	/**
	 * @Route("/{id}/forum/page/{pageNr}", name="user_profile_get_forum", methods={"GET"}, requirements={"id"="\d+", "pageNr"="\d+"})
	 * @return Response
	 */
	public function getForumAction($id, $pageNr)
	{
		return $this->forward('App\Controller\EntityController\ForumController::getAllAction', [
			'pageNr' => $pageNr,
			'userId' => $id,
		]);
	}

    /**
     * @return JsonResponse
     */
    public function logout()
    {
        $result = new JsonResponse("Logged out!!", Response::HTTP_OK);

        $result->headers->clearCookie('BEARER');
        $result->headers->clearCookie('REFRESH_TOKEN');

        session_unset();
        return $result;
    }

	/**
	 * Returns a JSON response
	 *
	 * @param array|string $data
	 * @param int $status
	 * @param array $headers
	 * @param bool $serialize Need serializing?
	 * @return JsonResponse|Response
	 */
	public function response($data, $status = 200, $headers = [], $serialize = false)
	{
		if ($serialize) {
			return new Response($this->serializer->serialize($data, 'json'), $status, $headers);
		}
		return new JsonResponse($data, $status, $headers);
	}
}