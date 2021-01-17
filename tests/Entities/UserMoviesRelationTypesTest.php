<?php


namespace App\Tests\Entities;

use App\Entity\UsersMoviesRelationTypes;
use PHPUnit\Framework\TestCase;

class UserMoviesRelationTypesTest extends TestCase {
	public function testId() {
		$obj = new UsersMoviesRelationTypes();
		$obj->setId(5);
		$this->assertEquals(5, $obj->getId());
	}

	public function testName() {
		$obj = new UsersMoviesRelationTypes();
		$obj->setName('vardas');
		$this->assertEquals('vardas', $obj->getName());
	}
}