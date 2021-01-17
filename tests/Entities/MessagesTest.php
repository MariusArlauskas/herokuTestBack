<?php

namespace App\Tests\Entities;

use App\Entity\Messages;
use PHPUnit\Framework\TestCase;

class MessagesTest extends TestCase {
	public function testId() {
		$obj = new Messages();
		$obj->setId(5);
		$this->assertEquals(5, $obj->getId());
	}

	public function testMovieId() {
		$obj = new Messages();
		$obj->setMovieId(5256);
		$this->assertEquals(5256, $obj->getMovieId());
	}

	public function testParentId() {
		$obj = new Messages();
		$obj->setParentId(54);
		$this->assertEquals(54, $obj->getParentId());
	}

	public function testMessage() {
		$obj = new Messages();
		$obj->setMessage('Lorem text to test message');
		$this->assertEquals('Lorem text to test message', $obj->getMessage());
	}

	public function testPostDate() {
		$obj = new Messages();
		$date = new \DateTime();
		$obj->setPostDate($date);
		$this->assertEquals($date, $obj->getPostDate());
	}

	public function testSharedApiId() {
		$obj = new Messages();
		$obj->setSharedApiId(7);
		$this->assertEquals(7, $obj->getSharedApiId());
	}

	public function testUserId() {
		$obj = new Messages();
		$obj->setUserId(7);
		$this->assertEquals(7, $obj->getUserId());
	}

	public function testConstructor() {
		$dataArray = [
			'id' => 3,
			'userId' => 5,
			'movieId' => 10,
			'message' => 'Text for message',
			'postDate' => '2020-05-15 10:11:12',
			'parentId' => 20,
			'sharedApiId' => 40,
		];
		$obj = new Messages($dataArray);

		$this->assertEquals(3, $obj->getId());
		$this->assertEquals(5, $obj->getUserId());
		$this->assertEquals(10, $obj->getMovieId());
		$this->assertEquals('Text for message', $obj->getMessage());
		$date = \DateTime::createFromFormat('Y-m-d H:i:s', '2020-05-15 10:11:12');
		$this->assertEquals($date, $obj->getPostDate());
		$this->assertEquals(20, $obj->getParentId());
		$this->assertEquals(40, $obj->getSharedApiId());
	}

	public function testToArray() {
		$dataArray = [
			'id' => 3,
			'userId' => 5,
			'movieId' => 10,
			'message' => 'Text for message',
			'postDate' => '2020-05-15 10:11:12',
			'parentId' => 20,
			'sharedApiId' => 40,
		];
		$obj = new Messages($dataArray);
		$obj = $obj->toArray();
		$this->assertIsArray($obj);
		$this->assertEquals(3, $obj['id']);
		$this->assertEquals(5, $obj['userId']);
		$this->assertEquals(10, $obj['movieId']);
		$this->assertEquals('Text for message', $obj['message']);
		$this->assertEquals('2020-05-15', $obj['postDate']);
		$this->assertEquals(20, $obj['parentId']);
		$this->assertEquals(40, $obj['sharedApiId']);
	}
}