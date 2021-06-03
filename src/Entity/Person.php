<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PersonRepository::class)
 */
class Person
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
    private $apiId;

    /**
     * @ORM\Column(type="integer")
     */
    private $personId;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $birthday;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $deathday;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $gender;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $knownFor;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $birthPlace;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $picture;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $biography;

	/**
	 * @ORM\Column(type="blob", nullable=true)
	 */
	private $movies;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApiId(): ?int
    {
        return $this->apiId;
    }

    public function setApiId(int $apiId): self
    {
        $this->apiId = $apiId;

        return $this;
    }

    public function getPersonId(): ?int
    {
        return $this->personId;
    }

    public function setPersonId(int $personId): self
    {
        $this->personId = $personId;

        return $this;
    }

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(?\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getDeathday(): ?\DateTimeInterface
    {
        return $this->deathday;
    }

    public function setDeathday(?\DateTimeInterface $deathday): self
    {
        $this->deathday = $deathday;

        return $this;
    }

    public function getGender(): ?int
    {
        return $this->gender;
    }

    public function setGender(?int $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getKnownFor(): ?string
    {
        return $this->knownFor;
    }

    public function setKnownFor(?string $knownFor): self
    {
        $this->knownFor = $knownFor;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBirthPlace(): ?string
    {
        return $this->birthPlace;
    }

    public function setBirthPlace(?string $birthPlace): self
    {
        $this->birthPlace = $birthPlace;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): self
    {
        $this->biography = $biography;

        return $this;
    }

	public function getMovies(): ?array
	{
		return $this->movies;
	}

	public function setMovies(?array $movies): self
	{
		$this->movies = $movies;

		return $this;
	}

	public function toArray() {
		$vars = get_object_vars ( $this );
		$array = array ();
		foreach ( $vars as $key => $value ) {
			switch ($key) {
				case 'birthday':
				case 'deathday':
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
