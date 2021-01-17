<?php

namespace App\Tests\Entities;

use App\Entity\Users;
use PHPUnit\Framework\TestCase;

class UsersTest extends TestCase {
	public function testId() {
		$obj = new Users();
		$obj->setId(5);
		$this->assertEquals(5, $obj->getId());
	}

	public function testEmail() {
		$obj = new Users();
		$obj->setEmail('bandymas@mail.com');
		$this->assertEquals('bandymas@mail.com', $obj->getEmail());
	}

	public function testRoles() {
		$obj = new Users();
		$obj->setRoles(['ROLE_USER']);
		$this->assertEquals(['ROLE_USER'], $obj->getRoles());
	}

	public function testPassword() {
		$obj = new Users();
		$pass = password_hash('slaptas', PASSWORD_DEFAULT);
		$obj->setPassword($pass);
		$this->assertEquals($pass, $obj->getPassword());
	}

	public function testName() {
		$obj = new Users();
		$obj->setName('Vardas');
		$this->assertEquals('Vardas', $obj->getName());
	}

	public function testProfilePicture() {
		$obj = new Users();
		$obj->setProfilePicture('files/pavadinimas.png');
		$this->assertEquals('files/pavadinimas.png', $obj->getProfilePicture());
	}

	public function testDescription() {
		$obj = new Users();
		$obj->setDescription('profilio apibuinimas');
		$this->assertEquals('profilio apibuinimas', $obj->getDescription());
	}

	public function testBirthDate() {
		$obj = new Users();
		$date = new \DateTime();
		$obj->setBirthDate($date);
		$this->assertEquals($date, $obj->getBirthDate());
	}

	public function testToArray() {
		$obj = new Users();
		$obj->setId(1);
		$obj->setEmail('mail@mail.com');
		$obj->setRoles(['ROLE_USER']);
		$obj->setName('vardas');
		$obj->setProfilePicture('kelias/paveikslelis.png');
		$obj->setDescription('apibuinimas');
		$date = new \DateTime();
		$obj->setBirthDate($date);
		$obj->setRegisterDate($date);
		$obj->setChatBannedUntil($date);

		$obj = $obj->toArray();

		$this->assertEquals(1, $obj['id']);
		$this->assertEquals('mail@mail.com', $obj['email']);
		$this->assertEquals(['ROLE_USER'], $obj['roles']);
		$this->assertEquals('vardas', $obj['name']);
		$this->assertEquals('kelias/paveikslelis.png', $obj['profilePicture']);
		$this->assertEquals('apibuinimas', $obj['description']);
		$this->assertEquals($date, $obj['birthDate']);
		$this->assertEquals($date, $obj['registerDate']);
		$this->assertEquals($date, $obj['chatBannedUntil']);
	}

	public function testRegisterDate() {
		$obj = new Users();
		$date = new \DateTime();
		$obj->setRegisterDate($date);
		$this->assertEquals($date, $obj->getRegisterDate());
	}

	public function testChatBannedUntil() {
		$obj = new Users();
		$date = new \DateTime();
		$obj->setChatBannedUntil($date);
		$this->assertEquals($date, $obj->getChatBannedUntil());
	}
}