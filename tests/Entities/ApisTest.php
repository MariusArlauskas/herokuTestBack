<?php


namespace App\Tests\Entities;

use App\Entity\Apis;
use PHPUnit\Framework\TestCase;

class ApisTest extends TestCase {
	public function testId() {
		$obj = new Apis();
		$obj->setId(5);
		$this->assertEquals(5, $obj->getId());
	}

	public function testApiKey() {
		$obj = new Apis();
		$obj->setApiKey('asdasg549efsd15s5sf1s6');
		$this->assertEquals('asdasg549efsd15s5sf1s6', $obj->getApiKey());
	}

	public function testName() {
		$obj = new Apis();
		$obj->setName('action');
		$this->assertEquals('action', $obj->getName());
	}
}