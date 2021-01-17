<?php

namespace App\Controller\EntityController;

use App\Controller\InitSerializer;
use App\Controller\RemoteApi\TmdbApi;
use App\Entity\Movies;
use App\Entity\UsersMovies;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class MoviesController
 * @package App\Controller
 * @Route("/movies")
 */
class MoviesController extends AbstractController
{
	protected $serializer;

	public function __construct()
	{
		$this->serializer = new InitSerializer();
	}

	/** Get top rated movies
	 * @Route("/{type}/page/{pageNumber}/user/{userId}", name="movies_with_favorites_get", methods={"POST"}, requirements={"userId"="\d+", "pageNumber"="\d+"})
	 * @param string $type
	 * @param int $pageNumber
	 * @param int $userId
	 * @param Request $request
	 * @return JsonResponse|Response
	 */
	public function getMoviesWithFavorites($type, $pageNumber, $userId, Request $request){
		if (!$this->isGranted("ROLE_USER") && !$this->isGranted("ROLE_ADMIN")) {
			throw new HttpException(Response::HTTP_FORBIDDEN, "Access denied!!");
		}
		$apiId = 1;

		$parametersAsArray = [];
		if ($content = $request->getContent()) {
			$parametersAsArray = json_decode($content, true);
		}
		$filter = [];
		if (!empty($parametersAsArray['filter'])) {
			$filter = $parametersAsArray['filter'];
		}

		$em = $this->getDoctrine()->getManager();
		$repMovies = $em->getRepository(Movies::class);
		if ($pageNumber > 1) {
			$limit = 20;
			$offset = $pageNumber * 20 - 20 + 1;
		} else {
			$limit = 21;
			$offset = $pageNumber * 20 - 20;
		}
		$movies = $repMovies->findByApiIdAndTypeWithUserStatuses($apiId, $userId, $type, $limit, $offset, $filter);

		$tries = 0;
		while (empty($movies) || count($movies) < 20) { 	// Fetch from remote api
			if ($tries > 20) {
				return $this->serializer->response('Change filter or retry', Response::HTTP_NOT_FOUND);
			}
			$tries++;
			if (!empty($filter)) {
				$pageNumber = ($repMovies->getMaxNumberByType($apiId, $type)[0][$type] / 20) + 1;
			}

			$movieApi = new TmdbApi($em);
			$movies = $movieApi->getMovies($type, $pageNumber, $pageNumber * 20 - 20);
			if (empty($movies)) {
				break;
			}
			foreach ($movies as $movie) {
				// Add movie to Doctrine so that it can be saved
				$dbMovie = $repMovies->findOneBy(['apiId' => $apiId, 'movieId' => $movie->getMovieId()]);
				if (!empty($dbMovie)) {
					$setAction = 'set'.ucfirst($type);
					$getAction = 'get'.ucfirst($type);
					$dbMovie->$setAction($movie->$getAction());
					$em->persist($dbMovie);
				} else {
					$em->persist($movie);
				}
			}
			// Save movies
			$em->flush();

			// Now check again to join with users liked movies list
			$movies = $repMovies->findByApiIdAndTypeWithUserStatuses($apiId, $userId, $type, $limit, $offset, $filter );
		}
		return $this->serializer->response($movies, 200, [], false, true, true);
	}

	/** Get most popular movies
	 * @Route("/{type}/page/{pageNumber}", name="movies_get", methods={"POST"}, requirements={"pageNumber"="\d+"})
	 * @param Request $request
	 * @param string $type
	 * @param int $pageNumber
	 * @return JsonResponse|Response
	 */
	public function getMovies(Request $request, $type, $pageNumber){
		$apiId = 1;

		$parametersAsArray = [];
		if ($content = $request->getContent()) {
			$parametersAsArray = json_decode($content, true);
		}
		$filter = [];
		if (!empty($parametersAsArray['filter'])) {
			$filter = $parametersAsArray['filter'];
		}
		$em = $this->getDoctrine()->getManager();
		$repMovies = $em->getRepository(Movies::class);
		if ($pageNumber > 1) {
			$limit = 20;
			$offset = $pageNumber * 20 - 20 + 1;
		} else {
			$limit = 21;
			$offset = $pageNumber * 20 - 20;
		}
		$movies = $repMovies->findByApi($apiId, $type, $limit, $offset, $filter);

		$tries = 0;
		while (empty($movies) || count($movies) < 20) { 	// Fetch from remote api
			if ($tries > 20) {
				return $this->serializer->response('Change filter or retry', Response::HTTP_NOT_FOUND);
			}
			$tries++;
			if (!empty($filter)) {
				$pageNumber = ($repMovies->getMaxNumberByType($apiId, $type)[0][$type] / 20) + 1;
			}

			$movieApi = new TmdbApi($em);
			$movies = $movieApi->getMovies($type, $pageNumber, $pageNumber * 20 - 20);
			if (empty($movies)) {
				return $this->serializer->response('No movies found', Response::HTTP_NOT_FOUND);
			}

			foreach ($movies as $movie) {
				// Add movie to Doctrine so that it can be saved
				$dbMovie = $repMovies->findOneBy(['apiId' => $apiId, 'movieId' => $movie->getMovieId()]);
				if (!empty($dbMovie)) {
					$setAction = 'set'.ucfirst($type);
					$getAction = 'get'.ucfirst($type);
					$dbMovie->$setAction($movie->$getAction());
					$em->persist($dbMovie);
				} else {
					$em->persist($movie);
				}
			}
			// Save movies
			$em->flush();

			// Now check again to join with users liked movies list
			$movies = $repMovies->findByApi($apiId, $type, $limit, $offset, $filter);
		}

		return $this->serializer->response($movies, 200, [], false, true, true);
	}

	/**
	 * @Route("/{id}", name="movie_show_one", methods={"GET"}, requirements={"id"="\d+"})
	 * @param int $id
	 * @return JsonResponse
	 * @throws ClientExceptionInterface
	 * @throws ORMException
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function getOneAction($id)
	{
		$apiId = 1;
		$em = $this->getDoctrine()->getManager();
		$repMovies = $em->getRepository(Movies::class);
		$movie = $repMovies->findOneBy(['movieId' => $id]);
		if (empty($movie)) { 	// Fetch from remote api
			$movieApi = new TmdbApi($em);
			$movie = $movieApi->getOneMovie($id)[0];

			// Add movie to Doctrine so that it can be saved
			$em->persist($movie);
			// Save movies
			$em->flush();

			// Now check again to join with users liked movies list
			$movie = $repMovies->findOneBy(['movieId' => $id]);
		}
		$movie = $movie->toArray();
		if ($this->isGranted("ROLE_USER") || $this->isGranted("ROLE_ADMIN")) {
			$userId = $this->getUser()->getId();
			$repUsersMovies = $em->getRepository(UsersMovies::class);
			$relation = $repUsersMovies->findOneBy(['userId' => $userId, 'movieId' => $id]);
			if (!empty($relation)) {
				$movie['isFavorite'] = $relation->getIsFavorite();
				$movie['relationTypeId'] = $relation->getRelationTypeId();
				$movie['userRating'] = $relation->getUserRating();
			}
		}

		return $this->serializer->response($movie, 200, [], false, true);
	}

	/** Fetches and inserts into db
	 */
	public function getOneFromApiAction($movieId, $apiId)
	{
		$em = $this->getDoctrine()->getManager();
		$movieApi = new TmdbApi($em);
		$movie = $movieApi->getOneMovie($movieId);
		if (empty($movie)) {
			return '';
		}
		$em->persist($movie);
		// Save movies
		$em->flush();

		return $movie;
	}

	/**
	 * @param int $userId
	 * @return JsonResponse|Response
	 * @throws ClientExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 * @throws ORMException
	 */
	public function getUsersMovieList($userId){
		$apiId = 1;
		$em = $this->getDoctrine()->getManager();
		$repMovies = $em->getRepository(Movies::class);
		$movies = $repMovies->findUsersMovieList($apiId, $userId);

		if (empty($movies)) { 	// Fetch from remote api
			return $this->serializer->response('User has no movies in his list');
		}

		$movieApi = new TmdbApi($em);
		$change = false;
		foreach ($movies as $movie) {
			if (empty($movie->getId())) {
				$change = true;
				$movie = $movieApi->getOneMovie($movie->getMovieId());
				$em->persist($movie[0]);
			}
		}
		if ($change) {
			$em->flush();

			// Now check again to join with users liked movies list
			if ($change) {
				$movies = $repMovies->findUsersMovieList($apiId, $userId);
			}
		}

		return $this->serializer->response($movies, 200, [], false, true, true);
	}

	/**
	 * @Route("/webMostPopular/page/{pageNumber}/{type}/{userId}", name="movies_get_most_popular_web", methods={"GET"}, requirements={"pageNumber"="\d+", "type"="\w", "userId"="\d+"})
	 * @param int $pageNumber
	 * @param int $type
	 * @param int $userId
	 * @return JsonResponse|Response
	 * @throws ClientExceptionInterface
	 * @throws ORMException
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function getMostPopularMoviesInWeb($pageNumber, $type, $userId = 0){
		$em = $this->getDoctrine()->getManager();
		$repMovies = $em->getRepository(Movies::class);
		$movies = $repMovies->findMostPopularInWeb(20, $pageNumber * 20 - 20, $userId, $type);

		$movieApi = new TmdbApi($em);
		$change = false;
		foreach ($movies as $movie) {
			if (empty($movie->getId())) {
				$change = true;
				$movie = $movieApi->getOneMovie($movie->getMovieId());
				$em->persist($movie[0]);
			}
		}
		if ($change) {
			$em->flush();

			// Now fetch filled list
			if ($change) {
				$movies = $repMovies->findMostPopularInWeb(20, $pageNumber * 20 - 20, $userId, $type);
			}
		}

		return $this->serializer->response($movies, 200, [], false, true, true);
	}

	/**
	 * @Route("/{id}/messages/{elementNumber}", name="movie_get_messages_list", methods={"GET"}, requirements={"id"="\d+", "elementNumber"="\d+"})
	 * @param int $id
	 * @param int $elementNumber
	 * @return Response
	 */
	public function getMovieMessages($id, $elementNumber)
	{
		return $this->forward('App\Controller\EntityController\MessagesController::getAllMovieMessages', [
			'movieId' => $id,
			'elementNumber' => $elementNumber,
		]);
	}

	/**
	 * @Route("/find", name="find_movie", methods={"POST"})
	 * @param int $userId
	 * @return JsonResponse|Response
	 * @throws ClientExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 * @throws ORMException
	 */
	public function findMovie(Request $request){
		$apiId = 1;

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

		$em = $this->getDoctrine()->getManager();
		$movieApi = new TmdbApi($em);
		$change = false;
		$movies = $movieApi->searchMovie($search);
		if (empty($movies)) {
			return $this->serializer->response("Nothing found!", Response::HTTP_NOT_FOUND);
		}

		return $this->serializer->response($movies, 200, [], false, true, true);
	}
}