<?php

namespace App\Tests\Entities;

use App\Entity\UsersMovies;
use PHPUnit\Framework\TestCase;

class UsersMoviesTest extends TestCase {
	public function testConstructor() {
		$obj = new UsersMovies();

		$this->assertNotEmpty($obj->getDateAdded());
	}

	public function testId() {
		$obj = new UsersMovies();
		$obj->setId(5);
		$this->assertEquals(5, $obj->getId());
	}

	public function testRelationTypeId() {
		$obj = new UsersMovies();
		$obj->setRelationTypeId(3);
		$this->assertEquals(3, $obj->getRelationTypeId());
	}

	public function testUserId() {
		$obj = new UsersMovies();
		$obj->setUserId(8);
		$this->assertEquals(8, $obj->getUserId());
	}

	public function testMovieId() {
		$obj = new UsersMovies();
		$obj->setMovieId(45668);
		$this->assertEquals(45668, $obj->getMovieId());
	}

	public function testApiId() {
		$obj = new UsersMovies();
		$obj->setApiId(4);
		$this->assertEquals(4, $obj->getApiId());
	}

	public function testIsFavorite() {
		$obj = new UsersMovies();
		$obj->setIsFavorite(true);
		$this->assertTrue($obj->getIsFavorite());
	}

	public function testUserRating() {
		$obj = new UsersMovies();
		$obj->setUserRating(5);
		$this->assertEquals(5, $obj->getUserRating());
	}

	public function testDateAdded() {
		$obj = new UsersMovies();
		$date = new \DateTime();
		$obj->setDateAdded($date);
		$this->assertEquals($date, $obj->getDateAdded());
	}
}