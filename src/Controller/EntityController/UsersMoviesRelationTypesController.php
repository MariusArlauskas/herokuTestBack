<?php

namespace App\Controller\EntityController;

use App\Controller\InitSerializer;
use App\Entity\UsersMoviesRelationTypes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UsersMoviesRelationTypesController
 * @package App\Controller
 * @Route("/lists/types")
 */
class UsersMoviesRelationTypesController extends AbstractController
{
	protected $serializer;

	public function __construct()
	{
		$this->serializer = new InitSerializer();
	}

	/** Get top rated movies
	 * @Route("", name="movie_add_types_get", methods={"GET"})
	 */
	public function getAllAction() {
		$em = $this->getDoctrine()->getManager();
		$repMoviesAddTypes = $em->getRepository(UsersMoviesRelationTypes::class);
		$moviesAddTypes = $repMoviesAddTypes->findAll();

		return $this->serializer->response($moviesAddTypes, 200, [], true);
	}
}