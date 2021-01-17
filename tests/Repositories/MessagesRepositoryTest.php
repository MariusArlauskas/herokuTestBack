<?php


namespace App\Tests\Repositories;

use App\Entity\Messages;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MessagesRepositoryTest extends KernelTestCase {
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

	public function testFindMessagesSortedByDate()
	{
		$results = $this->entityManager
			->getRepository(Messages::class)
			->findMessagesSortedByDate(20, 0, 0)
		;

		$this->assertNotEmpty($results);
		foreach ($results as $key => $value) {
			if (!empty($lastKey)) {
				$this->assertGreaterThanOrEqual($results[$key]['postDate'], $results[$lastKey]['postDate']);
			}
			$lastKey = $key;
		}
	}

	public function testFindMovieMessagesSortedByDate()
	{
		$results = $this->entityManager
			->getRepository(Messages::class)
			->findMovieMessagesSortedByDate(20, 0, 0)
		;

		$this->assertNotEmpty($results);
		foreach ($results as $key => $value) {
			if (!empty($lastKey)) {
				$this->assertGreaterThanOrEqual($results[$key]['postDate'], $results[$lastKey]['postDate']);
			}
			$lastKey = $key;
		}
	}

	public function testFindMessagesCommentsSortedByDate()
	{
		$results = $this->entityManager
			->getRepository(Messages::class)
			->findMessagesCommentsSortedByDate([30])
		;

		$this->assertNotEmpty($results);
		foreach ($results as $key => $value) {
			if (!empty($lastKey)) {
				$this->assertGreaterThanOrEqual($results[$key]['postDate'], $results[$lastKey]['postDate']);
			}
			$lastKey = $key;
		}
	}

	public function testFindMovieMessagesCommentsSortedByDate()
	{
		$results = $this->entityManager
			->getRepository(Messages::class)
			->findMovieMessagesCommentsSortedByDate([30])
		;

		$this->assertNotEmpty($results);
		foreach ($results as $key => $value) {
			if (!empty($lastKey)) {
				$this->assertGreaterThanOrEqual($results[$key]['postDate'], $results[$lastKey]['postDate']);
			}
			$lastKey = $key;
		}
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