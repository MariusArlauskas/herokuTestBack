<?php

namespace App\Tests\Controllers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UsersMoviesRelationsTypesControllerTest extends WebTestCase {
	public function testId() {
		$client = static::createClient();

		$client->setServerParameters([]);
		$client->request('GET', '/api/lists/types');

		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		$this->assertIsArray(json_decode($client->getResponse()->getContent()));
	}
}