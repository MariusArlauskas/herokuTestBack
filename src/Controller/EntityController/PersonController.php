<?php

namespace App\Controller\EntityController;

use App\Controller\InitSerializer;
use App\Controller\RemoteApi\TmdbApi;
use App\Entity\Person;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PeopleController
 * @package App\Controller\EntityController
 * @Route("/person")
 */
class PersonController extends AbstractController
{
	protected $serializer;

	public function __construct()
	{
		$this->serializer = new InitSerializer();
	}

	/**
	 * @Route("/{id}", name="person_show_one", methods={"GET"}, requirements={"id"="\d+"})
	 * @param int $id
	 * @return JsonResponse
	 */
	public function getOneAction($id) {
		$em = $this->getDoctrine()->getManager();
		$repPerson = $em->getRepository(Person::class);
		$person = $repPerson->findOneByPersonId($id);
		if (empty($person)) { 	// Fetch from remote api
			$movieApi = new TmdbApi($em);
			$person = $movieApi->getOnePerson($id)[0];
			if (empty($person)) {
				return $this->serializer->response("Nothing found!", Response::HTTP_NOT_FOUND);
			}

			// Add person to Doctrine so that it can be saved
			$em->persist($person);
			// Save people
			$em->flush();

			$movies = $movieApi->getPersonMovies($id);
			$dataToSave = [];
			foreach ($movies->crew as $movie) {
				$tempEl = [];
				if (isset($movie->poster_path)) {
					$tempEl['poster_path'] = 'https://image.tmdb.org/t/p/w600_and_h900_bestv2'.$movie->poster_path;
				}
				$tempEl['original_title'] = $movie->original_title ?? '';
				$tempEl['job'] = $movie->job ?? '';
				$tempEl['id'] = $movie->id ?? '';
				$dataToSave[] = $tempEl;
			}
			$repPerson->savePersonMovies($id, $dataToSave);
			$person->setMovies($dataToSave);
		}

		return $this->serializer->response($person, 200, [], false, true, true);
	}

	/**
	 * @Route("/find", name="find_movie", methods={"POST"})
	 * @param Request $request
	 * @return JsonResponse|Response
	 */
	public function findPerson(Request $request) {
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
		$people = $movieApi->searchPeople($search);
		if (empty($people)) {
			return $this->serializer->response("Nothing found!", Response::HTTP_NOT_FOUND);
		}

		return $this->serializer->response($people, 200, [], false, true);
	}
}