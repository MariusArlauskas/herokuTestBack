<?php

namespace App\Controller\EntityController;

use App\Controller\InitSerializer;
use App\Entity\Genres;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class GenresController
 * @package App\Controller
 * @Route("/genres")
 */
class GenresController extends AbstractController
{
	protected $serializer;

	public function __construct()
	{
		$this->serializer = new InitSerializer();
	}

	/** Get top rated movies
	 * @Route("", name="genres_get", methods={"GET"})
	 */
	public function getAllAction() {
		$em = $this->getDoctrine()->getManager();
		$repGenres = $em->getRepository(Genres::class);
		$genres = $repGenres->findAll();

		return $this->serializer->response($genres, 200, [], true);
	}
}