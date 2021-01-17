<?php

namespace App\Tests\Controllers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GenresControllerTest extends WebTestCase {
	public function testGenresGet() {
		$client = static::createClient();

		$client->request('GET', '/api/genres');

		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		$this->assertIsArray(json_decode($client->getResponse()->getContent()));
	}
}