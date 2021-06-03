<?php

namespace App\Tests\Controllers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UsersControllerTest extends WebTestCase {
//	public function testUserCreate() {
//		$client = static::createClient();
//
//		$client->setServerParameters([]);
//		$client->request(
//			'POST',
//			'/api/users',
//			[],
//			[],
//			['CONTENT_TYPE' => 'application/json'],
//			'{"email":"Vardas100@mail.com", "name":"vardas1", "birthDate":"2000-10-10", "password":"slaptas"}'
//		);
//
//		$this->assertEquals(200, $client->getResponse()->getStatusCode());
//		$this->assertIsString(json_decode($client->getResponse()->getContent()));
//	}

	public function testUserMovies() {
		$client = static::createClient();

		$client->setServerParameters([]);
		$client->request(
			'GET',
			'/api/users/5/movies'
		);

		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		$this->assertIsArray(json_decode($client->getResponse()->getContent()));
	}
}