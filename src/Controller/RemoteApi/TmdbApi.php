<?php

namespace App\Controller\RemoteApi;

use App\Entity\Apis;
use App\Entity\Genres;
use App\Entity\Movies;
use App\Entity\Person;
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
	 */
    protected function mostPopularMovies($page, $nr) {
        $client = HttpClient::create();
        return $this->returnMovies($client->request('GET', 'https://api.themoviedb.org/3/movie/popular?api_key='.$this->apiKey.'&language=en-US&page='.$page)->getContent(), 'MostPopular', $nr);
    }

	/**
	 * @return mixed
	 */
	protected function topRatedMovies($page, $nr) {
		$client = HttpClient::create();
		return $this->returnMovies($client->request('GET', 'https://api.themoviedb.org/3/movie/top_rated?api_key='.$this->apiKey.'&language=en-US&page='.$page)->getContent(), 'TopRated', $nr);
	}

	/**
	 * @return mixed
	 */
	protected function upcomingMovies($page, $nr) {
		$client = HttpClient::create();
		return $this->returnMovies($client->request('GET', 'https://api.themoviedb.org/3/movie/upcoming?api_key='.$this->apiKey.'&language=en-US&page='.$page)->getContent(), 'Upcoming', $nr);
	}

	/**
	 * @return mixed
	 */
	protected function latestMovies($page, $nr) {
		$client = HttpClient::create();
		return $this->returnMovies($client->request('GET', 'https://api.themoviedb.org/3/movie/latest?api_key='.$this->apiKey.'&language=en-US&page='.$page)->getContent(), 'Latest', $nr);
	}

	/**
	 * @return mixed
	 */
	protected function nowPlayingMovies($page, $nr) {
		$client = HttpClient::create();
		return $this->returnMovies($client->request('GET', 'https://api.themoviedb.org/3/movie/now_playing?api_key='.$this->apiKey.'&language=en-US&page='.$page)->getContent(), 'NowPlaying', $nr);
	}

    /**
     * @return mixed
     */
    public function getMovieGenresFromApi() {
        $client = HttpClient::create();
        return json_decode($client->request('GET', 'https://api.themoviedb.org/3/genre/movie/list?api_key='.$this->apiKey.'&language=en-US')->getContent());
    }

	/**
	 * @param int $movieId
	 * @return mixed
	 */
    public function getOneMovie($movieId) {
    	$client = HttpClient::create();
    	return $this->returnMovies($client->request('GET', 'https://api.themoviedb.org/3/movie/'.$movieId.'?api_key='.$this->apiKey.'&language=en-US')->getContent());
	}

	/**
	 * @param int $movieId
	 * @return mixed
	 */
    public function getMovieCredits($movieId) {
    	$client = HttpClient::create();
    	$response = $client->request('GET', 'https://api.themoviedb.org/3/movie/'.$movieId.'/credits?api_key='.$this->apiKey.'&language=en-US')->getContent();
		return json_decode($response);
	}

	/**
	 * @param int $personId
	 * @return mixed
	 */
	public function getOnePerson($personId) {
		$client = HttpClient::create();
		return $this->returnPeople($client->request('GET', 'https://api.themoviedb.org/3/person/'.$personId.'?api_key='.$this->apiKey.'&language=en-US')->getContent());
	}

	/**
	 * @param string $search
	 * @return array
	 */
	public function searchMovie($search) {
		$client = HttpClient::create();
		return $this->returnMovies($client->request('GET', 'https://api.themoviedb.org/3/search/movie?api_key='.$this->apiKey.'&language=en-US&query='.htmlentities($search))->getContent());
	}

	/**
	 * @param string $search
	 * @return array
	 */
	public function searchPeople($search) {
		$client = HttpClient::create();
		return $this->returnPeople($client->request('GET', 'https://api.themoviedb.org/3/search/person?api_key='.$this->apiKey.'&language=en-US&query='.htmlentities($search))->getContent(), true);
	}

	/**
	 * @param integer $id
	 * @return array
	 */
	public function getPersonMovies($id) {
		$client = HttpClient::create();
		$response = $client->request('GET', 'https://api.themoviedb.org/3/person/'.intval($id).'/movie_credits?api_key='.$this->apiKey.'&language=en-US')->getContent();
		return json_decode($response);
	}

	/**
	 * @param string $movies
	 * @param int $type
	 * @param int $nr
	 * @return array
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

			if (isset($movie->vote_average)) {
				$temp->setRating($movie->vote_average);
				$repMovies = $this->em->getRepository(Movies::class);
				$movieScore = $repMovies->getScoreByMovieId($movie->id);
				if (!empty($movieScore)) {
					$temp->setRating(
						round(($movie->vote_count * $movie->vote_average + $movieScore['voteCount'] * $movieScore['voteAverage'])/($movieScore['voteCount'] + $movie->vote_count), 1)
					);
				}
			}

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
	 * @param $movies
	 * @param int $type
	 * @param int $nr
	 * @return array
	 * @throws ORMException
	 */
	protected function returnPeople($people, $dontSave = false) {
		$people = json_decode($people);
		if (empty($people->results)) {	// Then its only one person
			$people->results[0] = $people;
		}
		if ($dontSave) {
			$returnArr = [];
			foreach ($people->results as $person) {
				if (!empty($person->profile_path)) {
					$person->profile_path = 'https://image.tmdb.org/t/p/w600_and_h900_bestv2'.$person->profile_path;
				}
				$returnArr[] = $person;
			}
			return $returnArr;
		}

		$peopleReturn = [];
		foreach ($people->results as $person) {
			// Data to person object for saving
			$temp = new Person();
			$temp->setApiId($this->apiId);
			$temp->setPersonId($person->id);
			if (!empty($person->birthday)) {
				$temp->setBirthday(\DateTime::createFromFormat('Y-m-d', $person->birthday));
			}
			if (!empty($person->deathday)) {
				$temp->setDeathday(\DateTime::createFromFormat('Y-m-d', $person->deathday));
			}
			if (!empty($person->gender)) {
				$temp->setGender($person->gender);
			}
			if (!empty($person->known_for_department)) {
				$temp->setKnownFor($person->known_for_department);
			}
			if (!empty($person->name)) {
				$temp->setName($person->name);
			}
			if (!empty($person->place_of_birth)) {
				$temp->setBirthPlace($person->place_of_birth);
			}
			if (!empty($person->profile_path)) {
				$temp->setPicture('https://image.tmdb.org/t/p/w600_and_h900_bestv2'.$person->profile_path);
			}
			if (!empty($person->biography)) {
				$temp->setBiography($person->biography);
			}
			$peopleReturn[] = $temp;
		}

		return $peopleReturn;
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