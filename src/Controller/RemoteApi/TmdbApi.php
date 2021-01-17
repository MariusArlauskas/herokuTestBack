<?php

namespace App\Controller\RemoteApi;

use App\Entity\Apis;
use App\Entity\Genres;
use App\Entity\Movies;
use App\Repository\GenresRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class TmdbApi
 * @package App\Controller
 */
class TmdbApi extends AbstractController
{
    /** Tmdb Api key
     * @var string
     */
    protected $apiKey = '';


	/**	Id in database
	 * @var int
	 */
	protected $apiId = 1;

	/**
	 * @var EntityManager
	 */
	protected $em;

	public function __construct(EntityManagerInterface $em)
	{
		$api = $em->getRepository(Apis::class);
		$this->apiKey = $api->find($this->apiId)->getApiKey();
		$this->em = $em;
	}

	public function getMovies($type, $page, $nr) {
		$function = $type.'Movies';
		return $this->$function($page, $nr);
	}

	/**
	 * @return mixed
	 * @throws ClientExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 * @throws ORMException
	 */
    protected function mostPopularMovies($page, $nr) {
        $client = HttpClient::create();
        return $this->returnMovies($client->request('GET', 'https://api.themoviedb.org/3/movie/popular?api_key='.$this->apiKey.'&language=en-US&page='.$page)->getContent(), 'MostPopular', $nr);
    }

	/**
	 * @return mixed
	 * @throws ClientExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 * @throws ORMException
	 */
	protected function topRatedMovies($page, $nr) {
		$client = HttpClient::create();
		return $this->returnMovies($client->request('GET', 'https://api.themoviedb.org/3/movie/top_rated?api_key='.$this->apiKey.'&language=en-US&page='.$page)->getContent(), 'TopRated', $nr);
	}

	/**
	 * @return mixed
	 * @throws ClientExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 * @throws ORMException
	 */
	protected function upcomingMovies($page, $nr) {
		$client = HttpClient::create();
		return $this->returnMovies($client->request('GET', 'https://api.themoviedb.org/3/movie/upcoming?api_key='.$this->apiKey.'&language=en-US&page='.$page)->getContent(), 'Upcoming', $nr);
	}

	/**
	 * @return mixed
	 * @throws ClientExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 * @throws ORMException
	 */
	protected function latestMovies($page, $nr) {
		$client = HttpClient::create();
		return $this->returnMovies($client->request('GET', 'https://api.themoviedb.org/3/movie/latest?api_key='.$this->apiKey.'&language=en-US&page='.$page)->getContent(), 'Latest', $nr);
	}

	/**
	 * @return mixed
	 * @throws ClientExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 * @throws ORMException
	 */
	protected function nowPlayingMovies($page, $nr) {
		$client = HttpClient::create();
		return $this->returnMovies($client->request('GET', 'https://api.themoviedb.org/3/movie/now_playing?api_key='.$this->apiKey.'&language=en-US&page='.$page)->getContent(), 'NowPlaying', $nr);
	}

    /**
     * @return mixed
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getMovieGenresFromApi() {
        $client = HttpClient::create();
        return json_decode($client->request('GET', 'https://api.themoviedb.org/3/genre/movie/list?api_key='.$this->apiKey.'&language=en-US')->getContent());
    }

	/**
	 * @param $movieId
	 * @return mixed
	 * @throws ClientExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 * @throws ORMException
	 */
    public function getOneMovie($movieId) {
    	$client = HttpClient::create();
    	return $this->returnMovies($client->request('GET', 'https://api.themoviedb.org/3/movie/'.$movieId.'?api_key='.$this->apiKey.'&language=en-US')->getContent());
	}

	/**
	 * @param $search
	 * @return array
	 * @throws ClientExceptionInterface
	 * @throws ORMException
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function searchMovie($search) {
		$client = HttpClient::create();
		return $this->returnMovies($client->request('GET', 'https://api.themoviedb.org/3/search/movie?api_key='.$this->apiKey.'&language=en-US&query='.htmlentities($search))->getContent());
	}

	/**
	 * @param $movies
	 * @param int $type
	 * @param int $nr
	 * @return array
	 * @throws ORMException
	 */
    protected function returnMovies($movies, $type = 0, $nr = 0) {
		$movies = json_decode($movies);
		if (empty($movies->results)) {	// Then its only one movie
			if (empty($movies->genres)) {
				return [];
			}
			$tmpArr = [];
			foreach ($movies->genres as $genre) {
				if (!empty($genre)) {
					array_push($tmpArr, $genre->id);
				}
			}
			$movies->genre_ids = $tmpArr;
			$movies->results[0] = $movies;
		}

		$genres = $this->getMovieGenres();
		// Converting genre_ids to genre names and adding for saving
		$moviesReturn = [];
		foreach ($movies->results as $id => $movie) {
			// Data to movie object for saving
			$temp = new Movies();
			$temp->setApiId($this->apiId);
			$temp->setMovieId($movie->id);
			$temp->setRating($movie->vote_average);
			$temp->setOriginalTitle($movie->original_title);
			if (!empty($movie->poster_path)) {
				$temp->setPosterPath('https://image.tmdb.org/t/p/w600_and_h900_bestv2'.$movie->poster_path);
			}
			$temp->setOverview($movie->overview);
			if (!empty($movie->release_date)) {
				$temp->setReleaseDate(\DateTime::createFromFormat('Y-m-d', $movie->release_date));
			}
			$temp->setTitle($movie->title);
			if (!empty($type)) {
				$temp->{'set'.$type}($nr + $id + 1);
			}
			// From genres_ids to names
			$tmpArr = [];
			foreach ($movie->genre_ids as $genre_id) {
				if (empty($genres[$genre_id])) {
					$genres = $this->getMovieGenres(true);
				}
				 array_push($tmpArr, $genres[$genre_id]);
			}
			$temp->setGenres($tmpArr);

			$moviesReturn[] = $temp;
		}

		return $moviesReturn;
	}

	/**
	 * @param bool $refresh Fetch from api and update db
	 * @return array
	 * @throws ClientExceptionInterface
	 * @throws ORMException
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 * @throws OptimisticLockException
	 */
	protected function getMovieGenres($refresh = false) {
		if ($refresh) {
			$genres = $this->getMovieGenresFromApi();
			$tmp = [];
			foreach ($genres->genres as $genre) {
				$item = new Genres();
				$item->setApiId($this->apiId);
				$item->setGenreId($genre->id);
				$item->setName($genre->name);
				$this->em->persist($item);

				$tmp[$genre->id] = $genre->name;
			}
			$this->em->flush();
			return $tmp;
		}
		$genres = $this->em->getRepository(Genres::class)->findBy(['apiId' => $this->apiId]);
		$tmp = [];
		foreach ($genres as $genre) {
			$tmp[$genre->getGenreId()] = $genre->getName();
		}
		return $tmp;
	}
}