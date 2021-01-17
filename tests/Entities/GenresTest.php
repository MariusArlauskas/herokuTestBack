<?php


namespace App\Tests\Entities;

use App\Entity\Genres;
use PHPUnit\Framework\TestCase;

class GenresTest extends TestCase {
	public function testId() {
		$obj = new Genres();
		$obj->setId(5);
		$this->assertEquals(5, $obj->getId());
	}

	public function testApiId() {
		$obj = new Genres();
		$obj->setApiId(5);
		$this->assertEquals(5, $obj->getApiId());
	}

	public function testGenreId() {
		$obj = new Genres();
		$obj->setGenreId(5);
		$this->assertEquals(5, $obj->getGenreId());
	}

	public function testName() {
		$obj = new Genres();
		$obj->setName('action');
		$this->assertEquals('action', $obj->getName());
	}
}