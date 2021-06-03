<?php

namespace App\Entity;

use App\Repository\ForumRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ForumRepository::class)
 * @ORM\Table(indexes={@ORM\Index(name="index", columns={"user_id"})})
 */
class Forum
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $userId;

    /**
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $message;

    /**
	 * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $postDate;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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
		return $array;
	}
}
