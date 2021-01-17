<?php

namespace App\Tests\Controllers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MoviesControllerTest extends WebTestCase {
	public function testMoviesGet() {
		$client = static::createClient();

		$client->setServerParameters([]);
		$client->request('POST', '/api/movies/mostPopular/page/1');

		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		$this->assertIsArray(json_decode($client->getResponse()->getContent()));
	}

	public function testMovieGet() {
		$client = static::createClient();

		$client->setServerParameters([]);
		$client->request('GET', '/api/movies/338762');

		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		$this->assertIsObject(json_decode($client->getResponse()->getContent()));
	}

	public function testMostPopularWeb() {
		$client = static::createClient();

		$client->setServerParameters([]);
		$client->request('GET', '/api/movies/webMostPopular/page/1/M/0');

		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		$this->assertIsArray(json_decode($client->getResponse()->getContent()));
	}
}