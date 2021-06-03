<?php

namespace App\Entity;

use App\Repository\UsersFollowersRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UsersFollowersRepository::class)
 * @ORM\Table(indexes={@ORM\Index(name="index", columns={"user_id", "followed_user_id"})})
 */
class UsersFollowers
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
     * @ORM\Column(type="integer")
     */
    private $followedUserId;

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

    public function getFollowedUserId(): ?int
    {
        return $this->followedUserId;
    }

    public function setFollowedUserId(int $followedUserId): self
    {
        $this->followedUserId = $followedUserId;

        return $this;
    }
}
