<?php


namespace App\Tests\Repositories;

use App\Entity\Movies;
use App\Entity\UsersMovies;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MoviesRepositoryTest extends KernelTestCase {
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $entityManager;

	/**
	 * {@inheritDoc}
	 */
	protected function setUp()
	{
		$kernel = self::bootKernel();

		$this->entityManager = $kernel->getContainer()
			->get('doctrine')
			->getManager();
	}

	public function testGetMaxNumberByType()
	{
		$results = $this->entityManager
			->getRepository(Movies::class)
			->getMaxNumberByType(1, 'mostPopular')
		;

		$this->assertNotEmpty($results[0]['mostPopular']);
	}

	public function testFindByApiIdAndTypeWithUserStatuses()
	{
		$results = $this->entityManager
			->getRepository(Movies::class)
			->findByApiIdAndTypeWithUserStatuses(1, 5, 'mostPopular', 20, 0, [['name' => 'year', 'sort' => [1970, 2010]]])
		;

		$this->assertNotEmpty($results);
		foreach ($results as $key => $value) {
			if (!empty($lastKey)) {
				$this->assertGreaterThanOrEqual(1970, $results[$key]->getReleaseDate()->format('Y'));
				$this->assertLessThanOrEqual(2010, $results[$key]->getReleaseDate()->format('Y'));
			}
			$lastKey = $key;
		}
	}

	public function testFindUsersMovieList()
	{
		$uid = 5;
		$results = $this->entityManager
			->getRepository(Movies::class)
			->findUsersMovieList(1, $uid)
		;

		$results2 = $this->entityManager
			->getRepository(UsersMovies::class)
			->findBy(['userId' => $uid])
		;

		$mids = [];
		foreach ($results2 as $key => $value) {
			$mids[] = $value->getMovieId();
		}

		$this->assertNotEmpty($results);
		foreach ($results as $value) {
			$this->assertContains($value->getMovieId(), $mids);
		}
	}

	public function testFindByApi()
	{
		$results = $this->entityManager
			->getRepository(Movies::class)
			->findByApi(1, 'mostPopular', 20, 0, [['name' => 'year', 'sort' => [1970, 2010]]])
		;

		$this->assertNotEmpty($results);
		foreach ($results as $value) {
			$this->assertEquals(1, $value->getApiId());
		}
	}

	public function testFindMostPopularInWeb()
	{
		$results = $this->entityManager
			->getRepository(Movies::class)
			->findMostPopularInWeb(100, 0, 0, 'M')
		;

		$this->assertNotEmpty($results);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function tearDown()
	{
		parent::tearDown();

		$this->entityManager->close();
		$this->entityManager = null; // avoid memory leaks
	}
}