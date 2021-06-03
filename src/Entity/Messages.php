<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessagesRepository")
 * @ORM\Table(indexes={@ORM\Index(name="index", columns={"user_id", "movie_id", "parent_id", "shared_api_id"})})
 */
class Messages
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $userId;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $movieId;

    /**
     * @ORM\Column(type="text")
     */
    private $message;

    /**
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $postDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $parentId;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $forumId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sharedApiId;

	public function __construct($arr = [])
	{
		if (!empty($arr)) {
			// Initializing class properties
			foreach($arr as $property => $value) {
				if ($property == 'postDate') {
					$value = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
				}
				$this->$property = $value;
			}
		}
	}

    public function getId(): ?int
    {
        return $this->id;
    }

	public function setId(int $id): self
	{
		$this->id = $id;

		return $this;
	}

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

	public function getMovieId(): ?int
	{
		return $this->movieId;
	}

	public function setMovieId(int $movieId): self
	{
		$this->movieId = $movieId;

		return $this;
	}

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getPostDate(): ?\DateTimeInterface
    {
        return $this->postDate;
    }

    public function setPostDate(\DateTimeInterface $postDate): self
    {
        $this->postDate = $postDate;

        return $this;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setParentId(?int $parentId): self
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function getForumId(): ?int
    {
        return $this->forumId;
    }

    public function setForumId(?int $forumId): self
    {
        $this->forumId = $forumId;

        return $this;
    }

    public function getSharedApiId(): ?int
    {
        return $this->sharedApiId;
    }

    public function setSharedApiId(?int $sharedApiId): self
    {
        $this->sharedApiId = $sharedApiId;

        return $this;
    }

	public function toArray() {
		$vars = get_object_vars ( $this );
		$array = array ();
		foreach ( $vars as $key => $value ) {
			switch ($key) {
				case 'postDate':
					if (!empty($value)) {
						$value = $value->format('Y-m-d');
					} else {
						$value = '';
					}
					break;
			}
			$array [ltrim ( $key, '_' )] = $value;
		}
		if (empty($array['children'])) {
			$array['children'] = [];
		}
		return $array;
	}
}
