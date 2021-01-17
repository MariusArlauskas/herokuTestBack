<?php

namespace App\Tests\Entities;

use App\Entity\Movies;
use PHPUnit\Framework\TestCase;

class MoviesTest extends TestCase {
	public function testConstructor() {
		$dataArray = [
			'id' => 3,
			'title' => 'Fox king',
			'author' => 'John wick',
			'releaseDate' => '2020-05-15 10:11:12',
			'overview' => 'Text for overview',
			'posterPath' => 'http://kelias/',
			'originalTitle' => 'Fox king',
			'rating' => 8,
			'apiId' => 1,
			'movieId' => 2,
			'genres' => ['Action', 'Romance'],
			'mostPopular' => 4,
			'topRated' => 8,
			'upcoming' => 16,
			'latest' => 32,
			'nowPlaying' => 5,
		];
		$obj = new Movies($dataArray);

		$this->assertEquals(3, $obj->getId());
		$this->assertEquals('Fox king', $obj->getTitle());
		$this->assertEquals('John wick', $obj->getAuthor());
		$this->assertEquals('Text for overview', $obj->getOverview());
		$this->assertEquals('http://kelias/', $obj->getPosterPath());
		$this->assertEquals('Fox king', $obj->getOriginalTitle());
		$this->assertEquals(8, $obj->getRating());
		$this->assertEquals(1, $obj->getApiId());
		$this->assertEquals(2, $obj->getMovieId());
		$this->assertEquals(['Action', 'Romance'], $obj->getGenres());
		$this->assertEquals(4, $obj->getMostPopular());
		$this->assertEquals(8, $obj->getTopRated());
		$this->assertEquals(16, $obj->getUpcoming());
		$this->assertEquals(32, $obj->getLatest());
		$this->assertEquals(5, $obj->getNowPlaying());
	}

	public function testId() {
		$obj = new Movies();
		$obj->setId(5);
		$this->assertEquals(5, $obj->getId());
	}

	public function testTitle() {
		$obj = new Movies();
		$obj->setTitle('Nine tails of fox');
		$this->assertEquals('Nine tails of fox', $obj->getTitle());
	}

	public function testAuthor() {
		$obj = new Movies();
		$obj->setAuthor('John wick');
		$this->assertEquals('John wick', $obj->getAuthor());
	}

	public function testReleaseDate() {
		$obj = new Movies();
		$date = new \DateTime();
		$obj->setReleaseDate($date);
		$this->assertEquals($date, $obj->getReleaseDate());
	}

	public function testOverview() {
		$obj = new Movies();
		$obj->setOverview('Text for overview');
		$this->assertEquals('Text for overview', $obj->getOverview());
	}

	public function testPosterPath() {
		$obj = new Movies();
		$obj->setPosterPath('https://xxx.xxx.xxx.xxx/kelias');
		$this->assertEquals('https://xxx.xxx.xxx.xxx/kelias', $obj->getPosterPath());
	}

	public function testOriginalTitle() {
		$obj = new Movies();
		$obj->setOriginalTitle('Nine tails of fox');
		$this->assertEquals('Nine tails of fox', $obj->getOriginalTitle());
	}

	public function testRating() {
		$obj = new Movies();
		$obj->setRating(7);
		$this->assertEquals(7, $obj->getRating());
	}

	public function testApiId() {
		$obj = new Movies();
		$obj->setApiId(7);
		$this->assertEquals(7, $obj->getApiId());
	}

	public function testMovieId() {
		$obj = new Movies();
		$obj->setMovieId(7);
		$this->assertEquals(7, $obj->getMovieId());
	}

	public function testGenres() {
		$obj = new Movies();
		$arr = ['Action', 'Romance'];
		$obj->setGenres($arr);
		$this->assertEquals($arr, $obj->getGenres());
	}

	public function testToArray() {
		$dataArray = [
			'id' => 3,
			'title' => 'Fox king',
			'author' => 'John wick',
			'releaseDate' => '2020-05-15',
			'overview' => 'Text for overview',
			'posterPath' => 'http://kelias/',
			'originalTitle' => 'Fox king',
			'rating' => 8,
			'apiId' => 1,
			'movieId' => 2,
			'genres' => ['Action', 'Romance'],
			'mostPopular' => 4,
			'topRated' => 8,
			'upcoming' => 16,
			'latest' => 32,
			'nowPlaying' => 5,
		];
		$obj = new Movies($dataArray);
		$obj = $obj->toArray();

		$this->assertEquals(3, $obj['id']);
		$this->assertEquals('Fox king', $obj['title']);
		$this->assertEquals('John wick', $obj['author']);
		$this->assertEquals('Text for overview', $obj['overview']);
		$this->assertEquals('2020-05-15', $obj['releaseDate']);
		$this->assertEquals('http://kelias/', $obj['posterPath']);
		$this->assertEquals('Fox king', $obj['originalTitle']);
		$this->assertEquals(8, $obj['rating']);
		$this->assertEquals(1, $obj['apiId']);
		$this->assertEquals(2, $obj['movieId']);
		$this->assertEquals('Action, Romance', $obj['genres']);
		$this->assertEquals(4, $obj['mostPopular']);
		$this->assertEquals(8, $obj['topRated']);
		$this->assertEquals(16, $obj['upcoming']);
		$this->assertEquals(32, $obj['latest']);
		$this->assertEquals(5, $obj['nowPlaying']);
	}

	public function testMostPopular() {
		$obj = new Movies();
		$obj->setMostPopular(7);
		$this->assertEquals(7, $obj->getMostPopular());
	}

	public function testTopRated() {
		$obj = new Movies();
		$obj->setTopRated(70);
		$this->assertEquals(70, $obj->getTopRated());
	}

	public function testUpcoming() {
		$obj = new Movies();
		$obj->setUpcoming(45);
		$this->assertEquals(45, $obj->getUpcoming());
	}

	public function testLatest() {
		$obj = new Movies();
		$obj->setLatest(1);
		$this->assertEquals(1, $obj->getLatest());
	}

	public function testNowPlaying() {
		$obj = new Movies();
		$obj->setNowPlaying(70);
		$this->assertEquals(70, $obj->getNowPlaying());
	}
}